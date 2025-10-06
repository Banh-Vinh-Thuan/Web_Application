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

    private const MIN_PER_CITY = 3;
    private const DEFAULT_LIMIT = 15;

    public function __construct(
        private readonly DatabaseService $dbService,
        private readonly float $k1 = 1.2,
        private readonly float $b = 0.75
    ) {
        $this->geminiService = new GeminiService();
        $this->reranker = new CrossEncoderReranker($this->geminiService);
        $this->weights = Config::HYBRID_WEIGHTS;
        Logger::debug("HybridRetriever initialized successfully");
    }

    public function hybridSearch(string $query, array $entities, string $intent): array
    {
        try {
            Logger::info("Starting hybrid retrieval", ['query' => $query, 'intent' => $intent]);
            $filters = $this->prepareFilters($entities, $intent);

            // STEP 1: RETRIEVE from all channels
            $semanticResults = $this->semanticSearch($query, $filters);
            $bm25Results = $this->bm25Search($query, $filters);
            $sqlResults = $this->sqlSearch($query, $filters);

            Logger::debug("Retrieval channels executed", [
                'semantic_count' => count($semanticResults),
                'bm25_count' => count($bm25Results),
                'sql_count' => count($sqlResults)
            ]);
            
            // STEP 2: FUSE & PRE-RANK
            $preRankedCandidates = $this->fuseAndPreRank($semanticResults, $bm25Results, $sqlResults);

            if (empty($preRankedCandidates)) {
                Logger::warning("No candidates found after fusion step.", ['query' => $query]);
                // Fallback logic can be added here if necessary
                return [
                    'success' => false,
                    'results' => [],
                    'confidence' => 0.1,
                    'error' => 'no_results_found',
                ];
            }

            // STEP 3: RERANK with Cross-Encoder
            $candidatesToRerank = array_slice($preRankedCandidates, 0, 20); // Rerank top 20
            $hybridScores = array_column($candidatesToRerank, 'combined_score');
            $rerankedResults = $this->reranker->rerank($query, $candidatesToRerank, $hybridScores);

            // STEP 4: FILTER the final list
            $finalResults = $this->applyDiversityFiltering($rerankedResults, Config::MAX_FINAL_RESULTS);
            
            $confidence = $this->calculateOverallConfidence($semanticResults, $bm25Results, $sqlResults, $finalResults);

            return [
                'success' => true,
                'results' => $finalResults,
                'confidence' => $confidence,
                'retrieval_stats' => [
                    'semantic_results' => count($semanticResults),
                    'bm25_results' => count($bm25Results),
                    'sql_results' => count($sqlResults),
                    'pre_ranked_count' => count($preRankedCandidates),
                    'reranked_count' => count($rerankedResults),
                    'final_results' => count($finalResults)
                ]
            ];

        } catch (Exception $e) {
            Logger::error("Hybrid search completely failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'results' => [], 'confidence' => 0, 'error' => $e->getMessage()];
        }
    }
    
    private function fuseAndPreRank(array $semanticResults, array $bm25Results, array $sqlResults): array
    {
        Logger::debug("Starting result fusion and pre-ranking");
        
        $normalizedSemantic = $this->normalizeResults($semanticResults, 'similarity_score');
        $normalizedBM25 = $this->normalizeResults($bm25Results, 'bm25_score');
        $normalizedSQL = $this->normalizeResults($sqlResults, 'sql_relevance');
        
        $unified = $this->unifyResultsByItem($normalizedSemantic, $normalizedBM25, $normalizedSQL);
        
        // Returns results sorted by 'combined_score'
        return $this->calculateCombinedScores($unified);
    }

    private function semanticSearch($query, $filters, $limit = self::DEFAULT_LIMIT) {
        try {
            Logger::debug("Starting semantic search", ['query' => $query]);
            $queryVector = $this->geminiService->generateEmbedding($query);
            if (!$queryVector) {
                Logger::warning("Failed to generate query embedding");
                return [];
            }
            $similarityResults = $this->dbService->findSimilarItemsByVector($queryVector, $limit * 2);
            if (empty($similarityResults)) {
                return [];
            }
            return $this->enrichWithItemData($similarityResults, $filters, $limit);
        } catch (Exception $e) {
            Logger::error("Semantic search failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function bm25Search($query, $filters, $limit = self::DEFAULT_LIMIT) {
        try {
            Logger::debug("Starting BM25 search", ['query' => $query]);
            $queryTerms = $this->tokenizeQuery($query);
            if (empty($queryTerms)) {
                return [];
            }
            $documents = $this->getSearchableDocuments($filters);
            if (empty($documents)) {
                return [];
            }
            $scores = $this->calculateBM25Scores($queryTerms, $documents);
            arsort($scores);
            return $this->buildBM25Results($scores, $documents, $queryTerms, $limit);
        } catch (Exception $e) {
            Logger::error("BM25 search failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function buildBM25Results($scores, $documents, $queryTerms, $limit) {
        $results = [];
        $topDocIds = array_slice(array_keys($scores), 0, $limit);
        foreach ($topDocIds as $docId) {
            if ($scores[$docId] > Config::MIN_BM25_SCORE) {
                $document = $documents[$docId];
                $results[] = [
                    'item' => $document['item'],
                    'bm25_score' => $scores[$docId],
                    'item_type' => $document['type'],
                    'matched_terms' => $this->getMatchedTerms($queryTerms, $document['text'])
                ];
            }
        }
        return $results;
    }

    private function sqlSearch($query, $filters, $limit = self::DEFAULT_LIMIT) {
        try {
            Logger::debug("Starting SQL search", ['query' => $query]);
            $enhancedFilters = $this->analyzeQueryForFilters($query, $filters);
            $searchTours = $this->shouldSearchTours($query, $enhancedFilters);
            $searchHotels = $this->shouldSearchHotels($query, $enhancedFilters);
            $isMultiCity = isset($enhancedFilters['cityIds']) && count($enhancedFilters['cityIds']) >= 2;
            if ($isMultiCity) {
                return $this->multiCitySearch($enhancedFilters, $searchTours, $searchHotels, $query, $limit);
            }
            return $this->singleCitySearch($enhancedFilters, $searchTours, $searchHotels, $query, $limit);
        } catch (Exception $e) {
            Logger::error("SQL search failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function multiCitySearch($filters, $searchTours, $searchHotels, $query, $limit) {
        $results = [];
        if ($searchTours) {
            $results = array_merge($results, $this->searchPerCity($filters, 'tour', $query));
            $results = $this->ensureMinimumResults($results, $filters, 'tour', $query, $limit);
        }
        if ($searchHotels) {
            $hotelResults = $this->searchPerCity($filters, 'hotel', $query);
            $results = array_merge($results, $hotelResults);
            $results = $this->ensureMinimumResults($results, $filters, 'hotel', $query, $limit);
        }
        return $this->sortAndLimitResults($results, $limit);
    }

    private function searchPerCity($filters, $type, $query) {
        $results = [];
        foreach ($filters['cityIds'] as $cityId) {
            $cityFilter = $filters;
            $cityFilter['cityIds'] = [$cityId];
            $items = $this->dbService->searchItems($type, $cityFilter, self::MIN_PER_CITY);
            foreach ($items as $item) {
                $results[] = [
                    'item' => $item,
                    'sql_relevance' => $this->calculateRelevanceScore($query, $item, $type),
                    'item_type' => $type,
                    'city_priority' => true
                ];
            }
        }
        return $results;
    }
    
    private function ensureMinimumResults($results, $filters, $type, $query, $limit) {
        $typeResults = array_filter($results, fn($r) => $r['item_type'] === $type);
        if (count($typeResults) < self::MIN_PER_CITY * 2) {
            $generalItems = $this->dbService->searchItems($type, $filters, $limit);
            foreach ($generalItems as $item) {
                if (!$this->itemExists($results, $item, $type)) {
                    $results[] = [
                        'item' => $item,
                        'sql_relevance' => $this->calculateRelevanceScore($query, $item, $type),
                        'item_type' => $type,
                        'city_priority' => false
                    ];
                }
            }
        }
        return $results;
    }
    
    private function itemExists($results, $item, $type) {
        $idField = $type === 'tour' ? 'tourid' : 'hotelid';
        foreach ($results as $existing) {
            if ($existing['item_type'] === $type && $existing['item'][$idField] === $item[$idField]) {
                return true;
            }
        }
        return false;
    }
    
    private function singleCitySearch($filters, $searchTours, $searchHotels, $query, $limit) {
        $results = [];
        if ($searchTours) {
            $tours = $this->dbService->searchItems('tour', $filters);
            foreach ($tours as $tour) {
                $results[] = [
                    'item' => $tour,
                    'sql_relevance' => $this->calculateRelevanceScore($query, $tour, 'tour'),
                    'item_type' => 'tour'
                ];
            }
        }
        if ($searchHotels) {
            $hotels = $this->dbService->searchItems('hotel', $filters);
            foreach ($hotels as $hotel) {
                $results[] = [
                    'item' => $hotel,
                    'sql_relevance' => $this->calculateRelevanceScore($query, $hotel, 'hotel'),
                    'item_type' => 'hotel'
                ];
            }
        }
        return $this->sortAndLimitResults($results, $limit);
    }

    private function sortAndLimitResults($results, $limit) {
        usort($results, function($a, $b) {
            $aPriority = $a['city_priority'] ?? false;
            $bPriority = $b['city_priority'] ?? false;
            if ($aPriority && !$bPriority) return -1;
            if (!$aPriority && $bPriority) return 1;
            return $b['sql_relevance'] <=> $a['sql_relevance'];
        });
        return array_slice($results, 0, $limit);
    }
    
    private function prepareFilters($entities, $intent) {
        $filters = [];
        if (!empty($entities['cities'])) {
            $cityIds = array_filter(
                array_map(fn($city) => $city['id'] ?? null, $entities['cities']),
                fn($id) => $id !== null
            );
            if (!empty($cityIds)) {
                $filters['cityIds'] = array_unique($cityIds);
            }
        }
        if (!empty($entities['budget'])) {
            $filters['budget'] = $entities['budget'];
            $filters['price_condition'] = $entities['price_condition'] ?? 'under';
        }
        if (!empty($entities['duration'])) {
            $filters['duration'] = $entities['duration'];
        }
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

    private function enrichWithItemData($similarityResults, $filters, $limit) {
        $enrichedResults = [];
        $tourIds = [];
        $hotelIds = [];
        foreach ($similarityResults as $simResult) {
            if ($simResult['item_type'] === 'tour') {
                $tourIds[] = $simResult['item_id'];
            } elseif ($simResult['item_type'] === 'hotel') {
                $hotelIds[] = $simResult['item_id'];
            }
        }
        try {
            $tours = !empty($tourIds) ? $this->dbService->getToursByIds($tourIds, $filters) : [];
            $hotels = !empty($hotelIds) ? $this->dbService->getHotelsByIds($hotelIds, $filters) : [];
            $tourMap = $this->createItemMap($tours, 'tourid');
            $hotelMap = $this->createItemMap($hotels, 'hotelid');
            foreach ($similarityResults as $simResult) {
                $itemData = null;
                if ($simResult['item_type'] === 'tour' && isset($tourMap[$simResult['item_id']])) {
                    $itemData = $tourMap[$simResult['item_id']];
                } elseif ($simResult['item_type'] === 'hotel' && isset($hotelMap[$simResult['item_id']])) {
                    $itemData = $hotelMap[$simResult['item_id']];
                }
                if ($itemData && $this->meetsQualityThreshold($simResult['score'])) {
                    $enrichedResults[] = [
                        'item' => $itemData,
                        'similarity_score' => $simResult['score'],
                        'retrieval_type' => 'semantic',
                        'item_type' => $simResult['item_type'],
                        'embedding_quality' => $this->assessEmbeddingQuality($simResult['score'])
                    ];
                }
                if (count($enrichedResults) >= $limit) {
                    break;
                }
            }
        } catch (Exception $e) {
            Logger::error("Failed to enrich semantic results", ['error' => $e->getMessage()]);
        }
        usort($enrichedResults, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);
        return $enrichedResults;
    }

    private function createItemMap($items, $idField) {
        $map = [];
        foreach ($items as $item) {
            if (isset($item[$idField])) {
                $map[$item[$idField]] = $item;
            }
        }
        return $map;
    }
    
    private function meetsQualityThreshold($score) {
        return $score >= Config::MIN_SIMILARITY_SCORE;
    }
    
    private function assessEmbeddingQuality($score) {
        if ($score >= 0.8) return 'excellent';
        if ($score >= 0.6) return 'good';
        if ($score >= 0.4) return 'fair';
        return 'poor';
    }

    private function tokenizeQuery($text) {
        $text = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text));
        $words = preg_split('/\s+/', trim($text));
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had'];
        $cleanTerms = array_filter($words, fn($word) => strlen($word) > 2 && !in_array($word, $stopWords) && !is_numeric($word));
        return array_unique($cleanTerms);
    }

    private function getSearchableDocuments($filters) {
        $documents = [];
        try {
            if (!isset($filters['item_focus']) || $filters['item_focus'] !== 'hotel') {
                $tours = $this->dbService->searchItems('tour', $filters);
                foreach ($tours as $tour) {
                    $text = $this->buildTourSearchText($tour);
                    $documents['tour_' . $tour['tourid']] = ['text' => $text, 'word_count' => str_word_count($text), 'type' => 'tour', 'item' => $tour];
                }
            }
            if (!isset($filters['item_focus']) || $filters['item_focus'] !== 'tour') {
                $hotels = $this->dbService->searchItems('hotel', $filters);
                foreach ($hotels as $hotel) {
                    $text = $this->buildHotelSearchText($hotel);
                    $documents['hotel_' . $hotel['hotelid']] = ['text' => $text, 'word_count' => str_word_count($text), 'type' => 'hotel', 'item' => $hotel];
                }
            }
        } catch (Exception $e) {
            Logger::error("Failed to get searchable documents", ['error' => $e->getMessage()]);
        }
        return $documents;
    }

    private function buildTourSearchText($tour) {
        $parts = [$tour['tour_name'] ?? '', $tour['city_name'] ?? '', $tour['description'] ?? '', ($tour['duration_days'] ?? '') . ' days', 'tour package travel'];
        return implode(' ', array_filter($parts));
    }

    private function buildHotelSearchText($hotel) {
        $parts = [$hotel['hotel'] ?? $hotel['hotel_name'] ?? '', $hotel['city_name'] ?? '', $hotel['description'] ?? '', ($hotel['ratings'] ?? '') . ' star hotel', 'accommodation stay lodge'];
        return implode(' ', array_filter($parts));
    }

    private function calculateBM25Scores($queryTerms, $documents) {
        $scores = array_fill_keys(array_keys($documents), 0.0);
        $docCount = count($documents);
        if ($docCount === 0) return $scores;
        $totalWordCount = array_sum(array_column($documents, 'word_count'));
        $avgDocLength = $totalWordCount / $docCount;
        $documentFrequency = [];
        foreach ($queryTerms as $term) {
            $df = 0;
            foreach ($documents as $doc) {
                if (stripos($doc['text'], $term) !== false) {
                    $df++;
                }
            }
            $documentFrequency[$term] = max(1, $df);
        }
        foreach ($documents as $docId => $document) {
            $docLength = $document['word_count'];
            $docText = strtolower($document['text']);
            foreach ($queryTerms as $term) {
                $termFrequency = substr_count($docText, $term);
                if ($termFrequency > 0) {
                    $idf = log(($docCount - $documentFrequency[$term] + 0.5) / ($documentFrequency[$term] + 0.5) + 1);
                    $numerator = $termFrequency * ($this->k1 + 1);
                    $denominator = $termFrequency + $this->k1 * (1 - $this->b + $this->b * ($docLength / $avgDocLength));
                    $termScore = $idf * ($numerator / $denominator);
                    $scores[$docId] += $termScore;
                }
            }
            $queryPhrase = implode(' ', $queryTerms);
            if (stripos($docText, $queryPhrase) !== false) {
                $scores[$docId] *= 1.5;
            }
            $itemName = $this->getItemName($document['item'], $document['type']);
            foreach ($queryTerms as $term) {
                if (stripos($itemName, $term) !== false) {
                    $scores[$docId] += 1.0;
                }
            }
        }
        return $scores;
    }

    private function getItemName($item, $type) {
        if ($type === 'tour') {
            return strtolower($item['tour_name'] ?? '');
        } elseif ($type === 'hotel') {
            return strtolower($item['hotel'] ?? $item['hotel_name'] ?? '');
        }
        return '';
    }

    private function analyzeQueryForFilters($query, $existingFilters) {
        $filters = $existingFilters;
        if (!isset($filters['rating'])) {
            if (preg_match('/(?:at least|minimum|min|above)\s*(\d+)\s*(?:star|sao)/i', $query, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'minimum';
                }
            } elseif (preg_match('/(?:under|below|max|maximum)\s*(\d+)\s*(?:star|sao)/i', $query, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'maximum';
                }
            } elseif (preg_match('/(?:exactly|only)\s*(\d+)\s*(?:star|sao)|(\d+)\s*(?:star|sao)\s+(?:only|exactly)/i', $query, $matches)) {
                $rating = intval($matches[1] ?: $matches[2]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'exact';
                }
            } elseif (preg_match('/(\d+)\s*(?:star|sao)(?:\s+hotel)?/i', $query, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'minimum';
                }
            }
        }
        return $filters;
    }

    private function shouldSearchTours($query, $filters) {
        if (isset($filters['item_focus']) && $filters['item_focus'] === 'hotel') return false;
        $queryLower = strtolower($query);
        $tourKeywords = ['tour', 'trip', 'package', 'excursion', 'travel', 'journey'];
        foreach ($tourKeywords as $keyword) {
            if (strpos($queryLower, $keyword) !== false) return true;
        }
        $hotelKeywords = ['hotel', 'accommodation', 'stay', 'resort', 'lodge'];
        foreach ($hotelKeywords as $keyword) {
            if (strpos($queryLower, $keyword) !== false) return false;
        }
        return true;
    }

    private function shouldSearchHotels($query, $filters) {
        if (isset($filters['item_focus']) && $filters['item_focus'] === 'tour') return false;
        $queryLower = strtolower($query);
        $hotelKeywords = ['hotel', 'accommodation', 'stay', 'resort', 'lodge', 'room'];
        foreach ($hotelKeywords as $keyword) {
            if (strpos($queryLower, $keyword) !== false) return true;
        }
        if (isset($filters['rating']) || preg_match('/\d+\s*star/i', $query)) return true;
        return false;
    }

    private function calculateRelevanceScore($query, $item, $type) {
        $score = 0.5;
        $queryLower = strtolower($query);
        $itemName = strtolower($type === 'tour' ? ($item['tour_name'] ?? '') : ($item['hotel'] ?? ''));
        if (strpos($itemName, $queryLower) !== false) {
            $score += 0.4;
        } else {
            $queryWords = preg_split('/\s+/', $queryLower);
            $matchedWords = count(array_filter($queryWords, fn($word) => strlen($word) > 2 && strpos($itemName, $word) !== false));
            $score += ($matchedWords / max(count($queryWords), 1)) * 0.3;
        }
        if (!empty($item['city_name']) && strpos($queryLower, strtolower($item['city_name'])) !== false) {
            $score += 0.2;
        }
        return min(1.0, $score);
    }

    private function normalizeResults($results, $scoreField) {
        if (empty($results)) return [];
        $scores = array_column($results, $scoreField);
        $maxScore = max($scores);
        $minScore = min($scores);
        $range = ($maxScore - $minScore) == 0 ? 1 : ($maxScore - $minScore);
        foreach ($results as &$result) {
            $result['norm_score'] = ($result[$scoreField] - $minScore) / $range;
        }
        return $results;
    }

    private function unifyResultsByItem($semanticResults, $bm25Results, $sqlResults) {
        $unified = [];
        $resultSets = ['semantic' => $semanticResults, 'bm25' => $bm25Results, 'sql' => $sqlResults];
        foreach ($resultSets as $channelType => $results) {
            foreach ($results as $result) {
                if (!isset($result['item']) || !isset($result['item_type'])) continue;
                $itemId = $this->extractItemId($result['item'], $result['item_type']);
                $key = $result['item_type'] . '_' . $itemId;
                if (!isset($unified[$key])) {
                    $unified[$key] = [
                        'item' => $result['item'],
                        'item_type' => $result['item_type'],
                        'item_id' => $itemId,
                        'scores' => ['semantic' => 0, 'bm25' => 0, 'sql' => 0],
                        'channels' => [],
                        'raw_scores' => []
                    ];
                }
                $unified[$key]['scores'][$channelType] = $result['norm_score'] ?? 0;
                $unified[$key]['channels'][] = $channelType;
                $originalScore = $result['similarity_score'] ?? $result['bm25_score'] ?? $result['sql_relevance'] ?? 0;
                $unified[$key]['raw_scores'][$channelType] = $originalScore;
            }
        }
        return $unified;
    }
    
    private function extractItemId($item, $itemType) {
        if ($itemType === 'tour') return $item['tourid'] ?? null;
        if ($itemType === 'hotel') return $item['hotelid'] ?? null;
        return null;
    }

    private function calculateCombinedScores($unifiedResults) {
        foreach ($unifiedResults as &$result) {
            $combinedScore = 0;
            foreach (['semantic', 'bm25', 'sql'] as $channel) {
                $combinedScore += ($result['scores'][$channel] ?? 0) * ($this->weights[$channel] ?? 0.33);
            }
            $channelCount = count($result['channels']);
            $consensusBonus = ($channelCount > 1) ? (0.1 * ($channelCount - 1)) : 0;
            $result['combined_score'] = min(1.0, $combinedScore + $consensusBonus);
            $result['channel_count'] = $channelCount;
        }
        uasort($unifiedResults, fn($a, $b) => $b['combined_score'] <=> $a['combined_score']);
        return array_values($unifiedResults);
    }
    
    private function applyDiversityFiltering($rankedResults, $limit) {
        if (empty($rankedResults)) return [];
        $finalResults = [];
        $cityCount = [];
        $typeCount = ['tour' => 0, 'hotel' => 0];
        $maxPerCity = 6;
        $maxPerType = ceil($limit * 0.7);
        foreach ($rankedResults as $result) {
            if (count($finalResults) >= $limit) break;
            $cityName = $result['item']['city_name'] ?? 'Unknown';
            $itemType = $result['item_type'];
            $currentCityCount = $cityCount[$cityName] ?? 0;
            if ($currentCityCount >= $maxPerCity || $typeCount[$itemType] >= $maxPerType) {
                continue;
            }
            $finalResults[] = $result;
            $cityCount[$cityName] = $currentCityCount + 1;
            $typeCount[$itemType]++;
        }
        return $finalResults;
    }
    
    private function getMatchedTerms($queryTerms, $documentText) {
        $matched = [];
        $docTextLower = strtolower($documentText);
        foreach ($queryTerms as $term) {
            if (stripos($docTextLower, $term) !== false) {
                $matched[] = $term;
            }
        }
        return $matched;
    }
    
    private function calculateOverallConfidence($semanticResults, $bm25Results, $sqlResults, $finalResults) {
        $baseConfidence = 0.5;
        $semanticBoost = min(0.2, count($semanticResults) * 0.02);
        $bm25Boost = min(0.15, count($bm25Results) * 0.015);
        $sqlBoost = min(0.15, count($sqlResults) * 0.015);
        $consensusBoost = 0;
        if (!empty($finalResults)) {
            $totalConsensus = array_sum(array_column($finalResults, 'channel_count'));
            $avgConsensus = $totalConsensus / count($finalResults);
            $consensusBoost = min(0.2, ($avgConsensus - 1) * 0.1);
        }
        $finalConfidence = $baseConfidence + $semanticBoost + $bm25Boost + $sqlBoost + $consensusBoost;
        return min(0.95, max(0.1, $finalConfidence));
    }
}