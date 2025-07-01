<?php
require_once 'dbconnect.php';

function handleHotelIPN() {
    $input = file_get_contents('php://input');
    file_put_contents('ipn_log.txt', "Input: $input\n", FILE_APPEND);
    $data = json_decode($input, true);

    $requiredFields = ['partnerCode', 'orderId', 'requestId', 'amount', 'resultCode', 'signature'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            file_put_contents('ipn_log.txt', "Error: Missing $field\n", FILE_APPEND);
            http_response_code(400);
            exit("Missing $field");
        }
    }

    $accessKey = 'F8BBA842ECF85';
    $secretKey = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';
    $rawSignature = "accessKey=$accessKey&amount={$data['amount']}&extraData={$data['extraData']}&message={$data['message']}&orderId={$data['orderId']}&orderInfo={$data['orderInfo']}&orderType={$data['orderType']}&partnerCode={$data['partnerCode']}&payType={$data['payType']}&requestId={$data['requestId']}&responseTime={$data['responseTime']}&resultCode={$data['resultCode']}&transId={$data['transId']}";
    $expectedSignature = hash_hmac('sha256', $rawSignature, $secretKey);

    file_put_contents('ipn_log.txt', "Raw Signature: $rawSignature\nExpected Signature: $expectedSignature\nReceived Signature: {$data['signature']}\n", FILE_APPEND);

    if ($expectedSignature !== $data['signature']) {
        file_put_contents('ipn_log.txt', "Error: Invalid signature\n", FILE_APPEND);
        http_response_code(401);
        exit("Invalid signature");
    }

    global $conn;
    $bookingId = explode('_', $data['orderId'])[1] ?? 0;
    $paymentStatus = $data['resultCode'] == 0 ? 'completed' : 'failed';

    $stmt = $conn->prepare("UPDATE hotel_bookings SET payment_status = ?, payment_response = ? WHERE id = ?");
    $paymentResponse = json_encode($data);
    $stmt->bind_param("ssi", $paymentStatus, $paymentResponse, $bookingId);
    $stmt->execute();
    $stmt->close();

    http_response_code(200);
    echo json_encode(['message' => 'Received']);
}
 handleHotelIPN();
?>