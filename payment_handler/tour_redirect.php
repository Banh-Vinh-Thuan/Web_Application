<?php
session_start();
require_once './dbconnect.php';

function handleTourRedirect() {
    if (!isset($_SESSION['usersid'])) {
        header("Location: Login/login.php");
        exit();
    }

    $resultCode = $_GET['resultCode'] ?? null;
    $orderId = $_GET['orderId'] ?? null;
    $message = $_GET['message'] ?? 'Unknown error';
    $amount = $_GET['amount'] ?? 0;

    file_put_contents('momo_return_log.txt', "Return Params: " . json_encode($_GET) . "\n", FILE_APPEND);

    if ($resultCode == '0') {
        global $conn;
        $stmt = $conn->prepare("UPDATE tour_bookings SET payment_status = 'completed' WHERE order_id = ?");
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $stmt->close();
        $message = "Thanh toán thành công cho đơn hàng $orderId!";
    } else {
        global $conn;
        $stmt = $conn->prepare("UPDATE tour_bookings SET payment_status = 'failed' WHERE order_id = ?");
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $stmt->close();
        $message = "Thanh toán thất bại: $message";
    }

    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kết quả thanh toán</title>
        <link rel="icon" type="image/png" href="/images/favicon.png">
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .success { color: green; }
            .error { color: red; }
            a { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h2>Kết quả thanh toán</h2>
        <p class="<?php echo $resultCode == '0' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
        <p>Số tiền: <?php echo number_format($amount); ?>đ</p>
        <a href="/Login/loggedinhome.php">🏠 Về trang chủ</a>
    </body>
    </html>
    <?php
}
handleTourRedirect();
?>
