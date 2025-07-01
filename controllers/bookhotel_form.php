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
    <title>Reserve at <?php echo htmlspecialchars($hotelName); ?></title>
    <link rel="stylesheet" href="/css/hotelbooking.css">
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="hotel-badge">
                    <i class="fas fa-hotel"></i>
                    <span>Hotel Booking</span>
                </div>
                <div class="header-content">
                    <div class="header-text">
                        <h1 class="hotel-title"><?php echo htmlspecialchars($hotelName); ?></h1>
                        <div class="hotel-info">
                            <div class="location-info">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($cityName); ?></span>
                            </div>
                            <div class="price-tag">
                                <i class="fas fa-tag"></i>
                                <div class="price-content">
                                    <span class="price"><?php echo number_format($costPerDay); ?>đ</span>
                                    <span class="per-night">per night</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="image-container">
                        <img src="/hotelphotoID/<?php echo $hotelId; ?>.jpg" alt="Hotel Image" class="hotel-image">
                        <div class="image-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Section -->
            <div class="booking-section">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-check"></i> Make Your Reservation</h2>
                    <p>Fill in the details below to complete your booking</p>
                </div>

                <form method="post" action="/hotel/booking/submit" class="booking-form">
                    <input type="hidden" name="hotel" value="<?php echo $hotelId; ?>">
                    <input type="hidden" name="city" value="<?php echo $cityId; ?>">
                    
                    <div class="form-grid">
                        <div class="form-column">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-user"></i>
                                    Full name:
                                </label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    Email:
                                </label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="tourists">
                                    <i class="fas fa-users"></i>
                                    Number of guests:
                                </label>
                                <div class="number-input-wrapper">
                                    <button type="button" class="number-btn" onclick="changeNumber('tourists', -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="tourists" name="tourists" value="1" min="1" required readonly>
                                    <button type="button" class="number-btn" onclick="changeNumber('tourists', 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="number_of_rooms">
                                    <i class="fas fa-bed"></i>
                                    Number of rooms:
                                </label>
                                <div class="number-input-wrapper">
                                    <button type="button" class="number-btn" onclick="changeNumber('number_of_rooms', -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="number_of_rooms" name="number_of_rooms" value="1" min="1" required readonly>
                                    <button type="button" class="number-btn" onclick="changeNumber('number_of_rooms', 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-column">
                            <div class="form-group">
                                <label for="check_in_date">
                                    <i class="fas fa-calendar-alt"></i>
                                    Check-in date:
                                </label>
                                <input type="date" id="check_in_date" name="check_in_date" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="check_out_date">
                                    <i class="fas fa-calendar-alt"></i>
                                    Check-out date:
                                </label>
                                <input type="date" id="check_out_date" name="check_out_date" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact">
                                    <i class="fas fa-phone"></i>
                                    Contact number:
                                </label>
                                <input type="text" id="contact" name="contact" placeholder="Enter your phone number" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="room_type">
                                    <i class="fas fa-star"></i>
                                    Room type:
                                </label>
                                <select id="room_type" name="room_type">
                                    <option value="Standard">Standard Room - <?php echo number_format($costPerDay); ?>đ</option>
                                    <option value="Deluxe">Deluxe Room - <?php echo number_format($costPerDay + 200000); ?>đ</option>
                                    <option value="Suite">Suite Room - <?php echo number_format($costPerDay + 500000); ?>đ</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Total Section -->
                    <div class="total-section">
                        <div class="total-breakdown">
                            <div class="breakdown-item guests-count">
                                <span>Guests: </span>
                                <span id="guests-display">1 person</span>
                            </div>
                            <div class="breakdown-item">
                                <span>Rooms: </span>
                                <span id="rooms-display">1 room</span>
                            </div>
                            <div class="breakdown-item">
                                <span>Nights: </span>
                                <span id="nights-display">1 night</span>
                            </div>
                            <div class="breakdown-item">
                                <span>Room type: </span>
                                <span id="room-type-display">Standard</span>
                            </div>
                        </div>
                        <div class="total-amount">
                            <span>Total Amount:</span>
                            <span id="total-amount">0đ</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="reserve-btn">
                            <span class="btn-animation"></span>
                            <i class="fas fa-check-circle"></i>
                            Complete Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Initialize page animations
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.container').classList.add('loaded');
    });

    // Number input controls
    function changeNumber(inputId, change) {
        const input = document.getElementById(inputId);
        const currentValue = parseInt(input.value) || 1;
        const newValue = Math.max(1, currentValue + change);
        input.value = newValue;
        updateDisplays();
        calculateTotal();
    }

    // Update display values
    function updateDisplays() {
        const guests = document.getElementById('tourists').value;
        const rooms = document.getElementById('number_of_rooms').value;
        
        document.getElementById('guests-display').textContent = guests + (guests == 1 ? ' person' : ' people');
        document.getElementById('rooms-display').textContent = rooms + (rooms == 1 ? ' room' : ' rooms');
    }

    function calculateTotal() {
        const roomType = document.getElementById('room_type').value;
        const checkInDate = new Date(document.getElementById('check_in_date').value);
        const checkOutDate = new Date(document.getElementById('check_out_date').value);
        const numberOfRooms = parseInt(document.getElementById('number_of_rooms').value) || 1;
        const baseCost = <?php echo $costPerDay; ?>;
        
        const roomExtraCosts = {
            'Standard': 0,
            'Deluxe': 200000,
            'Suite': 500000
        };
        
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
        document.getElementById('nights-display').textContent = days + (days == 1 ? ' night' : ' nights');
        document.getElementById('room-type-display').textContent = roomType;
        document.getElementById('total-amount').textContent = 
            total.toLocaleString('vi-VN') + 'đ';
    }

    // Event listeners
    document.getElementById('room_type').addEventListener('change', calculateTotal);
    document.getElementById('check_in_date').addEventListener('change', calculateTotal);
    document.getElementById('check_out_date').addEventListener('change', calculateTotal);
    document.getElementById('number_of_rooms').addEventListener('change', calculateTotal);

    // Initialize calculations
    updateDisplays();
    calculateTotal();
    </script>
</body>
</html>