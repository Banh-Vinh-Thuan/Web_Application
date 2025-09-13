<?php
require_once './Logger.php';

class DataRetriever {
    private $dbService;
    private $cityMappings;
    
    public function __construct($dbService) {
        $this->dbService = $dbService;
        $this->cityMappings = [
            'hoi an' => 'Hoi An',
            'ho chi minh' => 'Ho Chi Minh City', 
            'saigon' => 'Ho Chi Minh City',
            'da nang' => 'Da Nang',
            'da lat' => 'Da Lat',
            'dalat' => 'Da Lat'
        ];
    }
    
    public function retrieveRelevantData($intent, $entities, $message) {
        $result = $this->initializeResult($entities);
        
        // Early return for international destinations
        if ($this->isInternationalOnly($entities)) {
            return $this->handleInternationalDestination($entities);
        }
        
        // Handle Vietnamese destinations
        if ($this->hasVietnameseCities($entities)) {
            $this->processVietnameseDestinations($result, $entities, $intent, $message);
        } else {
            $this->processGeneralSearch($result, $entities, $intent, $message);
        }
        
        return $result;
    }
    
    private function initializeResult($entities) {
        return [
            'data' => ['tours' => [], 'hotels' => [], 'cities' => []],
            'match_level' => 'none',
            'fallback_message' => '',
            'suggestions' => [],
            'is_international' => $entities['is_international'],
            'layout_type' => 'default'
        ];
    }
    
    private function isInternationalOnly($entities) {
        return $entities['is_international'] && 
               !$this->hasVietnameseCities($entities) && 
               !empty($entities['cities']);
    }
    
    private function hasVietnameseCities($entities) {
        foreach ($entities['cities'] as $city) {
            if ($city['id'] !== null) {
                return true;
            }
        }
        return false;
    }
    
    private function handleInternationalDestination($entities) {
        $cityName = $entities['cities'][0]['name'];
        return [
            'data' => ['tours' => [], 'hotels' => [], 'cities' => []],
            'match_level' => 'international_gemini',
            'fallback_message' => "Let me create a custom travel plan for $cityName",
            'suggestions' => [
                "Best time to visit $cityName",
                "Popular attractions in $cityName",
                "Budget travel tips for $cityName",
                "Transportation in $cityName"
            ],
            'is_international' => true,
            'layout_type' => 'default'
        ];
    }
    
    private function processVietnameseDestinations(&$result, $entities, $intent, $message) {
        // Filter to Vietnamese cities only
        $entities['cities'] = $this->filterVietnameseCities($entities['cities']);
        
        // Set city data
        $result['data']['cities'] = $this->dbService->getCities($entities['cities'][0]['id']);
        
        // Route to appropriate handler
        $handlers = [
            'mixed_search' => 'handleMixedSearch',
            'tour_search' => 'handleTourSearch', 
            'hotel_search' => 'handleHotelSearch',
            'destination_info' => 'handleDestinationInfo'
        ];
        
        $handler = $handlers[$intent] ?? 'handleGeneralSearch';
        $this->$handler($result, $entities, $message);
    }
    
    private function filterVietnameseCities($cities) {
        return array_values(array_filter($cities, function($city) {
            return $city['id'] !== null;
        }));
    }
    
    // Mixed search with proper multi-city handling
    private function handleMixedSearch(&$result, $entities, $message) {
        $searchConfig = $this->parseMixedSearchIntent($message, $entities);
        $data = $this->executeMixedSearch($searchConfig, $entities);
        
        if (!empty($data['tours']) || !empty($data['hotels'])) {
            $result['data'] = $data;
            $result['match_level'] = 'mixed_search';
            $result['layout_type'] = 'mixed_content';
            $result['fallback_message'] = $this->buildMixedSearchMessage($data);
            $result['suggestions'] = $this->generateMixedSearchSuggestions($data, $entities);
        } else {
            $result['match_level'] = 'no_results';
            $result['fallback_message'] = "I couldn't find tours or hotels matching your criteria.";
            $result['suggestions'] = $this->getDefaultSuggestions();
        }
    }
    
    // Better parsing logic for mixed search intent
    private function parseMixedSearchIntent($message, $entities) {
        $message = strtolower($message);
        $config = [];
        
        // Initialize config for all detected Vietnamese cities
        foreach ($entities['cities'] as $city) {
            $config[$city['id']] = [
                'city_name' => $city['name'],
                'needs_tours' => false,
                'needs_hotels' => false
            ];
        }

        // Define keywords and find their positions in the message
        $tourKeywords = ['tour', 'trip', 'package', 'excursion'];
        $hotelKeywords = ['hotel', 'accommodation', 'stay', 'lodging', 'resort'];
        $keywordPositions = [];

        foreach ($tourKeywords as $keyword) {
            if (($pos = strpos($message, $keyword)) !== false) {
                $keywordPositions[] = ['type' => 'tour', 'pos' => $pos];
            }
        }
        foreach ($hotelKeywords as $keyword) {
            if (($pos = strpos($message, $keyword)) !== false) {
                $keywordPositions[] = ['type' => 'hotel', 'pos' => $pos];
            }
        }

        // If keywords are found, associate each with its nearest city
        if (!empty($keywordPositions)) {
            foreach ($keywordPositions as $keyword) {
                $closestCityId = null;
                $minDistance = PHP_INT_MAX;

                foreach ($entities['cities'] as $city) {
                    $cityName = strtolower($city['name']);
                    // Find the first occurrence of the city name
                    if (($cityPos = strpos($message, $cityName)) !== false) {
                        $distance = abs($keyword['pos'] - $cityPos);
                        if ($distance < $minDistance) {
                            $minDistance = $distance;
                            $closestCityId = $city['id'];
                        }
                    }
                }

                // Assign the service to the closest city found
                if ($closestCityId !== null) {
                    if ($keyword['type'] === 'tour') {
                        $config[$closestCityId]['needs_tours'] = true;
                    } else {
                        $config[$closestCityId]['needs_hotels'] = true;
                    }
                }
            }
        }
        
        // Check if any service was assigned. If not, fallback to default logic.
        $isAnyServiceAssigned = false;
        foreach ($config as $cityConfig) {
            if ($cityConfig['needs_tours'] || $cityConfig['needs_hotels']) {
                $isAnyServiceAssigned = true;
                break;
            }
        }

        if (!$isAnyServiceAssigned) {
            $this->applyMixedSearchDefaults($config, $message, $entities);
        }
        
        return $config;
    }
    
    //New method for handling mixed search defaults
    private function applyMixedSearchDefaults(&$config, $message, $entities) {
        $hasTourKeywords = $this->messageContainsKeywords($message, ['tour', 'trip', 'package', 'excursion']);
        $hasHotelKeywords = $this->messageContainsKeywords($message, ['hotel', 'accommodation', 'stay', 'lodging', 'resort']);
        
        $cityCount = count($config);
        
        // If both keywords present or no specific keywords, enable both for all cities
        if (($hasTourKeywords && $hasHotelKeywords) || (!$hasTourKeywords && !$hasHotelKeywords)) {
            foreach ($config as &$cityConfig) {
                if (!$cityConfig['needs_tours'] && !$cityConfig['needs_hotels']) {
                    $cityConfig['needs_tours'] = true;
                    $cityConfig['needs_hotels'] = true;
                }
            }
        }
        // For single keyword with multiple cities, distribute services
        elseif ($cityCount > 1) {
            $cities = array_keys($config);
            if ($hasTourKeywords && !$hasHotelKeywords) {
                // Tours in first city, hotels in second
                $config[$cities[0]]['needs_tours'] = true;
                if (isset($cities[1])) {
                    $config[$cities[1]]['needs_hotels'] = true;
                }
            } elseif ($hasHotelKeywords && !$hasTourKeywords) {
                // Hotels in first city, tours in second
                $config[$cities[0]]['needs_hotels'] = true;
                if (isset($cities[1])) {
                    $config[$cities[1]]['needs_tours'] = true;
                }
            }
        }
        // Single city with single keyword
        else {
            foreach ($config as &$cityConfig) {
                if (!$cityConfig['needs_tours'] && !$cityConfig['needs_hotels']) {
                    $cityConfig['needs_tours'] = $hasTourKeywords;
                    $cityConfig['needs_hotels'] = $hasHotelKeywords;
                }
            }
        }
    }

    private function messageContainsKeywords($message, $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * FIXED: Execute mixed search with proper result limiting (3 per side = 6 total)
     */
    private function executeMixedSearch($searchConfig, $entities) {
        $allData = ['tours' => [], 'hotels' => []];
        
        foreach ($searchConfig as $cityId => $config) {
            if ($config['needs_tours']) {
                $tours = $this->dbService->getTours(
                    $cityId, 
                    $entities['duration'], 
                    $entities['budget'], 
                    3, // FIXED: Limit to 3 per city for proper 2-column display
                    $entities['price_condition']
                );
                
                // Add city grouping
                foreach ($tours as &$tour) {
                    $tour['city_group'] = $config['city_name'];
                }
                $allData['tours'] = array_merge($allData['tours'], $tours);
            }
            
            if ($config['needs_hotels']) {
                $hotels = $this->dbService->getHotels(
                    $cityId,
                    3, // FIXED: Limit to 3 per city for proper 2-column display
                    $entities['rating'],
                    $entities['budget'], 
                    $entities['price_condition']
                );
                
                // Add city grouping
                foreach ($hotels as &$hotel) {
                    $hotel['city_group'] = $config['city_name'];
                }
                $allData['hotels'] = array_merge($allData['hotels'], $hotels);
            }
        }
        
        // FIXED: Ensure we don't exceed 6 results total for proper 2-column display
        $allData['tours'] = array_slice($allData['tours'], 0, 6);
        $allData['hotels'] = array_slice($allData['hotels'], 0, 6);
        
        return $allData;
    }
    
    /**
     * FIXED: Tour search with proper multi-city handling
     */
    private function handleTourSearch(&$result, $entities, $message) {
        if (count($entities['cities']) > 1) {
            $this->handleMultiCitySearch($result, $entities, 'tour');
        } else {
            $this->handleSingleCitySearch($result, $entities, 'tour');
        }
    }
    
    /**
     * FIXED: Hotel search with proper multi-city handling  
     */
    private function handleHotelSearch(&$result, $entities, $message) {
        if (count($entities['cities']) > 1) {
            $this->handleMultiCitySearch($result, $entities, 'hotel');
        } else {
            $this->handleSingleCitySearch($result, $entities, 'hotel');
        }
    }
    
    // Multi-city search with proper 3+3 distribution
    private function handleMultiCitySearch(&$result, $entities, $type) {
        $allItems = [];
        $itemsByCity = [];
        $cityNames = [];
        
        // Take only the first TWO cities for left/right layout
        $selectedCities = array_slice($entities['cities'], 0, 2);
        
        foreach ($selectedCities as $index => $city) {
            $cityNames[] = $city['name'];
            
            // Get 3 items per city
            $limit = 3;
            
            if ($type === 'tour') {
                $items = $this->dbService->getTours(
                    $city['id'], 
                    $entities['duration'], 
                    $entities['budget'], 
                    $limit,
                    $entities['price_condition']
                );
            } else {
                $items = $this->dbService->getHotels(
                    $city['id'],
                    $limit,
                    $entities['rating'],
                    $entities['budget'],
                    $entities['price_condition']
                );
            }
            
            if (!empty($items)) {
                foreach ($items as &$item) {
                    $item['city_group'] = $city['name'];
                    // FIXED: Assign column position based on city index
                    $item['column_position'] = $index === 0 ? 'left' : 'right';
                }
                $itemsByCity[$city['name']] = $items;
                $allItems = array_merge($allItems, $items);
            }
        }
        
        if (!empty($allItems)) {
            $result['data'][$type . 's'] = $allItems;
            $result['data'][$type . 's_by_city'] = $itemsByCity;
            $result['match_level'] = "multi_city_{$type}s";
            $result['layout_type'] = "multi_city_{$type}s"; // This triggers the correct layout
            
            $cityList = implode(' and ', $cityNames);
            $itemCount = count($allItems);
            $result['fallback_message'] = "Here are {$type}s available in $cityList ($itemCount {$type}s found):";
            $result['suggestions'] = $this->generateMultiCitySuggestions($cityNames, $entities, $type);
        } else {
            $result['match_level'] = 'no_city_match';
            $cityList = implode(', ', $cityNames);
            $result['fallback_message'] = "I don't have {$type} listings for $cityList in our database.";
            $result['suggestions'] = $this->getDefaultSuggestions();
        }
    }
    
    /**
     * FIXED: Single city search with proper 6-item limit
     */
    private function handleSingleCitySearch(&$result, $entities, $type) {
        $cityName = $entities['cities'][0]['name'];
        $cityId = $entities['cities'][0]['id'];
        
        // FIXED: Limit to 6 items for single city (3 left + 3 right columns)
        $limit = 6;
        
        if ($type === 'tour') {
            $items = $this->dbService->getTours(
                $cityId,
                $entities['duration'],
                $entities['budget'],
                $limit,
                $entities['price_condition']
            );
        } else {
            $items = $this->dbService->getHotels(
                $cityId,
                $limit,
                $entities['rating'],
                $entities['budget'],
                $entities['price_condition']
            );
        }
        
        if (!empty($items)) {
            // FIXED: Add column positioning for single city results
            foreach ($items as $index => &$item) {
                $item['column_position'] = $index < 3 ? 'left' : 'right';
            }
            
            $result['data'][$type . 's'] = $items;
            $result['match_level'] = 'exact';
            $result['fallback_message'] = "Here are {$type}s in $cityName:";
            $result['suggestions'] = $this->generateSingleCitySuggestions($cityName, $entities, $type);
        } else {
            $result['match_level'] = 'no_city_match';
            $result['fallback_message'] = "I don't have {$type} listings for $cityName in our database.";
            $result['suggestions'] = $this->getDefaultSuggestions();
        }
    }
    
    private function processGeneralSearch(&$result, $entities, $intent, $message) {
        switch ($intent) {
            case 'mixed_search':
                $this->handleGeneralMixedSearch($result, $entities);
                break;
            case 'tour_search':
                $this->handleGeneralTourSearch($result, $entities);
                break;
            case 'hotel_search':
                $this->handleGeneralHotelSearch($result, $entities);
                break;
            default:
                $this->handleGeneralSearch($result, $entities);
                break;
        }
    }
    
    private function handleGeneralMixedSearch(&$result, $entities) {
        $toursByCity = $this->dbService->getToursGroupedByCity(
            $entities['duration'],
            $entities['budget'],
            $entities['price_condition'],
            25
        );
        
        $hotelsByCity = $this->dbService->getHotelsGroupedByCity(
            $entities['rating'],
            $entities['budget'],
            $entities['price_condition'],
            25
        );
        
        $result['data']['tours'] = $this->selectDiverseResults($toursByCity, 6, 'tourid'); // FIXED: Limit to 6
        $result['data']['hotels'] = $this->selectDiverseResults($hotelsByCity, 6, 'hotelid'); // FIXED: Limit to 6
        
        if (!empty($result['data']['tours']) || !empty($result['data']['hotels'])) {
            $result['match_level'] = 'mixed_general_search';
            $result['layout_type'] = 'mixed_content';
            $result['fallback_message'] = 'Here are tours and hotels available across Vietnam:';
            $result['suggestions'] = $this->getGeneralSuggestions();
        } else {
            $result['match_level'] = 'no_results';
            $result['fallback_message'] = "I couldn't find tours or hotels matching your criteria.";
            $result['suggestions'] = $this->getDefaultSuggestions();
        }
    }
    
    private function selectDiverseResults($groupedData, $totalLimit, $idField) {
        $selected = [];
        $maxPerCity = 2;
        
        // First pass: up to 2 per city
        foreach ($groupedData as $cityName => $items) {
            $taken = 0;
            foreach ($items as $item) {
                if ($taken < $maxPerCity && count($selected) < $totalLimit) {
                    $selected[] = $item;
                    $taken++;
                }
            }
        }
        
        // Second pass: fill remaining slots
        if (count($selected) < $totalLimit) {
            foreach ($groupedData as $cityName => $items) {
                foreach ($items as $item) {
                    if (count($selected) >= $totalLimit) break 2;
                    
                    $alreadySelected = false;
                    foreach ($selected as $selectedItem) {
                        if ($selectedItem[$idField] === $item[$idField]) {
                            $alreadySelected = true;
                            break;
                        }
                    }
                    
                    if (!$alreadySelected) {
                        $selected[] = $item;
                    }
                }
            }
        }
        
        return $selected;
    }
    
    // Suggestion generators
    private function generateMixedSearchSuggestions($data, $entities) {
        $suggestions = [];
        
        if (!empty($data['tours']) && !empty($data['hotels'])) {
            $suggestions[] = "Compare tour and hotel packages";
            $suggestions[] = "Find complete vacation packages";
        }
        
        if ($entities['duration']) {
            $nextDuration = $entities['duration'] + 1;
            $suggestions[] = "Find {$nextDuration}-day packages";
        } else {
            $suggestions[] = "Show 3-day tour packages";
        }
        
        $suggestions[] = "Plan a multi-city itinerary";
        
        return array_slice($suggestions, 0, 4);
    }
    
    private function generateMultiCitySuggestions($cityNames, $entities, $type) {
        $suggestions = [];
        
        if (!empty($cityNames)) {
            $suggestions[] = "Focus on {$type}s in " . $cityNames[0] . " only";
            if (count($cityNames) > 1) {
                $otherType = $type === 'tour' ? 'hotel' : 'tour';
                $suggestions[] = "Find {$otherType}s in " . $cityNames[1];
            }
        }
        
        $suggestions[] = "Compare prices between cities";
        $suggestions[] = "Plan multi-city transportation";
        
        return array_slice($suggestions, 0, 4);
    }
    
    private function generateSingleCitySuggestions($cityName, $entities, $type) {
        $suggestions = [];
        
        $otherType = $type === 'tour' ? 'hotel' : 'tour';
        $suggestions[] = "Find {$otherType}s in $cityName";
        $suggestions[] = "Get travel tips for $cityName";
        $suggestions[] = "What's the weather like in $cityName?";
        $suggestions[] = "Best time to visit $cityName";
        
        return $suggestions;
    }
    
    private function getDefaultSuggestions() {
        return [
            'Show all available tours and hotels',
            'Find tours in popular cities',
            'Browse hotels by rating',
            'Get travel advice for Vietnam'
        ];
    }
    
    private function getGeneralSuggestions() {
        return [
            'Find tours in specific cities',
            'Show hotels with 4+ stars',
            'Plan a budget-friendly trip',
            'Popular destinations in Vietnam'
        ];
    }
    
    private function buildMixedSearchMessage($data) {
        $tourCount = count($data['tours']);
        $hotelCount = count($data['hotels']);
        
        if ($tourCount > 0 && $hotelCount > 0) {
            return "Found $tourCount tours and $hotelCount hotels:";
        } elseif ($tourCount > 0) {
            return "Found $tourCount tours:";
        } elseif ($hotelCount > 0) {
            return "Found $hotelCount hotels:";
        }
        
        return "Here are your options:";
    }
    
    // Legacy method handlers for backward compatibility
    private function handleDestinationInfo(&$result, $entities, $message) {
        $this->handleSingleCitySearch($result, $entities, 'mixed');
        $result['match_level'] = 'destination_info';
    }
    
    private function handleGeneralTourSearch(&$result, $entities) {
        $toursByCity = $this->dbService->getToursGroupedByCity(
            $entities['duration'],
            $entities['budget'],
            $entities['price_condition'],
            50
        );
        
        if (!empty($toursByCity)) {
            $result['data']['tours'] = $this->selectDiverseResults($toursByCity, 6, 'tourid'); // FIXED: Limit to 6
            $result['match_level'] = 'general_search';
            $result['fallback_message'] = 'Here are available tours across Vietnam:';
            $result['suggestions'] = $this->getGeneralSuggestions();
        } else {
            $result['match_level'] = 'no_results';
            $result['fallback_message'] = "I couldn't find tours matching your criteria.";
            $result['suggestions'] = $this->getDefaultSuggestions();
        }
    }
    
    private function handleGeneralHotelSearch(&$result, $entities) {
        $hotelsByCity = $this->dbService->getHotelsGroupedByCity(
            $entities['rating'],
            $entities['budget'],
            $entities['price_condition'],
            50
        );
        
        if (!empty($hotelsByCity)) {
            $result['data']['hotels'] = $this->selectDiverseResults($hotelsByCity, 6, 'hotelid'); // FIXED: Limit to 6
            $result['match_level'] = 'general_search';
            $result['fallback_message'] = 'Here are available hotels across Vietnam:';
            $result['suggestions'] = $this->getGeneralSuggestions();
        } else {
            $result['match_level'] = 'no_results';
            $result['fallback_message'] = "I couldn't find hotels matching your criteria.";
            $result['suggestions'] = $this->getDefaultSuggestions();
        }
    }
    
    private function handleGeneralSearch(&$result, $entities) {
        $result['data']['tours'] = $this->dbService->getTours(null, null, null, 6); // FIXED: Limit to 6
        $result['data']['hotels'] = $this->dbService->getHotels(null, 6); // FIXED: Limit to 6
        $result['match_level'] = 'general';
        $result['fallback_message'] = 'Here\'s what we offer for travel in Vietnam:';
        $result['suggestions'] = $this->getGeneralSuggestions();
    }
}
?>