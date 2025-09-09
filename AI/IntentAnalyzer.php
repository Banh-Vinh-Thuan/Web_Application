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
            'keywords' => ['tour', 'package', 'trip', 'travel package', 'excursion', 'sightseeing', 'list tours', 'show tours', 'find tours'],
            'patterns' => [
                '/(?:list|show|find)\s+(?:all\s+)?tours?/', 
                '/tours?\s*(?:with|having|of|for)/', 
                '/\d+\s*(?:day|days)\s*tours?/', 
                '/tours?\s*(?:under|over|below|above)/', 
                '/tours?\s*(?:price|cost|budget)/',
                '/(?:all|available)\s*tours?/',
                '/tours?\s*list/'
            ],
            'weight' => 6
        ],
        'hotel_search' => [
            'keywords' => ['hotel', 'hotels', 'accommodation', 'stay', 'lodge', 'resort', 'booking', 'list hotels', 'show hotels'],
            'patterns' => [
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
            'weight' => 5
        ],
        'duration_inquiry' => [
            'keywords' => ['duration', 'long', 'days', 'time', 'how long', 'how many days'],
            'patterns' => [
                '/\d+\s*(?:day|days)/',
                '/how long/',
                '/duration.*of/',
                '/how many days/'
            ],
            'weight' => 6
        ],
        'rating_inquiry' => [
            'keywords' => ['rating', 'star', 'stars', 'rated', 'review', 'quality'],
            'patterns' => [
                '/\d+\s*(?:star|stars)/',
                '/rating.*\d+/',
                '/rated.*\d+/',
                '/\d+\s*star\s*(?:hotel|rating)/'
            ],
            'weight' => 6
        ]
    ];

    public static function analyzeIntent($message) {
        $message = strtolower($message);
        $scores = [];
        
        foreach (self::$intents as $intent => $data) {
            $score = 0;
            
            // Score keywords
            foreach ($data['keywords'] as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $score += 2;
                }
            }
            
            // Score patterns
            foreach ($data['patterns'] as $pattern) {
                if (preg_match($pattern, $message)) {
                    $score += 4;
                }
            }
            
            $scores[$intent] = $score;
        }
        
        // Apply special scoring rules
        $scores = self::applySpecialRules($message, $scores);
        
        $maxScore = max($scores);
        return $maxScore > 0 ? array_keys($scores, $maxScore)[0] : 'general';
    }

    private static function applySpecialRules($message, $scores) {
        // Enhanced mixed search detection
        if (preg_match('/(?:tour|tours).*(?:and|&).*(?:hotel|hotels)|(?:hotel|hotels).*(?:and|&).*(?:tour|tours)/', $message)) {
            $scores['mixed_search'] += 10;
        }
        
        // Enhanced listing queries
        if (preg_match('/(?:list|show|find|all)\s+(?:tours?|hotels?)/', $message)) {
            if (strpos($message, 'tour') !== false && strpos($message, 'hotel') !== false) {
                $scores['mixed_search'] += 8;
            } elseif (strpos($message, 'tour') !== false) {
                $scores['tour_search'] += 5;
            } elseif (strpos($message, 'hotel') !== false) {
                $scores['hotel_search'] += 5;
            }
        }
        
        // Duration-based queries favor tours
        if (preg_match('/(\d+)\s*(?:day|days)/', $message)) {
            $scores['tour_search'] += 6;
            if (strpos($message, 'tour') !== false) {
                $scores['tour_search'] += 3;
            }
        }
        
        // Rating-based queries favor hotels
        if (preg_match('/(\d+)\s*(?:star|stars)/', $message)) {
            $scores['hotel_search'] += 6;
            if (strpos($message, 'hotel') !== false) {
                $scores['hotel_search'] += 3;
            }
        }
        
        // Price-based context detection
        if (preg_match('/(?:under|below|over|above|dưới|trên)\s*\d+/', $message) || 
            preg_match('/\d+\s*(?:triệu|million|vnd)/', $message)) {
            
            if (strpos($message, 'hotel') !== false || strpos($message, 'accommodation') !== false) {
                $scores['hotel_search'] += 5;
            } elseif (strpos($message, 'tour') !== false || strpos($message, 'package') !== false) {
                $scores['tour_search'] += 5;
            } else {
                $scores['tour_search'] += 3;
                $scores['hotel_search'] += 2;
            }
        }
        
        return $scores;
    }

    public static function extractEntities($message, $vietnameseCities) {
        $entities = [
            'cities' => [],
            'duration' => null,
            'budget' => null,
            'rating' => null,
            'price_condition' => null,
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
        
        // Extract rating
        $entities['rating'] = self::extractRating($message);
        
        // Extract budget and price condition
        $budgetData = self::extractBudget($message);
        $entities['budget'] = $budgetData['budget'];
        $entities['price_condition'] = $budgetData['condition'];
        
        // Extract preferences
        $entities['preferences'] = self::extractPreferences($message);
        
        return $entities;
    }

    private static function extractDuration($message) {
        if (preg_match('/(\d+)\s*(?:day|days|ngày)/i', $message, $matches) ||
            preg_match('/(\d+)[-\s]*(?:day|days|ngày)/i', $message, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }

    private static function extractRating($message) {
        if (preg_match('/(\d+)\s*(?:star|stars|sao)/i', $message, $matches)) {
            $rating = intval($matches[1]);
            return ($rating >= 1 && $rating <= 5) ? $rating : null;
        }
        return null;
    }

    private static function extractBudget($message) {
        $budget = null;
        $condition = null;
        
        // Vietnamese patterns with conditions
        if (preg_match('/(?:dưới|under|below)\s*(\d+)\s*(?:triệu|million)/i', $message, $matches)) {
            $budget = intval($matches[1]) * 1000000;
            $condition = 'under';
        } elseif (preg_match('/(?:trên|above|over)\s*(\d+)\s*(?:triệu|million)/i', $message, $matches)) {
            $budget = intval($matches[1]) * 1000000;
            $condition = 'over';
        } 
        // VND patterns with conditions
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
        } elseif (preg_match('/(\d+(?:[,.]?\d{3})*)\s*(?:vnd|dong)/i', $message, $matches)) {
            $budget = intval(str_replace([',', '.'], '', $matches[1]));
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
        }
        
        return ['budget' => $budget, 'condition' => $condition];
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
        $words = explode(' ', $normalizedMessage);
        $foundCities = [];
        $hasInternational = false;
        $usedPositions = [];
        
        // Multi-word combinations first (longest matches first)
        for ($length = 3; $length >= 1; $length--) {
            for ($i = 0; $i <= count($words) - $length; $i++) {
                // Skip if position already used
                $positionUsed = false;
                for ($j = $i; $j < $i + $length; $j++) {
                    if (in_array($j, $usedPositions)) {
                        $positionUsed = true;
                        break;
                    }
                }
                if ($positionUsed) continue;
                
                $cityCandidate = implode(' ', array_slice($words, $i, $length));
                
                if (isset($vietnameseCities[$cityCandidate])) {
                    $foundCities[] = $vietnameseCities[$cityCandidate];
                    for ($j = $i; $j < $i + $length; $j++) {
                        $usedPositions[] = $j;
                    }
                }
            }
        }
        
        // Look for capitalized words (potential international cities)
        preg_match_all('/\b\p{Lu}[\p{Ll}]+(?:\s+\p{Lu}[\p{Ll}]+)*\b/u', $message, $matches);
        if (!empty($matches[0])) {
            $commonWords = ['show', 'tour', 'hotel', 'find', 'list', 'and', 'the', 'in', 'to', 'for', 'with'];
            
            foreach ($matches[0] as $potentialCity) {
                $normalizedPotential = self::normalizeCityName($potentialCity);
                
                // Skip if already found or common word
                if (self::cityAlreadyFound($foundCities, $normalizedPotential) || 
                    in_array($normalizedPotential, $commonWords) || 
                    strlen($potentialCity) <= 2) {
                    continue;
                }
                
                $foundCities[] = ['name' => $potentialCity, 'id' => null];
                $hasInternational = true;
            }
        }
        
        // Handle "city1 and city2" pattern
        if (preg_match('/([a-zA-ZÀ-ÿ\s]+?)(?:\s+(?:and|và)\s+)([a-zA-ZÀ-ÿ\s]+)/iu', $message, $matches)) {
            $cities = [trim($matches[1]), trim($matches[2])];
            
            foreach ($cities as $cityName) {
                if (strlen($cityName) > 2) {
                    $normalized = self::normalizeCityName($cityName);
                    
                    if (!self::cityAlreadyFound($foundCities, $normalized)) {
                        if (isset($vietnameseCities[$normalized])) {
                            $foundCities[] = $vietnameseCities[$normalized];
                        } else {
                            $foundCities[] = ['name' => $cityName, 'id' => null];
                            $hasInternational = true;
                        }
                    }
                }
            }
        }
        
        return empty($foundCities) ? null : [
            'cities' => $foundCities,
            'has_international' => $hasInternational
        ];
    }

    private static function cityAlreadyFound($foundCities, $normalizedName) {
        foreach ($foundCities as $city) {
            if (self::normalizeCityName($city['name']) === $normalizedName) {
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