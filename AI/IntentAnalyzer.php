<?php

require_once './GeminiService.php';
require_once './Logger.php';

class FewShotIntentAnalyzer {
    
    private $geminiService;
    private $fewShotExamples;
    private $intentDefinitions;
    
    public function __construct() {
        $this->geminiService = new GeminiService();
        $this->initializeFewShotExamples();
        $this->initializeIntentDefinitions();
    }
    
    /**
     * Initialize few-shot examples for each intent class
     */
    private function initializeFewShotExamples() {
        $this->fewShotExamples = [
            'tour_search' => [
                "Show me tours in Da Nang",
                "I want to find 3-day package tours",
                "What tours are available in Hanoi?",
                "Looking for sightseeing trips in Hoi An",
                "Can you recommend some excursions in Nha Trang?"
            ],
            'hotel_search' => [
                "Find 4-star hotels in Ho Chi Minh City",
                "I need accommodation in Da Lat",
                "Show me luxury resorts in Phu Quoc",
                "What hotels are available with good ratings?",
                "Looking for a place to stay in Hanoi"
            ],
            'mixed_search' => [
                "I need both tours and hotels in Da Nang",
                "Show me hotel and tour packages",
                "Find me accommodation and sightseeing options",
                "I want to book both hotel and tours in Hoi An",
                "Looking for complete travel package with stay and activities"
            ],
            'price_inquiry' => [
                "What's the price for this tour?",
                "How much does it cost?",
                "Show me tours under 5 million VND",
                "I have a budget of 3 million",
                "Find affordable options below 2 million"
            ],
            'rating_inquiry' => [
                "Show me 5-star hotels only",
                "What's the rating of this hotel?",
                "I want highly rated accommodations",
                "Find hotels with at least 4 stars",
                "Show me top-rated options"
            ],
            'availability_inquiry' => [
                "Is this tour available next week?",
                "Do you have rooms available in December?",
                "Check availability for 3 people",
                "Can I book this for tomorrow?",
                "What dates are open?"
            ],
            'comparison_request' => [
                "Compare these two hotels",
                "What's the difference between tour A and B?",
                "Which one is better?",
                "Show me pros and cons",
                "Help me choose between these options"
            ],
            'general' => [
                "Hello, how can you help me?",
                "Tell me about Vietnam tourism",
                "What services do you offer?",
                "I'm planning a trip",
                "Thanks for your help"
            ]
        ];
    }
    
    /**
     * Initialize clear definitions for each intent
     */
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
    
    /**
     * Main intent analysis using few-shot prompting
     */
    public function analyzeIntent($message) {
        try {
            Logger::debug("Analyzing intent with few-shot learning", ['message' => $message]);
            
            $prompt = $this->buildFewShotPrompt($message);
            
            try {
                $response = $this->geminiService->generateText($prompt, [
                    'temperature' => 0.1,
                    'maxTokens' => 100
                ]);
                
                // CRITICAL: Check if response is valid
                if (empty(trim($response))) {
                    Logger::warning("Gemini returned empty response for intent, using fallback");
                    return $this->fallbackToRuleBase($message);
                }
                
                $intent = $this->parseIntentResponse($response);
                $confidence = $this->extractConfidence($response);
                
                // VALIDATION: Ensure intent is valid
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
                Logger::error("Gemini API failed in analyzeIntent", [
                    'error' => $apiError->getMessage(),
                    'message' => $message
                ]);
                return $this->fallbackToRuleBase($message);
            }
            
        } catch (Exception $e) {
            Logger::error("Intent analysis completely failed", [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
            
            // LAST RESORT: Return general intent
            return [
                'intent' => 'general',
                'confidence' => 0.3,
                'method' => 'emergency_fallback'
            ];
        }
    }
    
    /**
     * Build few-shot prompt following Brown et al. (2020) methodology
     */
    private function buildFewShotPrompt($message) {
        $prompt = "You are an expert intent classifier for a Vietnam travel booking system.\n\n";
        $prompt .= "INTENT DEFINITIONS:\n";
        
        foreach ($this->intentDefinitions as $intent => $definition) {
            $prompt .= "- {$intent}: {$definition}\n";
        }
        
        $prompt .= "\n=== FEW-SHOT EXAMPLES ===\n\n";
        
        // Add examples for each intent
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
    
    /**
     * Parse intent from LLM response
     */
    private function parseIntentResponse($response) {
        $response = strtolower(trim($response));
        
        // Extract intent name (first word usually)
        foreach (array_keys($this->intentDefinitions) as $intent) {
            if (strpos($response, $intent) === 0) {
                return $intent;
            }
        }
        
        // Fallback: find any mention of intent
        foreach (array_keys($this->intentDefinitions) as $intent) {
            if (strpos($response, $intent) !== false) {
                return $intent;
            }
        }
        
        return 'general';
    }
    
    /**
     * Extract confidence score from response
     */
    private function extractConfidence($response) {
        // Look for confidence indicators in response
        if (preg_match('/confidence[:\s]+(\d+\.?\d*)%?/i', $response, $matches)) {
            return floatval($matches[1]) / 100;
        }
        
        // Default confidence based on response clarity
        if (strlen($response) < 50 && $this->parseIntentResponse($response) !== 'general') {
            return 0.85; // High confidence for clear, short responses
        }
        
        return 0.70; // Moderate default confidence
    }
    
    /**
     * Fallback to rule-based classification if LLM fails
     */
    private function fallbackToRuleBase($message) {
        Logger::warning("Using fallback rule-based intent detection");
        
        $message = strtolower($message);
        
        // Simple keyword matching as fallback
        if (preg_match('/\b(tour|tours|trip|package)\b/', $message) && 
            preg_match('/\b(hotel|accommodation|stay)\b/', $message)) {
            return ['intent' => 'mixed_search', 'confidence' => 0.6, 'method' => 'fallback'];
        }
        
        if (preg_match('/\b(hotel|accommodation|stay|resort)\b/', $message)) {
            return ['intent' => 'hotel_search', 'confidence' => 0.6, 'method' => 'fallback'];
        }
        
        if (preg_match('/\b(tour|tours|trip|package|excursion)\b/', $message)) {
            return ['intent' => 'tour_search', 'confidence' => 0.6, 'method' => 'fallback'];
        }
        
        if (preg_match('/\b(price|cost|budget|cheap|expensive)\b/', $message)) {
            return ['intent' => 'price_inquiry', 'confidence' => 0.6, 'method' => 'fallback'];
        }
        
        if (preg_match('/\b(star|rating|rated|quality)\b/', $message)) {
            return ['intent' => 'rating_inquiry', 'confidence' => 0.6, 'method' => 'fallback'];
        }
        
        return ['intent' => 'general', 'confidence' => 0.5, 'method' => 'fallback'];
    }
    
    /**
     * Extract entities from message using few-shot approach
     */
    public function extractEntities($message, $vietnameseCities) {
        try {
            $prompt = $this->buildEntityExtractionPrompt($message, $vietnameseCities);
            $response = $this->geminiService->generateText($prompt, [
                'temperature' => 0.1,
                'maxTokens' => 300
            ]);
            
            return $this->parseEntityResponse($response, $message, $vietnameseCities);
            
        } catch (Exception $e) {
            Logger::error("Entity extraction failed", ['error' => $e->getMessage()]);
            return $this->fallbackEntityExtraction($message, $vietnameseCities);
        }
    }
    
    /**
     * Build entity extraction prompt with examples
     */
    private function buildEntityExtractionPrompt($message, $vietnameseCities) {
        $cityNames = array_keys($vietnameseCities);
        
        $prompt = "Extract travel-related entities from user messages.\n\n";
        $prompt .= "Available cities: " . implode(', ', $cityNames) . "\n\n";
        $prompt .= "=== EXAMPLES ===\n\n";
        
        $prompt .= "Message: \"Show me 3-day tours in Da Nang under 5 million\"\n";
        $prompt .= "Entities: {\"cities\": [\"Da Nang\"], \"duration\": 3, \"budget\": 5000000, \"price_condition\": \"under\"}\n\n";
        
        $prompt .= "Message: \"Find 4-star hotels in Hanoi and Ho Chi Minh City\"\n";
        $prompt .= "Entities: {\"cities\": [\"Hanoi\", \"Ho Chi Minh City\"], \"rating\": 4, \"rating_condition\": \"minimum\"}\n\n";
        
        $prompt .= "Message: \"I want luxury tours in Hoi An\"\n";
        $prompt .= "Entities: {\"cities\": [\"Hoi An\"], \"preferences\": [\"luxury\"]}\n\n";
        
        $prompt .= "=== NOW EXTRACT ===\n\n";
        $prompt .= "Message: \"{$message}\"\n";
        $prompt .= "Entities: ";
        
        return $prompt;
    }
    
    /**
     * Parse entity extraction response
     */
    private function parseEntityResponse($response, $message, $vietnameseCities) {
        // Try to parse JSON response
        if (preg_match('/\{[^}]+\}/', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $this->normalizeEntities($json, $vietnameseCities);
            }
        }
        
        // Fallback to rule-based extraction
        return $this->fallbackEntityExtraction($message, $vietnameseCities);
    }
    
    /**
     * Normalize extracted entities
     */
    private function normalizeEntities($entities, $vietnameseCities) {
        $normalized = [
            'cities' => [],
            'duration' => $entities['duration'] ?? null,
            'rating' => $entities['rating'] ?? null,
            'rating_condition' => $entities['rating_condition'] ?? 'minimum',
            'budget' => $entities['budget'] ?? null,
            'price_condition' => $entities['price_condition'] ?? null,
            'preferences' => $entities['preferences'] ?? [],
            'is_international' => false,
            'raw_message' => ''
        ];
        
        // Map city names to city data
        if (!empty($entities['cities'])) {
            foreach ($entities['cities'] as $cityName) {
                $cityKey = $this->findCityKey($cityName, $vietnameseCities);
                if ($cityKey && isset($vietnameseCities[$cityKey])) {
                    $normalized['cities'][] = $vietnameseCities[$cityKey];
                }
            }
        }
        
        return $normalized;
    }
    
    /**
     * Find city key in vietnameseCities array
     */
    private function findCityKey($cityName, $vietnameseCities) {
        $cityName = strtolower(trim($cityName));
        
        foreach (array_keys($vietnameseCities) as $key) {
            if (strtolower($key) === $cityName) {
                return $key;
            }
        }
        
        return null;
    }
    
    /**
     * Fallback entity extraction using rules
     */
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
            'raw_message' => $message
        ];
    }
    
    // Keep existing extraction methods as fallbacks
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
            'under' => '/(?:dưới|under|below)\s*(\d+)\s*(?:triệu|million)/i',
            'over' => '/(?:trên|above|over)\s*(\d+)\s*(?:triệu|million)/i',
            'default' => '/(\d+)\s*(?:triệu|million)/i'
        ];
        
        foreach ($patterns as $condition => $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return [
                    'budget' => intval($matches[1]) * 1000000,
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