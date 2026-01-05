<?php
require_once '../../dbconnect.php'; // Path to your database connection

// UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST['booking_id'];
    // Chỉ lấy các trường được phép chỉnh sửa từ POST
    $name = $_POST['name'];
    $email = $_POST['email'];
    $tour_date = $_POST['tour_date'];
    $contact = $_POST['contact'];
    $paymentStatus = $_POST['payment_status'];

    // Lấy lại các giá trị cố định từ hidden fields (để đảm bảo không bị thay đổi)
    $userid = $_POST['userid'];
    $cityid = $_POST['cityid'];
    $city_name = $_POST['city_name'];
    $tourid = $_POST['tourid'];
    $tour_name = $_POST['tour_name'];
    $tourists = $_POST['tourists'];
    $price_per_person = $_POST['price_per_person'];
    $total_amount = $price_per_person * $tourists;

    $stmt = $conn->prepare("UPDATE tour_bookings SET name=?, email=?, tour_date=?, contact=?, payment_status=? WHERE booking_id=?");
    $stmt->bind_param(
        "sssssi",
        $name,
        $email,
        $tour_date,
        $contact,
        $paymentStatus,
        $booking_id
    );
    $stmt->execute();
    header("Location: adminviewtourbooking.php");
    exit;
}

// Fetch booking data for editing
$booking = [
    "booking_id" => "",
    "userid" => "",
    "name" => "",
    "email" => "",
    "cityid" => "",
    "city_name" => "",
    "tourid" => "",
    "tour_name" => "",
    "tourists" => 1,
    "tour_date" => "",
    "contact" => "",
    "price_per_person" => "",
    "payment_status" => ""
];
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM tour_bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Tour Booking</title>
    <link rel="stylesheet" type="text/css" href="../../css/adminviewtourbooking.css">
    <link rel="icon" type="image/png" href="../../images/favicon.png">
    <style>
        .readonly-field {
            background-color: #f0f0f0;
            color: #666;
            cursor: not-allowed;
        }
        .readonly-info {
            font-size: 0.85em;
            color: #999;
            font-style: italic;
            margin-top: -8px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="form-wrapper">
        <form method="POST">
            <h2>Edit Booking</h2>
            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking['booking_id']) ?>">
            
            <!-- Hidden fields cho các giá trị cố định -->
            <input type="hidden" name="userid" value="<?= htmlspecialchars($booking['userid']) ?>">
            <input type="hidden" name="cityid" value="<?= htmlspecialchars($booking['cityid']) ?>">
            <input type="hidden" name="city_name" value="<?= htmlspecialchars($booking['city_name']) ?>">
            <input type="hidden" name="tourid" value="<?= htmlspecialchars($booking['tourid']) ?>">
            <input type="hidden" name="tour_name" value="<?= htmlspecialchars($booking['tour_name']) ?>">
            <input type="hidden" name="tourists" value="<?= htmlspecialchars($booking['tourists']) ?>">
            <input type="hidden" name="price_per_person" value="<?= htmlspecialchars($booking['price_per_person']) ?>">
            
            <div class="form-columns">
                <div>
                    <!-- Trường KHÔNG thể chỉnh sửa -->
                    <label for="userid">User ID:</label>
                    <input type="number" id="userid" class="readonly-field" value="<?= htmlspecialchars($booking['userid']) ?>" readonly>
                    
                    <!-- Trường CÓ THỂ chỉnh sửa -->
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($booking['name']) ?>" required>
                    
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($booking['email']) ?>" required>
                    
                    <!-- Trường KHÔNG thể chỉnh sửa -->
                    <label for="cityid">City ID:</label>
                    <input type="number" id="cityid" class="readonly-field" value="<?= htmlspecialchars($booking['cityid']) ?>" readonly>
                    
                    <label for="city_name">City Name:</label>
                    <input type="text" id="city_name" class="readonly-field" value="<?= htmlspecialchars($booking['city_name']) ?>" readonly>
                    
                    <label for="tourid">Tour ID:</label>
                    <input type="number" id="tourid" class="readonly-field" value="<?= htmlspecialchars($booking['tourid']) ?>" readonly>
                </div>
                <div>
                    <!-- Trường KHÔNG thể chỉnh sửa -->
                    <label for="tour_name">Tour Name:</label>
                    <input type="text" id="tour_name" class="readonly-field" value="<?= htmlspecialchars($booking['tour_name']) ?>" readonly>
                    
                    <label for="tourists">Guests:</label>
                    <input type="number" id="tourists" class="readonly-field" value="<?= htmlspecialchars($booking['tourists']) ?>" readonly>
                    
                    <!-- Trường CÓ THỂ chỉnh sửa -->
                    <label for="tour_date">Tour Date:</label>
                    <input type="date" id="tour_date" name="tour_date" value="<?= htmlspecialchars($booking['tour_date']) ?>" required>
                    
                    <label for="contact">Contact:</label>
                    <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($booking['contact']) ?>" required>
                    
                    <!-- Trường KHÔNG thể chỉnh sửa -->
                    <label for="price_per_person">Price per Person:</label>
                    <input type="number" id="price_per_person" class="readonly-field" value="<?= htmlspecialchars($booking['price_per_person']) ?>" readonly>
                    
                    <!-- Trường CÓ THỂ chỉnh sửa -->
                    <label for="payment_status">Status:</label>
                    <select id="payment_status" name="payment_status" required>
                        <option value="pending" <?= (isset($booking['payment_status']) && $booking['payment_status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= (isset($booking['payment_status']) && $booking['payment_status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="failed" <?= (isset($booking['payment_status']) && $booking['payment_status'] == 'failed') ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
            </div>
            <input type="submit" value="Update Booking">
        </form>
    </div>
    <button onclick="window.location.href='adminviewtourbooking.php'">Back to Manage Tour Bookings</button>
</body>
</html>