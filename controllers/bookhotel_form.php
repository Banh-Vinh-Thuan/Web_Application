<?php
session_start();
require_once './dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION["usersid"])) {
    header("Location: /login");
    exit();
}

// Initialize database connection using Flight
$conn = Flight::db();
if (!$conn) {
    die("Database connection failed");
}

// Get user info
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

// Validate hotel_id
if (!isset($_GET['hotel_id'])) {
    die("Thiếu hotel_id");
}

$hotelId = (int) $_GET['hotel_id'];
$hotelName = 'Unknown Hotel';
$cityId = 0;
$costPerDay = 0;

// Get hotel info
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

// Get city name
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
    <title>Hotel Booking: <?php echo htmlspecialchars($hotelName); ?></title>
    <link rel="stylesheet" href="../css/hotelbooking.css">
    <link rel="icon" type="image/png" href="/images/favicon.png">
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
            
            <form method="post" action="/hotel/booking/submit" class="booking-form">
                <input type="hidden" name="hotel" value="<?php echo $hotelId; ?>">
                <input type="hidden" name="city" value="<?php echo $cityId; ?>">
                
                <div class="form-group">
                    <label for="name">FULL NAME:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="email">EMAIL:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="contact">CONTACT NUMBER:</label>
                    <input type="text" id="contact" name="contact" placeholder="Enter your phone number" required>
                </div>
                
                <div class="form-group">
                    <label for="check_in_date">CHECK-IN DATE:</label>
                    <input type="date" id="check_in_date" name="check_in_date" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="check_out_date">CHECK-OUT DATE:</label>
                    <input type="date" id="check_out_date" name="check_out_date" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tourists">NUMBER OF GUESTS:</label>
                        <div class="quantity-control">
                            <button type="button" class="qty-btn minus" onclick="changeNumber('tourists', -1)">-</button>
                            <input type="number" id="tourists" name="tourists" value="1" min="1" required readonly>
                            <button type="button" class="qty-btn plus" onclick="changeNumber('tourists', 1)">+</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="number_of_rooms">NUMBER OF ROOMS:</label>
                        <div class="quantity-control">
                            <button type="button" class="qty-btn minus" onclick="changeNumber('number_of_rooms', -1)">-</button>
                            <input type="number" id="number_of_rooms" name="number_of_rooms" value="1" min="1" required readonly>
                            <button type="button" class="qty-btn plus" onclick="changeNumber('number_of_rooms', 1)">+</button>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="room_type">ROOM TYPE:</label>
                    <select id="room_type" name="room_type" class="room-type-select">
                        <option value="Standard">Standard Room - <?php echo number_format($costPerDay); ?>đ</option>
                        <option value="Deluxe">Deluxe Room - <?php echo number_format($costPerDay + 200000); ?>đ</option>
                        <option value="Suite">Suite Room - <?php echo number_format($costPerDay + 500000); ?>đ</option>
                    </select>
                </div>

                <button type="submit" class="book-now-btn">RESERVE NOW</button>
            </form>
        </div>

        <!-- Right Column - Hotel Information -->
        <div class="right-column">
            <div class="tour-info-header">
                <h2>HOTEL INFORMATION</h2>
            </div>
            
            <div class="tour-content">
                <div class="tour-image-container">
                    <img src="/hotelphotoID/<?php echo $hotelId; ?>.jpg" alt="Hotel Image" class="tour-image">
                </div>
                
                <div class="tour-details">
                    <h3 class="tour-title"><?php echo htmlspecialchars($hotelName); ?></h3>
                    <p class="tour-location"><?php echo htmlspecialchars($cityName); ?></p>
                    
                    <div class="tour-info-grid">
                        <div class="info-row">
                            <span class="label">HOTEL CODE:</span>
                            <span class="value">HT<?php echo str_pad($hotelId, 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">CHECK-IN:</span>
                            <span class="value" id="display-checkin"><?php echo date('d/m/Y'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">CHECK-OUT:</span>
                            <span class="value" id="display-checkout"><?php echo date('d/m/Y', strtotime('+1 day')); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">LOCATION:</span>
                            <span class="value"><?php echo strtoupper(htmlspecialchars($cityName)); ?></span>
                        </div>
                    </div>

                    <div class="pricing-section">
                        <div class="price-row">
                            <span class="price-label">Guests</span>
                            <div class="price-controls">
                                <span class="price-quantity" id="guest-qty">1</span>
                            </div>
                        </div>
                        
                        <div class="price-row">
                            <span class="price-label">Rooms</span>
                            <div class="price-controls">
                                <span class="price-quantity" id="room-qty">1</span>
                            </div>
                        </div>
                        
                        <div class="price-row">
                            <span class="price-label">Room Type</span>
                            <div class="price-controls">
                                <span class="price-quantity" id="room-type-qty">Standard</span>
                            </div>
                        </div>

                        <div class="total-section">
                            <div class="price-row total-amount-row">
                                <span class="price-label">TOTAL AMOUNT</span>
                                <div class="price-controls">
                                    <span class="price-quantity total-amount" id="total-amount"><?php echo number_format($costPerDay, 0, ',', '.'); ?> VND</span>
                                </div>
                            </div>
                            <div class="final-total-row">
                                <span class="final-label">FINAL TOTAL</span>
                                <span class="final-amount" id="final-total"><?php echo number_format($costPerDay, 0, ',', '.'); ?> VND</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const baseCost = <?php echo $costPerDay; ?>;
        
        const roomExtraCosts = {
            'Standard': 0,
            'Deluxe': 200000,
            'Suite': 500000
        };
        
        function changeNumber(inputId, change) {
            const input = document.getElementById(inputId);
            const currentValue = parseInt(input.value) || 1;
            const newValue = Math.max(1, currentValue + change);
            input.value = newValue;
            updateDisplays();
            calculateTotal();
        }

        function updateDisplays() {
            const guests = document.getElementById('tourists').value;
            const rooms = document.getElementById('number_of_rooms').value;
            
            document.getElementById('guest-qty').textContent = guests;
            document.getElementById('room-qty').textContent = rooms;
        }

        function calculateTotal() {
            const roomType = document.getElementById('room_type').value;
            const checkInDate = new Date(document.getElementById('check_in_date').value);
            const checkOutDate = new Date(document.getElementById('check_out_date').value);
            const numberOfRooms = parseInt(document.getElementById('number_of_rooms').value) || 1;
            
            const timeDiff = checkOutDate - checkInDate;
            const days = Math.max(1, timeDiff / (1000 * 60 * 60 * 24));
            const costPerDay = baseCost + roomExtraCosts[roomType];
            
            let subtotal = 0;
            let discount = 0;
            let total = 0;
            
            if (days > 0 && !isNaN(costPerDay) && numberOfRooms > 0) {
                subtotal = costPerDay * days * numberOfRooms;
                
                // Apply 7% discount for Standard rooms only
                if (roomType === 'Standard') {
                    discount = subtotal * 0.07;
                    total = subtotal - discount;
                } else {
                    total = subtotal;
                }
            }
            
            // Update displays
            document.getElementById('room-type-qty').textContent = roomType;
            document.getElementById('total-amount').textContent = 
                total.toLocaleString('vi-VN') + ' VND';
            document.getElementById('final-total').textContent = 
                total.toLocaleString('vi-VN') + ' VND';
        }
        
        // Update display dates when date inputs change
        document.getElementById('check_in_date').addEventListener('change', function() {
            const date = new Date(this.value);
            const displayDate = date.toLocaleDateString('vi-VN');
            document.getElementById('display-checkin').textContent = displayDate;
            calculateTotal();
        });
        
        document.getElementById('check_out_date').addEventListener('change', function() {
            const date = new Date(this.value);
            const displayDate = date.toLocaleDateString('vi-VN');
            document.getElementById('display-checkout').textContent = displayDate;
            calculateTotal();
        });

        // Event listeners
        document.getElementById('room_type').addEventListener('change', calculateTotal);
        document.getElementById('number_of_rooms').addEventListener('change', calculateTotal);
        
        // Phone number validation
        document.getElementById('contact').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\-\s]/g, '');
        });
        
        // Initialize
        updateDisplays();
        calculateTotal();
    </script>
</body>
</html>