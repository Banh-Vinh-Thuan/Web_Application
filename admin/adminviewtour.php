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
    <title>Professional Tour Management System</title>
    <link rel="stylesheet" type="text/css" href="../css/adminviewtour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="main-container">
        <!-- Header Section -->
        <header class="page-header">
            <h1 class="page-title">Tour Management Center</h1>
            <p class="page-subtitle">Manage and organize your travel experiences</p>
        </header>

        <!-- Filter Section -->
        <div class="filter-container">
            <form method="GET" class="loading">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label class="filter-label" for="cityid">🏙️ Select City</label>
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
                        <label class="filter-label" for="price_per_person">💰 Price Range</label>
                        <select id="price_per_person" name="price_per_person" class="filter-select">
                            <option value="">All Prices</option>
                            <option value="under5m" <?= isset($filters['price_per_person']) && $filters['price_per_person'] == 'under5m' ? "selected" : "" ?>>Under 5,000,000 VND</option>
                            <option value="5m-10m" <?= isset($filters['price_per_person']) && $filters['price_per_person'] == '5m-10m' ? "selected" : "" ?>>5,000,000 - 10,000,000 VND</option>
                            <option value="above10m" <?= isset($filters['price_per_person']) && $filters['price_per_person'] == 'above10m' ? "selected" : "" ?>>Above 10,000,000 VND</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="season">🌟 Season</label>
                        <select id="season" name="season" class="filter-select">
                            <option value="">All Seasons</option>
                            <option value="Spring" <?= isset($filters['season']) && $filters['season'] == 'Spring' ? "selected" : "" ?>>🌸 Spring</option>
                            <option value="Summer" <?= isset($filters['season']) && $filters['season'] == 'Summer' ? "selected" : "" ?>>☀️ Summer</option>
                            <option value="Fall" <?= isset($filters['season']) && $filters['season'] == 'Fall' ? "selected" : "" ?>>🍂 Fall</option>
                            <option value="Winter" <?= isset($filters['season']) && $filters['season'] == 'Winter' ? "selected" : "" ?>>❄️ Winter</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <button type="submit" class="filter-btn hover-lift">
                            🔍 Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tour List Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="tours-table loading">
                    <thead class="table-header">
                        <tr>
                            <th>ID</th>
                            <th>City Information</th>
                            <th>Tour Details</th>
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
                                <span class="status-indicator status-active"></span>
                                #<?= $row["tourid"] ?>
                            </td>
                            <td class="table-cell cell-city">
                                <strong><?= $row["cityid"] ?></strong><br>
                                <small><?= htmlspecialchars($city_options[$row["cityid"]] ?? "Unknown") ?></small>
                            </td>
                            <td class="table-cell cell-name">
                                <strong><?= htmlspecialchars($row["tour_name"]) ?></strong>
                            </td>
                            <td class="table-cell cell-description">
                                <?= nl2br(htmlspecialchars(substr($row["description"], 0, 100) . (strlen($row["description"]) > 100 ? "..." : ""))) ?>
                            </td>
                            <td class="table-cell cell-duration">
                                <strong><?= $row["duration_days"] ?></strong><br>
                                <small>days</small>
                            </td>
                            <td class="table-cell cell-price">
                                <?= number_format($row["price_per_person"]) ?><br>
                                <small>VND</small>
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
                                <a href="?edit=<?= $row["tourid"] ?>" class="action-link action-edit hover-lift">
                                    ✏️ Edit
                                </a>
                                <a href="controllers/delete.php?delete=<?= $row["tourid"] ?>" 
                                   class="action-link action-delete hover-lift" 
                                   onclick="return confirm('⚠️ Are you sure you want to delete this tour?\n\nTour: <?= htmlspecialchars($row["tour_name"]) ?>\nThis action cannot be undone!')">
                                    🗑️ Delete
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Form -->
        <div class="form-container">
            <h2 class="form-title">
                <?= $edit ? "✏️ Edit Tour #" . $edit["tourid"] : "➕ Add New Tour" ?>
            </h2>
            
            <form method="POST" action="<?= $edit ? 'controllers/edittour.php' : 'controllers/addtour.php' ?>" class="loading">
                <input type="hidden" name="tourid" value="<?= $edit["tourid"] ?? '' ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="cityid">🏙️ Select City</label>
                        <select id="cityid" name="cityid" class="form-select" required>
                            <option value="">Choose a city...</option>
                            <?php foreach ($city_options as $id => $name) { ?>
                                <option value="<?= $id ?>" <?= ($edit["cityid"] ?? '') == $id ? "selected" : "" ?>>
                                    <?= "$id - $name" ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tour_name">🎯 Tour Name</label>
                        <input id="tour_name" 
                               name="tour_name" 
                               type="text" 
                               value="<?= htmlspecialchars($edit["tour_name"] ?? '') ?>" 
                               class="form-input" 
                               placeholder="Enter tour name..."
                               required />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="duration_days">⏱️ Duration (Days)</label>
                        <input id="duration_days" 
                               name="duration_days" 
                               type="number" 
                               value="<?= $edit["duration_days"] ?? '' ?>" 
                               class="form-input" 
                               min="1"
                               placeholder="Enter duration..."
                               required />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="price_per_person">💰 Price per Person (VND)</label>
                        <input id="price_per_person" 
                               name="price_per_person" 
                               type="number" 
                               value="<?= $edit["price_per_person"] ?? '' ?>" 
                               class="form-input" 
                               min="0"
                               placeholder="Enter price..."
                               required />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="season">🌟 Best Season</label>
                        <select id="season" name="season" class="form-select" required>
                            <option value="">Choose season...</option>
                            <option value="Spring" <?= ($edit["season"] ?? '') == 'Spring' ? "selected" : "" ?>>🌸 Spring</option>
                            <option value="Summer" <?= ($edit["season"] ?? '') == 'Summer' ? "selected" : "" ?>>☀️ Summer</option>
                            <option value="Fall" <?= ($edit["season"] ?? '') == 'Fall' ? "selected" : "" ?>>🍂 Fall</option>
                            <option value="Winter" <?= ($edit["season"] ?? '') == 'Winter' ? "selected" : "" ?>>❄️ Winter</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">📝 Tour Description</label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-textarea" 
                                  placeholder="Describe the tour experience, highlights, and what makes it special..."
                                  rows="5"><?= htmlspecialchars($edit["description"] ?? '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="form-btn btn-primary hover-lift">
                            <?= $edit ? "💾 Update Tour" : "➕ Create Tour" ?>
                        </button>
                        <?php if ($edit) { ?>
                            <button type="button" onclick="window.location.href='adminviewtour.php'" class="form-btn btn-secondary hover-lift">
                                ❌ Cancel Edit
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Navigation -->
        <div class="navigation">
            <button type="button" 
                    onclick="window.location.href='admindashboard.php'" 
                    class="form-btn btn-secondary hover-lift">
                🏠 Back to Dashboard
            </button>
        </div>
    </div>

    <!-- JavaScript for Enhanced UX -->
    <script>
        // Add loading animations
        document.addEventListener('DOMContentLoaded', function() {
            const loadingElements = document.querySelectorAll('.loading');
            loadingElements.forEach((element, index) => {
                setTimeout(() => {
                    element.style.opacity = '1';
                }, index * 200);
            });
        });

        // Enhanced form validation
        const form = document.querySelector('form[action*="tour.php"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const tourName = document.getElementById('tour_name').value.trim();
                const duration = document.getElementById('duration_days').value;
                const price = document.getElementById('price_per_person').value;
                
                if (tourName.length < 3) {
                    e.preventDefault();
                    alert('⚠️ Tour name must be at least 3 characters long!');
                    return;
                }
                
                if (duration < 1 || duration > 365) {
                    e.preventDefault();
                    alert('⚠️ Duration must be between 1 and 365 days!');
                    return;
                }
                
                if (price < 0) {
                    e.preventDefault();
                    alert('⚠️ Price cannot be negative!');
                    return;
                }
            });
        }

        // Auto-format price input
        const priceInput = document.getElementById('price_per_person');
        if (priceInput) {
            priceInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/,/g, '');
                if (value && !isNaN(value)) {
                    e.target.value = parseInt(value).toLocaleString();
                }
            });
        }

        // Enhanced delete confirmation
        document.querySelectorAll('.action-delete').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const tourName = this.closest('tr').querySelector('.cell-name strong').textContent;
                
                if (confirm(`⚠️ PERMANENT DELETION WARNING\n\nYou are about to delete:\n"${tourName}"\n\nThis action cannot be undone!\n\nAre you absolutely sure?`)) {
                    window.location.href = this.href;
                }
            });
        });

        // Add smooth scrolling to form when editing
        if (window.location.search.includes('edit=')) {
            setTimeout(() => {
                document.querySelector('.form-container').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 500);
        }

        // Dynamic table row highlighting
        document.querySelectorAll('.table-row').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 4px 20px rgba(49, 130, 206, 0.15)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.boxShadow = '';
            });
        });

        // Filter form enhancement
        const filterForm = document.querySelector('.filter-container form');
        if (filterForm) {
            // Auto-submit on change (optional)
            const selects = filterForm.querySelectorAll('select');
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    // Uncomment the line below for auto-submit functionality
                    // filterForm.submit();
                });
            });
        }

        // Add success/error message handling
        function showMessage(message, type = 'success') {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-${type}`;
            messageDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                z-index: 1000;
                animation: slideInRight 0.3s ease-out;
                background: ${type === 'success' ? 'linear-gradient(135deg, #48bb78, #38a169)' : 'linear-gradient(135deg, #e53e3e, #c53030)'};
            `;
            messageDiv.textContent = message;
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => messageDiv.remove(), 300);
            }, 3000);
        }

        // Check for URL parameters to show messages
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('success')) {
            showMessage('✅ Operation completed successfully!', 'success');
        }
        if (urlParams.get('error')) {
            showMessage('❌ An error occurred. Please try again.', 'error');
        }
    </script>

    <!-- Additional CSS for animations -->
    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Loading state for elements */
        .loading {
            opacity: 0;
            transition: opacity 0.5s ease-out;
        }

        /* Custom scrollbar */
        .table-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #3182ce, #2b77cb);
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #2b77cb, #2c5aa0);
        }

        /* Enhanced focus states */
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            transform: translateY(-1px);
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1), 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Print styles */
        @media print {
            body {
                background: white;
            }
            
            .filter-container,
            .form-container,
            .navigation,
            .cell-actions {
                display: none;
            }
            
            .table-container {
                background: white;
                box-shadow: none;
            }
        }
    </style>
</body>
</html>