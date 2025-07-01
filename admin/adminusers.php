<?php
include '../dbconnect.php';

// Fetch admin login data
$sql = "SELECT srno, Admin_Name FROM admin_login";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/adminusers.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Admin Management Dashboard</title>
</head>
<body>
    <div class="page-wrapper">
        <!-- Header Section -->
        <header class="page-header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fas fa-users-cog"></i>
                    Admin Management
                </h1>
                <a href="admindashboard.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-card">
                <!-- Stats Header -->
                <div class="stats-header">
                    <div class="stats-item">
                        <div class="stats-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="stats-info">
                            <h3>Total Administrators</h3>
                            <span class="stats-number"><?php echo $result->num_rows; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="table-section">
                    <div class="table-header">
                        <h2>
                            <i class="fas fa-list"></i>
                            Administrator List
                        </h2>
                        <div class="table-actions">
                            <button class="action-btn refresh-btn" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>
                                        <i class="fas fa-hashtag"></i>
                                        Serial No.
                                    </th>
                                    <th>
                                        <i class="fas fa-user"></i>
                                        Administrator Name
                                    </th>
                                    <th>
                                        <i class="fas fa-cog"></i>
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    $counter = 1;
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr class='table-row'>";
                                        echo "<td class='serial-cell'>" . sprintf("%03d", $row['srno']) . "</td>";
                                        echo "<td class='name-cell'>";
                                        echo "<div class='admin-info'>";
                                        echo "<div class='admin-avatar'>";
                                        echo "<i class='fas fa-user-tie'></i>";
                                        echo "</div>";
                                        echo "<span class='admin-name'>" . htmlspecialchars($row['Admin_Name']) . "</span>";
                                        echo "</div>";
                                        echo "</td>";
                                        echo "<td class='status-cell'>";
                                        echo "<span class='status-badge active'>";
                                        echo "<i class='fas fa-circle'></i> Active";
                                        echo "</span>";
                                        echo "</td>";
                                        echo "</tr>";
                                        $counter++;
                                    }
                                } else {
                                    echo "<tr class='empty-row'>";
                                    echo "<td colspan='3' class='empty-cell'>";
                                    echo "<div class='empty-state'>";
                                    echo "<i class='fas fa-inbox'></i>";
                                    echo "<h3>No Administrators Found</h3>";
                                    echo "<p>There are currently no administrator records in the system.</p>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="page-footer">
            <p>&copy; 2025 Admin Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>