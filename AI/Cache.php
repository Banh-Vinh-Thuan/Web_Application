<?php

// Cache service with conversation-aware invalidation
class CacheService {
    private static $cache = [];
    private static $ttl = [];
    private static $conversationHash = null;

    /**
     * Get cached value with conversation validation
     */
    public static function get($key) {
        // Invalidate if conversation context changed
        if (self::hasConversationChanged()) {
            self::clearConversationCache();
            return null;
        }

        if (!isset(self::$cache[$key])) {
            return null;
        }

        // Check TTL expiration
        if (isset(self::$ttl[$key]) && time() > self::$ttl[$key]) {
            unset(self::$cache[$key]);
            unset(self::$ttl[$key]);
            return null;
        }

        return self::$cache[$key];
    }

    /**
     * Set cache with TTL
     */
    public static function set($key, $value, $ttlSeconds = 300) {
        self::$cache[$key] = $value;
        self::$ttl[$key] = time() + $ttlSeconds;
    }

    /**
     * Clear specific key or all cache
     */
    public static function clear($key = null) {
        if ($key === null) {
            self::$cache = [];
            self::$ttl = [];
        } else {
            unset(self::$cache[$key]);
            unset(self::$ttl[$key]);
        }
    }

    /**
     * Track conversation context to invalidate response cache
     */
    public static function setConversationContext($userMessage) {
        $newHash = md5(trim(strtolower($userMessage)));
        
        // If message changed, invalidate response-related cache
        if (self::$conversationHash !== null && self::$conversationHash !== $newHash) {
            self::clearConversationCache();
        }
        
        self::$conversationHash = $newHash;
    }

    /**
     * Check if conversation context changed
     */
    private static function hasConversationChanged() {
        return self::$conversationHash !== null;
    }

    /**
     * Clear conversation-specific cache (not city lookups)
     */
    private static function clearConversationCache() {
        foreach (array_keys(self::$cache) as $key) {
            // Keep city lookups cached, clear everything else
            if (strpos($key, 'city_search_') !== 0) {
                unset(self::$cache[$key]);
                unset(self::$ttl[$key]);
            }
        }
    }
}

?>