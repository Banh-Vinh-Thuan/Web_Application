<?php
include '../../dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hotelid = (int)$_POST['hotelid'];
    $hotel = $_POST['hotel'];
    $cityid = (int)$_POST['cityid'];
    $cost = (float)$_POST['cost'];
    $discount = isset($_POST['discount']) ? (int)$_POST['discount'] : 0;
    $amenities = $_POST['amenities'];
    $ratings = (int)$_POST['ratings'];
    $existing_image = $_POST['existing_image'];
    $image_name = $existing_image; // Keep existing image by default
    
    // Handle new image upload
    if (isset($_FILES['hotel_image']) && $_FILES['hotel_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $filename = $_FILES['hotel_image']['name'];
        $filesize = $_FILES['hotel_image']['size'];
        
        // Get file extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Validate file
        if (!in_array($ext, $allowed)) {
            header("Location: ../adminviewhotel.php?error=Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed.");
            exit();
        }
        
        if ($filesize > 5242880) { // 5MB limit
            header("Location: ../adminviewhotel.php?error=File size too large. Maximum 5MB allowed.");
            exit();
        }
        
        // Create unique filename
        $image_name = uniqid() . '_' . time() . '.' . $ext;
        $upload_path = '../../images/hotels/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['hotel_image']['tmp_name'], $upload_path . $image_name)) {
            // Delete old image if exists
            if ($existing_image && file_exists($upload_path . $existing_image)) {
                unlink($upload_path . $existing_image);
            }
        } else {
            header("Location: ../adminviewhotel.php?error=Failed to upload new image.");
            exit();
        }
    }
    
    // Update database
    $stmt = $conn->prepare("UPDATE hotels SET hotel = ?, cityid = ?, cost = ?, discount = ?, amenities = ?, image = ?, ratings = ? WHERE hotelid = ?");
    $stmt->bind_param("sidiisii", $hotel, $cityid, $cost, $discount, $amenities, $image_name, $ratings, $hotelid);
    
    if ($stmt->execute()) {
        header("Location: ../adminviewhotel.php?success=Hotel updated successfully");
    } else {
        header("Location: ../adminviewhotel.php?error=Failed to update hotel: " . $conn->error);
    }
    
    $stmt->close();
}

$conn->close();
?>