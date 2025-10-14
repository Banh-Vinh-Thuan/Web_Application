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

    public function generateText(string $prompt, array $config = []): string{
        $requestData = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => $config['temperature'] ?? 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => $config['max_tokens'] ?? 2048,  // INCREASED default from 1024
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
                $response .= "Tours available across multiple cities:\n\n";
                
                foreach ($tourCities as $cityName) {
                    $response .= "**Tours in " . trim($cityName) . ":**\n";
                    $citySection = $this->extractCitySection($context, "Tours in " . trim($cityName));
                    preg_match_all('/\*\*(.*?)\*\* - (\d+) days - ([\d,.]+) VND/', $citySection, $cityTours);
                    
                    $count = min(3, count($cityTours[0]));
                    for ($i = 0; $i < $count; $i++) {
                        $response .= "• **" . $cityTours[1][$i] . "** - " . $cityTours[2][$i] . " days - " . $cityTours[3][$i] . " VND\n";
                    }
                    $response .= "\n";
                }
            } else {
                $city = !empty($tourCities) ? trim($tourCities[0]) : 'Vietnam';
                $response .= "I found these tours in **$city**:\n\n";
                
                $count = min(6, count($tourMatches));
                for ($i = 0; $i < $count; $i++) {
                    $response .= "• **{$tourMatches[$i][1]}** - {$tourMatches[$i][2]} days - {$tourMatches[$i][3]} VND\n";
                }
            }
            
            $response .= "\nThese tours offer wonderful experiences!";
        }
        
        // Parse hotels from context (similar fix for encoding)
        elseif (preg_match_all(
            '/\*\*(.*?)\*\* - Rating: ([\d.]+)\/5 - ([\d,.]+\s*VND\/night)/s',
            $context,
            $hotelMatches,
            PREG_SET_ORDER
        )) {
            preg_match_all('/Hotels in (.*?):/s', $context, $cityMatches);
            $hotelCities = $cityMatches[1] ?? [];
            
            if (count($hotelCities) >= 2) {
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
    
    public function generateCreativeResponse(string $userMessage, array $conversationHistory): string {
        $conversationContext = $this->buildConversationContext($conversationHistory);
        
        $isVietnamQuery = $this->isVietnamSpecificQuery($userMessage);
        
        if ($isVietnamQuery) {
            $prompt = $this->buildVietnamContextualPrompt($userMessage, $conversationContext);
        } else {
            $prompt = $this->buildGenericTravelPrompt($userMessage, $conversationContext);
        }
        
        try {
            Logger::info("Calling Gemini for creative response", [
                'message_length' => strlen($userMessage),
                'is_vietnam_query' => $isVietnamQuery
            ]);

            $response = $this->generateText($prompt, [
                'temperature' => 0.7,
                'max_tokens' => 2048
            ]);
            
            if (empty(trim($response))) {
                Logger::warning("Empty response from Gemini API");
                throw new Exception("Empty response received from API");
            }
            
            if ($this->isResponseTruncated($response)) {
                Logger::warning("Response appears truncated, attempting continuation");
                
                $continuationPrompt = "Continue the previous response from where it stopped. Complete the remaining days and conclusion.";
                $continuation = $this->generateText($continuationPrompt, [
                    'temperature' => 0.7,
                    'max_tokens' => 1024
                ]);
                
                if (!empty($continuation)) {
                    $response .= "\n\n" . $continuation;
                }
            }
            
            Logger::info("Gemini creative response generated successfully", [
                'response_length' => strlen($response),
                'is_truncated' => $this->isResponseTruncated($response)
            ]);
            
            return $response;
            
        } catch (Exception $e) {
            Logger::error("Gemini API error in generateCreativeResponse", [
                'error' => $e->getMessage(),
                'user_message' => substr($userMessage, 0, 100),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->buildFallbackCreativeResponse($userMessage);
        }
    }

    private function buildVietnamContextualPrompt($userMessage, $context): string
{
    return <<<PROMPT
You are a Vietnam travel expert. The user is asking about a Vietnam travel experience
that may not be in our main database (e.g., lesser-known beaches, mountain trails).

Guidelines:
1. Only suggest REAL Vietnam locations you know about
2. DO NOT fabricate beaches, mountains, or attractions
3. If uncertain about a specific location, say so explicitly
4. Suggest realistic alternatives if the requested location is unclear

User Request: "$userMessage"

$context

Provide accurate, helpful information without inventing locations:
PROMPT;
}

    private function isVietnamSpecificQuery($userMessage): bool {
        $messageLower = strtolower($userMessage);
        
        $vietnamIndicators = [
            'vietnam', 'viet nam', 'hanoi', 'saigon', 'ho chi minh',
            'beach', 'bien', 'mountain', 'nui', 'mekong', 'halongbay',
            'hoi an', 'danang', 'nha trang', 'dalat', 'phuquoc'
        ];
        
        foreach ($vietnamIndicators as $indicator) {
            if (strpos($messageLower, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
   
    private function buildGenericTravelPrompt($userMessage, $context): string {
    return <<<PROMPT
You are an expert travel assistant AI. The user is asking about travel planning.

**User's Request:** "{$userMessage}"

{$context}

**Your Instructions:**
1. Provide a COMPLETE and detailed response in ENGLISH
2. For itineraries, include ALL days requested (don't stop early)
3. Structure clearly with headers and bullet points
4. Include practical information: attractions, activities, budget, tips
5. Be thorough - don't truncate your response

Generate your FULL response now:
PROMPT;
    }

    private function isResponseTruncated(string $response): bool{
        // Check for common truncation indicators
        $truncationIndicators = [
            'Day 5:', 'Day 6:', 'Day 7:',  // Stops mid-itinerary
            '* Activities:', '* Accommodation:',  // Stops mid-section
            '* Morning:', '* Afternoon:',  // Stops mid-day
        ];
        
        $lastLine = substr(trim($response), -200);  // Check last 200 chars
        
        foreach ($truncationIndicators as $indicator) {
            if (strpos($lastLine, $indicator) !== false && 
                !preg_match('/Day \d+:.*?Morning:.*?Afternoon:.*?Evening:/s', $lastLine)) {
                return true;
            }
        }
        
        // Check if ends abruptly without conclusion
        if (!preg_match('/(enjoy|safe travels|have a great trip|conclusion|summary)/i', $lastLine)) {
            return true;
        }
        
        return false;
    }

    private function buildFallbackCreativeResponse(string $userMessage): string
    {
        $messageLower = strtolower($userMessage);
        
        // Check for Korea/Korean
        if (strpos($messageLower, 'korea') !== false || strpos($messageLower, 'korean') !== false) {
            return <<<RESPONSE
I'd be happy to help you plan a trip to Korea! Here's a suggested itinerary:

**Planning Your Korea Trip**

**Popular Destinations:**
- **Seoul**: Modern city with palaces, shopping, and K-pop culture
- **Busan**: Beautiful beaches and seafood markets
- **Jeju Island**: Natural wonders and volcanic landscapes

**Sample 7-Day Itinerary:**
- **Days 1-3: Seoul**
  - Visit Gyeongbokgung Palace and Bukchon Hanok Village
  - Explore Myeongdong for shopping and street food
  - Experience Gangnam district and Han River
  
- **Days 4-5: Busan**
  - Haeundae Beach and Gamcheon Culture Village
  - Jagalchi Fish Market
  - Beomeosa Temple

- **Days 6-7: Jeju Island**
  - Seongsan Ilchulbong (Sunrise Peak)
  - Manjanggul Lava Tube
  - Beach relaxation

**Budget Estimate:**
- Flights: $300-$600 depending on season
- Accommodation: $40-$100/night
- Daily expenses: $50-$80 (food, transport, activities)
- Total for 7 days: ~$1,500-$2,500 per person

**Best Time to Visit:**
- Spring (April-May) or Fall (September-November) for mild weather

**Travel Tips:**
- Get a T-money card for public transportation
- Try local foods: kimchi, bibimbap, Korean BBQ
- Learn basic Korean phrases
- Download Papago translation app

Would you like more specific recommendations for any part of your Korea trip?
RESPONSE;
    }
    
    // Check for other international destinations
    if (preg_match('/(japan|tokyo|thailand|bangkok|singapore|paris|london)/i', $messageLower, $matches)) {
        $destination = ucfirst($matches[1]);
        return <<<RESPONSE
I'd love to help you plan your trip to {$destination}!

**General Travel Planning Tips:**

**Before You Go:**
- Check visa requirements for your nationality
- Book flights 2-3 months in advance for best prices
- Research accommodation options (hotels, hostels, Airbnb)
- Get travel insurance

**Budget Planning:**
- Research average daily costs for your destination
- Set aside 20% extra for unexpected expenses
- Book major attractions in advance for discounts

**What to Research:**
- Top attractions and must-see landmarks
- Local transportation options
- Cultural etiquette and customs
- Best neighborhoods to stay
- Local cuisine to try
- Safety tips and emergency contacts

**Itinerary Planning:**
- Don't over-schedule - leave room for spontaneity
- Group nearby attractions by day
- Consider travel time between locations
- Mix popular spots with local experiences

Would you like help planning a specific aspect of your {$destination} trip? Or would you like to explore tours and hotels in Vietnam instead?
RESPONSE;
    }
    
    // Generic fallback for other queries
    return <<<RESPONSE
I'd be happy to help you with your travel planning! 

While I specialize in Vietnam tours and hotels, I can offer some general travel advice:

**Travel Planning Essentials:**
- Research your destination thoroughly
- Check visa and entry requirements
- Book accommodation and major activities in advance
- Plan your budget with 20% buffer
- Get travel insurance
- Learn basic local phrases
- Download offline maps

**What I Can Help You With:**
- Tours and hotels throughout Vietnam
- Itinerary planning for Vietnamese destinations
- Budget travel options in Vietnam
- Cultural tips for visiting Vietnam

Would you like to explore what Vietnam has to offer? I have detailed information about tours and hotels in cities like Hanoi, Ho Chi Minh City, Da Nang, and many more!
RESPONSE;
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