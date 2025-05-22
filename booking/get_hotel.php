<?php
include '../dbconnect.php';

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Initialize response
$response = [];

try {
    if (!empty($_GET['q'])) {
        $query = '%' . strtolower(trim($_GET['q'])) . '%';

        // Sửa SELECT để lấy đúng cột: cityid và city
        $stmt = $conn->prepare("SELECT cityid, city FROM cities WHERE LOWER(city) LIKE ?");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }

        $stmt->bind_param("s", $query);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $response[] = [
                'id' => $row['cityid'],  // Dùng cityid làm id
                'name' => $row['city']
            ];
        }

        $stmt->close();
    } else {
        $response = []; // Empty result
    }
} catch (Exception $e) {
    error_log('Error in get_tour.php: ' . $e->getMessage());
    $response = ['error' => 'Server error occurred'];
    http_response_code(500);
}

$conn->close();

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
