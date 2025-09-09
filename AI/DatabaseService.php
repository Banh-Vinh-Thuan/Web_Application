<?php
require_once './Logger.php';
require_once './CacheService.php';

/**
 * Enhanced Database service for travel data operations with natural language support
 */
class DatabaseService {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Find city ID by name (case-insensitive, supports partial matching)
     * Essential for natural language queries like "hoi an", "Ho Chi Minh", etc.
     */
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
    
    /**
     * Smart search for tours and hotels combined
     * Handles queries like "find tour and hotel in hoi an"
     */
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
    
    /**
     * Get similar city names for suggestions when exact match fails
     */
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
        try {
            $sql = "SELECT t.*, c.city as city_name FROM tours t 
                    LEFT JOIN cities c ON t.cityid = c.cityid WHERE 1=1";
            $params = [];
            $types = "";
            
            if ($cityId !== null) {
                $sql .= " AND t.cityid = ?";
                $params[] = $cityId;
                $types .= 'i';
            }
            
            if ($duration !== null) {
                $sql .= " AND t.duration_days = ?";
                $params[] = $duration;
                $types .= 'i';
            }
            
            if ($budget !== null) {
                if ($priceCondition === 'under') {
                    $sql .= " AND t.price_per_person <= ?";
                    $order = "ASC";
                } elseif ($priceCondition === 'over') {
                    $sql .= " AND t.price_per_person >= ?";
                    $order = "DESC";
                } else {
                    $sql .= " AND t.price_per_person <= ?";
                    $order = "ASC";
                }
                $params[] = $budget;
                $types .= 'i';
            } else {
                $order = "ASC";
            }

            $sql .= " ORDER BY t.price_per_person $order LIMIT ?";
            $params[] = $limit;
            $types .= 'i';
            
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
            
            Logger::debug("Tours retrieved", ['count' => count($result), 'filters' => compact('cityId', 'duration', 'budget', 'priceCondition')]);
            
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to get tours", ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    public function getHotels($cityId = null, $limit = 6, $rating = null, $budget = null, $priceCondition = null) {
        try {
            $sql = "SELECT h.*, c.city as city_name FROM hotels h 
                    LEFT JOIN cities c ON h.cityid = c.cityid WHERE 1=1";
            $params = [];
            $types = "";
            
            if ($cityId !== null) {
                $sql .= " AND h.cityid = ?";
                $params[] = $cityId;
                $types .= 'i';
            }
            
            if ($rating !== null) {
                $sql .= " AND h.ratings >= ?";
                $params[] = $rating;
                $types .= 'i';
            }
            
            if ($budget !== null) {
                if ($priceCondition === 'under') {
                    $sql .= " AND h.cost <= ?";
                } elseif ($priceCondition === 'over') {
                    $sql .= " AND h.cost >= ?";
                } else {
                    $sql .= " AND h.cost <= ?";
                }
                $params[] = $budget;
                $types .= 'i';
            }
            
            $sql .= " ORDER BY h.ratings DESC, h.cost ASC LIMIT ?";
            $params[] = $limit;
            $types .= 'i';
            
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
            
            Logger::debug("Hotels retrieved", ['count' => count($result), 'filters' => compact('cityId', 'rating', 'budget', 'priceCondition')]);
            
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to get hotels", ['error' => $e->getMessage()]);
            return [];
        }
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
        try {
            $sql = "SELECT t.*, c.city as city_name FROM tours t 
                    LEFT JOIN cities c ON t.cityid = c.cityid WHERE 1=1";
            $params = [];
            $types = "";
            
            if ($duration !== null) {
                $sql .= " AND t.duration_days = ?";
                $params[] = $duration;
                $types .= 'i';
            }
            
            if ($budget !== null) {
                if ($priceCondition === 'under') {
                    $sql .= " AND t.price_per_person <= ?";
                } elseif ($priceCondition === 'over') {
                    $sql .= " AND t.price_per_person >= ?";
                } else {
                    $sql .= " AND t.price_per_person <= ?";
                }
                $params[] = $budget;
                $types .= 'i';
            }
            
            $sql .= " ORDER BY c.city, t.price_per_person ASC LIMIT ?";
            $params[] = $limit;
            $types .= 'i';
            
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
            
            // Group by city
            $groupedResults = [];
            foreach ($result as $tour) {
                $cityName = $tour['city_name'] ?? 'Unknown';
                if (!isset($groupedResults[$cityName])) {
                    $groupedResults[$cityName] = [];
                }
                $groupedResults[$cityName][] = $tour;
            }
            
            Logger::debug("Tours grouped by city retrieved", [
                'total_tours' => count($result), 
                'cities' => count($groupedResults),
                'filters' => compact('duration', 'budget', 'priceCondition')
            ]);
            
            return $groupedResults;
        } catch (Exception $e) {
            Logger::error("Failed to get tours grouped by city", ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    public function getHotelsGroupedByCity($rating = null, $budget = null, $priceCondition = null, $limit = 50) {
        try {
            $sql = "SELECT h.*, c.city as city_name FROM hotels h 
                    LEFT JOIN cities c ON h.cityid = c.cityid WHERE 1=1";
            $params = [];
            $types = "";
            
            if ($rating !== null) {
                $sql .= " AND h.ratings >= ?";
                $params[] = $rating;
                $types .= 'i';
            }
            
            if ($budget !== null) {
                if ($priceCondition === 'under') {
                    $sql .= " AND h.cost <= ?";
                } elseif ($priceCondition === 'over') {
                    $sql .= " AND h.cost >= ?";
                } else {
                    $sql .= " AND h.cost <= ?";
                }
                $params[] = $budget;
                $types .= 'i';
            }
            
            $sql .= " ORDER BY c.city, h.ratings DESC, h.cost ASC LIMIT ?";
            $params[] = $limit;
            $types .= 'i';
            
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
            
            // Group by city
            $groupedResults = [];
            foreach ($result as $hotel) {
                $cityName = $hotel['city_name'] ?? 'Unknown';
                if (!isset($groupedResults[$cityName])) {
                    $groupedResults[$cityName] = [];
                }
                $groupedResults[$cityName][] = $hotel;
            }
            
            Logger::debug("Hotels grouped by city retrieved", [
                'total_hotels' => count($result), 
                'cities' => count($groupedResults),
                'filters' => compact('rating', 'budget', 'priceCondition')
            ]);
            
            return $groupedResults;
        } catch (Exception $e) {
            Logger::error("Failed to get hotels grouped by city", ['error' => $e->getMessage()]);
            return [];
        }
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
}

?>