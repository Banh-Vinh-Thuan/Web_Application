<?php
include '../dbconnect.php';

if (isset($_GET['cityid'])) {
    $cityid = $_GET['cityid'];
    $stmt = $conn->prepare("SELECT hotelid, hotel, cost FROM hotels WHERE cityid = ?");
    $stmt->bind_param("i", $cityid);
    $stmt->execute();
    $result = $stmt->get_result();
    $hotels = [];
    while ($row = $result->fetch_assoc()) {
        $hotels[] = [
            'hotelid' => $row['hotelid'],
            'hotel' => $row['hotel'],
            'cost' => $row['cost']
        ];
    }
    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode($hotels);
}
?>