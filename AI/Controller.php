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

    /**
     * MAIN MESSAGE PROCESSING METHOD
     */
    public function processMessage(string $message, array $conversationHistory = []): array {
        $startTime = microtime(true);
        
        try {
            // STEP 1: Sanitize input
            $sanitizedMessage = $this->sanitizeInput($message);
            
            if (GreetingService::isSimpleGreeting($sanitizedMessage)) {
                return GreetingService::generateGreetingResponse();
            }
            
            // STEP 2: Intent and Entity Analysis
            $intentResult = $this->intentAnalyzer->analyzeIntent($sanitizedMessage);
            $intent = $intentResult['intent'];
            $entities = $this->intentAnalyzer->extractEntities($sanitizedMessage, $this->vietnameseCities);
            
            Logger::info("Intent Analysis", [
                'intent' => $intent,
                'cities' => count($entities['cities'] ?? []),
                'has_conditions' => $entities['has_conditions'] ?? false
            ]);
            
            // STEP 3: Check for international queries
            if ($entities['is_international'] ?? false) {
                return $this->handleInternationalQuery($sanitizedMessage, $entities, $conversationHistory);
            }
            
            // STEP 4: CRITICAL - Detect mixed queries and adjust intent
            $intent = $this->refineMixedQueryIntent($sanitizedMessage, $intent, $entities);
            
            // STEP 5: Hybrid Retrieval with refined intent
            $retrievalResult = $this->hybridRetriever->hybridSearch($sanitizedMessage, $entities, $intent);
            
            if (empty($retrievalResult['results'])) {
                Logger::warning("Retrieval returned no results", [
                    'message' => $sanitizedMessage,
                    'intent' => $intent
                ]);
                return $this->generateFallbackResponse("I couldn't find any specific matches for your request.");
            }
            
            // STEP 6: Generate Response
            $response = $this->responseGenerator->generateHybridResponse(
                $sanitizedMessage,
                $retrievalResult,
                $conversationHistory
            );
            
            // STEP 7: Save conversation
            try {
                $this->dbService->saveConversation($this->userId, $sanitizedMessage, $response);
            } catch (Exception $saveError) {
                Logger::warning("Failed to save conversation", ['error' => $saveError->getMessage()]);
            }
            
            // STEP 8: Finalize
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
        
        // Pattern 1: "tour in city1 and hotel in city2"
        $mixedPattern1 = '/\b(tour|tours)\b.*\b(in|at|to)\b.*\band\b.*\b(hotel|hotels)\b.*\b(in|at|to)\b/i';
        
        // Pattern 2: "hotel in city1 and tour in city2"
        $mixedPattern2 = '/\b(hotel|hotels)\b.*\b(in|at|to)\b.*\band\b.*\b(tour|tours)\b.*\b(in|at|to)\b/i';
        
        // Pattern 3: Simple "tour X and hotel Y"
        $mixedPattern3 = '/\b(tour|tours)\b.*\band\b.*\b(hotel|hotels)\b/i';
        
        if (preg_match($mixedPattern1, $messageLower) || 
            preg_match($mixedPattern2, $messageLower) ||
            preg_match($mixedPattern3, $messageLower)) {
            
            Logger::info("Detected mixed query pattern", ['message' => substr($message, 0, 100)]);
            return 'mixed_search';
        }
        
        // If we have 2+ cities and both tour/hotel keywords, likely mixed
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
     * Input sanitization and validation
     */
    private function sanitizeInput($message) {
        $message = trim($message);
        
        if (empty($message)) {
            throw new InvalidArgumentException('Message cannot be empty');
        }

        $maxLength = defined('Config::MAX_MESSAGE_LENGTH') ? Config::MAX_MESSAGE_LENGTH : 1000;
        if (strlen($message) > $maxLength) {
            throw new InvalidArgumentException('Message too long. Please keep it under ' . $maxLength . ' characters.');
        }

        // Remove potentially harmful content but preserve special characters for Vietnamese
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        
        // Remove excessive whitespace
        $message = preg_replace('/\s+/', ' ', $message);

        return $message;
    }

    /**
     * Handle international destination queries
     */
    private function handleInternationalQuery($message, $entities, $conversationHistory) {
        Logger::info("Processing international query", [
            'cities' => $entities['cities'] ?? [],
            'message' => substr($message, 0, 100)
        ]);

        try {
            $response = $this->responseGenerator->generateInternationalResponse(
                $message,
                $entities,
                $conversationHistory
            );
            
            // Validate response
            if (empty($response) || !is_array($response) || empty($response['text'] ?? '')) {
                throw new Exception("Invalid international response");
            }
            
        } catch (Exception $e) {
            Logger::error("International response generation failed", [
                'error' => $e->getMessage()
            ]);
            
            $cityName = $entities['cities'][0]['name'] ?? 'your destination';
            $response = [
                'text' => "I'd be happy to help you plan your trip to {$cityName}! However, I specialize in Vietnam travel. For international destinations, I recommend checking with a travel agency or online travel platforms for detailed information.",
                'type' => 'international',
                'data' => []
            ];
        }

        // Save conversation
        try {
            $this->dbService->saveConversation($this->userId, $message, $response);
        } catch (Exception $saveError) {
            Logger::warning("Failed to save international query", [
                'error' => $saveError->getMessage()
            ]);
        }

        return [
            'success' => true,
            'response' => $response,
            'processing_time' => 0,
            'debug_info' => [
                'type' => 'international',
                'cities' => $entities['cities'] ?? []
            ]
        ];
    }

    /**
     * Generate fallback response when retrieval fails
     */
    private function generateFallbackResponse($message) {
        $fallbackSuggestions = [
            'Show me popular tours in Hanoi',
            'Find hotels in Da Nang',
            'Plan a 3-day trip to Hoi An',
            'What are budget tours in Ho Chi Minh City?',
            'Tell me about Nha Trang attractions'
        ];

        return [
            'success' => true,
            'response' => [
                'text' => $message . "\n\nTry one of these popular queries:",
                'type' => 'fallback',
                'data' => [],
                'suggestions' => $fallbackSuggestions
            ]
        ];
    }

    /**
     * Generate error response for system failures
     */
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

    /**
     * Get user's chat history
     */
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