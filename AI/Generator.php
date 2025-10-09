<?php

require_once './Logger.php';
require_once './config.php';

/**
 * Response Generator with Centralized City Matching
 * FIXED: Multi-city display, response text, and card counts
 */
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

            $context = $this->buildHybridContext($retrievalResult['results']);
            
            $aiResponse = $this->geminiService->generateVietnameseResponse(
                $userMessage,
                $context,
                $conversationHistory,
                [
                    'match_level' => 'hybrid_match',
                    'confidence' => $retrievalResult['confidence']
                ]
            );

            $displayData = $this->structureDisplayData($retrievalResult['results'], $userMessage);
            $responseType = $this->determineResponseType($displayData);
            $layoutType = $this->determineLayoutType($displayData, $userMessage);

            Logger::info("Hybrid response generated", [
                'response_type' => $responseType,
                'layout_type' => $layoutType,
                'confidence' => $retrievalResult['confidence']
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
     */
    public function generateInternationalResponse($userMessage, $entities, $conversationHistory)
    {
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
                "Help me plan my budget for $cityName"
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
            // For mixed queries, group by city
            if (count($cities) >= 2) {
                $tourCity = $this->findItemCity($tours, $cities);
                $hotelCity = $this->findItemCity($hotels, $cities);
                
                $displayData['tours'] = $this->filterItemsByCity($tours, $tourCity, $hasConditions ? count($tours) : 3);
                $displayData['hotels'] = $this->filterItemsByCity($hotels, $hotelCity, $hasConditions ? count($hotels) : 3);
                $displayData['tour_city'] = $tourCity;
                $displayData['hotel_city'] = $hotelCity;
            } else {
                // Same city mixed
                $displayData['tours'] = array_slice($tours, 0, 3);
                $displayData['hotels'] = array_slice($hotels, 0, 3);
            }
            return $displayData;
        }
        
        // CASE 3 & 7: Multi-city tours
        if ($queryType === 'tour_only' && count($cities) >= 2 && !empty($tours)) {
            $grouped = $this->groupItemsByCity($tours, $cities, 100);
            
            if ($this->isValidMultiCityDistribution($grouped, $cities)) {
                $city1Tours = array_values(array_filter($grouped, fn($item) => 
                    ($item['city_group'] ?? '') === $cities[0]
                ));
                $city2Tours = array_values(array_filter($grouped, fn($item) => 
                    ($item['city_group'] ?? '') === $cities[1]
                ));
                
                // For basic queries, need at least 3 per city
                if (!$hasConditions) {
                    if (count($city1Tours) >= 3 && count($city2Tours) >= 3) {
                        $displayData['tours'] = array_merge(
                            array_slice($city1Tours, 0, 3),
                            array_slice($city2Tours, 0, 3)
                        );
                        $displayData['multi_city'] = true;
                        $displayData['cities'] = array_slice($cities, 0, 2);
                    } else {
                        // Fallback to single city if not enough results
                        $displayData['tours'] = array_slice($tours, 0, 6);
                    }
                } else {
                    // Conditional query: show all results found
                    $displayData['tours'] = array_merge($city1Tours, $city2Tours);
                    $displayData['multi_city'] = true;
                    $displayData['cities'] = array_slice($cities, 0, 2);
                }
                
                return $displayData;
            }
        }
        
        // CASE 4: Multi-city hotels
        if ($queryType === 'hotel_only' && count($cities) >= 2 && !empty($hotels)) {
            $grouped = $this->groupItemsByCity($hotels, $cities, 100);
            
            if ($this->isValidMultiCityDistribution($grouped, $cities)) {
                $city1Hotels = array_values(array_filter($grouped, fn($item) => 
                    ($item['city_group'] ?? '') === $cities[0]
                ));
                $city2Hotels = array_values(array_filter($grouped, fn($item) => 
                    ($item['city_group'] ?? '') === $cities[1]
                ));
                
                if (!$hasConditions) {
                    if (count($city1Hotels) >= 3 && count($city2Hotels) >= 3) {
                        $displayData['hotels'] = array_merge(
                            array_slice($city1Hotels, 0, 3),
                            array_slice($city2Hotels, 0, 3)
                        );
                        $displayData['multi_city'] = true;
                        $displayData['cities'] = array_slice($cities, 0, 2);
                    } else {
                        $displayData['hotels'] = array_slice($hotels, 0, 6);
                    }
                } else {
                    $displayData['hotels'] = array_merge($city1Hotels, $city2Hotels);
                    $displayData['multi_city'] = true;
                    $displayData['cities'] = array_slice($cities, 0, 2);
                }
                
                return $displayData;
            }
        }
        
        // CASE 1 & 8: Single city tours
        if (!empty($tours) && empty($hotels)) {
            $displayData['tours'] = array_slice($tours, 0, $hasConditions ? count($tours) : 6);
            return $displayData;
        }
        
        // CASE 2: Single city hotels
        if (!empty($hotels) && empty($tours)) {
            $displayData['hotels'] = array_slice($hotels, 0, $hasConditions ? count($hotels) : 6);
            return $displayData;
        }
        
        return $displayData;
    }

    private function findItemCity($items, $cities) {
        foreach ($items as $item) {
            $itemCity = strtolower(trim($item['city_name'] ?? ''));
            foreach ($cities as $city) {
                if (self::cityMatches($itemCity, $city)) {
                    return $city;
                }
            }
        }
        return $cities[0] ?? '';
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
        
        // Extract tour and hotel keywords with context
        $tourPattern = '/\b(tour|tours|trip|trips|package|excursion)s?\b/i';
        $hotelPattern = '/\b(hotel|hotels|accommodation|stay|resort)s?\b/i';
        
        preg_match_all($tourPattern, $messageLower, $tourMatches, PREG_OFFSET_CAPTURE);
        preg_match_all($hotelPattern, $messageLower, $hotelMatches, PREG_OFFSET_CAPTURE);
        
        $hasTour = !empty($tourMatches[0]);
        $hasHotel = !empty($hotelMatches[0]);
        
        // Check for explicit mixing with "and"
        if ($hasTour && $hasHotel) {
            // Check if tour and hotel are separated by "and"
            // Example: "tour in city1 and hotel in city2"
            if (preg_match('/\b(tour|tours)\b.*\band\b.*\b(hotel|hotels)\b/i', $messageLower) ||
                preg_match('/\b(hotel|hotels)\b.*\band\b.*\b(tour|tours)\b/i', $messageLower)) {
                return 'mixed';
            }
        }
        
        // Single type queries
        if ($hasTour && !$hasHotel) {
            return 'tour_only';
        }
        
        if ($hasHotel && !$hasTour) {
            return 'hotel_only';
        }
        
        // Default fallback
        return 'unknown';
    }

    private function detectConditions($userMessage): bool
    {
        $messageLower = strtolower($userMessage);
        
        // Price conditions
        $hasPrice = preg_match('/\b(under|over|below|above)\s+\d+/i', $messageLower) ||
                    preg_match('/\d+\s+(million|millions)/i', $messageLower);
        
        // Duration conditions with context
        $hasDuration = preg_match('/\b(for|with|of)\s+\d+\s+(day|days)/i', $messageLower) ||
                    preg_match('/\d+\s+(day|days)\s+(tour|trip)/i', $messageLower);
        
        // Rating conditions
        $hasRating = preg_match('/\b(for|with)?\s*\d+\s+star/i', $messageLower) ||
                    preg_match('/\bstar\s+\d+/i', $messageLower);
        
        return $hasPrice || $hasDuration || $hasRating;
    }

    private function groupItemsByCity($items, $cities, $maxItems = 6): array
    {
        $grouped = [];
        
        foreach ($items as $item) {
            if (!is_array($item)) continue;
            
            $itemCity = strtolower(trim($item['city_name'] ?? $item['city'] ?? ''));
            
            // Check which city this item belongs to
            foreach ($cities as $targetCity) {
                if (self::cityMatches($itemCity, $targetCity)) {
                    $item['city_group'] = $targetCity;
                    $grouped[] = $item;
                    break;
                }
            }
        }
        
        return $grouped;
    }

    /**
     * FIXED: Ensure each city gets at least 3 items, prefer 6 each
     */
    private function balanceDistribution($city1Items, $city2Items, $otherItems, $cities, $maxItems): array
    {
        $targetPerCity = 6; // Target 6 per city
        $minPerCity = 3;    // Minimum 3 per city
        
        $result = [];
        $c1 = count($city1Items);
        $c2 = count($city2Items);

        // Both cities have enough items
        if ($c1 >= $targetPerCity && $c2 >= $targetPerCity) {
            $result = array_merge(
                array_slice($city1Items, 0, $targetPerCity),
                array_slice($city2Items, 0, $targetPerCity)
            );
        }
        // City 1 has more items
        elseif ($c1 >= $minPerCity && $c2 >= $minPerCity) {
            $result = array_merge(
                array_slice($city1Items, 0, min($c1, $targetPerCity)),
                array_slice($city2Items, 0, min($c2, $targetPerCity))
            );
        }
        // Try to fill from other items
        else {
            $result = array_merge(
                array_slice($city1Items, 0, $c1),
                array_slice($city2Items, 0, $c2)
            );

            // Distribute other items to cities that need more
            if (count($result) < $maxItems && !empty($otherItems)) {
                $needed = min($maxItems - count($result), count($otherItems));
                $assignCity = ($c1 <= $c2) ? $cities[0] : $cities[1];
                
                foreach (array_slice($otherItems, 0, $needed) as $item) {
                    $item['city_group'] = $assignCity;
                    $result[] = $item;
                }
            }
        }

        return $result;
    }

    private function isValidMultiCityDistribution($groupedItems, $cities): bool
    {
        if (count($cities) < 2 || count($groupedItems) < 2) return false;
        
        $city1Count = count(array_filter($groupedItems, fn($item) =>
            ($item['city_group'] ?? '') === ($cities[0] ?? '')
        ));
        
        $city2Count = count(array_filter($groupedItems, fn($item) =>
            ($item['city_group'] ?? '') === ($cities[1] ?? '')
        ));
        
        return $city1Count >= 1 && $city2Count >= 1;
    }

    // ==================== LAYOUT DETERMINATION ====================

    private function determineLayoutType($displayData, $userMessage): string
    {
        if ($displayData['multi_city'] ?? false) {
            $hasTours = !empty($displayData['tours']);
            return $hasTours ? 'multi_city_tours' : 'multi_city_hotels';
        }

        $hasTours = !empty($displayData['tours']);
        $hasHotels = !empty($displayData['hotels']);

        if ($hasTours && $hasHotels) return 'mixed_content';
        if ($hasTours) return 'single_tours';
        if ($hasHotels) return 'single_hotels';

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

    /**
     * Build context string from retrieval results
     */
    private function buildHybridContext($results): string
    {
        $context = "# Available Travel Information\n\n";

        $tours = [];
        $hotels = [];

        foreach ($results as $result) {
            if (!isset($result['item_type']) || !isset($result['item'])) continue;

            if ($result['item_type'] === 'tour') {
                $tours[] = $result;
            } elseif ($result['item_type'] === 'hotel') {
                $hotels[] = $result;
            }
        }

        if (!empty($tours)) {
            $context .= "## Available Tours:\n";
            $toursByCity = [];
            
            foreach ($tours as $tourResult) {
                $tour = $tourResult['item'];
                $cityName = $tour['city_name'] ?? $tour['city'] ?? 'Unknown City';
                
                if (!isset($toursByCity[$cityName])) {
                    $toursByCity[$cityName] = [];
                }
                $toursByCity[$cityName][] = $tour;
            }

            foreach ($toursByCity as $cityName => $cityTours) {
                $context .= "\nTours in {$cityName}:\n";
                foreach ($cityTours as $tour) {
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
            $hotelsByCity = [];
            
            foreach ($hotels as $hotelResult) {
                $hotel = $hotelResult['item'];
                $cityName = $hotel['city_name'] ?? $hotel['city'] ?? 'Unknown City';
                
                if (!isset($hotelsByCity[$cityName])) {
                    $hotelsByCity[$cityName] = [];
                }
                $hotelsByCity[$cityName][] = $hotel;
            }

            foreach ($hotelsByCity as $cityName => $cityHotels) {
                $context .= "\nHotels in {$cityName}:\n";
                foreach ($cityHotels as $hotel) {
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