<?php

class UserService {
    public static function getCurrentUserId() {
        // Try JWT token first (if implemented)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
            $userId = self::validateJWT($token);
            if ($userId) {
                return $userId;
            }
        }
        
        // Fall back to session
        if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            return intval($_SESSION['user_id']);
        }
        
        // Default user for demo purposes
        return 1;
    }
    
    private static function validateJWT($token) {
        // Implement JWT validation here
        // For now, return null (not implemented)
        return null;
    }
}

?>