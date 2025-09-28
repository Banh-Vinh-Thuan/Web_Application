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

    public function generateVietnameseResponse($userMessage, $context, $conversationHistory, $metadata = []) {
        try {
            // DEBUG: Log input parameters
            Logger::info("generateVietnameseResponse called", [
                'userMessage' => substr($userMessage, 0, 100),
                'context_length' => strlen($context),
                'metadata' => $metadata
            ]);
            
            $prompt = $this->buildPrompt($userMessage, $context, $conversationHistory, $metadata);
            
            // DEBUG: Log the built prompt
            Logger::debug("Built prompt", [
                'prompt_length' => strlen($prompt)
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
            
            // DEBUG: Log request data
            Logger::debug("Making API request", [
                'url' => $this->apiUrl,
                'request_data_size' => strlen(json_encode($requestData))
            ]);
            
            $response = $this->makeApiRequest($this->apiUrl, $requestData);
            
            // DEBUG: Log API response status
            Logger::debug("Gemini API response received", [
                'has_candidates' => isset($response['candidates']),
                'candidates_count' => isset($response['candidates']) ? count($response['candidates']) : 0
            ]);
            
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
                
                // DEBUG: Log successful generation
                Logger::info("Successfully generated response", [
                    'response_length' => strlen($generatedText),
                    'response_preview' => substr($generatedText, 0, 100)
                ]);
                
                return $generatedText;
            }
            
            // DEBUG: Log structure issue and try fallback
            Logger::warning("Invalid Gemini API response structure, using context fallback", [
                'has_candidates' => isset($response['candidates']),
                'context_available' => !empty($context)
            ]);
            
            // Try to generate a response from available context
            return $this->generateContextualFallback($context, $userMessage);
            
        } catch (Exception $e) {
            // DEBUG: Log detailed error
            Logger::error("Gemini API error - generating fallback", [
                'error_message' => $e->getMessage(),
                'user_message' => substr($userMessage, 0, 50),
                'context_available' => !empty($context)
            ]);
            
            // Generate intelligent fallback based on available context
            return $this->generateContextualFallback($context, $userMessage);
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
        if (empty($context)) {
            return "I'd be happy to help you find travel options in Vietnam. Could you please specify which city or type of experience you're looking for?";
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
        
        // Final fallback if nothing could be parsed
        if (empty($response)) {
            if (stripos($userMessage, 'tour') !== false) {
                $response = "I have tour information available for various destinations in Vietnam. The tours range from 2-7 days with different price points and experiences.";
            } elseif (stripos($userMessage, 'hotel') !== false) {
                $response = "I have hotel information available across Vietnam with various ratings and price ranges to suit different budgets.";
            } else {
                $response = "I have travel information available for tours and hotels across Vietnam. Please let me know what specific destination or type of experience interests you.";
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
        $requestData = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];
        
        $response = $this->makeApiRequest($this->apiUrl, $requestData);
        
        if ($response && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return $response['candidates'][0]['content']['parts'][0]['text'];
        }
        
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
        
    } catch (Exception $e) {
        Logger::error("International plan generation error", ['error' => $e->getMessage()]);
        return "For travel to $cityName, please check visa requirements and book accommodations in advance.";
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
            $requestData = [
                'model' => Config::GEMINI_EMBEDDING_MODEL,
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ];
            
            $response = $this->makeApiRequest($this->embeddingApiUrl, $requestData);
            
            if ($response && isset($response['embedding']['values'])) {
                return $response['embedding']['values'];
            }
            
            Logger::warning("Invalid embedding response", ['text' => substr($text, 0, 100)]);
            return null;
            
        } catch (Exception $e) {
            Logger::error("Embedding generation failed", ['error' => $e->getMessage()]);
            return null;
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

    private function callApi($apiUrl, $prompt = null, $postData = null) {
        // Build post data if prompt provided
        if ($prompt && !$postData) {
            $postData = [
                "contents" => [["parts" => [["text" => $prompt]]]],
                "generationConfig" => [ 
                    "temperature" => 0.7,
                    "topP" => 0.8,
                    "topK" => 40,
                    "maxOutputTokens" => 800,
                    "stopSequences" => []
                ],
                "safetySettings" => [
                    [
                        "category" => "HARM_CATEGORY_HARASSMENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                    ],
                    [
                        "category" => "HARM_CATEGORY_HATE_SPEECH", 
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                    ],
                    [
                        "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                    ],
                    [
                        "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                        "threshold" => "BLOCK_MEDIUM_AND_ABOVE"
                    ]
                ]
            ];
        }

        $maxRetries = 3;
        $retryDelay = 1; // seconds
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $ch = curl_init();
                
                // FIXED: Corrected the URL concatenation syntax
                curl_setopt_array($ch, [
                    CURLOPT_URL => $apiUrl . "?key=" . $this->apiKey,  // FIXED: Changed => to .
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($postData),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'User-Agent: TravelChatbot/1.0'
                    ],
                    CURLOPT_TIMEOUT => Config::API_TIMEOUT,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_FOLLOWLOCATION => false
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                
                curl_close($ch);
                
                // Handle cURL errors
                if ($curlError) {
                    throw new Exception("cURL Error: " . $curlError);
                }
                
                // Handle empty response
                if ($response === false || empty($response)) {
                    throw new Exception("Empty response from API");
                }

                $decodedResponse = json_decode($response, true);
                
                // Handle JSON decode errors
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("JSON decode error: " . json_last_error_msg());
                }
                
                // Handle HTTP errors
                if ($httpCode !== 200) {
                    $errorMessage = $decodedResponse['error']['message'] ?? "Unknown API Error";
                    
                    // Handle rate limiting with exponential backoff
                    if ($httpCode === 429 && $attempt < $maxRetries) {
                        Logger::warning("Rate limit hit, retrying", [
                            'attempt' => $attempt,
                            'delay' => $retryDelay
                        ]);
                        sleep($retryDelay);
                        $retryDelay *= 2; // Exponential backoff
                        continue;
                    }
                    
                    throw new Exception("API error ($httpCode): " . $errorMessage);
                }

                // Handle different API response formats
                if (strpos($apiUrl, 'embedContent') !== false) {
                    // Embedding API response
                    return $decodedResponse;
                } else {
                    // Generation API response
                    if (!isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) {
                        // Check for content filtering
                        if (isset($decodedResponse['candidates'][0]['finishReason'])) {
                            $finishReason = $decodedResponse['candidates'][0]['finishReason'];
                            if ($finishReason === 'SAFETY') {
                                throw new Exception("Content was blocked by safety filters");
                            }
                        }
                        throw new Exception("Unexpected response structure from Gemini Generation API");
                    }
                    
                    return $decodedResponse['candidates'][0]['content']['parts'][0]['text'];
                }
                
            } catch (Exception $e) {
                // If this is the last attempt, throw the exception
                if ($attempt === $maxRetries) {
                    Logger::error("API call failed after all retries", [
                        'error' => $e->getMessage(),
                        'attempts' => $maxRetries,
                        'api_url' => $apiUrl
                    ]);
                    throw $e;
                }
                
                // Log the retry attempt
                Logger::warning("API call failed, retrying", [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'retry_delay' => $retryDelay
                ]);
                
                sleep($retryDelay);
                $retryDelay *= 2; // Exponential backoff
            }
        }
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
            'timeout' => Config::API_TIMEOUT
        ];
    }
}
?>