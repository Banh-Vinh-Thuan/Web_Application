<?php
require_once '../dbconnect.php';

// DELETE
if (isset($_GET['delete'])) {
    $booking_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tour_bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    header("Location: adminviewtourbooking.php");
    exit;
}

// Get distinct cities for dropdown
$city_query = "SELECT DISTINCT city_name FROM tour_bookings ORDER BY city_name";
$city_result = $conn->query($city_query);

// FILTER
$filter_city = $_GET['city'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_price = $_GET['price'] ?? '';

$where = [];
$params = [];
$types = '';

if (!empty($filter_city)) {
    $where[] = "city_name = ?";
    $params[] = $filter_city;
    $types .= 's';
}
if (!empty($filter_status)) {
    $where[] = "payment_status = ?";
    $params[] = $filter_status;
    $types .= 's';
}
if (!empty($filter_price)) {
    if ($filter_price === '1to5') {
        $where[] = "price_per_person BETWEEN 00 AND 5000000";
    } elseif ($filter_price === '5to10') {
        $where[] = "price_per_person BETWEEN 5000000 AND 10000000";
    } elseif ($filter_price === 'over10') {
        $where[] = "price_per_person > 10000000";
    }
}

$sql = "SELECT * FROM tour_bookings";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Bookings Management</title>
    <link rel="stylesheet" href="../css/adminviewtourbooking.css" />
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-map-marked-alt"></i>
                <h1>Tour Management</h1>
            </div>
            <nav class="navigation">
                <ul>
                    <li><a href="admindashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="#" class="active"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h2><i class="fas fa-list"></i> Tour Bookings Management</h2>
                <p class="subtitle">Manage and monitor all tour bookings</p>
            </div>
            <div class="stats-summary">
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <div class="stat-info">
                        <span class="stat-number"><?= $result->num_rows ?></span>
                        <span class="stat-label">Total Bookings</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-header">
                <h3><i class="fas fa-filter"></i> Filter Bookings</h3>
            </div>
            <form class="filter-form" method="GET" action="">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="city"><i class="fas fa-city"></i> City</label>
                        <select name="city" id="city">
                            <option value="">All Cities</option>
                            <?php 
                            $city_result->data_seek(0); // Reset result pointer
                            while ($city_row = $city_result->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($city_row['city_name']) ?>" 
                                    <?= $filter_city === $city_row['city_name'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($city_row['city_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status"><i class="fas fa-info-circle"></i> Status</label>
                        <select name="status" id="status">
                            <option value="">All Statuses</option>
                            <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="failed" <?= $filter_status === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="price"><i class="fas fa-dollar-sign"></i> Price Range</label>
                        <select name="price" id="price">
                            <option value="">All Prices</option>
                            <option value="1to5" <?= $filter_price === '1to5' ? 'selected' : '' ?>>0M - 5M VND</option>
                            <option value="5to10" <?= $filter_price === '5to10' ? 'selected' : '' ?>>5M - 10M VND</option>
                            <option value="over10" <?= $filter_price === 'over10' ? 'selected' : '' ?>>Over 10M VND</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="adminviewtourbooking.php" class="btn-reset">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <h3><i class="fas fa-table"></i> Booking Records</h3>
            </div>
            <div class="table-container">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-user"></i> User ID</th>
                            <th><i class="fas fa-user-circle"></i> Name</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-city"></i> City</th>
                            <th><i class="fas fa-route"></i> Tour</th>
                            <th><i class="fas fa-calendar"></i> Date</th>
                            <th><i class="fas fa-users"></i> Guests</th>
                            <th><i class="fas fa-phone"></i> Contact</th>
                            <th><i class="fas fa-money-bill"></i> Price</th>
                            <th><i class="fas fa-calculator"></i> Total</th>
                            <th><i class="fas fa-receipt"></i> Order ID</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $result->data_seek(0); // Reset result pointer
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="id-cell"><?= $row['booking_id'] ?></td>
                                <td><?= $row['userid'] ?></td>
                                <td class="name-cell"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="email-cell"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="city-cell"><?= htmlspecialchars($row['city_name']) ?></td>
                                <td class="tour-cell"><?= htmlspecialchars($row['tour_name']) ?></td>
                                <td class="date-cell"><?= date('M d, Y', strtotime($row['tour_date'])) ?></td>
                                <td class="guests-cell"><?= $row['tourists'] ?></td>
                                <td class="contact-cell"><?= htmlspecialchars($row['contact']) ?></td>
                                <td class="price-cell"><?= number_format($row['price_per_person'], 0, ',', '.') ?> VND</td>
                                <td class="total-cell"><?= number_format($row['total_amount'], 0, ',', '.') ?> VND</td>
                                <td class="order-cell"><?= htmlspecialchars($row['order_id']) ?></td>
                                <td class="status-cell">
                                    <span class="status-badge status-<?= $row['payment_status'] ?>">
                                        <?= ucfirst($row['payment_status']) ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <div class="action-buttons">
                                        <a href="controllers/edittourbooking.php?edit=<?= $row['booking_id'] ?>" 
                                           class="btn-edit" title="Edit Booking">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?= $row['booking_id'] ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('Are you sure you want to delete this booking?')"
                                           title="Delete Booking">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Back Button -->
        <div class="back-section">
            <button class="back-btn" onclick="window.location.href='admindashboard.php'">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </button>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2025 Tour Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>