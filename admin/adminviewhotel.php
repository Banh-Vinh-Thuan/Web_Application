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

// Fetch hotel for editing
$edit = null;
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $edit = $conn->query("SELECT * FROM hotels WHERE hotelid = $id")->fetch_assoc();
}
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <p class="header-subtitle">Manage your hotels with ease and efficiency</p>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Filter Section -->
        <section class="filter-section">
            <div class="filter-header">
                <h2><i class="fas fa-filter"></i> Filter Hotels</h2>
                <p>Use the filters below to find specific hotels</p>
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
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-search"></i>
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Hotels Table Section -->
        <section class="table-section">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Hotel Directory</h2>
                <p>Manage all your hotels in one place</p>
            </div>

            <div class="table-container">
                <table class="hotels-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-hotel"></i> Hotel Name</th>
                            <th><i class="fas fa-map-marker-alt"></i> City</th>
                            <th><i class="fas fa-dollar-sign"></i> Cost</th>
                            <th><i class="fas fa-concierge-bell"></i> Amenities</th>
                            <th><i class="fas fa-star"></i> Rating</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
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
                                <a href="?edit=<?= $row["hotelid"] ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="controllers/deletehotel.php?delete=<?= $row["hotelid"] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this hotel?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Add/Edit Form Section -->
        <section class="form-section">
            <div class="form-container">
                <div class="form-header">
                    <h2>
                        <i class="<?= $edit ? 'fas fa-edit' : 'fas fa-plus' ?>"></i>
                        <?= $edit ? "Edit Hotel #" . $edit["hotelid"] : "Add New Hotel" ?>
                    </h2>
                    <p><?= $edit ? "Update hotel information" : "Add a new hotel to your directory" ?></p>
                </div>

                <form method="POST" action="<?= $edit ? 'controllers/edithotel.php' : 'controllers/addhotel.php' ?>" class="hotel-form">
                    <input type="hidden" name="hotelid" value="<?= $edit["hotelid"] ?? '' ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="hotel">
                                <i class="fas fa-hotel"></i>
                                Hotel Name *
                            </label>
                            <input id="hotel" name="hotel" type="text" 
                                   value="<?= htmlspecialchars($edit["hotel"] ?? '') ?>" 
                                   class="form-input" 
                                   placeholder="Enter hotel name"
                                   required />
                        </div>

                        <div class="form-group">
                            <label for="cityid">
                                <i class="fas fa-map-marker-alt"></i>
                                City *
                            </label>
                            <select id="cityid" name="cityid" class="form-select" required>
                                <option value="">Select a city</option>
                                <?php foreach ($city_options as $id => $name) { ?>
                                    <option value="<?= $id ?>" <?= ($edit["cityid"] ?? '') == $id ? "selected" : "" ?>>
                                        <?= "$id - $name" ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cost">
                                <i class="fas fa-dollar-sign"></i>
                                Cost per Night *
                            </label>
                            <input id="cost" name="cost" type="number" 
                                   value="<?= $edit["cost"] ?? '' ?>" 
                                   class="form-input" 
                                   placeholder="Enter cost"
                                   required />
                        </div>

                        <div class="form-group">
                            <label for="ratings">
                                <i class="fas fa-star"></i>
                                Rating (1-5) *
                            </label>
                            <select id="ratings" name="ratings" class="form-select" required>
                                <option value="">Select rating</option>
                                <?php for ($i = 1; $i <= 5; $i++) { ?>
                                    <option value="<?= $i ?>" <?= ($edit["ratings"] ?? '') == $i ? "selected" : "" ?>>
                                        <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="amenities">
                            <i class="fas fa-concierge-bell"></i>
                            Amenities
                        </label>
                        <textarea id="amenities" name="amenities" 
                                  class="form-textarea" 
                                  placeholder="List hotel amenities (e.g., WiFi, Pool, Gym, Restaurant...)"
                                  rows="4"><?= htmlspecialchars($edit["amenities"] ?? '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-large">
                            <i class="<?= $edit ? 'fas fa-save' : 'fas fa-plus' ?>"></i>
                            <?= $edit ? "Update Hotel" : "Add New Hotel" ?>
                        </button>
                        <?php if ($edit) { ?>
                            <a href="adminviewhotel.php" class="btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel Edit
                            </a>
                        <?php } ?>
                    </div>
                </form>
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

    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Processing...</p>
        </div>
    </div>

    <script>
        // Add loading effect for form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                document.getElementById('loadingOverlay').style.display = 'flex';
            });
        });

        // Add smooth scrolling for edit links
        document.querySelectorAll('a[href*="edit="]').forEach(link => {
            link.addEventListener('click', function() {
                setTimeout(() => {
                    document.querySelector('.form-section').scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                }, 100);
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>