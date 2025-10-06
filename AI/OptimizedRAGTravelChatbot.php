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
 */
class OptimizedRAGTravelChatbot {
    private $dbService;
    private $geminiService;
    private $userId;
    private $responseGenerator;
    private $vietnameseCities;
    private $hybridRetriever;
    private $intentAnalyzer;

    public function __construct($db) {
        try {
            $this->dbService = new DatabaseService($db);
            $this->geminiService = new GeminiService();
            $this->userId = UserService::getCurrentUserId();
            $this->vietnameseCities = Config::getVietnameseCities();

            // CRITICAL FIX: Initialize intent analyzer
            $this->intentAnalyzer = new FewShotIntentAnalyzer();
            
            // Initialize the hybrid retriever with database service
            $this->hybridRetriever = new HybridRetriever($this->dbService);
            
            // Initialize response generator with gemini service
            $this->responseGenerator = new ResponseGenerator($this->geminiService);

            Logger::info("RAG Chatbot initialized successfully", [
                'userId' => $this->userId,
                'components' => [
                    'database' => 'connected',
                    'gemini' => 'initialized',
                    'intent_analyzer' => 'initialized',
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

            // STEP 1: Handle simple greetings efficiently
            if (GreetingService::isSimpleGreeting($message)) {
                Logger::debug("Simple greeting detected, bypassing RAG pipeline");
                return GreetingService::generateGreetingResponse();
            }

            // STEP 2: Intent Analysis & Entity Extraction
            // CRITICAL FIX: Use instance method instead of static
            $intentResult = $this->intentAnalyzer->analyzeIntent($message);
            $intent = $intentResult['intent'];
            $entities = $this->intentAnalyzer->extractEntities($message, $this->vietnameseCities);

            Logger::debug("Intent analysis completed", [
                'intent' => $intent,
                'confidence' => $intentResult['confidence'],
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
                Logger::debug("International destination detected");
                return $this->handleInternationalQuery($message, $entities, $conversationHistory);
            }

            // STEP 4: PARALLEL HYBRID RETRIEVAL
            Logger::debug("Starting parallel hybrid retrieval", [
                'intent' => $intent,
                'cities_count' => count($entities['cities'] ?? [])
            ]);

            $retrievalResult = $this->hybridRetriever->hybridSearch($message, $entities, $intent);

            // CRITICAL FIX: Better error handling
            if (!$retrievalResult['success']) {
                Logger::warning("Hybrid retrieval failed", [
                    'message' => substr($message, 0, 100),
                    'intent' => $intent,
                    'error' => $retrievalResult['error'] ?? 'unknown'
                ]);
                
                return $this->generateFallbackResponse(
                    "I'm having trouble finding specific results for your request. Let me help you explore some popular options in Vietnam instead."
                );
            }

            // CRITICAL FIX: Check if results are actually empty
            if (empty($retrievalResult['results'])) {
                Logger::warning("Retrieval returned no results", [
                    'message' => substr($message, 0, 100),
                    'intent' => $intent
                ]);
                
                return $this->generateFallbackResponse(
                    "I couldn't find specific matches for your request. Let me show you some popular travel options in Vietnam."
                );
            }

            Logger::info("Hybrid retrieval completed successfully", [
                'total_results' => count($retrievalResult['results']),
                'confidence' => $retrievalResult['confidence'],
                'retrieval_stats' => $retrievalResult['retrieval_stats'] ?? []
            ]);

            // STEP 5: AUGMENTATION & GENERATION
            try {
                $response = $this->responseGenerator->generateHybridResponse(
                    $message,
                    $retrievalResult,
                    $conversationHistory
                );
                
                // CRITICAL FIX: Validate response structure
                if (empty($response) || !is_array($response)) {
                    Logger::error("Invalid response from generator", [
                        'response_type' => gettype($response)
                    ]);
                    throw new Exception("Response generator returned invalid data");
                }
                
                // CRITICAL FIX: Ensure text field exists and is not empty
                if (empty(trim($response['text'] ?? ''))) {
                    Logger::error("Response text is empty");
                    $response['text'] = "I found some travel options for you based on your search.";
                }
                
            } catch (Exception $genError) {
                Logger::error("Response generation failed", [
                    'error' => $genError->getMessage(),
                    'results_count' => count($retrievalResult['results'])
                ]);
                
                // CRITICAL FIX: Create manual response from results
                $response = $this->createManualResponse($retrievalResult['results'], $intent);
            }

            // STEP 6: Save conversation
            try {
                $this->dbService->saveConversation($this->userId, $message, $response);
            } catch (Exception $saveError) {
                Logger::warning("Failed to save conversation", [
                    'error' => $saveError->getMessage()
                ]);
                // Don't fail the request if save fails
            }

            // STEP 7: Calculate performance metrics
            $processingTime = microtime(true) - $startTime;

            Logger::info("Message processed successfully", [
                'processing_time_ms' => round($processingTime * 1000, 2),
                'confidence' => $retrievalResult['confidence'],
                'response_type' => $response['type'] ?? 'unknown',
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
                'message' => substr($message ?? '', 0, 100)
            ]);

            return $this->generateErrorResponse($e->getMessage());
        }
    }

    /**
     * CRITICAL FIX: Create manual response when generator fails
     */
    private function createManualResponse($results, $intent) {
        if (empty($results)) {
            return [
                'text' => "I'm ready to help you explore Vietnam! What destination interests you?",
                'type' => 'fallback',
                'data' => []
            ];
        }
        
        // Separate tours and hotels
        $tours = array_filter($results, fn($r) => $r['item_type'] === 'tour');
        $hotels = array_filter($results, fn($r) => $r['item_type'] === 'hotel');
        
        $text = "";
        
        if (!empty($tours)) {
            $text .= "I found these tours for you:\n\n";
            foreach (array_slice($tours, 0, 3) as $result) {
                $tour = $result['item'];
                $text .= sprintf(
                    "• **%s** - %s days, %s VND\n",
                    $tour['tour_name'] ?? 'Tour',
                    $tour['duration_days'] ?? 'N/A',
                    number_format($tour['price_per_person'] ?? 0)
                );
            }
        }
        
        if (!empty($hotels)) {
            if (!empty($tours)) {
                $text .= "\n";
            }
            $text .= "I found these hotels for you:\n\n";
            foreach (array_slice($hotels, 0, 3) as $result) {
                $hotel = $result['item'];
                $text .= sprintf(
                    "• **%s** - Rating: %s/5, %s VND/night\n",
                    $hotel['hotel'] ?? $hotel['hotel_name'] ?? 'Hotel',
                    $hotel['ratings'] ?? 'N/A',
                    number_format($hotel['cost'] ?? 0)
                );
            }
        }
        
        return [
            'text' => $text,
            'type' => $intent,
            'data' => [
                'tours' => array_map(fn($r) => $r['item'], $tours),
                'hotels' => array_map(fn($r) => $r['item'], $hotels)
            ]
        ];
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