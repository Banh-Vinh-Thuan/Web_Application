<?php
include '../dbconnect.php';

// Fetch cities for dropdown
$city_result = $conn->query("SELECT DISTINCT cityid, city_name FROM hotel_bookings ORDER BY city_name");

// Handle filters
$city_filter = $_GET['city'] ?? '';
$price_filter = $_GET['price'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = [];
$params = [];
$types = '';

if ($city_filter) {
    $where[] = "cityid = ?";
    $params[] = $city_filter;
    $types .= 'i';
}

if ($price_filter) {
    if ($price_filter == 'under_1m') {
        $where[] = "total_amount < 1000000";
    } elseif ($price_filter == '1m_2m') {
        $where[] = "total_amount BETWEEN 1000000 AND 2000000";
    } elseif ($price_filter == 'above_2m') {
        $where[] = "total_amount > 2000000";
    }
}

if ($status_filter) {
    $where[] = "payment_status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$query = "SELECT * FROM hotel_bookings";
if ($where) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Function to format status with appropriate styling
function getStatusBadge($status) {
    $class = 'status-badge ';
    switch(strtolower($status)) {
        case 'pending':
            $class .= 'status-pending';
            break;
        case 'completed':
            $class .= 'status-completed';
            break;
        case 'failed':
            $class .= 'status-failed';
            break;
        default:
            $class .= 'status-pending';
    }
    return '<span class="' . $class . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}

// Function to format currency
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' VND';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hotel Bookings Management | Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../css/adminviewhotelbooking.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h2>🏨 Hotel Bookings Management</h2>
            <p class="header-subtitle">Manage and monitor all hotel reservations</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="city">🏙️ Filter by City</label>
                    <select id="city" name="city">
                        <option value="">All Cities</option>
                        <?php 
                        // Reset city result pointer
                        $city_result->data_seek(0);
                        while ($city = $city_result->fetch_assoc()) { ?>
                            <option value="<?= htmlspecialchars($city['cityid']) ?>" 
                                <?= $city_filter == $city['cityid'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($city['city_name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="price">💰 Price Range</label>
                    <select id="price" name="price">
                        <option value="">All Prices</option>
                        <option value="under_1m" <?= $price_filter == 'under_1m' ? 'selected' : '' ?>>Under 1M VND</option>
                        <option value="1m_2m" <?= $price_filter == '1m_2m' ? 'selected' : '' ?>>1M - 2M VND</option>
                        <option value="above_2m" <?= $price_filter == 'above_2m' ? 'selected' : '' ?>>Above 2M VND</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status">📊 Payment Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="failed" <?= $status_filter == 'failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div class="filter-group">
                    <button type="submit" class="filter-btn">
                        🔍 Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>📋 ID</th>
                            <th>👤 Guest Name</th>
                            <th>📧 Email</th>
                            <th>🏙️ City</th>
                            <th>🏨 Hotel</th>
                            <th>📅 Booking Date</th>
                            <th>🔄 Check-in</th>
                            <th>🔚 Check-out</th>
                            <th>🏠 Rooms</th>
                            <th>🛏️ Room Type</th>
                            <th>👥 Guests</th>
                            <th>💰 Total Amount</th>
                            <th>📊 Status</th>
                            <th>⚙️ Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($row["booking_id"]) ?></strong></td>
                                <td><?= htmlspecialchars($row["name"]) ?></td>
                                <td><?= htmlspecialchars($row["email"]) ?></td>
                                <td><?= htmlspecialchars($row["city_name"]) ?></td>
                                <td><strong><?= htmlspecialchars($row["hotel_name"]) ?></strong></td>
                                <td><?= date('M d, Y', strtotime($row["booking_date"])) ?></td>
                                <td><?= date('M d, Y', strtotime($row["check_in_date"])) ?></td>
                                <td><?= date('M d, Y', strtotime($row["check_out_date"])) ?></td>
                                <td><strong><?= htmlspecialchars($row["number_of_rooms"]) ?></strong></td>
                                <td><?= htmlspecialchars($row["room_type"]) ?></td>
                                <td><strong><?= htmlspecialchars($row["tourists"]) ?></strong></td>
                                <td><strong><?= formatCurrency($row["total_amount"]) ?></strong></td>
                                <td><?= getStatusBadge($row["payment_status"]) ?></td>
                                <td>
                                    <div class="action-links">
                                        <a href="controllers/edithotelbooking.php?edit=<?= htmlspecialchars($row["booking_id"]) ?>" 
                                           title="Edit Booking">
                                            ✏️ Edit
                                        </a>
                                        <a href="?delete=<?= htmlspecialchars($row["booking_id"]) ?>" 
                                           onclick="return confirm('⚠️ Are you sure you want to delete this booking?\n\nBooking ID: #<?= htmlspecialchars($row["booking_id"]) ?>\nGuest: <?= htmlspecialchars($row["name"]) ?>\n\nThis action cannot be undone!')"
                                           title="Delete Booking">
                                            🗑️ Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 50px; color: #7f8c8d;">
                    <h3>📭 No bookings found</h3>
                    <p>Try adjusting your filters or check back later.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back Button -->
        <div class="back-btn-wrapper">
            <button class="back-btn" onclick="window.location.href='admindashboard.php'">
                ⬅️ Back to Dashboard
            </button>
        </div>
    </div>

    <script>
        // Add smooth loading animation
        document.addEventListener('DOMContentLoaded', function() {
            // Add fade-in animation to elements
            const elements = document.querySelectorAll('.header, .filter-section, .table-container');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    el.style.transition = 'all 0.6s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 200);
            });

            // Add loading state to filter button
            const filterForm = document.querySelector('.filter-form');
            const filterBtn = document.querySelector('.filter-btn');
            
            if (filterForm && filterBtn) {
                filterForm.addEventListener('submit', function() {
                    filterBtn.innerHTML = '<span class="loading"></span> Filtering...';
                    filterBtn.disabled = true;
                });
            }

            // Enhanced table row hover effects
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.zIndex = '10';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.zIndex = '1';
                });
            });
        });

        // Add keyboard navigation for accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Clear all filters
                const selects = document.querySelectorAll('select');
                selects.forEach(select => select.selectedIndex = 0);
            }
        });
    </script>
</body>
</html>
<?php $stmt->close(); ?>