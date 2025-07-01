<?php
session_start();
require_once './dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['usersid'])) {
    header("Location: Login/login.php");
    exit();
}

function showBookingForm() {
    global $userid, $user_name, $user_email; // Make variables available inside the function

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
    // fallback từ CSDL nếu có usersid
    $userid = $_SESSION['usersid'];
    $stmt = $conn->prepare("SELECT usersuid, usersEmail FROM login WHERE usersid = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_name = $row['usersuid'];
        $user_email = $row['usersEmail'];
        // cập nhật lại session nếu muốn
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
    $tour_image = $tourid > 0 ? "../tourphotoID/{$tourid}.jpg" : '';

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Reserve: <?php echo htmlspecialchars($tour_name); ?></title>
    <link rel="stylesheet" href="../css/booktour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="tour-badge">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo htmlspecialchars($city_name); ?></span>
                </div>
                <div class="header-content">
                    <div class="header-text">
                        <h1 class="tour-title"><?php echo htmlspecialchars($tour_name); ?></h1>
                        <div class="tour-info">
                            <div class="price-tag">
                                <i class="fas fa-tag"></i>
                                <span class="price"><?php echo number_format($price_per_person, 0, ',', '.'); ?>đ</span>
                                <span class="per-person">per person</span>
                            </div>
                        </div>
                    </div>
                    <?php if ($tour_image): ?>
                        <div class="image-container">
                            <img src="<?php echo htmlspecialchars($tour_image); ?>" alt="Tour Image" class="tour-image">
                            <div class="image-overlay">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="booking-section">
                <div class="section-header">
                    <h2><i class="fas fa-ticket-alt"></i> Booking Information</h2>
                    <p>Complete your reservation details below</p>
                </div>
                
                <form action="/tour/booking/submit" method="POST" class="booking-form">
                    <input type="hidden" name="cityid" value="<?php echo htmlspecialchars($cityid); ?>">
                    <input type="hidden" name="city_name" value="<?php echo htmlspecialchars($city_name); ?>">
                    <input type="hidden" name="tourid" value="<?php echo htmlspecialchars($tourid); ?>">
                    <input type="hidden" name="tour_name" value="<?php echo htmlspecialchars($tour_name); ?>">
                    <input type="hidden" name="price_per_person" value="<?php echo htmlspecialchars($price_per_person); ?>">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($user_name); ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($user_email); ?>">

                    <div class="form-grid">
                        <div class="form-column">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-user"></i>
                                    Full Name
                                </label>
                                <input type="text" id="name" value="<?php echo htmlspecialchars($user_name); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact">
                                    <i class="fas fa-phone"></i>
                                    Contact Number
                                </label>
                                <input type="text" id="contact" name="contact" placeholder="Enter your phone number" required>
                            </div>
                        </div>
                        
                        <div class="form-column">
                            <div class="form-group">
                                <label for="tour_date">
                                    <i class="fas fa-calendar-alt"></i>
                                    Departure Date
                                </label>
                                <input type="date" id="tour_date" name="tour_date" value="<?php echo date('Y-m-d'); ?>" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="tourists">
                                    <i class="fas fa-users"></i>
                                    Number of Guests
                                </label>
                                <div class="number-input-wrapper">
                                    <button type="button" class="number-btn minus" onclick="changeGuests(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="tourists" name="tourists" value="1" min="1" max="20" required readonly>
                                    <button type="button" class="number-btn plus" onclick="changeGuests(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="total-section">
                                <div class="total-breakdown">
                                    <div class="breakdown-item">
                                        <span>Price per person:</span>
                                        <span><?php echo number_format($price_per_person, 0, ',', '.'); ?>đ</span>
                                    </div>
                                    <div class="breakdown-item guests-count">
                                        <span>Guests: <span id="guest-display">1</span></span>
                                        <span id="subtotal"><?php echo number_format($price_per_person, 0, ',', '.'); ?>đ</span>
                                    </div>
                                </div>
                                <div class="total-amount">
                                    <span>Total Amount:</span>
                                    <span id="total-amount"><?php echo number_format($price_per_person, 0, ',', '.'); ?>đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="reserve-btn">
                            <i class="fas fa-check-circle"></i>
                            Reserve Now
                            <div class="btn-animation"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const pricePerPerson = <?php echo $price_per_person; ?>;
        
        function changeGuests(change) {
            const input = document.getElementById('tourists');
            let current = parseInt(input.value);
            let newValue = current + change;
            
            if (newValue >= 1 && newValue <= 20) {
                input.value = newValue;
                calculateTotal();
            }
        }
        
        function calculateTotal() {
            const tourists = parseInt(document.getElementById('tourists').value) || 1;
            const total = tourists * pricePerPerson;
            
            document.getElementById('guest-display').textContent = tourists;
            document.getElementById('subtotal').textContent = total.toLocaleString('vi-VN') + 'đ';
            document.getElementById('total-amount').textContent = total.toLocaleString('vi-VN') + 'đ';
        }

        // Initialize
        calculateTotal();
        
        // Add input validation
        document.getElementById('contact').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\-\s]/g, '');
        });
        
        // Form animation on load
        window.addEventListener('load', function() {
            document.querySelector('.container').classList.add('loaded');
        });
    </script>
</body>
</html>
<?php
}

showBookingForm();
?>