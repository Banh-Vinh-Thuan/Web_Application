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
    <title>Admin Users</title>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1>Admin Users</h1>
            <a href="admindashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </header>

        <!-- Main Content -->
        <main class="content">
            <!-- Stats -->
            <div class="stats-card">
                <div class="stats-label">Total Administrators</div>
                <div class="stats-value"><?php echo $result->num_rows; ?></div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Administrator List</h2>
                    <button class="refresh-btn" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Administrator Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['srno'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['Admin_Name']) . "</td>";
                                echo "<td><span class='status-badge'>Active</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr>";
                            echo "<td colspan='3' class='empty-state'>No administrators found</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2025 Admin Management System</p>
        </footer>
    </div>
</body>
</html>