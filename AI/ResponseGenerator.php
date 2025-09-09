<?php
require_once './Logger.php';

/**
 * Service for generating contextual responses based on retrieved data
 */
class ResponseGenerator {
    private $geminiService;
    
    public function __construct($geminiService) {
        $this->geminiService = $geminiService;
    }
    
    /**
     * Generate contextual response based on user message and retrieved data
     */
    public function generateContextualResponse($userMessage, $retrievalResult, $conversationHistory) {
        try {
            // Handle international destinations with Gemini
            if ($retrievalResult['is_international'] && $retrievalResult['match_level'] === 'international_gemini') {
                $cityName = $this->extractCityNameFromMessage($userMessage);
                $geminiPlan = $this->geminiService->generateInternationalPlan($userMessage, $cityName, $conversationHistory);
                
                return [
                    'text' => $geminiPlan,
                    'type' => 'international_plan',
                    'data' => [],
                    'match_level' => 'international_gemini',
                    'suggestions' => [
                        'Tell me about local transportation',
                        'What are the must-see attractions?',
                        'Help me plan my budget',
                        'Cultural tips and etiquette'
                    ]
                ];
            }
            
            // Handle cases with no match or general inquiries
            if (in_array($retrievalResult['match_level'], ['no_city_match', 'general'])) {
                return [
                    'text' => $retrievalResult['fallback_message'],
                    'type' => $retrievalResult['match_level'] === 'general' ? 'general' : 'no_match',
                    'data' => [],
                    'match_level' => $retrievalResult['match_level'],
                    'suggestions' => $retrievalResult['suggestions']
                ];
            }
            
            // Use Gemini for Vietnamese destinations with database context
            $context = $this->buildVietnameseContext($retrievalResult['data']);
            $geminiResponse = $this->geminiService->generateVietnameseResponse(
                $userMessage, 
                $context, 
                $conversationHistory, 
                $retrievalResult
            );
            
            return $this->processGeminiResponse($geminiResponse, $retrievalResult, $userMessage);
            
        } catch (Exception $e) {
            Logger::error("Context generation error", ['error' => $e->getMessage()]);
            return $this->generateFallbackResponse($retrievalResult);
        }
    }
    
    /**
     * Extract city name from user message
     */
    private function extractCityNameFromMessage($message) {
        preg_match_all('/\b\p{Lu}[\p{Ll}]+(?:\s+\p{Lu}[\p{Ll}]+)*\b/u', $message, $matches);
        return !empty($matches[0]) ? $matches[0][0] : 'your destination';
    }
    
    /**
     * Build context for Vietnamese destinations
     */
    private function buildVietnameseContext($data) {
        $context = "Available travel data in Vietnam:\n\n";
        
        if (!empty($data['tours'])) {
            $context .= "AVAILABLE TOURS:\n";
            foreach ($data['tours'] as $tour) {
                $context .= "- {$tour['tour_name']} in {$tour['city_name']}: {$tour['duration_days']} days, " . 
                           number_format($tour['price_per_person']) . " VND per person\n";
            }
            $context .= "\n";
        }
        
        if (!empty($data['hotels'])) {
            $context .= "AVAILABLE HOTELS:\n";
            foreach ($data['hotels'] as $hotel) {
                $context .= "- {$hotel['hotel']} in {$hotel['city_name']}: Rating {$hotel['ratings']}/5";
                if ($hotel['cost'] > 0) {
                    $context .= ", " . number_format($hotel['cost']) . " VND per night";
                }
                $context .= "\n";
            }
        }
        
        return $context;
    }
    
    /**
     * Process Gemini response and format for frontend
     */
    private function processGeminiResponse($geminiText, $retrievalResult, $userMessage) {
        $type = 'general';
        $displayData = [];
        
        if (!empty($retrievalResult['data']['tours']) && !empty($retrievalResult['data']['hotels'])) {
            $type = 'destination_info';
            $displayData = [
                'tours' => array_slice($retrievalResult['data']['tours'], 0, 3),
                'hotels' => array_slice($retrievalResult['data']['hotels'], 0, 3)
            ];
        } elseif (!empty($retrievalResult['data']['tours'])) {
            $type = 'tour_search';
            $displayData = array_slice($retrievalResult['data']['tours'], 0, 6);
        } elseif (!empty($retrievalResult['data']['hotels'])) {
            $type = 'hotel_search';
            $displayData = array_slice($retrievalResult['data']['hotels'], 0, 6);
        }
        
        // Generate AI-powered suggestions
        $suggestions = $this->geminiService->generateSuggestions($userMessage, $geminiText, $retrievalResult);
        
        return [
            'text' => $geminiText,
            'type' => $type,
            'data' => $displayData,
            'match_level' => $retrievalResult['match_level'],
            'suggestions' => $suggestions
        ];
    }
    
    /**
     * Generate fallback response when Gemini fails
     */
    private function generateFallbackResponse($retrievalResult) {
        if (in_array($retrievalResult['match_level'], ['no_city_match', 'no_city_data'])) {
            return [
                'text' => $retrievalResult['fallback_message'] ?? "I'd be happy to help you with Vietnam travel planning! What would you like to know?",
                'type' => 'no_match',
                'data' => [],
                'match_level' => $retrievalResult['match_level'],
                'suggestions' => $retrievalResult['suggestions'] ?? [
                    'Plan a custom itinerary', 
                    'Show tours in major cities', 
                    'Get general travel advice'
                ]
            ];
        }
        
        if (!empty($retrievalResult['data']['tours'])) {
            return [
                'text' => $retrievalResult['fallback_message'] ?? "Here are some available tours:",
                'type' => 'tour_search',
                'data' => array_slice($retrievalResult['data']['tours'], 0, 6),
                'match_level' => $retrievalResult['match_level'],
                'suggestions' => ['Show me hotels', 'Compare prices', 'Best time to visit']
            ];
        } elseif (!empty($retrievalResult['data']['hotels'])) {
            return [
                'text' => $retrievalResult['fallback_message'] ?? "Here are some accommodation options:",
                'type' => 'hotel_search',
                'data' => array_slice($retrievalResult['data']['hotels'], 0, 6),
                'match_level' => $retrievalResult['match_level'],
                'suggestions' => ['Show me tours', 'Find restaurants', 'Local attractions']
            ];
        } else {
            return [
                'text' => $retrievalResult['fallback_message'] ?? "I'd be happy to help you with Vietnam travel planning! What would you like to know?",
                'type' => 'general',
                'data' => [],
                'match_level' => $retrievalResult['match_level'],
                'suggestions' => ['Show popular tours', 'Find hotels in cities', 'Plan a trip']
            ];
        }
    }
}

?>