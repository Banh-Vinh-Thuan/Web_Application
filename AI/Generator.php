<?php

require_once './Logger.php';
require_once './config.php';

/**
 * Response Generator with Centralized City Matching
 * CLEANED: Removed duplicate city matching logic
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

    /**
     * Structure display data with multi-city support
     */
    private function structureDisplayData($results, $userMessage): array
    {
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

        $cities = self::extractCitiesFromQuery($userMessage);
        $isMultiCity = count($cities) >= 2;

        $displayData = ['multi_city' => false];

        if ($isMultiCity && !empty($tours)) {
            $grouped = $this->groupItemsByCity($tours, $cities, 6);
            if ($this->isValidMultiCityDistribution($grouped, $cities)) {
                $displayData['tours'] = $grouped;
                $displayData['multi_city'] = true;
                $displayData['cities'] = array_slice($cities, 0, 2);
            } else {
                $displayData['tours'] = array_slice($tours, 0, 6);
            }
        } elseif ($isMultiCity && !empty($hotels)) {
            $grouped = $this->groupItemsByCity($hotels, $cities, 6);
            if ($this->isValidMultiCityDistribution($grouped, $cities)) {
                $displayData['hotels'] = $grouped;
                $displayData['multi_city'] = true;
                $displayData['cities'] = array_slice($cities, 0, 2);
            } else {
                $displayData['hotels'] = array_slice($hotels, 0, 6);
            }
        } else {
            if (!empty($tours)) $displayData['tours'] = array_slice($tours, 0, 6);
            if (!empty($hotels)) $displayData['hotels'] = array_slice($hotels, 0, 6);
        }

        return $displayData;
    }

    /**
     * Group items by city with balanced distribution
     */
    private function groupItemsByCity($items, $cities, $maxItems = 6): array
    {
        $city1Items = [];
        $city2Items = [];
        $otherItems = [];

        foreach ($items as $item) {
            if (!is_array($item)) continue;

            $itemCity = strtolower(trim($item['city_name'] ?? $item['city'] ?? ''));
            
            if (!empty($cities[0]) && self::cityMatches($itemCity, $cities[0])) {
                $item['city_group'] = $cities[0];
                $city1Items[] = $item;
            } elseif (!empty($cities[1]) && self::cityMatches($itemCity, $cities[1])) {
                $item['city_group'] = $cities[1];
                $city2Items[] = $item;
            } else {
                $otherItems[] = $item;
            }
        }

        return $this->balanceDistribution($city1Items, $city2Items, $otherItems, $cities, $maxItems);
    }

    private function balanceDistribution($city1Items, $city2Items, $otherItems, $cities, $maxItems): array
    {
        $targetPerCity = 3;
        $result = [];

        $c1 = count($city1Items);
        $c2 = count($city2Items);

        if ($c1 >= $targetPerCity && $c2 >= $targetPerCity) {
            $result = array_merge(
                array_slice($city1Items, 0, $targetPerCity),
                array_slice($city2Items, 0, $targetPerCity)
            );
        } elseif ($c1 >= $targetPerCity) {
            $result = array_merge(
                array_slice($city1Items, 0, min($maxItems - $c2, $targetPerCity)),
                array_slice($city2Items, 0, $c2)
            );
        } elseif ($c2 >= $targetPerCity) {
            $result = array_merge(
                array_slice($city1Items, 0, $c1),
                array_slice($city2Items, 0, min($maxItems - $c1, $targetPerCity))
            );
        } else {
            $result = array_merge(
                array_slice($city1Items, 0, $c1),
                array_slice($city2Items, 0, $c2)
            );
        }

        // Fill with other items if needed
        if (count($result) < $maxItems && !empty($otherItems)) {
            $needed = $maxItems - count($result);
            $additional = array_slice($otherItems, 0, $needed);
            $assignCity = ($c1 <= $c2) ? $cities[0] : $cities[1];
            
            foreach ($additional as $item) {
                $item['city_group'] = $assignCity;
                $result[] = $item;
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

        return $city1Count > 0 && $city2Count > 0;
    }

    // ==================== LAYOUT DETERMINATION ====================

    private function determineLayoutType($displayData, $userMessage): string
    {
        if ($displayData['multi_city'] ?? false) {
            return !empty($displayData['tours']) ? 'multi_city_tours' : 'multi_city_hotels';
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
            foreach ($tours as $tourResult) {
                $tour = $tourResult['item'];
                $score = $tourResult['combined_score'] ?? $tourResult['final_score'] ?? 0;
                
                // Định dạng cho tour (giữ nguyên)
                $context .= sprintf(
                    "**%s** (City: %s)\nDuration: %d days | Price: %s VND\n\n",
                    $tour['tour_name'] ?? 'Unknown Tour',
                    $tour['city_name'] ?? $tour['city'] ?? 'Unknown City',
                    $tour['duration_days'] ?? 0,
                    number_format($tour['price_per_person'] ?? 0)
                );
            }
        }

        if (!empty($hotels)) {
            $context .= "## Available Hotels:\n";
            foreach ($hotels as $hotelResult) {
                $hotel = $hotelResult['item'];
                $score = $hotelResult['combined_score'] ?? $hotelResult['final_score'] ?? 0;

                $context .= sprintf(
                    "**%s** (City: %s)\nRating: %.1f | Price: %s VND/night\n\n",
                    $hotel['hotel'] ?? $hotel['hotel_name'] ?? 'Unknown Hotel',
                    $hotel['city_name'] ?? $hotel['city'] ?? 'Unknown City',
                    $hotel['ratings'] ?? 0,
                    number_format($hotel['cost'] ?? 0)
                );
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

    /**
     * Initialize city patterns (static, shared across instances)
     */
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

        // Sort by length for better matching
        uksort(self::$cityPatterns, fn($a, $b) => strlen($b) - strlen($a));
    }

    /**
     * Extract cities from query string
     */
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

        // Check for "and" patterns (e.g., "Hanoi and Hue")
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

    /**
     * Check if item city matches query city
     */
    public static function cityMatches($itemCity, $queryCity): bool
    {
        self::initializeCityPatterns();
        
        if (empty($itemCity) || empty($queryCity)) return false;

        $itemCity = strtolower(trim($itemCity));
        $queryCity = strtolower(trim($queryCity));

        // Direct match
        if (strpos($itemCity, $queryCity) !== false || strpos($queryCity, $itemCity) !== false) {
            return true;
        }

        // Check mappings
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

        // Fuzzy match
        $similarity = 0;
        similar_text($itemCity, $queryCity, $similarity);
        return $similarity >= 60;
    }
}
?>