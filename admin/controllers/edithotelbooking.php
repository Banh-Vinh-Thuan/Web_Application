<?php
include '../../dbconnect.php';

// Function to validate date format (YYYY-MM-DD)
function isValidDate($date)
{
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        try {
            new DateTime($date);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST["booking_id"] ?? '';
    // Chỉ lấy các trường được phép chỉnh sửa từ POST
    $name = $_POST["name"] ?? '';
    $email = $_POST["email"] ?? '';
    $contact = $_POST["contact"] ?? '';
    $check_in_date = $_POST["check_in_date"] ?? '';
    $check_out_date = $_POST["check_out_date"] ?? '';
    $payment_status = $_POST["payment_status"] ?? '';

    // Lấy lại các giá trị cố định từ hidden fields
    $userid = $_POST["userid"] ?? '';
    $cityid = $_POST["cityid"] ?? '';
    $city_name = $_POST["city_name"] ?? '';
    $hotelid = $_POST["hotelid"] ?? '';
    $hotel_name = $_POST["hotel_name"] ?? '';
    $tourists = $_POST["tourists"] ?? '';
    $cost_per_day = $_POST["cost_per_day"] ?? '';
    $total_amount = $_POST["total_amount"] ?? '';
    $number_of_rooms = $_POST["number_of_rooms"] ?? '';
    $room_type = $_POST["room_type"] ?? '';

    // Validate date inputs
    $errors = [];
    if (!isValidDate($check_in_date)) {
        $errors[] = "Invalid check-in date format. Use YYYY-MM-DD.";
    }
    if (!isValidDate($check_out_date)) {
        $errors[] = "Invalid check-out date format. Use YYYY-MM-DD.";
    }

    if (!empty($errors)) {
        echo implode("<br>", $errors);
        exit();
    }

    // Prepare the UPDATE statement - chỉ update các trường được phép chỉnh sửa
    $stmt = $conn->prepare("UPDATE hotel_bookings 
        SET name = ?, email = ?, contact = ?, 
            check_in_date = ?, check_out_date = ?, payment_status = ?
        WHERE booking_id = ?");

    if (!$stmt) {
        echo "Prepare failed: " . $conn->error;
        exit();
    }

    $stmt->bind_param(
        "ssssssi",
        $name,
        $email,
        $contact,
        $check_in_date,
        $check_out_date,
        $payment_status,
        $booking_id
    );

    if (!$stmt->execute()) {
        echo "SQL error: " . $stmt->error;
    } else {
        echo "Booking updated successfully.";
        header("Location: ../adminviewhotelbooking.php");
        exit();
    }
    $stmt->close();
}

$edit = null;
if (isset($_GET["edit"])) {
    $id = $_GET["edit"];
    $edit = $conn->query("SELECT * FROM hotel_bookings WHERE booking_id = $id")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Hotel Booking</title>
    <link rel="stylesheet" type="text/css" href="../../css/adminviewhotelbooking.css">
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
    <div class="form-container">
        <h2>Edit Hotel Booking #<?= htmlspecialchars($edit["booking_id"] ?? '') ?></h2>
        <form method="POST" novalidate>
            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($edit["booking_id"] ?? '') ?>">

            <!-- Hidden fields cho các giá trị cố định -->
            <input type="hidden" name="userid" value="<?= htmlspecialchars($edit["userid"] ?? '') ?>">
            <input type="hidden" name="cityid" value="<?= htmlspecialchars($edit["cityid"] ?? '') ?>">
            <input type="hidden" name="city_name" value="<?= htmlspecialchars($edit["city_name"] ?? '') ?>">
            <input type="hidden" name="hotelid" value="<?= htmlspecialchars($edit["hotelid"] ?? '') ?>">
            <input type="hidden" name="hotel_name" value="<?= htmlspecialchars($edit["hotel_name"] ?? '') ?>">
            <input type="hidden" name="tourists" value="<?= htmlspecialchars($edit["tourists"] ?? '') ?>">
            <input type="hidden" name="cost_per_day" value="<?= htmlspecialchars($edit["cost_per_day"] ?? '') ?>">
            <input type="hidden" name="total_amount" value="<?= htmlspecialchars($edit["total_amount"] ?? '') ?>">
            <input type="hidden" name="number_of_rooms" value="<?= htmlspecialchars($edit["number_of_rooms"] ?? '') ?>">
            <input type="hidden" name="room_type" value="<?= htmlspecialchars($edit["room_type"] ?? '') ?>">

            <div class="form-columns">
                <div>
                    <!-- Trường KHÔNG thể chỉnh sửa -->
                    <label for="userid">User ID:</label>
                    <input id="userid" type="number" class="readonly-field" value="<?= htmlspecialchars($edit["userid"] ?? '') ?>" readonly>
                    
                    <!-- Trường CÓ THỂ chỉnh sửa -->
                    <label for="name">Name:</label>
                    <input id="name" name="name" type="text" value="<?= htmlspecialchars($edit["name"] ?? '') ?>" required>

                    <label for="email">Email:</label>
                    <input id="email" name="email" type="email" value="<?= htmlspecialchars($edit["email"] ?? '') ?>" required>

                    <!-- Trường KHÔNG thể chỉnh sửa -->
                    <label for="cityid">City ID:</label>
                    <input id="cityid" type="number" class="readonly-field" value="<?= htmlspecialchars($edit["cityid"] ?? '') ?>" readonly>
                    
                    <label for="city_name">City Name:</label>
                    <input id="city_name" type="text" class="readonly-field" value="<?= htmlspecialchars($edit["city_name"] ?? '') ?>" readonly>
                    
                    <label for="hotelid">Hotel ID:</label>
                    <input id="hotelid" type="number" class="readonly-field" value="<?= htmlspecialchars($edit["hotelid"] ?? '') ?>" readonly>
                    
                    <label for="hotel_name">Hotel Name:</label>
                    <input id="hotel_name" type="text" class="readonly-field" value="<?= htmlspecialchars($edit["hotel_name"] ?? '') ?>" readonly>
                    
                    <!-- Trường CÓ THỂ chỉnh sửa -->
                    <label for="payment_status">Payment Status:</label>
                    <select id="payment_status" name="payment_status" required>
                        <option value="pending" <?= (isset($edit['payment_status']) && $edit['payment_status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="failed" <?= (isset($edit['payment_status']) && $edit['payment_status'] == 'failed') ? 'selected' : '' ?>>Failed</option>
                        <option value="completed" <?= (isset($edit['payment_status']) && $edit['payment_status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div>
                    <!-- Trường KHÔNG thể chỉnh sửa -->
                    <label for="number_of_rooms">Number of Rooms:</label>
                    <input id="number_of_rooms" type="number" class="readonly-field" value="<?= htmlspecialchars($edit["number_of_rooms"] ?? '1') ?>" readonly>
                    
                    <!-- Trường CÓ THỂ chỉnh sửa -->
                    <label for="check_in_date">Check-in Date:</label>
                    <input id="check_in_date" name="check_in_date" type="date" value="<?= htmlspecialchars($edit["check_in_date"] ?? '') ?>" required>

                    <label for="check_out_date">Check-out Date:</label>
                    <input id="check_out_date" name="check_out_date" type="date" value="<?= htmlspecialchars($edit["check_out_date"] ?? '') ?>" required>

                    <!-- Trường KHÔNG thể chỉnh sửa -->
                    <label for="room_type">Room Type:</label>
                    <input id="room_type" type="text" class="readonly-field" value="<?= htmlspecialchars($edit["room_type"] ?? '') ?>" readonly>
                    
                    <label for="tourists">Number of Tourists:</label>
                    <input id="tourists" type="number" class="readonly-field" value="<?= htmlspecialchars($edit["tourists"] ?? '1') ?>" readonly>
                    
                    <!-- Trường CÓ THỂ chỉnh sửa -->
                    <label for="contact">Contact Number:</label>
                    <input id="contact" name="contact" type="text" value="<?= htmlspecialchars($edit["contact"] ?? '') ?>" required>

                    <!-- Trường KHÔNG thể chỉnh sửa -->
                    <label for="cost_per_day">Cost per Day:</label>
                    <input id="cost_per_day" type="number" class="readonly-field" value="<?= htmlspecialchars($edit["cost_per_day"] ?? '') ?>" readonly>
                    
                    <label for="total_amount">Total Amount:</label>
                    <input id="total_amount" type="number" class="readonly-field" value="<?= htmlspecialchars($edit["total_amount"] ?? '') ?>" readonly>
                                    </div>
            </div>

            <input type="submit" value="Update Booking">
        </form>
    </div>

    <div class="back-btn-wrapper">
        <button class="back-btn" onclick="window.location.href='../adminviewhotelbooking.php'">Back to Manage Hotel Bookings</button>
    </div>
</body>
</html>