<?php
require_once './Logger.php';
require_once './UserService.php';
require_once './DatabaseService.php';
require_once './GeminiService.php';
require_once './GreetingService.php';
require_once './IntentAnalyzer.php';
require_once './DataRetriever.php';
require_once './ResponseGenerator.php';
require_once './config.php';

class OptimizedRAGTravelChatbot {
    private $dbService;
    private $geminiService;
    private $userId;
    private $dataRetriever;
    private $responseGenerator;
    private $vietnameseCities;
       
    public function __construct($db) {
        $this->dbService = new DatabaseService($db);
        $this->geminiService = new GeminiService();
        $this->userId = UserService::getCurrentUserId();
        $this->vietnameseCities = Config::getVietnameseCities();
        $this->dataRetriever = new DataRetriever($this->dbService);
        $this->responseGenerator = new ResponseGenerator($this->geminiService);
        
        Logger::info("Chatbot initialized", ['userId' => $this->userId]);
    }

    public function processMessage($message, $conversationHistory = []) {
        $startTime = microtime(true);
        
        try {
            // Input validation and sanitization
            $message = trim($message);
            if (empty($message) || strlen($message) > 1000) {
                throw new InvalidArgumentException('Invalid message length');
            }
            
            // XSS prevention
            $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
            
            Logger::info("Processing message", [
                'userId' => $this->userId,
                'messageLength' => strlen($message)
            ]);
            
            // Handle simple greetings
            if (GreetingService::isSimpleGreeting($message)) {
                Logger::debug("Simple greeting detected");
                $response = GreetingService::generateGreetingResponse();
                $this->dbService->saveConversation($this->userId, "greeting", $response['response']);
                return $response;
            }
            
            // Analyze intent and extract entities
            $intent = IntentAnalyzer::analyzeIntent($message);
            $entities = IntentAnalyzer::extractEntities($message, $this->vietnameseCities);
            
            Logger::debug("Intent and entities extracted", [
                'intent' => $intent,
                'entities' => $entities
            ]);
            
            // Retrieve relevant data
            $retrievalResult = $this->dataRetriever->retrieveRelevantData($intent, $entities, $message);
            
            Logger::debug("Data retrieval completed", [
                'match_level' => $retrievalResult['match_level'],
                'is_international' => $retrievalResult['is_international'],
                'data_counts' => [
                    'tours' => count($retrievalResult['data']['tours'] ?? []),
                    'hotels' => count($retrievalResult['data']['hotels'] ?? []),
                    'cities' => count($retrievalResult['data']['cities'] ?? [])
                ]
            ]);
            
            // Generate response
            $response = $this->responseGenerator->generateContextualResponse($message, $retrievalResult, $conversationHistory);
            
            // Save conversation
            $this->dbService->saveConversation($this->userId, $message, $response);
            
            $processingTime = microtime(true) - $startTime;
            Logger::info("Message processed successfully", [
                'userId' => $this->userId,
                'processingTime' => round($processingTime * 1000, 2) . 'ms'
            ]);
            
            return [
                'success' => true,
                'response' => $response,
                'processing_time_ms' => round($processingTime * 1000, 2)
            ];
            
        } catch (InvalidArgumentException $e) {
            Logger::error("Invalid argument", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Logger::error("Processing error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => 'I apologize, but I encountered an error. Please try rephrasing your request.'
            ];
        }
    }
    
    /**
     * Get chat history for current user
     */
    public function getChatHistory() {
        try {
            $history = $this->dbService->getChatHistory($this->userId);
            
            return [
                'success' => true,
                'history' => $history
            ];
        } catch (Exception $e) {
            Logger::error("Error getting chat history", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Could not load chat history'
            ];
        }
    }
    
    /**
     * Get system statistics
     */
    public function getSystemStats() {
        try {
            $stats = $this->dbService->getSystemStats();
            
            return [
                'success' => true,
                'stats' => $stats
            ];
        } catch (Exception $e) {
            Logger::error("Error getting system stats", ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Could not load system statistics'
            ];
        }
    }
}

?>