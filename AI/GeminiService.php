<?php
require_once './Logger.php';
require_once './config.php';

class GeminiService {
    private $apiKey;
    private $apiUrl;
    private $embeddingApiUrl;

    public function __construct() {
        $this->apiKey = Config::GEMINI_API_KEY;
        $this->apiUrl = Config::GEMINI_API_URL;
        $this->embeddingApiUrl = Config::GEMINI_EMBEDDING_API_URL;
        
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API key is not configured");
        }
    }

    /**
     * Generate text using Gemini API with custom configuration
     * This is a simplified method for intent classification and entity extraction
     */
    public function generateText($prompt, $config = []) {
        try {
            $temperature = $config['temperature'] ?? 0.7;
            $maxTokens = $config['max_tokens'] ?? $config['maxTokens'] ?? 1024;
            
            $requestData = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => $maxTokens
                ]
            ];
            
            $jsonPayload = json_encode($requestData);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            
            $headers = [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                Logger::error("cURL error in generateText", ['error' => $error]);
                throw new Exception("Gemini API request failed: cURL error: " . $error);
            }

            curl_close($ch);
            
            if ($httpCode !== 200) {
                Logger::error("Gemini API returned non-200 status in generateText", ['http_code' => $httpCode, 'response' => $response]);
                
                // Attempt to decode error message from the JSON response
                $responseData = json_decode($response, true);
                $errorMessage = $responseData['error']['message'] ?? "Unknown API Error";
                
                throw new Exception("Gemini API request failed (HTTP $httpCode): " . $errorMessage);
            }

            $responseData = json_decode($response, true);
            
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                return $responseData['candidates'][0]['content']['parts'][0]['text'];
            } else {
                Logger::error("Invalid generateText response format", ['response' => $responseData]);
                return null;
            }

        } catch (Exception $e) {
            Logger::error("Error in generateText", ['error' => $e->getMessage()]);
            // Re-throw the exception so higher-level code can handle it
            throw $e; 
        }
    }

    /**
     * Chat method for conversational interactions
     * Alias for generateText with conversation history support
     */
    public function chat($prompt, $conversationHistory = [], $config = []) {
        // If conversation history is provided, build context
        if (!empty($conversationHistory)) {
            $contextualPrompt = $this->buildConversationContext($conversationHistory);
            $contextualPrompt .= "\n\nCurrent message: " . $prompt;
            return $this->generateText($contextualPrompt, $config);
        }
        
        return $this->generateText($prompt, $config);
    }

    public function generateVietnameseResponse($userMessage, $context, $conversationHistory, $metadata = []) {
        try {
            // DEBUG: Log input parameters
            Logger::info("generateVietnameseResponse called", [
                'userMessage' => substr($userMessage, 0, 100),
                'context_length' => strlen($context),
                'context_empty' => empty($context),
                'metadata' => $metadata
            ]);
            
            // CRITICAL FIX: Check if context is empty FIRST and return immediately
            if (empty(trim($context))) {
                Logger::warning("Empty context received, returning fallback immediately");
                $fallback = $this->generateContextualFallback('', $userMessage);
                
                // DOUBLE CHECK: Ensure fallback is never empty
                if (empty(trim($fallback))) {
                    Logger::error("Fallback generation returned empty, using emergency response");
                    return "I'm ready to help you explore tours and hotels in Vietnam! Could you tell me which city you're interested in, or what type of experience you're looking for?";
                }
                
                return $fallback;
            }
            
            $prompt = $this->buildPrompt($userMessage, $context, $conversationHistory, $metadata);
            
            // DEBUG: Log the built prompt
            Logger::debug("Built prompt", [
                'prompt_length' => strlen($prompt),
                'prompt_preview' => substr($prompt, 0, 200)
            ]);
            
            $requestData = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024
                ]
            ];
            
            $response = $this->makeApiRequest($this->apiUrl, $requestData);
            
            if ($response && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                $generatedText = trim($response['candidates'][0]['content']['parts'][0]['text']);
                
                // Check if response is too generic and try to enhance it
                if ($this->isGenericResponse($generatedText)) {
                    Logger::warning("Generic response detected, trying to enhance");
                    $enhancedResponse = $this->enhanceGenericResponse($generatedText, $context, $userMessage);
                    if (!empty($enhancedResponse)) {
                        $generatedText = $enhancedResponse;
                    }
                }
                
                Logger::info("Successfully generated response", [
                    'response_length' => strlen($generatedText),
                    'response_preview' => substr($generatedText, 0, 100)
                ]);
                
                return $generatedText;
            }
            
            // API returned invalid structure - use fallback
            Logger::warning("Invalid Gemini API response structure, using context fallback");
            $fallback = $this->generateContextualFallback($context, $userMessage);
            
            // CRITICAL: Ensure fallback is never empty
            if (empty(trim($fallback))) {
                Logger::error("Context fallback returned empty string");
                return "I found some travel options for you. Let me show you what's available based on your search.";
            }
            
            return $fallback;
            
        } catch (Exception $e) {
            // DEBUG: Log detailed error
            Logger::error("Gemini API error - generating fallback", [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'user_message' => substr($userMessage, 0, 50),
                'context_available' => !empty($context),
                'context_length' => strlen($context ?? '')
            ]);
            
            // CRITICAL FIX: Always return a valid non-empty fallback
            try {
                $fallback = $this->generateContextualFallback($context ?? '', $userMessage);
                
                if (empty(trim($fallback))) {
                    Logger::error("Fallback generation failed in catch block, using emergency response");
                    return "I'm experiencing technical difficulties, but I'm here to help you find tours and hotels in Vietnam. Please try asking about a specific city like Hanoi, Da Nang, or Ho Chi Minh City.";
                }
                
                return $fallback;
                
            } catch (Exception $fallbackError) {
                Logger::critical("Both API and fallback failed", [
                    'api_error' => $e->getMessage(),
                    'fallback_error' => $fallbackError->getMessage()
                ]);
                
                // LAST RESORT: Return hardcoded helpful message
                return "I'm ready to help you plan your Vietnam trip! I can show you:\n\n• Tours in popular cities\n• Hotels with various ratings\n• Travel packages and itineraries\n\nWhich city would you like to explore?";
            }
        }
    }

    // Check if response is too generic
    private function isGenericResponse($response) {
        $genericPhrases = [
            'I found some travel options',
            'based on your request',
            'Please check the results',
            'I can help you',
            'Here are some options'
        ];
        
        $response = strtolower($response);
        foreach ($genericPhrases as $phrase) {
            if (strpos($response, strtolower($phrase)) !== false) {
                return true;
            }
        }
        
        return false;
    }

    // Enhance generic responses with specific data
    private function enhanceGenericResponse($genericResponse, $context, $userMessage) {
        if (empty($context)) {
            return $genericResponse;
        }
        
        // Try to extract specific information from context
        $contextualResponse = $this->generateContextualFallback($context, $userMessage);
        
        // If contextual response is better than generic, use it
        if (strlen($contextualResponse) > strlen($genericResponse) && !$this->isGenericResponse($contextualResponse)) {
            return $contextualResponse;
        }
        
        return $genericResponse;
    }

    // Generate fallback response using available context data
    private function generateContextualFallback($context, $userMessage) {
        // CRITICAL: Handle completely empty context
        if (empty(trim($context))) {
            Logger::warning("generateContextualFallback called with empty context");
            return "I'd be happy to help you find travel options in Vietnam! I can show you tours and hotels in cities like Hanoi, Da Nang, Ho Chi Minh City, Hoi An, and more. Which destination interests you?";
        }
        
        // Check if this is a multi-city query
        $isMultiCity = $this->detectMultiCityQuery($userMessage, $context);
        
        // Parse context to extract structured information
        $tours = [];
        $hotels = [];
        $citiesFound = [];
        
        // Extract tour information with city tracking
        if (preg_match_all('/\*\*([^*]+)\*\*[^(]*\(City:\s*([^)]+)\)[^|]*Duration:\s*([^|]+)\|[^:]*:\s*([^\n]+)/i', $context, $tourMatches, PREG_SET_ORDER)) {
            foreach ($tourMatches as $match) {
                if (count($match) >= 5) {
                    $cityName = trim($match[2]);
                    $tours[] = [
                        'name' => trim($match[1]),
                        'city' => $cityName,
                        'duration' => trim($match[3]),
                        'price' => trim($match[4])
                    ];
                    if (!in_array($cityName, $citiesFound)) {
                        $citiesFound[] = $cityName;
                    }
                }
            }
        }
        
        // Extract hotel information with city tracking
        if (preg_match_all('/\*\*([^*]+)\*\*[^(]*\(City:\s*([^)]+)\)[^|]*Rating:\s*([^|]+)\|[^:]*:\s*([^\n]+)/i', $context, $hotelMatches, PREG_SET_ORDER)) {
            foreach ($hotelMatches as $match) {
                if (count($match) >= 5) {
                    $cityName = trim($match[2]);
                    $hotels[] = [
                        'name' => trim($match[1]),
                        'city' => $cityName,
                        'rating' => trim($match[3]),
                        'price' => trim($match[4])
                    ];
                    if (!in_array($cityName, $citiesFound)) {
                        $citiesFound[] = $cityName;
                    }
                }
            }
        }
        
        // Generate response based on what we found
        $response = "";
        
        // Handle multi-city tour responses
        if ($isMultiCity && count($citiesFound) >= 2 && !empty($tours)) {
            $toursByCity = [];
            foreach ($tours as $tour) {
                $toursByCity[$tour['city']][] = $tour;
            }
            
            foreach ($citiesFound as $city) {
                if (!empty($toursByCity[$city])) {
                    $response .= "**Tours in $city:**\n";
                    foreach (array_slice($toursByCity[$city], 0, 3) as $tour) {
                        $response .= "• **{$tour['name']}** - {$tour['duration']}, {$tour['price']} VND\n";
                    }
                    $response .= "\n";
                }
            }
            
            if (!empty($response)) {
                $response .= "These tours offer great experiences across multiple Vietnamese destinations.";
            }
        } 
        // Handle regular tour responses
        else if (!empty($tours)) {
            $cityName = !empty($tours[0]['city']) ? $tours[0]['city'] : 'Vietnam';
            $response .= "I found several tours in $cityName:\n\n";
            
            foreach (array_slice($tours, 0, 6) as $tour) {
                $response .= "• **{$tour['name']}** - {$tour['duration']}, {$tour['price']} VND\n";
            }
            
            $response .= "\nThese tours offer great experiences in Vietnam's beautiful destinations.";
        }
        
        // Handle hotel responses
        if (!empty($hotels)) {
            if (!empty($tours)) {
                $response .= "\n\nI also found these hotels:\n\n";
            } else {
                if ($isMultiCity && count($citiesFound) >= 2) {
                    $hotelsByCity = [];
                    foreach ($hotels as $hotel) {
                        $hotelsByCity[$hotel['city']][] = $hotel;
                    }
                    
                    foreach ($citiesFound as $city) {
                        if (!empty($hotelsByCity[$city])) {
                            $response .= "**Hotels in $city:**\n";
                            foreach (array_slice($hotelsByCity[$city], 0, 3) as $hotel) {
                                $response .= "• **{$hotel['name']}** - Rating: {$hotel['rating']}/5, {$hotel['price']} VND/night\n";
                            }
                            $response .= "\n";
                        }
                    }
                } else {
                    $cityName = !empty($hotels[0]['city']) ? $hotels[0]['city'] : 'Vietnam';
                    $response .= "I found several hotels in $cityName:\n\n";
                    
                    foreach (array_slice($hotels, 0, 6) as $hotel) {
                        $response .= "• **{$hotel['name']}** - Rating: {$hotel['rating']}/5, {$hotel['price']} VND/night\n";
                    }
                }
            }
        }
        
        // CRITICAL: Final fallback if parsing completely failed
        if (empty(trim($response))) {
            Logger::warning("Context parsing failed, using keyword-based fallback");
            
            if (stripos($userMessage, 'tour') !== false) {
                $response = "I have tour information available for various destinations in Vietnam. The tours range from 2-7 days with different price points and experiences. Which city would you like to explore?";
            } elseif (stripos($userMessage, 'hotel') !== false) {
                $response = "I have hotel information available across Vietnam with various ratings and price ranges to suit different budgets. Which city are you interested in?";
            } else {
                $response = "I can help you find tours and hotels across Vietnam's most popular destinations including Hanoi, Da Nang, Ho Chi Minh City, Hoi An, and more. What are you looking for?";
            }
        }
        
        return $response;
    }

    public function generateInternationalPlan($userMessage, $cityName, $conversationHistory) {
        $conversationContext = $this->buildConversationContext($conversationHistory);

        $prompt = "You are a helpful international travel assistant. The user is asking about travel to {$cityName}.

{$conversationContext}

User's request: {$userMessage}

Please provide a helpful, detailed response about traveling to {$cityName}. Include practical information such as:
- Key attractions and activities
- Best time to visit
- Transportation tips
- Cultural considerations
- Budget estimates where relevant
- Travel requirements (visa, etc.) if applicable

Keep your response conversational, informative, and well-structured. Focus on being practical and helpful.";
        
        try {
            return $this->generateText($prompt);
        } catch (Exception $e) {
            Logger::error("International plan generation error", ['error' => $e->getMessage()]);
            return "I'd be happy to help you plan your trip to {$cityName}! Here are some general tips:\n
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

Would you like me to help you research specific aspects of your {$cityName} trip?";
        }
    }

    public function generateSuggestions($userMessage, $aiResponse, $responseData) {
        $suggestions = [
            'Show me more tours in Vietnam',
            'Find hotels with good ratings',
            'What are popular destinations?',
            'Help me plan a budget trip'
        ];
        
        // Add context-specific suggestions based on response data
        if (isset($responseData['data']['tours']) && !empty($responseData['data']['tours'])) {
            $suggestions[] = 'Show me hotels in the same area';
            $suggestions[] = 'Find longer tour packages';
        }
        
        if (isset($responseData['data']['hotels']) && !empty($responseData['data']['hotels'])) {
            $suggestions[] = 'Show me tours in the same city';
            $suggestions[] = 'Find budget accommodations';
        }
        
        return array_slice($suggestions, 0, 4);
    }

    public function generateEmbedding($text) {
        try {
            // FIX: Removed 'model' from $requestData because it's typically in the URL (Config::GEMINI_EMBEDDING_API_URL)
            $requestData = [
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ];
            
            $jsonPayload = json_encode($requestData);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->embeddingApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            
            $headers = [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                Logger::error("cURL error in generateEmbedding", ['error' => $error]);
                throw new Exception("Gemini API request failed: cURL error: " . $error);
            }

            curl_close($ch);

            if ($httpCode !== 200) {
                Logger::error("Gemini API returned non-200 status in generateEmbedding", ['http_code' => $httpCode, 'response' => $response]);
                
                $responseData = json_decode($response, true);
                $errorMessage = $responseData['error']['message'] ?? "Unknown API Error";
                
                throw new Exception("Gemini API request failed (HTTP $httpCode): " . $errorMessage);
            }

            $responseData = json_decode($response, true);
            
            if (isset($responseData['embedding']['values'])) {
                return $responseData['embedding']['values'];
            } else {
                Logger::error("Invalid embedding response format", ['response' => $responseData]);
                return null;
            }
        } catch (Exception $e) {
            Logger::error("Error in generateEmbedding", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function buildPrompt($userMessage, $context, $conversationHistory, $metadata) {
        $conversationContext = $this->buildConversationContext($conversationHistory);
        
        // Determine query type for better response formatting
        $queryType = $this->determineQueryType($userMessage);
        
        // Check if this is a multi-city query
        $isMultiCity = $this->detectMultiCityQuery($userMessage, $context);
        
        $prompt = "You are a helpful Vietnam travel assistant specializing in tours and hotels across Vietnam. You provide clear, concise travel recommendations.

$conversationContext

**AVAILABLE TRAVEL DATA:**
$context

**User's Request:** $userMessage

**RESPONSE INSTRUCTIONS:**
1. **Primary Goal:** Provide specific, clean travel recommendations using ONLY the available data
2. **Multi-City Detection:** " . ($isMultiCity ? "MULTI-CITY QUERY DETECTED" : "Single location query") . "
3. **Response Format Based on Query Type:**
   
   **For TOUR queries only:**
   - Format: \"I found these tours:
   • **[Tour Name]** - [Duration], [Price] VND
   • **[Tour Name]** - [Duration], [Price] VND\"
   - Do NOT mention hotels
   
   **For HOTEL queries only:**  
   - Format: \"I found these hotels:
   • **[Hotel Name]** - Rating: [Rating]/5, [Price] VND/night
   • **[Hotel Name]** - Rating: [Rating]/5, [Price] VND/night\"
   - Do NOT mention tours
   
   **For MULTI-CITY TOUR queries:**
   - Format: \"**Tours in [City1]:**
   • **[Tour Name]** - [Duration], [Price] VND
   • **[Tour Name]** - [Duration], [Price] VND
   
   **Tours in [City2]:**
   • **[Tour Name]** - [Duration], [Price] VND
   • **[Tour Name]** - [Duration], [Price] VND\"
   
   **For MIXED queries (both tours and hotels):**
   - Format: \"**Tours:**
   • **[Tour Name]** - [Duration], [Price] VND
   
   **Hotels:**
   • **[Hotel Name]** - Rating: [Rating]/5, [Price] VND/night\"

4. **General Guidelines:**
   - Be conversational and helpful
   - Use Vietnamese currency format (VND)
   - Keep responses clean - no technical scoring or source information
   - Focus ONLY on items that will be displayed in the cards
   - For multi-city queries, group by city when possible
   - End with a brief helpful comment about the options

**Query Type Detected:** $queryType
**Multi-City Query:** " . ($isMultiCity ? "YES" : "NO") . "

Provide your response now in the appropriate format:";
        
        return $prompt;
    }

    private function detectMultiCityQuery($userMessage, $context) {
        $messageLower = strtolower($userMessage);
        
        // Check for explicit "and" between cities
        if (preg_match('/\b(hanoi|ha noi|hà nội).*\band\b.*(hue|huế|ho chi minh|saigon|da nang|nha trang)/i', $messageLower)) {
            return true;
        }
        
        // Check context for multiple cities
        if (preg_match_all('/City:\s*([^)]+)\)/i', $context, $matches)) {
            $cities = array_unique($matches[1]);
            return count($cities) >= 2;
        }
        
        return false;
    }

    // Determine the type of query to format response appropriately
    private function determineQueryType($userMessage) {
        $messageLower = strtolower($userMessage);
        
        $hasTour = (strpos($messageLower, 'tour') !== false);
        $hasHotel = (strpos($messageLower, 'hotel') !== false || 
                    strpos($messageLower, 'accommodation') !== false ||
                    strpos($messageLower, 'stay') !== false);
        
        if ($hasTour && $hasHotel) {
            return 'MIXED';
        } elseif ($hasTour) {
            return 'TOUR';
        } elseif ($hasHotel) {
            return 'HOTEL'; 
        } else {
            return 'GENERAL';
        }
    }
    
    private function makeApiRequest($url, $data) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?key=' . $this->apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => Config::API_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP error: $httpCode");
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON decode error: " . json_last_error_msg());
        }
        
        return $decoded;
    }

    /**
     * Build conversation context from history
     */
    private function buildConversationContext($conversationHistory) {
        if (empty($conversationHistory)) {
            return "No previous conversation.";
        }
        
        $context = "Previous conversation:\n";
        $recentHistory = array_slice($conversationHistory, -3); // Last 3 exchanges
        
        foreach ($recentHistory as $exchange) {
            $role = $exchange['role'] ?? 'unknown';
            $message = $exchange['message'] ?? '';
            
            if ($role === 'user') {
                $context .= "User: " . substr($message, 0, 100) . "\n";
            } elseif ($role === 'assistant') {
                $context .= "Assistant: " . substr($message, 0, 100) . "\n";
            }
        }
        
        return $context;
    }

    /**
     * Get fallback suggestions when generation fails
     */
    private function getFallbackSuggestions($userMessage) {
        $messageLower = strtolower($userMessage);
        
        if (strpos($messageLower, 'tour') !== false) {
            return [
                "What's the best time to visit?",
                "Show me budget tour options",
                "Find tours with accommodation included",
                "Tell me about tour duration options"
            ];
        }
        
        if (strpos($messageLower, 'hotel') !== false) {
            return [
                "Show me 4-star hotels",
                "Find hotels with good ratings",
                "What are the hotel amenities?",
                "Compare hotel prices"
            ];
        }
        
        // Generic fallback
        return [
            "Show me popular destinations",
            "Find budget travel options", 
            "Tell me about local attractions",
            "Help me plan my itinerary"
        ];
    }

    public function testConnection() {
        try {
            $testResponse = $this->generateEmbedding("test connection");
            return $testResponse !== null;
        } catch (Exception $e) {
            Logger::error("Gemini API connection test failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getApiStatus() {
        return [
            'api_key_configured' => !empty($this->apiKey) && $this->apiKey !== 'YOUR_API_KEY_HERE',
            'api_key_length' => strlen($this->apiKey),
            'generation_url' => Config::GEMINI_API_URL,
            'embedding_url' => Config::GEMINI_EMBEDDING_API_URL,
            'embedding_model' => Config::GEMINI_EMBEDDING_MODEL,
        ];
    }
}
?>