<?php
include '../dbconnect.php';

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Initialize response
$response = [];

try {
    if (!empty($_GET['q'])) {
        $query = '%' . strtolower(trim($_GET['q'])) . '%';
        
        // Get the type parameter to determine response format
        $type = $_GET['type'] ?? 'tour'; // default to tour format
        
        $stmt = $conn->prepare("SELECT cityid, city FROM cities WHERE LOWER(city) LIKE ?");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }

        $stmt->bind_param("s", $query);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if ($type === 'hotel') {
                // Format for hotel (with id and name)
                $response[] = [
                    'id' => $row['cityid'],
                    'name' => $row['city']
                ];
            } else {
                // Format for tour (just city names)
                $response[] = $row['city'];
            }
        }

        $stmt->close();
    } else {
        $response = []; // Empty result
    }
} catch (Exception $e) {
    error_log('Error in get_cities.php: ' . $e->getMessage());
    $response = ['error' => 'Server error occurred'];
    http_response_code(500);
}

$conn->close();

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>