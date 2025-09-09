<?php

// Logger class for detailed logging
class Logger {
    private static $logFile = 'rag_chatbot.log';
    
    public static function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message $contextStr" . PHP_EOL;
        
        error_log($logEntry, 3, self::$logFile);
        
        // Also log to PHP error log for important messages
        if (in_array($level, ['ERROR', 'CRITICAL'])) {
            error_log("RAG Chatbot [$level]: $message");
        }
    }
    
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    public static function debug($message, $context = []) {
        self::log('DEBUG', $message, $context);
    }
}

?>