<?php
require_once './Logger.php';
require_once './GeminiService.php';
require_once './config.php';

/**
 * Parallel Hybrid Retrieval System
 * Implements: Semantic (Vector) + BM25 (Keyword) + SQL (Structured) retrieval
 * Then merges and re-ranks results for optimal performance
 */
class HybridRetriever {
    private $dbService;
    private $geminiService;
    private $k1;
    private $b;
    private $weights;

    private const MIN_PER_CITY = 3;
    private const MAX_PER_CITY = 6;
    private const DEFAULT_LIMIT = 15;

    public function __construct($dbService, $k1 = 1.2, $b = 0.75) {
        if (!$dbService) {
            throw new InvalidArgumentException("DatabaseService is required");
        }

        $this->dbService = $dbService;
        $this->geminiService = $this->initializeGeminiService();
        $this->k1 = $k1;
        $this->b = $b;
        $this->weights = Config::HYBRID_WEIGHTS;

        Logger::debug("HybridRetriever initialized successfully");
    }

    private function initializeGeminiService() {
        try {
            return new GeminiService();
        } catch (Exception $e) {
            Logger::error("Failed to initialize GeminiService", ['error' => $e->getMessage()]);
            return new class {
                public function generateEmbedding($text) {
                    Logger::warning("Using fallback embedding service");
                    return null;
                }
            };
        }
    }
    /**
     * Main hybrid search method - executes all channels in parallel
     */
    public function hybridSearch($query, $entities, $intent) {
        try {
            Logger::info("Starting hybrid retrieval", ['query' => $query, 'intent' => $intent]);
            
            $filters = $this->prepareFilters($entities, $intent);
            
            // Execute all retrieval channels with error handling
            $semanticResults = [];
            $bm25Results = [];
            $sqlResults = [];
            
            try {
                $semanticResults = $this->semanticSearch($query, $filters);
            } catch (Exception $e) {
                Logger::error("Semantic search failed", ['error' => $e->getMessage()]);
            }
            
            try {
                $bm25Results = $this->bm25Search($query, $filters);
            } catch (Exception $e) {
                Logger::error("BM25 search failed", ['error' => $e->getMessage()]);
            }
            
            try {
                $sqlResults = $this->sqlSearch($query, $filters);
            } catch (Exception $e) {
                Logger::error("SQL search failed", ['error' => $e->getMessage()]);
            }
            
            Logger::debug("Retrieval channels executed", [
                'semantic_count' => count($semanticResults),
                'bm25_count' => count($bm25Results),
                'sql_count' => count($sqlResults)
            ]);
            
            // CRITICAL: Check if ALL channels failed
            $totalResults = count($semanticResults) + count($bm25Results) + count($sqlResults);
            
            if ($totalResults === 0) {
                Logger::error("All retrieval channels returned empty results", [
                    'query' => $query,
                    'filters' => $filters,
                    'intent' => $intent
                ]);
                
                // Try emergency fallback search without filters
                try {
                    Logger::warning("Attempting emergency fallback search");
                    $emergencyFilters = ['limit' => 10];
                    
                    if ($intent === 'hotel_search' || stripos($query, 'hotel') !== false) {
                        $sqlResults = $this->dbService->searchItems('hotel', $emergencyFilters);
                    } else {
                        $sqlResults = $this->dbService->searchItems('tour', $emergencyFilters);
                    }
                    
                    if (!empty($sqlResults)) {
                        // Convert to proper format
                        $sqlResults = array_map(function($item) use ($intent) {
                            return [
                                'item' => $item,
                                'sql_relevance' => 0.5,
                                'item_type' => $intent === 'hotel_search' ? 'hotel' : 'tour'
                            ];
                        }, $sqlResults);
                        
                        Logger::info("Emergency fallback found results", ['count' => count($sqlResults)]);
                    }
                } catch (Exception $emergencyError) {
                    Logger::critical("Emergency fallback also failed", [
                        'error' => $emergencyError->getMessage()
                    ]);
                }
            }
            
            // Merge and rerank results
            $mergedResults = $this->mergeAndRerank($semanticResults, $bm25Results, $sqlResults);
            
            // CRITICAL: Ensure we have results
            if (empty($mergedResults)) {
                Logger::error("Merge and rerank returned empty results");
                
                return [
                    'success' => false,
                    'results' => [],
                    'confidence' => 0,
                    'error' => 'no_results_found',
                    'retrieval_stats' => [
                        'semantic_results' => count($semanticResults),
                        'bm25_results' => count($bm25Results),
                        'sql_results' => count($sqlResults),
                        'final_results' => 0
                    ]
                ];
            }
            
            $confidence = $this->calculateOverallConfidence($semanticResults, $bm25Results, $sqlResults, $mergedResults);
            
            return [
                'success' => true,
                'results' => $mergedResults,
                'confidence' => $confidence,
                'retrieval_stats' => [
                    'semantic_results' => count($semanticResults),
                    'bm25_results' => count($bm25Results),
                    'sql_results' => count($sqlResults),
                    'final_results' => count($mergedResults)
                ]
            ];
            
        } catch (Exception $e) {
            Logger::error("Hybrid search completely failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'results' => [],
                'confidence' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * CHANNEL 1: Semantic Search using Vector Embeddings
     */
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

    /**
     * CHANNEL 2: BM25 Keyword Search
     */
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

    /**
     * CHANNEL 3: SQL Structured Search
     */
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

    /**
     * Merge and rerank results from all channels
     */
    private function mergeAndRerank($semanticResults, $bm25Results, $sqlResults, $limit = 10) {
        Logger::debug("Starting result merge and rerank");
        
        $normalizedSemantic = $this->normalizeResults($semanticResults, 'similarity_score');
        $normalizedBM25 = $this->normalizeResults($bm25Results, 'bm25_score');
        $normalizedSQL = $this->normalizeResults($sqlResults, 'sql_relevance');
        
        $unified = $this->unifyResultsByItem($normalizedSemantic, $normalizedBM25, $normalizedSQL);
        $ranked = $this->calculateCombinedScores($unified);
        
        return $this->applyDiversityFiltering($ranked, $limit);
    }

    // Prepare filters from extracted entities
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


    /**
     * Enrich similarity results with full item data
     */
    private function enrichWithItemData($similarityResults, $filters, $limit) {
        $enrichedResults = [];
        $tourIds = [];
        $hotelIds = [];
        
        // Group IDs by type
        foreach ($similarityResults as $simResult) {
            if ($simResult['item_type'] === 'tour') {
                $tourIds[] = $simResult['item_id'];
            } elseif ($simResult['item_type'] === 'hotel') {
                $hotelIds[] = $simResult['item_id'];
            }
        }
        
        try {
            // Fetch full item data
            $tours = !empty($tourIds) ? $this->dbService->getToursByIds($tourIds, $filters) : [];
            $hotels = !empty($hotelIds) ? $this->dbService->getHotelsByIds($hotelIds, $filters) : [];
            
            // Create lookup maps
            $tourMap = $this->createItemMap($tours, 'tourid');
            $hotelMap = $this->createItemMap($hotels, 'hotelid');
            
            // Match similarity results with item data
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
        
        // Sort by similarity score
        usort($enrichedResults, function($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });
        
        return $enrichedResults;
    }

    /**
     * Create item lookup map by ID field
     */
    private function createItemMap($items, $idField) {
        $map = [];
        foreach ($items as $item) {
            if (isset($item[$idField])) {
                $map[$item[$idField]] = $item;
            }
        }
        return $map;
    }

    /**
     * Check if similarity score meets quality threshold
     */
    private function meetsQualityThreshold($score) {
        return $score >= Config::MIN_SIMILARITY_SCORE;
    }

    /**
     * Assess embedding quality based on similarity score
     */
    private function assessEmbeddingQuality($score) {
        if ($score >= 0.8) return 'excellent';
        if ($score >= 0.6) return 'good';
        if ($score >= 0.4) return 'fair';
        return 'poor';
    }

    /**
     * Tokenize query for BM25 search
     */
    private function tokenizeQuery($text) {
        $text = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text));
        $words = preg_split('/\s+/', trim($text));
        
        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with',
            'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had'
        ];
        
        $cleanTerms = array_filter($words, fn($word) => 
            strlen($word) > 2 && !in_array($word, $stopWords) && !is_numeric($word)
        );
        
        return array_unique($cleanTerms);
    }

    /**
     * Get searchable documents for BM25
     */
    private function getSearchableDocuments($filters) {
        $documents = [];
        
        try {
            // Get tours if not filtering for hotels only
            if (!isset($filters['item_focus']) || $filters['item_focus'] !== 'hotel') {
                $tours = $this->dbService->searchItems('tour', $filters);
                foreach ($tours as $tour) {
                    $text = $this->buildTourSearchText($tour);
                    $documents['tour_' . $tour['tourid']] = [
                        'text' => $text,
                        'word_count' => str_word_count($text),
                        'type' => 'tour',
                        'item' => $tour
                    ];
                }
            }
            
            // Get hotels if not filtering for tours only
            if (!isset($filters['item_focus']) || $filters['item_focus'] !== 'tour') {
                $hotels = $this->dbService->searchItems('hotel', $filters);
                foreach ($hotels as $hotel) {
                    $text = $this->buildHotelSearchText($hotel);
                    $documents['hotel_' . $hotel['hotelid']] = [
                        'text' => $text,
                        'word_count' => str_word_count($text),
                        'type' => 'hotel',
                        'item' => $hotel
                    ];
                }
            }
            
        } catch (Exception $e) {
            Logger::error("Failed to get searchable documents", ['error' => $e->getMessage()]);
        }
        
        return $documents;
    }

    /**
     * Build searchable text for tours
     */
    private function buildTourSearchText($tour) {
        $parts = [
            $tour['tour_name'] ?? '',
            $tour['city_name'] ?? '',
            $tour['description'] ?? '',
            ($tour['duration_days'] ?? '') . ' days',
            'tour package travel'
        ];
        
        return implode(' ', array_filter($parts));
    }

    /**
     * Build searchable text for hotels
     */
    private function buildHotelSearchText($hotel) {
        $parts = [
            $hotel['hotel'] ?? $hotel['hotel_name'] ?? '',
            $hotel['city_name'] ?? '',
            $hotel['description'] ?? '',
            ($hotel['ratings'] ?? '') . ' star hotel',
            'accommodation stay lodge'
        ];
        
        return implode(' ', array_filter($parts));
    }

    /**
     * Calculate BM25 scores for documents
     */
    private function calculateBM25Scores($queryTerms, $documents) {
        $scores = array_fill_keys(array_keys($documents), 0.0);
        $docCount = count($documents);
        
        if ($docCount === 0) return $scores;
        
        // Calculate average document length
        $totalWordCount = array_sum(array_column($documents, 'word_count'));
        $avgDocLength = $totalWordCount / $docCount;
        
        // Calculate document frequency for each term
        $documentFrequency = [];
        foreach ($queryTerms as $term) {
            $df = 0;
            foreach ($documents as $doc) {
                if (stripos($doc['text'], $term) !== false) {
                    $df++;
                }
            }
            $documentFrequency[$term] = max(1, $df); // Avoid division by zero
        }
        
        // Calculate BM25 score for each document
        foreach ($documents as $docId => $document) {
            $docLength = $document['word_count'];
            $docText = strtolower($document['text']);
            
            foreach ($queryTerms as $term) {
                $termFrequency = substr_count($docText, $term);
                
                if ($termFrequency > 0) {
                    // IDF calculation
                    $idf = log(($docCount - $documentFrequency[$term] + 0.5) / ($documentFrequency[$term] + 0.5) + 1);
                    
                    // BM25 formula
                    $numerator = $termFrequency * ($this->k1 + 1);
                    $denominator = $termFrequency + $this->k1 * (1 - $this->b + $this->b * ($docLength / $avgDocLength));
                    $termScore = $idf * ($numerator / $denominator);
                    
                    $scores[$docId] += $termScore;
                }
            }
            
            // Apply boost for exact phrase matches
            $queryPhrase = implode(' ', $queryTerms);
            if (stripos($docText, $queryPhrase) !== false) {
                $scores[$docId] *= 1.5; // 50% boost for exact phrase match
            }
            
            // Apply boost for title matches
            $itemName = $this->getItemName($document['item'], $document['type']);
            foreach ($queryTerms as $term) {
                if (stripos($itemName, $term) !== false) {
                    $scores[$docId] += 1.0; // Fixed boost for title match
                }
            }
        }
        
        return $scores;
    }

    /**
     * Get item name for matching
     */
    private function getItemName($item, $type) {
        if ($type === 'tour') {
            return strtolower($item['tour_name'] ?? '');
        } elseif ($type === 'hotel') {
            return strtolower($item['hotel'] ?? $item['hotel_name'] ?? '');
        }
        return '';
    }

    /**
     * Analyze query for additional filters
     */
    private function analyzeQueryForFilters($query, $existingFilters) {
        $filters = $existingFilters;
        $queryLower = strtolower($query);
        
        // Extract rating from query with exact matching
        if (!isset($filters['rating'])) {
            // Pattern for minimum rating (most common)
            if (preg_match('/(?:at least|minimum|min|above)\s*(\d+)\s*(?:star|sao)/i', $query, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'minimum';
                }
            }
            // Pattern for maximum rating
            elseif (preg_match('/(?:under|below|max|maximum)\s*(\d+)\s*(?:star|sao)/i', $query, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'maximum';
                }
            }
            // Pattern for exact rating (must be explicit)
            elseif (preg_match('/(?:exactly|only)\s*(\d+)\s*(?:star|sao)|(\d+)\s*(?:star|sao)\s+(?:only|exactly)/i', $query, $matches)) {
                $rating = intval($matches[1] ?: $matches[2]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'exact';
                }
            }
            // Default pattern - treat as MINIMUM (most intuitive)
            elseif (preg_match('/(\d+)\s*(?:star|sao)(?:\s+hotel)?/i', $query, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'minimum'; // Changed default
                }
            }
        }
        
        // Extract budget hints from query (existing logic)
        if (!isset($filters['budget'])) {
            if (preg_match('/(?:under|below|less than)\s*(\d+(?:,\d{3})*)\s*(?:vnd|dong)/i', $query, $matches)) {
                $filters['budget'] = intval(str_replace(',', '', $matches[1]));
                $filters['price_condition'] = 'under';
            } elseif (preg_match('/(?:over|above|more than)\s*(\d+(?:,\d{3})*)\s*(?:vnd|dong)/i', $query, $matches)) {
                $filters['budget'] = intval(str_replace(',', '', $matches[1]));
                $filters['price_condition'] = 'over';
            } elseif (preg_match('/(\d+)\s*(?:million|triệu)/i', $query, $matches)) {
                $filters['budget'] = intval($matches[1]) * 1000000;
                $filters['price_condition'] = 'under';
            }
        }
        
        // Extract duration from query (existing logic)
        if (!isset($filters['duration'])) {
            if (preg_match('/(\d+)\s*(?:day|days|ngày)/i', $query, $matches)) {
                $filters['duration'] = intval($matches[1]);
            }
        }
        
        // Set quality preferences
        if (strpos($queryLower, 'luxury') !== false || strpos($queryLower, 'premium') !== false) {
            $filters['rating'] = $filters['rating'] ?? 4;
            $filters['rating_condition'] = 'minimum';
            $filters['price_condition'] = 'over';
        } elseif (strpos($queryLower, 'budget') !== false || strpos($queryLower, 'cheap') !== false) {
            $filters['price_condition'] = 'under';
        }
        
        return $filters;
    }

    /**
     * Determine if we should search tours
     */
    private function shouldSearchTours($query, $filters) {
        if (isset($filters['item_focus']) && $filters['item_focus'] === 'hotel') {
            return false;
        }
        
        $queryLower = strtolower($query);
        
        // Search tours if tour-related keywords found
        $tourKeywords = ['tour', 'trip', 'package', 'excursion', 'travel', 'journey'];
        foreach ($tourKeywords as $keyword) {
            if (strpos($queryLower, $keyword) !== false) {
                return true;
            }
        }
        
        // Search tours if no hotel-specific keywords found
        $hotelKeywords = ['hotel', 'accommodation', 'stay', 'resort', 'lodge'];
        foreach ($hotelKeywords as $keyword) {
            if (strpos($queryLower, $keyword) !== false) {
                return false; // Don't search tours if hotels specifically mentioned
            }
        }
        
        return true;
    }

    /**
     * Determine if we should search hotels
     */
    private function shouldSearchHotels($query, $filters) {
        if (isset($filters['item_focus']) && $filters['item_focus'] === 'tour') {
            return false;
        }
        
        $queryLower = strtolower($query);
        
        // Search hotels if hotel-related keywords found
        $hotelKeywords = ['hotel', 'accommodation', 'stay', 'resort', 'lodge', 'room'];
        foreach ($hotelKeywords as $keyword) {
            if (strpos($queryLower, $keyword) !== false) {
                return true;
            }
        }
        
        // Search hotels if rating mentioned (usually for hotels)
        if (isset($filters['rating']) || preg_match('/\d+\s*star/i', $query)) {
            return true;
        }
        
        return false;
    }

    /**
     * Calculate relevance score for SQL results
     */
    private function calculateRelevanceScore($query, $item, $type) {
        $score = 0.5;
        $queryLower = strtolower($query);
        
        $itemName = strtolower($type === 'tour' ? ($item['tour_name'] ?? '') : ($item['hotel'] ?? ''));
        
        if (strpos($itemName, $queryLower) !== false) {
            $score += 0.4;
        } else {
            $queryWords = preg_split('/\s+/', $queryLower);
            $matchedWords = count(array_filter($queryWords, fn($word) => 
                strlen($word) > 2 && strpos($itemName, $word) !== false
            ));
            $score += ($matchedWords / max(count($queryWords), 1)) * 0.3;
        }
        
        if (!empty($item['city_name']) && strpos($queryLower, strtolower($item['city_name'])) !== false) {
            $score += 0.2;
        }
        
        return min(1.0, $score);
    }

    /**
     * Normalize results scores to 0-1 range
     */
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

    /**
     * Unify results by item (merge same items from different channels)
     */
    private function unifyResultsByItem($semanticResults, $bm25Results, $sqlResults) {
        $unified = [];
        
        $resultSets = [
            'semantic' => $semanticResults,
            'bm25' => $bm25Results,
            'sql' => $sqlResults
        ];
        
        foreach ($resultSets as $channelType => $results) {
            foreach ($results as $result) {
                if (!isset($result['item']) || !isset($result['item_type'])) {
                    continue; // Skip invalid results
                }
                
                // Create unique key for item
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
                
                // Store normalized score
                $unified[$key]['scores'][$channelType] = $result['norm_score'] ?? 0;
                $unified[$key]['channels'][] = $channelType;
                
                // Store original score for analysis
                $originalScore = $result['similarity_score'] ?? $result['bm25_score'] ?? $result['sql_relevance'] ?? 0;
                $unified[$key]['raw_scores'][$channelType] = $originalScore;
            }
        }
        
        return $unified;
    }

    /**
     * Extract item ID from item data
     */
    private function extractItemId($item, $itemType) {
        if ($itemType === 'tour') {
            return $item['tourid'] ?? null;
        }
        if ($itemType === 'hotel') {
            return $item['hotelid'] ?? null;
        }
        return null;
    }

    /**
     * Calculate combined scores using weighted fusion
     */
    private function calculateCombinedScores($unifiedResults) {
        foreach ($unifiedResults as &$result) {
            $combinedScore = 0;
            
            // Calculate weighted score
            foreach (['semantic', 'bm25', 'sql'] as $channel) {
                $channelScore = $result['scores'][$channel];
                $weight = $this->weights[$channel];
                $combinedScore += $channelScore * $weight;
            }
            
            // Apply consensus bonus (items found by multiple channels get boosted)
            $channelCount = count($result['channels']);
            $consensusBonus = 0;
            if ($channelCount > 1) {
                $consensusBonus = 0.1 * ($channelCount - 1); // 10% bonus per additional channel
            }
            
            // Apply diversity penalty for very common items (optional)
            $diversityPenalty = 0;
            if ($channelCount >= 3 && $combinedScore > 0.8) {
                $diversityPenalty = 0.05; // Small penalty for very common items
            }
            
            $result['combined_score'] = max(0, min(1, $combinedScore + $consensusBonus - $diversityPenalty));
            $result['consensus_bonus'] = $consensusBonus;
            $result['channel_count'] = $channelCount;
        }
        
        // Sort by combined score
        uasort($unifiedResults, function($a, $b) {
            if ($a['combined_score'] == $b['combined_score']) {
                // Secondary sort by channel count (more channels = higher rank)
                return $b['channel_count'] <=> $a['channel_count'];
            }
            return $b['combined_score'] <=> $a['combined_score'];
        });
        
        return array_values($unifiedResults);
    }

    /**
     * Apply diversity filtering to final results
     */
    private function applyDiversityFiltering($rankedResults, $limit) {
        if (empty($rankedResults)) return [];
        
        $finalResults = [];
        $cityCount = [];
        $typeCount = ['tour' => 0, 'hotel' => 0];
        
        $maxPerCity = 6; // Maximum items per city
        $maxPerType = ceil($limit * 0.7); // Maximum 70% of results from one type
        
        foreach ($rankedResults as $result) {
            if (count($finalResults) >= $limit) break;
            
            $cityName = $result['item']['city_name'] ?? 'Unknown';
            $itemType = $result['item_type'];
            
            // Check city diversity constraint
            $currentCityCount = $cityCount[$cityName] ?? 0;
            if ($currentCityCount >= $maxPerCity) {
                continue; // Skip if too many from this city
            }
            
            // Check type diversity constraint
            if ($typeCount[$itemType] >= $maxPerType) {
                continue; // Skip if too many of this type
            }
            
            // Add to final results
            $finalResults[] = [
                'item' => $result['item'],
                'item_type' => $result['item_type'],
                'combined_score' => $result['combined_score'],
                'channels' => $result['channels'],
                'methods' => $result['channels'], // Alias for backward compatibility
                'scores' => $result['scores'],
                'raw_scores' => $result['raw_scores'] ?? []
            ];
            
            // Update counters
            $cityCount[$cityName] = $currentCityCount + 1;
            $typeCount[$itemType]++;
        }
        
        return $finalResults;
    }

    /**
     * Get matched terms for BM25 results
     */
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

    /**
     * Calculate overall confidence score
     */
    private function calculateOverallConfidence($semanticResults, $bm25Results, $sqlResults, $finalResults) {
        $baseConfidence = 0.5;
        
        // Boost confidence based on number of results from each channel
        $semanticBoost = min(0.2, count($semanticResults) * 0.02);
        $bm25Boost = min(0.15, count($bm25Results) * 0.015);
        $sqlBoost = min(0.15, count($sqlResults) * 0.015);
        
        // Boost confidence based on consensus (same items found by multiple channels)
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