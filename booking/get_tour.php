<?php
include '../dbconnect.php';

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Initialize response
$response = [];

try {
    if (!empty($_GET['q'])) {
        $query = '%' . strtolower(trim($_GET['q'])) . '%';


        // Prepare query to find matching cities
        $stmt = $conn->prepare("SELECT city FROM cities WHERE LOWER(city) LIKE ?");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }

        $stmt->bind_param("s", $query);
        $stmt->execute();
        $result = $stmt->get_result();

        $cities = [];
        while ($row = $result->fetch_assoc()) {
            $cities[] = $row['city'];
        }

        $response = $cities;
        $stmt->close();
    } else {
        $response = []; // Return empty array for empty query
    }
} catch (Exception $e) {
    // Log error (optional: log to a file or error tracking system)
    error_log('Error in get_tour.php: ' . $e->getMessage());
    $response = ['error' => 'Server error occurred'];
    http_response_code(500); // Set HTTP status code
}

$conn->close();

// Output JSON response
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>