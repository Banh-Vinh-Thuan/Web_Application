<?php
session_start();
require_once './dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['usersid'])) {
    header("Location: /login");
    exit();
}

// Initialize database connection
$conn = Flight::db();
if (!$conn) {
    die("Database connection failed");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_SESSION["usersid"];
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $tourists = (int) ($_POST['tourists'] ?? 1);
    $number_of_rooms = (int) ($_POST['number_of_rooms'] ?? 1);
    $check_in_date = $_POST['check_in_date'] ?? date('Y-m-d');
    $check_out_date = $_POST['check_out_date'] ?? date('Y-m-d');
    $room_type = $_POST['room_type'] ?? 'Standard';
    $contact = $_POST['contact'] ?? '';
    $hotelId = (int) ($_POST['hotel'] ?? 0);
    $cityId = (int) ($_POST['city'] ?? 0);

    // Validate dates
    $today = date('Y-m-d');
    if ($check_in_date < $today || $check_out_date < $today) {
        die("Ngày nhận/trả phòng không được nhỏ hơn hôm nay.");
    }

    $hotelName = 'Unknown Hotel';
    $cityName = 'Unknown City';
    $costPerDay = 0;
    $totalAmount = 0;

    // Get hotel info
    if ($hotelId > 0) {
        $stmt = $conn->prepare("SELECT hotel, cost FROM hotels WHERE hotelid = ?");
        $stmt->bind_param("i", $hotelId);
        $stmt->execute();
        $stmt->bind_result($hotelNameDB, $baseCost);
        if ($stmt->fetch()) {
            $hotelName = $hotelNameDB;
            $baseCost = (int) $baseCost;
        }
        $stmt->close();
    }

    // Get city name
    if ($cityId > 0) {
        $stmt = $conn->prepare("SELECT city FROM cities WHERE cityid = ?");
        $stmt->bind_param("i", $cityId);
        $stmt->execute();
        $stmt->bind_result($cityNameDB);
        if ($stmt->fetch()) {
            $cityName = $cityNameDB;
        }
        $stmt->close();
    }

    // Room extra costs
    $room_extra_cost = [
        "Standard" => 0,
        "Deluxe" => 200000,
        "Suite" => 500000
    ];
    if (!array_key_exists($room_type, $room_extra_cost)) {
        die("Loại phòng không hợp lệ.");
    }

    $costPerDay = $baseCost + $room_extra_cost[$room_type];
    $days = (strtotime($check_out_date) - strtotime($check_in_date)) / (60 * 60 * 24);
    if ($days <= 0) {
        die("Ngày trả phòng phải sau ngày nhận phòng.");
    }

    $totalAmount = $costPerDay * $days * $number_of_rooms;

    // Insert booking first to get bookingId
    $orderId = 'TEMP_' . time(); // Temporary orderId
    $stmt = $conn->prepare("INSERT INTO hotel_bookings (
        userid, name, email, cityid, city_name, hotelid, hotel_name, tourists,
        check_in_date, check_out_date, room_type, contact, cost_per_day, total_amount, number_of_rooms, order_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ississsissssddis",
        $userid,
        $name,
        $email,
        $cityId,
        $cityName,
        $hotelId,
        $hotelName,
        $tourists,
        $check_in_date,
        $check_out_date,
        $room_type,
        $contact,
        $costPerDay,
        $totalAmount,
        $number_of_rooms,
        $orderId
    );

    if ($stmt->execute()) {
        $bookingId = $conn->insert_id;

        // MoMo Payment
        $accessKey = 'F8BBA842ECF85';
        $secretKey = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';
        $orderInfo = 'Thanh toan dat phong tai ' . preg_replace('/[^A-Za-z0-9 ]/', '', $hotelName);
        $partnerCode = 'MOMO';
        $redirectUrl = 'http://localhost:8000/payment/hotel/redirect';
        $ipnUrl = 'http://localhost:8000/payment/hotel/ipn';
        $requestType = 'payWithMethod';
        $amount = (string) $totalAmount;
        $orderId = $partnerCode . time() . '_' . $bookingId; // Use bookingId in orderId
        $requestId = $orderId;
        $extraData = '';
        $autoCapture = true;
        $lang = 'vi';

        $rawSignature = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
        $signature = hash_hmac('sha256', $rawSignature, $secretKey);

        file_put_contents('momo_log.txt', "Raw Signature: $rawSignature\nSignature: $signature\n", FILE_APPEND);

        $requestBody = json_encode([
            'partnerCode' => $partnerCode,
            'partnerName' => 'Test',
            'storeId' => 'MomoTestStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => $lang,
            'requestType' => $requestType,
            'autoCapture' => $autoCapture,
            'extraData' => $extraData,
            'signature' => $signature
        ], JSON_UNESCAPED_SLASHES);

        file_put_contents('momo_log.txt', "Request Body: $requestBody\n", FILE_APPEND);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://test-payment.momo.vn/v2/gateway/api/create');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($requestBody)
        ]);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('momo_verbose_log.txt', 'a');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            fclose($verbose);
            file_put_contents('momo_log.txt', "cURL Error: $error\n", FILE_APPEND);
            die("❌ Lỗi cURL: $error");
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($verbose);

        // Update order_id in database
        $stmt = $conn->prepare("UPDATE hotel_bookings SET order_id = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $orderId, $bookingId);
        $stmt->execute();
        $stmt->close();

        file_put_contents('momo_log.txt', "HTTP Code: $httpCode\nResponse: $response\n", FILE_APPEND);

        $responseData = json_decode($response, true);
        if ($httpCode == 200 && isset($responseData['payUrl']) && $responseData['resultCode'] == 0) {
            header('Location: ' . $responseData['payUrl']);
            exit();
        } else {
            $errorMessage = $responseData['message'] ?? 'Lỗi khi kết nối với MoMo';
            $resultCode = $responseData['resultCode'] ?? 'N/A';
            file_put_contents('momo_log.txt', "Error Message: $errorMessage\nResult Code: $resultCode\n", FILE_APPEND);
            echo "<p>❌ Lỗi thanh toán: $errorMessage (Result Code: $resultCode)</p>";
            echo "<a href='/loggedinhome' style='
                display: inline-block;
                margin-top: 10px;
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                text-decoration: none;
                border-radius: 5px;'>🏠 Về trang chủ</a>";
        }
    } else {
        echo "❌ Lỗi khi đặt tour: " . $stmt->error;
        $stmt->close();
    }

    $conn->close();
}
?>