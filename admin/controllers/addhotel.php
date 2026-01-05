<?php
include '../../dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hotel = $_POST['hotel'];
    $cityid = (int)$_POST['cityid'];
    $cost = (float)$_POST['cost'];
    $amenities = $_POST['amenities'];
    $ratings = (int)$_POST['ratings'];
    $image_name = null;
    
    // Handle image upload
    if (isset($_FILES['hotel_image']) && $_FILES['hotel_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $filename = $_FILES['hotel_image']['name'];
        $filetype = $_FILES['hotel_image']['type'];
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
        if (!move_uploaded_file($_FILES['hotel_image']['tmp_name'], $upload_path . $image_name)) {
            header("Location: ../adminviewhotel.php?error=Failed to upload image.");
            exit();
        }
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO hotels (hotel, cityid, cost, amenities, image, ratings) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sidssi", $hotel, $cityid, $cost, $amenities, $image_name, $ratings);
    
    if ($stmt->execute()) {
        header("Location: ../adminviewhotel.php?success=Hotel added successfully");
    } else {
        // Delete uploaded image if database insert fails
        if ($image_name && file_exists($upload_path . $image_name)) {
            unlink($upload_path . $image_name);
        }
        header("Location: ../adminviewhotel.php?error=Failed to add hotel: " . $conn->error);
    }
    
    $stmt->close();
}

$conn->close();
?>