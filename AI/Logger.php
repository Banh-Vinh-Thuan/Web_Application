<?php

class Logger {
    private static $logFile = 'rag_chatbot.log';

    public static function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logEntry = "[$timestamp] [$level] $message $contextStr" . PHP_EOL;

        // Log to the specified file
        error_log($logEntry, 3, self::$logFile);

        // Also log critical errors to the default PHP error log for high visibility
        if (in_array($level, ['ERROR', 'CRITICAL'])) {
            error_log("RAG Chatbot [$level]: $message");
        }
    }

    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }

    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }

    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    public static function debug($message, $context = []) {
        if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
            self::log('DEBUG', $message, $context);
        }
    }
}
?>