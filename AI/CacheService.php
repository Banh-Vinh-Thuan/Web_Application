<?php

// Cache service for performance optimization
class CacheService {
    private static $cache = [];
    private static $ttl = [];
    
    public static function get($key) {
        if (!isset(self::$cache[$key])) {
            return null;
        }
        
        // Check TTL
        if (isset(self::$ttl[$key]) && time() > self::$ttl[$key]) {
            unset(self::$cache[$key]);
            unset(self::$ttl[$key]);
            return null;
        }
        
        return self::$cache[$key];
    }
    
    public static function set($key, $value, $ttlSeconds = 300) {
        self::$cache[$key] = $value;
        self::$ttl[$key] = time() + $ttlSeconds;
    }
    
    public static function clear($key = null) {
        if ($key === null) {
            self::$cache = [];
            self::$ttl = [];
        } else {
            unset(self::$cache[$key]);
            unset(self::$ttl[$key]);
        }
    }
}

?>