<?php
require_once './Logger.php';
require_once './CacheService.php';
require_once './config.php';

/**
 * Enhanced Database Service with Vector Search and Optimized Queries
 * Supports hybrid retrieval operations for RAG system
 */
class DatabaseService {
    private $db;

    public function __construct($db) {
        if (!$db) {
            throw new Exception("Invalid database connection");
        }
        $this->db = $db;
    }

    /**
     * Main search method for items (tours/hotels) with flexible filtering
     */
    public function searchItems($itemType, $filters = []) {
        if (!in_array($itemType, ['tour', 'hotel'])) {
            throw new InvalidArgumentException("Invalid item type: $itemType");
        }

        $tableAlias = $itemType === 'tour' ? 't' : 'h';
        $tableName = $itemType === 'tour' ? 'tours' : 'hotels';
        $idField = $itemType === 'tour' ? 'tourid' : 'hotelid';

        $sql = "SELECT {$tableAlias}.*, c.city as city_name
                FROM {$tableName} {$tableAlias}
                LEFT JOIN cities c ON {$tableAlias}.cityid = c.cityid
                WHERE 1=1";

        $params = [];
        $types = "";

        // Handle multiple city IDs
        if (!empty($filters['cityIds']) && is_array($filters['cityIds'])) {
            $cityIds = array_filter($filters['cityIds'], 'is_numeric');
            if (!empty($cityIds)) {
                $placeholders = implode(',', array_fill(0, count($cityIds), '?'));
                $sql .= " AND {$tableAlias}.cityid IN ({$placeholders})";
                foreach ($cityIds as $id) {
                    $params[] = (int)$id;
                    $types .= 'i';
                }
            }
        }

        // Budget filter with enhanced logic
        if (!empty($filters['budget']) && is_numeric($filters['budget'])) {
            $condition = $filters['price_condition'] ?? 'under';
            $operator = ($condition === 'over') ? '>=' : '<=';
            $priceField = $itemType === 'tour' ? 'price_per_person' : 'cost';
            $sql .= " AND {$tableAlias}.{$priceField} {$operator} ?";
            $params[] = (float)$filters['budget'];
            $types .= 'd';
        }

        // Duration filter (tours only)
        if ($itemType === 'tour' && !empty($filters['duration']) && is_numeric($filters['duration'])) {
            $sql .= " AND {$tableAlias}.duration_days = ?";
            $params[] = (int)$filters['duration'];
            $types .= 'i';
        }

        // Rating filter (hotels only)
        if ($itemType === 'hotel' && !empty($filters['rating']) && is_numeric($filters['rating'])) {
            $sql .= " AND {$tableAlias}.ratings >= ?";
            $params[] = (float)$filters['rating'];
            $types .= 'd';
        }

        // Name/keyword search
        if (!empty($filters['search_term'])) {
            $nameField = $itemType === 'tour' ? 'tour_name' : 'hotel';
            $sql .= " AND ({$tableAlias}.{$nameField} LIKE ? OR c.city LIKE ?)";
            $searchTerm = '%' . $filters['search_term'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }

        // Ordering
        $orderBy = $this->sanitizeOrderBy($filters['orderBy'] ?? null, $itemType, $tableAlias, $idField);
        $sql .= " ORDER BY " . $orderBy;

        // Limit
        $limit = isset($filters['limit']) && is_numeric($filters['limit'])
            ? max(1, min(100, (int)$filters['limit']))
            : 20;
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= 'i';

        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            Logger::debug("Items retrieved successfully", [
                'type' => $itemType,
                'count' => count($result),
                'filters' => $filters
            ]);

            return $result;

        } catch (Exception $e) {
            Logger::error("Failed to search items", [
                'error' => $e->getMessage(),
                'type' => $itemType,
                'sql' => $sql
            ]);
            return [];
        }
    }

    /**
     * Enhanced vector similarity search with proper error handling
     */
    public function findSimilarItemsByVector($queryVector, $limit = 10, $filters = []) {
        if (!is_array($queryVector) || empty($queryVector)) {
            Logger::warning("Invalid query vector provided");
            return [];
        }

        try {
            // Get all vectors from database with optimized query
            $sql = "SELECT id, item_id, item_type, vector_embedding 
                    FROM item_vectors 
                    WHERE vector_embedding IS NOT NULL 
                    LIMIT 2000"; // Increased limit for better coverage

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $allVectors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if (empty($allVectors)) {
                Logger::warning("No vectors found in database");
                return [];
            }

            // Calculate similarities in PHP with improved algorithm
            $similarities = [];
            $validVectorCount = 0;

            foreach ($allVectors as $row) {
                $itemVector = json_decode($row['vector_embedding'], true);
                
                if (is_array($itemVector) && count($itemVector) === count($queryVector)) {
                    $similarity = $this->cosineSimilarity($queryVector, $itemVector);
                    
                    if ($similarity > Config::MIN_SIMILARITY_SCORE) {
                        $similarities[] = [
                            'item_id' => $row['item_id'],
                            'item_type' => $row['item_type'],
                            'score' => $similarity
                        ];
                        $validVectorCount++;
                    }
                }
            }

            // Sort by similarity score (descending)
            usort($similarities, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            $results = array_slice($similarities, 0, $limit);

            Logger::debug("Vector similarity search completed", [
                'total_vectors' => count($allVectors),
                'valid_vectors' => $validVectorCount,
                'results_returned' => count($results)
            ]);

            return $results;

        } catch (Exception $e) {
            Logger::error("Vector similarity search failed", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Improved cosine similarity calculation
     */
    private function cosineSimilarity(array $vec1, array $vec2) {
        $dotProduct = 0.0;
        $mag1 = 0.0;
        $mag2 = 0.0;
        $count = count($vec1);

        if ($count !== count($vec2) || $count === 0) {
            return 0.0;
        }

        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $mag1 += $vec1[$i] * $vec1[$i];
            $mag2 += $vec2[$i] * $vec2[$i];
        }

        $magnitude = sqrt($mag1) * sqrt($mag2);
        return $magnitude === 0.0 ? 0.0 : $dotProduct / $magnitude;
    }

    /**
     * Find city by name with enhanced caching and fuzzy matching
     */
    public function findCityByName($cityName) {
        if (empty($cityName)) {
            return null;
        }

        try {
            $cacheKey = "city_search_" . strtolower(trim($cityName));
            $cached = CacheService::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            // Try exact match first
            $stmt = $this->db->prepare("SELECT * FROM cities WHERE LOWER(city) = LOWER(?) LIMIT 1");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            $stmt->bind_param('s', $cityName);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // If no exact match, try partial match with better algorithm
            if (!$result) {
                $searchTerm = '%' . $cityName . '%';
                $stmt = $this->db->prepare("
                    SELECT *, 
                    CASE 
                        WHEN LOWER(city) LIKE LOWER(?) THEN 1
                        WHEN LOWER(city) LIKE LOWER(CONCAT(?, '%')) THEN 2
                        WHEN LOWER(city) LIKE LOWER(CONCAT('%', ?, '%')) THEN 3
                        ELSE 4
                    END as match_priority
                    FROM cities 
                    WHERE LOWER(city) LIKE LOWER(?) 
                    ORDER BY match_priority ASC, LENGTH(city) ASC 
                    LIMIT 1
                ");
                
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $this->db->error);
                }

                $stmt->bind_param('ssss', $cityName, $cityName, $cityName, $searchTerm);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }

            CacheService::set($cacheKey, $result, Config::CACHE_CITIES_TTL);

            Logger::debug("City search completed", [
                'query' => $cityName,
                'found' => $result ? $result['city'] : null
            ]);

            return $result;

        } catch (Exception $e) {
            Logger::error("City search failed", [
                'error' => $e->getMessage(),
                'city' => $cityName
            ]);
            return null;
        }
    }

    /**
     * Enhanced tours retrieval by IDs with filtering
     */
    public function getToursByIds(array $tourIds, array $filters = []) {
        if (empty($tourIds)) return [];

        $tourIds = array_filter($tourIds, 'is_numeric');
        if (empty($tourIds)) return [];

        $placeholders = implode(',', array_fill(0, count($tourIds), '?'));
        $types = str_repeat('i', count($tourIds));

        $sql = "SELECT t.*, c.city as city_name
                FROM tours t
                LEFT JOIN cities c ON t.cityid = c.cityid
                WHERE t.tourid IN ($placeholders)";

        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            $stmt->bind_param($types, ...$tourIds);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $this->applyPostFilters($result, $filters, 'tour');

        } catch (Exception $e) {
            Logger::error("Failed to get tours by IDs", [
                'error' => $e->getMessage(),
                'tour_ids' => $tourIds
            ]);
            return [];
        }
    }

    /**
     * Enhanced hotels retrieval by IDs with filtering
     */
    public function getHotelsByIds(array $hotelIds, array $filters = []) {
        if (empty($hotelIds)) return [];

        $hotelIds = array_filter($hotelIds, 'is_numeric');
        if (empty($hotelIds)) return [];

        $placeholders = implode(',', array_fill(0, count($hotelIds), '?'));
        $types = str_repeat('i', count($hotelIds));

        $sql = "SELECT h.*, c.city as city_name
                FROM hotels h
                LEFT JOIN cities c ON h.cityid = c.cityid
                WHERE h.hotelid IN ($placeholders)";

        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            $stmt->bind_param($types, ...$hotelIds);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $this->applyPostFilters($result, $filters, 'hotel');

        } catch (Exception $e) {
            Logger::error("Failed to get hotels by IDs", [
                'error' => $e->getMessage(),
                'hotel_ids' => $hotelIds
            ]);
            return [];
        }
    }

    /**
     * Apply post-retrieval filters in PHP for fine-grained control
     */
    private function applyPostFilters($items, $filters, $itemType) {
        if (empty($filters) || empty($items)) return $items;

        return array_filter($items, function($item) use ($filters, $itemType) {
            // Budget filter
            if (!empty($filters['budget'])) {
                $priceField = $itemType === 'tour' ? 'price_per_person' : 'cost';
                $condition = $filters['price_condition'] ?? 'under';
                
                if ($condition === 'under' && $item[$priceField] > $filters['budget']) {
                    return false;
                }
                if ($condition === 'over' && $item[$priceField] < $filters['budget']) {
                    return false;
                }
            }

            // Duration filter (tours only)
            if ($itemType === 'tour' && !empty($filters['duration'])) {
                if ($item['duration_days'] != $filters['duration']) {
                    return false;
                }
            }

            // Rating filter (hotels only)
            if ($itemType === 'hotel' && !empty($filters['rating'])) {
                if ($item['ratings'] < $filters['rating']) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Enhanced conversation saving with better error handling
     */
    public function saveConversation($userId, $userMessage, $botResponse) {
        try {
            $title = $this->generateConversationTitle($userMessage);
            
            $stmt = $this->db->prepare("
                INSERT INTO chat_history (user_id, title, user_message, bot_response, created_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                bot_response = VALUES(bot_response),
                created_at = NOW()
            ");

            $responseText = is_array($botResponse) ? ($botResponse['text'] ?? '') : $botResponse;
            $stmt->bind_param("isss", $userId, $title, $userMessage, $responseText);

            $result = $stmt->execute();
            $stmt->close();

            if ($result) {
                Logger::info("Conversation saved successfully", [
                    'userId' => $userId,
                    'title' => $title
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Logger::error("Failed to save conversation", [
                'error' => $e->getMessage(),
                'userId' => $userId
            ]);
            return false;
        }
    }

    /**
     * Enhanced conversation title generation
     */
    private function generateConversationTitle($message) {
        // Clean the message
        $cleanMessage = trim($message);
        $cleanMessage = preg_replace('/[?!.,]/', '', $cleanMessage);

        // Remove common filler words with more comprehensive list
        $fillerWords = [
            'can you', 'could you', 'please', 'help me', 'i want to', 'i need to',
            'show me', 'find me', 'tell me about', 'what is', 'what are', 'how to', 
            'where is', 'give me', 'let me know', 'i would like', 'looking for'
        ];

        $lowerMessage = strtolower($cleanMessage);
        foreach ($fillerWords as $filler) {
            $lowerMessage = str_replace($filler, '', $lowerMessage);
        }

        // Split into words and take first 4-5 meaningful words
        $words = array_filter(explode(' ', trim($lowerMessage)));
        $selectedWords = array_slice($words, 0, 5);

        if (empty($selectedWords)) {
            // Fallback to original message words
            $originalWords = array_filter(explode(' ', $cleanMessage));
            $selectedWords = array_slice($originalWords, 0, 4);
        }

        if (empty($selectedWords)) {
            return 'New Chat';
        }

        // Capitalize first letter of each word
        $title = implode(' ', array_map('ucfirst', $selectedWords));

        // Add ellipsis if original message was longer
        if (count(explode(' ', $cleanMessage)) > count($selectedWords)) {
            $title .= '...';
        }

        return $title;
    }

    /**
     * Get chat history with improved query
     */
    public function getChatHistory($userId, $limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, title, user_message, bot_response, created_at
                FROM chat_history
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $history = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $history;

        } catch (Exception $e) {
            Logger::error("Failed to get chat history", [
                'error' => $e->getMessage(),
                'userId' => $userId
            ]);
            return [];
        }
    }

    /**
     * Get enhanced system statistics
     */
    public function getSystemStats() {
        $cacheKey = 'system_stats_enhanced';
        $cached = CacheService::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $stats = [];

            // Get table counts
            $tables = ['tours', 'hotels', 'cities', 'chat_history', 'item_vectors'];
            foreach ($tables as $table) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM $table");
                if ($stmt && $stmt->execute()) {
                    $result = $stmt->get_result()->fetch_assoc();
                    $stats["total_$table"] = $result['count'];
                    $stmt->close();
                }
            }

            // Get additional statistics
            $stats['avg_tour_price'] = $this->getAverageValue('tours', 'price_per_person');
            $stats['avg_hotel_rating'] = $this->getAverageValue('hotels', 'ratings');
            $stats['cities_with_tours'] = $this->getDistinctCount('tours', 'cityid');
            $stats['cities_with_hotels'] = $this->getDistinctCount('hotels', 'cityid');

            CacheService::set($cacheKey, $stats, Config::CACHE_STATS_TTL);
            return $stats;

        } catch (Exception $e) {
            Logger::error("Failed to get system stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Helper method to get average values
     */
    private function getAverageValue($table, $column) {
        try {
            $stmt = $this->db->prepare("SELECT AVG($column) as avg_value FROM $table WHERE $column > 0");
            if ($stmt && $stmt->execute()) {
                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                return round($result['avg_value'], 2);
            }
        } catch (Exception $e) {
            Logger::warning("Failed to get average value", [
                'table' => $table,
                'column' => $column,
                'error' => $e->getMessage()
            ]);
        }
        return 0;
    }

    /**
     * Helper method to get distinct counts
     */
    private function getDistinctCount($table, $column) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(DISTINCT $column) as count FROM $table");
            if ($stmt && $stmt->execute()) {
                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                return $result['count'];
            }
        } catch (Exception $e) {
            Logger::warning("Failed to get distinct count", [
                'table' => $table,
                'column' => $column,
                'error' => $e->getMessage()
            ]);
        }
        return 0;
    }

    /**
     * Sanitize ORDER BY clause to prevent SQL injection
     */
    private function sanitizeOrderBy($orderBy, $itemType, $tableAlias, $idField) {
        if (empty($orderBy)) {
            return "$tableAlias.$idField ASC";
        }

        // Define allowed columns for each item type
        $allowedColumns = [
            'tour' => ['tourid', 'tour_name', 'duration_days', 'price_per_person', 'created_at'],
            'hotel' => ['hotelid', 'hotel', 'ratings', 'cost', 'created_at']
        ];

        $columns = $allowedColumns[$itemType] ?? [];

        // Parse and validate ORDER BY
        if (preg_match('/^(\w+)\s+(ASC|DESC)$/i', trim($orderBy), $matches)) {
            $column = $matches[1];
            $direction = strtoupper($matches[2]);

            if (in_array($column, $columns)) {
                return "$tableAlias.$column $direction";
            }
        }

        // Fallback to safe default
        return "$tableAlias.$idField ASC";
    }
}
?>