<?php

class Config {
    // Database configuration
    const DB_HOST = "localhost";
    const DB_USERNAME = "root";
    const DB_PASSWORD = "4444";
    const DB_NAME = "travelscapes";
    
    // API configuration
    const GEMINI_API_KEY = "AIzaSyBKlus-HPPK2H14xstpE1VHsfkzbUkoRJA";
    const GEMINI_API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";
    
    // Cache configuration
    const CACHE_DEFAULT_TTL = 300; // 5 minutes
    const CACHE_CITIES_TTL = 600; // 10 minutes
    const CACHE_STATS_TTL = 300; // 5 minutes
    
    // Logging configuration
    const LOG_FILE = 'rag_chatbot.log';
    const LOG_LEVELS = ['DEBUG', 'INFO', 'ERROR', 'CRITICAL'];
    
    // Application configuration
    const MAX_MESSAGE_LENGTH = 1000;
    const MAX_CHAT_HISTORY = 50;
    const DEFAULT_USER_ID = 1;
    
    // API timeouts
    const API_TIMEOUT = 30;
    const DB_TIMEOUT = 10;
    
    /**
     * Get database connection
     */
    public static function getDatabaseConnection() {
        $conn = mysqli_connect(
            self::DB_HOST, 
            self::DB_USERNAME, 
            self::DB_PASSWORD, 
            self::DB_NAME
        );
        
        if (!$conn) {
            throw new Exception('Database connection failed: ' . mysqli_connect_error());
        }
        
        return $conn;
    }

     // Get Vietnamese cities mapping
    public static function getVietnameseCities() {
        return [
            'ho chi minh' => ['id' => 11, 'name' => 'Ho Chi Minh City'], 'saigon' => ['id' => 11, 'name' => 'Ho Chi Minh City'],
            'hcmc' => ['id' => 11, 'name' => 'Ho Chi Minh City'],
            'nha trang' => ['id' => 12, 'name' => 'Nha Trang'], 'nhatrang' => ['id' => 12, 'name' => 'Nha Trang'],
            'hue' => ['id' => 13, 'name' => 'Hue'], 
            'phu yen' => ['id' => 14, 'name' => 'Phu Yen'],'phuyen' => ['id' => 14, 'name' => 'Phu Yen'], 
            'da lat' => ['id' => 15, 'name' => 'Da Lat'],'dalat' => ['id' => 15, 'name' => 'Da Lat'],
            'phu quoc' => ['id' => 16, 'name' => 'Phu Quoc'], 'phuquoc' => ['id' => 16, 'name' => 'Phu Quoc'],
            'hoi an' => ['id' => 17, 'name' => 'Hoi An'],'hoian' => ['id' => 17, 'name' => 'Hoi An'],
            'ha giang' => ['id' => 18, 'name' => 'Ha Giang'],'hagiang' => ['id' => 18, 'name' => 'Ha Giang'],
            'tay bac' => ['id' => 10, 'name' => 'Tay Bac'],'taybac' => ['id' => 10, 'name' => 'Tay Bac'],
            'northwest' => ['id' => 10, 'name' => 'Tay Bac'], 
            'danang' => ['id' => 19, 'name' => 'Da Nang'],'da nang' => ['id' => 19, 'name' => 'Da Nang'],
            'cantho' => ['id' => 20, 'name' => 'Can Tho'],'can tho' => ['id' => 20, 'name' => 'Can Tho'],
            'hanoi' => ['id' => 21, 'name' => 'Hanoi'],'ha noi' => ['id' => 21, 'name' => 'Hanoi'],
        ];
    }
}

?>