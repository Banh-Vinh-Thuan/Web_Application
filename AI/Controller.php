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

class OptimizedRAGTravelChatbot {
    private DatabaseService $dbService;
    private GeminiService $geminiService;
    private int $userId;
    private ResponseGenerator $responseGenerator;
    private array $vietnameseCities;
    private HybridRetriever $hybridRetriever;
    private FewShotIntentAnalyzer $intentAnalyzer;

    public function __construct($db) {
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

    public function processMessage(string $message, array $conversationHistory = []): array {
        $startTime = microtime(true);

        try {
            $sanitizedMessage = $this->sanitizeInput($message);

            if (GreetingService::isSimpleGreeting($sanitizedMessage)) {
                return GreetingService::generateGreetingResponse();
            }

            $intentResult = $this->intentAnalyzer->analyzeIntent($sanitizedMessage);
            $intent = $intentResult['intent'];
            $entities = $this->intentAnalyzer->extractEntities($sanitizedMessage, $this->vietnameseCities);

            Logger::info("Intent Analysis", [
                'intent' => $intent,
                'cities' => count($entities['cities'] ?? []),
                'has_conditions' => $entities['has_conditions'] ?? false,
                'is_international' => $entities['is_international'] ?? false
            ]);

            // FIXED: Handle international/out-of-scope queries FIRST - no retrieval needed
            if ($entities['is_international'] ?? false) {
                return $this->handleComplexOrOutOfScopeQuery($sanitizedMessage, $entities, $conversationHistory);
            }

            $intent = $this->refineMixedQueryIntent($sanitizedMessage, $intent, $entities);

            $retrievalResult = $this->hybridRetriever->hybridSearch($sanitizedMessage, $entities, $intent);

            $isSearchIntent = in_array($intent, ['tour_search', 'hotel_search', 'mixed_search']);
            $retrievalConfidence = $retrievalResult['confidence'] ?? 0;
            $minConfidenceThreshold = 0.40;

            // FIXED: Relax the fallback condition - allow generative response for low confidence OR empty results
            if ($isSearchIntent && ($retrievalConfidence < $minConfidenceThreshold || empty($retrievalResult['results']))) {
                Logger::warning("Low confidence or no results, using generative fallback.", [
                    'message' => $sanitizedMessage, 
                    'intent' => $intent, 
                    'confidence' => $retrievalConfidence,
                    'result_count' => count($retrievalResult['results'] ?? [])
                ]);
                
                // CRITICAL: Pass through to generative response WITHOUT blocking
                return $this->handleComplexOrOutOfScopeQuery($sanitizedMessage, $entities, $conversationHistory);
            }

            if (empty($retrievalResult['results'])) {
                return $this->generateFallbackResponse("I couldn't find any specific matches for your request.");
            }

            $response = $this->responseGenerator->generateHybridResponse(
                $sanitizedMessage,
                $retrievalResult,
                $conversationHistory
            );

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

    private function refineMixedQueryIntent($message, $currentIntent, $entities): string {
        $messageLower = strtolower($message);
        $mixedPattern1 = '/\b(tour|tours)\b.*\b(in|at|to)\b.*\band\b.*\b(hotel|hotels)\b.*\b(in|at|to)\b/i';
        $mixedPattern2 = '/\b(hotel|hotels)\b.*\b(in|at|to)\b.*\band\b.*\b(tour|tours)\b.*\b(in|at|to)\b/i';
        $mixedPattern3 = '/\b(tour|tours)\b.*\band\b.*\b(hotel|hotels)\b/i';
        
        if (preg_match($mixedPattern1, $messageLower) || 
            preg_match($mixedPattern2, $messageLower) ||
            preg_match($mixedPattern3, $messageLower)) {
            
            Logger::info("Detected mixed query pattern", ['message' => substr($message, 0, 100)]);
            return 'mixed_search';
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

    private function sanitizeInput($message) {
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

    private function handleComplexOrOutOfScopeQuery($message, $entities, $conversationHistory) {
        Logger::info("Handling complex/out-of-scope query with generative model", [
            'message' => substr($message, 0, 100),
            'is_international' => $entities['is_international'] ?? false,
            'message_length' => strlen($message)
        ]);

        try {
            // CRITICAL: Always call generative response - with detailed error logging
            $generativeText = $this->geminiService->generateCreativeResponse($message, $conversationHistory);

            if (empty($generativeText)) {
                Logger::error("Empty generative text returned");
                throw new Exception("Generative response was empty.");
            }

            Logger::info("Generative response created successfully", [
                'response_length' => strlen($generativeText),
                'response_preview' => substr($generativeText, 0, 100)
            ]);

            $response = [
                'text' => $generativeText,
                'type' => 'general_info',
                'layout_type' => 'default',
                'data' => [],
                'match_level' => 'generative',
                'confidence' => 0.85,
                'suggestions' => [
                    "Tell me more about tours in Hanoi",
                    "Find hotels in Ho Chi Minh City",
                    "What are Vietnam's top destinations?"
                ]
            ];

            try {
                $this->dbService->saveConversation($this->userId, $message, $response);
            } catch (Exception $saveError) {
                Logger::warning("Failed to save generative response conversation", ['error' => $saveError->getMessage()]);
            }

            return [
                'success' => true,
                'response' => $response,
                'processing_time' => 0,
                'debug_info' => [
                    'type' => 'generative_fallback',
                    'entities' => $entities,
                    'api_called' => true
                ]
            ];

        } catch (Exception $e) {
            Logger::error("Generative response handler failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message' => substr($message, 0, 100)
            ]);
            
            return $this->generateErrorResponse("I'm sorry, I couldn't generate a response for that specific request. Please try asking in a different way.");
        }
    }

    private function generateFallbackResponse($message) {
        $fallbackSuggestions = [
            'Show me popular tours in Hanoi',
            'Find hotels in Da Nang',
            'Plan a 3-day trip to Hoi An',
            'Tell me about Nha Trang attractions'
        ];

        return [
            'success' => true,
            'response' => [
                'text' => $message . "\n\nPerhaps you could try one of these suggestions?",
                'type' => 'fallback',
                'data' => [],
                'suggestions' => $fallbackSuggestions
            ]
        ];
    }

    private function generateErrorResponse($errorMessage) {
        Logger::error("Generating error response", ['error' => $errorMessage]);

        return [
            'success' => false,
            'error' => 'Unable to process your request. Please try again.',
            'response' => [
                'text' => "I'm experiencing technical difficulties. Please try asking about tours or hotels in Vietnam's beautiful cities like Hanoi, Da Nang, or Ho Chi Minh City.",
                'type' => 'error',
                'data' => [],
                'suggestions' => [
                    'Show me tours in Hanoi',
                    'Find hotels in Da Nang',
                    'What are popular destinations in Vietnam?',
                    'Help me plan a trip to Hoi An'
                ]
            ]
        ];
    }

    public function getChatHistory($limit = 50) {
        try {
            $history = $this->dbService->getChatHistory($this->userId, $limit);
            
            Logger::debug("Chat history retrieved", [
                'user_id' => $this->userId,
                'count' => count($history)
            ]);

            return $history;

        } catch (Exception $e) {
            Logger::error("Failed to get chat history", [
                'error' => $e->getMessage(),
                'userId' => $this->userId
            ]);
            return [];
        }
    }

    /**
     * Get system statistics for monitoring
     */
    public function getSystemStats() {
        try {
            $stats = [
                'status' => 'operational',
                'runtime_info' => [
                    'current_user' => $this->userId,
                    'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . 'MB',
                    'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
                    'php_version' => PHP_VERSION,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];

            return $stats;

        } catch (Exception $e) {
            Logger::error("Failed to get system stats", ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
?>