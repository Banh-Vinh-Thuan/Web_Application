<?php

class IntentAnalyzer {
    
    private static $intents = [
        'mixed_search' => [
            'keywords' => ['tour and hotel', 'hotel and tour', 'tours and hotels', 'hotels and tours', 'both tour', 'both hotel', 'accommodation and tour', 'tour and accommodation'],
            'patterns' => [
                '/(?:tour|tours).*(?:and|&).*(?:hotel|hotels)/',
                '/(?:hotel|hotels).*(?:and|&).*(?:tour|tours)/',
                '/(?:show|find|list).*(?:both|all).*(?:tour|hotel)/',
                '/(?:tour|hotel).*(?:and|&).*(?:accommodation|stay)/',
                '/(?:accommodation|stay).*(?:and|&).*(?:tour|package)/'
            ],
            'weight' => 10
        ],
        'tour_search' => [
            'keywords' => ['tour', 'tours', 'package', 'packages', 'trip', 'trips', 'travel package', 'excursion', 'sightseeing', 'list tours', 'show tours', 'find tours'],
            'patterns' => [
                '/\btours?\b/',  // FIXED: Match 'tour' as whole word
                '/\btour\s+in\b/',  // FIXED: Explicit "tour in" pattern
                '/(?:list|show|find)\s+(?:all\s+)?tours?/', 
                '/tours?\s*(?:with|having|of|for)/', 
                '/\d+\s*(?:day|days)\s*tours?/', 
                '/tours?\s*(?:under|over|below|above)/', 
                '/tours?\s*(?:price|cost|budget)/',
                '/(?:all|available)\s*tours?/',
                '/tours?\s*list/',
                '/\b(?:package|trip)\s+(?:in|to|for)\b/'  // FIXED: Package/trip patterns
            ],
            'weight' => 8  // INCREASED weight
        ],
        'hotel_search' => [
            'keywords' => ['hotel', 'hotels', 'accommodation', 'stay', 'lodge', 'resort', 'booking', 'list hotels', 'show hotels'],
            'patterns' => [
                '/\bhotels?\b/',  // Match 'hotel' as whole word
                '/\bhotel\s+in\b/',  // Explicit "hotel in" pattern
                '/(?:list|show|find)\s+(?:all\s+)?hotels?/', 
                '/hotels?\s*(?:with|having|of|for)/', 
                '/\d+\s*(?:star|stars)\s*hotels?/', 
                '/hotels?\s*(?:under|over|below|above)/', 
                '/hotels?\s*(?:price|cost|budget)/',
                '/(?:all|available)\s*hotels?/',
                '/hotels?\s*list/',
                '/\d+\s*(?:star|sao)\s*(?:hotel|rating)/'
            ],
            'weight' => 6
        ],
        'destination_info' => [
            'keywords' => ['destination', 'place', 'city', 'location', 'visit', 'about', 'information'],
            'patterns' => ['/about.*city/', '/tell.*about/', '/plan.*trip/', '/what.*do.*in/'],
            'weight' => 3
        ],
        'price_inquiry' => [
            'keywords' => ['price', 'cost', 'budget', 'expensive', 'cheap', 'affordable', 'how much'],
            'patterns' => [
                '/(?:under|below)\s*\d+/', 
                '/(?:over|above)\s*\d+/', 
                '/(?:price|cost)\s*(?:under|over|below|above)/',
                '/\d+\s*(?:vnd|million|triệu)/',
                '/how much/',
                '/price.*for/',
                '/cost.*of/',
                '/budget.*for/'
            ],
            'weight' => 4  // REDUCED weight to avoid overriding tour/hotel intent
        ],
        'duration_inquiry' => [
            'keywords' => ['duration', 'long', 'days', 'time', 'how long', 'how many days'],
            'patterns' => [
                '/\d+\s*(?:day|days)/',
                '/how long/',
                '/duration.*of/',
                '/how many days/'
            ],
            'weight' => 4  // REDUCED weight
        ],
        'rating_inquiry' => [
            'keywords' => ['rating', 'star', 'stars', 'rated', 'review', 'quality'],
            'patterns' => [
                '/\d+\s*(?:star|stars)/',
                '/rating.*\d+/',
                '/rated.*\d+/',
                '/\d+\s*star\s*(?:hotel|rating)/'
            ],
            'weight' => 4  // REDUCED weight
        ]
    ];

    public static function analyzeIntent($message) {
        $message = strtolower($message);
        $scores = [];
        
        // Initialize scores
        foreach (self::$intents as $intent => $data) {
            $scores[$intent] = 0;
        }
        
        // STEP 1: Primary keyword scoring - ENHANCED
        foreach (self::$intents as $intent => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (strpos($message, strtolower($keyword)) !== false) {
                    $scores[$intent] += 3; // Increased keyword weight
                }
            }
        }
        
        // STEP 2: Pattern matching scoring
        foreach (self::$intents as $intent => $data) {
            foreach ($data['patterns'] as $pattern) {
                if (preg_match($pattern, $message)) {
                    $scores[$intent] += 5; // Increased pattern weight
                }
            }
        }
        
        // STEP 3: Apply special scoring rules - ENHANCED
        $scores = self::applySpecialRules($message, $scores);
        
        // STEP 4: Debug logging
        error_log("Intent Analysis Debug:");
        error_log("Message: " . $message);
        foreach ($scores as $intent => $score) {
            if ($score > 0) {
                error_log("$intent: $score");
            }
        }
        
        $maxScore = max($scores);
        $topIntent = $maxScore > 0 ? array_keys($scores, $maxScore)[0] : 'general';
        
        error_log("Selected Intent: $topIntent (Score: $maxScore)");
        
        return $topIntent;
    }

    private static function applySpecialRules($message, $scores) {
        // RULE 1: Explicit tour/hotel detection - HIGHEST PRIORITY
        if (preg_match('/\btour\s+in\b/', $message) || preg_match('/\btours?\s+in\b/', $message)) {
            $scores['tour_search'] += 15; // VERY HIGH boost for "tour in"
            error_log("Applied Rule: Explicit tour pattern detected (+15)");
        }
        
        if (preg_match('/\bhotel\s+in\b/', $message) || preg_match('/\bhotels?\s+in\b/', $message)) {
            $scores['hotel_search'] += 15; // VERY HIGH boost for "hotel in"
            error_log("Applied Rule: Explicit hotel pattern detected (+15)");
        }
        
        // RULE 2: Enhanced mixed search detection
        if (preg_match('/(?:tour|tours).*(?:and|&).*(?:hotel|hotels)|(?:hotel|hotels).*(?:and|&).*(?:tour|tours)/', $message)) {
            $scores['mixed_search'] += 12;
            error_log("Applied Rule: Mixed search detected (+12)");
        }

        // RULE 3: Multi-city detection with type priority
        $cityCount = self::countCitiesInMessage($message);
        if ($cityCount >= 2) {
            if (preg_match('/\btours?\b/', $message)) {
                $scores['tour_search'] += 8;
                error_log("Applied Rule: Multi-city tour detected (+8)");
            }
            if (preg_match('/\bhotels?\b/', $message)) {
                $scores['hotel_search'] += 8;
                error_log("Applied Rule: Multi-city hotel detected (+8)");
            }
        }
        
        // RULE 4: Context-specific scoring
        // Duration context strongly favors tours
        if (preg_match('/(\d+)\s*(?:day|days)/', $message)) {
            if (strpos($message, 'tour') !== false || strpos($message, 'package') !== false) {
                $scores['tour_search'] += 10;
                error_log("Applied Rule: Duration with tour context (+10)");
            } else {
                $scores['tour_search'] += 6; // Default duration boost for tours
                error_log("Applied Rule: Duration context favors tours (+6)");
            }
        }
        
        // Rating context strongly favors hotels
        if (preg_match('/(\d+)\s*(?:star|stars|sao)/', $message)) {
            if (strpos($message, 'hotel') !== false) {
                $scores['hotel_search'] += 10;
                error_log("Applied Rule: Rating with hotel context (+10)");
            } else {
                $scores['hotel_search'] += 6; // Default rating boost for hotels
                error_log("Applied Rule: Rating context favors hotels (+6)");
            }
        }
        
        // RULE 5: Price context detection - REFINED
        if (preg_match('/(?:price|cost|budget).*(?:under|below|over|above)/', $message) || 
            preg_match('/(?:under|below|over|above).*\d+/', $message) ||
            preg_match('/\d+.*(?:vnd|million|triệu)/', $message)) {
            
            // Check explicit context first
            if (strpos($message, 'hotel') !== false) {
                $scores['hotel_search'] += 7;
                error_log("Applied Rule: Price with hotel context (+7)");
            } elseif (strpos($message, 'tour') !== false || strpos($message, 'package') !== false) {
                $scores['tour_search'] += 7;
                error_log("Applied Rule: Price with tour context (+7)");
            } else {
                // Default: tours are more commonly price-filtered
                $scores['tour_search'] += 4;
                $scores['hotel_search'] += 2;
                error_log("Applied Rule: Price context default boost (tour +4, hotel +2)");
            }
        }
        
        // RULE 6: Listing queries enhancement
        if (preg_match('/(?:list|show|find|all)\s+(?:tours?|hotels?)/', $message)) {
            if (strpos($message, 'tour') !== false && strpos($message, 'hotel') !== false) {
                $scores['mixed_search'] += 8;
                error_log("Applied Rule: List both types (+8 mixed)");
            } elseif (strpos($message, 'tour') !== false) {
                $scores['tour_search'] += 6;
                error_log("Applied Rule: List tours (+6)");
            } elseif (strpos($message, 'hotel') !== false) {
                $scores['hotel_search'] += 6;
                error_log("Applied Rule: List hotels (+6)");
            }
        }
        
        // RULE 7: Penalty for conflicting signals
        // If both tour and hotel keywords exist but no explicit "and", penalize mixed_search slightly
        if (strpos($message, 'tour') !== false && strpos($message, 'hotel') !== false && 
            !preg_match('/(?:and|&)/', $message)) {
            $scores['mixed_search'] -= 2;
            error_log("Applied Rule: Conflicting signals penalty (-2 mixed)");
        }
        
        return $scores;
    }
    
    // HELPER: Count cities in message
    private static function countCitiesInMessage($message) {
        $vietnameseCities = [
            'ho chi minh', 'saigon', 'hanoi', 'da nang', 'hue', 'nha trang', 
            'da lat', 'phu quoc', 'hoi an', 'can tho', 'phu yen', 'ha giang', 'tay bac'
        ];
        
        $cityCount = 0;
        $message = strtolower($message);
        
        foreach ($vietnameseCities as $city) {
            if (strpos($message, $city) !== false) {
                $cityCount++;
            }
        }
        
        return $cityCount;
    }

    public static function extractEntities($message, $vietnameseCities) {
        $entities = [
            'cities' => [],
            'duration' => null,
            'budget' => null,
            'rating' => null,
            'price_condition' => null,
            'rating_condition' => 'exact', // ADDED: Default to exact rating match
            'preferences' => [],
            'is_international' => false,
            'raw_message' => $message
        ];
        
        // Extract cities
        $cityResults = self::extractAllCitiesFromMessage($message, $vietnameseCities);
        if (!empty($cityResults)) {
            $entities['cities'] = $cityResults['cities'];
            $entities['is_international'] = $cityResults['has_international'];
        }
        
        // Extract duration
        $entities['duration'] = self::extractDuration($message);
        
        // Extract rating with condition - ENHANCED
        $ratingData = self::extractRatingWithCondition($message);
        $entities['rating'] = $ratingData['rating'];
        $entities['rating_condition'] = $ratingData['condition'];
        
        // Extract budget and price condition - ENHANCED for multi-city
        $budgetData = self::extractBudgetWithMultiCity($message, $entities['cities']);
        $entities['budget'] = $budgetData['budget'];
        $entities['price_condition'] = $budgetData['condition'];
        
        // Extract preferences
        $entities['preferences'] = self::extractPreferences($message);
        
        return $entities;
    }

    // ENHANCED: Extract rating with condition detection
    private static function extractRatingWithCondition($message) {
        $rating = null;
        $condition = 'exact'; // Default to exact match
        
        // Patterns for minimum rating
        if (preg_match('/(?:at least|minimum|min|above)\s*(\d+)\s*(?:star|stars|sao)/i', $message, $matches)) {
            $rating = intval($matches[1]);
            $condition = 'minimum';
        }
        // Patterns for maximum rating  
        elseif (preg_match('/(?:under|below|max|maximum)\s*(\d+)\s*(?:star|stars|sao)/i', $message, $matches)) {
            $rating = intval($matches[1]);
            $condition = 'maximum';
        }
        // Patterns for exact rating (default)
        elseif (preg_match('/(\d+)\s*(?:star|stars|sao)/i', $message, $matches)) {
            $rating = intval($matches[1]);
            $condition = 'exact';
        }
        
        // Validate rating range
        if ($rating && ($rating < 1 || $rating > 5)) {
            $rating = null;
            $condition = 'exact';
        }
        
        return ['rating' => $rating, 'condition' => $condition];
    }

    private static function extractBudgetWithMultiCity($message, $cities) {
        $budget = null;
        $condition = null;
        
        // Check if this is a multi-city query with conditions
        $isMultiCity = count($cities) >= 2;
        
        // Vietnamese patterns with conditions
        if (preg_match('/(?:dưới|under|below)\s*(\d+)\s*(?:triệu|million)/i', $message, $matches)) {
            $budget = intval($matches[1]) * 1000000;
            $condition = 'under';
        } elseif (preg_match('/(?:trên|above|over)\s*(\d+)\s*(?:triệu|million)/i', $message, $matches)) {
            $budget = intval($matches[1]) * 1000000;
            $condition = 'over';
        }
        // VND patterns with conditions - FIXED decimal handling
        elseif (preg_match('/(?:under|below|dưới)\s*(\d+(?:[,.]?\d{3})*)\s*(?:vnd|dong)?/i', $message, $matches)) {
            $budget = intval(str_replace([',', '.'], '', $matches[1]));
            $condition = 'under';
        } elseif (preg_match('/(?:over|above|trên)\s*(\d+(?:[,.]?\d{3})*)\s*(?:vnd|dong)?/i', $message, $matches)) {
            $budget = intval(str_replace([',', '.'], '', $matches[1]));
            $condition = 'over';
        }
        // General budget patterns
        elseif (preg_match('/(\d+)\s*(?:triệu|million)/i', $message, $matches)) {
            $budget = intval($matches[1]) * 1000000;
            if ($isMultiCity) {
                $condition = 'under';
            }
        } elseif (preg_match('/(\d+(?:[,.]?\d{3})*)\s*(?:vnd|dong)/i', $message, $matches)) {
            $budget = intval(str_replace([',', '.'], '', $matches[1]));
            if ($isMultiCity) {
                $condition = 'under';
            }
        }
        // Short forms
        elseif (preg_match('/(?:under|below|dưới)\s*(\d+)\s*tr(?:iệu)?/i', $message, $matches)) {
            $budget = intval($matches[1]) * 1000000;
            $condition = 'under';
        } elseif (preg_match('/(?:over|above|trên)\s*(\d+)\s*tr(?:iệu)?/i', $message, $matches)) {
            $budget = intval($matches[1]) * 1000000;
            $condition = 'over';
        } elseif (preg_match('/(\d+)\s*tr(?:iệu)?/i', $message, $matches)) {
            $budget = intval($matches[1]) * 1000000;
            if ($isMultiCity) {
                $condition = 'under';
            }
        }
        
        return ['budget' => $budget, 'condition' => $condition];
    }

    private static function extractDuration($message) {
        if (preg_match('/(\d+)\s*(?:day|days|ngày)/i', $message, $matches) ||
            preg_match('/(\d+)[-\s]*(?:day|days|ngày)/i', $message, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }

    private static function extractPreferences($message) {
        $preferences = [
            'luxury', 'budget', 'family', 'romantic', 'adventure', 
            'cultural', 'beach', 'mountain', 'backpacker', 'solo',
            'cao cấp', 'tiết kiệm', 'gia đình', 'lãng mạn', 'mạo hiểm',
            'văn hóa', 'biển', 'núi', 'du lịch bụi'
        ];
        
        $messageLower = strtolower($message);
        $foundPreferences = [];
        
        foreach ($preferences as $pref) {
            if (strpos($messageLower, strtolower($pref)) !== false) {
                $foundPreferences[] = $pref;
            }
        }
        
        return $foundPreferences;
    }

    private static function extractAllCitiesFromMessage($message, $vietnameseCities) {
        $normalizedMessage = self::normalizeCityName($message);
        $foundCities = [];
        $hasInternational = false;

        // Prioritize multi-word combinations (longest matches first)
        uksort($vietnameseCities, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($vietnameseCities as $cityName => $cityData) {
            if (strpos($normalizedMessage, $cityName) !== false) {
                if (!self::cityAlreadyFoundById($foundCities, $cityData['id'])) {
                    $foundCities[] = $cityData;
                }
            }
        }

        // Handle potential international cities
        if (empty($foundCities)) {
            preg_match_all('/\b[A-Z][a-z]+(?:\s[A-Z][a-z]+)*\b/', $message, $matches);
            
            if (!empty($matches[0])) {
                foreach ($matches[0] as $potentialCity) {
                    if (!in_array(strtolower($potentialCity), ['find', 'show', 'tell', 'what', 'how'])) {
                        $foundCities[] = ['id' => null, 'name' => $potentialCity];
                        $hasInternational = true;
                    }
                }
            }
        }
        
        return [
            'cities' => $foundCities,
            'has_international' => $hasInternational
        ];
    }

    private static function cityAlreadyFoundById($foundCities, $cityId) {
        foreach ($foundCities as $city) {
            if ($city['id'] === $cityId) {
                return true;
            }
        }
        return false;
    }

    private static function normalizeCityName($cityName) {
        $cityName = strtolower(trim($cityName));
        $cityName = preg_replace('/[^\p{L}\p{N}\s]/u', '', $cityName);
        $cityName = preg_replace('/\s+/', ' ', $cityName);
        return trim($cityName);
    }
}