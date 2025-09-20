<?php
require_once './Logger.php';
require_once './config.php';

class EmbeddingService {
    private $apiKey;
    private $apiUrl;

    public function __construct() {
        // Sử dụng các hằng số từ file config
        $this->apiKey = Config::GEMINI_API_KEY; 
        $this->apiUrl = Config::GEMINI_EMBEDDING_API_URL;
    }

    /**
     * Generates an embedding vector for a given text.
     * @param string $text The text to embed.
     * @return array|null The embedding vector as an array of floats, or null on failure.
     */
    public function generateEmbedding($text) {
        $startTime = microtime(true);
        $data = [
            'model' => Config::GEMINI_EMBEDDING_MODEL,
            'content' => [
                'parts' => [
                    ['text' => $text]
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl . "?key=" . $this->apiKey,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => Config::API_TIMEOUT,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            Logger::error("Embedding API cURL error", ['error' => curl_error($ch)]);
            curl_close($ch);
            return null;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        if ($httpCode !== 200 || !isset($decodedResponse['embedding']['values'])) {
            $errorMsg = $decodedResponse['error']['message'] ?? 'Unknown API error';
            Logger::error("Embedding API error", [
                'http_code' => $httpCode,
                'error' => $errorMsg
            ]);
            return null;
        }

        $duration = microtime(true) - $startTime;
        Logger::info("Embedding generated successfully", [
            'duration_ms' => round($duration * 1000, 2)
        ]);

        return $decodedResponse['embedding']['values'];
    }
}
?>