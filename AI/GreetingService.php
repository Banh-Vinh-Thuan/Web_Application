<?php
require_once './Logger.php';

class GreetingService {
    
    // Check if message is a simple greeting

    public static function isSimpleGreeting($message) {
        $message = strtolower(trim($message));
        $greetings = [
            'hi', 'hello', 'hey', 'greetings', 'good morning', 'good afternoon', 
            'good evening', 'howdy', 'hiya', 'what\'s up', 'whats up'
        ];
        
        return in_array($message, $greetings) || 
            (strlen($message) <= 15 && preg_match('/^(hi|hello|hey)\s*(there|!)*$/i', $message));
    }
    
    /**
     * Generate greeting response
     */
    public static function generateGreetingResponse() {
        $response = [
            'text' => "Hello! I'm your travel assistant. I can help you find tours and hotels in Vietnam, or plan international trips. How can I assist you today?",
            'type' => 'greeting',
            'data' => [],
            'match_level' => 'greeting',
            'suggestions' => [
                'Show me tours in Ho Chi Minh City',
                'Plan a 3-day trip to Tokyo', 
                'Find hotels in Nha Trang',
                'Help me plan a budget trip to Paris'
            ]
        ];
        
        Logger::debug("Greeting response generated");
        
        return [
            'success' => true,
            'response' => $response
        ];
    }
}

?>