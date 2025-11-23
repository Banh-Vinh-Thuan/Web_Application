<?php

declare(strict_types=1);

require_once './Logger.php';
require_once './config.php';

class GeminiService
{
    private int $retryAttempts = 3;
    private int $retryDelay = 2;
    private string $currentApiKey;
    private array $backupKeys;

    public function __construct(
        private readonly string $apiKey = Config::GEMINI_API_KEY,
        private readonly string $apiUrl = Config::GEMINI_API_URL,
        private readonly string $embeddingApiUrl = Config::GEMINI_EMBEDDING_API_URL
    ) {
        $this->currentApiKey = $this->apiKey;
        $this->backupKeys = Config::GEMINI_BACKUP_KEYS;
    }

    private function getNextApiKey(): ?string {
        if (!empty($this->backupKeys)) {
            $key = array_shift($this->backupKeys);
            Logger::info("Switching to backup API key");
            return $key;
        }
        return null;
    }


    /**
     * Generate text from prompt with retry logic
     */
    public function generateText(string $prompt, array $config = []): string {
        $requestData = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => $config['temperature'] ?? 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => $config['max_tokens'] ?? 2048,
            ],
        ];

        $lastError = null;
        
        for ($attempt = 1; $attempt <= $this->retryAttempts; $attempt++) {
            try {
                $response = $this->makeApiRequest($this->apiUrl, $requestData);
                $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($text === null) {
                    throw new RuntimeException("Empty response from Gemini API");
                }

                return $text;

            } catch (Exception $e) {
                $lastError = $e->getMessage();
                
                Logger::warning("Gemini API attempt $attempt failed", [
                    'error' => $lastError,
                    'attempt' => $attempt,
                    'current_key' => substr($this->currentApiKey, -10)
                ]);

                if (strpos($lastError, '401') !== false || strpos($lastError, '403') !== false) {
                    $backupKey = $this->getNextApiKey();
                    if ($backupKey) {
                        $this->currentApiKey = $backupKey;
                        Logger::info("Switched to backup API key, retrying...");
                        continue; // Retry immediately with new key
                    }
                }

                if ($attempt < $this->retryAttempts) {
                    sleep($this->retryDelay * $attempt);
                    continue;
                }

                throw new RuntimeException(
                    "Gemini API failed after {$this->retryAttempts} attempts: " . $lastError
                );
            }
        }

        throw new RuntimeException("Unexpected error in generateText");
    }

    /**
     * Generate embedding vector for text
     */
    public function generateEmbedding(string $text): array {
        $requestData = [
            'content' => ['parts' => [['text' => $text]]],
        ];

        $lastError = null;

        for ($attempt = 1; $attempt <= $this->retryAttempts; $attempt++) {
            try {
                $response = $this->makeApiRequest($this->embeddingApiUrl, $requestData);
                $embedding = $response['embedding']['values'] ?? null;

                if ($embedding === null) {
                    throw new RuntimeException("Empty embedding from Gemini API");
                }

                return $embedding;

            } catch (Exception $e) {
                $lastError = $e->getMessage();
                
                Logger::warning("Embedding attempt $attempt failed", [
                    'error' => $lastError,
                    'attempt' => $attempt
                ]);

                if (strpos($lastError, '401') !== false || strpos($lastError, '403') !== false) {
                    $backupKey = $this->getNextApiKey();
                    if ($backupKey) {
                        $this->currentApiKey = $backupKey;
                        continue;
                    }
                }

                if ($attempt < $this->retryAttempts) {
                    sleep($this->retryDelay * $attempt);
                    continue;
                }

                throw new RuntimeException(
                    "Embedding generation failed after {$this->retryAttempts} attempts"
                );
            }
        }

        throw new RuntimeException("Unexpected error in generateEmbedding");
    }

    /**
     * Make API request with error handling
     */
    private function makeApiRequest(string $url, array $data): array {
        $ch = curl_init();
        
        $fullUrl = $url . '?key=' . $this->currentApiKey;

        $options = [
            CURLOPT_URL => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => Config::API_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true, 
            CURLOPT_CONNECTTIMEOUT => 15, 
        ];

        curl_setopt_array($ch, $options);

        $responseJson = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        if ($responseJson === false) {
            throw new RuntimeException("cURL error: $error");
        }

        $response = json_decode($responseJson, true);

        if ($httpCode !== 200) {
            $errorMessage = $this->parseApiError($response, $httpCode);
            
            Logger::error("Gemini API returned non-200 status", [
                'http_code' => $httpCode,
                'error_message' => $errorMessage,
                'response_preview' => substr($responseJson, 0, 500),
                'api_key_suffix' => substr($this->currentApiKey, -10)
            ]);

            throw new RuntimeException("Gemini API error (HTTP $httpCode): $errorMessage");
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Failed to decode JSON response");
        }

        return $response;
    }

    /**
     * Parse API error messages
     */
    private function parseApiError(?array $response, int $httpCode): string {
        if (isset($response['error']['message'])) {
            return $response['error']['message'];
        }

        return match($httpCode) {
            400 => 'Bad Request - Invalid API parameters',
            401 => 'Unauthorized - Check API key',
            403 => 'Forbidden - API key may be restricted',
            429 => 'Rate Limit Exceeded - Too many requests',
            500, 502, 503 => 'Gemini API server error - Try again later',
            default => 'Unknown API Error'
        };
    }

    /**
     * Generate out-of-database response (international travel planning)
     */
    public function generateOutOfDatabaseResponse(
        $message,
        $context,
        $conversationHistory,
        $queryContext
    ): ?string {
        try {
            $conversationContext = $this->buildConversationContext($conversationHistory);
            $isPlanningQuery = $queryContext['is_planning_query'] ?? false;

            // Build appropriate prompt based on query type
            $prompt = $isPlanningQuery
                ? $this->buildTravelPlanPrompt($message, $conversationContext, $queryContext)
                : $this->buildGeneralPrompt($message, $conversationContext, $context);

            Logger::info("Generating OOD response", [
                'prompt_type' => $isPlanningQuery ? 'planning' : 'general',
                'destination' => $queryContext['mentioned_city'] ?? 'unknown'
            ]);

            return $this->generateText($prompt, [
                'temperature' => 0.7,
                'max_tokens' => 2048
            ]);

        } catch (Exception $e) {
            Logger::error("Out-of-database response generation failed", [
                'error' => $e->getMessage()
            ]);

            // Return null to trigger fallback in Controller
            return null;
        }
    }

    /**
     * Build travel planning prompt for international destinations
     */
    private function buildTravelPlanPrompt(string $message, string $conversationContext, array $queryContext): string {
        $destination = $this->extractDestination($message, $queryContext);
        $duration = $this->extractDuration($message);
        $budget = $this->extractBudget($message);

        $budgetInfo = $budget
            ? "Budget: {$budget['min']}-{$budget['max']} million VND"
            : "Budget: To be determined";

        return <<<PROMPT
You are an expert travel planner creating detailed itineraries.

{$conversationContext}

# User Request
"{$message}"

# Destination Details
- Location: {$destination}
- Duration: {$duration}
- {$budgetInfo}

# Instructions
Create a comprehensive day-by-day travel plan with this structure:

## Title Format
**{$duration} Travel Plan to {$destination}** ({$budgetInfo})

## Daily Itinerary Structure
For each day, provide:
- **🗓️ Day X - [Theme/Focus]**
- **Morning** (6:00-12:00): Activities, attractions, breakfast suggestions
- **Afternoon** (12:00-18:00): Main activities, lunch recommendations
- **Evening** (18:00-22:00): Dinner, entertainment, relaxation
- **💰 Estimated Daily Cost**: ~X,XXX,XXX VND

## Content Requirements
1. **Specific Attractions**: Name real landmarks, museums, markets, beaches
2. **Transportation**: How to get around (metro, bus, taxi, walking)
3. **Food Recommendations**: Local dishes and where to try them
4. **Cultural Tips**: Customs, etiquette, best times to visit
5. **Practical Details**: Opening hours, ticket prices (estimated)

## Budget Breakdown Table
At the end, provide:
```
| Category        | Estimated Cost (VND) |
|-----------------|----------------------|
| Accommodation   | X,XXX,XXX            |
| Food & Dining   | X,XXX,XXX            |
| Transportation  | X,XXX,XXX            |
| Attractions     | X,XXX,XXX            |
| Shopping        | X,XXX,XXX            |
| Miscellaneous   | X,XXX,XXX            |
| **Total**       | **X,XXX,XXX**        |
```

## Important Notes
- Use markdown formatting (##, **, -, emojis)
- Be specific and actionable
- Include money-saving tips where relevant
- Response must be in English
- Keep descriptions concise but informative

Generate the detailed travel plan now:
PROMPT;
    }

    /**
     * Build general travel advice prompt
     */
    private function buildGeneralPrompt(string $message, string $conversationContext, string $context): string {
        return <<<PROMPT
You are an expert travel assistant with deep knowledge of both Vietnam and international destinations.

{$conversationContext}

# Available Context
{$context}

# User Query
"{$message}"

# Instructions
Provide helpful, actionable travel advice following this structure:

1. **Direct Answer** (2-3 sentences)
   - Immediately address the user's question
   - Be clear and specific

2. **Key Recommendations** (3-4 points)
   - Top attractions or experiences
   - Practical considerations (visa, weather, best time)
   - Budget estimates if relevant
   - Transportation and accommodation tips

3. **Cultural & Practical Tips** (2-3 points)
   - Local customs and etiquette
   - Safety considerations
   - Language tips
   - What to pack

4. **Next Steps** (1-2 suggestions)
   - What to research further
   - Booking recommendations
   - Follow-up question to help user

## Guidelines
- Be conversational and encouraging
- Use bullet points for clarity
- Provide realistic estimates
- If uncertain about specific details, acknowledge it
- Keep response between 200-350 words
- Response must be in English

Provide your travel advice now:
PROMPT;
    }

    /**
     * Extract destination from message
     */
    private function extractDestination(string $message, array $queryContext): string {
        // Priority 1: From query context
        if (!empty($queryContext['mentioned_city'])) {
            return ucwords($queryContext['mentioned_city']);
        }

        // Priority 2: Parse from message with improved patterns
        $patterns = [
            '/(?:to|visit|in|for)\s+([A-Z][a-zA-ZÀ-ỹ\s]+?)(?:\s+for|\s+with|\s*$)/i',
            '/(?:plan|design|create)\s+(?:tour|trip)?\s*(?:to)?\s*([A-Z][a-zA-ZÀ-ỹ\s]+?)(?:\s+for|\s*$)/i',
            '/go\s+([A-Z][a-zA-ZÀ-ỹ\s]+?)\s+for/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $extracted = trim($matches[1]);
                $cleaned = preg_replace('/\s+(for|with|and|day|days|to)$/i', '', $extracted);
                if (strlen($cleaned) >= 3) {
                    return ucwords($cleaned);
                }
            }
        }

        return 'your destination';
    }

    /**
     * Extract duration from message
     */
    private function extractDuration(string $message): string {
        if (preg_match('/(\d+)\s*(?:day|days)/i', $message, $matches)) {
            $days = (int)$matches[1];
            return $days . '-Day';
        }
        return 'Multi-Day';
    }

    /**
     * Extract budget range from message
     */
    private function extractBudget(string $message): ?array {
        // Pattern: "between X to/and Y million"
        if (preg_match('/between\s+([\d,.]+)\s+(?:to|and)\s+([\d,.]+)\s*million/i', $message, $matches)) {
            return [
                'min' => $matches[1],
                'max' => $matches[2],
                'has_range' => true
            ];
        }

        // Pattern: "under/below X million"
        if (preg_match('/(?:under|below)\s+([\d,.]+)\s*million/i', $message, $matches)) {
            return [
                'min' => '0',
                'max' => $matches[1],
                'has_range' => false
            ];
        }

        // Pattern: "over/above X million"
        if (preg_match('/(?:over|above)\s+([\d,.]+)\s*million/i', $message, $matches)) {
            return [
                'min' => $matches[1],
                'max' => '∞',
                'has_range' => false
            ];
        }

        return null;
    }

    /**
     * Build conversation context from history
     */
    private function buildConversationContext($conversationHistory): string {
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

    /**
     * Generate Vietnamese response (for in-database results)
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

    /**
     * Build prompt for Vietnamese response
     */
    private function buildPrompt($userMessage, $context, $conversationHistory, $metadata) {
        $conversationContext = $this->buildConversationContext($conversationHistory);
        $isMultiCity = $this->detectMultiCityQuery($userMessage, $context);
        $isDurationOnly = $this->isDurationOnlyQuery($userMessage);

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
For TOURS (Duration-only, no specific city):
- \"I have some tours suitable for you:
  • **[Tour Name]** - [X] days - [Price] VND
  • **[Tour Name]** - [X] days - [Price] VND\"

For TOURS (City-specific):
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

Tours in [City2]:
• **[Tour Name]** - [X] days - [Price] VND\"

For MIXED CONTENT:
- Greet with acknowledgment of both types
- Present tours first with their city
- Transition to hotels
- Close with a connecting statement
- Clearly separate tours and hotels sections
- Specify city for each item

4. **End with Brief Comment**: Add a short helpful statement (1 sentence)

Provide your response now:";

        return $prompt;
    }

    /**
     * Check if query is duration-only (no city specified)
     */
    private function isDurationOnlyQuery(string $userMessage): bool {
        $messageLower = strtolower($userMessage);
        
        // Check if message has duration pattern
        $hasDuration = preg_match('/\b\d+\s*(?:day|days|ngày)\b/i', $messageLower);
        
        // Check if message mentions city
        $vietnameseCities = ['hanoi', 'ha noi', 'hcm', 'ho chi minh', 'saigon', 
                            'da nang', 'danang', 'hue', 'nha trang', 'hoi an',
                            'da lat', 'dalat', 'phu quoc', 'can tho', 'ha giang',
                            'phu yen', 'tay bac'];
        
        $hasCity = false;
        foreach ($vietnameseCities as $city) {
            if (strpos($messageLower, $city) !== false) {
                $hasCity = true;
                break;
            }
        }
        
        return $hasDuration && !$hasCity;
    }

    /**
     * Detect multi-city query
     */
    private function detectMultiCityQuery($userMessage, $context) {
        $messageLower = strtolower($userMessage);

        if (preg_match('/\b(hanoi|ha noi|hà nội).*\band\b.*(hue|huế|ho chi minh|saigon|da nang|nha trang|can tho|phu quoc)/i', $messageLower)) {
            return true;
        }

        if (preg_match_all('/(?:Tours|Hotels) in\s*([^:]+):/i', $context, $matches)) {
            $cities = array_unique($matches[1]);
            return count($cities) >= 2;
        }

        return false;
    }

    /**
     * Build context-based fallback response
     */
    private function buildContextBasedResponse($context, $userMessage) {
        if (empty(trim($context))) {
            return "I'm ready to help you plan your trip to Vietnam! Which destination are you interested in?";
        }

        $response = "";

        // Extract tours from context
        if (preg_match_all('/\*\*(.*?)\*\* - (\d+) days - ([\d,.]+) VND/s', $context, $tourMatches, PREG_SET_ORDER)) {
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
                        $response .= "• **" . $cityTours[1][$i] . "** - " . $cityTours[2][$i] .
                            " days - " . $cityTours[3][$i] . " VND\n";
                    }
                    $response .= "\n";
                }
            } else {
                // ✅ FIX: Check if duration-only query
                $isDurationOnly = $this->isDurationOnlyQuery($userMessage);
                
                if ($isDurationOnly) {
                    $response .= "I have some tours suitable for you:\n\n";
                } else {
                    $city = !empty($tourCities) ? trim($tourCities[0]) : 'Vietnam';
                    $response .= "I found these tours in **$city**:\n\n";
                }
                
                $count = min(6, count($tourMatches));
                for ($i = 0; $i < $count; $i++) {
                    $response .= "• **{$tourMatches[$i][1]}** - {$tourMatches[$i][2]} days - {$tourMatches[$i][3]} VND\n";
                }
            }

            $response .= "\nThese tours offer wonderful experiences!";
        }
        // Extract hotels from context
        elseif (preg_match_all('/\*\*(.*?)\*\* - Rating: ([\d.]+)\/5 - ([\d,.]+ *VND\/night)/s', $context, $hotelMatches, PREG_SET_ORDER)) {
            preg_match_all('/Hotels in (.*?):/s', $context, $cityMatches);
            $hotelCities = $cityMatches[1] ?? [];

            if (count($hotelCities) >= 2) {
                $response .= "Hotels available across multiple cities:\n\n";
                foreach ($hotelCities as $cityName) {
                    $response .= "**Hotels in " . trim($cityName) . ":**\n";
                    $citySection = $this->extractCitySection($context, "Hotels in " . trim($cityName));
                    preg_match_all('/\*\*(.*?)\*\* - Rating: ([\d.]+)\/5 - ([\d,.]+ *VND\/night)/', $citySection, $cityHotels);

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
     * Extract city section from context
     */
    private function extractCitySection($context, $cityHeader) {
        $startPos = strpos($context, $cityHeader);
        if ($startPos === false) return "";

        $nextCity = strpos($context, "\n\n", $startPos + strlen($cityHeader));
        if ($nextCity === false) {
            return substr($context, $startPos);
        }

        return substr($context, $startPos, $nextCity - $startPos);
    }

    /**
     * Check if response is generic
     */
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

    /**
     * Generate suggestions for user
     */
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
}

?>