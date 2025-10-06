<?php

require_once './Logger.php';
require_once './Gemini.php';
require_once './config.php';

/**
 * Cross-Encoder Reranker
 * Re-ranks retrieved results using LLM-based cross-attention scoring
 */
class CrossEncoderReranker
{
    private $geminiService;
    
    // Configuration constants
    private const TOP_K_FINAL = 10;
    private const RERANK_BATCH_SIZE = 15;
    private const MAX_RETRIES = 2;
    private const MIN_SCORE = 0.0;
    private const MAX_SCORE = 1.0;
    
    // Fusion weights
    private const ALPHA_BALANCED = 0.5;
    private const ALPHA_FAVOR_HYBRID = 0.7;
    private const ALPHA_FAVOR_CROSS = 0.3;
    
    // Agreement bonus
    private const AGREEMENT_BONUS_WEIGHT = 0.1;
    
    public function __construct($geminiService)
    {
        if (!$geminiService) {
            throw new InvalidArgumentException('GeminiService is required');
        }
        
        $this->geminiService = $geminiService;
        
        Logger::debug('CrossEncoderReranker initialized');
    }
    
    /**
     * Main reranking method
     * 
     * @param string $query User query
     * @param array $candidates Retrieved candidates
     * @param array $hybridScores Hybrid retrieval scores
     * @return array Reranked results
     */
    public function rerank($query, $candidates, $hybridScores)
    {
        try {
            Logger::info('Reranking started', [
                'candidates' => count($candidates),
                'query_length' => strlen($query)
            ]);
            
            // Quick return for small result sets
            if (empty($candidates)) {
                return [];
            }
            
            if (count($candidates) <= self::TOP_K_FINAL) {
                return $this->formatCandidatesWithScores($candidates, $hybridScores);
            }
            
            // Step 1: Prepare query-document pairs
            $pairs = $this->preparePairs($query, $candidates);
            
            // Step 2: Get cross-attention scores
            $crossScores = $this->computeCrossAttentionScores($pairs);
            
            // Step 3: Fuse scores
            $finalScores = $this->fuseScores($hybridScores, $crossScores);
            
            // Step 4: Select top-K
            $reranked = $this->selectTopK($candidates, $finalScores);
            
            Logger::info('Reranking completed', [
                'input' => count($candidates),
                'output' => count($reranked)
            ]);
            
            return $reranked;
            
        } catch (Exception $e) {
            Logger::error('Reranking failed', ['error' => $e->getMessage()]);
            
            // Fallback: use hybrid scores only
            return $this->formatCandidatesWithScores(
                array_slice($candidates, 0, self::TOP_K_FINAL),
                array_slice($hybridScores, 0, self::TOP_K_FINAL)
            );
        }
    }
    
    /**
     * Prepare query-document pairs for scoring
     */
    private function preparePairs($query, $candidates)
    {
        $pairs = [];
        
        foreach ($candidates as $idx => $candidate) {
            if (!isset($candidate['item']) || !isset($candidate['item_type'])) {
                Logger::warning('Invalid candidate structure', ['index' => $idx]);
                continue;
            }
            
            $pairs[] = [
                'index' => $idx,
                'query' => $query,
                'document' => $this->extractDocumentText($candidate),
                'item' => $candidate['item'],
                'type' => $candidate['item_type']
            ];
        }
        
        return $pairs;
    }
    
    /**
     * Extract clean document text for reranking
     */
    private function extractDocumentText($candidate)
    {
        $item = $candidate['item'];
        $type = $candidate['item_type'];
        
        if ($type === 'tour') {
            return $this->buildTourText($item);
        }
        
        return $this->buildHotelText($item);
    }
    
    private function buildTourText($tour)
    {
        return sprintf(
            '%s in %s. %d-day tour. Price: %s VND per person.',
            $tour['tour_name'] ?? 'Tour',
            $tour['city_name'] ?? 'Vietnam',
            $tour['duration_days'] ?? 0,
            number_format($tour['price_per_person'] ?? 0)
        );
    }
    
    private function buildHotelText($hotel)
    {
        return sprintf(
            '%s in %s. %s-star hotel. Price: %s VND per night.',
            $hotel['hotel'] ?? $hotel['hotel_name'] ?? 'Hotel',
            $hotel['city_name'] ?? 'Vietnam',
            number_format($hotel['ratings'] ?? 0, 1),
            number_format($hotel['cost'] ?? 0)
        );
    }
    
    /**
     * Compute cross-attention scores using batched API calls
     */
    private function computeCrossAttentionScores($pairs)
    {
        $scores = [];
        $batches = array_chunk($pairs, self::RERANK_BATCH_SIZE);
        
        foreach ($batches as $batchIndex => $batch) {
            $batchScores = $this->scoreBatchWithRetry($batch, $batchIndex);
            $scores = array_merge($scores, $batchScores);
        }
        
        return $scores;
    }
    
    /**
     * Score a batch with retry logic
     */
    private function scoreBatchWithRetry($batch, $batchIndex)
    {
        $attempt = 0;
        $lastError = null;
        
        while ($attempt < self::MAX_RETRIES) {
            try {
                return $this->scoreBatch($batch);
                
            } catch (Exception $e) {
                $attempt++;
                $lastError = $e;
                
                Logger::warning("Batch scoring failed, retry $attempt", [
                    'error' => $e->getMessage(),
                    'batch' => $batchIndex
                ]);
                
                if ($attempt < self::MAX_RETRIES) {
                    usleep(500000); // 0.5 second delay
                }
            }
        }
        
        // All retries failed - use neutral scores
        Logger::error('Batch scoring failed after all retries', [
            'batch' => $batchIndex,
            'error' => $lastError->getMessage()
        ]);
        
        return array_fill(0, count($batch), 0.5);
    }
    
    /**
     * Score a single batch using LLM
     */
    private function scoreBatch($batch)
    {
        $prompt = $this->buildRerankingPrompt($batch);
        
        $response = $this->geminiService->generateText($prompt, [
            'temperature' => 0.1,
            'maxTokens' => 300
        ]);
        
        return $this->parseRerankingScores($response, count($batch));
    }
    
    /**
     * Build optimized reranking prompt
     */
    private function buildRerankingPrompt($batch)
    {
        $query = $batch[0]['query'];
        
        $prompt = "Rate relevance of results to user query. Score 0.0-1.0 only.\n\n";
        $prompt .= "Query: \"$query\"\n\n";
        $prompt .= "Results:\n";
        
        foreach ($batch as $idx => $pair) {
            $prompt .= sprintf("[%d] %s\n", $idx + 1, $pair['document']);
        }
        
        $prompt .= "\nFormat: [1]=0.X [2]=0.Y [3]=0.Z\nScores: ";
        
        return $prompt;
    }
    
    /**
     * Parse LLM scores from response
     */
    private function parseRerankingScores($response, $expectedCount)
    {
        $scores = [];
        
        // Extract scores: [1]=0.8, [2]=0.6, etc.
        preg_match_all('/\[(\d+)\]\s*=\s*([0-9.]+)/', $response, $matches, PREG_SET_ORDER);
        
        if (!empty($matches)) {
            foreach ($matches as $match) {
                $index = intval($match[1]) - 1;
                $score = floatval($match[2]);
                $scores[$index] = $this->clampScore($score);
            }
        }
        
        // Fill missing scores with neutral value
        for ($i = 0; $i < $expectedCount; $i++) {
            if (!isset($scores[$i])) {
                $scores[$i] = 0.5;
            }
        }
        
        return $scores;
    }
    
    /**
     * Clamp score to valid range
     */
    private function clampScore($score)
    {
        return max(self::MIN_SCORE, min(self::MAX_SCORE, $score));
    }
    
    /**
     * Fuse hybrid and cross-encoder scores
     */
    private function fuseScores($hybridScores, $crossScores)
    {
        $fusedScores = [];
        $alpha = $this->computeDynamicAlpha($hybridScores, $crossScores);
        
        foreach ($hybridScores as $idx => $hybridScore) {
            $crossScore = $crossScores[$idx] ?? 0.5;
            
            // Weighted fusion
            $fusedScore = $alpha * $hybridScore + (1 - $alpha) * $crossScore;
            
            // Agreement bonus
            $agreement = 1 - abs($hybridScore - $crossScore);
            $fusedScore += self::AGREEMENT_BONUS_WEIGHT * $agreement;
            
            $fusedScores[$idx] = $this->clampScore($fusedScore);
        }
        
        return $fusedScores;
    }
    
    /**
     * Compute dynamic alpha based on score distributions
     */
    private function computeDynamicAlpha($hybridScores, $crossScores)
    {
        $hybridVar = $this->calculateVariance($hybridScores);
        $crossVar = $this->calculateVariance($crossScores);
        
        // Higher variance indicates more discriminative scores
        if ($hybridVar > $crossVar * 1.5) {
            return self::ALPHA_FAVOR_HYBRID;
        }
        
        if ($crossVar > $hybridVar * 1.5) {
            return self::ALPHA_FAVOR_CROSS;
        }
        
        return self::ALPHA_BALANCED;
    }
    
    /**
     * Calculate variance of score array
     */
    private function calculateVariance($scores)
    {
        if (empty($scores)) {
            return 0;
        }
        
        $mean = array_sum($scores) / count($scores);
        $variance = 0;
        
        foreach ($scores as $score) {
            $variance += pow($score - $mean, 2);
        }
        
        return $variance / count($scores);
    }
    
    /**
     * Select top-K results after reranking
     */
    private function selectTopK($candidates, $finalScores)
    {
        $ranked = [];
        
        foreach ($candidates as $idx => $candidate) {
            $ranked[] = [
                'item' => $candidate['item'],
                'item_type' => $candidate['item_type'],
                'final_score' => $finalScores[$idx] ?? 0,
                'hybrid_score' => $candidate['combined_score'] ?? 0,
                'channels' => $candidate['channels'] ?? [],
                'reranked' => true
            ];
        }
        
        // Sort by final score descending
        usort($ranked, fn($a, $b) => $b['final_score'] <=> $a['final_score']);
        
        return array_slice($ranked, 0, self::TOP_K_FINAL);
    }
    
    /**
     * Format candidates with scores (fallback method)
     */
    private function formatCandidatesWithScores($candidates, $scores)
    {
        $formatted = [];
        
        foreach ($candidates as $idx => $candidate) {
            $formatted[] = [
                'item' => $candidate['item'],
                'item_type' => $candidate['item_type'],
                'final_score' => $scores[$idx] ?? 0.5,
                'hybrid_score' => $scores[$idx] ?? 0.5,
                'channels' => $candidate['channels'] ?? [],
                'reranked' => false
            ];
        }
        
        return $formatted;
    }
}

?>