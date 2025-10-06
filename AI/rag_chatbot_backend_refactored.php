<?php

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');
session_start();
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $conn = mysqli_connect("localhost", "root", "4444", "travelscapes");
    
    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8mb4");

    // Updated include list with error checking
    $requiredFiles = [
        './Logger.php',
        './config.php', 
        './CacheService.php',
        './UserService.php',
        './DatabaseService.php',
        './GeminiService.php',
        './GreetingService.php',
        './IntentAnalyzer.php',
        './ResponseGenerator.php',
        './HybridRetriever.php',
        './OptimizedRAGTravelChatbot.php'
    ];

    foreach ($requiredFiles as $file) {
        if (!file_exists($file)) {
            Logger::critical("Required file missing", ['file' => $file]);
            throw new Exception("Required file missing: $file");
        }
        require_once $file;
    }

    // Initialize chatbot with error handling
    try {
        $chatbot = new OptimizedRAGTravelChatbot($conn);
        Logger::info("Chatbot initialized successfully");
    } catch (Exception $initError) {
        Logger::critical("Chatbot initialization failed", [
            'error' => $initError->getMessage(),
            'trace' => $initError->getTraceAsString()
        ]);
        throw new Exception("Failed to initialize chatbot: " . $initError->getMessage());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handlePostRequest($chatbot);
    } else {
        respondWithError('Method not allowed', 405);
    }

} catch (Exception $e) {
    Logger::critical("Backend fatal error", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
    ]);
    
    error_log("Chatbot critical error: " . $e->getMessage());
    respondWithError('Service temporarily unavailable. Please try again.', 500);
    
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}

function handlePostRequest($chatbot) {
    try {
        $rawInput = file_get_contents('php://input');
        
        // Log raw input for debugging
        Logger::debug("Received POST request", [
            'raw_input_length' => strlen($rawInput),
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
        ]);
        
        if (empty($rawInput)) {
            Logger::warning("Empty POST body received");
            respondWithError('Empty request body', 400);
        }
        
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error("JSON decode failed", [
                'error' => json_last_error_msg(),
                'raw_input_preview' => substr($rawInput, 0, 200)
            ]);
            respondWithError('Invalid JSON format: ' . json_last_error_msg(), 400);
        }
        
        if (!$input || !isset($input['message'])) {
            Logger::warning("Invalid request structure", ['input' => $input]);
            respondWithError('Invalid request format. Message field required.', 400);
        }

        $message = trim($input['message']);
        $conversationHistory = $input['conversation_history'] ?? [];
        
        if (empty($message)) {
            Logger::warning("Empty message received");
            respondWithError('Message cannot be empty.', 400);
        }
        
        // Validate message length
        if (strlen($message) > 1000) {
            Logger::warning("Message too long", ['length' => strlen($message)]);
            respondWithError('Message too long. Please keep it under 1000 characters.', 400);
        }

        Logger::info("Processing user message", [
            'message_length' => strlen($message),
            'message_preview' => substr($message, 0, 50),
            'history_count' => count($conversationHistory)
        ]);

        // Process message with comprehensive error handling
        try {
            $response = $chatbot->processMessage($message, $conversationHistory);
            
            // CRITICAL: Validate response structure
            if (!is_array($response)) {
                Logger::error("Invalid response type from chatbot", [
                    'type' => gettype($response),
                    'value' => print_r($response, true)
                ]);
                throw new Exception("Chatbot returned invalid response type");
            }
            
            if (!isset($response['success'])) {
                Logger::error("Response missing 'success' field", [
                    'response_keys' => array_keys($response)
                ]);
                throw new Exception("Invalid response structure");
            }
            
            if ($response['success']) {
                // Validate response data
                if (!isset($response['response'])) {
                    Logger::error("Success response missing 'response' field");
                    throw new Exception("Invalid success response structure");
                }
                
                // Ensure response text is not empty
                if (empty(trim($response['response']['text'] ?? ''))) {
                    Logger::error("Response text is empty", [
                        'response_structure' => array_keys($response['response'])
                    ]);
                    
                    // Create fallback response
                    $response['response']['text'] = "I'm here to help you explore Vietnam! I can show you tours and hotels in popular destinations. What would you like to discover?";
                }
                
                Logger::info("Request processed successfully", [
                    'response_type' => $response['response']['type'] ?? 'unknown',
                    'has_data' => !empty($response['response']['data'] ?? [])
                ]);
                
                respondWithSuccess(['response' => $response['response']]);
                
            } else {
                // Handle error response from chatbot
                $errorMessage = $response['error'] ?? 'Unable to process request.';
                
                Logger::error("Chatbot returned error response", [
                    'error' => $errorMessage,
                    'full_response' => $response
                ]);
                
                // Return user-friendly error with fallback message
                respondWithError($errorMessage, 500, [
                    'fallback_message' => "I'm having trouble processing your request. Could you try rephrasing it or asking about tours or hotels in a specific city?"
                ]);
            }
            
        } catch (Exception $processError) {
            Logger::error("Message processing threw exception", [
                'error' => $processError->getMessage(),
                'trace' => $processError->getTraceAsString(),
                'message' => substr($message, 0, 100)
            ]);
            
            // Return helpful error response
            respondWithError(
                'Unable to process your message. Please try again.',
                500,
                [
                    'suggestion' => "Try asking about tours or hotels in cities like Hanoi, Da Nang, or Ho Chi Minh City.",
                    'error_code' => 'PROCESSING_ERROR'
                ]
            );
        }
        
    } catch (Exception $e) {
        Logger::error("Request handling failed", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        error_log("Request processing error: " . $e->getMessage());
        respondWithError(
            'Request processing failed. Please try again.',
            500,
            ['error_code' => 'REQUEST_HANDLER_ERROR']
        );
    }
}

function respondWithSuccess($data) {
    ob_clean();
    
    $response = array_merge(['success' => true], $data);
    
    // Validate response before sending
    if (!isset($response['response']['text'])) {
        Logger::critical("Attempting to send response without text field");
        $response['response']['text'] = "I'm ready to help you with your Vietnam travel plans!";
    }
    
    $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    if ($json === false) {
        Logger::critical("JSON encode failed in success response", [
            'error' => json_last_error_msg()
        ]);
        
        // Send minimal valid response
        echo json_encode([
            'success' => true,
            'response' => [
                'text' => 'Response generated but encoding failed. Please try again.',
                'type' => 'error',
                'data' => []
            ]
        ]);
    } else {
        echo $json;
    }
    
    exit();
}

function respondWithError($message, $httpCode = 400, $additionalData = []) {
    ob_clean();
    http_response_code($httpCode);
    
    $errorResponse = array_merge(
        [
            'success' => false,
            'error' => $message
        ],
        $additionalData
    );
    
    // Always include a helpful response object for the frontend
    if (!isset($errorResponse['response'])) {
        $errorResponse['response'] = [
            'text' => $additionalData['fallback_message'] ?? 
                     "I'm experiencing technical difficulties. Please try asking about tours or hotels in Vietnam's beautiful cities like Hanoi, Da Nang, or Ho Chi Minh City.",
            'type' => 'error',
            'data' => []
        ];
    }
    
    $json = json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
    
    if ($json === false) {
        Logger::critical("JSON encode failed in error response", [
            'error' => json_last_error_msg()
        ]);
        
        // Send minimal valid error
        echo json_encode([
            'success' => false,
            'error' => 'An error occurred',
            'response' => [
                'text' => 'Technical error. Please refresh and try again.',
                'type' => 'error',
                'data' => []
            ]
        ]);
    } else {
        echo $json;
    }
    
    Logger::warning("Error response sent", [
        'message' => $message,
        'http_code' => $httpCode,
        'additional_data' => $additionalData
    ]);
    
    exit();
}

?>