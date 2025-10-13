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
            // API key validation
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
     * FIXED: Generate Vietnamese response with proper context handling
     */
    public function generateVietnameseResponse($userMessage, $context, $conversationHistory, $metadata = []) {
        try {
            Logger::info("generateVietnameseResponse called", [
                'userMessage' => substr($userMessage, 0, 100),
                'context_length' => strlen($context),
                'metadata' => $metadata
            ]);

            if (empty(trim($context))) {
                Logger::warning("Empty context received, returning fallback immediately");
                return "I'm ready to help you explore tours and hotels in Vietnam! Could you tell me which city you're interested in?";
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

                if (empty($generatedText) || $this->isGenericResponse($generatedText)) {
                    Logger::warning("Empty or generic response detected, using context fallback");
                    return $this->buildContextBasedResponse($context, $userMessage);
                }

                Logger::info("Successfully generated response", [
                    'response_length' => strlen($generatedText),
                    'response_preview' => substr($generatedText, 0, 100)
                ]);

                return $generatedText;
            }

            Logger::warning("Invalid Gemini API response structure, using context fallback");
            return $this->buildContextBasedResponse($context, $userMessage);

        } catch (Exception $e) {
            Logger::error("Gemini API error - generating fallback", [
                'error_message' => $e->getMessage(),
                'user_message' => substr($userMessage, 0, 50)
            ]);
            return $this->buildContextBasedResponse($context ?? '', $userMessage);
        }
    }

    private function buildContextBasedResponse($context, $userMessage) {
        if (empty(trim($context))) {
            return "I'm ready to help you plan your trip to Vietnam! Which destination are you interested in?";
        }
        
        $response = "";
        
        // Parse tours from context
        if (preg_match_all(
            '/\*\*(.*?)\*\* - (\d+) days - ([\d,.]+) VND/s',
            $context,
            $tourMatches,
            PREG_SET_ORDER
        )) {
            preg_match_all('/Tours in (.*?):/s', $context, $cityMatches);
            $tourCities = $cityMatches[1] ?? [];
            
            if (count($tourCities) >= 2) {
                // Multi-city tours
                $response .= "Tours available across multiple cities:\n\n";
                
                foreach ($tourCities as $cityName) {
                    $response .= "**Tours in " . trim($cityName) . ":**\n";
                    $citySection = $this->extractCitySection($context, "Tours in " . trim($cityName));
                    preg_match_all('/\*\*(.*?)\*\* - (\d+) days - ([\d,.]+) VND/', $citySection, $cityTours);
                    
                    $count = min(3, count($cityTours[0]));
                    for ($i = 0; $i < $count; $i++) {
                        $response .= "• **" . $cityTours[1][$i] . "** - " . $cityTours[2][$i] . " days - " . $cityTours[3][$i] . " VND\n";
                    }
                    $response .= "\n"; // Ensure blank line between cities
                }
            } else {
                // Single city tours
                $city = !empty($tourCities) ? trim($tourCities[0]) : 'Vietnam';
                $response .= "I found these tours in **$city**:\n\n";
                
                $count = min(6, count($tourMatches));
                for ($i = 0; $i < $count; $i++) {
                    $response .= "• **{$tourMatches[$i][1]}** - {$tourMatches[$i][2]} days - {$tourMatches[$i][3]} VND\n";
                }
            }
            
            $response .= "\nThese tours offer wonderful experiences!";
        }
        
        // Parse hotels from context
        elseif (preg_match_all(
            '/\*\*(.*?)\*\* - Rating: ([\d.]+)\/5 - ([\d,.]+\s*VND\/night)/s',
            $context,
            $hotelMatches,
            PREG_SET_ORDER
        )) {
            preg_match_all('/Hotels in (.*?):/s', $context, $cityMatches);
            $hotelCities = $cityMatches[1] ?? [];
            
            if (count($hotelCities) >= 2) {
                // Multi-city hotels
                $response .= "Hotels available across multiple cities:\n\n";
                
                foreach ($hotelCities as $cityName) {
                    $response .= "**Hotels in " . trim($cityName) . ":**\n";
                    
                    $citySection = $this->extractCitySection($context, "Hotels in " . trim($cityName));
                    preg_match_all('/\*\*(.*?)\*\* - Rating: ([\d.]+)\/5 - ([\d,.]+\s*VND\/night)/', $citySection, $cityHotels);
                    
                    $count = min(3, count($cityHotels[0]));
                    for ($i = 0; $i < $count; $i++) {
                        $response .= "• **{$cityHotels[1][$i]}** - Rating: {$cityHotels[2][$i]}/5 - {$cityHotels[3][$i]}\n";
                    }
                    $response .= "\n";
                }
            } else {
                // Single city hotels
                $city = !empty($hotelCities) ? trim($hotelCities[0]) : 'Vietnam';
                $response .= "I found these hotels in **$city**:\n\n";
                
                $count = min(6, count($hotelMatches));
                for ($i = 0; $i < $count; $i++) {
                    $response .= "• **{$hotelMatches[$i][1]}** - Rating: {$hotelMatches[$i][2]}/5 - {$hotelMatches[$i][3]}\n";
                }
            }
            
            $response .= "\nThese accommodations offer excellent stays!";
        }
        
        if (empty(trim($response))) {
            return "I found some travel options based on your search. Please check the results below!";
        }
        
        return $response;
    }

    /**
     * Extract section for a specific city from context
     */
    private function extractCitySection($context, $cityHeader) {
        $startPos = strpos($context, $cityHeader);
        if ($startPos === false) return "";
        
        // Find next city header or end
        $nextCity = strpos($context, "\n\n", $startPos + strlen($cityHeader));
        if ($nextCity === false) {
            return substr($context, $startPos);
        }
        
        return substr($context, $startPos, $nextCity - $startPos);
    }

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
        $isMultiCity = $this->detectMultiCityQuery($userMessage, $context);
        
        $prompt = "You are a helpful Vietnam travel assistant specializing in tours and hotels across Vietnam.

    $conversationContext

**AVAILABLE TRAVEL DATA:**
$context

**User's Request:** $userMessage

**RESPONSE INSTRUCTIONS:**

1. **Be Conversational**: Start with a friendly, natural response
2. **List Format**: Use bullet points with proper formatting:
   - Format: • **[Item Name]** - [Duration/Rating] - [Price]
   - Example: • **Sunset City Tour** - 3 days - 5,500,000 VND
3. **Multi-City Handling**: " . ($isMultiCity ? "This is a MULTI-CITY query. Group results by city with clear headers." : "Single location query.") . "

**Response Structure:**

For TOURS:
- \"I found these tours in [City]:
  • **[Tour Name]** - [X] days - [Price] VND
  • **[Tour Name]** - [X] days - [Price] VND\"

For HOTELS:
- \"I found these hotels in [City]:
  • **[Hotel Name]** - Rating: [X.X]/5 - [Price] VND/night
  • **[Hotel Name]** - Rating: [X.X]/5 - [Price] VND/night\"

For MULTI-CITY TOURS:
- \"Tours available across [City1] and [City2]:
  
  Tours in [City1]:
  • **[Tour Name]** - [X] days - [Price] VND
  • **[Tour Name]** - [X] days - [Price] VND
  
  Tours in [City2]:
  • **[Tour Name]** - [X] days - [Price] VND
  • **[Tour Name]** - [X] days - [Price] VND\"

For MIXED CONTENT:
- Greet with acknowledgment of both types: \"I found these great options for you!\" 
- Present tours first with their city: \"Here are wonderful tours in [City1]:\"
- Transition to hotels: \"For accommodation in [City2], I've found excellent options:\" 
- Close with a connecting statement about the overall experience
- Clearly separate tours and hotels sections
- Specify city for each item

4. **End with Brief Comment**: Add a short helpful statement (1 sentence)

Provide your response now:";

        return $prompt;
    }

    private function detectMultiCityQuery($userMessage, $context) {
        $messageLower = strtolower($userMessage);

        // Check for explicit "and" between cities
        if (preg_match('/\b(hanoi|ha noi|hà nội).*\band\b.*(hue|huế|ho chi minh|saigon|da nang|nha trang|can tho|phu quoc)/i', $messageLower)) {
            return true;
        }

        // Check context for multiple cities
        if (preg_match_all('/(?:Tours|Hotels) in\s*([^:]+):/i', $context, $matches)) {
            $cities = array_unique($matches[1]);
            return count($cities) >= 2;
        }

        return false;
    }

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

    private function buildConversationContext($conversationHistory) {
        if (empty($conversationHistory)) {
            return "No previous conversation.";
        }

        $context = "Previous conversation:\n";
        $recentHistory = array_slice($conversationHistory, -3);

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