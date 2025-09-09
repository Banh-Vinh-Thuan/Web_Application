<?php
session_start();

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$serverName = "localhost";
$dbUsername = "root";
$dbPassword = "4444";
$dbName = "travelscapes";

// Initialize database connection
$conn = mysqli_connect($serverName, $dbUsername, $dbPassword, $dbName);

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit();
}

// Include all service files
require_once './Logger.php';
require_once './CacheService.php';
require_once './UserService.php';
require_once './DatabaseService.php';
require_once './GeminiService.php';
require_once './GreetingService.php';
require_once './IntentAnalyzer.php';
require_once './DataRetriever.php';
require_once './ResponseGenerator.php';
require_once './OptimizedRAGTravelChatbot.php';

// Main request handling
try {
    $chatbot = new OptimizedRAGTravelChatbot($conn);
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'get_history':
                    echo json_encode($chatbot->getChatHistory());
                    exit;
                    
                case 'get_stats':
                    echo json_encode($chatbot->getSystemStats());
                    exit;
                    
                case 'clear_cache':
                    CacheService::clear();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Cache cleared successfully'
                    ]);
                    exit;
                    
                default:
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Invalid action parameter'
                    ]);
                    exit;
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Enhanced Vietnam Travel RAG Chatbot API',
            'version' => '3.0',
            'features' => [
                'Multi-user support',
                'Improved caching',
                'Detailed logging',
                'AI-powered suggestions',
                'International destination support'
            ],
            'endpoints' => [
                'POST /' => 'Send chat message',
                'GET /?action=get_history' => 'Get chat history',
                'GET /?action=get_stats' => 'Get system statistics',
                'GET /?action=clear_cache' => 'Clear system cache'
            ]
        ]);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['message'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request format. Expected JSON with "message" field.'
            ]);
            exit;
        }
        
        $message = trim($input['message']);
        $conversationHistory = $input['conversation_history'] ?? [];
        
        if (empty($message)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Message cannot be empty'
            ]);
            exit;
        }
        
        $response = $chatbot->processMessage($message, $conversationHistory);
        $response['timestamp'] = date('c');
        $response['user_id'] = UserService::getCurrentUserId();
        
        echo json_encode($response);
        exit;
    }
    
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use GET or POST.'
    ]);
    
} catch (Exception $e) {
    Logger::error("RAG Chatbot backend error", ['error' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error. Please try again later.',
        'debug' => $e->getMessage(), // For debugging
        'timestamp' => date('c')
    ]);
}

mysqli_close($conn);
?>