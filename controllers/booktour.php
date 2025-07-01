<?php
require_once './dbconnect.php';
$db = Flight::db();
if (!class_exists('Flight')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid access. Please use the correct route.'], 403);
    exit;
}

function handleTourBooking() {
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();

    if (!isset($_SESSION['usersid'])) {
        header('Content-Type: text/html');
        echo "<h2>Error</h2><p>Please log in to book a tour.</p><a href='/login'>Log In</a>";
        exit;
    }

    $userid = $_SESSION['usersid'];

    // MoMo Configuration
    $accessKey = 'F8BBA842ECF85';
    $secretKey = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';
    $partnerCode = 'MOMO';
    $redirectUrl = 'http://localhost:8000/payment/tour/redirect';
    $ipnUrl = 'http://localhost:8000/payment/tour/ipn';
    $requestType = 'payWithMethod';
    $orderInfo = 'Pay with MoMo';
    $endpoint = 'https://test-payment.momo.vn/v2/gateway/api/create';
    $lang = 'vi';
    $autoCapture = true;
    $extraData = '';
    $orderGroupId = '';
    $payType = 'momo_wallet';

    $conn = Flight::db();

    // Get user info
    $stmt = $conn->prepare("SELECT usersuid, usersEmail FROM login WHERE usersid = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!($row = $result->fetch_assoc())) {
        header('Content-Type: text/html');
        echo "<h2>Error</h2><p>User not found.</p>";
        exit;
    }
    $user_name = $row['usersuid'];
    $user_email = $row['usersEmail'];

    // Get POST data
    $data = Flight::request()->data;
    $cityid = intval($data->cityid ?? 0);
    $tourid = intval($data->tourid ?? 0);
    $tourists = intval($data->tourists ?? 0);
    $tour_date = $data->tour_date ?? '';
    $contact = htmlspecialchars($data->contact ?? '');
    $name = htmlspecialchars($data->name ?? $user_name);
    $email = filter_var($data->email ?? $user_email, FILTER_SANITIZE_EMAIL);

    if ($cityid <= 0 || $tourid <= 0 || $tourists <= 0 || empty($tour_date) || empty($contact)) {
        header('Content-Type: text/html');
        echo "<h2>Error</h2><p>Invalid input data. Please fill out all required fields.</p><a href='/tour/booking?cityid=$cityid&tourid=$tourid'>Try Again</a>";
        exit;
    }

    // Get city and tour info
    $stmt = $conn->prepare("SELECT city FROM cities WHERE cityid = ?");
    $stmt->bind_param("i", $cityid);
    $stmt->execute();
    $result = $stmt->get_result();
    $city_name = ($row = $result->fetch_assoc()) ? $row['city'] : '';

    $stmt = $conn->prepare("SELECT tour_name, price_per_person FROM tours WHERE tourid = ?");
    $stmt->bind_param("i", $tourid);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!($row = $result->fetch_assoc())) {
        header('Content-Type: text/html');
        echo "<h2>Error</h2><p>Tour not found.</p>";
        exit;
    }
    $tour_name = $row['tour_name'];
    $price_per_person = $row['price_per_person'];

    $total_amount = $price_per_person * $tourists;
    $orderId = $partnerCode . time();
    $requestId = $orderId;

    //XHR Request
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://test-payment.momo.vn/v2/gateway/api/create',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode([
        'partnerCode' => $partnerCode,
        'partnerName' => 'Test',
        'storeId' => 'MomoTestStore',
        'requestId' => $requestId,
        'amount' => $total_amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'lang' => $lang,
        'requestType' => $requestType,
        'payType' => $payType,
        'autoCapture' => $autoCapture,
        'extraData' => $extraData,
        'orderGroupId' => $orderGroupId,
    ]),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    // Insert booking
    $stmt = $conn->prepare("INSERT INTO tour_bookings 
        (userid, name, email, cityid, city_name, tourid, tour_name, tourists, tour_date, contact, price_per_person, total_amount, order_id, payment_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ississsississ", $userid, $name, $email, $cityid, $city_name, $tourid, $tour_name, $tourists, $tour_date, $contact, $price_per_person, $total_amount, $orderId);

    if (!$stmt->execute()) {
        header('Content-Type: text/html');
        echo "<h2>Error</h2><p>Failed to book tour: " . htmlspecialchars($stmt->error) . "</p>";
        exit;
    }

    // Create MoMo signature
    $rawSignature = "accessKey=$accessKey&amount=$total_amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
    $signature = hash_hmac('sha256', $rawSignature, $secretKey);

    $requestBody = json_encode([
        'partnerCode' => $partnerCode,
        'partnerName' => 'Test',
        'storeId' => 'MomoTestStore',
        'requestId' => $requestId,
        'amount' => $total_amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'lang' => $lang,
        'requestType' => $requestType,
        'payType' => $payType,
        'autoCapture' => $autoCapture,
        'extraData' => $extraData,
        'orderGroupId' => $orderGroupId,
        'signature' => $signature
    ]);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($requestBody)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    file_put_contents('momo_log.txt', "Request: $requestBody\nResponse: $response\nHTTP Code: $httpCode\nCurl Error: $curlError\n", FILE_APPEND);

    if ($httpCode == 200) {
        $responseData = json_decode($response, true);
        if (($responseData['resultCode'] ?? -1) == 0) {
            header('Location: ' . $responseData['payUrl']);
            exit;
        } else {
            header('Content-Type: text/html');
            echo "<h2>Payment Error</h2><p>" . htmlspecialchars($responseData['message'] ?? 'Unknown error') . "</p>";
            exit;
        }
    } else {
        header('Content-Type: text/html');
        echo "<h2>Payment Gateway Error</h2><p>HTTP $httpCode: Connection error</p>";
        exit;
    }

}

handleTourBooking();