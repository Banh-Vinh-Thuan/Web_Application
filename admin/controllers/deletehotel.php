<?php
include '../../dbconnect.php';

if (isset($_GET['delete'])) {
    $hotelid = (int)$_GET['delete'];
    
    // Get image filename before deleting
    $result = $conn->query("SELECT image FROM hotels WHERE hotelid = $hotelid");
    $hotel = $result->fetch_assoc();
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM hotels WHERE hotelid = ?");
    $stmt->bind_param("i", $hotelid);
    
    if ($stmt->execute()) {
        // Delete image file if exists
        if ($hotel && $hotel['image']) {
            $image_path = '../../images/hotels/' . $hotel['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        header("Location: ../adminviewhotel.php?success=Hotel deleted successfully");
    } else {
        header("Location: ../adminviewhotel.php?error=Failed to delete hotel: " . $conn->error);
    }
    
    $stmt->close();
}

$conn->close();
?>