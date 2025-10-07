<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); // Không giới hạn thời gian chạy
ini_set('memory_limit', '512M'); // Tăng memory limit

require_once './config.php';
require_once './Logger.php';
require_once './Gemini.php';
require_once './Database.php';

class VectorGenerator {
    private $db;
    private $geminiService;
    private $stats = [
        'tours_processed' => 0,
        'hotels_processed' => 0,
        'tours_failed' => 0,
        'hotels_failed' => 0,
        'total_vectors_created' => 0,
        'start_time' => null,
        'end_time' => null
    ];
    
    // Cấu hình
    private const BATCH_SIZE = 10; // Số lượng items xử lý mỗi batch
    private const RETRY_ATTEMPTS = 3; // Số lần retry khi API fail
    private const RETRY_DELAY = 2; // Delay giữa các retry (giây)
    private const API_DELAY = 1; // Delay giữa các API calls (giây)
    
    public function __construct() {
        try {
            // Kết nối database
            $this->db = Config::getDatabaseConnection();
            Logger::info("Database connected successfully");
            
            // Khởi tạo Gemini Service
            $this->geminiService = new GeminiService();
            Logger::info("Gemini Service initialized");
            
            $this->stats['start_time'] = microtime(true);
            
        } catch (Exception $e) {
            Logger::critical("Failed to initialize VectorGenerator", [
                'error' => $e->getMessage()
            ]);
            die("Initialization failed: " . $e->getMessage() . "\n");
        }
    }
    
    /**
     * Main execution method
     */
    public function run($clearExisting = false) {
        echo "\n=== VECTOR GENERATION STARTED ===\n\n";
        
        // Bước 1: Clear existing vectors nếu cần
        if ($clearExisting) {
            echo "Clearing existing vectors...\n";
            $this->clearExistingVectors();
        }
        
        // Bước 2: Generate vectors cho tours
        echo "\n--- Processing Tours ---\n";
        $this->processTours();
        
        // Bước 3: Generate vectors cho hotels
        echo "\n--- Processing Hotels ---\n";
        $this->processHotels();
        
        // Bước 4: Show statistics
        $this->showStatistics();
        
        echo "\n=== VECTOR GENERATION COMPLETED ===\n\n";
    }
    
    /**
     * Clear existing vectors from database
     */
    private function clearExistingVectors() {
        try {
            $stmt = $this->db->prepare("DELETE FROM item_vectors");
            $stmt->execute();
            $deleted = $stmt->affected_rows;
            $stmt->close();
            
            echo "Deleted $deleted existing vectors\n";
            Logger::info("Cleared existing vectors", ['count' => $deleted]);
            
        } catch (Exception $e) {
            Logger::error("Failed to clear existing vectors", [
                'error' => $e->getMessage()
            ]);
            echo "Warning: Could not clear existing vectors\n";
        }
    }
    
    /**
     * Process all tours
     */
    private function processTours() {
        try {
            // Lấy tất cả tours cùng với city name
            $query = "SELECT t.*, c.city as city_name 
                     FROM tours t 
                     LEFT JOIN cities c ON t.cityid = c.cityid 
                     ORDER BY t.tourid ASC";
            
            $result = $this->db->query($query);
            
            if (!$result) {
                throw new Exception("Failed to fetch tours: " . $this->db->error);
            }
            
            $tours = $result->fetch_all(MYSQLI_ASSOC);
            $totalTours = count($tours);
            
            echo "Found $totalTours tours to process\n";
            Logger::info("Starting tour processing", ['total' => $totalTours]);
            
            // Process tours in batches
            $batches = array_chunk($tours, self::BATCH_SIZE);
            $batchNumber = 1;
            $totalBatches = count($batches);
            
            foreach ($batches as $batch) {
                echo "\nProcessing batch $batchNumber/$totalBatches...\n";
                
                foreach ($batch as $tour) {
                    $this->processTourItem($tour);
                }
                
                $batchNumber++;
                
                // Delay giữa các batches để tránh rate limit
                if ($batchNumber <= $totalBatches) {
                    echo "Waiting " . self::API_DELAY . " seconds...\n";
                    sleep(self::API_DELAY);
                }
            }
            
        } catch (Exception $e) {
            Logger::error("Tour processing failed", [
                'error' => $e->getMessage()
            ]);
            echo "Error processing tours: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Process single tour item
     */
    private function processTourItem($tour) {
        $tourId = $tour['tourid'];
        $tourName = $tour['tour_name'];
        
        try {
            // Build content text for embedding
            $contentText = $this->buildTourContentText($tour);
            
            echo "Processing Tour #$tourId: $tourName... ";
            
            // Generate embedding with retry logic
            $embedding = $this->generateEmbeddingWithRetry($contentText);
            
            if (!$embedding) {
                throw new Exception("Failed to generate embedding");
            }
            
            // Save to database
            $this->saveVector($tourId, 'tour', $contentText, $embedding);
            
            $this->stats['tours_processed']++;
            $this->stats['total_vectors_created']++;
            
            echo "✓ Success\n";
            
        } catch (Exception $e) {
            $this->stats['tours_failed']++;
            echo "✗ Failed: " . $e->getMessage() . "\n";
            
            Logger::error("Failed to process tour", [
                'tour_id' => $tourId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Process all hotels
     */
    private function processHotels() {
        try {
            // Lấy tất cả hotels cùng với city name
            $query = "SELECT h.*, c.city as city_name 
                     FROM hotels h 
                     LEFT JOIN cities c ON h.cityid = c.cityid 
                     ORDER BY h.hotelid ASC";
            
            $result = $this->db->query($query);
            
            if (!$result) {
                throw new Exception("Failed to fetch hotels: " . $this->db->error);
            }
            
            $hotels = $result->fetch_all(MYSQLI_ASSOC);
            $totalHotels = count($hotels);
            
            echo "Found $totalHotels hotels to process\n";
            Logger::info("Starting hotel processing", ['total' => $totalHotels]);
            
            // Process hotels in batches
            $batches = array_chunk($hotels, self::BATCH_SIZE);
            $batchNumber = 1;
            $totalBatches = count($batches);
            
            foreach ($batches as $batch) {
                echo "\nProcessing batch $batchNumber/$totalBatches...\n";
                
                foreach ($batch as $hotel) {
                    $this->processHotelItem($hotel);
                }
                
                $batchNumber++;
                
                // Delay giữa các batches
                if ($batchNumber <= $totalBatches) {
                    echo "Waiting " . self::API_DELAY . " seconds...\n";
                    sleep(self::API_DELAY);
                }
            }
            
        } catch (Exception $e) {
            Logger::error("Hotel processing failed", [
                'error' => $e->getMessage()
            ]);
            echo "Error processing hotels: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Process single hotel item
     */
    private function processHotelItem($hotel) {
        $hotelId = $hotel['hotelid'];
        $hotelName = $hotel['hotel'];
        
        try {
            // Build content text for embedding
            $contentText = $this->buildHotelContentText($hotel);
            
            echo "Processing Hotel #$hotelId: $hotelName... ";
            
            // Generate embedding with retry logic
            $embedding = $this->generateEmbeddingWithRetry($contentText);
            
            if (!$embedding) {
                throw new Exception("Failed to generate embedding");
            }
            
            // Save to database
            $this->saveVector($hotelId, 'hotel', $contentText, $embedding);
            
            $this->stats['hotels_processed']++;
            $this->stats['total_vectors_created']++;
            
            echo "✓ Success\n";
            
        } catch (Exception $e) {
            $this->stats['hotels_failed']++;
            echo "✗ Failed: " . $e->getMessage() . "\n";
            
            Logger::error("Failed to process hotel", [
                'hotel_id' => $hotelId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Build rich content text for tour embedding
     */
    private function buildTourContentText($tour) {
        $parts = [
            $tour['tour_name'] ?? '',
            'City: ' . ($tour['city_name'] ?? 'Vietnam'),
            'Duration: ' . ($tour['duration_days'] ?? 0) . ' days',
            'Price: ' . number_format($tour['price_per_person'] ?? 0) . ' VND per person',
            'Season: ' . ($tour['season'] ?? 'All year'),
            'Description: ' . ($tour['description'] ?? ''),
            'Keywords: tour package travel Vietnam sightseeing excursion'
        ];
        
        return implode('. ', array_filter($parts));
    }
    
    /**
     * Build rich content text for hotel embedding
     */
    private function buildHotelContentText($hotel) {
        $parts = [
            $hotel['hotel'] ?? '',
            'City: ' . ($hotel['city_name'] ?? 'Vietnam'),
            'Rating: ' . ($hotel['ratings'] ?? 0) . ' stars',
            'Price: ' . number_format($hotel['cost'] ?? 0) . ' VND per night',
            'Amenities: ' . ($hotel['amenities'] ?? 'Standard amenities'),
            'Keywords: hotel accommodation stay resort lodge Vietnam'
        ];
        
        return implode('. ', array_filter($parts));
    }
    
    /**
     * Generate embedding with retry logic
     */
    private function generateEmbeddingWithRetry($text) {
        $attempt = 1;
        $lastError = null;
        
        while ($attempt <= self::RETRY_ATTEMPTS) {
            try {
                $embedding = $this->geminiService->generateEmbedding($text);
                
                if ($embedding && is_array($embedding) && !empty($embedding)) {
                    return $embedding;
                }
                
                throw new Exception("Empty embedding returned");
                
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                
                if ($attempt < self::RETRY_ATTEMPTS) {
                    echo "\n  Retry $attempt/" . self::RETRY_ATTEMPTS . " after error: $lastError\n";
                    sleep(self::RETRY_DELAY);
                }
                
                $attempt++;
            }
        }
        
        throw new Exception("All retry attempts failed. Last error: $lastError");
    }
    
    /**
     * Save vector to database
     */
    private function saveVector($itemId, $itemType, $contentText, $embedding) {
        try {
            // Encode embedding as JSON
            $embeddingJson = json_encode($embedding);
            
            if ($embeddingJson === false) {
                throw new Exception("Failed to encode embedding as JSON");
            }
            
            // Check if vector already exists
            $checkStmt = $this->db->prepare(
                "SELECT id FROM item_vectors WHERE item_id = ? AND item_type = ?"
            );
            $checkStmt->bind_param('is', $itemId, $itemType);
            $checkStmt->execute();
            $exists = $checkStmt->get_result()->num_rows > 0;
            $checkStmt->close();
            
            if ($exists) {
                // Update existing vector
                $stmt = $this->db->prepare(
                    "UPDATE item_vectors 
                     SET content_text = ?, vector_embedding = ? 
                     WHERE item_id = ? AND item_type = ?"
                );
                $stmt->bind_param('ssis', $contentText, $embeddingJson, $itemId, $itemType);
            } else {
                // Insert new vector
                $stmt = $this->db->prepare(
                    "INSERT INTO item_vectors (item_id, item_type, content_text, vector_embedding) 
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param('isss', $itemId, $itemType, $contentText, $embeddingJson);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $stmt->error);
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            Logger::error("Failed to save vector", [
                'item_id' => $itemId,
                'item_type' => $itemType,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Show statistics
     */
    private function showStatistics() {
        $this->stats['end_time'] = microtime(true);
        $duration = $this->stats['end_time'] - $this->stats['start_time'];
        
        echo "\n=== STATISTICS ===\n";
        echo "Tours processed: " . $this->stats['tours_processed'] . "\n";
        echo "Tours failed: " . $this->stats['tours_failed'] . "\n";
        echo "Hotels processed: " . $this->stats['hotels_processed'] . "\n";
        echo "Hotels failed: " . $this->stats['hotels_failed'] . "\n";
        echo "Total vectors created: " . $this->stats['total_vectors_created'] . "\n";
        echo "Total duration: " . round($duration, 2) . " seconds\n";
        echo "Average time per item: " . round($duration / max(1, $this->stats['total_vectors_created']), 2) . " seconds\n";
        
        Logger::info("Vector generation completed", $this->stats);
    }
    
    /**
     * Destructor - close database connection
     */
    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}

// =====================================================
// SCRIPT EXECUTION
// =====================================================

// Check if running from command line
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    // Running from browser - add basic authentication
    header('Content-Type: text/plain; charset=utf-8');
    
    // SECURITY: Add basic authentication here
    // Uncomment and set your password
    /*
    if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_PW'] !== 'your_password') {
        header('WWW-Authenticate: Basic realm="Vector Generation"');
        header('HTTP/1.0 401 Unauthorized');
        die("Access denied\n");
    }
    */
}

// Parse command line arguments
$clearExisting = false;

if ($isCLI && isset($argv)) {
    foreach ($argv as $arg) {
        if ($arg === '--clear' || $arg === '-c') {
            $clearExisting = true;
        }
        if ($arg === '--help' || $arg === '-h') {
            echo "Usage: php generate_vectors.php [options]\n";
            echo "Options:\n";
            echo "  --clear, -c    Clear existing vectors before generating\n";
            echo "  --help, -h     Show this help message\n";
            exit(0);
        }
    }
}

// Check for clear parameter in browser
if (!$isCLI && isset($_GET['clear']) && $_GET['clear'] === '1') {
    $clearExisting = true;
}

try {
    // Create and run generator
    $generator = new VectorGenerator();
    $generator->run($clearExisting);
    
    exit(0);
    
} catch (Exception $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
    Logger::critical("Vector generation failed", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit(1);
}
?>