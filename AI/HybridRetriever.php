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

public function __construct($dbService, $k1 = 1.2, $b = 0.75) {
    if (!$dbService) {
        throw new InvalidArgumentException("DatabaseService is required");
    }

    $this->dbService = $dbService;
    
    // Initialize GeminiService with proper error handling
    try {
        $this->geminiService = new GeminiService();
    } catch (Exception $e) {
        Logger::error("Failed to initialize GeminiService", ['error' => $e->getMessage()]);
        // Create a mock service for fallback
        $this->geminiService = new class {
            public function generateEmbedding($text) {
                Logger::warning("Using fallback embedding service");
                return null;
            }
        };
    }
    
    $this->k1 = $k1;
    $this->b = $b;
    $this->weights = Config::HYBRID_WEIGHTS;

    Logger::debug("HybridRetriever initialized successfully");
}

    /**
     * Main hybrid search method - executes all channels in parallel
     */
    public function hybridSearch($query, $entities, $intent) {
        try {
            Logger::info("Starting hybrid retrieval", ['query' => $query, 'intent' => $intent]);
            
            $filters = $this->prepareFilters($entities, $intent);
            
            // STEP 1: Execute all retrieval channels in parallel
            $semanticResults = $this->semanticSearch($query, $filters);
            $bm25Results = $this->bm25Search($query, $filters);
            $sqlResults = $this->sqlSearch($query, $filters);
            
            Logger::debug("Retrieval channels executed", [
                'semantic_count' => count($semanticResults),
                'bm25_count' => count($bm25Results),
                'sql_count' => count($sqlResults)
            ]);
            
            // STEP 2: Merge and rerank results
            $mergedResults = $this->mergeAndRerank($semanticResults, $bm25Results, $sqlResults);
            
            // STEP 3: Calculate overall confidence
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
            Logger::error("Hybrid search failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'results' => [], 'confidence' => 0];
        }
    }

    /**
     * CHANNEL 1: Semantic Search using Vector Embeddings
     */
    private function semanticSearch($query, $filters, $limit = 15) {
        try {
            Logger::debug("Starting semantic search", ['query' => $query]);
            
            $queryVector = $this->geminiService->generateEmbedding($query);
            if (!$queryVector) {
                Logger::warning("Failed to generate query embedding");
                return [];
            }
            
            $similarityResults = $this->dbService->findSimilarItemsByVector($queryVector, $limit * 2);
            if (empty($similarityResults)) {
                Logger::debug("No similar items found by vector");
                return [];
            }
            
            $enrichedResults = $this->enrichWithItemData($similarityResults, $filters, $limit);
            Logger::debug("Semantic search completed", ['results' => count($enrichedResults)]);
            
            return $enrichedResults;
            
        } catch (Exception $e) {
            Logger::error("Semantic search failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * CHANNEL 2: BM25 Keyword Search
     */
    private function bm25Search($query, $filters, $limit = 15) {
        try {
            Logger::debug("Starting BM25 search", ['query' => $query]);
            
            $queryTerms = $this->tokenizeQuery($query);
            if (empty($queryTerms)) {
                Logger::debug("No valid query terms for BM25");
                return [];
            }
            
            $documents = $this->getSearchableDocuments($filters);
            if (empty($documents)) {
                Logger::debug("No searchable documents found");
                return [];
            }
            
            $scores = $this->calculateBM25Scores($queryTerms, $documents);
            arsort($scores);
            
            $topDocIds = array_slice(array_keys($scores), 0, $limit);
            $results = [];
            
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
            
            Logger::debug("BM25 search completed", ['results' => count($results)]);
            return $results;
            
        } catch (Exception $e) {
            Logger::error("BM25 search failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * CHANNEL 3: SQL Structured Search
     */
    private function sqlSearch($query, $filters, $limit = 15) {
        try {
            Logger::debug("Starting SQL search", ['query' => $query]);
            
            $enhancedFilters = $this->analyzeQueryForFilters($query, $filters);
            
            $searchTours = $this->shouldSearchTours($query, $enhancedFilters);
            $searchHotels = $this->shouldSearchHotels($query, $enhancedFilters);
            
            $results = [];
            
            // FIXED: Handle multi-city queries with minimum guarantee per city
            if (isset($enhancedFilters['cityIds']) && count($enhancedFilters['cityIds']) >= 2) {
                $minPerCity = 3; // Minimum items per city
                
                if ($searchTours) {
                    foreach ($enhancedFilters['cityIds'] as $cityId) {
                        $cityFilter = $enhancedFilters;
                        $cityFilter['cityIds'] = [$cityId]; // Search one city at a time
                        
                        $cityTours = $this->dbService->searchItems('tour', $cityFilter, $minPerCity);
                        foreach ($cityTours as $tour) {
                            $results[] = [
                                'item' => $tour,
                                'sql_relevance' => $this->calculateRelevanceScore($query, $tour, 'tour'),
                                'item_type' => 'tour',
                                'city_priority' => true // Mark as city-prioritized
                            ];
                        }
                    }
                    
                    // If we don't have enough results, do a general search
                    if (count(array_filter($results, fn($r) => $r['item_type'] === 'tour')) < $minPerCity * 2) {
                        $generalTours = $this->dbService->searchItems('tour', $enhancedFilters, $limit);
                        foreach ($generalTours as $tour) {
                            // Avoid duplicates
                            $exists = false;
                            foreach ($results as $existing) {
                                if ($existing['item_type'] === 'tour' && 
                                    $existing['item']['tourid'] === $tour['tourid']) {
                                    $exists = true;
                                    break;
                                }
                            }
                            
                            if (!$exists) {
                                $results[] = [
                                    'item' => $tour,
                                    'sql_relevance' => $this->calculateRelevanceScore($query, $tour, 'tour'),
                                    'item_type' => 'tour',
                                    'city_priority' => false
                                ];
                            }
                        }
                    }
                }
                
                // Similar logic for hotels
                if ($searchHotels) {
                    foreach ($enhancedFilters['cityIds'] as $cityId) {
                        $cityFilter = $enhancedFilters;
                        $cityFilter['cityIds'] = [$cityId];
                        
                        $cityHotels = $this->dbService->searchItems('hotel', $cityFilter, $minPerCity);
                        foreach ($cityHotels as $hotel) {
                            $results[] = [
                                'item' => $hotel,
                                'sql_relevance' => $this->calculateRelevanceScore($query, $hotel, 'hotel'),
                                'item_type' => 'hotel',
                                'city_priority' => true
                            ];
                        }
                    }
                    
                    // Fallback general hotel search if needed
                    if (count(array_filter($results, fn($r) => $r['item_type'] === 'hotel')) < $minPerCity * 2) {
                        $generalHotels = $this->dbService->searchItems('hotel', $enhancedFilters, $limit);
                        foreach ($generalHotels as $hotel) {
                            $exists = false;
                            foreach ($results as $existing) {
                                if ($existing['item_type'] === 'hotel' && 
                                    $existing['item']['hotelid'] === $hotel['hotelid']) {
                                    $exists = true;
                                    break;
                                }
                            }
                            
                            if (!$exists) {
                                $results[] = [
                                    'item' => $hotel,
                                    'sql_relevance' => $this->calculateRelevanceScore($query, $hotel, 'hotel'),
                                    'item_type' => 'hotel',
                                    'city_priority' => false
                                ];
                            }
                        }
                    }
                }
            } else {
                // Single city or general search - original logic
                if ($searchTours) {
                    $tourResults = $this->dbService->searchItems('tour', $enhancedFilters);
                    foreach ($tourResults as $tour) {
                        $results[] = [
                            'item' => $tour,
                            'sql_relevance' => $this->calculateRelevanceScore($query, $tour, 'tour'),
                            'item_type' => 'tour'
                        ];
                    }
                }
                
                if ($searchHotels) {
                    $hotelResults = $this->dbService->searchItems('hotel', $enhancedFilters);
                    foreach ($hotelResults as $hotel) {
                        $results[] = [
                            'item' => $hotel,
                            'sql_relevance' => $this->calculateRelevanceScore($query, $hotel, 'hotel'),
                            'item_type' => 'hotel'
                        ];
                    }
                }
            }
            
            // Sort by relevance, prioritizing city-specific results
            usort($results, function($a, $b) {
                // First, prioritize city-specific results
                $aCityPriority = $a['city_priority'] ?? false;
                $bCityPriority = $b['city_priority'] ?? false;
                
                if ($aCityPriority && !$bCityPriority) return -1;
                if (!$aCityPriority && $bCityPriority) return 1;
                
                // Then sort by relevance score
                return $b['sql_relevance'] <=> $a['sql_relevance'];
            });
            
            $finalResults = array_slice($results, 0, $limit);
            Logger::debug("SQL search completed", ['results' => count($finalResults)]);
            
            return $finalResults;
            
        } catch (Exception $e) {
            Logger::error("SQL search failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function searchItems($itemType, $filters, $cityLimit = null) {
        try {
            $conditions = [];
            $params = [];
            
            // Build base query based on item type
            if ($itemType === 'tour') {
                $baseQuery = "SELECT t.*, c.city_name FROM tours t JOIN cities c ON t.cityid = c.id WHERE 1=1";
            } else if ($itemType === 'hotel') {
                $baseQuery = "SELECT h.*, c.city_name FROM hotels h JOIN cities c ON h.cityid = c.id WHERE 1=1";
            } else {
                throw new InvalidArgumentException("Invalid item type: $itemType");
            }
            
            // Apply city filters
            if (!empty($filters['cityIds'])) {
                if (count($filters['cityIds']) === 1) {
                    $conditions[] = "c.id = ?";
                    $params[] = $filters['cityIds'][0];
                } else {
                    $placeholders = str_repeat('?,', count($filters['cityIds']) - 1) . '?';
                    $conditions[] = "c.id IN ($placeholders)";
                    $params = array_merge($params, $filters['cityIds']);
                }
            }
            
            // Apply budget filters
            if (!empty($filters['budget'])) {
                $priceField = $itemType === 'tour' ? 'price_per_person' : 'cost';
                $condition = $filters['price_condition'] ?? 'under';
                
                if ($condition === 'under') {
                    $conditions[] = "$priceField <= ?";
                } else {
                    $conditions[] = "$priceField >= ?";
                }
                $params[] = $filters['budget'];
            }
            
            // Apply duration filter (tours only)
            if ($itemType === 'tour' && !empty($filters['duration'])) {
                $conditions[] = "duration_days = ?";
                $params[] = $filters['duration'];
            }
            
            // FIXED: Apply rating filter with exact/minimum/maximum conditions
            if ($itemType === 'hotel' && !empty($filters['rating'])) {
                $ratingCondition = $filters['rating_condition'] ?? 'exact'; // Default to exact
                
                switch ($ratingCondition) {
                    case 'exact':
                        // For exact match, we need to handle decimal ratings
                        // 4-star means rating between 4.0 and 4.9
                        $conditions[] = "ratings >= ? AND ratings < ?";
                        $params[] = floatval($filters['rating']);
                        $params[] = floatval($filters['rating']) + 1.0;
                        break;
                        
                    case 'minimum':
                        $conditions[] = "ratings >= ?";
                        $params[] = floatval($filters['rating']);
                        break;
                        
                    case 'maximum':
                        $conditions[] = "ratings <= ?";
                        $params[] = floatval($filters['rating']);
                        break;
                        
                    default:
                        // Fallback to exact
                        $conditions[] = "ratings >= ? AND ratings < ?";
                        $params[] = floatval($filters['rating']);
                        $params[] = floatval($filters['rating']) + 1.0;
                }
            }
            
            // Build final query
            $query = $baseQuery;
            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }
            
            // Add ordering
            if ($itemType === 'tour') {
                $query .= " ORDER BY price_per_person ASC, duration_days DESC";
            } else {
                // For hotels, when filtering by exact rating, sort by price
                if (!empty($filters['rating']) && ($filters['rating_condition'] ?? 'exact') === 'exact') {
                    $query .= " ORDER BY cost ASC, ratings DESC";
                } else {
                    $query .= " ORDER BY ratings DESC, cost ASC";
                }
            }
            
            // Apply limit
            $limit = $cityLimit ?? 20;
            $query .= " LIMIT $limit";
            
            Logger::debug("Executing search query with rating filter", [
                'query' => $query,
                'params' => $params,
                'itemType' => $itemType,
                'rating_condition' => $filters['rating_condition'] ?? 'not_set'
            ]);
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            Logger::debug("Search completed with rating filter", [
                'itemType' => $itemType,
                'resultCount' => count($results),
                'rating_filter' => $filters['rating'] ?? 'none',
                'rating_condition' => $filters['rating_condition'] ?? 'none'
            ]);
            
            return $results;
            
        } catch (Exception $e) {
            Logger::error("Database search failed", [
                'error' => $e->getMessage(),
                'itemType' => $itemType,
                'filters' => $filters
            ]);
            return [];
        }
    }

    /**
     * Merge and rerank results from all channels
     */
    private function mergeAndRerank($semanticResults, $bm25Results, $sqlResults, $limit = 10) {
        Logger::debug("Starting result merge and rerank");
        
        // Normalize scores from each channel
        $normalizedSemantic = $this->normalizeResults($semanticResults, 'similarity_score');
        $normalizedBM25 = $this->normalizeResults($bm25Results, 'bm25_score');
        $normalizedSQL = $this->normalizeResults($sqlResults, 'sql_relevance');
        
        // Unify results by item (same item from different channels)
        $unified = $this->unifyResultsByItem($normalizedSemantic, $normalizedBM25, $normalizedSQL);
        
        // Calculate combined scores
        $ranked = $this->calculateCombinedScores($unified);
        
        // Apply diversity filtering
        $finalResults = $this->applyDiversityFiltering($ranked, $limit);
        
        Logger::debug("Merge and rerank completed", ['final_count' => count($finalResults)]);
        return $finalResults;
    }

    // Prepare filters from extracted entities
    private function prepareFilters($entities, $intent) {
        $filters = [];
        
        // Handle multiple city IDs
        if (!empty($entities['cities'])) {
            $cityIds = [];
            foreach ($entities['cities'] as $city) {
                if (isset($city['id']) && $city['id'] !== null) {
                    $cityIds[] = $city['id'];
                }
            }
            if (!empty($cityIds)) {
                $filters['cityIds'] = array_unique($cityIds);
            }
        }
        
        // Budget and price conditions
        if (!empty($entities['budget'])) {
            $filters['budget'] = $entities['budget'];
            $filters['price_condition'] = $entities['price_condition'] ?? 'under';
        }
        
        // Duration filter (mainly for tours)
        if (!empty($entities['duration'])) {
            $filters['duration'] = $entities['duration'];
        }
        
        // Rating filter (mainly for hotels) - PRESERVE CONDITION
        if (!empty($entities['rating'])) {
            $filters['rating'] = $entities['rating'];
            $filters['rating_condition'] = $entities['rating_condition'] ?? 'exact'; // Default to exact
        }
        
        // Focus on specific item type based on intent
        switch ($intent) {
            case 'tour_search':
                $filters['item_focus'] = 'tour';
                break;
            case 'hotel_search':
                $filters['item_focus'] = 'hotel';
                break;
            case 'mixed_search':
                $filters['item_focus'] = 'both';
                break;
        }
        
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
        
        // Remove stopwords and short terms
        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with',
            'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does',
            'did', 'will', 'would', 'could', 'should', 'can', 'may', 'might', 'this', 'that',
            'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her',
            'us', 'them', 'my', 'your', 'his', 'her', 'its', 'our', 'their'
        ];
        
        $cleanTerms = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) > 2 && !in_array($word, $stopWords) && !is_numeric($word)) {
                $cleanTerms[] = $word;
            }
        }
        
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
            // Pattern for exact star rating (e.g., "4 star", "5 sao", "rating 4")
            if (preg_match('/\b(\d+)\s*(?:star|sao|rating)\b/i', $query, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'exact'; // NEW: Specify exact match
                }
            }
            // Pattern for "X star hotel" or "X-star hotel"
            elseif (preg_match('/\b(\d+)[-\s]*star\s+hotel/i', $query, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'exact';
                }
            }
            // Pattern for minimum rating (e.g., "at least 4 star", "minimum 4 star")
            elseif (preg_match('/(?:at least|minimum|min|above)\s*(\d+)\s*(?:star|sao)/i', $query, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'minimum';
                }
            }
            // Pattern for maximum rating (e.g., "under 4 star", "below 4 star")
            elseif (preg_match('/(?:under|below|max|maximum)\s*(\d+)\s*(?:star|sao)/i', $query, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    $filters['rating'] = $rating;
                    $filters['rating_condition'] = 'maximum';
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
        $score = 0.5; // Base score for SQL filtering match
        $queryLower = strtolower($query);
        
        // Score based on name matching
        if ($type === 'tour') {
            $itemName = strtolower($item['tour_name'] ?? '');
            if (strpos($itemName, $queryLower) !== false) {
                $score += 0.4; // Exact substring match
            } else {
                // Check for individual word matches
                $queryWords = preg_split('/\s+/', $queryLower);
                $matchedWords = 0;
                foreach ($queryWords as $word) {
                    if (strlen($word) > 2 && strpos($itemName, $word) !== false) {
                        $matchedWords++;
                    }
                }
                $score += ($matchedWords / count($queryWords)) * 0.3;
            }
        } elseif ($type === 'hotel') {
            $itemName = strtolower($item['hotel'] ?? $item['hotel_name'] ?? '');
            if (strpos($itemName, $queryLower) !== false) {
                $score += 0.4;
            } else {
                $queryWords = preg_split('/\s+/', $queryLower);
                $matchedWords = 0;
                foreach ($queryWords as $word) {
                    if (strlen($word) > 2 && strpos($itemName, $word) !== false) {
                        $matchedWords++;
                    }
                }
                $score += ($matchedWords / count($queryWords)) * 0.3;
            }
        }
        
        // Score based on city matching
        $cityName = strtolower($item['city_name'] ?? '');
        if (!empty($cityName) && strpos($queryLower, $cityName) !== false) {
            $score += 0.2;
        }
        
        // Score based on description matching (if available)
        $description = strtolower($item['description'] ?? '');
        if (!empty($description)) {
            $queryWords = preg_split('/\s+/', $queryLower);
            $descMatches = 0;
            foreach ($queryWords as $word) {
                if (strlen($word) > 3 && strpos($description, $word) !== false) {
                    $descMatches++;
                }
            }
            if ($descMatches > 0) {
                $score += min(0.2, $descMatches * 0.05);
            }
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