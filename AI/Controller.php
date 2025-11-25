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

            // ✅ INTERNATIONAL QUERY: Let exceptions propagate
            if ($entities['is_international'] ?? false) {
                Logger::info("International query detected", [
                    'message' => substr($sanitizedMessage, 0, 100)
                ]);
                
                // This will throw exception if API fails, which is caught by outer try-catch
                $response = $this->handleInternationalQuery($sanitizedMessage, $entities, $conversationHistory);
                
                // Save successful conversation
                try {
                    $this->dbService->saveConversation($this->userId, $sanitizedMessage, $response);
                } catch (Exception $saveError) {
                    Logger::warning("Failed to save conversation", ['error' => $saveError->getMessage()]);
                }
                
                $processingTime = microtime(true) - $startTime;
                
                return [
                    'success' => true,
                    'response' => $response['response'],
                    'processing_time' => round($processingTime * 1000, 2),
                    'debug_info' => $response['debug_info']
                ];
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
                Logger::warning("Low confidence - redirecting to international handler", [
                    'message' => $sanitizedMessage,
                    'intent' => $intent,
                    'confidence' => $retrievalConfidence
                ]);

                // This will throw exception if API fails
                $response = $this->handleInternationalQuery($sanitizedMessage, $entities, $conversationHistory);
                
                try {
                    $this->dbService->saveConversation($this->userId, $sanitizedMessage, $response);
                } catch (Exception $saveError) {
                    Logger::warning("Failed to save conversation", ['error' => $saveError->getMessage()]);
                }
                
                $processingTime = microtime(true) - $startTime;
                
                return [
                    'success' => true,
                    'response' => $response['response'],
                    'processing_time' => round($processingTime * 1000, 2),
                    'debug_info' => $response['debug_info']
                ];
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
            
        } catch (RuntimeException $e) {
            // ✅ Handle Gemini API errors specifically
            Logger::error("Gemini API error in processMessage", [
                'error' => $e->getMessage(),
                'message' => substr($message, 0, 100)
            ]);
            
            return $this->generateErrorResponse(
                "I'm having trouble connecting to the travel planning service. Please try again in a moment."
            );
            
        } catch (Exception $e) {
            Logger::error("Message processing error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->generateErrorResponse(
                "I'm sorry, an unexpected error occurred. Please try again."
            );
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
     * Handle international queries
     * DIRECTLY REDIRECTS to GeminiService->generateInternationalPlan
     * which then uses buildTravelPlanPrompt
     */
    private function handleInternationalQuery($message, $entities, $conversationHistory) {
        // Extract destination name
        $cityName = !empty($entities['cities']) ? $entities['cities'][0]['name'] : 'your destination';

        Logger::info("Redirecting to generateInternationalPlan", [
            'destination' => $cityName,
            'message_preview' => substr($message, 0, 100)
        ]);

        // ✅ NO TRY-CATCH - Let exceptions propagate to main handler
        // This allows Gemini's retry logic to work properly
        $responseText = $this->geminiService->generateInternationalPlan(
            $message,
            $cityName,
            $conversationHistory
        );

        // ✅ Validate response
        if (empty(trim($responseText))) {
            Logger::warning("Empty response from generateInternationalPlan");
            throw new RuntimeException("Failed to generate travel plan - empty response");
        }

        Logger::info("International plan generated successfully", [
            'length' => strlen($responseText),
            'preview' => substr($responseText, 0, 100)
        ]);

        return [
            'success' => true,
            'response' => [
                'text' => $responseText,
                'type' => 'international_info',
                'layout_type' => 'default',
                'data' => [],
                'match_level' => 'international',
                'confidence' => 0.8,
                'suggestions' => [
                    "Tell me more about {$cityName} attractions",
                    "What's the best time to visit {$cityName}?",
                    "Help me plan my budget for {$cityName}",
                    "Show me tours in Vietnam instead"
                ]
            ],
            'processing_time' => 0,
            'debug_info' => [
                'type' => 'international',
                'destination' => $cityName
            ]
        ];
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