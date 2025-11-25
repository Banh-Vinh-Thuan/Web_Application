<?php
require_once './Logger.php';
require_once './config.php';

class ResponseGenerator
{
    private $geminiService;
    private static $cityPatterns;
    private static $cityMappings;

    public function __construct($geminiService)
    {
        if (!$geminiService) {
            Logger::warning("GeminiService not provided, initializing new instance");
            $this->geminiService = new GeminiService();
        } else {
            $this->geminiService = $geminiService;
        }
        self::initializeCityPatterns();
    }

    /**
     * Generate hybrid response from retrieval results
     */
    public function generateHybridResponse($userMessage, $retrievalResult, $conversationHistory)
    {
        try {
            if (!$retrievalResult['success'] || empty($retrievalResult['results'])) {
                return $this->createFallbackResponse(
                    "I couldn't find specific information matching your request. Could you try rephrasing?",
                    'no_results'
                );
            }

            // CRITICAL FIX: Structure data first, then build context from it
            $displayData = $this->structureDisplayData($retrievalResult['results'], $userMessage);
            
            // Build context from the SAME structured data that will be displayed
            $context = $this->buildHybridContext($displayData);

            $aiResponse = $this->geminiService->generateVietnameseResponse(
                $userMessage,
                $context,
                $conversationHistory,
                [
                    'match_level' => 'hybrid_match',
                    'confidence' => $retrievalResult['confidence']
                ]
            );

            $responseType = $this->determineResponseType($displayData);
            $layoutType = $this->determineLayoutType($displayData, $userMessage);

            Logger::info("Hybrid response generated", [
                'response_type' => $responseType,
                'layout_type' => $layoutType,
                'confidence' => $retrievalResult['confidence'],
                'display_data_keys' => array_keys($displayData)
            ]);

            return [
                'text' => $aiResponse,
                'type' => $responseType,
                'layout_type' => $layoutType,
                'data' => $displayData,
                'match_level' => 'hybrid',
                'confidence' => $retrievalResult['confidence'],
                'suggestions' => $this->geminiService->generateSuggestions($userMessage, $aiResponse, ['data' => $displayData]),
                'retrieval_stats' => $retrievalResult['retrieval_stats'] ?? []
            ];

        } catch (Exception $e) {
            Logger::error("Hybrid response generation failed", ['error' => $e->getMessage()]);
            return $this->createFallbackResponse(
                "I'm having trouble processing your request. Please try rephrasing.",
                'generation_error'
            );
        }
    }

    /**
     * Generate international travel response
     * DIRECTLY REDIRECTS to GeminiService->generateInternationalPlan
     */
    public function generateInternationalResponse($userMessage, $entities, $conversationHistory)
    {
        $cityName = !empty($entities['cities']) ? $entities['cities'][0]['name'] : 'your destination';

        Logger::info("Generating international response", [
            'destination' => $cityName,
            'message_preview' => substr($userMessage, 0, 100)
        ]);

        // ✅ Let exceptions propagate - don't catch here
        $response = $this->geminiService->generateInternationalPlan(
            $userMessage,
            $cityName,
            $conversationHistory
        );

        // ✅ Validate response before returning
        if (empty(trim($response))) {
            Logger::warning("Empty response from international plan generation");
            $response = "I'd be happy to help plan your trip to {$cityName}! " .
                "Please provide more details about your travel duration, budget, and interests.";
        }

        return [
            'text' => $response,
            'type' => 'international_info',
            'layout_type' => 'default',
            'data' => [],
            'match_level' => 'international',
            'confidence' => 0.8,
            'suggestions' => [
                "Tell me more about {$cityName} attractions",
                "What's the best time to visit {$cityName}?",
                "Help me plan my budget for {$cityName}",
                "Show me Vietnam tours instead"
            ]
        ];
    }

    // ==================== DISPLAY DATA STRUCTURING ====================

    private function structureDisplayData($results, $userMessage): array {
        $tours = [];
        $hotels = [];

        foreach ($results as $result) {
            if (!isset($result['item']) || !isset($result['item_type'])) continue;

            if ($result['item_type'] === 'tour') {
                $tours[] = $result['item'];
            } elseif ($result['item_type'] === 'hotel') {
                $hotels[] = $result['item'];
            }
        }

        // Extract query information
        $cities = self::extractCitiesFromQuery($userMessage);
        $queryType = $this->analyzeQueryType($userMessage);
        $hasConditions = $this->detectConditions($userMessage);

        $displayData = [
            'multi_city' => false,
            'has_conditions' => $hasConditions
        ];

        Logger::debug("Query Analysis", [
            'cities' => $cities,
            'query_type' => $queryType,
            'has_conditions' => $hasConditions,
            'tours_count' => count($tours),
            'hotels_count' => count($hotels)
        ]);

        // CASE 5-6: Mixed query (tour in city1 AND hotel in city2)
        if ($queryType === 'mixed' && !empty($tours) && !empty($hotels)) {
            if (count($cities) >= 2) {
                $tourCity = $this->findItemCity($tours, $cities) ?: $cities[0];
                $hotelCity = $this->findItemCity($hotels, $cities, [$tourCity]) ?: $cities[1];
                
                $displayData['tours'] = $this->filterItemsByCity($tours, $tourCity, 3);
                $displayData['hotels'] = $this->filterItemsByCity($hotels, $hotelCity, 3);
                
                $displayData['tour_city'] = $tourCity;
                $displayData['hotel_city'] = $hotelCity;
                
                Logger::debug("Mixed query structured", [
                    'tour_city' => $tourCity,
                    'tours_count' => count($displayData['tours']),
                    'hotel_city' => $hotelCity,
                    'hotels_count' => count($displayData['hotels'])
                ]);
                
                return $displayData;
            }
        }

        // CASE 3 & 7: Multi-city tours
        if ($queryType === 'tour_only' && count($cities) >= 2 && !empty($tours)) {
            $city1 = $cities[0];
            $city2 = $cities[1];

            $city1Tours = $this->filterItemsByCity($tours, $city1, 3);
            $city2Tours = $this->filterItemsByCity($tours, $city2, 3);

            if (!empty($city1Tours) || !empty($city2Tours)) {
                $displayData['multi_city'] = true;
                $displayData['cities'] = [$city1, $city2];
                $displayData['city1_tours'] = $city1Tours;
                $displayData['city2_tours'] = $city2Tours;
                $displayData['tours'] = array_merge($city1Tours, $city2Tours);
                
                Logger::debug("Multi-city tours structured", [
                    'city1' => $city1,
                    'city1_count' => count($city1Tours),
                    'city2' => $city2,
                    'city2_count' => count($city2Tours)
                ]);
            } else {
                $displayData['tours'] = array_slice($tours, 0, 6);
            }
            return $displayData;
        }

        // CASE 4: Multi-city hotels
        if ($queryType === 'hotel_only' && count($cities) >= 2 && !empty($hotels)) {
            $city1 = $cities[0];
            $city2 = $cities[1];

            $city1Hotels = $this->filterItemsByCity($hotels, $city1, 3);
            $city2Hotels = $this->filterItemsByCity($hotels, $city2, 3);

            if (!empty($city1Hotels) || !empty($city2Hotels)) {
                $displayData['multi_city'] = true;
                $displayData['cities'] = [$city1, $city2];
                $displayData['city1_hotels'] = $city1Hotels;
                $displayData['city2_hotels'] = $city2Hotels;
                $displayData['hotels'] = array_merge($city1Hotels, $city2Hotels);

                Logger::debug("Multi-city hotels structured", [
                    'city1' => $city1,
                    'city1_count' => count($city1Hotels),
                    'city2' => $city2,
                    'city2_count' => count($city2Hotels)
                ]);
            } else {
                $displayData['hotels'] = array_slice($hotels, 0, 6);
            }
            return $displayData;
        }

        // CASE 1 & 8: Single city tours
        if (!empty($tours)) {
            if ($hasConditions) {
                $displayData['tours'] = $tours;
            } else {
                $displayData['tours'] = array_slice($tours, 0, 6);
            }
            return $displayData;
        }

        // CASE 2: Single city hotels  
        if (!empty($hotels)) {
            if ($hasConditions) {
                $displayData['hotels'] = $hotels;
            } else {
                $displayData['hotels'] = array_slice($hotels, 0, 6);
            }
            return $displayData;
        }

        return $displayData;
    }

    private function findItemCity($items, $cities, $excludeCities = [])
    {
        $availableCities = array_diff($cities, $excludeCities);

        foreach ($items as $item) {
            $itemCityName = $item['city_name'] ?? '';
            foreach ($availableCities as $queryCity) {
                if (self::cityMatches($itemCityName, $queryCity)) {
                    return $queryCity;
                }
            }
        }

        return reset($availableCities) ?: null;
    }

    private function filterItemsByCity($items, $targetCity, $limit) {
        $filtered = [];
        
        foreach ($items as $item) {
            $itemCity = strtolower(trim($item['city_name'] ?? ''));
            if (self::cityMatches($itemCity, $targetCity)) {
                $filtered[] = $item;
                if (count($filtered) >= $limit) break;
            }
        }
        
        return $filtered;
    }

    private function analyzeQueryType($userMessage): string
    {
        $messageLower = strtolower($userMessage);

        $tourPattern = '/\b(tour|tours|trip|trips|package|excursion)s?\b/i';
        $hotelPattern = '/\b(hotel|hotels|accommodation|stay|resort)s?\b/i';

        preg_match_all($tourPattern, $messageLower, $tourMatches, PREG_OFFSET_CAPTURE);
        preg_match_all($hotelPattern, $messageLower, $hotelMatches, PREG_OFFSET_CAPTURE);

        $hasTour = !empty($tourMatches[0]);
        $hasHotel = !empty($hotelMatches[0]);

        if ($hasTour && $hasHotel) {
            if (preg_match('/\b(tour|tours)\b.*\band\b.*\b(hotel|hotels)\b/i', $messageLower) ||
                preg_match('/\b(hotel|hotels)\b.*\band\b.*\b(tour|tours)\b/i', $messageLower)) {
                return 'mixed';
            }
        }

        if ($hasTour && !$hasHotel) {
            return 'tour_only';
        }

        if ($hasHotel && !$hasTour) {
            return 'hotel_only';
        }

        return 'unknown';
    }

    private function detectConditions($userMessage): bool
    {
        $messageLower = strtolower($userMessage);

        $hasPrice = preg_match('/\b(under|over|below|above)\s+\d+/i', $messageLower) ||
                    preg_match('/\d+\s+(million|millions)/i', $messageLower);

        $hasDuration = preg_match('/\b(for|with|of)\s+\d+\s+(day|days)/i', $messageLower) ||
                       preg_match('/\d+\s+(day|days)\s+(tour|trip)/i', $messageLower);

        $hasRating = preg_match('/\b(for|with)?\s*\d+\s+star/i', $messageLower) ||
                     preg_match('/\bstar\s+\d+/i', $messageLower);

        return $hasPrice || $hasDuration || $hasRating;
    }

    // ==================== LAYOUT DETERMINATION ====================

    private function determineLayoutType($displayData, $userMessage): string
    {
        $hasTours = !empty($displayData['tours']);
        $hasHotels = !empty($displayData['hotels']);
        $isMultiCity = $displayData['multi_city'] ?? false;

        if ($isMultiCity && !empty($displayData['city1_tours']) && !empty($displayData['city2_tours'])) {
            return 'multi_city_tours';
        }

        if ($isMultiCity && !empty($displayData['city1_hotels']) && !empty($displayData['city2_hotels'])) {
            return 'multi_city_hotels';
        }

        if ($hasTours && $hasHotels && !empty($displayData['tour_city']) && !empty($displayData['hotel_city'])) {
            return 'mixed_content';
        }

        if ($hasTours && !$hasHotels) return 'single_tours';
        if ($hasHotels && !$hasTours) return 'single_hotels';

        return 'default';
    }

    private function determineResponseType($displayData): string
    {
        $hasTours = !empty($displayData['tours']);
        $hasHotels = !empty($displayData['hotels']);

        if ($hasTours && $hasHotels) return 'mixed_content';
        if ($hasTours) return 'tour_search';
        if ($hasHotels) return 'hotel_search';

        return 'general';
    }

    // ==================== CONTEXT BUILDING ====================

    private function buildHybridContext($displayData): string
    {
        $context = "# Available Travel Information\n\n";

        $tours = $displayData['tours'] ?? [];
        $hotels = $displayData['hotels'] ?? [];

        if (!empty($tours)) {
            $context .= "## Available Tours:\n";

            if (!empty($displayData['city1_tours'])) {
                $context .= "\nTours in {$displayData['cities'][0]}:\n";
                foreach($displayData['city1_tours'] as $tour) {
                    $context .= sprintf(
                        "* **%s** - %d days - %s VND\n",
                        $tour['tour_name'] ?? 'Unknown Tour',
                        $tour['duration_days'] ?? 0,
                        number_format($tour['price_per_person'] ?? 0)
                    );
                }

                if (!empty($displayData['city2_tours'])) {
                    $context .= "\nTours in {$displayData['cities'][1]}:\n";
                    foreach($displayData['city2_tours'] as $tour) {
                        $context .= sprintf(
                            "* **%s** - %d days - %s VND\n",
                            $tour['tour_name'] ?? 'Unknown Tour',
                            $tour['duration_days'] ?? 0,
                            number_format($tour['price_per_person'] ?? 0)
                        );
                    }
                }
            } else {
                $cityName = $displayData['tour_city'] ?? ($tours[0]['city_name'] ?? 'Vietnam');
                $context .= "\nTours in {$cityName}:\n";
                foreach ($tours as $tour) {
                    $context .= sprintf(
                        "* **%s** - %d days - %s VND\n",
                        $tour['tour_name'] ?? 'Unknown Tour',
                        $tour['duration_days'] ?? 0,
                        number_format($tour['price_per_person'] ?? 0)
                    );
                }
            }
        }

        if (!empty($hotels)) {
            $context .= "\n## Available Hotels:\n";

            if (!empty($displayData['city1_hotels'])) {
                $context .= "\nHotels in {$displayData['cities'][0]}:\n";
                foreach($displayData['city1_hotels'] as $hotel) {
                    $context .= sprintf(
                        "* **%s** - Rating: %.1f/5 - %s VND/night\n",
                        $hotel['hotel'] ?? $hotel['hotel_name'] ?? 'Unknown Hotel',
                        $hotel['ratings'] ?? 0,
                        number_format($hotel['cost'] ?? 0)
                    );
                }

                if (!empty($displayData['city2_hotels'])) {
                    $context .= "\nHotels in {$displayData['cities'][1]}:\n";
                    foreach($displayData['city2_hotels'] as $hotel) {
                        $context .= sprintf(
                            "* **%s** - Rating: %.1f/5 - %s VND/night\n",
                            $hotel['hotel'] ?? $hotel['hotel_name'] ?? 'Unknown Hotel',
                            $hotel['ratings'] ?? 0,
                            number_format($hotel['cost'] ?? 0)
                        );
                    }
                }
            } else {
                $cityName = $displayData['hotel_city'] ?? ($hotels[0]['city_name'] ?? 'Vietnam');
                $context .= "\nHotels in {$cityName}:\n";
                foreach ($hotels as $hotel) {
                    $context .= sprintf(
                        "* **%s** - Rating: %.1f/5 - %s VND/night\n",
                        $hotel['hotel'] ?? $hotel['hotel_name'] ?? 'Unknown Hotel',
                        $hotel['ratings'] ?? 0,
                        number_format($hotel['cost'] ?? 0)
                    );
                }
            }
        }

        return $context;
    }

    // ==================== FALLBACK RESPONSES ====================

    private function createFallbackResponse($message, $errorType = 'general_error'): array
    {
        return [
            'text' => $message,
            'type' => 'fallback',
            'layout_type' => 'default',
            'data' => [],
            'match_level' => 'fallback',
            'confidence' => 0.2,
            'suggestions' => $this->getFallbackSuggestions($errorType)
        ];
    }

    private function getFallbackSuggestions($errorType): array
    {
        return match($errorType) {
            'no_results' => [
                'Show me popular tours in Vietnam',
                'Find hotels in Ho Chi Minh City',
                'Plan a 3-day trip to Da Lat',
                'What are the best destinations in Vietnam?'
            ],
            'generation_error' => [
                'Try asking about specific cities',
                'Ask for tour recommendations',
                'Look for hotel options',
                'Tell me your travel preferences'
            ],
            default => [
                'Show me tours and hotels',
                'Help me plan a trip',
                'Find budget travel options',
                'Tell me about destinations'
            ]
        };
    }

    // ==================== CENTRALIZED CITY MATCHING ====================

    private static function initializeCityPatterns(): void
    {
        if (self::$cityPatterns !== null) return;

        self::$cityPatterns = [
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

        self::$cityMappings = [
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

        uksort(self::$cityPatterns, fn($a, $b) => strlen($b) - strlen($a));
    }

    public static function extractCitiesFromQuery($query): array
    {
        self::initializeCityPatterns();

        $cities = [];
        $query = strtolower(trim($query));

        foreach (self::$cityPatterns as $pattern => $cityName) {
            if (preg_match('/\b' . preg_quote($pattern, '/') . '\b/', $query)) {
                if (!in_array($cityName, $cities)) {
                    $cities[] = $cityName;
                }
            }
        }

        if (preg_match('/(.+?)\s+and\s+(.+?)/', $query, $matches)) {
            foreach ([trim($matches[1]), trim($matches[2])] as $part) {
                foreach (self::$cityPatterns as $pattern => $cityName) {
                    if (strpos($part, $pattern) !== false && !in_array($cityName, $cities)) {
                        $cities[] = $cityName;
                    }
                }
            }
        }

        Logger::debug("Cities extracted from query", ['query' => $query, 'cities' => $cities]);
        return array_unique($cities);
    }

    public static function cityMatches($itemCity, $queryCity): bool
    {
        self::initializeCityPatterns();

        if (empty($itemCity) || empty($queryCity)) return false;

        $itemCity = strtolower(trim($itemCity));
        $queryCity = strtolower(trim($queryCity));

        if (strpos($itemCity, $queryCity) !== false || strpos($queryCity, $itemCity) !== false) {
            return true;
        }

        foreach (self::$cityMappings as $standard => $variations) {
            if (strpos($queryCity, $standard) !== false) {
                foreach ($variations as $variation) {
                    if (strpos($itemCity, $variation) !== false) return true;
                }
            }

            foreach ($variations as $variation) {
                if (strpos($queryCity, $variation) !== false && strpos($itemCity, $standard) !== false) {
                    return true;
                }
            }
        }

        $similarity = 0;
        similar_text($itemCity, $queryCity, $similarity);
        return $similarity >= 60;
    }
}
?>