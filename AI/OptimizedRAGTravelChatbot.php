<?php
require_once './Logger.php';
require_once './UserService.php';
require_once './DatabaseService.php';
require_once './GeminiService.php';
require_once './GreetingService.php';
require_once './IntentAnalyzer.php';
require_once './ResponseGenerator.php';
require_once './config.php';
require_once './HybridRetriever.php';

/**
 * Main RAG Travel Chatbot Controller
 * Implements Complete Parallel Hybrid Retrieval Architecture
 * 
 * ARCHITECTURE OVERVIEW:
 * 1. Intent Analysis & Entity Extraction
 * 2. Parallel Hybrid Retrieval (Semantic + BM25 + SQL)
 * 3. Result Merging & Reranking
 * 4. Context Augmentation
 * 5. LLM Response Generation
 */
class OptimizedRAGTravelChatbot {
    private $dbService;
    private $geminiService;
    private $userId;
    private $responseGenerator;
    private $vietnameseCities;
    private $hybridRetriever;

    public function __construct($db) {
        try {
            $this->dbService = new DatabaseService($db);
            $this->geminiService = new GeminiService();
            $this->userId = UserService::getCurrentUserId();
            $this->vietnameseCities = Config::getVietnameseCities();

            // Initialize the hybrid retriever with database service
            $this->hybridRetriever = new HybridRetriever($this->dbService);
            
            // Initialize response generator with gemini service
            $this->responseGenerator = new ResponseGenerator($this->geminiService);

            Logger::info("RAG Chatbot initialized successfully", [
                'userId' => $this->userId,
                'components' => [
                    'database' => 'connected',
                    'gemini' => 'initialized', 
                    'hybrid_retriever' => 'ready',
                    'response_generator' => 'ready'
                ]
            ]);

        } catch (Exception $e) {
            Logger::error("Failed to initialize chatbot", ['error' => $e->getMessage()]);
            throw new Exception("Chatbot initialization failed: " . $e->getMessage());
        }
    }

    /**
     * MAIN MESSAGE PROCESSING METHOD
     * Implements Full RAG Pipeline:
     * R - Retrieval (Parallel Hybrid: Semantic + BM25 + SQL)
     * A - Augmentation (Context building with retrieved data)
     * G - Generation (LLM response with enriched context)
     */
    public function processMessage($message, $conversationHistory = []) {
        $startTime = microtime(true);

        try {
            // STEP 0: Input validation and sanitization
            $message = $this->sanitizeInput($message);
            
            Logger::info("Processing message", [
                'message' => substr($message, 0, 100),
                'history_count' => count($conversationHistory)
            ]);

            // STEP 1: Handle simple greetings efficiently (bypass RAG for performance)
            if (GreetingService::isSimpleGreeting($message)) {
                Logger::debug("Simple greeting detected, bypassing RAG pipeline");
                return GreetingService::generateGreetingResponse();
            }

            // STEP 2: Intent Analysis & Entity Extraction
            $intent = IntentAnalyzer::analyzeIntent($message);
            $entities = IntentAnalyzer::extractEntities($message, $this->vietnameseCities);

            Logger::debug("Intent analysis completed", [
                'intent' => $intent,
                'entities_found' => [
                    'cities' => count($entities['cities'] ?? []),
                    'has_budget' => !empty($entities['budget']),
                    'has_duration' => !empty($entities['duration']),
                    'has_rating' => !empty($entities['rating']),
                    'is_international' => $entities['is_international'] ?? false
                ]
            ]);

            // STEP 3: Route based on destination type
            if ($entities['is_international']) {
                Logger::debug("International destination detected, routing to international handler");
                return $this->handleInternationalQuery($message, $entities, $conversationHistory);
            }

            // STEP 4: PARALLEL HYBRID RETRIEVAL
            // Execute Semantic, BM25, and SQL searches simultaneously
            Logger::debug("Starting parallel hybrid retrieval", [
                'intent' => $intent,
                'entities' => $entities
            ]);

            $retrievalResult = $this->hybridRetriever->hybridSearch($message, $entities, $intent);

            if (!$retrievalResult['success']) {
                Logger::warning("Hybrid retrieval failed", [
                    'message' => substr($message, 0, 100),
                    'intent' => $intent
                ]);
                return $this->generateFallbackResponse(
                    "I couldn't find relevant information for your request. Please try rephrasing or being more specific about your travel needs."
                );
            }

            Logger::info("Hybrid retrieval completed successfully", [
                'total_results' => count($retrievalResult['results']),
                'confidence' => $retrievalResult['confidence'],
                'retrieval_stats' => $retrievalResult['retrieval_stats'] ?? []
            ]);

            // STEP 5: AUGMENTATION & GENERATION
            // Generate contextualized response using retrieved data
            $response = $this->responseGenerator->generateHybridResponse(
                $message,
                $retrievalResult,
                $conversationHistory
            );

            // STEP 6: Save conversation for future context
            $this->dbService->saveConversation($this->userId, $message, $response);

            // STEP 7: Calculate and log performance metrics
            $processingTime = microtime(true) - $startTime;

            Logger::info("Message processed successfully", [
                'processing_time_ms' => round($processingTime * 1000, 2),
                'confidence' => $retrievalResult['confidence'],
                'response_type' => $response['type'] ?? 'unknown',
                'layout_type' => $response['layout_type'] ?? 'default',
                'items_returned' => count($retrievalResult['results'])
            ]);

            return [
                'success' => true,
                'response' => $response,
                'processing_time' => round($processingTime * 1000, 2),
                'debug_info' => [
                    'intent' => $intent,
                    'retrieval_stats' => $retrievalResult['retrieval_stats'] ?? [],
                    'confidence' => $retrievalResult['confidence']
                ]
            ];

        } catch (Exception $e) {
            Logger::error("Message processing error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message' => substr($message, 0, 100)
            ]);

            return $this->generateErrorResponse($e->getMessage());
        }
    }

    /**
     * Input sanitization and validation
     */
    private function sanitizeInput($message) {
        $message = trim($message);
        
        if (empty($message)) {
            throw new InvalidArgumentException('Message cannot be empty');
        }

        if (strlen($message) > Config::MAX_MESSAGE_LENGTH) {
            throw new InvalidArgumentException('Message too long. Please keep it under ' . Config::MAX_MESSAGE_LENGTH . ' characters.');
        }

        // Remove potentially harmful content
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        
        // Remove excessive whitespace
        $message = preg_replace('/\s+/', ' ', $message);

        return $message;
    }

    /**
     * Handle international destination queries (non-Vietnam)
     */
    private function handleInternationalQuery($message, $entities, $conversationHistory) {
        Logger::info("Processing international query", [
            'cities' => $entities['cities'] ?? [],
            'message' => substr($message, 0, 100)
        ]);

        $response = $this->responseGenerator->generateInternationalResponse(
            $message,
            $entities,
            $conversationHistory
        );

        // Save conversation for history
        $this->dbService->saveConversation($this->userId, $message, $response);

        return [
            'success' => true,
            'response' => $response,
            'processing_time' => 0, // International queries are fast
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
            'Show me popular tours in Vietnam',
            'Find hotels in Ho Chi Minh City',
            'Plan a 3-day trip to Da Lat',
            'What are the best budget tours?',
            'Tell me about Nha Trang attractions'
        ];

        return [
            'success' => true,
            'response' => [
                'text' => $message,
                'type' => 'fallback',
                'layout_type' => 'default',
                'data' => [],
                'match_level' => 'fallback',
                'confidence' => 0.2,
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
            'error' => 'I encountered an error processing your request. Please try again in a moment.',
            'suggestions' => [
                'Try rephrasing your question',
                'Ask about tours or hotels in Vietnam',
                'Start with a simple greeting',
                'Check your internet connection'
            ],
            'debug_error' => $errorMessage // Only in development
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
            $stats = $this->dbService->getSystemStats();
            
            // Add runtime statistics
            $stats['runtime_info'] = [
                'current_user' => $this->userId,
                'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . 'MB',
                'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
                'php_version' => PHP_VERSION,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            return $stats;

        } catch (Exception $e) {
            Logger::error("Failed to get system stats", ['error' => $e->getMessage()]);
            return [];
        }
    }
}
?>