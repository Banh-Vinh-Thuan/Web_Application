<?php
include '../dbconnect.php';

if (isset($_GET["delete"])) {
    $journeyIdToDelete = $_GET["delete"];
    $sql = "DELETE FROM cities WHERE cityid = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $journeyIdToDelete);
        $stmt->execute();
        $stmt->close();
    }
}

$selectedRegions = $_POST['region'] ?? ["All"];

$sql = "SELECT * FROM cities WHERE 1=1";

if (!in_array("All", $selectedRegions)) {
    $sql .= " AND region IN ('" . implode("','", $selectedRegions) . "')";
}

$result = $conn->query($sql);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/adminviewcity.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Manage Cities</title>
    <script>
        function toggleDropdown(filterName) {
            var dropdownContent = document.getElementById(filterName + "Dropdown");
            var isVisible = dropdownContent.style.display === "block";
            
            // Close all dropdowns first
            var allDropdowns = document.querySelectorAll('.custom-dropdown-content');
            allDropdowns.forEach(function(dropdown) {
                dropdown.style.display = "none";
            });
            
            // Toggle the clicked dropdown
            dropdownContent.style.display = isVisible ? "none" : "block";
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown-trigger')) {
                var dropdowns = document.querySelectorAll('.custom-dropdown-content');
                dropdowns.forEach(function(dropdown) {
                    dropdown.style.display = "none";
                });
            }
        }

        function confirmDelete(cityName) {
            return confirm(`Are you sure you want to delete "${cityName}"? This action cannot be undone.`);
        }

        // Auto-hide success message
        window.onload = function() {
            var successMessage = document.getElementById('successMessage');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.opacity = '0';
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 300);
                }, 3000);
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="header-content">
                <i class="fas fa-city header-icon"></i>
                <div class="header-text">
                    <h1>City Management</h1>
                    <p>Manage and organize cities across different regions</p>
                </div>
            </div>
            <a href="admindashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <!-- Success Message -->
        <?php if (isset($_GET["delete"])): ?>
        <div id="successMessage" class="success-message">
            <i class="fas fa-check-circle"></i>
            <span>Journey deleted successfully.</span>
        </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="filter-container">
                    <div class="filter-header">
                        <i class="fas fa-filter"></i>
                        <h3>Filter Cities</h3>
                    </div>
                    
                    <div class="filter-controls">
                        <!-- Region Filter -->
                        <div class="custom-dropdown">
                            <div class="dropdown-trigger" onclick="toggleDropdown('region')">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Select Region</span>
                                <i class="fas fa-chevron-down arrow"></i>
                            </div>
                            <div id="regionDropdown" class="custom-dropdown-content">
                                <div class="dropdown-header">Choose Regions</div>
                                <?php
                                $regions = ["All", "North", "North West", "South", "South East", "Central", "Mekong Delta"];
                                
                                // Show selected regions first
                                $sortedRegions = [];
                                foreach ($selectedRegions as $selected) {
                                    if (in_array($selected, $regions)) {
                                        $sortedRegions[] = $selected;
                                    }
                                }
                                // Add remaining regions
                                foreach ($regions as $region) {
                                    if (!in_array($region, $sortedRegions)) {
                                        $sortedRegions[] = $region;
                                    }
                                }
                                
                                foreach ($sortedRegions as $region) {
                                    $checked = in_array($region, $selectedRegions) ? "checked" : "";
                                    echo "<label class='checkbox-label'>
                                            <input type='checkbox' name='region[]' value='$region' $checked>
                                            <span class='checkmark'></span>
                                            <span class='label-text'>$region</span>
                                          </label>";
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="filter-submit">
                            <i class="fas fa-search"></i>
                            <span>Apply Filters</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-map"></i>
                </div>
                <div class="stat-info">
                    <h3><?= count(array_unique(array_column($data, 'region'))) ?></h3>
                    <p>Regions</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-city"></i>
                </div>
                <div class="stat-info">
                    <h3><?= count($data) ?></h3>
                    <p>Total Cities</p>
                </div>
            </div>
        </div>

        <!-- Action Section -->
        <div class="action-section">
            <a href="addcity.php" class="add-button">
                <i class="fas fa-plus"></i>
                <span>+ Add Journey</span>
            </a>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="table-header">
                <h3><i class="fas fa-list"></i> Cities List</h3>
            </div>
            
            <?php if (empty($data)): ?>
            <div class="no-data">
                <i class="fas fa-search"></i>
                <h3>No Cities Found</h3>
                <p>No cities match your current filter criteria.</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="cities-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-city"></i> City Name</th>
                            <th><i class="fas fa-map-marker-alt"></i> Region</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <tr class="table-row">
                            <td class="id-cell"><?= $row["cityid"] ?></td>
                            <td class="city-cell">
                                <div class="city-info">
                                    <span class="city-name"><?= htmlspecialchars($row["city"]) ?></span>
                                </div>
                            </td>
                            <td class="region-cell">
                                <span class="region-badge"><?= htmlspecialchars($row["region"]) ?></span>
                            </td>
                            <td class="action-cell">
                                <div class="action-buttons">
                                    <a href='editcity.php?cityid=<?= $row["cityid"] ?>' class='edit-btn' title="Edit City">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </a>
                                    <a href='?delete=<?= $row["cityid"] ?>' 
                                       class='delete-btn' 
                                       title="Delete City"
                                       onclick="return confirmDelete('<?= htmlspecialchars($row["city"]) ?>')">
                                        <i class="fas fa-trash"></i>
                                        <span>Delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>