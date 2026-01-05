<?php
include '../dbconnect.php';

// Handle image migration from hotelphotoID folder
if (isset($_GET['migrate_images']) && $_GET['migrate_images'] == '1') {
    $sourceDir = '../hotelphotoID/';
    $targetDir = '../images/hotels/';
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $migrated = 0;
    $errors = 0;
    
    // Get hotels without images
    $hotels_to_migrate = $conn->query("SELECT hotelid FROM hotels WHERE image IS NULL OR image = ''");
    
    while ($hotel = $hotels_to_migrate->fetch_assoc()) {
        $hotelId = $hotel['hotelid'];
        $sourceFile = $sourceDir . $hotelId . '.jpg';
        
        if (file_exists($sourceFile)) {
            $newFileName = $hotelId . '_' . time() . '.jpg';
            $targetFile = $targetDir . $newFileName;
            
            if (copy($sourceFile, $targetFile)) {
                $stmt = $conn->prepare("UPDATE hotels SET image = ?, discount = 7 WHERE hotelid = ?");
                $stmt->bind_param("si", $newFileName, $hotelId);
                
                if ($stmt->execute()) {
                    $migrated++;
                } else {
                    $errors++;
                }
                $stmt->close();
            } else {
                $errors++;
            }
        }
    }
    
    if ($migrated > 0) {
        header("Location: adminviewhotel.php?success=Successfully migrated {$migrated} hotel images" . ($errors > 0 ? " ({$errors} errors)" : ""));
    } else {
        header("Location: adminviewhotel.php?error=No images to migrate or migration failed");
    }
    exit();
}

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

// Fetch hotels
$result = $conn->query($filter_query);

// Fetch cities for dropdown
$cities_result = $conn->query("SELECT cityid, city FROM cities");
$city_options = [];
while ($city = $cities_result->fetch_assoc()) {
    $city_options[$city['cityid']] = $city['city'];
}

// Check if there are hotels without images
$hotels_without_images = $conn->query("SELECT COUNT(*) as count FROM hotels WHERE image IS NULL OR image = ''")->fetch_assoc()['count'];

// Fetch hotel for editing if requested
$edit_data = null;
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $edit_data = $conn->query("SELECT * FROM hotels WHERE hotelid = $id")->fetch_assoc();
}

// Display success/error messages
$message = '';
$message_type = '';
if (isset($_GET['success'])) {
    $message = $_GET['success'];
    $message_type = 'success';
} elseif (isset($_GET['error'])) {
    $message = $_GET['error'];
    $message_type = 'error';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .migration-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .migration-banner-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .migration-banner i {
            font-size: 24px;
        }
        .migration-banner-text h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        .migration-banner-text p {
            margin: 5px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .btn-migrate {
            background: white;
            color: #667eea;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-migrate:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
    </style>
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
        <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>">
            <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <?php if ($hotels_without_images > 0): ?>
        <div class="migration-banner">
            <div class="migration-banner-content">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="migration-banner-text">
                    <h3><?= $hotels_without_images ?> hotel(s) without images detected</h3>
                    <p>Click the button to automatically migrate images from hotelphotoID folder</p>
                </div>
            </div>
            <button onclick="migrateImages()" class="btn-migrate">
                <i class="fas fa-sync-alt"></i>
                Migrate Images
            </button>
        </div>
        <?php endif; ?>

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
                            <th>Image</th>
                            <th>Hotel Name</th>
                            <th>City</th>
                            <th>Cost</th>
                            <th>Discount</th>
                            <th>Amenities</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="table-row">
                            <td class="id-cell"><?= $row["hotelid"] ?></td>
                            <td class="image-cell">
                                <?php if (!empty($row["image"])): ?>
                                    <img src="../images/hotels/<?= htmlspecialchars($row["image"]) ?>" alt="<?= htmlspecialchars($row["hotel"]) ?>" class="hotel-thumbnail">
                                <?php else: ?>
                                    <div class="no-image"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="hotel-name"><?= htmlspecialchars($row["hotel"]) ?></td>
                            <td class="city-info"><?= $row["cityid"] ?> - <?= $city_options[$row["cityid"]] ?? "Unknown" ?></td>
                            <td class="cost-cell">$<?= number_format($row["cost"]) ?></td>
                            <td class="cost-cell"><?= $row["discount"] ?? 0 ?>%</td>
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

            <form id="hotelForm" method="POST" action="controllers/addhotel.php" enctype="multipart/form-data" class="hotel-form">
                <input type="hidden" name="hotelid" id="hotelid" value="">
                <input type="hidden" name="existing_image" id="existing_image" value="">
                
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
                        <input id="modal_cost" name="cost" type="number" step="0.01"
                               class="form-input" 
                               placeholder="Enter cost"
                               required />
                    </div>

                    <div class="form-group">
                        <label for="modal_discount">
                            <i class="fas fa-percent"></i>
                            Discount (%)
                        </label>
                        <input id="modal_discount" name="discount" type="number" min="0" max="100"
                               class="form-input" 
                               placeholder="Enter discount percentage"
                               value="0" />
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

                <div class="form-group full-width">
                    <label for="hotel_image">
                        <i class="fas fa-image"></i>
                        Hotel Image
                    </label>
                    <div class="image-upload-container">
                        <input type="file" id="hotel_image" name="hotel_image" accept="image/*" class="form-file" onchange="previewImage(event)">
                        <div id="imagePreview" class="image-preview"></div>
                    </div>
                    <small class="form-hint">Recommended: JPG, PNG, or WEBP (Max 5MB)</small>
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

        // Migrate images function
        function migrateImages() {
            if (confirm('This will migrate images from hotelphotoID folder to database. Continue?')) {
                document.getElementById('loadingOverlay').style.display = 'flex';
                window.location.href = 'adminviewhotel.php?migrate_images=1';
            }
        }

        // Preview image before upload
        function previewImage(event) {
            const preview = document.getElementById('imagePreview');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        }

        // Open modal for adding new hotel
        function openModal() {
            document.getElementById('hotelModal').style.display = 'flex';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Add New Hotel';
            document.getElementById('submitBtnText').textContent = 'Add Hotel';
            document.getElementById('hotelForm').action = 'controllers/addhotel.php';
            document.getElementById('hotelForm').reset();
            document.getElementById('hotelid').value = '';
            document.getElementById('existing_image').value = '';
            document.getElementById('imagePreview').innerHTML = '';
            document.getElementById('modal_discount').value = '0';
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
            document.getElementById('modal_discount').value = hotel.discount || 0;
            document.getElementById('modal_ratings').value = hotel.ratings;
            document.getElementById('modal_amenities').value = hotel.amenities;
            document.getElementById('existing_image').value = hotel.image || '';
            
            // Show existing image
            const preview = document.getElementById('imagePreview');
            if (hotel.image) {
                preview.innerHTML = `<img src="../images/hotels/${hotel.image}" alt="Current image"><p class="current-image-text">Current Image</p>`;
            } else {
                preview.innerHTML = '';
            }
        }

        // Close modal
        function closeModal() {
            document.getElementById('hotelModal').style.display = 'none';
            document.getElementById('hotelForm').reset();
            document.getElementById('imagePreview').innerHTML = '';
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