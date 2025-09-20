<?php
require_once './Logger.php';
require_once './CacheService.php';

class DatabaseService {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function searchItems($itemType, $filters = []) {
        $tableAlias = $itemType === 'tour' ? 't' : 'h';
        $tableName = $itemType === 'tour' ? 'tours' : 'hotels';
        $idField = $itemType === 'tour' ? 'tourid' : 'hotelid';

        $sql = "SELECT {$tableAlias}.*, c.city as city_name 
                FROM {$tableName} {$tableAlias} 
                LEFT JOIN cities c ON {$tableAlias}.cityid = c.cityid 
                WHERE 1=1";
        
        $params = [];
        $types = "";

        // Common filters
        if (!empty($filters['cityId'])) {
            $sql .= " AND {$tableAlias}.cityid = ?";
            $params[] = $filters['cityId'];
            $types .= 'i';
        }
        if (!empty($filters['budget'])) {
            $condition = $filters['price_condition'] ?? 'under';
            $operator = ($condition === 'over') ? '>=' : '<=';
            $priceField = $itemType === 'tour' ? 'price_per_person' : 'cost';
            $sql .= " AND {$tableAlias}.{$priceField} {$operator} ?";
            $params[] = $filters['budget'];
            $types .= 'd'; // Use 'd' for decimal/float
        }

        // Item-specific filters
        if ($itemType === 'tour' && !empty($filters['duration'])) {
            $sql .= " AND {$tableAlias}.duration_days = ?";
            $params[] = $filters['duration'];
            $types .= 'i';
        }
        if ($itemType === 'hotel' && !empty($filters['rating'])) {
            $sql .= " AND {$tableAlias}.ratings >= ?";
            $params[] = $filters['rating'];
            $types .= 'd';
        }

        // Ordering
        $orderBy = $filters['orderBy'] ?? "{$tableAlias}.{$idField} ASC";
        $sql .= " ORDER BY " . $this->db->real_escape_string($orderBy);

        // Limit
        $limit = $filters['limit'] ?? 10;
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= 'i';
        
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed: " . $this->db->error);
            if (!empty($params)) $stmt->bind_param($types, ...$params);
            
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            Logger::debug(ucfirst($itemType) . "s retrieved", ['count' => count($result), 'filters' => $filters]);
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to get " . $itemType, ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function findCityByName($cityName) {
        try {
            $cacheKey = "city_search_" . strtolower($cityName);
            $cached = CacheService::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
            
            // Try exact match first
            $stmt = $this->db->prepare("SELECT * FROM cities WHERE LOWER(city) = LOWER(?) LIMIT 1");
            $stmt->bind_param('s', $cityName);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            // If no exact match, try partial match
            if (!$result) {
                $searchTerm = '%' . $cityName . '%';
                $stmt = $this->db->prepare("SELECT * FROM cities WHERE LOWER(city) LIKE LOWER(?) ORDER BY LENGTH(city) ASC LIMIT 1");
                $stmt->bind_param('s', $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
            
            CacheService::set($cacheKey, $result, 1800); // Cache for 30 minutes
            Logger::debug("City search", ['query' => $cityName, 'found' => $result ? $result['city'] : null]);
            
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to find city by name", ['error' => $e->getMessage(), 'city' => $cityName]);
            return null;
        }
    }
    
    public function findToursAndHotels($cityName, $filters = []) {
        $city = $this->findCityByName($cityName);
        if (!$city) {
            return [
                'success' => false,
                'message' => "Sorry, I couldn't find information for '$cityName'. Please check the city name.",
                'suggestions' => $this->getSimilarCityNames($cityName)
            ];
        }
        
        $cityId = $city['cityid'];
        $result = [
            'success' => true,
            'city' => $city,
            'tours' => $this->getTours($cityId, 
                $filters['duration'] ?? null, 
                $filters['budget'] ?? null, 
                $filters['limit'] ?? 6, 
                $filters['priceCondition'] ?? null
            ),
            'hotels' => $this->getHotels($cityId, 
                $filters['limit'] ?? 6, 
                $filters['rating'] ?? null, 
                $filters['budget'] ?? null, 
                $filters['priceCondition'] ?? null
            )
        ];
        
        Logger::info("Combined search performed", [
            'city' => $cityName, 
            'tours_found' => count($result['tours']), 
            'hotels_found' => count($result['hotels'])
        ]);
        
        return $result;
    }
    
    // Get similar city names for suggestions when exact match fails
    public function getSimilarCityNames($cityName, $limit = 3) {
        try {
            $searchTerm = '%' . $cityName . '%';
            $stmt = $this->db->prepare("SELECT city FROM cities WHERE LOWER(city) LIKE LOWER(?) ORDER BY LENGTH(city) ASC LIMIT ?");
            $stmt->bind_param('si', $searchTerm, $limit);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return array_column($result, 'city');
        } catch (Exception $e) {
            Logger::error("Failed to get similar city names", ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Enhanced search with natural language processing
     * Handles various query patterns
     */
    public function naturalLanguageSearch($query) {
        $query = strtolower(trim($query));
        $result = [
            'query' => $query,
            'intent' => 'unknown',
            'entities' => [],
            'data' => []
        ];
        
        // Extract city names from query
        $cities = $this->extractCitiesFromQuery($query);
        
        // Determine search intent
        if (strpos($query, 'tour') !== false && strpos($query, 'hotel') !== false) {
            $result['intent'] = 'tours_and_hotels';
        } elseif (strpos($query, 'tour') !== false) {
            $result['intent'] = 'tours_only';
        } elseif (strpos($query, 'hotel') !== false) {
            $result['intent'] = 'hotels_only';
        } elseif (strpos($query, 'city') !== false || strpos($query, 'cities') !== false) {
            $result['intent'] = 'cities_info';
        }
        
        // Extract filters from query
        $filters = $this->extractFiltersFromQuery($query);
        $result['entities'] = $filters;
        
        // Execute search based on intent
        switch ($result['intent']) {
            case 'tours_and_hotels':
                if (!empty($cities)) {
                    $result['data'] = $this->findToursAndHotels($cities[0], $filters);
                }
                break;
                
            case 'tours_only':
                if (!empty($cities)) {
                    $city = $this->findCityByName($cities[0]);
                    if ($city) {
                        $result['data'] = [
                            'city' => $city,
                            'tours' => $this->getTours($city['cityid'], 
                                $filters['duration'] ?? null, 
                                $filters['budget'] ?? null, 
                                $filters['limit'] ?? 10, 
                                $filters['priceCondition'] ?? null
                            )
                        ];
                    }
                } else {
                    $result['data'] = $this->getToursGroupedByCity(
                        $filters['duration'] ?? null, 
                        $filters['budget'] ?? null, 
                        $filters['priceCondition'] ?? null
                    );
                }
                break;
                
            case 'hotels_only':
                if (!empty($cities)) {
                    $city = $this->findCityByName($cities[0]);
                    if ($city) {
                        $result['data'] = [
                            'city' => $city,
                            'hotels' => $this->getHotels($city['cityid'], 
                                $filters['limit'] ?? 10, 
                                $filters['rating'] ?? null, 
                                $filters['budget'] ?? null, 
                                $filters['priceCondition'] ?? null
                            )
                        ];
                    }
                } else {
                    $result['data'] = $this->getHotelsGroupedByCity(
                        $filters['rating'] ?? null, 
                        $filters['budget'] ?? null, 
                        $filters['priceCondition'] ?? null
                    );
                }
                break;
                
            case 'cities_info':
                $result['data'] = $this->getCities();
                break;
                
            default:
                // Try to find any travel-related content
                if (!empty($cities)) {
                    $result['data'] = $this->findToursAndHotels($cities[0], $filters);
                    $result['intent'] = 'general_search';
                }
        }
        
        Logger::info("Natural language search", [
            'query' => $query, 
            'intent' => $result['intent'], 
            'cities' => $cities, 
            'filters' => $filters
        ]);
        
        return $result;
    }
    
    /**
     * Extract city names from natural language query
     */
    private function extractCitiesFromQuery($query) {
        $cities = [];
        
        // Get all cities from database for matching
        $allCities = $this->getCities();
        
        foreach ($allCities as $city) {
            $cityName = strtolower($city['city']);
            if (strpos($query, $cityName) !== false) {
                $cities[] = $city['city'];
            }
        }
        
        // Handle common variations
        $cityMappings = [
            'hoi an' => 'Hoi An',
            'ho chi minh' => 'Ho Chi Minh City',
            'saigon' => 'Ho Chi Minh City',
            'hanoi' => 'Hanoi',
            'da nang' => 'Da Nang',
            'nha trang' => 'Nha Trang',
            'da lat' => 'Da Lat',
            'dalat' => 'Da Lat',
            'hue' => 'Hue',
            'can tho' => 'Can Tho',
            'phu quoc' => 'Phu Quoc'
        ];
        
        foreach ($cityMappings as $variation => $standard) {
            if (strpos($query, $variation) !== false && !in_array($standard, $cities)) {
                $cities[] = $standard;
            }
        }
        
        return array_unique($cities);
    }
    
    /**
     * Extract filters from natural language query
     */
    private function extractFiltersFromQuery($query) {
        $filters = [];
        
        // Extract budget information
        if (preg_match('/under (\d+)/', $query, $matches)) {
            $filters['budget'] = intval($matches[1]);
            $filters['priceCondition'] = 'under';
        } elseif (preg_match('/below (\d+)/', $query, $matches)) {
            $filters['budget'] = intval($matches[1]);
            $filters['priceCondition'] = 'under';
        } elseif (preg_match('/over (\d+)/', $query, $matches)) {
            $filters['budget'] = intval($matches[1]);
            $filters['priceCondition'] = 'over';
        } elseif (preg_match('/above (\d+)/', $query, $matches)) {
            $filters['budget'] = intval($matches[1]);
            $filters['priceCondition'] = 'over';
        } elseif (preg_match('/(\d+)\s*(?:usd|dollars?|đ|vnd)/', $query, $matches)) {
            $filters['budget'] = intval($matches[1]);
            $filters['priceCondition'] = 'under';
        }
        
        // Extract duration
        if (preg_match('/(\d+)\s*days?/', $query, $matches)) {
            $filters['duration'] = intval($matches[1]);
        }
        
        // Extract rating
        if (preg_match('/(\d+)\s*stars?/', $query, $matches)) {
            $filters['rating'] = intval($matches[1]);
        } elseif (strpos($query, 'high rated') !== false || strpos($query, 'best rated') !== false) {
            $filters['rating'] = 4;
        }
        
        // Extract limit
        if (preg_match('/(\d+)\s*(?:results?|items?|options?)/', $query, $matches)) {
            $filters['limit'] = intval($matches[1]);
        } elseif (strpos($query, 'few') !== false) {
            $filters['limit'] = 3;
        } elseif (strpos($query, 'many') !== false || strpos($query, 'all') !== false) {
            $filters['limit'] = 20;
        }
        
        // Extract price preferences
        if (strpos($query, 'cheap') !== false || strpos($query, 'budget') !== false || strpos($query, 'affordable') !== false) {
            $filters['priceCondition'] = 'under';
            if (!isset($filters['budget'])) {
                $filters['budget'] = 100; // Default budget threshold
            }
        } elseif (strpos($query, 'luxury') !== false || strpos($query, 'expensive') !== false || strpos($query, 'premium') !== false) {
            $filters['priceCondition'] = 'over';
            if (!isset($filters['budget'])) {
                $filters['budget'] = 200; // Default luxury threshold
            }
        }
        
        return $filters;
    }
    
    /**
     * Get popular destinations with statistics
     */
    public function getPopularDestinations($limit = 10) {
        try {
            $cacheKey = 'popular_destinations';
            $cached = CacheService::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    c.cityid,
                    c.city,
                    c.description,
                    COUNT(DISTINCT t.tourid) as tour_count,
                    COUNT(DISTINCT h.hotelid) as hotel_count,
                    AVG(t.price_per_person) as avg_tour_price,
                    AVG(h.cost) as avg_hotel_price,
                    AVG(h.ratings) as avg_hotel_rating
                FROM cities c
                LEFT JOIN tours t ON c.cityid = t.cityid
                LEFT JOIN hotels h ON c.cityid = h.cityid
                GROUP BY c.cityid, c.city, c.description
                HAVING tour_count > 0 OR hotel_count > 0
                ORDER BY (tour_count + hotel_count) DESC
                LIMIT ?
            ");
            
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            CacheService::set($cacheKey, $result, 900); // Cache for 15 minutes
            
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to get popular destinations", ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Search recommendations based on user preferences
     */
    public function getRecommendations($preferences = []) {
        try {
            $result = [
                'popular_destinations' => $this->getPopularDestinations(5),
                'budget_tours' => [],
                'luxury_hotels' => [],
                'short_trips' => [],
                'long_trips' => []
            ];
            
            // Budget-friendly tours
            $result['budget_tours'] = $this->getTours(null, null, 100, 5, 'under');
            
            // Luxury hotels
            $result['luxury_hotels'] = $this->getHotels(null, 5, 4, null, null);
            
            // Short trips (1-3 days)
            $shortTours = $this->getTours(null, 1, null, 3, null);
            $shortTours = array_merge($shortTours, $this->getTours(null, 2, null, 2, null));
            $result['short_trips'] = array_slice($shortTours, 0, 5);
            
            // Long trips (7+ days)
            $result['long_trips'] = $this->getTours(null, 7, null, 3, null);
            $result['long_trips'] = array_merge($result['long_trips'], $this->getTours(null, 10, null, 2, null));
            
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to get recommendations", ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    // Keep all existing methods from original file
    public function getTours($cityId = null, $duration = null, $budget = null, $limit = 6, $priceCondition = null) {
        $orderDirection = ($priceCondition === 'over') ? 'DESC' : 'ASC';
        $filters = [
            'cityId' => $cityId,
            'duration' => $duration,
            'budget' => $budget,
            'limit' => $limit,
            'price_condition' => $priceCondition,
            'orderBy' => "price_per_person $orderDirection"
        ];
        // array_filter loại bỏ các giá trị null, đảm bảo chỉ các bộ lọc có giá trị được truyền đi
        return $this->searchItems('tour', array_filter($filters, fn($value) => $value !== null));
    }
    
    public function getHotels($cityId = null, $limit = 6, $rating = null, $budget = null, $priceCondition = null) {
        $filters = [
            'cityId' => $cityId,
            'rating' => $rating,
            'budget' => $budget,
            'limit' => $limit,
            'price_condition' => $priceCondition,
            'orderBy' => 'ratings DESC, cost ASC'
        ];
        return $this->searchItems('hotel', array_filter($filters, fn($value) => $value !== null));
    }
    
    public function getCities($cityId = null) {
        try {
            $cacheKey = "cities_" . ($cityId ?? 'all');
            $cached = CacheService::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
            
            $sql = "SELECT * FROM cities";
            $params = [];
            $types = "";
            
            if ($cityId !== null) {
                $sql .= " WHERE cityid = ?";
                $params[] = $cityId;
                $types .= 'i';
            }
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            CacheService::set($cacheKey, $result, 600); // Cache for 10 minutes
            
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to get cities", ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    public function getToursGroupedByCity($duration = null, $budget = null, $priceCondition = null, $limit = 50) {
        // 1. Lấy dữ liệu phẳng bằng hàm searchItems
        $tours = $this->getTours(null, $duration, $budget, $limit, $priceCondition);
        
        // 2. Thực hiện logic nhóm kết quả
        $groupedResults = [];
        foreach ($tours as $tour) {
            $cityName = $tour['city_name'] ?? 'Unknown';
            if (!isset($groupedResults[$cityName])) {
                $groupedResults[$cityName] = [];
            }
            $groupedResults[$cityName][] = $tour;
        }
        
        return $groupedResults;
    }
    
    public function getHotelsGroupedByCity($rating = null, $budget = null, $priceCondition = null, $limit = 50) {
        // 1. Lấy dữ liệu phẳng bằng hàm searchItems
        $hotels = $this->getHotels(null, $limit, $rating, $budget, $priceCondition);
        
        // 2. Thực hiện logic nhóm kết quả
        $groupedResults = [];
        foreach ($hotels as $hotel) {
            $cityName = $hotel['city_name'] ?? 'Unknown';
            if (!isset($groupedResults[$cityName])) {
                $groupedResults[$cityName] = [];
            }
            $groupedResults[$cityName][] = $hotel;
        }
        
        return $groupedResults;
    }
    
    public function getSystemStats() {
        $cacheKey = 'system_stats';
        $cached = CacheService::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        try {
            $stats = [];
            
            // Get total counts with prepared statements
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM tours");
            $stmt->execute();
            $stats['total_tours'] = $stmt->get_result()->fetch_assoc()['count'];
            $stmt->close();
            
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM hotels");
            $stmt->execute();
            $stats['total_hotels'] = $stmt->get_result()->fetch_assoc()['count'];
            $stmt->close();
            
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM cities");
            $stmt->execute();
            $stats['total_cities'] = $stmt->get_result()->fetch_assoc()['count'];
            $stmt->close();
            
            // Get cities with tours
            $stmt = $this->db->prepare("
                SELECT c.city, COUNT(t.tourid) as tour_count 
                FROM cities c 
                LEFT JOIN tours t ON c.cityid = t.cityid 
                GROUP BY c.cityid, c.city 
                ORDER BY tour_count DESC
            ");
            $stmt->execute();
            $stats['cities_with_tours'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            CacheService::set($cacheKey, $stats, 300); // Cache for 5 minutes
            Logger::info("System stats generated", ['stats' => $stats]);
            
            return $stats;
        } catch (Exception $e) {
            Logger::error("Failed to get system stats", ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    public function saveConversation($userId, $userMessage, $botResponse) {
        try {
            $stmt = $this->db->prepare("INSERT INTO chat_history (user_id, user_message, bot_response) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }
            
            $botResponseText = is_array($botResponse) ? json_encode($botResponse) : $botResponse;
            $stmt->bind_param('iss', $userId, $userMessage, $botResponseText);
            $stmt->execute();
            $stmt->close();
            
            Logger::debug("Conversation saved", ['userId' => $userId]);
            
        } catch (Exception $e) {
            Logger::error("Failed to save conversation", ['error' => $e->getMessage()]);
        }
    }
    
    public function getChatHistory($userId, $limit = 50) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param('ii', $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $history = [];
            while ($row = $result->fetch_assoc()) {
                $botResponse = json_decode($row['bot_response'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $botResponse = ['text' => $row['bot_response'], 'type' => 'general'];
                }
                
                $history[] = [
                    'id' => $row['id'],
                    'user_message' => $row['user_message'],
                    'bot_response' => $botResponse,
                    'created_at' => $row['created_at']
                ];
            }
            $stmt->close();
            
            return $history;
        } catch (Exception $e) {
            Logger::error("Failed to get chat history", ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function findSimilarItemsByVector($queryVector, $limit = 10, $filters = []) {
        try {
            // Bước 1: Lấy tất cả các vector từ DB (kém hiệu quả với DB lớn, nhưng là cách mô phỏng)
            // Trong thực tế, bạn nên lọc trước bằng SQL nếu có thể (ví dụ: lọc theo city_id)
            $sql = "SELECT id, item_id, item_type, vector_embedding FROM item_vectors";
            // TODO: Thêm điều kiện WHERE dựa trên $filters nếu cần
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $allVectors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if (empty($allVectors)) {
                return [];
            }

            // Bước 2: Tính toán Cosine Similarity trong PHP
            $similarities = [];
            foreach ($allVectors as $row) {
                // Vector được lưu dưới dạng JSON string hoặc binary, cần giải mã
                $itemVector = json_decode($row['vector_embedding'], true); 
                if (is_array($itemVector)) {
                     $similarities[] = [
                        'item_id' => $row['item_id'],
                        'item_type' => $row['item_type'],
                        'score' => $this->cosineSimilarity($queryVector, $itemVector)
                    ];
                }
            }

            // Bước 3: Sắp xếp và lấy top N kết quả
            usort($similarities, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            return array_slice($similarities, 0, $limit);

        } catch (Exception $e) {
            Logger::error("Failed to find similar items by vector", ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function cosineSimilarity(array $vec1, array $vec2): float {
        $dotProduct = 0.0;
        $mag1 = 0.0;
        $mag2 = 0.0;
        $count = count($vec1);

        if ($count !== count($vec2)) {
            return 0.0; // Vectors must have the same dimension
        }

        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $mag1 += $vec1[$i] * $vec1[$i];
            $mag2 += $vec2[$i] * $vec2[$i];
        }

        $magnitude = sqrt($mag1) * sqrt($mag2);
        return $magnitude === 0.0 ? 0.0 : $dotProduct / $magnitude;
    }
}

?>