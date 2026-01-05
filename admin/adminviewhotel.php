<?php
include '../dbconnect.php';

// Handle filters
$filters = [];
$filter_query = "SELECT * FROM hotels WHERE 1=1";

if (isset($_GET['cityid']) && $_GET['cityid'] !== '') {
    $cityid = (int)$_GET['cityid'];
    $filter_query .= " AND cityid = $cityid";
    $filters['cityid'] = $cityid;
}

if (isset($_GET['ratings']) && $_GET['ratings'] !== '') {
    $ratings = (int)$_GET['ratings'];
    $filter_query .= " AND ratings = $ratings";
    $filters['ratings'] = $ratings;
}

if (isset($_GET['cost']) && $_GET['cost'] !== '') {
    $cost = $_GET['cost'];
    if ($cost == 'under1m') {
        $filter_query .= " AND cost < 1000000";
    } elseif ($cost == '1m-2m') {
        $filter_query .= " AND cost BETWEEN 1000000 AND 2000000";
    } elseif ($cost == 'above2m') {
        $filter_query .= " AND cost > 2000000";
    }
    $filters['cost'] = $cost;
}

if (isset($_GET['payment_status']) && $_GET['payment_status'] !== '') {
    $payment_status = $_GET['payment_status'];
    $filter_query .= " AND payment_status = '$payment_status'";
    $filters['payment_status'] = $payment_status;
}

// Fetch hotels
$result = $conn->query($filter_query);

// Fetch cities for dropdown
$cities_result = $conn->query("SELECT cityid, city FROM cities");
$city_options = [];
while ($city = $cities_result->fetch_assoc()) {
    $city_options[$city['cityid']] = $city['city'];
}

// Fetch hotel for editing if requested
$edit_data = null;
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $edit_data = $conn->query("SELECT * FROM hotels WHERE hotelid = $id")->fetch_assoc();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hotel = $_POST['hotel'];
    $cityid = (int)$_POST['cityid'];
    $cost = (int)$_POST['cost'];
    $amenities = $_POST['amenities'];
    $ratings = (int)$_POST['ratings'];
    
    $stmt = $conn->prepare("INSERT INTO hotels (hotel, cityid, cost, amenities, ratings) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sidsi", $hotel, $cityid, $cost, $amenities, $ratings);
    
    if ($stmt->execute()) {
        header("Location: ../adminviewhotel.php?success=Hotel added successfully");
    } else {
        header("Location: ../adminviewhotel.php?error=Failed to add hotel");
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management System</title>
    <link rel="stylesheet" type="text/css" href="../css/adminviewhotel.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="header-title">
                    <i class="fas fa-hotel"></i>
                    Hotel Management System
                </h1>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Filter Section -->
        <section class="filter-section">
            <div class="section-header">
                <h2><i class="fas fa-filter"></i> Filters</h2>
            </div>
            
            <form method="GET" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="cityid">
                            <i class="fas fa-map-marker-alt"></i>
                            City
                        </label>
                        <select id="cityid" name="cityid" class="form-select">
                            <option value="">All Cities</option>
                            <?php foreach ($city_options as $id => $name) { ?>
                                <option value="<?= $id ?>" <?= isset($filters['cityid']) && $filters['cityid'] == $id ? "selected" : "" ?>>
                                    <?= "$id - $name" ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="ratings">
                            <i class="fas fa-star"></i>
                            Ratings
                        </label>
                        <select id="ratings" name="ratings" class="form-select">
                            <option value="">All Ratings</option>
                            <?php for ($i = 1; $i <= 5; $i++) { ?>
                                <option value="<?= $i ?>" <?= isset($filters['ratings']) && $filters['ratings'] == $i ? "selected" : "" ?>>
                                    <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="cost">
                            <i class="fas fa-dollar-sign"></i>
                            Cost Range
                        </label>
                        <select id="cost" name="cost" class="form-select">
                            <option value="">All Costs</option>
                            <option value="under1m" <?= isset($filters['cost']) && $filters['cost'] == 'under1m' ? "selected" : "" ?>>Under 1M</option>
                            <option value="1m-2m" <?= isset($filters['cost']) && $filters['cost'] == '1m-2m' ? "selected" : "" ?>>1M - 2M</option>
                            <option value="above2m" <?= isset($filters['cost']) && $filters['cost'] == 'above2m' ? "selected" : "" ?>>Above 2M</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search"></i>
                            Apply
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Hotels Table Section -->
        <section class="table-section">
            <div class="table-header-actions">
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> Hotel Directory</h2>
                </div>
                <button type="button" class="btn-add-new" onclick="openModal()">
                    <i class="fas fa-plus"></i>
                    Add New Hotel
                </button>
            </div>

            <div class="table-container">
                <table class="hotels-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hotel Name</th>
                            <th>City</th>
                            <th>Cost</th>
                            <th>Amenities</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="table-row">
                            <td class="id-cell"><?= $row["hotelid"] ?></td>
                            <td class="hotel-name"><?= htmlspecialchars($row["hotel"]) ?></td>
                            <td class="city-info"><?= $row["cityid"] ?> - <?= $city_options[$row["cityid"]] ?? "Unknown" ?></td>
                            <td class="cost-cell">$<?= number_format($row["cost"]) ?></td>
                            <td class="amenities-cell"><?= nl2br(htmlspecialchars($row["amenities"])) ?></td>
                            <td class="rating-cell">
                                <div class="star-rating">
                                    <?php for($i = 1; $i <= 5; $i++) { ?>
                                        <i class="fas fa-star <?= $i <= $row["ratings"] ? 'star-filled' : 'star-empty' ?>"></i>
                                    <?php } ?>
                                </div>
                            </td>
                            <td class="actions-cell">
                                <button class="btn-icon btn-edit" onclick='editHotel(<?= json_encode($row) ?>)' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="controllers/deletehotel.php?delete=<?= $row["hotelid"] ?>" 
                                   class="btn-icon btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this hotel?')"
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Navigation Section -->
        <section class="navigation-section">
            <button type="button" onclick="window.location.href='admindashboard.php'" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </button>
        </section>
    </div>

    <!-- Modal for Add/Edit Hotel -->
    <div id="hotelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">
                    <i class="fas fa-plus"></i>
                    Add New Hotel
                </h2>
                <button type="button" class="btn-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="hotelForm" method="POST" action="controllers/addhotel.php" class="hotel-form">
                <input type="hidden" name="hotelid" id="hotelid" value="">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="hotel">
                            <i class="fas fa-hotel"></i>
                            Hotel Name *
                        </label>
                        <input id="hotel" name="hotel" type="text" 
                               class="form-input" 
                               placeholder="Enter hotel name"
                               required />
                    </div>

                    <div class="form-group">
                        <label for="modal_cityid">
                            <i class="fas fa-map-marker-alt"></i>
                            City *
                        </label>
                        <select id="modal_cityid" name="cityid" class="form-select" required>
                            <option value="">Select a city</option>
                            <?php foreach ($city_options as $id => $name) { ?>
                                <option value="<?= $id ?>">
                                    <?= "$id - $name" ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal_cost">
                            <i class="fas fa-dollar-sign"></i>
                            Cost per Night *
                        </label>
                        <input id="modal_cost" name="cost" type="number" 
                               class="form-input" 
                               placeholder="Enter cost"
                               required />
                    </div>

                    <div class="form-group">
                        <label for="modal_ratings">
                            <i class="fas fa-star"></i>
                            Rating (1-5) *
                        </label>
                        <select id="modal_ratings" name="ratings" class="form-select" required>
                            <option value="">Select rating</option>
                            <?php for ($i = 1; $i <= 5; $i++) { ?>
                                <option value="<?= $i ?>">
                                    <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="modal_amenities">
                        <i class="fas fa-concierge-bell"></i>
                        Amenities
                    </label>
                    <textarea id="modal_amenities" name="amenities" 
                              class="form-textarea" 
                              placeholder="List hotel amenities (e.g., WiFi, Pool, Gym, Restaurant...)"
                              rows="3"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check"></i>
                        <span id="submitBtnText">Add Hotel</span>
                    </button>
                    <button type="button" class="btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Processing...</p>
        </div>
    </div>

    <script>
        const cityOptions = <?= json_encode($city_options) ?>;
        const editData = <?= $edit_data ? json_encode($edit_data) : 'null' ?>;

        // Open modal for adding new hotel
        function openModal() {
            document.getElementById('hotelModal').style.display = 'flex';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Add New Hotel';
            document.getElementById('submitBtnText').textContent = 'Add Hotel';
            document.getElementById('hotelForm').action = 'controllers/addhotel.php';
            document.getElementById('hotelForm').reset();
            document.getElementById('hotelid').value = '';
        }

        // Open modal for editing hotel
        function editHotel(hotel) {
            document.getElementById('hotelModal').style.display = 'flex';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Hotel #' + hotel.hotelid;
            document.getElementById('submitBtnText').textContent = 'Update Hotel';
            document.getElementById('hotelForm').action = 'controllers/edithotel.php';
            
            // Populate form with hotel data
            document.getElementById('hotelid').value = hotel.hotelid;
            document.getElementById('hotel').value = hotel.hotel;
            document.getElementById('modal_cityid').value = hotel.cityid;
            document.getElementById('modal_cost').value = hotel.cost;
            document.getElementById('modal_ratings').value = hotel.ratings;
            document.getElementById('modal_amenities').value = hotel.amenities;
        }

        // Close modal
        function closeModal() {
            document.getElementById('hotelModal').style.display = 'none';
            document.getElementById('hotelForm').reset();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('hotelModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Add loading effect for form submissions
        document.getElementById('hotelForm').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });

        // If edit parameter exists, open modal with edit data
        if (editData) {
            editHotel(editData);
        }
    </script>
</body>
</html>