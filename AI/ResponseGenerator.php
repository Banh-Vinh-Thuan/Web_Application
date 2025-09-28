<?php
require_once './Logger.php';
require_once './config.php';

class ResponseGenerator {
    private $geminiService;

    public function __construct($geminiService) {
        if (!$geminiService) {
            Logger::warning("GeminiService not provided, initializing new instance");
            try {
                $this->geminiService = new GeminiService();
            } catch (Exception $e) {
                Logger::error("Failed to initialize GeminiService", ['error' => $e->getMessage()]);
                throw new InvalidArgumentException("GeminiService initialization failed: " . $e->getMessage());
            }
        } else {
            $this->geminiService = $geminiService;
        }
    }

    public function generateHybridResponse($userMessage, $retrievalResult, $conversationHistory) {
        try {
            if (!$retrievalResult['success'] || empty($retrievalResult['results'])) {
                return $this->createFallbackResponse(
                    "I couldn't find specific information matching your request. Could you try rephrasing or being more specific?",
                    'no_results'
                );
            }

            // Build rich context from retrieved results
            $context = $this->buildHybridContext($retrievalResult['results']);
            
            Logger::debug("Context built for generation", [
                'context_length' => strlen($context),
                'result_count' => count($retrievalResult['results'])
            ]);

            // Generate AI response with context
            $aiResponse = $this->geminiService->generateVietnameseResponse(
                $userMessage,
                $context,
                $conversationHistory,
                [
                    'match_level' => 'hybrid_match', 
                    'confidence' => $retrievalResult['confidence']
                ]
            );

            // Extract and structure display data with enhanced multi-city detection
            $displayData = $this->structureDisplayDataWithMultiCity($retrievalResult['results'], $userMessage);

            // Determine response type and layout
            $responseType = $this->determineResponseType($displayData);
            $layoutType = $this->determineLayoutType($displayData, $userMessage);

            // Generate contextual suggestions
            $suggestions = $this->geminiService->generateSuggestions(
                $userMessage,
                $aiResponse,
                ['data' => $displayData, 'type' => $responseType]
            );

            Logger::info("Hybrid response generated successfully", [
                'response_type' => $responseType,
                'layout_type' => $layoutType,
                'confidence' => $retrievalResult['confidence'],
                'display_items' => count($displayData['tours'] ?? []) + count($displayData['hotels'] ?? [])
            ]);

            return [
                'text' => $aiResponse,
                'type' => $responseType,
                'layout_type' => $layoutType,
                'data' => $displayData,
                'match_level' => 'hybrid',
                'confidence' => $retrievalResult['confidence'],
                'suggestions' => $suggestions,
                'retrieval_stats' => $retrievalResult['retrieval_stats'] ?? []
            ];

        } catch (Exception $e) {
            Logger::error("Hybrid response generation failed", [
                'error' => $e->getMessage(),
                'user_message' => substr($userMessage, 0, 100)
            ]);
            
            return $this->createFallbackResponse(
                "I'm having trouble processing your request right now. Please try rephrasing or ask something simpler.",
                'generation_error'
            );
        }
    }

    public function generateInternationalResponse($userMessage, $entities, $conversationHistory) {
        $cityName = !empty($entities['cities']) ? $entities['cities'][0]['name'] : 'your destination';

        $response = $this->geminiService->generateInternationalPlan(
            $userMessage,
            $cityName,
            $conversationHistory
        );

        return [
            'text' => $response,
            'type' => 'international_info',
            'layout_type' => 'default',
            'data' => [],
            'match_level' => 'international',
            'confidence' => 0.8,
            'suggestions' => [
                "Tell me more about $cityName attractions",
                "What's the best time to visit $cityName?",
                "Help me plan my budget for $cityName",
                "What should I know about $cityName culture?"
            ]
        ];
    }

    // FIXED: Enhanced multi-city structure with proper error handling and city grouping
    private function structureDisplayDataWithMultiCity($results, $userMessage) {
        $displayData = [];
        $tours = [];
        $hotels = [];

        try {
            // Separate by item type first with error checking
            foreach ($results as $result) {
                if (!isset($result['item']) || !isset($result['item_type'])) {
                    Logger::warning("Invalid result structure", ['result' => $result]);
                    continue;
                }
                
                $item = $result['item'];
                if ($result['item_type'] === 'tour') {
                    $tours[] = $item;
                } elseif ($result['item_type'] === 'hotel') {
                    $hotels[] = $item;
                }
            }

            // Enhanced city detection from query with better error handling
            $cities = $this->extractCitiesFromQuery($userMessage);
            $isMultiCity = count($cities) >= 2;

            Logger::debug("Multi-city analysis", [
                'detected_cities' => $cities,
                'is_multi_city' => $isMultiCity,
                'tour_count' => count($tours),
                'hotel_count' => count($hotels)
            ]);

            // FIXED: Better validation for multi-city display
            if ($isMultiCity && !empty($tours)) {
                // Group tours by city with balanced distribution
                $groupedTours = $this->groupItemsByCityWithMinimum($tours, $cities, 6);
                
                // Validate that we have a reasonable distribution
                $city1Count = count(array_filter($groupedTours, function($item) use ($cities) {
                    return ($item['city_group'] ?? '') === ($cities[0] ?? '');
                }));
                $city2Count = count(array_filter($groupedTours, function($item) use ($cities) {
                    return ($item['city_group'] ?? '') === ($cities[1] ?? '');
                }));
                
                // Only use multi-city layout if both cities have at least 1 tour
                if ($city1Count > 0 && $city2Count > 0 && count($groupedTours) >= 2) {
                    $displayData['tours'] = $groupedTours;
                    $displayData['multi_city'] = true;
                    $displayData['cities'] = array_slice($cities, 0, 2);
                    
                    Logger::debug("Multi-city layout approved", [
                        'city1_tours' => $city1Count,
                        'city2_tours' => $city2Count,
                        'total_tours' => count($groupedTours)
                    ]);
                } else {
                    // Fallback to single layout if distribution is too uneven
                    $displayData['tours'] = array_slice($tours, 0, 6);
                    $displayData['multi_city'] = false;
                    
                    Logger::debug("Multi-city layout rejected, using single layout", [
                        'city1_tours' => $city1Count,
                        'city2_tours' => $city2Count,
                        'reason' => 'uneven_distribution'
                    ]);
                }
            } else if ($isMultiCity && !empty($hotels)) {
                // Similar logic for hotels
                $groupedHotels = $this->groupItemsByCityWithMinimum($hotels, $cities, 6);
                
                $city1Count = count(array_filter($groupedHotels, function($item) use ($cities) {
                    return ($item['city_group'] ?? '') === ($cities[0] ?? '');
                }));
                $city2Count = count(array_filter($groupedHotels, function($item) use ($cities) {
                    return ($item['city_group'] ?? '') === ($cities[1] ?? '');
                }));
                
                if ($city1Count > 0 && $city2Count > 0 && count($groupedHotels) >= 2) {
                    $displayData['hotels'] = $groupedHotels;
                    $displayData['multi_city'] = true;
                    $displayData['cities'] = array_slice($cities, 0, 2);
                } else {
                    $displayData['hotels'] = array_slice($hotels, 0, 6);
                    $displayData['multi_city'] = false;
                }
            } else {
                // Regular single-section layout
                if (!empty($tours)) {
                    $displayData['tours'] = array_slice($tours, 0, 6);
                }
                if (!empty($hotels)) {
                    $displayData['hotels'] = array_slice($hotels, 0, 6);
                }
                $displayData['multi_city'] = false;
            }

            return $displayData;

        } catch (Exception $e) {
            Logger::error("Error in structureDisplayDataWithMultiCity", [
                'error' => $e->getMessage(),
                'message' => $userMessage
            ]);
            
            // Fallback: return simple structure
            return [
                'tours' => array_slice($tours, 0, 6),
                'hotels' => array_slice($hotels, 0, 6),
                'multi_city' => false
            ];
        }
    }

    private function groupItemsByCityWithMinimum($items, $cities, $maxItems = 6) {
        $city1Items = [];
        $city2Items = [];
        $otherItems = [];
        $targetPerCity = 3; // Target items per city

        try {
            // First pass: Group items by city
            foreach ($items as $item) {
                if (!is_array($item)) {
                    Logger::warning("Invalid item structure in groupItemsByCity", ['item' => $item]);
                    continue;
                }

                $itemCity = strtolower(trim($item['city_name'] ?? $item['city'] ?? ''));
                $grouped = false;
                
                // Try to match with first city
                if (!empty($cities[0]) && $this->cityMatches($itemCity, $cities[0])) {
                    $item['city_group'] = $cities[0];
                    $city1Items[] = $item;
                    $grouped = true;
                }
                // Try to match with second city  
                elseif (!empty($cities[1]) && $this->cityMatches($itemCity, $cities[1])) {
                    $item['city_group'] = $cities[1];
                    $city2Items[] = $item;
                    $grouped = true;
                }
                
                // If no match, add to other items
                if (!$grouped) {
                    $otherItems[] = $item;
                }
            }

            Logger::debug("Items grouped by city before balancing", [
                'city1' => $cities[0] ?? 'Unknown',
                'city1_count' => count($city1Items),
                'city2' => $cities[1] ?? 'Unknown', 
                'city2_count' => count($city2Items),
                'other_count' => count($otherItems)
            ]);

            // FIXED: Enforced balanced distribution logic
            $result = [];
            
            // Calculate how many items each city should get
            $city1Available = count($city1Items);
            $city2Available = count($city2Items);
            $totalAvailable = $city1Available + $city2Available;
            
            // If we have very few items total, show what we have
            if ($totalAvailable < 4) {
                // Just return what we have, grouped by city
                $result = array_merge(
                    array_slice($city1Items, 0, $city1Available),
                    array_slice($city2Items, 0, $city2Available)
                );
                
                Logger::debug("Insufficient items for balanced distribution", [
                    'total_available' => $totalAvailable,
                    'returning' => count($result)
                ]);
                
                return $result;
            }
            
            // FIXED: Enforce balanced 3-3 distribution when possible
            if ($city1Available >= $targetPerCity && $city2Available >= $targetPerCity) {
                // Both cities have enough tours - take exactly 3 from each
                $result = array_merge(
                    array_slice($city1Items, 0, $targetPerCity),
                    array_slice($city2Items, 0, $targetPerCity)
                );
            }
            elseif ($city1Available >= $targetPerCity && $city2Available < $targetPerCity) {
                // City1 has enough, city2 is limited
                // Take all from city2, balance remaining with city1
                $city2Count = $city2Available;
                $city1Count = min($maxItems - $city2Count, $targetPerCity);
                
                $result = array_merge(
                    array_slice($city1Items, 0, $city1Count),
                    array_slice($city2Items, 0, $city2Count)
                );
            }
            elseif ($city2Available >= $targetPerCity && $city1Available < $targetPerCity) {
                // City2 has enough, city1 is limited  
                // Take all from city1, balance remaining with city2
                $city1Count = $city1Available;
                $city2Count = min($maxItems - $city1Count, $targetPerCity);
                
                $result = array_merge(
                    array_slice($city1Items, 0, $city1Count),
                    array_slice($city2Items, 0, $city2Count)
                );
            }
            else {
                // Both cities are limited - take what we can get
                $result = array_merge(
                    array_slice($city1Items, 0, $city1Available),
                    array_slice($city2Items, 0, $city2Available)
                );
            }
            
            // FIXED: If we still don't have enough items, try to use "other" items
            $currentCount = count($result);
            if ($currentCount < $maxItems && !empty($otherItems)) {
                $needed = $maxItems - $currentCount;
                $additional = array_slice($otherItems, 0, $needed);
                
                // Assign these items to the city with fewer items
                $city1InResult = count(array_filter($result, function($item) use ($cities) {
                    return ($item['city_group'] ?? '') === ($cities[0] ?? '');
                }));
                $city2InResult = count(array_filter($result, function($item) use ($cities) {
                    return ($item['city_group'] ?? '') === ($cities[1] ?? '');
                }));
                
                $assignToCity = $city1InResult <= $city2InResult ? $cities[0] : $cities[1];
                
                foreach ($additional as $item) {
                    $item['city_group'] = $assignToCity;
                    $result[] = $item;
                }
            }

            Logger::debug("Final balanced result", [
                'total_items' => count($result),
                'city1_final' => count(array_filter($result, function($item) use ($cities) {
                    return ($item['city_group'] ?? '') === ($cities[0] ?? '');
                })),
                'city2_final' => count(array_filter($result, function($item) use ($cities) {
                    return ($item['city_group'] ?? '') === ($cities[1] ?? '');
                })),
                'target_per_city' => $targetPerCity
            ]);

            return $result;

        } catch (Exception $e) {
            Logger::error("Error in groupItemsByCityWithMinimum", [
                'error' => $e->getMessage(),
                'cities' => $cities
            ]);
            
            // Fallback: return first N items
            return array_slice($items, 0, min($maxItems, count($items)));
        }
    }

    // FIXED: Enhanced city extraction with better Vietnamese city support
    private function extractCitiesFromQuery($query) {
        $cities = [];
        $query = strtolower(trim($query));

        try {
            // Comprehensive Vietnamese cities with aliases - FIXED ordering and patterns
            $cityPatterns = [
                // Major cities with multiple variations - longer patterns first
                'ho chi minh city' => 'Ho Chi Minh City',
                'ho chi minh' => 'Ho Chi Minh City', 
                'sài gòn' => 'Ho Chi Minh City',
                'saigon' => 'Ho Chi Minh City',
                'hcmc' => 'Ho Chi Minh City',
                'hcm' => 'Ho Chi Minh City',
                
                'hà nội' => 'Hanoi',
                'ha noi' => 'Hanoi',
                'hanoi' => 'Hanoi',
                
                'đà nẵng' => 'Da Nang',
                'da nang' => 'Da Nang',
                'danang' => 'Da Nang',
                
                'nha trang' => 'Nha Trang',
                'nhatrang' => 'Nha Trang',
                
                'hội an' => 'Hoi An',
                'hoi an' => 'Hoi An',
                'hoian' => 'Hoi An',
                
                'huế' => 'Hue',
                'hue' => 'Hue',
                
                'đà lạt' => 'Da Lat',
                'da lat' => 'Da Lat',
                'dalat' => 'Da Lat',
                
                'phú quốc' => 'Phu Quoc',
                'phu quoc' => 'Phu Quoc',
                'phuquoc' => 'Phu Quoc',
                
                'cần thơ' => 'Can Tho',
                'can tho' => 'Can Tho',
                'cantho' => 'Can Tho',
                
                'hà giang' => 'Ha Giang',
                'ha giang' => 'Ha Giang',
                'hagiang' => 'Ha Giang',
                
                'phú yên' => 'Phu Yen',
                'phu yen' => 'Phu Yen',
                'phuyen' => 'Phu Yen',
                
                'tây bắc' => 'Tay Bac',
                'tay bac' => 'Tay Bac',
                'taybac' => 'Tay Bac',
                'northwest' => 'Tay Bac'
            ];

            // Sort by length (longest first for better matching)
            uksort($cityPatterns, function($a, $b) {
                return strlen($b) - strlen($a);
            });

            // FIXED: Better pattern matching with word boundaries
            foreach ($cityPatterns as $pattern => $cityName) {
                // Use word boundary matching for better accuracy
                if (preg_match('/\b' . preg_quote($pattern, '/') . '\b/', $query)) {
                    if (!in_array($cityName, $cities)) {
                        $cities[] = $cityName;
                    }
                }
            }

            // FIXED: Also check for "and" patterns specifically
            if (preg_match('/(.+?)\s+and\s+(.+?)/', $query, $matches)) {
                $city1 = trim($matches[1]);
                $city2 = trim($matches[2]);
                
                foreach ($cityPatterns as $pattern => $cityName) {
                    if (strpos($city1, $pattern) !== false && !in_array($cityName, $cities)) {
                        $cities[] = $cityName;
                    }
                    if (strpos($city2, $pattern) !== false && !in_array($cityName, $cities)) {
                        $cities[] = $cityName;
                    }
                }
            }

            Logger::debug("Cities extracted from query", [
                'query' => $query,
                'found_cities' => $cities
            ]);

        } catch (Exception $e) {
            Logger::error("Error extracting cities from query", [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
        }

        return array_unique($cities);
    }
    /**
     * FIXED: Enhanced city grouping with better error handling and distribution
     */
    private function groupItemsByCity($items, $cities, $maxItems = 12) {
        $city1Items = [];
        $city2Items = [];
        $otherItems = [];

        try {
            // Group items by city with fuzzy matching - IMPROVED
            foreach ($items as $item) {
                if (!is_array($item)) {
                    Logger::warning("Invalid item structure in groupItemsByCity", ['item' => $item]);
                    continue;
                }

                $itemCity = strtolower(trim($item['city_name'] ?? $item['city'] ?? ''));
                $grouped = false;
                
                // Try to match with first city
                if (!empty($cities[0]) && $this->cityMatches($itemCity, $cities[0])) {
                    $item['city_group'] = $cities[0];
                    $city1Items[] = $item;
                    $grouped = true;
                }
                // Try to match with second city  
                elseif (!empty($cities[1]) && $this->cityMatches($itemCity, $cities[1])) {
                    $item['city_group'] = $cities[1];
                    $city2Items[] = $item;
                    $grouped = true;
                }
                
                // If no match, add to other items
                if (!$grouped) {
                    $otherItems[] = $item;
                }
            }

            Logger::debug("Items grouped by city", [
                'city1' => $cities[0] ?? 'Unknown',
                'city1_count' => count($city1Items),
                'city2' => $cities[1] ?? 'Unknown', 
                'city2_count' => count($city2Items),
                'other_count' => count($otherItems)
            ]);

            // FIXED: Smart distribution algorithm with better balancing
            $totalItems = min($maxItems, count($city1Items) + count($city2Items) + count($otherItems));
            
            if ($totalItems === 0) {
                return [];
            }

            if (count($city1Items) + count($city2Items) >= $totalItems) {
                // We have enough city-specific items
                $leftCount = ceil($totalItems / 2);
                $rightCount = $totalItems - $leftCount;
                
                // Ensure the city with more items gets fair representation
                if (count($city2Items) > count($city1Items)) {
                    // Swap if city2 has more items
                    list($city1Items, $city2Items) = [$city2Items, $city1Items];
                    list($cities[0], $cities[1]) = [$cities[1], $cities[0]];
                }
                
                $result = array_merge(
                    array_slice($city1Items, 0, min($leftCount, count($city1Items))),
                    array_slice($city2Items, 0, min($rightCount, count($city2Items)))
                );
            } else {
                // Mix city-specific and other items
                $result = array_merge(
                    array_slice($city1Items, 0, min(6, count($city1Items))),
                    array_slice($city2Items, 0, min(6, count($city2Items))),
                    array_slice($otherItems, 0, max(0, $totalItems - count($city1Items) - count($city2Items)))
                );
                $result = array_slice($result, 0, $totalItems);
            }

            return $result;

        } catch (Exception $e) {
            Logger::error("Error in groupItemsByCity", [
                'error' => $e->getMessage(),
                'cities' => $cities
            ]);
            
            // Fallback: return first N items
            return array_slice($items, 0, min($maxItems, count($items)));
        }
    }

    private function cityMatches($itemCity, $queryCity) {
        try {
            if (empty($itemCity) || empty($queryCity)) {
                return false;
            }

            $itemCity = strtolower(trim($itemCity));
            $queryCity = strtolower(trim($queryCity));

            // Direct substring match
            if (strpos($itemCity, $queryCity) !== false || strpos($queryCity, $itemCity) !== false) {
                return true;
            }

            // FIXED: Better city name mappings with comprehensive aliases
            $cityMappings = [
                'ho chi minh' => ['saigon', 'hcm', 'ho chi minh city', 'sài gòn', 'hcmc'],
                'hanoi' => ['ha noi', 'hà nội'],
                'da nang' => ['danang', 'đà nẵng'],
                'hoi an' => ['hoian', 'hội an'], 
                'hue' => ['huế'],
                'da lat' => ['dalat', 'đà lạt'],
                'phu quoc' => ['phuquoc', 'phú quốc'],
                'can tho' => ['cantho', 'cần thơ'],
                'ha giang' => ['hagiang', 'hà giang'],
                'phu yen' => ['phuyen', 'phú yên'],
                'tay bac' => ['taybac', 'tây bắc', 'northwest'],
                'nha trang' => ['nhatrang']
            ];

            foreach ($cityMappings as $standard => $variations) {
                if (strpos($queryCity, $standard) !== false) {
                    foreach ($variations as $variation) {
                        if (strpos($itemCity, $variation) !== false) {
                            return true;
                        }
                    }
                }
                
                // Check reverse mapping
                foreach ($variations as $variation) {
                    if (strpos($queryCity, $variation) !== false && strpos($itemCity, $standard) !== false) {
                        return true;
                    }
                }
            }

            // FIXED: More lenient fuzzy matching for close names
            $similarity = 0;
            similar_text($itemCity, $queryCity, $similarity);
            return $similarity >= 60; // Reduced threshold for better matching

        } catch (Exception $e) {
            Logger::error("Error in cityMatches", [
                'error' => $e->getMessage(),
                'itemCity' => $itemCity,
                'queryCity' => $queryCity
            ]);
            return false;
        }
    }

    private function determineLayoutType($displayData, $userMessage) {
        try {
            if ($displayData['multi_city'] ?? false) {
                $hasTours = !empty($displayData['tours']);
                $hasHotels = !empty($displayData['hotels']);

                if ($hasTours) {
                    return 'multi_city_tours';
                } elseif ($hasHotels) {
                    return 'multi_city_hotels';
                }
            }

            // Check for cross-city mixed content (tours from city1 + hotels from city2)
            $hasTours = !empty($displayData['tours']);
            $hasHotels = !empty($displayData['hotels']);
            
            if ($hasTours && $hasHotels) {
                // FIXED: Better handling of mixed content detection
                if (isset($displayData['cross_city_mixed']) && $displayData['cross_city_mixed']) {
                    return 'cross_city_mixed';
                }
                
                return 'mixed_content';
            } elseif ($hasTours) {
                return 'single_tours';
            } elseif ($hasHotels) {
                return 'single_hotels';
            }

            return 'default';

        } catch (Exception $e) {
            Logger::error("Error determining layout type", [
                'error' => $e->getMessage(),
                'displayData' => $displayData
            ]);
            return 'default';
        }
    }

    // Build comprehensive context for AI generation
    private function buildHybridContext($results) {
        try {
            $context = "# Available Travel Information\n\n";
            $tours = [];
            $hotels = [];

            foreach ($results as $result) {
                if (!isset($result['item_type']) || !isset($result['item'])) {
                    continue;
                }

                if ($result['item_type'] === 'tour') {
                    $tours[] = $result;
                } elseif ($result['item_type'] === 'hotel') {
                    $hotels[] = $result;
                }
            }

            if (!empty($tours)) {
                $context .= "## Available Tours:\n";
                foreach ($tours as $tourResult) {
                    $tour = $tourResult['item'];
                    $score = $tourResult['combined_score'] ?? 0;
                    $channels = implode(', ', $tourResult['channels'] ?? ['unknown']);
                    
                    $context .= sprintf(
                        "- **%s** (City: %s)\n  Duration: %d days | Price: %s VND\n  Found via: %s (Score: %.2f)\n\n",
                        $tour['tour_name'] ?? 'Unknown Tour',
                        $tour['city_name'] ?? $tour['city'] ?? 'Unknown City',
                        $tour['duration_days'] ?? 0,
                        number_format($tour['price_per_person'] ?? 0),
                        $channels,
                        $score
                    );
                }
            }

            if (!empty($hotels)) {
                $context .= "## Available Hotels:\n";
                foreach ($hotels as $hotelResult) {
                    $hotel = $hotelResult['item'];
                    $score = $hotelResult['combined_score'] ?? 0;
                    $channels = implode(', ', $hotelResult['channels'] ?? ['unknown']);
                    
                    $context .= sprintf(
                        "- **%s** (City: %s)\n  Rating: %.1f/5 | Price: %s VND/night\n  Found via: %s (Score: %.2f)\n\n",
                        $hotel['hotel'] ?? $hotel['hotel_name'] ?? 'Unknown Hotel',
                        $hotel['city_name'] ?? $hotel['city'] ?? 'Unknown City',
                        $hotel['ratings'] ?? 0,
                        number_format($hotel['cost'] ?? 0),
                        $channels,
                        $score
                    );
                }
            }

            return $context;

        } catch (Exception $e) {
            Logger::error("Error building hybrid context", [
                'error' => $e->getMessage()
            ]);
            return "# Available Travel Information\n\nNo information available.";
        }
    }

    /**
     * Determine response type based on content
     */
    private function determineResponseType($displayData) {
        try {
            $hasTours = !empty($displayData['tours']);
            $hasHotels = !empty($displayData['hotels']);

            if ($hasTours && $hasHotels) {
                return 'mixed_content';
            } elseif ($hasTours) {
                return 'tour_search';
            } elseif ($hasHotels) {
                return 'hotel_search';
            } else {
                return 'general';
            }
        } catch (Exception $e) {
            Logger::error("Error determining response type", [
                'error' => $e->getMessage()
            ]);
            return 'general';
        }
    }

    /**
     * Create fallback response for errors
     */
    private function createFallbackResponse($message, $errorType = 'general_error') {
        $suggestions = $this->getFallbackSuggestions($errorType);

        return [
            'text' => $message,
            'type' => 'fallback',
            'layout_type' => 'default',
            'data' => [],
            'match_level' => 'fallback',
            'confidence' => 0.2,
            'suggestions' => $suggestions
        ];
    }

    /**
     * Get appropriate fallback suggestions
     */
    private function getFallbackSuggestions($errorType) {
        switch ($errorType) {
            case 'no_results':
                return [
                    'Show me popular tours in Vietnam',
                    'Find hotels in Ho Chi Minh City', 
                    'Plan a 3-day trip to Da Lat',
                    'What are the best destinations in Vietnam?'
                ];
            case 'generation_error':
                return [
                    'Try asking about specific cities',
                    'Ask for tour recommendations',
                    'Look for hotel options',
                    'Tell me your travel preferences'
                ];
            default:
                return [
                    'Show me tours and hotels',
                    'Help me plan a trip',
                    'Find budget travel options',
                    'Tell me about destinations'
                ];
        }
    }
}
?>