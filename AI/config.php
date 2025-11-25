<?php

class Config {
    // Database Configuration
    const DB_HOST = "localhost";
    const DB_USERNAME = "root";
    const DB_PASSWORD = "4444";
    const DB_NAME = "travelscapes";
    const DB_CHARSET = "utf8mb4";

    // Gemini AI Configuration
    const GEMINI_API_KEY = "AIzaSyAKHCAo2Ci778MOo4EkHBvYJ-BivzxTIdE";
    const GEMINI_BACKUP_KEYS = [
        "AIzaSyDgfAp6MB4m9vAwI4e8RnT-yc-P3AKyPIk",
        "AIzaSyAd5KlnovRifyuQml6lheBNi0fE20KdNgo",
    ];
    // config.php
    const GEMINI_API_URL = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent";
    const GEMINI_EMBEDDING_API_URL = "https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent";
    const GEMINI_EMBEDDING_MODEL = "models/text-embedding-004";

    // Generation Configuration
    const GEMINI_MAX_OUTPUT_TOKENS = 3072;
    const GEMINI_TEMPERATURE = 0.7;
    const GEMINI_TOP_K = 40;
    const GEMINI_TOP_P = 0.95;
    const API_TIMEOUT = 60;

    // RAG Pipeline - Optimized weights
    const HYBRID_WEIGHTS = ['semantic' => 0.6, 'bm25' => 0.4];

    // Cache Configuration
    const CACHE_DEFAULT_TTL = 300;
    const CACHE_CITIES_TTL = 3600;

    // Logging Configuration
    const LOG_FILE = __DIR__ . '/rag_chatbot.log';
    const DEBUG_MODE = false;

    // Application Limits
    const MAX_MESSAGE_LENGTH = 1000;
    const MAX_CONVERSATION_HISTORY = 10;
    const MAX_RESULTS_PER_CHANNEL = 15;
    const MAX_FINAL_RESULTS = 10;

    // Search Quality Thresholds
    const MIN_SIMILARITY_SCORE = 0.3;
    const MIN_BM25_SCORE = 0.1;
    const MIN_CONFIDENCE_THRESHOLD = 0.2;

    public static function getDatabaseConnection(): mysqli {
        try {
            $conn = new mysqli(
                self::DB_HOST, 
                self::DB_USERNAME, 
                self::DB_PASSWORD, 
                self::DB_NAME
            );

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

    // Consolidated city mappings
    private static $cityMap = null;

    public static function getVietnameseCities(): array {
        if (self::$cityMap === null) {
            self::$cityMap = [
                'ho chi minh' => ['id' => 11, 'name' => 'Ho Chi Minh City'], 'ho chi minh city' => ['id' => 11, 'name' => 'Ho Chi Minh City'],
                'saigon' => ['id' => 11, 'name' => 'Ho Chi Minh City'], 'hcmc' => ['id' => 11, 'name' => 'Ho Chi Minh City'],
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
        return self::$cityMap;
    }

    public static function getOutOfDatabaseVietnameseCities(): array {
        return [
            // Mekong Delta
            'an giang', 'tien giang', 'my tho', 'ben tre', 'vinh long', 'long an', 'kien giang', 'rach gia', 'ca mau', 'bac lieu', 'soc trang', 'hau giang', 'dong thap',
            // Southeast
            'binh duong', 'thu dau mot', 'binh phuoc', 'tay ninh', 'dong nai', 'bien hoa', 'ba ria', 'vung tau',
            // Central Coast
            'binh thuan', 'phan thiet', 'mui ne', 'ninh thuan', 'quang nam', 'quang ngai', 'binh dinh', 'quy nhon', 'quang tri', 'quang binh', 'dong hoi', 'kon tum', 'gia lai', 'pleiku', 'dak lak', 'buon ma thuot', 'lam dong',
            // North Central
            'nghe an', 'vinh', 'ha tinh', 'thanh hoa', 'sam son',
            // Red River Delta
            'hai phong', 'quang ninh', 'ha long', 'halong', 'bac ninh', 'bac giang', 'hung yen', 'hai duong', 'nam dinh', 'ninh binh', 'tam coc', 'thai binh',
            // Northern Mountains
            'lao cai', 'sapa', 'sa pa', 'yen bai', 'tuyen quang', 'phu tho', 'thai nguyen', 'bac kan', 'cao bang', 'lang son', 'son la', 'dien bien', 'lai chau', 'hoa binh', 'mai chau',
            // Popular spots
            'cat ba', 'ba na hills', 'marble mountains', 'my son', 'cu chi tunnels', 'mekong delta'
        ];
    }

    public static function getPerformanceConfig(): array {
        return [
            'enable_caching' => true,
            'enable_logging' => true,
            'log_debug' => self::DEBUG_MODE,
            'max_concurrent_requests' => 10,
            'request_rate_limit' => 100,
            'embedding_cache_enabled' => true,
            'query_optimization' => true
        ];
    }

    public static function getRAGConfig(): array {
        return [
            'retrieval_channels' => ['semantic', 'bm25'],
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

    public static function getAPIConfig(): array {
        return [
            'gemini_api_key' => self::GEMINI_API_KEY,
            'gemini_api_url' => self::GEMINI_API_URL,
            'embedding_api_url' => self::GEMINI_EMBEDDING_API_URL,
            'embedding_model' => self::GEMINI_EMBEDDING_MODEL,
            'timeout' => self::API_TIMEOUT,
            'max_retries' => 3,
            'retry_delay' => 1000
        ];
    }

    public static function validateConfig(): array {
        $errors = [];

        if (empty(self::GEMINI_API_KEY) || self::GEMINI_API_KEY === 'YOUR_API_KEY_HERE') {
            $errors[] = 'Gemini API key is not configured';
        }

        if (empty(self::DB_HOST) || empty(self::DB_NAME)) {
            $errors[] = 'Database configuration is incomplete';
        }

        $weightSum = array_sum(self::HYBRID_WEIGHTS);
        if (abs($weightSum - 1.0) > 0.01) {
            $errors[] = 'Hybrid weights must sum to 1.0';
        }

        if (!file_exists(self::LOG_FILE) && !is_writable(dirname(self::LOG_FILE))) {
            $errors[] = 'Log directory is not writable: ' . dirname(self::LOG_FILE);
        }

        return $errors;
    }
}

$configErrors = Config::validateConfig();
if (!empty($configErrors)) {
    error_log("Configuration errors: " . implode(', ', $configErrors));
}