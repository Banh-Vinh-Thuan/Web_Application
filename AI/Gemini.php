<?php
declare(strict_types=1);

require_once './Logger.php';
require_once './config.php';

class GeminiService
{
    public function __construct(
        private readonly string $apiKey = Config::GEMINI_API_KEY,
        private readonly string $apiUrl = Config::GEMINI_API_URL,
        private readonly string $embeddingApiUrl = Config::GEMINI_EMBEDDING_API_URL
    ) {
        if (empty($this->apiKey) || $this->apiKey === 'AIzaSyBKlus-HPPK2H14xstpE1VHsfkzbUkoRJA') {
        }
    }

    public function generateText(string $prompt, array $config = []): string
    {
        $requestData = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => $config['temperature'] ?? 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => $config['max_tokens'] ?? 1024,
            ],
        ];

        $response = $this->makeApiRequest($this->apiUrl, $requestData);
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($text === null) {
            Logger::error("Invalid generateText response format", ['response' => $response]);
            throw new RuntimeException("Failed to extract text from Gemini response.");
        }
        return $text;
    }

    public function generateEmbedding(string $text): array
    {
        $requestData = [
            'content' => ['parts' => [['text' => $text]]],
        ];

        $response = $this->makeApiRequest($this->embeddingApiUrl, $requestData);
        $embedding = $response['embedding']['values'] ?? null;

        if ($embedding === null) {
            Logger::error("Invalid embedding response format", ['response' => $response]);
            throw new RuntimeException("Failed to extract embedding from Gemini response.");
        }
        return $embedding;
    }

    private function makeApiRequest(string $url, array $data): array
    {
        $ch = curl_init();
        $fullUrl = $url . '?key=' . $this->apiKey;

        $options = [
            CURLOPT_URL => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => Config::API_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => false,
        ];
        curl_setopt_array($ch, $options);

        $responseJson = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($responseJson === false) {
            Logger::error("cURL error in API request", ['error' => $error, 'url' => $fullUrl]);
            throw new RuntimeException("Gemini API request failed: cURL error: " . $error);
        }
        
        $response = json_decode($responseJson, true);
        
        if ($httpCode !== 200) {
            $errorMessage = $response['error']['message'] ?? 'Unknown API Error';
            Logger::error("Gemini API returned non-200 status", ['http_code' => $httpCode, 'response' => $responseJson]);
            throw new RuntimeException("Gemini API request failed (HTTP $httpCode): " . $errorMessage);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Failed to decode JSON response from API.");
        }
        
        return $response;
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

            // CRITICAL FIX: Check if context is empty FIRST
            if (empty(trim($context))) {
                Logger::warning("Empty context received, returning fallback immediately");
                return "I'm ready to help you explore tours and hotels in Vietnam! Could you tell me which city you're interested in, or what type of experience you're looking for?";
            }

            $prompt = $this->buildPrompt($userMessage, $context, $conversationHistory, $metadata);

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

                // CRITICAL FIX: If response is empty or too generic, use context-based fallback
                if (empty($generatedText) || $this->isGenericResponse($generatedText)) {
                    Logger::warning("Empty or generic response detected, using context fallback");
                    $fallback = $this->buildContextBasedResponse($context, $userMessage);
                    return !empty($fallback) ? $fallback : "I found some travel options for you based on your search.";
                }

                Logger::info("Successfully generated response", [
                    'response_length' => strlen($generatedText),
                    'response_preview' => substr($generatedText, 0, 100)
                ]);

                return $generatedText;
            }

            // API returned invalid structure - use context fallback
            Logger::warning("Invalid Gemini API response structure, using context fallback");
            return $this->buildContextBasedResponse($context, $userMessage);

        } catch (Exception $e) {
            Logger::error("Gemini API error - generating fallback", [
                'error_message' => $e->getMessage(),
                'user_message' => substr($userMessage, 0, 50),
                'context_available' => !empty($context)
            ]);

            return $this->buildContextBasedResponse($context ?? '', $userMessage);
        }
    }

    private function buildContextBasedResponse($context, $userMessage) {
        // If no context is provided, return a default welcome message
        if (empty(trim($context))) {
            return "I'm ready to help you plan your trip to Vietnam! Which destination are you interested in?";
        }

        $response = "";

        // Parse context to extract structured tour information
        if (preg_match_all(
            '/\*\*(.*?)\*\* \(City: (.*?)\)\nDuration: (\d+) days \| Price: ([\d,.]+) VND/s',
            $context,
            $tourMatches,
            PREG_SET_ORDER
        )) {
            $city = $tourMatches[0][2] ?? 'Vietnam';
            $response .= "Here are some interesting tours available in **{$city}**:\n\n";
            
            $toursToList = array_slice($tourMatches, 0, 6);

            foreach ($toursToList as $match) {
                $tourName = $match[1];
                $duration = $match[3];
                $price = $match[4];
                $response .= "• **{$tourName}** — a {$duration}-day tour priced from {$price} VND.\n";
            }

            $response .= "\nWould you like to see more details about these options?";
        }
        // SỬA ĐỔI: Thêm khối `else if` để xử lý và định dạng cho khách sạn
        else if (preg_match_all(
            // Regex to capture Hotel Name, City, Rating, and Price per night
            '/\*\*(.*?)\*\* \(City: (.*?)\)\nRating: ([\d.]+)\s*\|\s*Price: ([\d,.]+\s*VND\/night)/s',
            $context,
            $hotelMatches,
            PREG_SET_ORDER
        )) {
            $city = $hotelMatches[0][2] ?? 'Vietnam';
            $response .= "Here are some excellent hotels available in **{$city}**:\n\n";

            $hotelsToList = array_slice($hotelMatches, 0, 6);

            foreach ($hotelsToList as $match) {
                $hotelName = $match[1];
                $rating = $match[3];
                $price = $match[4];
                // Định dạng đầu ra cho khách sạn để tương đồng với tour
                $response .= "• **{$hotelName}** — Rating: {$rating}/5, priced from {$price}.\n";
            }

            $response .= "\nWould you like more details on any of these hotels?";
        }

        // Fallback response if no structured data is found
        if (empty(trim($response))) {
            return "I found some travel options based on your search. Please check the results below!";
        }

        // Add a friendly closing line
        $response .= "\nThese options offer wonderful experiences across Vietnam. Which one would you like to explore further?";

        return $response;
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
        if (preg_match_all('/\*\*([^*]+)\*\*[^(]*\(City:\s*([^)]+)\)[^|]*Rating:\s*([^|]+)\|[^:]*:\s*([^\n]+)/i', $context, $hotelMatches, PREG_SET_ORDER)) {
            foreach ($hotelMatches as $match) {
                if (count($match) >= 5) {
                    $cityName = trim($match[2]);
                    $hotels[] = [
                        'name' => trim($match[1]),
                        'city' => $cityName,
                        'rating' => trim($match[3]), // Giữ nguyên rating gốc
                        'price' => trim($match[4]) // Giữ nguyên giá gốc
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
                                // **FIX**: Check for existing units before appending
                                $ratingText = (strpos($hotel['rating'], '/5') === false) ? "{$hotel['rating']}/5" : $hotel['rating'];
                                $priceText = (stripos($hotel['price'], 'VND') === false) ? "{$hotel['price']} VND/night" : $hotel['price'];
                                $response .= "• **{$hotel['name']}** - Rating: {$ratingText}, {$priceText}\n";
                            }
                            $response .= "\n";
                        }
                    }
                } else {
                    $cityName = !empty($hotels[0]['city']) ? $hotels[0]['city'] : 'Vietnam';
                    $response .= "I found several hotels in $cityName:\n\n";
                    
                    foreach (array_slice($hotels, 0, 6) as $hotel) {
                        // **FIX**: Check for existing units before appending
                        $ratingText = (strpos($hotel['rating'], '/5') === false) ? "{$hotel['rating']}/5" : $hotel['rating'];
                        $priceText = (stripos($hotel['price'], 'VND') === false) ? "{$hotel['price']} VND/night" : $hotel['price'];
                        $response .= "• **{$hotel['name']}** - Rating: {$ratingText}, {$priceText}\n";
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
}
?>