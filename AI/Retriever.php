<?php
declare(strict_types=1);

require_once './Logger.php';
require_once './Gemini.php';
require_once './config.php';
require_once './Reranker.php';

class HybridRetriever
{
    private GeminiService $geminiService;
    private CrossEncoderReranker $reranker;
    private array $weights;
    private const DEFAULT_LIMIT = 15;

    public function __construct(
        private readonly DatabaseService $dbService,
        private readonly float $k1 = 1.2,
        private readonly float $b = 0.75
    ) {
        $this->geminiService = new GeminiService();
        $this->reranker = new CrossEncoderReranker($this->geminiService);
        
        // Fusion weights (removed SQL channel)
        $this->weights = [
            'semantic' => 0.6,
            'bm25' => 0.4
        ];
        
        Logger::debug("HybridRetriever initialized", ['weights' => $this->weights]);
    }

    /**
     * Main hybrid search pipeline
     */
    public function hybridSearch(string $query, array $entities, string $intent): array
    {
        try {
            Logger::info("Hybrid search started", ['query' => $query, 'intent' => $intent]);

            $filters = $this->prepareFilters($entities, $intent);

            // STEP 1: Parallel Retrieval (BM25 + Semantic)
            $semanticResults = $this->semanticSearch($query, $filters);
            $bm25Results = $this->bm25Search($query, $filters);

            Logger::debug("Retrieval completed", [
                'semantic' => count($semanticResults),
                'bm25' => count($bm25Results)
            ]);

            // STEP 2: Fusion & Pre-ranking
            $preRankedCandidates = $this->fuseResults($semanticResults, $bm25Results);

            if (empty($preRankedCandidates)) {
                return $this->emptyResultsResponse($query);
            }

            // STEP 3: Cross-Encoder Reranking (top 20 candidates)
            $topCandidates = array_slice($preRankedCandidates, 0, 20);
            $hybridScores = array_column($topCandidates, 'combined_score');
            $rerankedResults = $this->reranker->rerank($query, $topCandidates, $hybridScores);

            // STEP 4: Diversity Filtering
            $finalResults = $this->applyDiversityFiltering($rerankedResults, Config::MAX_FINAL_RESULTS);

            $confidence = $this->calculateConfidence($semanticResults, $bm25Results, $finalResults);

            return [
                'success' => true,
                'results' => $finalResults,
                'confidence' => $confidence,
                'retrieval_stats' => [
                    'semantic_results' => count($semanticResults),
                    'bm25_results' => count($bm25Results),
                    'pre_ranked' => count($preRankedCandidates),
                    'reranked' => count($rerankedResults),
                    'final' => count($finalResults)
                ]
            ];

        } catch (Exception $e) {
            Logger::error("Hybrid search failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'results' => [], 'confidence' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Dense Semantic Search using embeddings
     */
    private function semanticSearch($query, $filters, $limit = self::DEFAULT_LIMIT): array
    {
        try {
            $queryVector = $this->geminiService->generateEmbedding($query);
            if (!$queryVector) {
                Logger::warning("Failed to generate query embedding");
                return [];
            }

            $similarityResults = $this->dbService->findSimilarItemsByVector($queryVector, $limit * 2);
            return empty($similarityResults) ? [] : $this->enrichWithItemData($similarityResults, $filters, $limit);

        } catch (Exception $e) {
            Logger::error("Semantic search failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * BM25 Search using tokenized query
     */
    private function bm25Search($query, $filters, $limit = self::DEFAULT_LIMIT): array
    {
        try {
            $queryTerms = $this->tokenizeQuery($query);
            if (empty($queryTerms)) return [];

            $documents = $this->getSearchableDocuments($filters);
            if (empty($documents)) return [];

            $scores = $this->calculateBM25Scores($queryTerms, $documents);
            arsort($scores);

            return $this->buildBM25Results($scores, $documents, $queryTerms, $limit);

        } catch (Exception $e) {
            Logger::error("BM25 search failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * BM25 Score Calculation (Okapi BM25)
     */
    private function calculateBM25Scores($queryTerms, $documents): array
    {
        $scores = array_fill_keys(array_keys($documents), 0.0);
        $docCount = count($documents);
        if ($docCount === 0) return $scores;

        $avgDocLength = array_sum(array_column($documents, 'word_count')) / $docCount;

        // Calculate document frequency
        $df = array_fill_keys($queryTerms, 0);
        foreach ($documents as $doc) {
            $docText = strtolower($doc['text']);
            foreach ($queryTerms as $term) {
                if (stripos($docText, $term) !== false) $df[$term]++;
            }
        }

        // BM25 scoring
        foreach ($documents as $docId => $document) {
            $docLength = $document['word_count'];
            $docText = strtolower($document['text']);

            foreach ($queryTerms as $term) {
                $tf = substr_count($docText, $term);
                if ($tf === 0) continue;

                $idf = log(($docCount - $df[$term] + 0.5) / ($df[$term] + 0.5) + 1);
                $numerator = $tf * ($this->k1 + 1);
                $denominator = $tf + $this->k1 * (1 - $this->b + $this->b * ($docLength / $avgDocLength));
                
                $scores[$docId] += $idf * ($numerator / $denominator);
            }

            // Phrase match bonus
            if (stripos($docText, implode(' ', $queryTerms)) !== false) {
                $scores[$docId] *= 1.5;
            }

            // Title match bonus
            $itemName = $this->getItemName($document['item'], $document['type']);
            foreach ($queryTerms as $term) {
                if (stripos($itemName, $term) !== false) {
                    $scores[$docId] += 1.0;
                }
            }
        }

        return $scores;
    }

    /**
     * Fusion: Combine BM25 + Semantic results with weighted scoring
     */
    private function fuseResults($semanticResults, $bm25Results): array
    {
        // Normalize scores
        $normSemantic = $this->normalizeScores($semanticResults, 'similarity_score');
        $normBM25 = $this->normalizeScores($bm25Results, 'bm25_score');

        // Unify by item ID
        $unified = [];
        foreach (['semantic' => $normSemantic, 'bm25' => $normBM25] as $channel => $results) {
            foreach ($results as $result) {
                if (!isset($result['item']) || !isset($result['item_type'])) continue;

                $itemId = $this->extractItemId($result['item'], $result['item_type']);
                $key = $result['item_type'] . '_' . $itemId;

                if (!isset($unified[$key])) {
                    $unified[$key] = [
                        'item' => $result['item'],
                        'item_type' => $result['item_type'],
                        'item_id' => $itemId,
                        'scores' => ['semantic' => 0, 'bm25' => 0],
                        'channels' => []
                    ];
                }

                $unified[$key]['scores'][$channel] = $result['norm_score'] ?? 0;
                $unified[$key]['channels'][] = $channel;
            }
        }

        // Calculate combined scores
        foreach ($unified as &$result) {
            $combinedScore = 
                $result['scores']['semantic'] * $this->weights['semantic'] +
                $result['scores']['bm25'] * $this->weights['bm25'];
            
            // Consensus bonus (both channels agree)
            $consensusBonus = count($result['channels']) > 1 ? 0.1 : 0;
            
            $result['combined_score'] = min(1.0, $combinedScore + $consensusBonus);
            $result['channel_count'] = count($result['channels']);
        }

        usort($unified, fn($a, $b) => $b['combined_score'] <=> $a['combined_score']);
        return array_values($unified);
    }

    /**
     * Normalize scores to [0,1] range
     */
    private function normalizeScores($results, $scoreField): array
    {
        if (empty($results)) return [];

        $scores = array_column($results, $scoreField);
        $max = max($scores);
        $min = min($scores);
        $range = ($max - $min) == 0 ? 1 : ($max - $min);

        foreach ($results as &$result) {
            $result['norm_score'] = ($result[$scoreField] - $min) / $range;
        }
        return $results;
    }

    /**
     * Diversity Filtering: Limit per city and type
     */
    private function applyDiversityFiltering($rankedResults, $limit): array
    {
        $finalResults = [];
        $cityCount = [];
        $typeCount = ['tour' => 0, 'hotel' => 0];
        
        $maxPerCity = 6;
        $maxPerType = ceil($limit * 0.7);

        foreach ($rankedResults as $result) {
            if (count($finalResults) >= $limit) break;

            $cityName = $result['item']['city_name'] ?? 'Unknown';
            $itemType = $result['item_type'];

            if (($cityCount[$cityName] ?? 0) >= $maxPerCity || $typeCount[$itemType] >= $maxPerType) {
                continue;
            }

            $finalResults[] = $result;
            $cityCount[$cityName] = ($cityCount[$cityName] ?? 0) + 1;
            $typeCount[$itemType]++;
        }

        return $finalResults;
    }

    /**
     * Calculate overall confidence score
     */
    private function calculateConfidence($semanticResults, $bm25Results, $finalResults): float
    {
        $baseConfidence = 0.5;
        $semanticBoost = min(0.25, count($semanticResults) * 0.025);
        $bm25Boost = min(0.25, count($bm25Results) * 0.025);
        
        $consensusBoost = 0;
        if (!empty($finalResults)) {
            $avgConsensus = array_sum(array_column($finalResults, 'channel_count')) / count($finalResults);
            $consensusBoost = min(0.2, ($avgConsensus - 1) * 0.1);
        }

        return min(0.95, max(0.1, $baseConfidence + $semanticBoost + $bm25Boost + $consensusBoost));
    }

    // ==================== HELPER METHODS ====================

    private function prepareFilters($entities, $intent): array
    {
        $filters = [];
        
        if (!empty($entities['cities'])) {
            $cityIds = array_filter(array_map(fn($city) => $city['id'] ?? null, $entities['cities']));
            if (!empty($cityIds)) $filters['cityIds'] = array_unique($cityIds);
        }
        
        if (!empty($entities['budget'])) {
            $filters['budget'] = $entities['budget'];
            $filters['price_condition'] = $entities['price_condition'] ?? 'under';
        }
        
        if (!empty($entities['duration'])) $filters['duration'] = $entities['duration'];
        if (!empty($entities['rating'])) {
            $filters['rating'] = $entities['rating'];
            $filters['rating_condition'] = $entities['rating_condition'] ?? 'exact';
        }
        
        $filters['item_focus'] = match($intent) {
            'tour_search' => 'tour',
            'hotel_search' => 'hotel',
            'mixed_search' => 'both',
            default => null
        };

        return $filters;
    }

    private function enrichWithItemData($similarityResults, $filters, $limit): array
    {
        $tourIds = $hotelIds = [];
        foreach ($similarityResults as $sim) {
            if ($sim['item_type'] === 'tour') $tourIds[] = $sim['item_id'];
            elseif ($sim['item_type'] === 'hotel') $hotelIds[] = $sim['item_id'];
        }

        $tours = !empty($tourIds) ? $this->dbService->getToursByIds($tourIds, $filters) : [];
        $hotels = !empty($hotelIds) ? $this->dbService->getHotelsByIds($hotelIds, $filters) : [];

        $tourMap = $this->createItemMap($tours, 'tourid');
        $hotelMap = $this->createItemMap($hotels, 'hotelid');

        $enriched = [];
        foreach ($similarityResults as $sim) {
            $itemData = ($sim['item_type'] === 'tour') 
                ? ($tourMap[$sim['item_id']] ?? null)
                : ($hotelMap[$sim['item_id']] ?? null);

            if ($itemData && $sim['score'] >= Config::MIN_SIMILARITY_SCORE) {
                $enriched[] = [
                    'item' => $itemData,
                    'similarity_score' => $sim['score'],
                    'item_type' => $sim['item_type']
                ];
            }

            if (count($enriched) >= $limit) break;
        }

        return $enriched;
    }

    private function buildBM25Results($scores, $documents, $queryTerms, $limit): array
    {
        $results = [];
        $topDocIds = array_slice(array_keys($scores), 0, $limit);

        foreach ($topDocIds as $docId) {
            if ($scores[$docId] > Config::MIN_BM25_SCORE) {
                $doc = $documents[$docId];
                $results[] = [
                    'item' => $doc['item'],
                    'bm25_score' => $scores[$docId],
                    'item_type' => $doc['type']
                ];
            }
        }
        return $results;
    }

    private function getSearchableDocuments($filters): array
    {
        $documents = [];
        
        if (!isset($filters['item_focus']) || $filters['item_focus'] !== 'hotel') {
            foreach ($this->dbService->searchItems('tour', $filters) as $tour) {
                $text = $this->buildSearchText($tour, 'tour');
                $documents['tour_' . $tour['tourid']] = [
                    'text' => $text,
                    'word_count' => str_word_count($text),
                    'type' => 'tour',
                    'item' => $tour
                ];
            }
        }

        if (!isset($filters['item_focus']) || $filters['item_focus'] !== 'tour') {
            foreach ($this->dbService->searchItems('hotel', $filters) as $hotel) {
                $text = $this->buildSearchText($hotel, 'hotel');
                $documents['hotel_' . $hotel['hotelid']] = [
                    'text' => $text,
                    'word_count' => str_word_count($text),
                    'type' => 'hotel',
                    'item' => $hotel
                ];
            }
        }

        return $documents;
    }

    private function buildSearchText($item, $type): string
    {
        if ($type === 'tour') {
            return implode(' ', array_filter([
                $item['tour_name'] ?? '',
                $item['city_name'] ?? '',
                $item['description'] ?? '',
                ($item['duration_days'] ?? '') . ' days',
                'tour package travel'
            ]));
        }
        
        return implode(' ', array_filter([
            $item['hotel'] ?? $item['hotel_name'] ?? '',
            $item['city_name'] ?? '',
            $item['description'] ?? '',
            ($item['ratings'] ?? '') . ' star hotel',
            'accommodation stay lodge'
        ]));
    }

    private function tokenizeQuery($text): array
    {
        $text = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text));
        $words = preg_split('/\s+/', trim($text));
        
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        
        return array_unique(array_filter($words, fn($w) => 
            strlen($w) > 2 && !in_array($w, $stopWords) && !is_numeric($w)
        ));
    }

    private function createItemMap($items, $idField): array
    {
        $map = [];
        foreach ($items as $item) {
            if (isset($item[$idField])) $map[$item[$idField]] = $item;
        }
        return $map;
    }

    private function extractItemId($item, $itemType): ?int
    {
        return ($itemType === 'tour') ? ($item['tourid'] ?? null) : ($item['hotelid'] ?? null);
    }

    private function getItemName($item, $type): string
    {
        return strtolower(($type === 'tour') 
            ? ($item['tour_name'] ?? '') 
            : ($item['hotel'] ?? $item['hotel_name'] ?? ''));
    }

    private function emptyResultsResponse($query): array
    {
        Logger::warning("No results found", ['query' => $query]);
        return [
            'success' => false,
            'results' => [],
            'confidence' => 0.1,
            'error' => 'no_results_found'
        ];
    }
}
?>