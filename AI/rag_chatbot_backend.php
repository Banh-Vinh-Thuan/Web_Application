<?php
session_start();

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$serverName = "localhost";
$dbUsername = "root";
$dbPassword = "4444";
$dbName = "travelscapes";

$conn = mysqli_connect($serverName, $dbUsername, $dbPassword, $dbName);

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit();
}

class OptimizedRAGTravelChatbot {
    private $db;
    private $userId;
    private $geminiApiKey;
    private $geminiApiUrl;
    
    // Vietnamese cities with database records
    private $vietnameseCities = [
        'ho chi minh' => ['id' => 11, 'name' => 'Ho Chi Minh City'], 
        'saigon' => ['id' => 11, 'name' => 'Ho Chi Minh City'],
        'hcmc' => ['id' => 11, 'name' => 'Ho Chi Minh City'],
        'nha trang' => ['id' => 12, 'name' => 'Nha Trang'], 
        'nhatrang' => ['id' => 12, 'name' => 'Nha Trang'],
        'hue' => ['id' => 13, 'name' => 'Hue'], 
        'phu yen' => ['id' => 14, 'name' => 'Phu Yen'],
        'phuyen' => ['id' => 14, 'name' => 'Phu Yen'], 
        'da lat' => ['id' => 15, 'name' => 'Da Lat'],
        'dalat' => ['id' => 15, 'name' => 'Da Lat'],
        'phu quoc' => ['id' => 16, 'name' => 'Phu Quoc'], 
        'phuquoc' => ['id' => 16, 'name' => 'Phu Quoc'],
        'hoi an' => ['id' => 17, 'name' => 'Hoi An'],
        'hoian' => ['id' => 17, 'name' => 'Hoi An'],
        'ha giang' => ['id' => 18, 'name' => 'Ha Giang'],
        'hagiang' => ['id' => 18, 'name' => 'Ha Giang'],
        'tay bac' => ['id' => 10, 'name' => 'Tay Bac'],
        'taybac' => ['id' => 10, 'name' => 'Tay Bac'],
        'northwest' => ['id' => 10, 'name' => 'Tay Bac'], 
        'sapa' => ['id' => 10, 'name' => 'Tay Bac'], 
        'fansipan' => ['id' => 10, 'name' => 'Tay Bac']
    ];
    
    public function __construct($db) {
        $this->db = $db;
        $this->userId = $_SESSION['user_id'] ?? 1;
        $this->geminiApiKey = "AIzaSyBKlus-HPPK2H14xstpE1VHsfkzbUkoRJA";
        $this->geminiApiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";
    }
    
    public function processMessage($message, $conversationHistory = []) {
        try {
            // Input validation and sanitization
            $message = trim($message);
            if (empty($message) || strlen($message) > 1000) {
                throw new InvalidArgumentException('Invalid message length');
            }
            
            // Basic XSS prevention
            $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
            
            // Handle simple greetings first
            if ($this->isSimpleGreeting($message)) {
                return $this->generateGreetingResponse();
            }
            
            // Analyze user intent and extract entities
            $intent = $this->analyzeIntent($message);
            $entities = $this->extractEntities($message);
            
            // Smart data retrieval with Vietnamese DB vs International Gemini fallback
            $retrievalResult = $this->retrieveRelevantData($intent, $entities, $message);
            
            // Generate contextual response
            $response = $this->generateContextualResponse($message, $retrievalResult, $conversationHistory);
            
            // Save conversation for history
            $this->saveConversation($message, $response);
            
            return [
                'success' => true,
                'response' => $response
            ];
            
        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (Exception $e) {
            error_log("RAG Chatbot error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'I apologize, but I encountered an error. Please try rephrasing your request.'
            ];
        }
    }
    
    /**
     * Check if message is a simple greeting
     */
    private function isSimpleGreeting($message) {
        $message = strtolower(trim($message));
        $greetings = [
            'hi', 'hello', 'hey', 'greetings', 'good morning', 'good afternoon', 
            'good evening', 'howdy', 'hiya', 'what\'s up', 'whats up'
        ];
        
        return in_array($message, $greetings) || 
            (strlen($message) <= 15 && preg_match('/^(hi|hello|hey)\s*(there|!)*$/i', $message));
    }
    
    /**
     * Generate greeting response with suggestions
     */
    private function generateGreetingResponse() {
        $response = [
            'text' => "Hello! I'm your travel assistant. I can help you find tours and hotels in Vietnam, or plan international trips. How can I assist you today?",
            'type' => 'greeting',
            'data' => [],
            'match_level' => 'greeting',
            'suggestions' => [
                'Show me tours in Ho Chi Minh City',
                'Plan a 3-day trip to Tokyo', 
                'Find hotels in Nha Trang',
                'Help me plan a budget trip to Paris'
            ]
        ];
        
        $this->saveConversation("greeting", $response);
        return [
            'success' => true,
            'response' => $response
        ];
    }
    
    /**
     * Analyze user intent from message
     */
    private function analyzeIntent($message) {
        $message = strtolower($message);
        
        $intents = [
            'tour_search' => [
                'keywords' => ['tour', 'package', 'trip', 'travel package', 'excursion', 'sightseeing'],
                'patterns' => ['/show.*tour/', '/find.*tour/', '/tour.*to/', '/tour.*in/', '/\d+\s*day.*tour/']
            ],
            'hotel_search' => [
                'keywords' => ['hotel', 'accommodation', 'stay', 'lodge', 'resort', 'booking'],
                'patterns' => ['/find.*hotel/', '/hotel.*in/', '/where.*stay/', '/accommodation/']
            ],
            'destination_info' => [
                'keywords' => ['destination', 'place', 'city', 'location', 'visit', 'about', 'information'],
                'patterns' => ['/about.*city/', '/tell.*about/', '/plan.*trip/', '/what.*do.*in/']
            ],
            'price_inquiry' => [
                'keywords' => ['price', 'cost', 'budget', 'expensive', 'cheap', 'affordable', 'how much'],
                'patterns' => ['/how much/', '/price.*for/', '/cost.*of/', '/budget.*for/']
            ],
            'duration_inquiry' => [
                'keywords' => ['duration', 'long', 'days', 'time', 'how long', 'how many days'],
                'patterns' => ['/how long/', '/duration.*of/', '/.*days/', '/\d+.*day/']
            ]
        ];
        
        $scores = [];
        foreach ($intents as $intent => $data) {
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
                    $score += 3;
                }
            }
            
            $scores[$intent] = $score;
        }
        
        $maxScore = max($scores);
        return $maxScore > 0 ? array_keys($scores, $maxScore)[0] : 'general';
    }
    
    /**
     * Normalize city name for consistent matching
     */
    private function normalizeCityName($cityName) {
        $cityName = strtolower(trim($cityName));
        // Remove special characters but keep unicode letters
        $cityName = preg_replace('/[^\p{L}\p{N}\s]/u', '', $cityName);
        // Replace multiple spaces with single space
        $cityName = preg_replace('/\s+/', ' ', $cityName);
        return trim($cityName);
    }
    
    /**
     * Smart city extraction with Vietnamese DB vs International fallback
     */
    private function extractCityFromMessage($message) {
        $normalizedMessage = $this->normalizeCityName($message);
        $words = explode(' ', $normalizedMessage);
        
        // Try multi-word combinations first (longest matches first)
        for ($length = 3; $length >= 1; $length--) {
            for ($i = 0; $i <= count($words) - $length; $i++) {
                $cityCandidate = implode(' ', array_slice($words, $i, $length));
                
                // Check if it's a Vietnamese city in our database
                if (isset($this->vietnameseCities[$cityCandidate])) {
                    return [
                        'city' => $this->vietnameseCities[$cityCandidate],
                        'is_vietnamese' => true,
                        'original_input' => $cityCandidate
                    ];
                }
            }
        }
        
        // No Vietnamese city found - extract potential international destinations
        // Look for capitalized words (proper nouns) in original message
        preg_match_all('/\b\p{Lu}[\p{Ll}]+(?:\s+\p{Lu}[\p{Ll}]+)*\b/u', $message, $matches);
        if (!empty($matches[0])) {
            $potentialCity = $matches[0][0]; // Take the first capitalized word/phrase
            return [
                'city' => ['name' => $potentialCity, 'id' => null],
                'is_vietnamese' => false,
                'original_input' => strtolower($potentialCity)
            ];
        }
        
        // Fallback: look for any word that might be a city name
        $commonCityIndicators = ['to', 'in', 'visit', 'trip', 'travel'];
        foreach ($commonCityIndicators as $indicator) {
            if (preg_match("/$indicator\\s+([a-zA-Z\\s]+?)(?:\\s|$)/i", $message, $matches)) {
                $potentialCity = trim($matches[1]);
                if (strlen($potentialCity) > 2 && !in_array(strtolower($potentialCity), ['the', 'and', 'or', 'but'])) {
                    return [
                        'city' => ['name' => $potentialCity, 'id' => null],
                        'is_vietnamese' => false,
                        'original_input' => strtolower($potentialCity)
                    ];
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract relevant entities from user message
     */
    private function extractEntities($message) {
        $entities = [
            'cities' => [],
            'duration' => null,
            'budget' => null,
            'preferences' => [],
            'is_international' => false
        ];
        
        // Smart city extraction
        $cityResult = $this->extractCityFromMessage($message);
        if ($cityResult) {
            $entities['cities'][] = $cityResult['city'];
            $entities['is_international'] = !$cityResult['is_vietnamese'];
        }
        
        // Extract duration - improved with Vietnamese support
        if (preg_match('/(\d+)\s*(ngày|ngay|day[s]?|night[s]?)/iu', $message, $matches)) {
            $entities['duration'] = intval($matches[1]);
        }
        
        // Extract budget - improved with Vietnamese support
        if (preg_match('/(\d+)\s*(triệu|triều|million)/iu', $message, $matches)) {
            $entities['budget'] = intval($matches[1]) * 1000000;
        } elseif (preg_match('/(\d+(?:[,.]?\d{3})*)\s*(?:vnd|dong|usd|\$|€|£)/i', $message, $matches)) {
            $budgetStr = str_replace([',', '.'], '', $matches[1]);
            $entities['budget'] = intval($budgetStr);
        }
        
        // Extract travel preferences
        $preferences = [
            'luxury', 'budget', 'family', 'romantic', 'adventure', 
            'cultural', 'beach', 'mountain', 'backpacker', 'solo'
        ];
        $messageLower = strtolower($message);
        foreach ($preferences as $pref) {
            if (strpos($messageLower, $pref) !== false) {
                $entities['preferences'][] = $pref;
            }
        }
        
        return $entities;
    }
    
    /**
     * Retrieve relevant data based on intent and entities
     */
    private function retrieveRelevantData($intent, $entities, $message) {
        $result = [
            'data' => [
                'tours' => [],
                'hotels' => [],
                'cities' => []
            ],
            'match_level' => 'none',
            'fallback_message' => '',
            'suggestions' => [],
            'is_international' => $entities['is_international']
        ];
        
        // Handle international destinations - delegate to Gemini
        if ($entities['is_international'] && !empty($entities['cities'])) {
            $cityName = $entities['cities'][0]['name'];
            $result['match_level'] = 'international_gemini';
            $result['fallback_message'] = "Let me create a custom travel plan for $cityName";
            return $result;
        }
        
        // Handle Vietnamese destinations with database lookup
        if (!$entities['is_international'] && !empty($entities['cities'])) {
            $this->getCityData($result['data'], $entities);
            
            switch ($intent) {
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
        
        // No specific location detected
        if (empty($entities['cities'])) {
            $result['match_level'] = 'general';
            $result['fallback_message'] = "I can help you plan trips to Vietnam (with specific tour and hotel data) or international destinations. Which would you prefer?";
            $result['suggestions'] = [
                'Show me tours in Vietnamese cities',
                'Plan an international trip',
                'Find hotels in Vietnam',
                'Help me choose a destination'
            ];
        }
        
        return $result;
    }
    
    /**
     * Perform Vietnamese tour search with different match levels
     */
    private function performVietnameseTourSearch(&$result, $entities) {
        $cityName = $entities['cities'][0]['name'];
        
        // Try exact match first (city + duration + budget)
        if ($entities['duration']) {
            $exactTours = $this->getTourDataExact($entities);
            if (!empty($exactTours)) {
                $result['data']['tours'] = $exactTours;
                $result['match_level'] = 'exact';
                $result['fallback_message'] = "Perfect! Here are {$entities['duration']}-day tours in $cityName:";
                return;
            }
        }
        
        // Try city match
        $cityTours = $this->getTourDataByCity($entities);
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
        } else {
            $result['match_level'] = 'no_city_match';
            $result['fallback_message'] = "I don't have tour packages for $cityName in our database. However, I can help you plan a custom itinerary.";
            $result['suggestions'] = [
                "Help me plan a custom itinerary for $cityName",
                "Show me tours in other Vietnamese cities",
                "Find general travel information about $cityName"
            ];
        }
    }
    
    /**
     * Perform Vietnamese hotel search
     */
    private function performVietnameseHotelSearch(&$result, $entities) {
        $cityName = $entities['cities'][0]['name'];
        $hotels = $this->getHotelDataByCity($entities);
        
        if (!empty($hotels)) {
            $result['data']['hotels'] = $hotels;
            $result['match_level'] = 'exact';
            $result['fallback_message'] = "Here are accommodations in $cityName:";
        } else {
            $result['match_level'] = 'no_city_match';
            $result['fallback_message'] = "I don't have hotel listings for $cityName in our database. I recommend checking online booking platforms.";
            $result['suggestions'] = [
                "Show me hotels in other Vietnamese cities",
                "Help me plan activities in $cityName",
                "Get general travel advice for $cityName"
            ];
        }
    }
    
    /**
     * Perform Vietnamese destination info search
     */
    private function performVietnameseDestinationSearch(&$result, $entities) {
        $cityName = $entities['cities'][0]['name'];
        $this->getTourData($result['data'], $entities, 3);
        $this->getHotelData($result['data'], $entities, 3);
        
        $result['match_level'] = 'destination_info';
        $result['fallback_message'] = "Here's what we offer in $cityName:";
    }
    
    /**
     * Perform Vietnamese general search
     */
    private function performVietnameseGeneralSearch(&$result, $entities) {
        $this->getTourData($result['data'], $entities, 3);
        $this->getHotelData($result['data'], $entities, 3);
        
        $cityName = $entities['cities'][0]['name'];
        $result['match_level'] = 'general_mixed';
        $result['fallback_message'] = "Here's what we offer in $cityName:";
    }
    
    /**
     * Get exact tour matches (city + duration + budget)
     */
    private function getTourDataExact($entities) {
        if (empty($entities['cities']) || $entities['is_international']) return [];
        
        try {
            $cityId = $entities['cities'][0]['id'];
            $sql = "SELECT t.*, c.city as city_name FROM tours t 
                    LEFT JOIN cities c ON t.cityid = c.cityid 
                    WHERE t.cityid = ?";
            $params = [$cityId];
            $types = "i";
            
            if ($entities['duration']) {
                $sql .= " AND t.duration_days = ?";
                $params[] = $entities['duration'];
                $types .= "i";
            }
            
            if ($entities['budget']) {
                $sql .= " AND t.price_per_person <= ?";
                $params[] = $entities['budget'];
                $types .= "i";
            }
            
            $sql .= " ORDER BY t.price_per_person ASC LIMIT 6";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return [];
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("getTourDataExact error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get tours by city only
     */
    private function getTourDataByCity($entities) {
        if (empty($entities['cities']) || $entities['is_international']) return [];
        
        try {
            $cityId = $entities['cities'][0]['id'];
            $sql = "SELECT t.*, c.city as city_name FROM tours t 
                    LEFT JOIN cities c ON t.cityid = c.cityid 
                    WHERE t.cityid = ? ORDER BY t.price_per_person ASC LIMIT 6";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return [];
            
            $stmt->bind_param('i', $cityId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("getTourDataByCity error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get hotels by city
     */
    private function getHotelDataByCity($entities) {
        if (empty($entities['cities']) || $entities['is_international']) return [];
        
        try {
            $cityId = $entities['cities'][0]['id'];
            $sql = "SELECT h.*, c.city as city_name FROM hotels h 
                    LEFT JOIN cities c ON h.cityid = c.cityid 
                    WHERE h.cityid = ? ORDER BY h.ratings DESC, h.cost ASC LIMIT 6";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return [];
            
            $stmt->bind_param('i', $cityId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("getHotelDataByCity error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get city information from database
     */
    private function getCityData(&$data, $entities) {
        if (!empty($entities['cities']) && !$entities['is_international']) {
            try {
                $cityId = $entities['cities'][0]['id'];
                $sql = "SELECT * FROM cities WHERE cityid = ?";
                $stmt = $this->db->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param('i', $cityId);
                    $stmt->execute();
                    $data['cities'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                }
            } catch (Exception $e) {
                error_log("getCityData error: " . $e->getMessage());
                $data['cities'] = [];
            }
        }
    }
    
    /**
     * Get tour data with optional filters
     */
    private function getTourData(&$data, $entities, $limit = 6) {
        if ($entities['is_international']) return;
        
        try {
            $sql = "SELECT t.*, c.city as city_name FROM tours t 
                    LEFT JOIN cities c ON t.cityid = c.cityid WHERE 1=1";
            $params = [];
            $types = "";
            
            if (!empty($entities['cities'])) {
                $sql .= " AND t.cityid = ?";
                $params[] = $entities['cities'][0]['id'];
                $types .= 'i';
            }
            
            $sql .= " ORDER BY t.price_per_person ASC LIMIT ?";
            $params[] = $limit;
            $types .= 'i';
            
            $stmt = $this->db->prepare($sql);
            if ($stmt && !empty($params)) {
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $data['tours'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        } catch (Exception $e) {
            error_log("getTourData error: " . $e->getMessage());
            $data['tours'] = [];
        }
    }
    
    /**
     * Get hotel data with optional filters
     */
    private function getHotelData(&$data, $entities, $limit = 6) {
        if ($entities['is_international']) return;
        
        try {
            $sql = "SELECT h.*, c.city as city_name FROM hotels h 
                    LEFT JOIN cities c ON h.cityid = c.cityid WHERE 1=1";
            $params = [];
            $types = "";
            
            if (!empty($entities['cities'])) {
                $sql .= " AND h.cityid = ?";
                $params[] = $entities['cities'][0]['id'];
                $types .= 'i';
            }
            
            $sql .= " ORDER BY h.ratings DESC, h.cost ASC LIMIT ?";
            $params[] = $limit;
            $types .= 'i';
            
            $stmt = $this->db->prepare($sql);
            if ($stmt && !empty($params)) {
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $data['hotels'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        } catch (Exception $e) {
            error_log("getHotelData error: " . $e->getMessage());
            $data['hotels'] = [];
        }
    }
    
    /**
     * Generate contextual response using database and/or Gemini AI
     */
    private function generateContextualResponse($userMessage, $retrievalResult, $conversationHistory) {
        try {
            // Handle international destinations with Gemini
            if ($retrievalResult['is_international'] && $retrievalResult['match_level'] === 'international_gemini') {
                $cityName = $this->extractCityNameFromMessage($userMessage);
                $geminiPlan = $this->generateInternationalPlan($userMessage, $cityName);
                
                return [
                    'text' => $geminiPlan,
                    'type' => 'international_plan',
                    'data' => [],
                    'match_level' => 'international_gemini',
                    'suggestions' => [
                        'Tell me about local transportation',
                        'What are the must-see attractions?',
                        'Help me plan my budget',
                        'Cultural tips and etiquette'
                    ]
                ];
            }
            
            // Handle Vietnamese destinations and general queries
            switch ($retrievalResult['match_level']) {
                case 'no_city_match':
                    return $this->generateNoMatchResponse($retrievalResult);
                    
                case 'general':
                    return $this->generateGeneralResponse($retrievalResult);
                    
                default:
                    // Use Gemini for Vietnamese destinations with database context
                    $context = $this->buildVietnameseContext($retrievalResult['data']);
                    $conversationContext = $this->buildConversationContext($conversationHistory);
                    $prompt = $this->buildGeminiPrompt($userMessage, $context, $conversationContext, $retrievalResult);
                    
                    $geminiResponse = $this->callGeminiAPI($prompt);
                    return $this->processGeminiResponse($geminiResponse, $retrievalResult);
            }
            
        } catch (Exception $e) {
            error_log("Context generation error: " . $e->getMessage());
            return $this->generateFallbackResponse($retrievalResult);
        }
    }
    
    /**
     * Extract city name from user message for international destinations
     */
    private function extractCityNameFromMessage($message) {
        preg_match_all('/\b\p{Lu}[\p{Ll}]+(?:\s+\p{Lu}[\p{Ll}]+)*\b/u', $message, $matches);
        return !empty($matches[0]) ? $matches[0][0] : 'your destination';
    }
    
    /**
     * Generate international travel plan using Gemini AI
     */
    private function generateInternationalPlan($userMessage, $cityName) {
        $prompt = "You are a helpful international travel assistant. The user is asking about travel to $cityName.

User's request: $userMessage

Please provide a helpful, detailed response about traveling to $cityName. Include practical information such as:
- Key attractions and activities
- Best time to visit
- Transportation tips
- Cultural considerations
- Budget estimates where relevant
- Travel requirements (visa, etc.) if applicable

Keep your response conversational, informative, and well-structured. Focus on being practical and helpful.";

        try {
            return $this->callGeminiAPI($prompt);
        } catch (Exception $e) {
            error_log("Gemini API error for international plan: " . $e->getMessage());
            return "I'd be happy to help you plan your trip to $cityName! Here are some general tips:

**Planning Your Visit:**
- Research visa requirements and travel documents
- Check weather conditions for your travel dates  
- Book flights and accommodation in advance
- Consider travel insurance

**What to Research:**
- Top attractions and landmarks
- Local transportation options
- Popular neighborhoods to explore
- Local cuisine and dining recommendations
- Cultural etiquette and customs

Would you like me to help you research specific aspects of your $cityName trip?";
        }
    }
    
    private function generateNoMatchResponse($retrievalResult) {
        return [
            'text' => $retrievalResult['fallback_message'],
            'type' => 'no_match',
            'data' => [],
            'match_level' => $retrievalResult['match_level'],
            'suggestions' => $retrievalResult['suggestions']
        ];
    }
    
    private function generateGeneralResponse($retrievalResult) {
        return [
            'text' => $retrievalResult['fallback_message'],
            'type' => 'general',
            'data' => [],
            'match_level' => 'general',
            'suggestions' => $retrievalResult['suggestions']
        ];
    }
    
    private function buildVietnameseContext($data) {
        $context = "Available travel data in Vietnam:\n\n";
        
        if (!empty($data['tours'])) {
            $context .= "AVAILABLE TOURS:\n";
            foreach ($data['tours'] as $tour) {
                $context .= "- {$tour['tour_name']} in {$tour['city_name']}: {$tour['duration_days']} days, " . 
                           number_format($tour['price_per_person']) . " VND per person\n";
            }
            $context .= "\n";
        }
        
        if (!empty($data['hotels'])) {
            $context .= "AVAILABLE HOTELS:\n";
            foreach ($data['hotels'] as $hotel) {
                $context .= "- {$hotel['hotel']} in {$hotel['city_name']}: Rating {$hotel['ratings']}/5";
                if ($hotel['cost'] > 0) {
                    $context .= ", " . number_format($hotel['cost']) . " VND per night";
                }
                $context .= "\n";
            }
        }
        
        return $context;
    }
    
    private function buildConversationContext($conversationHistory) {
        if (empty($conversationHistory)) {
            return "";
        }
        
        $context = "Recent conversation:\n";
        $recentMessages = array_slice($conversationHistory, -6);
        
        foreach ($recentMessages as $msg) {
            if (isset($msg['role']) && isset($msg['message'])) {
                $role = $msg['role'] === 'user' ? 'User' : 'Assistant';
                $message = is_string($msg['message']) ? $msg['message'] : 
                        (isset($msg['message']['text']) ? $msg['message']['text'] : 'Invalid message format');
                
                if (strlen($message) > 150) {
                    $message = substr($message, 0, 147) . '...';
                }
                
                $context .= "{$role}: {$message}\n";
            }
        }
        
        return $context . "\n";
    }
    
    private function buildGeminiPrompt($userMessage, $context, $conversationContext, $retrievalResult) {
        $prompt = "You are a helpful Vietnam travel assistant. Provide concise, accurate, and friendly responses.

{$conversationContext}

Available data:
{$context}

User's question: {$userMessage}

Response guidelines:
- Be conversational and helpful
- Use specific data when available (mention tour names, prices, locations)
- Keep responses focused and not too lengthy
- Provide practical travel advice
- If you have limited data, acknowledge it honestly
- Format prices in Vietnamese currency (VND)

";

        if (isset($retrievalResult['fallback_message']) && !empty($retrievalResult['fallback_message'])) {
            $prompt .= "\nContext: " . $retrievalResult['fallback_message'] . "\n";
        }

        $prompt .= "\nPlease respond:";
        
        return $prompt;
    }
    
    private function callGeminiAPI($prompt) {
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "topP" => 0.8,
                "topK" => 40,
                "maxOutputTokens" => 800
            ]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->geminiApiUrl . "?key=" . $this->geminiApiKey,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception("Gemini API cURL error: " . curl_error($ch));
        }
        
        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        
        if (!$decodedResponse) {
            throw new Exception("Invalid JSON response from Gemini API");
        }
        
        if ($httpCode !== 200) {
            $errorMsg = isset($decodedResponse['error']['message']) ? 
                       $decodedResponse['error']['message'] : 'Unknown API error';
            throw new Exception("Gemini API error ($httpCode): $errorMsg");
        }
        
        if (!isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception("Unexpected response structure from Gemini API");
        }
        
        return $decodedResponse['candidates'][0]['content']['parts'][0]['text'];
    }
    
    private function processGeminiResponse($geminiText, $retrievalResult) {
        $type = 'general';
        $displayData = [];
        
        if (!empty($retrievalResult['data']['tours']) && !empty($retrievalResult['data']['hotels'])) {
            $type = 'destination_info';
            $displayData = [
                'tours' => array_slice($retrievalResult['data']['tours'], 0, 3),
                'hotels' => array_slice($retrievalResult['data']['hotels'], 0, 3)
            ];
        } elseif (!empty($retrievalResult['data']['tours'])) {
            $type = 'tour_search';
            $displayData = array_slice($retrievalResult['data']['tours'], 0, 6);
        } elseif (!empty($retrievalResult['data']['hotels'])) {
            $type = 'hotel_search';
            $displayData = array_slice($retrievalResult['data']['hotels'], 0, 6);
        }
        
        $suggestions = !empty($retrievalResult['suggestions']) ? 
                      $retrievalResult['suggestions'] : 
                      $this->generateContextualSuggestions($geminiText, $retrievalResult);
        
        return [
            'text' => $geminiText,
            'type' => $type,
            'data' => $displayData,
            'match_level' => $retrievalResult['match_level'],
            'suggestions' => $suggestions
        ];
    }
    
    private function generateFallbackResponse($retrievalResult) {
        if (in_array($retrievalResult['match_level'], ['no_city_match', 'no_city_data'])) {
            return [
                'text' => $retrievalResult['fallback_message'] ?? "I'd be happy to help you with Vietnam travel planning! What would you like to know?",
                'type' => 'no_match',
                'data' => [],
                'match_level' => $retrievalResult['match_level'],
                'suggestions' => $retrievalResult['suggestions'] ?? [
                    'Plan a custom itinerary', 
                    'Show tours in major cities', 
                    'Get general travel advice'
                ]
            ];
        }
        
        if (!empty($retrievalResult['data']['tours'])) {
            return [
                'text' => $retrievalResult['fallback_message'] ?? "Here are some available tours:",
                'type' => 'tour_search',
                'data' => array_slice($retrievalResult['data']['tours'], 0, 6),
                'match_level' => $retrievalResult['match_level'],
                'suggestions' => $retrievalResult['suggestions'] ?? ['Show me hotels', 'Compare prices', 'Best time to visit']
            ];
        } elseif (!empty($retrievalResult['data']['hotels'])) {
            return [
                'text' => $retrievalResult['fallback_message'] ?? "Here are some accommodation options:",
                'type' => 'hotel_search',
                'data' => array_slice($retrievalResult['data']['hotels'], 0, 6),
                'match_level' => $retrievalResult['match_level'],
                'suggestions' => $retrievalResult['suggestions'] ?? ['Show me tours', 'Find restaurants', 'Local attractions']
            ];
        } else {
            return [
                'text' => $retrievalResult['fallback_message'] ?? "I'd be happy to help you with Vietnam travel planning! What would you like to know?",
                'type' => 'general',
                'data' => [],
                'match_level' => $retrievalResult['match_level'],
                'suggestions' => $retrievalResult['suggestions'] ?? ['Show popular tours', 'Find hotels in cities', 'Plan a trip']
            ];
        }
    }
    
    private function generateContextualSuggestions($responseText, $retrievalResult) {
        $suggestions = [];
        
        switch ($retrievalResult['match_level']) {
            case 'exact':
                $suggestions = [
                    'Show me more options in this area',
                    'What\'s the best time to visit?',
                    'Find nearby accommodations',
                    'Tell me about local attractions'
                ];
                break;
                
            case 'same_city':
                $suggestions = [
                    'Help me customize the duration',
                    'Show alternatives in nearby cities',
                    'Find hotels in the same area',
                    'What activities are included?'
                ];
                break;
                
            case 'general_tours':
            case 'general_hotels':
                $suggestions = [
                    'Filter by specific cities',
                    'Show budget-friendly options',
                    'Find luxury experiences',
                    'Plan a multi-city trip'
                ];
                break;
                
            default:
                $suggestions = [
                    'Show me popular destinations',
                    'Find tours within my budget',
                    'Plan a 3-day trip',
                    'What\'s included in tour packages?'
                ];
        }
        
        if (!empty($retrievalResult['data']['cities'])) {
            $cityName = $retrievalResult['data']['cities'][0]['city'];
            $suggestions[] = "Tell me more about $cityName attractions";
        }
        
        shuffle($suggestions);
        return array_slice($suggestions, 0, 4);
    }
    
    private function saveConversation($userMessage, $botResponse) {
        try {
            $createTable = "CREATE TABLE IF NOT EXISTS chat_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT DEFAULT 1,
                user_message TEXT NOT NULL,
                bot_response TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_created_at (created_at)
            )";
            $this->db->query($createTable);
            
            $stmt = $this->db->prepare("INSERT INTO chat_history (user_id, user_message, bot_response) VALUES (?, ?, ?)");
            if ($stmt) {
                $botResponseText = is_array($botResponse) ? json_encode($botResponse) : $botResponse;
                $stmt->bind_param('iss', $this->userId, $userMessage, $botResponseText);
                $stmt->execute();
                $stmt->close();
            }
            
        } catch (Exception $e) {
            error_log("Error saving conversation: " . $e->getMessage());
        }
    }
    
    public function getChatHistory() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $this->userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $history = [];
            while ($row = $result->fetch_assoc()) {
                $botResponse = json_decode($row['bot_response'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $botResponse = ['text' => $row['bot_response'], 'type' => 'general'];
                }
                
                $history[] = [
                    'id' => $row['id'],
                    'user_message' => $row['user_message'],
                    'bot_response' => $botResponse,
                    'created_at' => $row['created_at']
                ];
            }
            $stmt->close();
            
            return [
                'success' => true,
                'history' => $history
            ];
        } catch (Exception $e) {
            error_log("Error getting chat history: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Could not load chat history'
            ];
        }
    }
    
    public function getSystemStats() {
        try {
            $stats = [];
            
            $result = $this->db->query("SELECT COUNT(*) as count FROM tours");
            $stats['total_tours'] = $result ? $result->fetch_assoc()['count'] : 0;
            
            $result = $this->db->query("SELECT COUNT(*) as count FROM hotels");
            $stats['total_hotels'] = $result ? $result->fetch_assoc()['count'] : 0;
            
            $result = $this->db->query("SELECT COUNT(*) as count FROM cities");
            $stats['total_cities'] = $result ? $result->fetch_assoc()['count'] : 0;
            
            $result = $this->db->query("
                SELECT c.city, COUNT(t.tourid) as tour_count 
                FROM cities c 
                LEFT JOIN tours t ON c.cityid = t.cityid 
                GROUP BY c.cityid, c.city 
                ORDER BY tour_count DESC
            ");
            $stats['cities_with_tours'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            
            return [
                'success' => true,
                'stats' => $stats
            ];
        } catch (Exception $e) {
            error_log("Error getting system stats: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Could not load system statistics'
            ];
        }
    }
}

// Main request handling
try {
    $chatbot = new OptimizedRAGTravelChatbot($conn);
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'get_history':
                    echo json_encode($chatbot->getChatHistory());
                    exit;
                    
                case 'get_stats':
                    echo json_encode($chatbot->getSystemStats());
                    exit;
                    
                default:
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Invalid action parameter'
                    ]);
                    exit;
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Vietnam Travel RAG Chatbot API',
            'version' => '2.0',
            'endpoints' => [
                'POST /' => 'Send chat message',
                'GET /?action=get_history' => 'Get chat history',
                'GET /?action=get_stats' => 'Get system statistics'
            ]
        ]);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['message'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request format. Expected JSON with "message" field.'
            ]);
            exit;
        }
        
        $message = trim($input['message']);
        $conversationHistory = $input['conversation_history'] ?? [];
        
        if (empty($message)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Message cannot be empty'
            ]);
            exit;
        }
        
        $response = $chatbot->processMessage($message, $conversationHistory);
        $response['timestamp'] = date('c');
        
        echo json_encode($response);
        exit;
    }
    
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use GET or POST.'
    ]);
    
} catch (Exception $e) {
    error_log("RAG Chatbot backend error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error. Please try again later.',
        'timestamp' => date('c')
    ]);
}

mysqli_close($conn);
?>