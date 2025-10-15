<?php

require_once './Gemini.php';
require_once './Logger.php';
require_once './config.php';

class FewShotIntentAnalyzer {
    
    private $geminiService;
    private $fewShotExamples;
    private $intentDefinitions;
    
    public function __construct() {
        $this->geminiService = new GeminiService();
        $this->initializeFewShotExamples();
        $this->initializeIntentDefinitions();
    }
    
    private function initializeFewShotExamples() {
        $this->fewShotExamples = [
            'tour_search' => [
                "Show me tours in Da Nang",
                "Tour in Hanoi and Hue",
                "I want to find 3-day package tours",
                "What tours are available in Hanoi?",
                "Looking for sightseeing trips in Hoi An",
                "Tour Nha Trang and Phu Quoc with 3 days",
                "Tour Ha Giang and Tay Bac with 3 days and under 10 millions"
            ],
            'hotel_search' => [
                "Find 4-star hotels in Ho Chi Minh City",
                "Hotel in Phu Quoc and Can Tho",
                "I need accommodation in Da Lat",
                "Show me luxury resorts in Phu Quoc",
                "Hotel Can Tho for 3 stars and hotel Hoi An 4 stars",
                "What hotels are available with good ratings?"
            ],
            'mixed_search' => [
                "Tour in Nha Trang and hotel in Da Lat",
                "Hotel in city1 and tour in city2",
                "I need both tours and hotels in Da Nang",
                "Show me hotel and tour packages",
                "Tour Nha Trang under 5 millions and hotel Da Lat 5 stars",
                "Find me accommodation and sightseeing options"
            ],
            'price_inquiry' => [
                "What's the price for this tour?",
                "Show me tours under 5 million VND",
                "I have a budget of 3 million",
                "Find affordable options below 2 million",
                "Tour under 10 millions"
            ],
            'rating_inquiry' => [
                "Show me 5-star hotels only",
                "Hotel with 4 stars",
                "I want highly rated accommodations",
                "Find hotels with at least 4 stars",
                "3 stars hotel"
            ],
            'duration_inquiry' => [
                "Tour with 3 days",
                "3-day tours in Hanoi",
                "I want a 2-day trip",
                "Tour for 4 days",
                "Multi-day tour packages"
            ],
            'general' => [
                "Hello, how can you help me?",
                "Tell me about Vietnam tourism",
                "What services do you offer?",
                "Thanks for your help"
            ]
        ];
    }
    
    private function initializeIntentDefinitions() {
        $this->intentDefinitions = [
            'tour_search' => 'User wants to find, browse, or get information about tours, trips, packages, excursions, or travel activities.',
            'hotel_search' => 'User wants to find, browse, or get information about hotels, accommodations, resorts, or places to stay.',
            'mixed_search' => 'User explicitly wants both tours AND hotels together, or complete travel packages including accommodation and activities.',
            'price_inquiry' => 'User asks about cost, price, budget, or wants to filter by price range.',
            'rating_inquiry' => 'User asks about ratings, quality, stars, or wants to filter by rating.',
            'availability_inquiry' => 'User asks about availability, booking dates, capacity, or scheduling.',
            'comparison_request' => 'User wants to compare options, see differences, or needs help choosing between alternatives.',
            'general' => 'General conversation, greetings, thanks, or unclear intent that doesn\'t fit other categories.'
        ];
    }
    
    public function analyzeIntent($message) {
        try {
            Logger::debug("Analyzing intent with few-shot learning", ['message' => $message]);
            
            $prompt = $this->buildFewShotPrompt($message);
            
            try {
                $response = $this->geminiService->generateText($prompt, [
                    'temperature' => 0.1,
                    'maxTokens' => 100
                ]);
                
                if (empty(trim($response))) {
                    Logger::warning("Gemini returned empty response for intent, using fallback");
                    return $this->fallbackToRuleBase($message);
                }
                
                $intent = $this->parseIntentResponse($response);
                $confidence = $this->extractConfidence($response);
                
                if (!array_key_exists($intent, $this->intentDefinitions)) {
                    Logger::warning("Invalid intent detected: $intent, using fallback");
                    return $this->fallbackToRuleBase($message);
                }
                
                Logger::info("Intent detected", [
                    'intent' => $intent,
                    'confidence' => $confidence,
                    'message' => $message
                ]);
                
                return [
                    'intent' => $intent,
                    'confidence' => $confidence,
                    'method' => 'few_shot_learning'
                ];
                
            } catch (Exception $apiError) {
                // ✅ FIX: Log detailed error but continue with fallback
                Logger::error("Gemini API failed in analyzeIntent", [
                    'error' => $apiError->getMessage(),
                    'message' => substr($message, 0, 100),
                    'error_type' => get_class($apiError)
                ]);
                
                return $this->fallbackToRuleBase($message);
            }
            
        } catch (Exception $e) {
            Logger::error("Intent analysis completely failed", [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
            
            return [
                'intent' => 'general',
                'confidence' => 0.3,
                'method' => 'emergency_fallback'
            ];
        }
    }
    
    private function buildFewShotPrompt($message) {
        $prompt = "You are an expert intent classifier for a Vietnam travel booking system.\n\n";
        $prompt .= "INTENT DEFINITIONS:\n";
        
        foreach ($this->intentDefinitions as $intent => $definition) {
            $prompt .= "- {$intent}: {$definition}\n";
        }
        
        $prompt .= "\n=== FEW-SHOT EXAMPLES ===\n\n";
        
        foreach ($this->fewShotExamples as $intent => $examples) {
            foreach (array_slice($examples, 0, 3) as $example) {
                $prompt .= "User message: \"{$example}\"\n";
                $prompt .= "Intent: {$intent}\n\n";
            }
        }
        
        $prompt .= "=== NOW CLASSIFY THIS MESSAGE ===\n\n";
        $prompt .= "User message: \"{$message}\"\n";
        $prompt .= "Intent: ";
        
        return $prompt;
    }
    
    private function parseIntentResponse($response) {
        $response = strtolower(trim($response));
        
        foreach (array_keys($this->intentDefinitions) as $intent) {
            if (strpos($response, $intent) === 0) {
                return $intent;
            }
        }
        
        foreach (array_keys($this->intentDefinitions) as $intent) {
            if (strpos($response, $intent) !== false) {
                return $intent;
            }
        }
        
        return 'general';
    }
    
    private function extractConfidence($response) {
        if (preg_match('/confidence[:\s]+(\d+\.?\d*)%?/i', $response, $matches)) {
            return floatval($matches[1]) / 100;
        }
        
        if (strlen($response) < 50 && $this->parseIntentResponse($response) !== 'general') {
            return 0.85;
        }
        
        return 0.70;
    }
    
    private function fallbackToRuleBase($message) {
        Logger::warning("Using enhanced fallback rule-based intent detection");
        
        $messageLower = strtolower($message);
        
        $cities = $this->extractCityNames($messageLower);
        $hasTwoOrMoreCities = count($cities) >= 2;
        
        $hasTourKeyword = preg_match('/\b(tour|tours|trip|package)\b/', $messageLower);
        $hasHotelKeyword = preg_match('/\b(hotel|hotels|accommodation|stay|resort)\b/', $messageLower);
        
        $hasMixedPattern = preg_match('/\b(tour|tours)\b.*\band\b.*\b(hotel|hotels)\b/i', $messageLower) ||
                        preg_match('/\b(hotel|hotels)\b.*\band\b.*\b(tour|tours)\b/i', $messageLower);
        
        $hasConditions = $this->detectConditionsInMessage($messageLower);
        
        if ($hasMixedPattern || ($hasTourKeyword && $hasHotelKeyword && $hasTwoOrMoreCities)) {
            return [
                'intent' => 'mixed_search', 
                'confidence' => 0.85, 
                'method' => 'fallback', 
                'has_conditions' => $hasConditions
            ];
        }
        
        if ($hasTwoOrMoreCities && $hasConditions) {
            if ($hasTourKeyword && !$hasHotelKeyword) {
                return [
                    'intent' => 'tour_search', 
                    'confidence' => 0.80, 
                    'method' => 'fallback', 
                    'has_conditions' => true, 
                    'multi_city' => true
                ];
            }
            if ($hasHotelKeyword && !$hasTourKeyword) {
                return [
                    'intent' => 'hotel_search', 
                    'confidence' => 0.80, 
                    'method' => 'fallback', 
                    'has_conditions' => true, 
                    'multi_city' => true
                ];
            }
        }
        
        if ($hasTwoOrMoreCities) {
            if ($hasTourKeyword && !$hasHotelKeyword) {
                return [
                    'intent' => 'tour_search', 
                    'confidence' => 0.75, 
                    'method' => 'fallback', 
                    'has_conditions' => false, 
                    'multi_city' => true
                ];
            }
            if ($hasHotelKeyword && !$hasTourKeyword) {
                return [
                    'intent' => 'hotel_search', 
                    'confidence' => 0.75, 
                    'method' => 'fallback', 
                    'has_conditions' => false, 
                    'multi_city' => true
                ];
            }
        }
        
        if ($hasTourKeyword) {
            return [
                'intent' => 'tour_search', 
                'confidence' => 0.70, 
                'method' => 'fallback', 
                'has_conditions' => $hasConditions, 
                'multi_city' => false
            ];
        }
        
        if ($hasHotelKeyword) {
            return [
                'intent' => 'hotel_search', 
                'confidence' => 0.70, 
                'method' => 'fallback', 
                'has_conditions' => $hasConditions, 
                'multi_city' => false
            ];
        }
        
        return [
            'intent' => 'general', 
            'confidence' => 0.50, 
            'method' => 'fallback', 
            'has_conditions' => false, 
            'multi_city' => false
        ];
    }

    private function detectConditionsInMessage($messageLower) {
        $hasPrice = preg_match('/\b(under|over|below|above)\s+\d+/i', $messageLower) ||
                    preg_match('/\d+\s+(million|millions)/i', $messageLower);
        $hasDuration = preg_match('/\b(for|with|of)\s+\d+\s+(day|days)/i', $messageLower);
        $hasRating = preg_match('/\b(for|with)?\s*\d+\s+star/i', $messageLower);
        return $hasPrice || $hasDuration || $hasRating;
    }

    private function extractCityNames($message) {
        $vietnameseCities = [
            'hanoi', 'ha noi', 'hà nội',
            'ho chi minh', 'saigon', 'hcmc',
            'da nang', 'danang', 'đà nẵng',
            'hue', 'huế',
            'nha trang', 'nhatrang',
            'hoi an', 'hoian', 'hội an',
            'da lat', 'dalat', 'đà lạt',
            'phu quoc', 'phuquoc', 'phú quốc',
            'can tho', 'cantho', 'cần thơ',
            'ha giang', 'hagiang', 'hà giang',
            'phu yen', 'phuyen', 'phú yên',
            'tay bac', 'taybac', 'tây bắc'
        ];
        
        $message = strtolower($message);
        $foundCities = [];
        
        foreach ($vietnameseCities as $city) {
            if (strpos($message, $city) !== false && !in_array($city, $foundCities)) {
                $foundCities[] = $city;
            }
        }
        
        return array_unique($foundCities);
    }

    public function extractEntities($message, $vietnameseCities) {
        $entities = $this->fallbackEntityExtraction($message, $vietnameseCities);
        
        $entities['excluded_cities'] = $this->extractExcludedCities($message, $vietnameseCities);
        $entities['has_conditions'] = $this->detectConditions($message);
        $entities['is_international'] = $this->detectOutOfScopeQuery($message, $entities['cities']);
        
        return $entities;
    }

    private function extractExcludedCities($message, $vietnameseCities): array {
        $excluded = [];
        $messageLower = strtolower($message);
        
        $patterns = [
            '/except\s+(?:for\s+)?([^,\.]+?)(?:\s+and|\s+or|,|\.|\s*$)/i',
            '/excluding\s+([^,\.]+?)(?:\s+and|\s+or|,|\.|\s*$)/i',
            '/without\s+([^,\.]+?)(?:\s+and|\s+or|,|\.|\s*$)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $messageLower, $matches)) {
                foreach ($matches[1] as $excludedCity) {
                    $excludedCityLower = strtolower(trim($excludedCity));
                    foreach ($vietnameseCities as $cityName => $cityData) {
                        if (strpos(strtolower($cityName), $excludedCityLower) !== false) {
                            $excluded[] = $cityData;
                            break;
                        }
                    }
                }
            }
        }
        
        return array_unique($excluded, SORT_REGULAR);
    }

    private function detectOutOfScopeQuery($message, $foundVNCities): bool {
        $messageLower = strtolower($message);

        // Check for international destinations first
        if ($this->isInternationalQuery($messageLower)) {
            return true;
        }

        // If no Vietnam cities found in our database, check out-of-DB cities
        if (empty($foundVNCities)) {
            $outOfDBCities = Config::getOutOfDatabaseVietnameseCities();
            
            foreach ($outOfDBCities as $city) {
                if (strpos($messageLower, $city) !== false) {
                    Logger::info("Vietnam out-of-database city detected", [
                        'city' => $city, 
                        'message' => substr($message, 0, 100)
                    ]);
                    return true;
                }
            }
        }

        return false;
    }
  
    private function isInternationalQuery($messageLower): bool {
        // Expanded international locations list
        $internationalLocations = [
            // East Asia
            'korea', 'south korea', 'korean', 'seoul', 'busan', 'incheon', 'jeju',
            'japan', 'tokyo', 'osaka', 'kyoto', 'nagoya', 'hokkaido', 'fukuoka',
            'china', 'beijing', 'shanghai', 'guangzhou', 'shenzhen', 'chengdu',
            'hong kong', 'hongkong', 'macau', 'macao',
            'taiwan', 'taipei', 'kaohsiung', 'taichung',
            
            // Southeast Asia (excluding Vietnam)
            'thailand', 'bangkok', 'phuket', 'chiang mai', 'pattaya',
            'singapore',
            'malaysia', 'kuala lumpur', 'penang', 'langkawi',
            'indonesia', 'bali', 'jakarta', 'yogyakarta',
            'philippines', 'manila', 'cebu', 'boracay',
            'cambodia', 'siem reap', 'phnom penh', 'angkor',
            'laos', 'vientiane', 'luang prabang',
            'myanmar', 'yangon', 'mandalay', 'bagan',
            'brunei',
            
            // South Asia
            'india', 'new delhi', 'mumbai', 'bangalore', 'goa', 'jaipur',
            'nepal', 'kathmandu', 'pokhara',
            'sri lanka', 'colombo', 'kandy',
            'maldives', 'male',
            'bhutan', 'pakistan', 'bangladesh',
            
            // Middle East
            'uae', 'dubai', 'abu dhabi', 'emirates',
            'qatar', 'doha',
            'saudi arabia', 'riyadh', 'jeddah', 'mecca',
            'turkey', 'istanbul', 'ankara', 'antalya',
            'israel', 'jerusalem', 'tel aviv',
            'jordan', 'petra', 'oman', 'kuwait', 'bahrain',
            
            // Europe
            'france', 'paris', 'lyon', 'nice', 'marseille',
            'england', 'london', 'manchester', 'liverpool',
            'uk', 'britain', 'scotland', 'wales',
            'germany', 'berlin', 'munich', 'frankfurt', 'cologne',
            'italy', 'rome', 'milan', 'venice', 'florence', 'naples',
            'spain', 'madrid', 'barcelona', 'seville', 'valencia',
            'portugal', 'lisbon', 'porto',
            'netherlands', 'amsterdam', 'rotterdam',
            'switzerland', 'zurich', 'geneva', 'bern',
            'austria', 'vienna', 'salzburg',
            'belgium', 'brussels', 'bruges',
            'sweden', 'stockholm', 'gothenburg',
            'norway', 'oslo', 'bergen',
            'denmark', 'copenhagen',
            'finland', 'helsinki',
            'greece', 'athens', 'santorini', 'mykonos',
            'czech republic', 'prague',
            'poland', 'warsaw', 'krakow',
            'russia', 'moscow', 'st petersburg',
            
            // North America
            'usa', 'america', 'united states', 
            'new york', 'los angeles', 'chicago', 'san francisco', 
            'las vegas', 'miami', 'boston', 'seattle', 'washington dc',
            'canada', 'toronto', 'vancouver', 'montreal', 'ottawa',
            'mexico', 'cancun', 'mexico city',
            
            // Oceania
            'australia', 'sydney', 'melbourne', 'brisbane', 'perth', 'cairns',
            'new zealand', 'auckland', 'wellington', 'christchurch', 'queenstown',
            'fiji', 'tahiti',
            
            // South America
            'brazil', 'rio de janeiro', 'sao paulo', 'brasilia',
            'argentina', 'buenos aires', 'patagonia',
            'chile', 'santiago', 'peru', 'lima', 'machu picchu',
            'colombia', 'bogota', 'cartagena',
            
            // Africa
            'egypt', 'cairo', 'luxor', 'alexandria',
            'south africa', 'cape town', 'johannesburg',
            'morocco', 'marrakech', 'casablanca',
            'kenya', 'nairobi', 'tanzania', 'zanzibar',
            'ethiopia', 'tunisia', 'algeria'
        ];

        foreach ($internationalLocations as $location) {
            if (strpos($messageLower, $location) !== false) {
                Logger::info("Detected international location", [
                    'location' => $location,
                    'message_preview' => substr($messageLower, 0, 100)
                ]);
                return true;
            }
        }

        return false;
    }

    private function fallbackEntityExtraction($message, $vietnameseCities) {
        return [
            'cities' => $this->extractCities($message, $vietnameseCities),
            'duration' => $this->extractDuration($message),
            'rating' => $this->extractRating($message)['rating'],
            'rating_condition' => $this->extractRating($message)['condition'],
            'budget' => $this->extractBudget($message)['budget'],
            'price_condition' => $this->extractBudget($message)['condition'],
            'preferences' => $this->extractPreferences($message),
            'is_international' => false,
            'raw_message' => $message,
            'has_conditions' => $this->detectConditions($message)
        ];
    }
    
    private function detectConditions($message) {
        $messageLower = strtolower($message);
        $hasPrice = preg_match('/\b(under|over|below|above|budget)\s+\d+/i', $messageLower) ||
                    preg_match('/\d+\s+(million|triệu)/i', $messageLower);
        $hasDuration = preg_match('/\b(with|for|of)?\s*\d+\s+(day|days|ngày)\b/i', $messageLower);
        $hasRating = preg_match('/\b\d+\s+star/i', $messageLower) ||
                    preg_match('/\bstar\s+\d+/i', $messageLower) ||
                    preg_match('/\d+\s+sao/i', $messageLower);
        return $hasPrice || $hasDuration || $hasRating;
    }
    
    private function extractCities($message, $vietnameseCities) {
        $foundCities = [];
        $normalizedMessage = strtolower($message);
        
        foreach ($vietnameseCities as $cityName => $cityData) {
            if (strpos($normalizedMessage, strtolower($cityName)) !== false) {
                if (!in_array($cityData['id'], array_column($foundCities, 'id'))) {
                    $foundCities[] = $cityData;
                }
            }
        }
        
        return $foundCities;
    }
    
    private function extractRating($message) {
        $patterns = [
            'minimum' => '/(?:at least|minimum|min|above)\s*(\d+)\s*(?:star|sao)/i',
            'maximum' => '/(?:under|below|max)\s*(\d+)\s*(?:star|sao)/i',
            'exact' => '/(?:exactly|only)\s*(\d+)\s*(?:star|sao)/i',
            'default' => '/(\d+)\s*(?:star|sao)/i'
        ];
        
        foreach ($patterns as $condition => $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    return [
                        'rating' => $rating,
                        'condition' => $condition === 'default' ? 'minimum' : $condition
                    ];
                }
            }
        }
        
        return ['rating' => null, 'condition' => 'minimum'];
    }
    
    private function extractBudget($message) {
        $patterns = [
            'under' => '/(?:dưới|under|below)\s*([\d,.]+)\s*(?:triệu|million)/i',
            'over' => '/(?:trên|above|over)\s*([\d,.]+)\s*(?:triệu|million)/i',
            'default' => '/([\d,.]+)\s*(?:triệu|million)/i'
        ];
        
        foreach ($patterns as $condition => $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $amount = (float)str_replace(',', '', $matches[1]);
                return [
                    'budget' => $amount * 1000000,
                    'condition' => $condition === 'default' ? 'under' : $condition
                ];
            }
        }
        
        return ['budget' => null, 'condition' => null];
    }
    
    private function extractDuration($message) {
        if (preg_match('/(\d+)\s*(?:day|days|ngày)/i', $message, $matches)) {
            return intval($matches[1]);
        }
        return null;
    }
    
    private function extractPreferences($message) {
        $preferences = ['luxury', 'budget', 'family', 'romantic', 'adventure', 'cultural', 'beach'];
        $messageLower = strtolower($message);
        
        return array_values(array_filter($preferences, fn($pref) => 
            strpos($messageLower, $pref) !== false
        ));
    }
}
?>