<?php
require_once './Logger.php';

/**
 * Service for retrieving relevant data based on user intent and entities
 */
class DataRetriever {
    private $dbService;
    
    public function __construct($dbService) {
        $this->dbService = $dbService;
    }
    
    // Main method to retrieve relevant data
   public function retrieveRelevantData($intent, $entities, $message) {
        $result = [
            'data' => [
                'tours' => [],
                'hotels' => [],
                'cities' => []
            ],
            'match_level' => 'none',
            'fallback_message' => '',
            'suggestions' => [],
            'is_international' => $entities['is_international'],
            'layout_type' => 'default' // New field to specify layout type
        ];
        
        // Check if we have Vietnamese cities with database IDs
        $hasVietnameseCities = false;
        foreach ($entities['cities'] as $city) {
            if ($city['id'] !== null) {
                $hasVietnameseCities = true;
                break;
            }
        }
        
        // Handle international destinations ONLY when there are no Vietnamese cities
        if ($entities['is_international'] && !$hasVietnameseCities && !empty($entities['cities'])) {
            $cityName = $entities['cities'][0]['name'];
            $result['match_level'] = 'international_gemini';
            $result['fallback_message'] = "Let me create a custom travel plan for $cityName";
            Logger::debug("International destination detected", ['city' => $cityName]);
            return $result;
        }
        
        // Handle Vietnamese destinations with database lookup
        if ($hasVietnameseCities) {
            // Filter to only Vietnamese cities
            $vietnameseCities = array_filter($entities['cities'], function($city) {
                return $city['id'] !== null;
            });
            
            // Update entities to use only Vietnamese cities
            $entities['cities'] = array_values($vietnameseCities);
            $result['data']['cities'] = $this->dbService->getCities($entities['cities'][0]['id']);
            
            switch ($intent) {
                case 'mixed_search':
                    $this->performVietnameseMixedSearch($result, $entities);
                    break;
                case 'tour_search':
                    $this->performVietnameseTourSearch($result, $entities);
                    break;
                case 'hotel_search':
                    $this->performVietnameseHotelSearch($result, $entities);
                    break;
                case 'destination_info':
                    $this->performVietnameseDestinationSearch($result, $entities);
                    break;
                default:
                    $this->performVietnameseGeneralSearch($result, $entities);
                    break;
            }
        }
        // Handle general searches without specific locations
        else if (empty($entities['cities'])) {
            switch ($intent) {
                case 'mixed_search':
                    $this->performGeneralMixedSearch($result, $entities);
                    break;
                case 'tour_search':
                    $this->performGeneralTourSearch($result, $entities);
                    break;
                case 'hotel_search':
                    $this->performGeneralHotelSearch($result, $entities);
                    break;
                case 'price_inquiry':
                    // Determine if it's tour or hotel based on context
                    if (strpos($message, 'hotel') !== false || strpos($message, 'accommodation') !== false) {
                        $this->performGeneralHotelSearch($result, $entities);
                    } else {
                        $this->performGeneralTourSearch($result, $entities);
                    }
                    break;
                case 'duration_inquiry':
                    $this->performGeneralTourSearch($result, $entities);
                    break;
                case 'rating_inquiry':
                    $this->performGeneralHotelSearch($result, $entities);
                    break;
                default:
                    $this->performGeneralSearch($result, $entities);
                    break;
            }
        }
        
        return $result;
    }

    // Helper method to check if a keyword appears near a city name in the message
    private function isKeywordNearCity($message, $keyword, $cityName) {
        $keywordPos = strpos($message, $keyword);
        $cityPos = strpos($message, $cityName);
        
        if ($keywordPos === false || $cityPos === false) {
            return false;
        }
        
        // Consider keyword and city as "near" if they're within 50 characters of each other
        return abs($keywordPos - $cityPos) <= 50;
    }

    // Build descriptive message for multi-city mixed search results
    private function buildMixedSearchMessage($tourCities, $hotelCities, $tours, $hotels) {
        $messages = [];
        
        if (!empty($tours) && !empty($tourCities)) {
            $tourCount = count($tours);
            $cityList = implode(', ', array_unique($tourCities));
            if (count($tourCities) == 1) {
                $messages[] = "Found $tourCount tours in $cityList";
            } else {
                $messages[] = "Found $tourCount tours across $cityList";
            }
        }
        
        if (!empty($hotels) && !empty($hotelCities)) {
            $hotelCount = count($hotels);
            $cityList = implode(', ', array_unique($hotelCities));
            if (count($hotelCities) == 1) {
                $messages[] = "Found $hotelCount hotels in $cityList";
            } else {
                $messages[] = "Found $hotelCount hotels across $cityList";
            }
        }
        
        if (empty($messages)) {
            return "Here are the available options:";
        }
        
        return implode(" and ", $messages) . ":";
    }

    // Generate suggestions for multi-city mixed searches
    private function generateMultiCityMixedSuggestions($tourCities, $hotelCities, $entities) {
        $suggestions = [];
        
        // Suggest focusing on specific cities
        $allCities = array_unique(array_merge($tourCities, $hotelCities));
        if (count($allCities) > 1) {
            $suggestions[] = "Focus on " . $allCities[0] . " only";
            $suggestions[] = "Show options for " . $allCities[1] . " only";
        }
        
        // Suggest opposite service for cities
        if (!empty($tourCities) && !empty($hotelCities)) {
            $tourOnlyCities = array_diff($tourCities, $hotelCities);
            $hotelOnlyCities = array_diff($hotelCities, $tourCities);
            
            if (!empty($tourOnlyCities)) {
                $suggestions[] = "Find hotels in " . $tourOnlyCities[0];
            }
            if (!empty($hotelOnlyCities)) {
                $suggestions[] = "Find tours in " . $hotelOnlyCities[0];
            }
        }
        
        // Add contextual suggestions based on criteria
        if ($entities['duration']) {
            $nextDuration = $entities['duration'] + 1;
            $suggestions[] = "Find {$nextDuration}-day packages";
        }
        
        if ($entities['budget']) {
            if ($entities['price_condition'] === 'under') {
                $suggestions[] = "Show premium options over " . number_format($entities['budget']) . " VND";
            } else {
                $suggestions[] = "Show budget options under " . number_format($entities['budget']) . " VND";
            }
        }
        
        // Fill with generic suggestions if needed
        $genericSuggestions = [
            "Plan a multi-city itinerary",
            "Compare prices between cities", 
            "Best transportation between cities",
            "Travel tips for multiple destinations",
            "Customize your multi-city trip"
        ];
        
        while (count($suggestions) < 4 && !empty($genericSuggestions)) {
            $suggestion = array_shift($genericSuggestions);
            if (!in_array($suggestion, $suggestions)) {
                $suggestions[] = $suggestion;
            }
        }
        
        return array_slice($suggestions, 0, 4);
    }

    // Perform mixed search for both tours and hotels in a specific Vietnamese city
    private function performVietnameseMixedSearch(&$result, $entities) {
        $tours = [];
        $hotels = [];
        $tourCities = [];
        $hotelCities = [];
        $message = strtolower($entities['raw_message'] ?? '');
        
        // Check if this is a specific multi-city request with different services
        $hasSpecificTourCities = false;
        $hasSpecificHotelCities = false;
        
        foreach ($entities['cities'] as $city) {
            $cityName = $city['name'];
            $cityId = $city['id'];
            $cityNameLower = strtolower($cityName);
            
            // Check if this city is specifically mentioned with "tour" keywords
            $tourKeywords = ['tour', 'trip', 'package', 'excursion', 'itinerary'];
            $hotelKeywords = ['hotel', 'accommodation', 'stay', 'lodging', 'resort'];
            
            $cityMentionedWithTour = false;
            $cityMentionedWithHotel = false;
            
            // Find if city is mentioned near tour keywords
            foreach ($tourKeywords as $keyword) {
                if ($this->isKeywordNearCity($message, $keyword, $cityNameLower)) {
                    $cityMentionedWithTour = true;
                    $hasSpecificTourCities = true;
                    break;
                }
            }
            
            // Find if city is mentioned near hotel keywords
            foreach ($hotelKeywords as $keyword) {
                if ($this->isKeywordNearCity($message, $keyword, $cityNameLower)) {
                    $cityMentionedWithHotel = true;
                    $hasSpecificHotelCities = true;
                    break;
                }
            }
            
            // If no specific keywords found, check general context
            if (!$cityMentionedWithTour && !$cityMentionedWithHotel) {
                // If user didn't specify, include both for this city
                $cityMentionedWithTour = true;
                $cityMentionedWithHotel = true;
            }
            
            // Get tours for this city if mentioned with tour keywords
            if ($cityMentionedWithTour) {
                $cityTours = $this->dbService->getTours(
                    $cityId,
                    $entities['duration'],
                    $entities['budget'],
                    6,
                    $entities['price_condition']
                );
                
                if (!empty($cityTours)) {
                    $tours = array_merge($tours, $cityTours);
                    $tourCities[] = $cityName;
                }
            }
            
            // Get hotels for this city if mentioned with hotel keywords
            if ($cityMentionedWithHotel) {
                $cityHotels = $this->dbService->getHotels(
                    $cityId,
                    6,
                    $entities['rating'],
                    $entities['budget'],
                    $entities['price_condition']
                );
                
                if (!empty($cityHotels)) {
                    $hotels = array_merge($hotels, $cityHotels);
                    $hotelCities[] = $cityName;
                }
            }
        }
        
        // Limit results to avoid overwhelming response
        $result['data']['tours'] = array_slice($tours, 0, 10);
        $result['data']['hotels'] = array_slice($hotels, 0, 10);
        
        // Build appropriate response message
        if (!empty($tours) || !empty($hotels)) {
            $result['match_level'] = 'mixed_city_match';
            $result['fallback_message'] = $this->buildMixedSearchMessage($tourCities, $hotelCities, $tours, $hotels);
            $result['suggestions'] = $this->generateMultiCityMixedSuggestions($tourCities, $hotelCities, $entities);
            
            // Set layout type based on service types found
            if (!empty($tours) && !empty($hotels)) {
                $result['layout_type'] = 'mixed_content'; // Tours and hotels side by side
            } else {
                $result['layout_type'] = 'multi_city'; // Multi-city same service type
            }
            
            Logger::debug("Multi-city mixed search completed", [
                'tour_cities' => $tourCities,
                'hotel_cities' => $hotelCities,
                'tours_found' => count($tours),
                'hotels_found' => count($hotels)
            ]);
        } else {
            $result['match_level'] = 'no_city_match';
            $allCities = array_unique(array_column($entities['cities'], 'name'));
            $cityList = implode(', ', $allCities);
            $result['fallback_message'] = "I don't have tours or hotels for $cityList in our database. However, I can help you plan a custom itinerary.";
            $result['suggestions'] = [
                "Help me plan a custom itinerary for these cities",
                "Show me tours and hotels in other Vietnamese cities",
                "Find general travel information about these destinations",
                "Get travel tips for multi-city trips"
            ];
            
            Logger::debug("No mixed results found for cities", ['cities' => $allCities]);
        }
    }

    // NEW: Perform multi-city tour search with city-based layout
    private function performVietnameseTourSearch(&$result, $entities) {
        // Check if this is a multi-city tour search
        if (count($entities['cities']) > 1) {
            $this->performMultiCityTourSearch($result, $entities);
            return;
        }
        
        // Single city tour search (existing logic)
        $cityName = $entities['cities'][0]['name'];
        $cityId = $entities['cities'][0]['id'];
        
        // Try exact match first (city + duration + budget)
        if ($entities['duration'] || $entities['budget']) {
            $exactTours = $this->dbService->getTours(
                $cityId, 
                $entities['duration'], 
                $entities['budget'], 
                6, 
                $entities['price_condition']
            );
            if (!empty($exactTours)) {
                $result['data']['tours'] = $exactTours;
                $result['match_level'] = 'exact';
                $durationText = $entities['duration'] ? "{$entities['duration']}-day " : "";
                $result['fallback_message'] = "Perfect! Here are {$durationText}tours in $cityName:";
                Logger::debug("Exact tour match found", ['count' => count($exactTours)]);
                return;
            }
        }
        
        // Try city match
        $cityTours = $this->dbService->getTours($cityId);
        if (!empty($cityTours)) {
            $result['data']['tours'] = $cityTours;
            $result['match_level'] = 'same_city';
            
            if ($entities['duration']) {
                $result['fallback_message'] = "I couldn't find exact {$entities['duration']}-day tours in $cityName, but here are other tour options:";
                $result['suggestions'] = [
                    "Show me {$entities['duration']}-day tours in other cities",
                    "Help me plan a custom {$entities['duration']}-day itinerary for $cityName",
                    "Find hotels in $cityName"
                ];
            } else {
                $result['fallback_message'] = "Here are available tours in $cityName:";
            }
            Logger::debug("City tour match found", ['count' => count($cityTours)]);
        } else {
            $result['match_level'] = 'no_city_match';
            $result['fallback_message'] = "I don't have tour packages for $cityName in our database. However, I can help you plan a custom itinerary.";
            $result['suggestions'] = [
                "Help me plan a custom itinerary for $cityName",
                "Show me tours in other Vietnamese cities",
                "Find general travel information about $cityName"
            ];
            Logger::debug("No tour match found for city", ['city' => $cityName]);
        }
    }

    // NEW: Handle multi-city tour searches
    private function performMultiCityTourSearch(&$result, $entities) {
        $toursByCity = [];
        $allTours = [];
        $cityNames = [];
        
        foreach ($entities['cities'] as $city) {
            $cityName = $city['name'];
            $cityId = $city['id'];
            $cityNames[] = $cityName;
            
            $cityTours = $this->dbService->getTours(
                $cityId,
                $entities['duration'],
                $entities['budget'],
                6,
                $entities['price_condition']
            );
            
            if (!empty($cityTours)) {
                // Add city grouping information to each tour
                foreach ($cityTours as &$tour) {
                    $tour['city_group'] = $cityName;
                }
                unset($tour);
                
                $toursByCity[$cityName] = $cityTours;
                $allTours = array_merge($allTours, $cityTours);
            }
        }
        
        if (!empty($allTours)) {
            $result['data']['tours'] = $allTours;
            $result['data']['tours_by_city'] = $toursByCity; // NEW: Grouped data for multi-city layout
            $result['match_level'] = 'multi_city_tours';
            $result['layout_type'] = 'multi_city_tours'; // NEW: Specific layout type
            
            $cityList = implode(' and ', $cityNames);
            $totalTours = count($allTours);
            $result['fallback_message'] = "Here are tours available in $cityList ($totalTours tours found):";
            
            $result['suggestions'] = $this->generateMultiCityTourSuggestions($cityNames, $entities);
            
            Logger::debug("Multi-city tour search completed", [
                'cities' => $cityNames,
                'tours_by_city' => array_map('count', $toursByCity),
                'total_tours' => $totalTours
            ]);
        } else {
            $result['match_level'] = 'no_city_match';
            $cityList = implode(', ', $cityNames);
            $result['fallback_message'] = "I don't have tour packages for $cityList in our database. However, I can help you plan custom itineraries.";
            $result['suggestions'] = [
                "Help me plan custom itineraries for these cities",
                "Show me tours in other Vietnamese cities",
                "Find general travel information about these destinations",
                "Get travel tips for multi-city trips"
            ];
            
            Logger::debug("No tours found for multi-city search", ['cities' => $cityNames]);
        }
    }

    // NEW: Handle multi-city hotel searches 
    private function performVietnameseHotelSearch(&$result, $entities) {
        // Check if this is a multi-city hotel search
        if (count($entities['cities']) > 1) {
            $this->performMultiCityHotelSearch($result, $entities);
            return;
        }
        
        // Single city hotel search (existing logic)
        $cityName = $entities['cities'][0]['name'];
        $cityId = $entities['cities'][0]['id'];
        $hotels = $this->dbService->getHotels(
            $cityId, 
            6, 
            $entities['rating'], 
            $entities['budget'], 
            $entities['price_condition']
        );
        
        if (!empty($hotels)) {
            $result['data']['hotels'] = $hotels;
            $result['match_level'] = 'exact';
            $result['fallback_message'] = "Here are accommodations in $cityName:";
            Logger::debug("Hotels found", ['count' => count($hotels)]);
        } else {
            $result['match_level'] = 'no_city_match';
            $result['fallback_message'] = "I don't have hotel listings for $cityName in our database. I recommend checking online booking platforms.";
            $result['suggestions'] = [
                "Show me hotels in other Vietnamese cities",
                "Help me plan activities in $cityName",
                "Get general travel advice for $cityName"
            ];
            Logger::debug("No hotels found for city", ['city' => $cityName]);
        }
    }

    // NEW: Handle multi-city hotel searches
    private function performMultiCityHotelSearch(&$result, $entities) {
        $hotelsByCity = [];
        $allHotels = [];
        $cityNames = [];
        
        foreach ($entities['cities'] as $city) {
            $cityName = $city['name'];
            $cityId = $city['id'];
            $cityNames[] = $cityName;
            
            $cityHotels = $this->dbService->getHotels(
                $cityId,
                6,
                $entities['rating'],
                $entities['budget'],
                $entities['price_condition']
            );
            
            if (!empty($cityHotels)) {
                // Add city grouping information to each hotel
                foreach ($cityHotels as &$hotel) {
                    $hotel['city_group'] = $cityName;
                }
                unset($hotel);
                
                $hotelsByCity[$cityName] = $cityHotels;
                $allHotels = array_merge($allHotels, $cityHotels);
            }
        }
        
        if (!empty($allHotels)) {
            $result['data']['hotels'] = $allHotels;
            $result['data']['hotels_by_city'] = $hotelsByCity; // NEW: Grouped data for multi-city layout
            $result['match_level'] = 'multi_city_hotels';
            $result['layout_type'] = 'multi_city_hotels'; // NEW: Specific layout type
            
            $cityList = implode(' and ', $cityNames);
            $totalHotels = count($allHotels);
            $result['fallback_message'] = "Here are hotels available in $cityList ($totalHotels hotels found):";
            
            $result['suggestions'] = $this->generateMultiCityHotelSuggestions($cityNames, $entities);
            
            Logger::debug("Multi-city hotel search completed", [
                'cities' => $cityNames,
                'hotels_by_city' => array_map('count', $hotelsByCity),
                'total_hotels' => $totalHotels
            ]);
        } else {
            $result['match_level'] = 'no_city_match';
            $cityList = implode(', ', $cityNames);
            $result['fallback_message'] = "I don't have hotel listings for $cityList in our database. I recommend checking online booking platforms.";
            $result['suggestions'] = [
                "Show me hotels in other Vietnamese cities",
                "Help me plan activities in these cities",
                "Get general travel advice for these destinations",
                "Find tours in these cities"
            ];
            
            Logger::debug("No hotels found for multi-city search", ['cities' => $cityNames]);
        }
    }

    // NEW: Generate suggestions for multi-city tour searches
    private function generateMultiCityTourSuggestions($cityNames, $entities) {
        $suggestions = [];
        
        if (!empty($cityNames)) {
            $suggestions[] = "Focus on tours in " . $cityNames[0] . " only";
            if (count($cityNames) > 1) {
                $suggestions[] = "Find hotels in " . $cityNames[1];
            }
        }
        
        if ($entities['duration']) {
            $nextDuration = $entities['duration'] + 1;
            $suggestions[] = "Find {$nextDuration}-day tours";
        } else {
            $suggestions[] = "Show 3-day tour packages";
        }
        
        $suggestions[] = "Plan multi-city itinerary";
        
        return array_slice($suggestions, 0, 4);
    }

    // NEW: Generate suggestions for multi-city hotel searches
    private function generateMultiCityHotelSuggestions($cityNames, $entities) {
        $suggestions = [];
        
        if (!empty($cityNames)) {
            $suggestions[] = "Focus on hotels in " . $cityNames[0] . " only";
            if (count($cityNames) > 1) {
                $suggestions[] = "Find tours in " . $cityNames[1];
            }
        }
        
        if ($entities['rating']) {
            $lowerRating = max(1, $entities['rating'] - 1);
            $suggestions[] = "Show {$lowerRating}+ star hotels";
        } else {
            $suggestions[] = "Show 4+ star hotels";
        }
        
        $suggestions[] = "Compare prices between cities";
        
        return array_slice($suggestions, 0, 4);
    }
    
    /**
     * Perform general mixed search for both tours and hotels across all cities
     */
    private function performGeneralMixedSearch(&$result, $entities) {
        // Get tours from multiple cities
        $toursByCity = $this->dbService->getToursGroupedByCity(
            $entities['duration'], 
            $entities['budget'], 
            $entities['price_condition'],
            25 // Reduced to make room for hotels
        );
        
        // Get hotels from multiple cities
        $hotelsByCity = $this->dbService->getHotelsGroupedByCity(
            $entities['rating'],
            $entities['budget'],
            $entities['price_condition'],
            25 // Reduced to make room for tours
        );
        
        // Select diverse tours and hotels
        $selectedTours = $this->selectDiverseResults($toursByCity, 8, 'tourid');
        $selectedHotels = $this->selectDiverseResults($hotelsByCity, 8, 'hotelid');
        
        $result['data']['tours'] = $selectedTours;
        $result['data']['hotels'] = $selectedHotels;
        
        if (!empty($selectedTours) || !empty($selectedHotels)) {
            $result['match_level'] = 'mixed_general_search';
            $result['layout_type'] = 'mixed_content'; // Tours and hotels side by side
            
            $tourCount = count($selectedTours);
            $hotelCount = count($selectedHotels);
            $totalCities = count(array_unique(array_merge(array_keys($toursByCity), array_keys($hotelsByCity))));
            
            if ($tourCount > 0 && $hotelCount > 0) {
                $result['fallback_message'] = "Here are tours and hotels available across $totalCities cities in Vietnam:";
            } elseif ($tourCount > 0) {
                $result['fallback_message'] = "Here are available tours across Vietnam (hotels data limited):";
            } elseif ($hotelCount > 0) {
                $result['fallback_message'] = "Here are available hotels across Vietnam (tour data limited):";
            }
            
            // Generate suggestions
            $allCities = array_unique(array_merge(array_keys($toursByCity), array_keys($hotelsByCity)));
            $result['suggestions'] = $this->generateMixedSearchSuggestions(null, $entities, $tourCount > 0, $hotelCount > 0, $allCities);
            
            Logger::debug("General mixed search completed", [
                'tours' => $tourCount,
                'hotels' => $hotelCount,
                'cities' => $totalCities
            ]);
        } else {
            $result['match_level'] = 'no_results';
            $result['fallback_message'] = $this->buildNoResultsMessage('tours and hotels', $entities);
            $result['suggestions'] = [
                'Show all available tours and hotels',
                'Adjust your search criteria',
                'Try specific cities',
                'Browse by different price ranges'
            ];
        }
    }
    
    /**
     * Helper method to select diverse results from grouped data
     */
    private function selectDiverseResults($groupedData, $totalLimit, $idField) {
        $selected = [];
        $maxPerCity = 2; // Take at most 2 from each city initially
        
        // First pass: take up to 2 from each city
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
                    
                    // Check if this item is already selected
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
    
    /**
     * Generate suggestions for mixed searches
     */
    private function generateMixedSearchSuggestions($cityName = null, $entities = [], $hasTours = false, $hasHotels = false, $cities = []) {
        $suggestions = [];
        
        if ($cityName) {
            // City-specific suggestions
            if ($hasHotels && !$hasHotels) {
                $suggestions[] = "Find hotels in $cityName";
            }
            if ($hasTours && !$hasTours) {
                $suggestions[] = "Find tours in $cityName";
            }
            $suggestions[] = "Get travel tips for $cityName";
            $suggestions[] = "Plan itinerary for $cityName";
        } else {
            // General suggestions
            if (!empty($cities)) {
                $suggestions[] = "Focus on " . $cities[0] . " only";
                if (count($cities) > 1) {
                    $suggestions[] = "Show options in " . $cities[1];
                }
            }
            
            if ($entities['duration']) {
                $nextDuration = $entities['duration'] + 1;
                $suggestions[] = "Find {$nextDuration}-day packages";
            } else {
                $suggestions[] = "Show 3-day tour packages";
            }
            
            if ($entities['rating']) {
                $lowerRating = max(1, $entities['rating'] - 1);
                $suggestions[] = "Include {$lowerRating}+ star hotels";
            } else {
                $suggestions[] = "Show 4+ star accommodations";
            }
        }
        
        // Fill remaining slots with generic suggestions
        $genericSuggestions = [
            "Compare prices across cities",
            "Best time to visit Vietnam",
            "Budget travel tips",
            "Luxury travel options",
            "Family-friendly destinations",
            "Adventure travel packages"
        ];
        
        while (count($suggestions) < 4 && !empty($genericSuggestions)) {
            $suggestion = array_shift($genericSuggestions);
            if (!in_array($suggestion, $suggestions)) {
                $suggestions[] = $suggestion;
            }
        }
        
        return array_slice($suggestions, 0, 4);
    }
    
    /**
     * Enhanced general tour search with better filtering and city grouping
     */
    private function performGeneralTourSearch(&$result, $entities) {
        // Use the new grouped method for better city distribution
        $toursByCity = $this->dbService->getToursGroupedByCity(
            $entities['duration'], 
            $entities['budget'], 
            $entities['price_condition'],
            50
        );
        
        if (!empty($toursByCity)) {
            // Select tours from different cities to show variety
            $selectedTours = [];
            $maxToursPerCity = 3; // Increased to show more variety
            $totalLimit = 15; // Increased limit for list queries
            
            // First pass: take up to 3 tours from each city
            foreach ($toursByCity as $cityName => $cityTours) {
                $taken = 0;
                foreach ($cityTours as $tour) {
                    if ($taken < $maxToursPerCity && count($selectedTours) < $totalLimit) {
                        $selectedTours[] = $tour;
                        $taken++;
                    }
                }
            }
            
            // Second pass: fill remaining slots if needed
            if (count($selectedTours) < $totalLimit) {
                foreach ($toursByCity as $cityName => $cityTours) {
                    foreach ($cityTours as $tour) {
                        if (count($selectedTours) >= $totalLimit) break 2;
                        
                        // Check if this tour is already selected
                        $alreadySelected = false;
                        foreach ($selectedTours as $selectedTour) {
                            if ($selectedTour['tourid'] === $tour['tourid']) {
                                $alreadySelected = true;
                                break;
                            }
                        }
                        
                        if (!$alreadySelected) {
                            $selectedTours[] = $tour;
                        }
                    }
                }
            }
            
            $result['data']['tours'] = $selectedTours;
            $result['match_level'] = 'general_search';
            
            // Create descriptive message based on filters
            $description = $this->buildTourSearchDescription($entities, $toursByCity);
            $result['fallback_message'] = $description;
            
            // Generate suggestions based on found tours
            $cities = array_keys($toursByCity);
            $result['suggestions'] = $this->generateTourSearchSuggestions($cities, $entities);
            
            Logger::debug("General tour search completed", [
                'total_found' => array_sum(array_map('count', $toursByCity)),
                'selected' => count($selectedTours),
                'cities' => count($toursByCity),
                'duration' => $entities['duration'],
                'budget' => $entities['budget'],
                'price_condition' => $entities['price_condition']
            ]);
        } else {
            $result['match_level'] = 'no_results';
            $result['fallback_message'] = $this->buildNoResultsMessage('tours', $entities);
            $result['suggestions'] = [
                'Show all available tours',
                'Adjust your budget range',
                'Try different durations',
                'Browse tours by city'
            ];
        }
    }
    
    /**
     * Enhanced general hotel search with better filtering and city grouping
     */
    private function performGeneralHotelSearch(&$result, $entities) {
        // Use the new grouped method for better city distribution
        $hotelsByCity = $this->dbService->getHotelsGroupedByCity(
            $entities['rating'],
            $entities['budget'],
            $entities['price_condition'],
            50
        );
        
        if (!empty($hotelsByCity)) {
            // Select hotels from different cities to show variety
            $selectedHotels = [];
            $maxHotelsPerCity = 3; // Increased to show more variety
            $totalLimit = 15; // Increased limit for list queries
            
            // First pass: take up to 3 hotels from each city
            foreach ($hotelsByCity as $cityName => $cityHotels) {
                $taken = 0;
                foreach ($cityHotels as $hotel) {
                    if ($taken < $maxHotelsPerCity && count($selectedHotels) < $totalLimit) {
                        $selectedHotels[] = $hotel;
                        $taken++;
                    }
                }
            }
            
            // Second pass: fill remaining slots if needed
            if (count($selectedHotels) < $totalLimit) {
                foreach ($hotelsByCity as $cityName => $cityHotels) {
                    foreach ($cityHotels as $hotel) {
                        if (count($selectedHotels) >= $totalLimit) break 2;
                        
                        // Check if this hotel is already selected
                        $alreadySelected = false;
                        foreach ($selectedHotels as $selectedHotel) {
                            if ($selectedHotel['hotelid'] === $hotel['hotelid']) {
                                $alreadySelected = true;
                                break;
                            }
                        }
                        
                        if (!$alreadySelected) {
                            $selectedHotels[] = $hotel;
                        }
                    }
                }
            }
            
            $result['data']['hotels'] = $selectedHotels;
            $result['match_level'] = 'general_search';
            
            // Create descriptive message based on filters
            $description = $this->buildHotelSearchDescription($entities, $hotelsByCity);
            $result['fallback_message'] = $description;
            
            // Generate suggestions based on found hotels
            $cities = array_keys($hotelsByCity);
            $result['suggestions'] = $this->generateHotelSearchSuggestions($cities, $entities);
            
            Logger::debug("General hotel search completed", [
                'total_found' => array_sum(array_map('count', $hotelsByCity)),
                'selected' => count($selectedHotels),
                'cities' => count($hotelsByCity),
                'rating' => $entities['rating'],
                'budget' => $entities['budget'],
                'price_condition' => $entities['price_condition']
            ]);
        } else {
            $result['match_level'] = 'no_results';
            $result['fallback_message'] = $this->buildNoResultsMessage('hotels', $entities);
            $result['suggestions'] = [
                'Show all available hotels',
                'Adjust your rating requirements',
                'Try different price ranges',
                'Browse hotels by city'
            ];
        }
    }
    
    /**
     * Build descriptive message for tour search results
     */
    private function buildTourSearchDescription($entities, $toursByCity) {
        $description = "Here are available tours";
        $filters = [];
        $cities = array_keys($toursByCity);
        
        if ($entities['duration']) {
            $filters[] = "{$entities['duration']}-day";
        }
        
        if ($entities['budget'] && $entities['price_condition']) {
            $formattedBudget = number_format($entities['budget']);
            $condition = $entities['price_condition'] === 'under' ? 'under' : 'over';
            $filters[] = "$condition {$formattedBudget} VND";
        } elseif ($entities['budget']) {
            $formattedBudget = number_format($entities['budget']);
            $filters[] = "around {$formattedBudget} VND";
        }
        
        if (!empty($filters)) {
            $description .= " " . implode(" and ", $filters);
        }
        
        if (count($cities) > 1) {
            $description .= " across " . count($cities) . " cities in Vietnam";
            
            // List the cities
            if (count($cities) <= 5) {
                $description .= " (" . implode(", ", array_slice($cities, 0, 5)) . ")";
            } else {
                $description .= " (including " . implode(", ", array_slice($cities, 0, 3)) . " and " . (count($cities) - 3) . " more)";
            }
            $description .= ":";
        } else {
            $description .= " in " . $cities[0] . ":";
        }
        
        return $description;
    }
    
    /**
     * Build descriptive message for hotel search results
     */
    private function buildHotelSearchDescription($entities, $hotelsByCity) {
        $description = "Here are available hotels";
        $filters = [];
        $cities = array_keys($hotelsByCity);
        
        if ($entities['rating']) {
            $filters[] = "with {$entities['rating']}+ star rating";
        }
        
        if ($entities['budget'] && $entities['price_condition']) {
            $formattedBudget = number_format($entities['budget']);
            $condition = $entities['price_condition'] === 'under' ? 'under' : 'over';
            $filters[] = "$condition {$formattedBudget} VND per night";
        } elseif ($entities['budget']) {
            $formattedBudget = number_format($entities['budget']);
            $filters[] = "around {$formattedBudget} VND per night";
        }
        
        if (!empty($filters)) {
            $description .= " " . implode(" and ", $filters);
        }
        
        if (count($cities) > 1) {
            $description .= " across " . count($cities) . " cities in Vietnam";
            
            // List the cities
            if (count($cities) <= 5) {
                $description .= " (" . implode(", ", array_slice($cities, 0, 5)) . ")";
            } else {
                $description .= " (including " . implode(", ", array_slice($cities, 0, 3)) . " and " . (count($cities) - 3) . " more)";
            }
            $description .= ":";
        } else {
            $description .= " in " . $cities[0] . ":";
        }
        
        return $description;
    }
    
    /**
     * Generate contextual suggestions for tour searches
     */
    private function generateTourSearchSuggestions($cities, $entities) {
        $suggestions = [];
        
        if (!empty($cities)) {
            $suggestions[] = "Show tours only in " . $cities[0];
            if (count($cities) > 1) {
                $suggestions[] = "Find hotels in " . $cities[1];
            }
        }
        
        if ($entities['duration']) {
            $nextDuration = $entities['duration'] + 1;
            $suggestions[] = "Find {$nextDuration}-day tours";
        } else {
            $suggestions[] = "Show 3-day tours";
        }
        
        if ($entities['budget']) {
            if ($entities['price_condition'] === 'under') {
                $suggestions[] = "Show tours over " . number_format($entities['budget']) . " VND";
            } else {
                $suggestions[] = "Show budget tours under 2,000,000 VND";
            }
        } else {
            $suggestions[] = "Show budget-friendly tours";
        }
        
        // Fill remaining slots with generic suggestions
        $genericSuggestions = [
            "Find hotels in these locations",
            "Show tours with different durations",
            "Best time to visit these destinations",
            "What's included in tour packages",
            "Compare tour prices by city"
        ];
        
        while (count($suggestions) < 4 && !empty($genericSuggestions)) {
            $suggestions[] = array_shift($genericSuggestions);
        }
        
        return array_slice($suggestions, 0, 4);
    }
    
    /**
     * Generate contextual suggestions for hotel searches
     */
    private function generateHotelSearchSuggestions($cities, $entities) {
        $suggestions = [];
        
        if (!empty($cities)) {
            $suggestions[] = "Show hotels only in " . $cities[0];
            if (count($cities) > 1) {
                $suggestions[] = "Find tours in " . $cities[1];
            }
        }
        
        if ($entities['rating']) {
            $lowerRating = max(1, $entities['rating'] - 1);
            $suggestions[] = "Show {$lowerRating}+ star hotels";
        } else {
            $suggestions[] = "Show 4+ star hotels";
        }
        
        if ($entities['budget']) {
            if ($entities['price_condition'] === 'under') {
                $suggestions[] = "Show hotels over " . number_format($entities['budget']) . " VND";
            } else {
                $suggestions[] = "Show budget hotels under 1,500,000 VND";
            }
        } else {
            $suggestions[] = "Show luxury accommodations";
        }
        
        // Fill remaining slots with generic suggestions
        $genericSuggestions = [
            "Find tours in these locations",
            "Show different rating categories",
            "Best neighborhoods to stay",
            "Hotel amenities and facilities",
            "Compare hotel prices by city"
        ];
        
        while (count($suggestions) < 4 && !empty($genericSuggestions)) {
            $suggestions[] = array_shift($genericSuggestions);
        }
        
        return array_slice($suggestions, 0, 4);
    }
    
    /**
     * Build message for no results found
     */
    private function buildNoResultsMessage($type, $entities) {
        $filters = [];
        
        if ($entities['duration']) {
            $filters[] = "{$entities['duration']}-day duration";
        }
        
        if ($entities['rating']) {
            $filters[] = "{$entities['rating']}+ star rating";
        }
        
        if ($entities['budget'] && $entities['price_condition']) {
            $formattedBudget = number_format($entities['budget']);
            $condition = $entities['price_condition'] === 'under' ? 'under' : 'over';
            $filters[] = "$condition {$formattedBudget} VND";
        }
        
        if (empty($filters)) {
            return "I couldn't find any $type matching your criteria. Let me help you find alternatives.";
        }
        
        $filterText = implode(", ", $filters);
        return "I couldn't find any $type with $filterText. Let me suggest some alternatives.";
    }
    
    /**
     * Perform general search for both tours and hotels
     */
    private function performGeneralSearch(&$result, $entities) {
        // Get a mix of tours and hotels
        $result['data']['tours'] = $this->dbService->getTours(null, null, null, 8);
        $result['data']['hotels'] = $this->dbService->getHotels(null, 8);
        
        $result['match_level'] = 'general';
        $result['fallback_message'] = "I can help you plan trips to Vietnam (with specific tour and hotel data) or international destinations. Here's what's available:";
        $result['suggestions'] = [
            'Show me tours in specific cities',
            'Find hotels with specific ratings',
            'Plan a trip with specific duration',
            'Help me choose a destination'
        ];
        
        Logger::debug("General mixed search completed", [
            'tours' => count($result['data']['tours']),
            'hotels' => count($result['data']['hotels'])
        ]);
    }
    
    private function performVietnameseDestinationSearch(&$result, $entities) {
        $cityName = $entities['cities'][0]['name'];
        $cityId = $entities['cities'][0]['id'];
        
        $result['data']['tours'] = $this->dbService->getTours($cityId, null, null, 3);
        $result['data']['hotels'] = $this->dbService->getHotels($cityId, 3);
        
        $result['match_level'] = 'destination_info';
        $result['fallback_message'] = "Here's what we offer in $cityName:";
        Logger::debug("Destination info retrieved", [
            'tours' => count($result['data']['tours']),
            'hotels' => count($result['data']['hotels'])
        ]);
    }
    
    private function performVietnameseGeneralSearch(&$result, $entities) {
        $cityId = $entities['cities'][0]['id'];
        
        $result['data']['tours'] = $this->dbService->getTours($cityId, null, null, 3);
        $result['data']['hotels'] = $this->dbService->getHotels($cityId, 3);
        
        $cityName = $entities['cities'][0]['name'];
        $result['match_level'] = 'general_mixed';
        $result['fallback_message'] = "Here's what we offer in $cityName:";
        Logger::debug("General search completed", [
            'tours' => count($result['data']['tours']),
            'hotels' => count($result['data']['hotels'])
        ]);
    }
}
