<?php
class LLMService {
    private $apiKey;
    private $apiUrl;

    public function __construct() {
        $config = include __DIR__ . '/config.php';
        $this->apiKey = $config['llm_api_key'];
        $this->apiUrl = $config['llm_api_url'];

    }

    /**
     * Sends a query to the LLM and returns the parsed response.
     * @param string $query The user's natural language query.
     * @return array Parsed travel preferences and suggestions.
     */
    public function processQuery($query) {
        // Prepare the prompt for the LLM
        $prompt = $this->buildPrompt($query);

        // Make API request to LLM
        $response = $this->makeApiRequest($prompt);

        // Parse the LLM response
        return $this->parseResponse($response);
    }

    /**
     * Builds a structured prompt for the LLM to ensure consistent output.
     * @param string $query The user's query.
     * @return string The formatted prompt.
     */
    private function buildPrompt($query) {
        return <<<EOT
You are a travel planning assistant for a Vietnamese travel agency. The user has provided the following request: "$query".

Please analyze the request and provide a structured JSON response with the following:
1. **destination**: The primary destination city (e.g., "Dalat", "Phu Quoc").
2. **duration**: Number of days for the trip (integer, e.g., 4).
3. **budget**: Budget in VND (integer, e.g., 10000000).
4. **group_type**: Type of travelers (e.g., "solo", "couple", "family", "group").
5. **preferences**: Array of user preferences (e.g., ["beach", "cultural", "luxury"]).
6. **suggested_plan**: Array of daily plans, each with:
   - day: Day number (e.g., "Day 1").
   - location: Specific location or attraction.
   - activity: Planned activity.
   - estimated_cost: Cost in VND (integer).
   - notes: Additional notes or tips.
7. **tips**: General travel tips for the destination (string).

Ensure the response is in valid JSON format. If the query is unclear, make reasonable assumptions and note them in the response.
EOT;
    }

    /**
     * Makes an API request to the LLM provider.
     * @param string $prompt The formatted prompt.
     * @return array The LLM's response.
     */
    private function makeApiRequest($prompt) {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-4',

            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 1000,
            'temperature' => 0.7
        ]));

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('LLM API request failed: ' . curl_error($ch));
        }
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Parses the LLM response into a structured format.
     * @param array $response The raw LLM API response.
     * @return array Parsed travel plan data.
     */
    private function parseResponse($response) {
        if (isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];

            // 🔧 CLEAN CODE BLOCK
            $content = preg_replace('/^```json|```$/m', '', $content);
            $content = trim($content);

            $parsed = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $parsed;
            } else {
                throw new Exception('Invalid JSON response from LLM: ' . $content);
            }
        } else {
            throw new Exception('Unexpected LLM response format.');
        }
    }
}
?>