<?php
require_once 'dbconnect.php';

session_start();

if (!isset($_SESSION['usersid'])) {
    header("Location: Login/login.php");
    exit();
}

$userid = $_SESSION['usersid'];

// MoMo Payment Gateway Configuration
$accessKey = 'F8BBA842ECF85';
$secretKey = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';
$partnerCode = 'MOMO';
$redirectUrl = 'http://localhost:8000/payment_return.php'; // Replace with your return URL
$ipnUrl = 'http://localhost:8000/ipn2.php'; // Replace with your IPN URL
$requestType = 'payWithMethod';
$orderInfo = 'Pay with MoMo';
$endpoint = 'https://test-payment.momo.vn/v2/gateway/api/create';
$lang = 'vi';
$autoCapture = true;
$extraData = '';
$orderGroupId = '';
$payType = 'momo_wallet'; // Set to match orderType

// Lấy cityid và tourid từ URL nếu có
$cityid = isset($_GET['cityid']) ? intval($_GET['cityid']) : 0;
$tourid = isset($_GET['tourid']) ? intval($_GET['tourid']) : 0;

// Lấy thông tin người dùng từ database
$user_name = "";
$user_email = "";

$stmt = $conn->prepare("SELECT usersuid, usersEmail FROM login WHERE usersid = ?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $user_name = $row['usersuid'];
    $user_email = $row['usersEmail'];
}

// Truy vấn thông tin city và tour
$city_name = "";
$tour_name = "";
$price_per_person = 0;

if ($cityid > 0) {
    $stmt = $conn->prepare("SELECT city FROM cities WHERE cityid = ?");
    $stmt->bind_param("i", $cityid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $city_name = $row['city'];
    }
}

if ($tourid > 0) {
    $stmt = $conn->prepare("SELECT tour_name, price_per_person FROM tours WHERE tourid = ?");
    $stmt->bind_param("i", $tourid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $tour_name = $row['tour_name'];
        $price_per_person = $row['price_per_person'];
    }
}

// Xử lý đặt tour và chuyển hướng đến cổng thanh toán
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['usersid'])) {
        die("⚠️ Vui lòng đăng nhập trước khi đặt tour.");
    }

    $userid = $_SESSION['usersid'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $cityid = $_POST['cityid'];
    $city_name = $_POST['city_name'];
    $tourid = $_POST['tourid'];
    $tour_name = $_POST['tour_name'];
    $tourists = intval($_POST['tourists']);
    $tour_date = $_POST['tour_date'];
    $contact = $_POST['contact'];
    $price_per_person = intval($_POST['price_per_person']);
    $total_amount = $price_per_person * $tourists;

    // Tạo orderId và requestId
    $orderId = $partnerCode . time();
    $requestId = $orderId;

    // Lưu thông tin đặt tour tạm thời (trạng thái pending)
    $stmt = $conn->prepare("INSERT INTO tour_bookings 
        (userid, name, email, cityid, city_name, tourid, tour_name, tourists, tour_date, contact, price_per_person, total_amount, order_id, payment_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ississsississ", $userid, $name, $email, $cityid, $city_name, $tourid, $tour_name, $tourists, $tour_date, $contact, $price_per_person, $total_amount, $orderId);

    if ($stmt->execute()) {
        // Tạo chữ ký (signature) cho MoMo
        $rawSignature = "accessKey=$accessKey&amount=$total_amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
        $signature = hash_hmac('sha256', $rawSignature, $secretKey);

        // Tạo request body
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

        // Gửi yêu cầu đến MoMo
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

        // Log response for debugging
        file_put_contents('momo_log.txt', "Request: $requestBody\nResponse: $response\nHTTP Code: $httpCode\nCurl Error: $curlError\n", FILE_APPEND);

        if ($httpCode == 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['resultCode']) && $responseData['resultCode'] == 0) {
                // Chuyển hướng đến URL thanh toán của MoMo
                header("Location: " . $responseData['payUrl']);
                exit();
            } else {
                // Hiển thị lỗi cụ thể từ MoMo
                $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'Unknown error';
                $resultCode = isset($responseData['resultCode']) ? $responseData['resultCode'] : 'N/A';
                echo "<div class='container'>";
                echo "<h2>❌ Lỗi khi tạo thanh toán</h2>";
                echo "<p>Mã lỗi: " . htmlspecialchars($resultCode) . "</p>";
                echo "<p>Thông báo: " . htmlspecialchars($errorMessage) . "</p>";
                if ($resultCode == '1002') {
                    echo "<p>Vui lòng kiểm tra phương thức thanh toán hoặc sử dụng phương thức khác (ví MoMo, thẻ ATM, thẻ tín dụng).</p>";
                }
                echo "<a href='./bookingtour.php?cityid=$cityid&tourid=$tourid' style='
                    display: inline-block;
                    margin-top: 10px;
                    padding: 10px 20px;
                    background-color: #f44336;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;'>🔄 Thử lại</a>";
                echo "<a href='./Login/loggedinhome.php' style='
                    display: inline-block;
                    margin-top: 10px;
                    padding: 10px 20px;
                    background-color: #4CAF50;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px; margin-left: 10px;'>🏠 Về trang chủ</a>";
                echo "</div>";
            }
        } else {
            echo "<div class='container'>❌ Lỗi kết nối đến cổng thanh toán: HTTP $httpCode</div>";
        }
    } else {
        echo "<div class='container'>❌ Lỗi khi đặt tour: " . $stmt->error . "</div>";
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Reserve: <?php echo htmlspecialchars($tour_name); ?></title>
    <link rel="stylesheet" href="./css/booktour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-text">
                <h2>Tour: <?php echo htmlspecialchars($tour_name); ?></h2>
            </div>
            <?php if ($tourid > 0): ?>
                <img src="tourphotoID/<?php echo $tourid; ?>.jpg" alt="Tour Image" class="tour-image">
            <?php endif; ?>
        </div>
        <form method="POST">
            <input type="hidden" name="cityid" value="<?php echo htmlspecialchars($cityid); ?>">
            <input type="hidden" name="city_name" value="<?php echo htmlspecialchars($city_name); ?>">
            <input type="hidden" name="tourid" value="<?php echo htmlspecialchars($tourid); ?>">
            <input type="hidden" name="tour_name" value="<?php echo htmlspecialchars($tour_name); ?>">
            <input type="hidden" name="price_per_person" value="<?php echo htmlspecialchars($price_per_person); ?>">
            <input type="hidden" name="name" value="<?php echo htmlspecialchars($user_name); ?>">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">

            <div class="form-container">
                <div class="form-left">
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" value="<?php echo htmlspecialchars($user_name); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="tourists">Guests:</label>
                        <input type="number" id="tourists" name="tourists" value="1" min="1" required>
                    </div>
                </div>
                <div class="form-right">
                    <div class="form-group">
                        <label for="tour_date">Departure Date:</label>
                        <input type="date" id="tour_date" name="tour_date" value="<?php echo date('Y-m-d'); ?>" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact:</label>
                        <input type="text" id="contact" name="contact" required>
                    </div>
                </div>
            </div>
            <div class="button-container">
                <span id="total-amount">Total: <?php echo number_format($price_per_person); ?>đ</span>
                <button type="submit">Reserve</button>
            </div>
        </form>
    </div>
    <script>
        function calculateTotal() {
            const tourists = parseInt(document.getElementById('tourists').value) || 1;
            const pricePerPerson = <?php echo $price_per_person; ?>;
            const total = tourists * pricePerPerson;
            document.getElementById('total-amount').textContent = 
                `Total: ${total.toLocaleString('vi-VN')}đ`;
        }

        document.getElementById('tourists').addEventListener('change', calculateTotal);
        calculateTotal();
    </script>
</body>
</html>