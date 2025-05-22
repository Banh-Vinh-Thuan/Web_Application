<?php
session_start();
require_once 'dbconnect.php'; // file kết nối CSDL

// Kiểm tra login
if (!isset($_SESSION["usersid"])) {
    header("Location: Login/login.php");
    exit();
}

// Lấy thông tin người dùng
$userid = $_SESSION["usersid"];
$userEmail = "";
$userName = "";

$sql = "SELECT usersEmail, usersUid FROM login WHERE usersId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$stmt->bind_result($email, $username);
if ($stmt->fetch()) {
    $userEmail = $email;
    $userName = $username;
}
$stmt->close();

// Nếu là GET thì hiển thị form
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['hotel_id'])) {
        die("Thiếu hotel_id");
    }

    $hotelId = (int) $_GET['hotel_id'];
    $hotelName = 'Unknown Hotel';
    $cityId = 0;
    $costPerDay = 0;

    // Lấy thông tin khách sạn
    $stmt = $conn->prepare("SELECT hotel, cost, cityid FROM hotels WHERE hotelid = ?");
    $stmt->bind_param("i", $hotelId);
    $stmt->execute();
    $stmt->bind_result($hotelName, $baseCost, $cityId);
    if ($stmt->fetch()) {
        $costPerDay = $baseCost;
    } else {
        die("Không tìm thấy khách sạn.");
    }
    $stmt->close();

    // Lấy tên thành phố
    $stmt = $conn->prepare("SELECT city FROM cities WHERE cityid = ?");
    $stmt->bind_param("i", $cityId);
    $stmt->execute();
    $stmt->bind_result($cityName);
    $stmt->fetch();
    $stmt->close();
    ?>
    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reserve at <?php echo htmlspecialchars($hotelName); ?></title>
        <link rel="stylesheet" href="./css/hotelbooking.css">
        <link rel="icon" type="image/png" href="./images/favicon.png">
    </head>

    <body>
        <div class="container">
            <div class="header">
                <div class="header-text">
                    <h2>Reserve room: <?php echo htmlspecialchars($hotelName); ?></h2>
                </div>
                <img src="hotelphotoID/<?php echo $hotelId; ?>.jpg" alt="Hotel Image" class="hotel-image">
            </div>
            <form method="post">
                <input type="hidden" name="hotel" value="<?php echo $hotelId; ?>">
                <input type="hidden" name="city" value="<?php echo $cityId; ?>">
                <div class="form-container">
                    <div class="form-left">
                        <div class="form-group">
                            <label for="name">Full name:</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>"
                                readonly>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>"
                                readonly>
                        </div>
                        <div class="form-group">
                            <label for="tourists">People:</label>
                            <input type="number" id="tourists" name="tourists" value="1" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="number_of_rooms">Room:</label>
                            <input type="number" id="number_of_rooms" name="number_of_rooms" value="1" min="1" required>
                        </div>
                    </div>
                    <div class="form-right">
                        <div class="form-group">
                            <label for="check_in_date">Check in date:</label>
                            <input type="date" id="check_in_date" name="check_in_date" value="<?php echo date('Y-m-d'); ?>"
                                min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="check_out_date">Check out date:</label>
                            <input type="date" id="check_out_date" name="check_out_date"
                                value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" min="<?php echo date('Y-m-d'); ?>"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="contact">Contact:</label>
                            <input type="text" id="contact" name="contact" required>
                        </div>
                        <div class="form-group">
                            <label for="room_type">Room type:</label>
                            <select id="room_type" name="room_type">
                                <option value="Standard">Standard (<?php echo number_format($costPerDay); ?>đ)</option>
                                <option value="Deluxe">Deluxe (<?php echo number_format($costPerDay + 200000); ?>đ)</option>
                                <option value="Suite">Suite (<?php echo number_format($costPerDay + 500000); ?>đ)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="button-container">
                    <span id="total-amount">Total: 0đ</span>
                    <button type="submit">Reserve</button>
                    
                </div>
            </form>
        </div>
        <script>
            function calculateTotal() {
                const roomType = document.getElementById('room_type').value;
                const checkInDate = new Date(document.getElementById('check_in_date').value);
                const checkOutDate = new Date(document.getElementById('check_out_date').value);
                const numberOfRooms = parseInt(document.getElementById('number_of_rooms').value) || 1;

                // Base cost from PHP
                const baseCost = <?php echo $costPerDay; ?>;
                // Room type extra costs
                const roomExtraCosts = {
                    'Standard': 0,
                    'Deluxe': 200000,
                    'Suite': 500000
                };

                // Calculate days
                const timeDiff = checkOutDate - checkInDate;
                const days = timeDiff / (1000 * 60 * 60 * 24);
                
                // Calculate cost per day
                const costPerDay = baseCost + roomExtraCosts[roomType];
                
                // Calculate total
                let total = 0;
                if (days > 0 && !isNaN(costPerDay) && numberOfRooms > 0) {
                    total = costPerDay * days * numberOfRooms;
                }

                // Update total amount display
                document.getElementById('total-amount').textContent = 
                    `Total: ${total.toLocaleString('vi-VN')}đ`;
            }

            // Add event listeners to update total on input change
            document.getElementById('room_type').addEventListener('change', calculateTotal);
            document.getElementById('check_in_date').addEventListener('change', calculateTotal);
            document.getElementById('check_out_date').addEventListener('change', calculateTotal);
            document.getElementById('number_of_rooms').addEventListener('change', calculateTotal);

            // Initial calculation
            calculateTotal();
        </script>
    </body>

    </html>
    <?php
}

// POST handling remains unchanged
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nhận dữ liệu từ form
    $name = $_POST['name'] ?? $userName;
    $email = $_POST['email'] ?? $userEmail;
    $tourists = (int) ($_POST['tourists'] ?? 1);
    $number_of_rooms = (int) ($_POST['number_of_rooms'] ?? 1);
    $check_in_date = $_POST['check_in_date'] ?? date('Y-m-d');
    $check_out_date = $_POST['check_out_date'] ?? date('Y-m-d');
    $room_type = $_POST['room_type'] ?? 'Standard';
    $contact = $_POST['contact'] ?? '';
    $hotelId = (int) ($_POST['hotel'] ?? 0);
    $cityId = (int) ($_POST['city'] ?? 0);

    // Kiểm tra ngày
    $today = date('Y-m-d');
    if ($check_in_date < $today || $check_out_date < $today) {
        die("Ngày nhận/trả phòng không được nhỏ hơn hôm nay.");
    }

    $hotelName = 'Unknown Hotel';
    $cityName = 'Unknown City';
    $costPerDay = 0;
    $totalAmount = 0;

    // Lấy thông tin khách sạn
    if ($hotelId > 0) {
        $hotelQuery = "SELECT hotel, cost FROM hotels WHERE hotelid = ?";
        $stmt = $conn->prepare($hotelQuery);
        $stmt->bind_param("i", $hotelId);
        $stmt->execute();
        $stmt->bind_result($hotelNameDB, $baseCost);
        if ($stmt->fetch()) {
            $hotelName = $hotelNameDB;
            $baseCost = (int) $baseCost;
        }
        $stmt->close();
    }

    // Lấy tên thành phố
    if ($cityId > 0) {
        $cityQuery = "SELECT city FROM cities WHERE cityid = ?";
        $stmt = $conn->prepare($cityQuery);
        $stmt->bind_param("i", $cityId);
        $stmt->execute();
        $stmt->bind_result($cityNameDB);
        if ($stmt->fetch()) {
            $cityName = $cityNameDB;
        }
        $stmt->close();
    }

    // Phụ phí phòng
    $room_extra_cost = [
        "Standard" => 0,
        "Deluxe" => 200000,
        "Suite" => 500000
    ];
    if (!array_key_exists($room_type, $room_extra_cost)) {
        die("Loại phòng không hợp lệ.");
    }

    $costPerDay = $baseCost + $room_extra_cost[$room_type];

    // Tính số ngày ở
    $days = (strtotime($check_out_date) - strtotime($check_in_date)) / (60 * 60 * 24);
    if ($days <= 0) {
        die("Ngày trả phòng phải sau ngày nhận phòng.");
    }

    $totalAmount = $costPerDay * $days * $number_of_rooms;

    // INSERT dữ liệu vào DB
    $stmt = $conn->prepare("INSERT INTO hotel_bookings (
        userid, name, email,
        cityid, city_name,
        hotelid, hotel_name,
        tourists,
        check_in_date, check_out_date,
        room_type, contact,
        cost_per_day, total_amount, number_of_rooms
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ississsissssddi",
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
        $number_of_rooms
    );

    if ($stmt->execute()) {
        $bookingId = $conn->insert_id; // Get the ID of the inserted booking

        // MoMo Payment Integration
        $accessKey = 'F8BBA842ECF85'; // Replace with your MoMo test accessKey if different
        $secretKey = 'K951B6PE1waDMi640xX08PD3vg6EkVlz'; // Replace with your MoMo test secretKey if different
        $orderInfo = 'Thanh toan dat phong tai ' . preg_replace('/[^A-Za-z0-9 ]/', '', $hotelName);
        $partnerCode = 'MOMO';
        $redirectUrl = 'http://localhost:8000/redirect.php';
        $ipnUrl = 'http://localhost:8000/ipn.php';
        $requestType = 'payWithMethod';
        $amount = (string) $totalAmount; // Ensure amount is a string
        $orderId = $partnerCode . time() . '_' . $bookingId; // Unique order ID
        $requestId = $orderId;
        $extraData = '';
        $autoCapture = true;
        $lang = 'vi';

        // Create raw signature
        $rawSignature = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";

        // Generate HMAC SHA256 signature
        $signature = hash_hmac('sha256', $rawSignature, $secretKey);

        // Log signature details
        file_put_contents('momo_log.txt', "Raw Signature: $rawSignature\nSignature: $signature\n", FILE_APPEND);

        // Prepare request body
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

        // Log request body
        file_put_contents('momo_log.txt', "Request Body: $requestBody\n", FILE_APPEND);

        // Send request to MoMo
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://test-payment.momo.vn/v2/gateway/api/create');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($requestBody)
        ]);
        // Enable verbose output for debugging
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

        // Log response
        file_put_contents('momo_log.txt', "HTTP Code: $httpCode\nResponse: $response\n", FILE_APPEND);

        // Process MoMo response
        $responseData = json_decode($response, true);
        if ($httpCode == 200 && isset($responseData['payUrl']) && $responseData['resultCode'] == 0) {
            // Redirect to MoMo payment page
            header('Location: ' . $responseData['payUrl']);
            exit();
        } else {
            // Handle error
            $errorMessage = $responseData['message'] ?? 'Lỗi khi kết nối với MoMo';
            $resultCode = $responseData['resultCode'] ?? 'N/A';
            file_put_contents('momo_log.txt', "Error Message: $errorMessage\nResult Code: $resultCode\n", FILE_APPEND);
            echo "<p>❌ Lỗi thanh toán: $errorMessage (Result Code: $resultCode)</p>";
            echo "<a href='./Login/loggedinhome.php' style='
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
    }

    $stmt->close();
    $conn->close();
}