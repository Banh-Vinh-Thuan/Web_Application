<?php
require_once './Logger.php';

class GeminiService {
    private $apiKey;
    private $apiUrl;
    
    public function __construct() {
        $this->apiKey = "AIzaSyBKlus-HPPK2H14xstpE1VHsfkzbUkoRJA";
        $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";
    }
    
    /**
     * Generate international travel plan
     */
    public function generateInternationalPlan($userMessage, $cityName, $conversationHistory = []) {
        $conversationContext = $this->buildConversationContext($conversationHistory);
        
        $prompt = "You are a helpful international travel assistant. The user is asking about travel to {$cityName}.

{$conversationContext}

User's request: {$userMessage}

Please provide a helpful, detailed response about traveling to {$cityName}. Include practical information such as:
- Key attractions and activities
- Best time to visit
- Transportation tips
- Cultural considerations
- Budget estimates where relevant
- Travel requirements (visa, etc.) if applicable

Keep your response conversational, informative, and well-structured. Focus on being practical and helpful.";

        try {
            $startTime = microtime(true);
            $response = $this->callAPI($prompt);
            $duration = microtime(true) - $startTime;
            
            Logger::info("International plan generated", [
                'city' => $cityName,
                'duration_ms' => round($duration * 1000, 2)
            ]);
            
            return $response;
        } catch (Exception $e) {
            Logger::error("Failed to generate international plan", [
                'city' => $cityName,
                'error' => $e->getMessage()
            ]);
            
            return "I'd be happy to help you plan your trip to {$cityName}! Here are some general tips:

**Planning Your Visit:**
- Research visa requirements and travel documents
- Check weather conditions for your travel dates  
- Book flights and accommodation in advance
- Consider travel insurance

**What to Research:**
- Top attractions and landmarks
- Local transportation options
- Popular neighborhoods to explore
- Local cuisine and dining recommendations
- Cultural etiquette and customs

Would you like me to help you research specific aspects of your {$cityName} trip?";
        }
    }
    
    /**
     * Generate Vietnam travel response with database context
     */
    public function generateVietnameseResponse($userMessage, $databaseContext, $conversationHistory, $retrievalResult) {
        $conversationContext = $this->buildConversationContext($conversationHistory);
        $contextualPrompt = $this->buildContextualPrompt($userMessage, $databaseContext, $conversationContext, $retrievalResult);
        
        try {
            $startTime = microtime(true);
            $response = $this->callAPI($contextualPrompt);
            $duration = microtime(true) - $startTime;
            
            Logger::info("Vietnamese response generated", [
                'match_level' => $retrievalResult['match_level'],
                'duration_ms' => round($duration * 1000, 2)
            ]);
            
            return $response;
        } catch (Exception $e) {
            Logger::error("Failed to generate Vietnamese response", [
                'error' => $e->getMessage(),
                'match_level' => $retrievalResult['match_level']
            ]);
            
            throw $e; // Re-throw to be handled by fallback
        }
    }
    
    /**
     * Generate contextual suggestions using AI
     */
    public function generateSuggestions($userMessage, $botResponse, $retrievalResult) {
        $prompt = "Based on this travel conversation, suggest 4 natural follow-up questions a user might ask:

User asked: {$userMessage}
Bot responded about: " . (isset($retrievalResult['data']['cities'][0]['city']) ? $retrievalResult['data']['cities'][0]['city'] : 'travel planning') . "

Generate 4 short, practical follow-up questions that would be helpful for travel planning. Each should be 3-8 words.

Format as simple text, one per line, no numbering or bullets.";

        try {
            $response = $this->callAPI($prompt);
            $suggestions = array_filter(array_map('trim', explode("\n", $response)));
            return array_slice($suggestions, 0, 4);
        } catch (Exception $e) {
            Logger::error("Failed to generate AI suggestions", ['error' => $e->getMessage()]);
            return $this->getFallbackSuggestions($retrievalResult);
        }
    }
    
    private function getFallbackSuggestions($retrievalResult) {
        switch ($retrievalResult['match_level']) {
            case 'exact':
                return [
                    'Show me more options in this area',
                    'What\'s the best time to visit?',
                    'Find nearby accommodations',
                    'Tell me about local attractions'
                ];
            case 'same_city':
                return [
                    'Help me customize the duration',
                    'Show alternatives in nearby cities',
                    'Find hotels in the same area',
                    'What activities are included?'
                ];
            default:
                return [
                    'Show me popular destinations',
                    'Find tours within my budget',
                    'Plan a 3-day trip',
                    'What\'s included in packages?'
                ];
        }
    }
    
    private function buildConversationContext($conversationHistory) {
        if (empty($conversationHistory)) {
            return "";
        }
        
        $context = "Recent conversation:\n";
        $recentMessages = array_slice($conversationHistory, -6);
        
        foreach ($recentMessages as $msg) {
            if (isset($msg['role']) && isset($msg['message'])) {
                $role = $msg['role'] === 'user' ? 'User' : 'Assistant';
                $message = is_string($msg['message']) ? $msg['message'] : 
                        (isset($msg['message']['text']) ? $msg['message']['text'] : 'Invalid message format');
                
                if (strlen($message) > 150) {
                    $message = substr($message, 0, 147) . '...';
                }
                
                $context .= "{$role}: {$message}\n";
            }
        }
        
        return $context . "\n";
    }
    
    private function buildContextualPrompt($userMessage, $databaseContext, $conversationContext, $retrievalResult) {
        $prompt = "You are a helpful Vietnam travel assistant. Provide concise, accurate, and friendly responses.

{$conversationContext}

Available data:
{$databaseContext}

User's question: {$userMessage}

Response guidelines:
- Be conversational and helpful
- Use specific data when available (mention tour names, prices, locations)
- Keep responses focused and not too lengthy
- Provide practical travel advice
- If you have limited data, acknowledge it honestly
- Format prices in Vietnamese currency (VND)

";

        if (isset($retrievalResult['fallback_message']) && !empty($retrievalResult['fallback_message'])) {
            $prompt .= "\nContext: " . $retrievalResult['fallback_message'] . "\n";
        }

        $prompt .= "\nPlease respond:";
        
        return $prompt;
    }
    
    private function callAPI($prompt) {
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "topP" => 0.8,
                "topK" => 40,
                "maxOutputTokens" => 800
            ]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl . "?key=" . $this->apiKey,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception("Gemini API cURL error: " . curl_error($ch));
        }
        
        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        
        if (!$decodedResponse) {
            throw new Exception("Invalid JSON response from Gemini API");
        }
        
        if ($httpCode !== 200) {
            $errorMsg = isset($decodedResponse['error']['message']) ? 
                       $decodedResponse['error']['message'] : 'Unknown API error';
            throw new Exception("Gemini API error ($httpCode): $errorMsg");
        }
        
        if (!isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception("Unexpected response structure from Gemini API");
        }
        
        return $decodedResponse['candidates'][0]['content']['parts'][0]['text'];
    }
}

?>