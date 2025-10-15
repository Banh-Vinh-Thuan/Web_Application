<?php

require_once './Logger.php';
require_once './User.php';
require_once './Database.php';
require_once './Gemini.php';
require_once './GreetingService.php';
require_once './Intent.php';
require_once './Generator.php';
require_once './config.php';
require_once './Retriever.php';

class OptimizedRAGTravelChatbot
{
    private DatabaseService $dbService;
    private GeminiService $geminiService;
    private int $userId;
    private ResponseGenerator $responseGenerator;
    private array $vietnameseCities;
    private HybridRetriever $hybridRetriever;
    private FewShotIntentAnalyzer $intentAnalyzer;

    public function __construct($db)
    {
        try {
            $this->dbService = new DatabaseService($db);
            $this->geminiService = new GeminiService();
            $this->userId = UserService::getCurrentUserId();
            $this->vietnameseCities = Config::getVietnameseCities();
            $this->intentAnalyzer = new FewShotIntentAnalyzer();
            $this->hybridRetriever = new HybridRetriever($this->dbService);
            $this->responseGenerator = new ResponseGenerator($this->geminiService);

            Logger::info("RAG Chatbot initialized successfully", ['userId' => $this->userId]);
        } catch (Exception $e) {
            Logger::error("Failed to initialize chatbot", ['error' => $e->getMessage()]);
            throw new Exception("Chatbot initialization failed: " . $e->getMessage());
        }
    }

    public function processMessage(string $message, array $conversationHistory = []): array
    {
        $startTime = microtime(true);

        try {
            $sanitizedMessage = $this->sanitizeInput($message);

            // Check if greeting
            if (GreetingService::isSimpleGreeting($sanitizedMessage)) {
                return GreetingService::generateGreetingResponse();
            }

            // Analyze intent and extract entities
            $intentResult = $this->intentAnalyzer->analyzeIntent($sanitizedMessage);
            $intent = $intentResult['intent'];
            $entities = $this->intentAnalyzer->extractEntities($sanitizedMessage, $this->vietnameseCities);

            Logger::info("Intent Analysis", [
                'intent' => $intent,
                'cities' => count($entities['cities'] ?? []),
                'has_conditions' => $entities['has_conditions'] ?? false,
                'is_international' => $entities['is_international'] ?? false
            ]);

            // Route to appropriate handler
            if ($entities['is_international'] ?? false) {
                return $this->handleOutOfDatabaseQuery($sanitizedMessage, $entities, $conversationHistory);
            }

            // Refine mixed intent if needed
            $intent = $this->refineMixedQueryIntent($sanitizedMessage, $intent, $entities);

            // Perform hybrid search
            $retrievalResult = $this->hybridRetriever->hybridSearch($sanitizedMessage, $entities, $intent);

            // Check if results are acceptable
            $isSearchIntent = in_array($intent, ['tour_search', 'hotel_search', 'mixed_search']);
            $retrievalConfidence = $retrievalResult['confidence'] ?? 0;
            $minConfidenceThreshold = 0.40;

            if ($isSearchIntent && ($retrievalConfidence < $minConfidenceThreshold || empty($retrievalResult['results']))) {
                Logger::warning("Low confidence or no results, using embedding-based fallback.", [
                    'message' => $sanitizedMessage,
                    'intent' => $intent,
                    'confidence' => $retrievalConfidence
                ]);

                return $this->handleOutOfDatabaseQuery($sanitizedMessage, $entities, $conversationHistory);
            }

            if (empty($retrievalResult['results'])) {
                return $this->generateFallbackResponse("I couldn't find any specific matches for your request.");
            }

            // Generate hybrid response with retrieval results
            $response = $this->responseGenerator->generateHybridResponse(
                $sanitizedMessage,
                $retrievalResult,
                $conversationHistory
            );

            // Save to chat history
            try {
                $this->dbService->saveConversation($this->userId, $sanitizedMessage, $response);
            } catch (Exception $saveError) {
                Logger::warning("Failed to save conversation", ['error' => $saveError->getMessage()]);
            }

            $processingTime = microtime(true) - $startTime;

            Logger::info("Message processed successfully", [
                'processing_time_ms' => round($processingTime * 1000, 2),
                'confidence' => $retrievalResult['confidence'] ?? 0,
                'response_type' => $response['type'] ?? 'unknown'
            ]);

            return [
                'success' => true,
                'response' => $response,
                'processing_time' => round($processingTime * 1000, 2),
                'debug_info' => [
                    'intent' => $intent,
                    'retrieval_stats' => $retrievalResult['retrieval_stats'] ?? [],
                    'confidence' => $retrievalResult['confidence'] ?? 0
                ]
            ];

        } catch (InvalidArgumentException $e) {
            Logger::warning("Invalid input", ['error' => $e->getMessage()]);
            return $this->generateErrorResponse($e->getMessage());
        } catch (Exception $e) {
            Logger::error("Message processing error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->generateErrorResponse("I'm sorry, an unexpected error occurred. Please try again.");
        }
    }

    /**
     * Sanitize user input
     */
    private function sanitizeInput($message)
    {
        $message = trim($message);
        if (empty($message)) {
            throw new InvalidArgumentException('Message cannot be empty');
        }

        $maxLength = defined('Config::MAX_MESSAGE_LENGTH') ? Config::MAX_MESSAGE_LENGTH : 1000;
        if (strlen($message) > $maxLength) {
            throw new InvalidArgumentException('Message too long. Please keep it under ' . $maxLength . ' characters.');
        }

        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $message = preg_replace('/\s+/', ' ', $message);

        return $message;
    }

    /**
     * Refine mixed query intent detection
     */
    private function refineMixedQueryIntent($message, $currentIntent, $entities): string
    {
        $messageLower = strtolower($message);

        $mixedPatterns = [
            '/\b(tour|tours)\b.*\b(in|at|to)\b.*\band\b.*\b(hotel|hotels)\b.*\b(in|at|to)\b/i',
            '/\b(hotel|hotels)\b.*\b(in|at|to)\b.*\band\b.*\b(tour|tours)\b.*\b(in|at|to)\b/i',
            '/\b(tour|tours)\b.*\band\b.*\b(hotel|hotels)\b/i'
        ];

        foreach ($mixedPatterns as $pattern) {
            if (preg_match($pattern, $messageLower)) {
                Logger::info("Detected mixed query pattern", ['message' => substr($message, 0, 100)]);
                return 'mixed_search';
            }
        }

        if (count($entities['cities'] ?? []) >= 2) {
            $hasTour = preg_match('/\b(tour|tours)\b/', $messageLower);
            $hasHotel = preg_match('/\b(hotel|hotels)\b/', $messageLower);

            if ($hasTour && $hasHotel) {
                Logger::info("Detected mixed query (2 cities + both keywords)");
                return 'mixed_search';
            }
        }

        return $currentIntent;
    }

    /**
     * IMPROVED: Handle queries outside database scope
     */
    private function handleOutOfDatabaseQuery($message, $entities, $conversationHistory) {
        Logger::info("Handling out-of-database query", [
            'message' => substr($message, 0, 100),
            'is_international' => $entities['is_international'] ?? false
        ]);

        try {
            // Step 1: Analyze query context
            $queryContext = $this->analyzeOutOfDatabaseQuery($message, $entities);

            // Step 2: Try to generate embedding (may fail if API is down)
            $queryEmbedding = null;
            $similarItems = [];

            try {
                $queryEmbedding = $this->generateQueryEmbeddingWithRetry($message);
                if ($queryEmbedding) {
                    $similarItems = $this->dbService->findSimilarItemsByVector($queryEmbedding, 8);
                }
            } catch (Exception $embeddingError) {
                Logger::warning("Embedding generation failed, proceeding without semantic context", [
                    'error' => $embeddingError->getMessage()
                ]);
            }

            // Step 3: Build context
            $context = !empty($similarItems)
                ? $this->buildIntelligentContext($similarItems, $message, $queryContext)
                : $this->buildTopicBasedContext($message, $queryContext);

            // Step 4: Generate response using Gemini
            $responseText = $this->geminiService->generateOutOfDatabaseResponse(
                $message,
                $context,
                $conversationHistory,
                $queryContext
            );

            // CRITICAL FIX: Handle empty/null response (API failure)
            if (empty(trim($responseText ?? ''))) {
                Logger::warning("Gemini returned empty response, using structured fallback", [
                    'raw_response' => $responseText,
                    'is_null' => $responseText === null
                ]);
                $responseText = $this->buildStructuredFallback($queryContext);
            }

            Logger::info("OOD response successfully generated", [
                'length' => strlen($responseText),
                'preview' => substr($responseText, 0, 100)
            ]);

            Logger::info("Out-of-database response generated", [
                'response_length' => strlen($responseText),
                'similar_items' => count($similarItems),
                'had_embedding' => $queryEmbedding !== null
            ]);

            return [
                'success' => true,
                'response' => [
                    'text' => $responseText,
                    'type' => 'out_of_database',
                    'layout_type' => 'default',
                    'data' => [],
                    'match_level' => 'out_of_database',
                    'confidence' => 0.7,
                    'suggestions' => $this->generateOutOfDatabaseSuggestions($queryContext),
                    'destination_info' => [
                        'type' => $queryContext['destination_type'],
                        'focus' => $queryContext['query_focus']
                    ]
                ],
                'processing_time' => 0,
                'debug_info' => [
                    'type' => 'embedding_based_fallback',
                    'similar_items' => count($similarItems),
                    'api_called' => true,
                    'used_fallback' => $queryEmbedding === null
                ]
            ];

        } catch (Exception $e) {
            Logger::error("Out-of-database handler failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->generateErrorResponse(
                "I'm having trouble with that query right now. For Vietnam travel, I can help you with cities like Hanoi, Da Nang, or Ho Chi Minh City. For international travel, please try again in a moment."
            );
        }
    }

    /**
     * Build structured fallback for OOD queries when API fails
     */
    private function buildStructuredFallback(array $queryContext): string {
        $destination = $queryContext['mentioned_city'] ?? 'your destination';
        $isPlanningQuery = $queryContext['is_planning_query'] ?? false;

        if ($isPlanningQuery) {
            return <<<FALLBACK
## Travel Planning for {$destination}

I'd be happy to help you plan your trip to **{$destination}**! Here's how to get started:

### 🗓️ Planning Essentials
- Research visa requirements and entry regulations
- Check weather conditions for your travel dates
- Book flights and accommodation in advance
- Purchase comprehensive travel insurance
- Download offline maps and translation apps

### 💰 Budget Considerations
- Accommodation costs (budget/mid-range/luxury)
- Daily food and dining expenses
- Local transportation options
- Entrance fees for attractions
- Shopping and souvenirs
- Emergency fund (10-15% of total)

### 🎯 Things to Research
**Must-See Attractions:**
- Top-rated landmarks and tourist sites
- Hidden gems and local favorites
- Opening hours and admission fees

**Local Transportation:**
- Public transit options (metro, buses, trains)
- Taxi services and ride-sharing apps
- Walking distances between attractions

**Cultural Tips:**
- Local customs and etiquette
- Basic phrases in local language
- Dress codes for cultural sites
- Tipping practices

### 📱 Recommended Resources
- Official tourism website for {$destination}
- Recent traveler reviews on TripAdvisor
- Travel blogs and vlogs
- Local travel forums and Facebook groups

**How else can I help with your {$destination} trip planning?**
FALLBACK;
        }

        return <<<FALLBACK
## Travel Information for {$destination}

I understand you're interested in **{$destination}**. Here's some helpful guidance:

### 🌍 General Travel Tips
**Planning Essentials:**
- Best time to visit and seasonal weather
- Visa requirements and application process
- Local currency and exchange methods
- Basic language phrases
- Safety tips and travel advisories

**What to Explore:**
- Major landmarks and cultural sites
- Local food markets and authentic dining
- Popular tourist areas and local neighborhoods
- Day trips and nearby attractions
- Cultural festivals and events

**Practical Considerations:**
- Local transportation and costs
- Average daily budget
- SIM cards and WiFi availability
- Power outlets and adapters
- Emergency contacts

### 🇻🇳 Alternative: Explore Vietnam
Since I specialize in Vietnam travel, I can provide detailed recommendations for:
- **Hanoi**: Rich cultural heritage and street food
- **Ho Chi Minh City**: Dynamic urban life and history
- **Da Nang**: Modern coastal city with beaches
- **Hoi An**: UNESCO ancient town
- **Nha Trang**: Beach paradise
- **Phu Quoc**: Tropical island getaway

I can help you plan detailed itineraries, find tours and hotels, and provide budget estimates for any Vietnamese destination!

**How can I assist you further?**
FALLBACK;
    }

    /**
     * Analyze out-of-database query
     */
    private function analyzeOutOfDatabaseQuery($message, $entities): array {
        $messageLower = strtolower($message);
        $isInternational = $entities['is_international'] ?? false;

        // Extract mentioned city
        $mentionedCity = $this->extractMentionedCity($message, $entities);
        $queryFocus = $this->detectQueryFocus($messageLower);

        // Enhanced planning query detection
        $isPlanningQuery = (bool)preg_match(
            '/\b(plan|design|help|organize|suggest|recommend|create|make|build|prepare|itinerary|schedule)\b/i',
            $message
        );

        // Detect if asking about visiting/traveling
        $isVisitQuery = (bool)preg_match(
            '/\b(visit|travel|go to|trip to|tour|explore|discover)\b/i',
            $message
        );

        if ($isVisitQuery && !$isPlanningQuery) {
            $isPlanningQuery = true;
        }

        $isAttractionFocus = (bool)preg_match(
            '/\b(attraction|see|visit|activity|experience|place|landmark|museum|temple|beach|mountain)\b/i',
            $message
        );

        return [
            'destination_type' => $isInternational ? 'international' : 'vietnam_related',
            'mentioned_city' => $mentionedCity,
            'query_focus' => $queryFocus,
            'is_planning_query' => $isPlanningQuery,
            'is_visit_query' => $isVisitQuery,
            'is_attraction_focus' => $isAttractionFocus,
            'has_conditions' => $entities['has_conditions'] ?? false
        ];
    }

    /**
     * Extract mentioned city from message
     */
    private function extractMentionedCity(string $message, array $entities): ?string {
        // Method 1: Check extracted entities
        if (!empty($entities['cities'])) {
            return $entities['cities'][0]['name'] ?? null;
        }

        // Method 2: Parse directly from message with CASE-INSENSITIVE patterns
        $patterns = [
            '/(?:visit|to|in|for)\s+([a-zA-Z][a-zA-ZÀ-ỹ\s]+?)(?:\s+for|\s+\d+|$)/i',
            '/([a-zA-Z]+)\s+for\s+\d+/i',
            '/(?:plan|design|create)\s+(?:tour|trip)?\s*(?:to)?\s*([a-zA-Z][a-zA-ZÀ-ỹ\s]+?)(?:\s+for|$)/i',
            '/trip\s+to\s+([a-zA-Z][a-zA-ZÀ-ỹ\s]+?)(?:\s+for|\s+\d+|$)/i',
            '/go\s+([a-zA-Z][a-zA-ZÀ-ỹ\s]+?)\s+for/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $city = trim($matches[1]);
                $city = preg_replace('/\s+(for|with|and|under|over|day|days|to|plan|visit|create)$/i', '', $city);
                if (strlen($city) >= 3) {
                    // Capitalize properly: "taiwan" -> "Taiwan"
                    return ucwords(strtolower($city));
                }
            }
        }

        // Method 3: Check for known international destinations (case-insensitive)
        $internationalKeywords = [
            'korea' => 'Korea', 'seoul' => 'Seoul',
            'japan' => 'Japan', 'tokyo' => 'Tokyo',
            'taiwan' => 'Taiwan', 'taipei' => 'Taipei',
            'thailand' => 'Thailand', 'bangkok' => 'Bangkok',
            'singapore' => 'Singapore'
        ];

        $messageLower = strtolower($message);
        foreach ($internationalKeywords as $keyword => $cityName) {
            if (strpos($messageLower, $keyword) !== false) {
                return $cityName;
            }
        }

        return null;
    }

    /**
     * Detect query focus/theme
     */
    private function detectQueryFocus($messageLower): string {
        $focusMap = [
            'beach' => ['beach', 'sea', 'coast', 'island', 'seaside'],
            'mountain' => ['mountain', 'hiking', 'trek', 'climbing', 'altitude'],
            'culture' => ['culture', 'tradition', 'history', 'temple', 'monument', 'heritage'],
            'food' => ['food', 'cuisine', 'restaurant', 'eat', 'taste', 'culinary'],
            'adventure' => ['adventure', 'extreme', 'thrilling', 'action', 'sports'],
            'nature' => ['nature', 'forest', 'waterfall', 'cave', 'national park', 'wildlife'],
            'budget' => ['budget', 'cheap', 'affordable', 'economical', 'backpack'],
            'luxury' => ['luxury', 'expensive', 'high-end', 'resort', 'premium'],
            'family' => ['family', 'kids', 'children', 'child-friendly']
        ];

        foreach ($focusMap as $focus => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($messageLower, $keyword) !== false) {
                    return $focus;
                }
            }
        }

        return 'general_travel';
    }

    /**
     * Build intelligent context from similar items
     */
    private function buildIntelligentContext($similarItems, $query, $queryContext): string {
        $context = "# Reference Information from Database\n\n";
        $context .= "Based on similar travel experiences in our database:\n\n";

        $tours = array_filter($similarItems, fn($item) => $item['item_type'] === 'tour');
        $hotels = array_filter($similarItems, fn($item) => $item['item_type'] === 'hotel');

        if (!empty($tours)) {
            $context .= "## Similar Tour Experiences:\n";
            foreach (array_slice($tours, 0, 3) as $tour) {
                $tourData = $this->dbService->getToursByIds([$tour['item_id']]);
                if (!empty($tourData)) {
                    $t = $tourData[0];
                    $context .= sprintf(
                        "- **%s** (%s): %d days, %s VND\n",
                        $t['tour_name'] ?? 'Tour',
                        $t['city_name'] ?? 'Vietnam',
                        $t['duration_days'] ?? 0,
                        number_format($t['price_per_person'] ?? 0)
                    );
                }
            }
            $context .= "\n";
        }

        if (!empty($hotels)) {
            $context .= "## Similar Accommodations:\n";
            foreach (array_slice($hotels, 0, 3) as $hotel) {
                $hotelData = $this->dbService->getHotelsByIds([$hotel['item_id']]);
                if (!empty($hotelData)) {
                    $h = $hotelData[0];
                    $context .= sprintf(
                        "- **%s** (%s): %s/5 stars, %s VND/night\n",
                        $h['hotel'] ?? $h['hotel_name'] ?? 'Hotel',
                        $h['city_name'] ?? 'Vietnam',
                        $h['ratings'] ?? 'N/A',
                        number_format($h['cost'] ?? 0)
                    );
                }
            }
            $context .= "\n";
        }

        $context .= "## Query Context\n";
        $context .= sprintf("Destination: %s\n", $queryContext['destination_type']);
        $context .= sprintf("Interest: %s\n", $queryContext['query_focus']);

        return $context;
    }

    /**
     * Build topic-based context
     */
    private function buildTopicBasedContext($message, $queryContext): string {
        $context = "# Travel Information Context\n\n";
        $context .= "User is asking about:\n";
        $context .= sprintf("- Destination: %s\n", $queryContext['destination_type']);

        if ($queryContext['mentioned_city']) {
            $context .= sprintf("- Location: %s\n", $queryContext['mentioned_city']);
        }

        $context .= sprintf("- Interest: %s\n", $queryContext['query_focus']);

        if ($queryContext['is_planning_query']) {
            $context .= "- Needs: Planning guidance\n";
        }

        return $context;
    }

    /**
     * Generate suggestions for OOD queries
     */
    private function generateOutOfDatabaseSuggestions($queryContext): array {
        $suggestions = [
            "Tell me about tours in Vietnam",
            "Find hotels in Ho Chi Minh City"
        ];

        $focusSuggestions = [
            'beach' => "Show me beach destinations in Vietnam",
            'mountain' => "Suggest mountain trekking experiences",
            'culture' => "Find culturally immersive tours",
            'food' => "Show me culinary tours in Vietnam",
            'family' => "Find family-friendly tours in Vietnam"
        ];

        if (isset($focusSuggestions[$queryContext['query_focus']])) {
            $suggestions[] = $focusSuggestions[$queryContext['query_focus']];
        }

        if ($queryContext['destination_type'] === 'international') {
            $suggestions[] = "Help me plan a trip to Vietnam instead";
        }

        $suggestions[] = "What are my best travel options?";

        return array_slice($suggestions, 0, 4);
    }

    /**
     * Generate query embedding with retry
     */
    private function generateQueryEmbeddingWithRetry($message, $maxRetries = 2): ?array {
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                return $this->geminiService->generateEmbedding($message);
            } catch (Exception $e) {
                Logger::warning("Embedding attempt $attempt failed", ['error' => $e->getMessage()]);
                if ($attempt < $maxRetries) {
                    sleep(1);
                }
            }
        }

        return null;
    }

    /**
     * Generate fallback response
     */
    private function generateFallbackResponse($message)
    {
        return [
            'success' => true,
            'response' => [
                'text' => $message . "\n\nTry one of these suggestions:",
                'type' => 'fallback',
                'data' => [],
                'suggestions' => [
                    'Show me popular tours in Hanoi',
                    'Find hotels in Da Nang',
                    'Plan a 3-day trip to Hoi An',
                    'Tell me about Nha Trang attractions'
                ]
            ]
        ];
    }

    /**
     * Generate error response
     */
    private function generateErrorResponse($errorMessage)
    {
        Logger::error("Generating error response", ['error' => $errorMessage]);

        return [
            'success' => false,
            'error' => 'Unable to process your request. Please try again.',
            'response' => [
                'text' => "I'm experiencing technical difficulties. Try asking about tours or hotels in Hanoi, Da Nang, or Ho Chi Minh City.",
                'type' => 'error',
                'data' => [],
                'suggestions' => [
                    'Show me tours in Hanoi',
                    'Find hotels in Da Nang',
                    'What are popular destinations?',
                    'Help me plan a trip'
                ]
            ]
        ];
    }

    /**
     * Get chat history
     */
    public function getChatHistory($limit = 50)
    {
        try {
            $history = $this->dbService->getChatHistory($this->userId, $limit);
            Logger::debug("Chat history retrieved", ['user_id' => $this->userId, 'count' => count($history)]);
            return $history;
        } catch (Exception $e) {
            Logger::error("Failed to get chat history", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get system stats
     */
    public function getSystemStats()
    {
        try {
            return [
                'status' => 'operational',
                'runtime_info' => [
                    'current_user' => $this->userId,
                    'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . 'MB',
                    'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
                    'php_version' => PHP_VERSION,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
        } catch (Exception $e) {
            Logger::error("Failed to get system stats", ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

?>