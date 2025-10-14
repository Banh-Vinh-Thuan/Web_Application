<?php

class Config {
    // Database Configuration
    const DB_HOST = "localhost";
    const DB_USERNAME = "root";
    const DB_PASSWORD = "4444";
    const DB_NAME = "travelscapes";
    const DB_CHARSET = "utf8mb4";

    // Gemini AI Configuration
    const GEMINI_API_KEY = "AIzaSyBKlus-HPPK2H14xstpE1VHsfkzbUkoRJA";
    const GEMINI_API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent"; // UPDATED MODEL
    const GEMINI_EMBEDDING_API_URL = "https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent";
    const GEMINI_EMBEDDING_MODEL = "models/text-embedding-004";

    // Generation Configuration
    const GEMINI_MAX_OUTPUT_TOKENS = 2048;  // NEW: Increased for long-form content
    const GEMINI_TEMPERATURE = 0.7;
    const GEMINI_TOP_K = 40;
    const GEMINI_TOP_P = 0.95;

    // API Timeouts
    const API_TIMEOUT = 30;

    // RAG Pipeline
    public const HYBRID_WEIGHTS = ['semantic' => 0.45, 'bm25' => 0.35, 'sql' => 0.20];

    // Cache Configuration
    public const CACHE_DEFAULT_TTL = 300;     
    public const CACHE_CITIES_TTL = 3600;       
    
    // Logging Configuration
    public const LOG_FILE = __DIR__ . '/rag_chatbot.log'; 
    public const DEBUG_MODE = false; 

    // Application Limits
    public const MAX_MESSAGE_LENGTH = 1000;
    public const MAX_CONVERSATION_HISTORY = 10;
    public const MAX_RESULTS_PER_CHANNEL = 15;
    public const MAX_FINAL_RESULTS = 10;

    // Search Quality Thresholds
    public const MIN_SIMILARITY_SCORE = 0.3;
    public const MIN_BM25_SCORE = 0.1;
    public const MIN_CONFIDENCE_THRESHOLD = 0.2;

    public static function getDatabaseConnection(): mysqli
    {
        try {
            $conn = new mysqli(self::DB_HOST, self::DB_USERNAME, self::DB_PASSWORD, self::DB_NAME);
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: ' . $conn->connect_error);
            }
            if (!$conn->set_charset(self::DB_CHARSET)) {
                throw new Exception('Error setting charset: ' . $conn->error);
            }
            return $conn;
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new RuntimeException("Unable to connect to the database.");
        }
    }

    // Get Vietnamese cities mapping for entity extraction
    public static function getVietnameseCities() {
        return [
            // Major cities with variations
            'ho chi minh' => ['id' => 11, 'name' => 'Ho Chi Minh City'],
            'ho chi minh city' => ['id' => 11, 'name' => 'Ho Chi Minh City'],
            'saigon' => ['id' => 11, 'name' => 'Ho Chi Minh City'],
            'hcmc' => ['id' => 11, 'name' => 'Ho Chi Minh City'],
            'sài gòn' => ['id' => 11, 'name' => 'Ho Chi Minh City'],

            'hanoi' => ['id' => 21, 'name' => 'Hanoi'],
            'ha noi' => ['id' => 21, 'name' => 'Hanoi'],
            'hà nội' => ['id' => 21, 'name' => 'Hanoi'],

            'da nang' => ['id' => 19, 'name' => 'Da Nang'],
            'danang' => ['id' => 19, 'name' => 'Da Nang'],
            'đà nẵng' => ['id' => 19, 'name' => 'Da Nang'],

            'nha trang' => ['id' => 12, 'name' => 'Nha Trang'],
            'nhatrang' => ['id' => 12, 'name' => 'Nha Trang'],

            'hoi an' => ['id' => 17, 'name' => 'Hoi An'],
            'hoian' => ['id' => 17, 'name' => 'Hoi An'],
            'hội an' => ['id' => 17, 'name' => 'Hoi An'],

            'hue' => ['id' => 13, 'name' => 'Hue'],
            'huế' => ['id' => 13, 'name' => 'Hue'],

            'da lat' => ['id' => 15, 'name' => 'Da Lat'],
            'dalat' => ['id' => 15, 'name' => 'Da Lat'],
            'đà lạt' => ['id' => 15, 'name' => 'Da Lat'],

            'phu quoc' => ['id' => 16, 'name' => 'Phu Quoc'],
            'phuquoc' => ['id' => 16, 'name' => 'Phu Quoc'],
            'phú quốc' => ['id' => 16, 'name' => 'Phu Quoc'],

            'can tho' => ['id' => 20, 'name' => 'Can Tho'],
            'cantho' => ['id' => 20, 'name' => 'Can Tho'],
            'cần thơ' => ['id' => 20, 'name' => 'Can Tho'],

            'ha giang' => ['id' => 18, 'name' => 'Ha Giang'],
            'hagiang' => ['id' => 18, 'name' => 'Ha Giang'],
            'hà giang' => ['id' => 18, 'name' => 'Ha Giang'],

            'phu yen' => ['id' => 14, 'name' => 'Phu Yen'],
            'phuyen' => ['id' => 14, 'name' => 'Phu Yen'],
            'phú yên' => ['id' => 14, 'name' => 'Phu Yen'],

            'tay bac' => ['id' => 10, 'name' => 'Tay Bac'],
            'taybac' => ['id' => 10, 'name' => 'Tay Bac'],
            'tây bắc' => ['id' => 10, 'name' => 'Tay Bac'],
            'northwest' => ['id' => 10, 'name' => 'Tay Bac'],
        ];
    }

    // Get system performance configuration
    public static function getPerformanceConfig() {
        return [
            'enable_caching' => true,
            'enable_logging' => true,
            'log_debug' => self::DEBUG_MODE,
            'max_concurrent_requests' => 10,
            'request_rate_limit' => 100, // per hour
            'embedding_cache_enabled' => true,
            'query_optimization' => true
        ];
    }

    // Get RAG pipeline configuration
    public static function getRAGConfig() {
        return [
            'retrieval_channels' => ['semantic', 'bm25', 'sql'],
            'channel_weights' => self::HYBRID_WEIGHTS,
            'min_results_per_channel' => 3,
            'max_results_per_channel' => self::MAX_RESULTS_PER_CHANNEL,
            'diversity_filtering' => true,
            'consensus_boosting' => true,
            'quality_thresholds' => [
                'semantic' => self::MIN_SIMILARITY_SCORE,
                'bm25' => self::MIN_BM25_SCORE,
                'confidence' => self::MIN_CONFIDENCE_THRESHOLD
            ]
        ];
    }

    // Get API configuration
    public static function getAPIConfig() {
        return [
            'gemini_api_key' => self::GEMINI_API_KEY,
            'gemini_api_url' => self::GEMINI_API_URL,
            'embedding_api_url' => self::GEMINI_EMBEDDING_API_URL,
            'embedding_model' => self::GEMINI_EMBEDDING_MODEL,
            'timeout' => self::API_TIMEOUT,
            'max_retries' => 3,
            'retry_delay' => 1000 // milliseconds
        ];
    }

    // Validate configuration
    public static function validateConfig() {
        $errors = [];
        if (empty(self::GEMINI_API_KEY) || self::GEMINI_API_KEY === 'YOUR_API_KEY_HERE') { $errors[] = 'Gemini API key is not configured'; }
        if (empty(self::DB_HOST) || empty(self::DB_NAME)) { $errors[] = 'Database configuration is incomplete'; }
        $weightSum = array_sum(self::HYBRID_WEIGHTS);
        if (abs($weightSum - 1.0) > 0.01) { $errors[] = 'Hybrid weights must sum to 1.0'; }
        if (!file_exists(self::LOG_FILE) && !is_writable(dirname(self::LOG_FILE))) { $errors[] = 'Log directory is not writable: ' . dirname(self::LOG_FILE); }
        return $errors;
    }

    // Get environment-specific configuration
    public static function getEnvironmentConfig() { $environment = $_SERVER['APP_ENV'] ?? 'production'; switch ($environment) { case 'development': return [ 'debug' => true, 'log_level' => 'DEBUG', 'cache_ttl' => 60, 'api_timeout' => 60 ]; case 'testing': return [ 'debug' => true, 'log_level' => 'INFO', 'cache_ttl' => 10, 'api_timeout' => 30 ]; case 'production': default: return [ 'debug' => false, 'log_level' => 'ERROR', 'cache_ttl' => self::CACHE_DEFAULT_TTL, 'api_timeout' => self::API_TIMEOUT ]; } }
}

$configErrors = Config::validateConfig();
if (!empty($configErrors)) {
    error_log("Configuration errors: " . implode(', ', $configErrors));
}
?>