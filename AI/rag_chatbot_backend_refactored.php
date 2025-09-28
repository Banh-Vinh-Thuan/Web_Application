<?php

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
            throw new Exception("Required file missing: $file");
        }
        require_once $file;
    }

    $chatbot = new OptimizedRAGTravelChatbot($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handlePostRequest($chatbot);
    } else {
        respondWithError('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Chatbot error: " . $e->getMessage());
    respondWithError('Service temporarily unavailable.', 500);
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}

function handlePostRequest($chatbot) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['message'])) {
            respondWithError('Invalid request format.', 400);
        }

        $message = trim($input['message']);
        $conversationHistory = $input['conversation_history'] ?? [];
        
        if (empty($message)) {
            respondWithError('Message cannot be empty.', 400);
        }

        $response = $chatbot->processMessage($message, $conversationHistory);

        if ($response['success']) {
            respondWithSuccess(['response' => $response['response']]);
        } else {
            respondWithError($response['error'] ?? 'Unable to process request.', 500);
        }
        
    } catch (Exception $e) {
        error_log("Request processing error: " . $e->getMessage());
        respondWithError('Request processing failed.', 500);
    }
}

function respondWithSuccess($data) {
    ob_clean();
    echo json_encode(array_merge(['success' => true], $data));
    exit();
}

function respondWithError($message, $httpCode = 400) {
    ob_clean();
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'error' => $message]);
    exit();
}

?>