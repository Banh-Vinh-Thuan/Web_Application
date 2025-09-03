<?php
session_start();
require_once './dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['usersid'])) {
    header("Location: Login/login.php");
    exit();
}

function generateImagePath($cityid, $tourid) {
    // Map numeric city ID to image folder pattern
    $imageMap = [
        11 => [5, 6, 7, 8],      // Ho Chi Minh City
        12 => [9, 10, 11, 12],   // Nha Trang  
        19 => [13, 14, 15, 16],  // Hue
        14 => [17, 18, 19, 20],  // Phu Yen
        15 => [21, 22, 23, 24],  // Dalat
        16 => [25, 26, 27, 28],  // Phu Quoc
        17 => [29, 30, 31, 32],  // Hoi An
        18 => [33, 34, 35, 36],  // Ha Giang
        10 => [1, 2, 3, 4],      // Northern Vietnam/Tay Bac
        13 => [1, 2, 3, 4],       // Central Vietnam (using same as Northern)
        19 => [37, 38, 39, 40],   // Da Nang
        20 => [41, 42, 43, 44],   // Can Tho
        21 => [45, 46, 47, 48]    // Ha Noi
    ];
    
    $imageIds = $imageMap[$cityid] ?? [1, 2, 3, 4];
    $imageId = $imageIds[($tourid - 1) % count($imageIds)];
    
    return "../tourphotoID/{$imageId}.jpg";
}

function showBookingForm() {
    global $userid, $user_name, $user_email;

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Validate input parameters
    $cityid = isset($_GET['cityid']) ? (int)$_GET['cityid'] : 0;
    $tourid = isset($_GET['tourid']) ? (int)$_GET['tourid'] : 0;

    if ($cityid <= 0 || $tourid <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid city or tour ID'], JSON_PRETTY_PRINT);
        http_response_code(400);
        exit;
    }

    // Database connection
    $conn = Flight::db();
    if (!$conn) {
        die("Database connection failed: " . $conn->error);
    }

    // Fetch tour and city information
    $stmt = $conn->prepare("
        SELECT t.tour_name, t.price_per_person, c.city 
        FROM tours t 
        JOIN cities c ON t.cityid = c.cityid 
        WHERE t.tourid = ? AND c.cityid = ?
    ");
    if ($stmt === false) {
        die("Error preparing tour query: " . $conn->error);
    }
    $stmt->bind_param("ii", $tourid, $cityid);
    if (!$stmt->execute()) {
        die("Error executing tour query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        echo "<h2>Tour not found or city mismatch.</h2>";
        exit;
    }

    $userid = (int)$_SESSION['usersid'];

    // Fetch username and email from session
    $user_name = $_SESSION['usersuid'] ?? '';
    $user_email = $_SESSION['usersEmail'] ?? '';

    if (empty($user_name) || empty($user_email)) {
        $userid = $_SESSION['usersid'];
        $stmt = $conn->prepare("SELECT usersuid, usersEmail FROM login WHERE usersid = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $user_name = $row['usersuid'];
            $user_email = $row['usersEmail'];
            $_SESSION['usersuid'] = $user_name;
            $_SESSION['usersEmail'] = $user_email;
        } else {
            die("User data could not be retrieved from database.");
        }
    }

    $tour_name = $data['tour_name'];
    $price_per_person = (float)$data['price_per_person'];
    $city_name = $data['city'];

    // Tour image path
    $tour_image = generateImagePath($cityid, $tourid);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Booking: <?php echo htmlspecialchars($tour_name); ?></title>
    <link rel="stylesheet" href="../css/booktour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../header.php'; ?>
    <div class="main-container">
        <!-- Left Column - Booking Information -->
        <div class="left-column">
            <div class="booking-info-header">
                <h2>BOOKING INFORMATION</h2>
            </div>
            
            <form action="/tour/booking/submit" method="POST" class="booking-form">
                <input type="hidden" name="cityid" value="<?php echo htmlspecialchars($cityid); ?>">
                <input type="hidden" name="city_name" value="<?php echo htmlspecialchars($city_name); ?>">
                <input type="hidden" name="tourid" value="<?php echo htmlspecialchars($tourid); ?>">
                <input type="hidden" name="tour_name" value="<?php echo htmlspecialchars($tour_name); ?>">
                <input type="hidden" name="price_per_person" value="<?php echo htmlspecialchars($price_per_person); ?>">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($user_name); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">

                <div class="form-group">
                    <label for="name">FULL NAME:</label>
                    <input type="text" id="name" value="<?php echo htmlspecialchars($user_name); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="email">EMAIL:</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="contact">CONTACT NUMBER:</label>
                    <input type="text" id="contact" name="contact" placeholder="Enter your phone number" required>
                </div>

                <div class="form-group">
                    <label for="tour_date">DEPARTURE DATE:</label>
                    <input type="date" id="tour_date" name="tour_date" value="<?php echo date('Y-m-d'); ?>" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="tourists">NUMBER OF GUESTS:</label>
                    <div class="quantity-control">
                        <button type="button" class="qty-btn minus" onclick="changeGuests(-1)">-</button>
                        <input type="number" id="tourists" name="tourists" value="1" min="1" max="20" required readonly>
                        <button type="button" class="qty-btn plus" onclick="changeGuests(1)">+</button>
                    </div>
                </div>

                <button type="submit" class="book-now-btn">RESERVE NOW</button>
            </form>
        </div>

        <!-- Right Column - Tour Information -->
        <div class="right-column">
            <div class="tour-info-header">
                <h2>TOUR INFORMATION</h2>
            </div>
            
            <div class="tour-content">
                <?php if ($tour_image): ?>
                    <div class="tour-image-container">
                        <img src="<?php echo htmlspecialchars($tour_image); ?>" alt="Tour Image" class="tour-image">
                    </div>
                <?php endif; ?>
                
                <div class="tour-details">
                    <h3 class="tour-title"><?php echo htmlspecialchars($tour_name); ?></h3>
                    <p class="tour-location"><?php echo htmlspecialchars($city_name); ?></p>
                    
                    <div class="tour-info-grid">
                        <div class="info-row">
                            <span class="label">TOUR CODE:</span>
                            <span class="value">TD<?php echo str_pad($tourid, 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">DEPARTURE DATE:</span>
                            <span class="value" id="display-date"><?php echo date('d/m/Y'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">DESTINATION:</span>
                            <span class="value"><?php echo strtoupper(htmlspecialchars($city_name)); ?></span>
                        </div>
                    </div>

                    <div class="pricing-section">
                        <div class="price-row">
                            <span class="price-label">Guest</span>
                            <div class="price-controls">
                                <span class="price-quantity" id="guest-qty">1</span>
                            </div>
                            <span class="price-total" id="guest-total"><?php echo number_format($price_per_person, 0, ',', '.'); ?> VND</span>
                        </div>

                        <div class="total-section">
                            <div class="final-total-row">
                                <span class="final-label">TOTAL AMOUNT</span>
                                <span class="final-amount" id="final-total"><?php echo number_format($price_per_person, 0, ',', '.'); ?> VND</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const pricePerPerson = <?php echo $price_per_person; ?>;
        
        function changeGuests(change) {
            const input = document.getElementById('tourists');
            const guestQty = document.getElementById('guest-qty');
            let current = parseInt(input.value);
            let newValue = current + change;
            
            if (newValue >= 1 && newValue <= 20) {
                input.value = newValue;
                guestQty.textContent = newValue;
                calculateTotal();
            }
        }
        
        function calculateTotal() {
            const tourists = parseInt(document.getElementById('tourists').value) || 1;
            const total = tourists * pricePerPerson;
            
            document.getElementById('guest-total').textContent = total.toLocaleString('vi-VN') + ' VND';
            document.getElementById('final-total').textContent = total.toLocaleString('vi-VN') + ' VND';
        }

        // Update display date when date input changes
        document.getElementById('tour_date').addEventListener('change', function() {
            const date = new Date(this.value);
            const displayDate = date.toLocaleDateString('vi-VN');
            document.getElementById('display-date').textContent = displayDate;
        });

        // Phone number validation
        document.getElementById('contact').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\-\s]/g, '');
        });
        
        // Initialize
        calculateTotal();
    </script>
</body>
</html>
<?php
}

showBookingForm();
?>