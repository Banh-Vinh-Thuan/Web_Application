<?php
session_start();
require_once '../dbconnect.php';

if (!isset($_SESSION["usersid"])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION["usersid"];

// Xử lý xóa hotel booking
if (isset($_GET['delete_hotel']) && is_numeric($_GET['delete_hotel'])) {
    $deleteId = (int) $_GET['delete_hotel'];
    $deleteStmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($deleteStmt, "DELETE FROM hotel_bookings WHERE booking_id = ? AND userid = ?")) {
        mysqli_stmt_bind_param($deleteStmt, "ii", $deleteId, $userid);
        mysqli_stmt_execute($deleteStmt);
        mysqli_stmt_close($deleteStmt);
    }
    header("Location: profile.php");
    exit();
}

// Xử lý xóa tour booking
if (isset($_GET['delete_tour']) && is_numeric($_GET['delete_tour'])) {
    $booking_id = (int)$_GET['delete_tour'];
    $deleteStmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($deleteStmt, "DELETE FROM tour_bookings WHERE booking_id = ? AND userid = ?")) {
        mysqli_stmt_bind_param($deleteStmt, "ii", $booking_id, $userid);
        mysqli_stmt_execute($deleteStmt);
        mysqli_stmt_close($deleteStmt);
    }
    header("Location: profile.php");
    exit();
}

// Lấy thông tin người dùng
$userSql = "SELECT usersId, usersEmail, usersUid FROM login WHERE usersId = ?";
$userStmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($userStmt, $userSql)) {
    echo "Lỗi truy vấn cơ sở dữ liệu.";
    exit();
}
mysqli_stmt_bind_param($userStmt, "i", $userid);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);

// Lấy danh sách thành phố yêu thích
$favSql = "
    SELECT cities.cityid, cities.city 
    FROM favorites 
    JOIN cities ON favorites.cityid = cities.cityid 
    WHERE favorites.usersid = ?
";
$favStmt = mysqli_stmt_init($conn);
$favoriteCities = [];
if (mysqli_stmt_prepare($favStmt, $favSql)) {
    mysqli_stmt_bind_param($favStmt, "i", $userid);
    mysqli_stmt_execute($favStmt);
    $favResult = mysqli_stmt_get_result($favStmt);
    while ($favRow = mysqli_fetch_assoc($favResult)) {
        $favoriteCities[] = $favRow;
    }
    mysqli_stmt_close($favStmt);
}

// Lấy danh sách hotel bookings
$hotelReceipts = [];
$hotelStmt = mysqli_stmt_init($conn);
if (mysqli_stmt_prepare($hotelStmt, "SELECT booking_id, hotel_name, city_name, tourists, number_of_rooms, room_type, total_amount, booking_date, check_in_date, check_out_date, payment_status FROM hotel_bookings WHERE userid = ?")) {
    mysqli_stmt_bind_param($hotelStmt, "i", $userid);
    mysqli_stmt_execute($hotelStmt);
    $hotelResult = mysqli_stmt_get_result($hotelStmt);
    while ($row = mysqli_fetch_assoc($hotelResult)) {
        $hotelReceipts[] = $row;
    }
    mysqli_stmt_close($hotelStmt);
}

// Lấy danh sách tour bookings
$tourBookings = [];
$tourStmt = mysqli_stmt_init($conn);
if (mysqli_stmt_prepare($tourStmt, "SELECT booking_id, name, email, city_name, tour_name, tourists, tour_date, contact, price_per_person, total_amount, booking_date, payment_status FROM tour_bookings WHERE userid = ?")) {
    mysqli_stmt_bind_param($tourStmt, "i", $userid);
    mysqli_stmt_execute($tourStmt);
    $tourResult = mysqli_stmt_get_result($tourStmt);
    while ($row = mysqli_fetch_assoc($tourResult)) {
        $tourBookings[] = $row;
    }
    mysqli_stmt_close($tourStmt);
}

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
    <style>
        .info-box table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
            background-color: #fff;
            border: 1px solid #3498db;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .info-box th, .info-box td {
            border-bottom: 1px solid #3498db;
            padding: 12px;
            text-align: center;
        }
        .info-box th {
            background-color: #eaf6fb;
            color: #3498db;
            font-weight: bold;
        }
        .info-box tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .info-box .delete-link a {
            display: inline-block;
            padding: 6px 12px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .info-box .delete-link a:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">
            <a href="LoggedinHome.php">
                <img src="../images/logo.png" alt="VietTransit Logo">
                <span>VietTransit</span>
            </a>
        </div>
        <div class="nav-links">
            <a href="LoggedinHome.php" class="nav-btn">Home</a>
            <a href="login.php" class="nav-btn">Logout</a>
        </div>
    </nav>

    <!-- Profile Header Section -->
    <div class="profile-header">
        <div class="cover-photo" style="background-image: url('../images/Cover_pic.png');"></div>
        <div class="profile-info-container">
            <div class="avatar-container">
                <img src="../images/avatar.jpg" alt="User Avatar" class="avatar">
                <div class="avatar-overlay">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            <div class="user-info">
                <?php
                if ($row = mysqli_fetch_assoc($userResult)) {
                    echo "<h2>" . htmlspecialchars($row["usersUid"]) . "</h2>";
                    echo "<p><i class='fas fa-envelope'></i> " . htmlspecialchars($row["usersEmail"]) . "</p>";
                } else {
                    echo "<h2>User</h2>";
                    echo "<p><i class='fas fa-envelope'></i> Not found</p>";
                }
                ?>
                <div class="user-stats">
                    <span><i class="fas fa-award"></i> 2840 PTS</span>
                    <span><i class="fas fa-map-marker-alt"></i> Group 1 Web Dev, Vietnam</span>
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
                <?php
                if ($favoriteCities !== null && count($favoriteCities) > 0) {
                    echo "<div class='city-gallery'>";
                    $citySlugMap = [
                        10 => 'tay_bac',
                        11 => 'ho_chi_minh',
                        12 => 'nha_trang',
                        13 => 'hue',
                        14 => 'phu_yen',
                        15 => 'da_lat',
                        16 => 'phu_quoc',
                        17 => 'hoi_an',
                        18 => 'ha_giang',
                    ];
                    foreach ($favoriteCities as $favRow) {
                        $cityName = htmlspecialchars($favRow['city']);
                        $cityId = intval($favRow['cityid']);
                        $imagePath = "/Places/{$cityId}.jpg";
                        $citySlug = isset($citySlugMap[$cityId]) ? $citySlugMap[$cityId] : 'default';
                        $tourPage = "../Journey/viewjourney_$citySlug.php";
                        echo "<div class='city-item'>";
                        echo "<img src='$imagePath' alt='$cityName'>";
                        echo "<p>$cityName</p>";
                        echo "<div class='city-actions'>";
                        echo "<a href='../view_hotels.php?city_id=$cityId' class='btn-book'>Book Hotel</a>";
                        echo "<a href='$tourPage' class='btn-book'>Book Tour</a>";
                        echo "<form action='remove_favorite.php' method='post'>";
                        echo "<input type='hidden' name='cityid' value='$cityId'>";
                        echo "<button type='submit' class='btn-remove'>Remove</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<p>You haven't favorited any cities.</p>";
                }
                ?>
            </div>
        </div>

        <!-- View Tours Tab -->
<div id="view-tours" class="tab-content">
    <div class="info-box">
        <h3>Your Booked Tours</h3>
        <table>
            <tr>
                <th>Booking ID</th>
                <th>City</th>
                <th>Tour Name</th>
                <th>Tourists</th>
                <th>Tour Date</th>
                <th>Contact</th>
                <th>Price/Person (VND)</th>
                <th>Total Amount (VND)</th>
                <th>Booking Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if (!empty($tourBookings) && is_array($tourBookings)): ?>
                <?php foreach ($tourBookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['booking_id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($booking['city_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($booking['tour_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars((string)($booking['tourists'] ?? '1')) ?></td>
                        <td><?= htmlspecialchars($booking['tour_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars($booking['contact'] ?? '') ?></td>
                        <td><?= number_format($booking['price_per_person'] ?? 0) ?></td>
                        <td><?= number_format($booking['total_amount'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($booking['booking_date'] ?? '') ?></td>
                        <td><?= htmlspecialchars($booking['payment_status'] ?? '') ?></td>
                        <td class="delete-link">
                            <a href="?delete_tour=<?= htmlspecialchars($booking['booking_id'] ?? '') ?>" 
                               onclick="return confirm('Are you sure you want to delete this booking?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="11">No bookings available.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

        <!-- View Hotels Tab -->
        <div id="view-hotels" class="tab-content">
            <div class="info-box">
                <h3>Your Booked Hotels</h3>
                <table>
                    <tr>
                        <th>Booking ID</th>
                        <th>Hotel Name</th>
                        <th>City</th>
                        <th>Tourists</th>
                        <th>Number of Rooms</th>
                        <th>Room Types</th>
                        <th>Check-In Date</th>
                        <th>Check-Out Date</th>
                        <th>Total Amount</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php if (!empty($hotelReceipts)): ?>
                        <?php foreach ($hotelReceipts as $receipt): ?>
                            <tr>
                                <td><?= htmlspecialchars($receipt['booking_id']) ?></td>
                                <td><?= htmlspecialchars($receipt['hotel_name']) ?></td>
                                <td><?= htmlspecialchars($receipt['city_name']) ?></td>
                                <td><?= htmlspecialchars($receipt['tourists']) ?></td>
                                <td><?= htmlspecialchars($receipt['number_of_rooms']) ?></td>
                                <td><?= htmlspecialchars($receipt['room_type']) ?></td>
                                <td><?= htmlspecialchars($receipt['check_in_date']) ?></td>
                                <td><?= htmlspecialchars($receipt['check_out_date']) ?></td>
                                <td><?= htmlspecialchars($receipt['total_amount']) ?> VND</td>
                                <td><?= htmlspecialchars($receipt['booking_date']) ?></td>
                                 <td><?= htmlspecialchars($receipt['payment_status']) ?></td>
                                <td class="delete-link">
                                    <a href="?delete_hotel=<?= $receipt['booking_id'] ?>" onclick="return confirm('Are you sure you want to delete this booking?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="13">No receipts recorded.</td></tr>
                    <?php endif; ?>
                </table>
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
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            const avatarContainer = document.querySelector('.avatar-container');
            avatarContainer.addEventListener('click', function() {
                alert('Avatar upload functionality will be implemented here.');
            });
        });
    </script>

    <?php
    mysqli_stmt_close($userStmt);
    ?>
</body>
</html>