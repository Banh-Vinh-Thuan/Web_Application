<?php
include '../dbconnect.php';

$db = new PDO('mysql:host=localhost;dbname=travelscapes', 'root', '4444');

$sql = 'SELECT * FROM login';
$stmt = $db->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll();

// Get user count
$totalUsers = count($results);

// Close the database connection
$db = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/adminviewusers.css">
    <title>Admin Dashboard - User Management | TravelScapes</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <meta name="description" content="Admin dashboard for managing TravelScapes users">
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h1>User Management Dashboard</h1>
            <p class="subtitle">Monitor and manage all registered users in TravelScapes</p>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number" id="userCount"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="search-container">
                    <input type="text" id="searchInput" class="search-box" placeholder="Search users by email or UID...">
                    <span class="search-icon">🔍</span>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <table class="user-table" id="userTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Email Address</th>
                        <th>User Identifier</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php
                    $serialNumber = 1; 
                    foreach ($results as $row):
                    ?>
                    <tr class="user-row">
                        <td>
                            <span class="user-id"><?php echo $serialNumber; ?></span>
                        </td>
                        <td>
                            <span class="user-email"><?php echo htmlspecialchars($row['usersEmail']); ?></span>
                        </td>
                        <td>
                            <span class="user-uid"><?php echo htmlspecialchars($row['usersuid']); ?></span>
                        </td>
                    </tr>
                    <?php
                    $serialNumber++; 
                    endforeach;
                    ?>
                </tbody>
            </table>
            
            <!-- No results message (hidden by default) -->
            <div id="noResults" style="display: none; text-align: center; padding: 40px; color: #64748b; font-size: 16px;">
                <p>🔍 No users found matching your search criteria.</p>
                <p style="font-size: 14px; margin-top: 10px;">Try adjusting your search terms.</p>
            </div>
        </div>
    </div>

    <!-- Scroll to top button -->
    <button class="scroll-top" id="scrollTop" onclick="scrollToTop()">↑</button>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.user-row');
            const noResults = document.getElementById('noResults');
            const userTable = document.getElementById('userTable');
            let visibleRows = 0;

            tableRows.forEach(row => {
                const email = row.querySelector('.user-email').textContent.toLowerCase();
                const uid = row.querySelector('.user-uid').textContent.toLowerCase();
                
                if (email.includes(searchTerm) || uid.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleRows === 0 && searchTerm !== '') {
                userTable.style.display = 'none';
                noResults.style.display = 'block';
            } else {
                userTable.style.display = 'table';
                noResults.style.display = 'none';
            }
        });

        // Scroll to top functionality
        window.onscroll = function() {
            const scrollTop = document.getElementById('scrollTop');
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                scrollTop.classList.add('show');
            } else {
                scrollTop.classList.remove('show');
            }
        };

        function scrollToTop() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
        }

        // Add loading animation when page loads
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.3s ease';
            setTimeout(function() {
                document.body.style.opacity = '1';
            }, 100);
        });

        // Add row hover effect enhancement
        document.querySelectorAll('.user-row').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.01)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Add click effect for table rows
        document.querySelectorAll('.user-row').forEach(row => {
            row.addEventListener('click', function() {
                // Remove active class from all rows
                document.querySelectorAll('.user-row').forEach(r => r.classList.remove('active'));
                // Add active class to clicked row
                this.classList.add('active');
            });
        });
    </script>

    <style>
        /* Additional styles for active row */
        .user-row.active {
            background: linear-gradient(90deg, #dbeafe 0%, #bfdbfe 100%) !important;
            border-left: 4px solid #3b82f6;
        }
        
        .user-row.active td {
            color: #1e40af !important;
            font-weight: 500;
        }
    </style>
</body>
</html>