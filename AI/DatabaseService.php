<?php
require_once './Logger.php';
require_once './CacheService.php';
require_once './config.php';

/**
 * Enhanced Database Service with Vector Search
 */
class DatabaseService {
    private $db;
    
    private const DEFAULT_LIMIT = 20;
    private const MAX_LIMIT = 100;
    private const VECTOR_SEARCH_LIMIT = 2000;

    public function __construct($db) {
        if (!$db) {
            throw new Exception("Invalid database connection");
        }
        $this->db = $db;
    }

    /**
     * Main search method for items
     */
    public function searchItems($itemType, $filters = []) {
        if (!in_array($itemType, ['tour', 'hotel'])) {
            throw new InvalidArgumentException("Invalid item type: $itemType");
        }

        $query = $this->buildSearchQuery($itemType, $filters);
        
        try {
            $stmt = $this->db->prepare($query['sql']);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            if (!empty($query['params'])) {
                $stmt->bind_param($query['types'], ...$query['params']);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            Logger::debug("Items retrieved", [
                'type' => $itemType,
                'count' => count($result),
                'filters' => $filters
            ]);

            return $result;

        } catch (Exception $e) {
            Logger::error("Search failed", [
                'error' => $e->getMessage(),
                'type' => $itemType
            ]);
            return [];
        }
    }

    private function buildSearchQuery($itemType, $filters) {
        $tableAlias = $itemType === 'tour' ? 't' : 'h';
        $tableName = $itemType === 'tour' ? 'tours' : 'hotels';

        $sql = "SELECT {$tableAlias}.*, c.city as city_name
                FROM {$tableName} {$tableAlias}
                LEFT JOIN cities c ON {$tableAlias}.cityid = c.cityid
                WHERE 1=1";

        $params = [];
        $types = "";

        // City filter
        if (!empty($filters['cityIds'])) {
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

        // Budget filter
        if (!empty($filters['budget']) && is_numeric($filters['budget'])) {
            $priceField = $itemType === 'tour' ? 'price_per_person' : 'cost';
            $operator = ($filters['price_condition'] ?? 'under') === 'over' ? '>=' : '<=';
            $sql .= " AND {$tableAlias}.{$priceField} {$operator} ?";
            $params[] = (float)$filters['budget'];
            $types .= 'd';
        }

        // Duration filter (tours only)
        if ($itemType === 'tour' && !empty($filters['duration'])) {
            $sql .= " AND {$tableAlias}.duration_days = ?";
            $params[] = (int)$filters['duration'];
            $types .= 'i';
        }

        // Rating filter (hotels only)
        if ($itemType === 'hotel' && !empty($filters['rating'])) {
            $condition = $filters['rating_condition'] ?? 'minimum';
            switch ($condition) {
                case 'exact':
                    $sql .= " AND {$tableAlias}.ratings >= ? AND {$tableAlias}.ratings < ?";
                    $params[] = floatval($filters['rating']);
                    $params[] = floatval($filters['rating']) + 1.0;
                    $types .= 'dd';
                    break;
                case 'maximum':
                    $sql .= " AND {$tableAlias}.ratings < ?";
                    $params[] = floatval($filters['rating']) + 1.0;
                    $types .= 'd';
                    break;
                default: // minimum
                    $sql .= " AND {$tableAlias}.ratings >= ?";
                    $params[] = floatval($filters['rating']);
                    $types .= 'd';
            }
        }

        // Search term
        if (!empty($filters['search_term'])) {
            $nameField = $itemType === 'tour' ? 'tour_name' : 'hotel';
            $sql .= " AND ({$tableAlias}.{$nameField} LIKE ? OR c.city LIKE ?)";
            $searchTerm = '%' . $filters['search_term'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }

        // Ordering
        $sql .= " ORDER BY " . $this->getOrderByClause($itemType, $tableAlias, $filters);

        // Limit
        $limit = $this->sanitizeLimit($filters['limit'] ?? self::DEFAULT_LIMIT);
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= 'i';

        return ['sql' => $sql, 'params' => $params, 'types' => $types];
    }

    private function getOrderByClause($itemType, $alias, $filters) {
        if ($itemType === 'tour') {
            return "{$alias}.price_per_person ASC, {$alias}.duration_days DESC";
        }
        
        // Hotels: sort by rating desc if exact rating filter, otherwise by price
        if (!empty($filters['rating']) && ($filters['rating_condition'] ?? 'exact') === 'exact') {
            return "{$alias}.cost ASC, {$alias}.ratings DESC";
        }
        return "{$alias}.ratings DESC, {$alias}.cost ASC";
    }

    private function sanitizeLimit($limit) {
        return max(1, min(self::MAX_LIMIT, (int)$limit));
    }

    /**
     * Vector similarity search
     */
    public function findSimilarItemsByVector($queryVector, $limit = 10) {
        if (!is_array($queryVector) || empty($queryVector)) {
            Logger::warning("Invalid query vector");
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT id, item_id, item_type, vector_embedding 
                 FROM item_vectors 
                 WHERE vector_embedding IS NOT NULL 
                 LIMIT ?"
            );
            
            $vectorLimit = self::VECTOR_SEARCH_LIMIT;
            $stmt->bind_param('i', $vectorLimit);
            $stmt->execute();
            
            $allVectors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if (empty($allVectors)) {
                return [];
            }

            $similarities = $this->calculateSimilarities($queryVector, $allVectors);
            usort($similarities, fn($a, $b) => $b['score'] <=> $a['score']);

            return array_slice($similarities, 0, $limit);

        } catch (Exception $e) {
            Logger::error("Vector search failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function calculateSimilarities($queryVector, $allVectors) {
        $similarities = [];
        
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
                }
            }
        }
        
        return $similarities;
    }

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
     * Find city by name
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

            // Exact match
            $stmt = $this->db->prepare("SELECT * FROM cities WHERE LOWER(city) = LOWER(?) LIMIT 1");
            $stmt->bind_param('s', $cityName);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Partial match if no exact match
            if (!$result) {
                $result = $this->findCityPartialMatch($cityName);
            }

            CacheService::set($cacheKey, $result, Config::CACHE_CITIES_TTL);
            return $result;

        } catch (Exception $e) {
            Logger::error("City search failed", ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function findCityPartialMatch($cityName) {
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
        
        $stmt->bind_param('ssss', $cityName, $cityName, $cityName, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get tours by IDs
     */
    public function getToursByIds(array $tourIds, array $filters = []) {
        return $this->getItemsByIds('tour', $tourIds, 'tourid', $filters);
    }

    /**
     * Get hotels by IDs
     */
    public function getHotelsByIds(array $hotelIds, array $filters = []) {
        return $this->getItemsByIds('hotel', $hotelIds, 'hotelid', $filters);
    }

    private function getItemsByIds($itemType, $ids, $idField, $filters) {
        if (empty($ids)) return [];

        $ids = array_filter($ids, 'is_numeric');
        if (empty($ids)) return [];

        $tableName = $itemType === 'tour' ? 'tours' : 'hotels';
        $alias = $itemType === 'tour' ? 't' : 'h';
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT {$alias}.*, c.city as city_name
                FROM {$tableName} {$alias}
                LEFT JOIN cities c ON {$alias}.cityid = c.cityid
                WHERE {$alias}.{$idField} IN ({$placeholders})";

        try {
            $stmt = $this->db->prepare($sql);
            $types = str_repeat('i', count($ids));
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
            
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $this->applyPostFilters($result, $filters, $itemType);

        } catch (Exception $e) {
            Logger::error("Get items by IDs failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function applyPostFilters($items, $filters, $itemType) {
        if (empty($filters) || empty($items)) return $items;

        return array_filter($items, function($item) use ($filters, $itemType) {
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

            if ($itemType === 'tour' && !empty($filters['duration'])) {
                if ($item['duration_days'] != $filters['duration']) {
                    return false;
                }
            }

            if ($itemType === 'hotel' && !empty($filters['rating'])) {
                if ($item['ratings'] < $filters['rating']) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Save conversation
     */
    public function saveConversation($userId, $userMessage, $botResponse) {
        try {
            $title = $this->generateConversationTitle($userMessage);
            
            $stmt = $this->db->prepare("
                INSERT INTO chat_history (user_id, title, user_message, bot_response, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            $responseText = is_array($botResponse) ? ($botResponse['text'] ?? '') : $botResponse;
            $stmt->bind_param("isss", $userId, $title, $userMessage, $responseText);

            $result = $stmt->execute();
            $stmt->close();

            return $result;

        } catch (Exception $e) {
            Logger::error("Save conversation failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function generateConversationTitle($message) {
        $cleanMessage = preg_replace('/[?!.,]/', '', trim($message));
        
        $fillerWords = ['can you', 'could you', 'please', 'help me', 'show me', 'find me'];
        $lowerMessage = strtolower($cleanMessage);
        
        foreach ($fillerWords as $filler) {
            $lowerMessage = str_replace($filler, '', $lowerMessage);
        }

        $words = array_filter(explode(' ', trim($lowerMessage)));
        $selectedWords = array_slice($words, 0, 5);

        if (empty($selectedWords)) {
            return 'New Chat';
        }

        $title = implode(' ', array_map('ucfirst', $selectedWords));
        
        if (count($words) > count($selectedWords)) {
            $title .= '...';
        }

        return substr($title, 0, 50);
    }

    /**
     * Get chat history
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
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $result;

        } catch (Exception $e) {
            Logger::error("Get chat history failed", ['error' => $e->getMessage()]);
            return [];
        }
    }
}
?>