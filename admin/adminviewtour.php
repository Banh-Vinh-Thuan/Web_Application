<?php
include '../dbconnect.php';

// Handle filters
$filters = [];
$filter_query = "SELECT * FROM tours WHERE 1=1";

if (isset($_GET['cityid']) && $_GET['cityid'] !== '') {
    $cityid = (int)$_GET['cityid'];
    $filter_query .= " AND cityid = $cityid";
    $filters['cityid'] = $cityid;
}

if (isset($_GET['price_per_person']) && $_GET['price_per_person'] !== '') {
    $price = $_GET['price_per_person'];
    if ($price == 'under5m') {
        $filter_query .= " AND price_per_person < 5000000";
    } elseif ($price == '5m-10m') {
        $filter_query .= " AND price_per_person BETWEEN 5000000 AND 10000000";
    } elseif ($price == 'above10m') {
        $filter_query .= " AND price_per_person > 10000000";
    }
    $filters['price_per_person'] = $price;
}

if (isset($_GET['season']) && $_GET['season'] !== '') {
    $season = $_GET['season'];
    $filter_query .= " AND season = '$season'";
    $filters['season'] = $season;
}

// Fetch tours
$tours = $conn->query($filter_query);

// Fetch cities for dropdown
$cities = $conn->query("SELECT cityid, city FROM cities");
$city_options = [];
while ($row = $cities->fetch_assoc()) {
    $city_options[$row['cityid']] = $row['city'];
}

// Fetch tour for editing
$edit = null;
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $edit = $conn->query("SELECT * FROM tours WHERE tourid = $id")->fetch_assoc();
}

// Function to get season class
function getSeasonClass($season) {
    switch(strtolower($season)) {
        case 'spring': return 'season-spring';
        case 'summer': return 'season-summer';
        case 'fall': return 'season-fall';
        case 'winter': return 'season-winter';
        default: return '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Management System</title>
    <link rel="stylesheet" type="text/css" href="../css/adminviewtour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <!-- Header Section -->
        <header class="page-header">
            <h1 class="page-title">Tour Management Center</h1>
            <button type="button" class="btn-add-tour" onclick="openModal()">
                + Add New Tour
            </button>
        </header>

        <!-- Filter Section -->
        <div class="filter-container">
            <form method="GET">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label class="filter-label" for="cityid">Select City</label>
                        <select id="cityid" name="cityid" class="filter-select">
                            <option value="">All Cities</option>
                            <?php foreach ($city_options as $id => $name) { ?>
                                <option value="<?= $id ?>" <?= isset($filters['cityid']) && $filters['cityid'] == $id ? "selected" : "" ?>>
                                    <?= "$id - $name" ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="price_per_person">Price Range</label>
                        <select id="price_per_person" name="price_per_person" class="filter-select">
                            <option value="">All Prices</option>
                            <option value="under5m" <?= isset($filters['price_per_person']) && $filters['price_per_person'] == 'under5m' ? "selected" : "" ?>>Under 5,000,000 VND</option>
                            <option value="5m-10m" <?= isset($filters['price_per_person']) && $filters['price_per_person'] == '5m-10m' ? "selected" : "" ?>>5,000,000 - 10,000,000 VND</option>
                            <option value="above10m" <?= isset($filters['price_per_person']) && $filters['price_per_person'] == 'above10m' ? "selected" : "" ?>>Above 10,000,000 VND</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="season">Season</label>
                        <select id="season" name="season" class="filter-select">
                            <option value="">All Seasons</option>
                            <option value="Spring" <?= isset($filters['season']) && $filters['season'] == 'Spring' ? "selected" : "" ?>>Spring</option>
                            <option value="Summer" <?= isset($filters['season']) && $filters['season'] == 'Summer' ? "selected" : "" ?>>Summer</option>
                            <option value="Fall" <?= isset($filters['season']) && $filters['season'] == 'Fall' ? "selected" : "" ?>>Fall</option>
                            <option value="Winter" <?= isset($filters['season']) && $filters['season'] == 'Winter' ? "selected" : "" ?>>Winter</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="filter-btn">Apply Filters</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tour List Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="tours-table">
                    <thead class="table-header">
                        <tr>
                            <th>ID</th>
                            <th>City</th>
                            <th>Tour Name</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Season</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $tours->fetch_assoc()) { ?>
                        <tr class="table-row">
                            <td class="table-cell cell-id">
                                #<?= $row["tourid"] ?>
                            </td>
                            <td class="table-cell cell-city">
                                <strong><?= $row["cityid"] ?></strong><br>
                                <small><?= htmlspecialchars($city_options[$row["cityid"]] ?? "Unknown") ?></small>
                            </td>
                            <td class="table-cell cell-name">
                                <?= htmlspecialchars($row["tour_name"]) ?>
                            </td>
                            <td class="table-cell cell-description">
                                <?= nl2br(htmlspecialchars(substr($row["description"], 0, 80) . (strlen($row["description"]) > 80 ? "..." : ""))) ?>
                            </td>
                            <td class="table-cell cell-duration">
                                <strong><?= $row["duration_days"] ?></strong> days
                            </td>
                            <td class="table-cell cell-price">
                                <?= number_format($row["price_per_person"]) ?> VND
                            </td>
                            <td class="table-cell">
                                <span class="cell-season <?= getSeasonClass($row["season"]) ?>">
                                    <?= htmlspecialchars($row["season"]) ?>
                                </span>
                            </td>
                            <td class="table-cell cell-date">
                                <?= date('M d, Y', strtotime($row["created_at"])) ?><br>
                                <small><?= date('H:i', strtotime($row["created_at"])) ?></small>
                            </td>
                            <td class="table-cell cell-actions">
                                <a href="?edit=<?= $row["tourid"] ?>" class="action-link action-edit" onclick="openEditModal(<?= $row["tourid"] ?>); return false;">
                                    Edit
                                </a>
                                <form method="POST" action="controllers/deletetour.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this tour?\n\nTour: <?= htmlspecialchars($row['tour_name']) ?>\nThis action cannot be undone!')">
                                    <input type="hidden" name="tourid" value="<?= $row['tourid'] ?>">
                                    <button type="submit" class="action-link action-delete" style="border: none; background: none; cursor: pointer; padding: 0; color: inherit; font: inherit;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Navigation -->
        <div class="navigation">
            <button type="button" onclick="window.location.href='admindashboard.php'" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </button>
        </div>
    </div>

    <!-- Modal for Add/Edit Tour -->
    <div id="tourModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle"><?= $edit ? "Edit Tour" : "Add New Tour" ?></h2>
                <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            
            <form action="controllers/<?= $edit ? 'edittour.php' : 'addtour.php' ?>" method="post" class="modal-form" id="tourForm">
                <?php if ($edit) { ?>
                    <input type="hidden" name="tourid" value="<?= $edit["tourid"] ?>">
                <?php } ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="cityid">City</label>
                        <select id="modal-cityid" name="cityid" class="form-select" required>
                            <option value="">Select a city</option>
                            <?php foreach ($city_options as $id => $name) { ?>
                                <option value="<?= $id ?>" <?= $edit && $edit["cityid"] == $id ? "selected" : "" ?>>
                                    <?= "$id - $name" ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tour_name">Tour Name</label>
                        <input type="text" id="tour_name" name="tour_name" class="form-input" 
                               value="<?= $edit ? htmlspecialchars($edit["tour_name"]) : "" ?>" 
                               placeholder="Enter tour name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="duration_days">Duration (Days)</label>
                        <input type="number" id="duration_days" name="duration_days" class="form-input" 
                               value="<?= $edit ? $edit["duration_days"] : "" ?>" 
                               placeholder="Number of days" min="1" max="365" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="price_per_person">Price per Person (VND)</label>
                        <input type="number" id="modal-price_per_person" name="price_per_person" class="form-input" 
                               value="<?= $edit ? $edit["price_per_person"] : "" ?>" 
                               placeholder="Enter price" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="season">Season</label>
                        <select id="modal-season" name="season" class="form-select" required>
                            <option value="">Select a season</option>
                            <option value="Spring" <?= $edit && $edit["season"] == "Spring" ? "selected" : "" ?>>Spring</option>
                            <option value="Summer" <?= $edit && $edit["season"] == "Summer" ? "selected" : "" ?>>Summer</option>
                            <option value="Fall" <?= $edit && $edit["season"] == "Fall" ? "selected" : "" ?>>Fall</option>
                            <option value="Winter" <?= $edit && $edit["season"] == "Winter" ? "selected" : "" ?>>Winter</option>
                        </select>
                    </div>

                    <div class="form-group form-group-full">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" name="description" class="form-textarea" 
                                  placeholder="Enter tour description" required><?= $edit ? htmlspecialchars($edit["description"]) : "" ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="form-btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="form-btn btn-primary">
                        <?= $edit ? "Update Tour" : "Create Tour" ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('tourModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('tourModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            // Reset form if not editing
            if (!<?= $edit ? 'true' : 'false' ?>) {
                document.getElementById('tourForm').reset();
            }
        }

        function openEditModal(tourId) {
            window.location.href = '?edit=' + tourId;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('tourModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Open modal automatically if editing
        <?php if ($edit) { ?>
            openModal();
        <?php } ?>

        // Form validation
        const tourForm = document.getElementById('tourForm');
        if (tourForm) {
            tourForm.addEventListener('submit', function(e) {
                const tourName = document.getElementById('tour_name').value.trim();
                const duration = parseInt(document.getElementById('duration_days').value);
                const price = parseInt(document.getElementById('modal-price_per_person').value);
                
                if (tourName.length < 3) {
                    e.preventDefault();
                    alert('Tour name must be at least 3 characters long!');
                    return;
                }
                
                if (duration < 1 || duration > 365) {
                    e.preventDefault();
                    alert('Duration must be between 1 and 365 days!');
                    return;
                }
                
                if (price < 0) {
                    e.preventDefault();
                    alert('Price cannot be negative!');
                    return;
                }
            });
        }

        // Enhanced delete confirmation
        document.querySelectorAll('.action-delete').forEach(link => {
            link.addEventListener('click', function(e) {
                const confirmDelete = confirm('Are you sure you want to delete this tour?\n\nThis action cannot be undone!');
                if (!confirmDelete) {
                    e.preventDefault();
                }
            });
        });

        // Auto-format price input
        const priceInput = document.getElementById('modal-price_per_person');
        if (priceInput) {
            priceInput.addEventListener('blur', function(e) {
                if (e.target.value) {
                    e.target.value = parseInt(e.target.value);
                }
            });
        }
    </script>
</body>
</html>