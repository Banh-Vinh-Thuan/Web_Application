<?php
session_start();
require_once '../dbconnect.php';

if (!isset($_SESSION["usersid"])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION["usersid"];

// Get user info
$userStmt = mysqli_prepare($conn, "SELECT usersId, usersEmail, usersUid FROM login WHERE usersId = ?");
mysqli_stmt_bind_param($userStmt, "i", $userid);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);

// Get favorite cities
$favStmt = mysqli_prepare($conn, "SELECT cities.cityid, cities.city FROM favorites JOIN cities ON favorites.cityid = cities.cityid WHERE favorites.usersid = ?");
mysqli_stmt_bind_param($favStmt, "i", $userid);
mysqli_stmt_execute($favStmt);
$favResult = mysqli_stmt_get_result($favStmt);
$favoriteCities = mysqli_fetch_all($favResult, MYSQLI_ASSOC);

// Get hotel bookings
$hotelStmt = mysqli_prepare($conn, "SELECT booking_id, hotel_name, city_name, tourists, number_of_rooms, room_type, total_amount, booking_date, check_in_date, check_out_date, payment_status FROM hotel_bookings WHERE userid = ?");
mysqli_stmt_bind_param($hotelStmt, "i", $userid);
mysqli_stmt_execute($hotelStmt);
$hotelResult = mysqli_stmt_get_result($hotelStmt);
$hotelReceipts = mysqli_fetch_all($hotelResult, MYSQLI_ASSOC);

// Get tour bookings
$tourStmt = mysqli_prepare($conn, "SELECT booking_id, name, email, city_name, tour_name, tourists, tour_date, contact, price_per_person, total_amount, booking_date, payment_status FROM tour_bookings WHERE userid = ?");
mysqli_stmt_bind_param($tourStmt, "i", $userid);
mysqli_stmt_execute($tourStmt);
$tourResult = mysqli_stmt_get_result($tourStmt);
$tourBookings = mysqli_fetch_all($tourResult, MYSQLI_ASSOC);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VietTransit - User Profile</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>
    <?php include __DIR__ . '/../header.php'; ?>

    <!-- Profile Header Section -->
    <div class="profile-header">
        <div class="cover-photo" style="background-image: url('../images/Cover_pic.png');"></div>
        <div class="profile-info-container">
            <div class="avatar-container">
                <img src="../images/avatar.jpg" alt="User Avatar" class="avatar">
                <div class="avatar-overlay"><i class="fas fa-camera"></i></div>
            </div>
            <div class="user-info">
                <?php
                if ($row = mysqli_fetch_assoc($userResult)) {
                    echo "<h2>" . htmlspecialchars($row["usersUid"]) . "</h2>";
                    echo "<p><i class='fas fa-envelope'></i> " . htmlspecialchars($row["usersEmail"]) . "</p>";
                } else {
                    echo "<h2>User</h2><p><i class='fas fa-envelope'></i> Not found</p>";
                }
                ?>
                <div class="user-stats">
                    <span><i class="fas fa-award"></i> 2840 PTS</span>
                    <span><i class="fas fa-map-marker-alt"></i> Vietnam</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="profile-tabs">
        <button class="tab-btn active" data-tab="favorite-cities">Favorite Cities</button>
        <button class="tab-btn" data-tab="view-tours">View Tours</button>
        <button class="tab-btn" data-tab="view-hotels">View Hotels</button>
    </div>

    <!-- Tab Contents -->
    <div class="tab-contents">
        <!-- Favorite Cities Tab -->
        <div id="favorite-cities" class="tab-content active">
            <div class="info-box">
                <h3>Favorite Cities</h3>
                <?php if (!empty($favoriteCities)): ?>
                    <div class='city-gallery'>
                        <?php
                        $citySlugMap = [
                            10 => 'taybac', 
                            11 => 'hcm', 
                            12 => 'nhatrang', 
                            13 => 'hue', 
                            14 => 'phuyen', 
                            15 => 'dalat', 
                            16 => 'phuquoc', 
                            17 => 'hoian', 
                            18 => 'hagiang', 
                            19 => 'danang', 
                            20 => 'cantho', 
                            21 => 'hanoi'
                        ];
                        foreach ($favoriteCities as $favRow):
                            $cityName = htmlspecialchars($favRow['city']);
                            $cityId = intval($favRow['cityid']);
                            $imagePath = "/Places/{$cityId}.jpg";
                            $citySlug = $citySlugMap[$cityId] ?? 'default';
                        ?> 
                        <div class='city-item'>
                            <img src='<?= $imagePath ?>' alt='<?= $cityName ?>'>
                            <p><?= $cityName ?></p>
                            <div class='city-actions'>
                                <a href='../hotelinfo/view_hotels.php?city_id=<?= $cityId ?>' class='btn-book'>Book Hotel</a>
                                <a href='../Journey/viewjourney.php?id=<?= $citySlug ?>' class='btn-book'>Book Tour</a>
                                <form action='remove_favorite.php' method='post'>
                                    <input type='hidden' name='cityid' value='<?= $cityId ?>'>
                                    <button type='submit' class='btn-remove'>Remove</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>You haven't favorited any cities.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- View Tours Tab -->
        <div id="view-tours" class="tab-content">
            <div class="info-box">
                <h3>Your Booked Tours</h3>
                <div class="table-responsive">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>City</th>
                                <th>Tour Name</th>
                                <th>Tourists</th>
                                <th>Tour Date</th>
                                <th>Contact</th>
                                <th>Price/Person</th>
                                <th>Total Amount</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tourBookings)): ?>
                                <?php foreach ($tourBookings as $booking): ?>
                                    <tr>
                                        <td data-label="ID"><span class="booking-id">#<?= htmlspecialchars($booking['booking_id'] ?? '') ?></span></td>
                                        <td data-label="City"><?= htmlspecialchars($booking['city_name'] ?? '') ?></td>
                                        <td data-label="Tour Name"><strong><?= htmlspecialchars($booking['tour_name'] ?? '') ?></strong></td>
                                        <td data-label="Tourists"><span class="badge badge-info"><?= htmlspecialchars($booking['tourists'] ?? '1') ?> <i class="fas fa-user"></i></span></td>
                                        <td data-label="Tour Date"><i class="far fa-calendar"></i> <?= htmlspecialchars($booking['tour_date'] ?? '') ?></td>
                                        <td data-label="Contact"><i class="fas fa-phone"></i> <?= htmlspecialchars($booking['contact'] ?? '') ?></td>
                                        <td data-label="Price/Person"><span class="price"><?= number_format($booking['price_per_person'] ?? 0) ?> VND</span></td>
                                        <td data-label="Total Amount"><span class="total-price"><?= number_format($booking['total_amount'] ?? 0) ?> VND</span></td>
                                        <td data-label="Booking Date"><?= htmlspecialchars($booking['booking_date'] ?? '') ?></td>
                                        <td data-label="Status"><span class="status-badge status-<?= strtolower($booking['payment_status'] ?? 'pending') ?>"><?= htmlspecialchars($booking['payment_status'] ?? '') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="10" class="empty-message">No bookings available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- View Hotels Tab -->
        <div id="view-hotels" class="tab-content">
            <div class="info-box">
                <h3>Your Booked Hotels</h3>
                <div class="table-responsive">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hotel Name</th>
                                <th>City</th>
                                <th>Tourists</th>
                                <th>Rooms</th>
                                <th>Room Type</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Total Amount</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($hotelReceipts)): ?>
                                <?php foreach ($hotelReceipts as $receipt): ?>
                                    <tr>
                                        <td data-label="ID"><span class="booking-id">#<?= htmlspecialchars($receipt['booking_id']) ?></span></td>
                                        <td data-label="Hotel Name"><strong><?= htmlspecialchars($receipt['hotel_name']) ?></strong></td>
                                        <td data-label="City"><?= htmlspecialchars($receipt['city_name']) ?></td>
                                        <td data-label="Tourists"><span class="badge badge-info"><?= htmlspecialchars($receipt['tourists']) ?> <i class="fas fa-user"></i></span></td>
                                        <td data-label="Rooms"><span class="badge badge-secondary"><?= htmlspecialchars($receipt['number_of_rooms']) ?> <i class="fas fa-bed"></i></span></td>
                                        <td data-label="Room Type"><?= htmlspecialchars($receipt['room_type']) ?></td>
                                        <td data-label="Check-In"><i class="far fa-calendar-check"></i> <?= htmlspecialchars($receipt['check_in_date']) ?></td>
                                        <td data-label="Check-Out"><i class="far fa-calendar-times"></i> <?= htmlspecialchars($receipt['check_out_date']) ?></td>
                                        <td data-label="Total Amount"><span class="total-price"><?= number_format($receipt['total_amount']) ?> VND</span></td>
                                        <td data-label="Booking Date"><?= htmlspecialchars($receipt['booking_date']) ?></td>
                                        <td data-label="Status"><span class="status-badge status-<?= strtolower($receipt['payment_status']) ?>"><?= htmlspecialchars($receipt['payment_status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="11" class="empty-message">No receipts recorded.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    this.classList.add('active');
                    document.getElementById(this.getAttribute('data-tab')).classList.add('active');
                });
            });
            
            document.querySelector('.avatar-container').addEventListener('click', function() {
                alert('Avatar upload functionality will be implemented here.');
            });
        });
    </script>
</body>
</html>