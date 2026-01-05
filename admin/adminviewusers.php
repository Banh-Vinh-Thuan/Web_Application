<?php
include '../dbconnect.php';

$db = new PDO('mysql:host=localhost;dbname=travelscapes', 'root', '4444');

$sql = 'SELECT * FROM login';
$stmt = $db->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll();

$totalUsers = count($results);

$db = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/adminviewusers.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>View Users</title>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Users List</h1>
            <a href="admindashboard.php" class="back-btn">Back to Dashboard</a>
        </div>

        <div class="info-bar">
            <div class="total-count">
                <span class="count-number"><?php echo $totalUsers; ?></span>
                <span class="count-label">Total Users</span>
            </div>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by email or username...">
            </div>
        </div>

        <div class="table-wrapper">
            <table class="users-table" id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Username</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php
                    $serialNumber = 1;
                    foreach ($results as $row):
                    ?>
                    <tr class="user-row">
                        <td class="user-id"><?php echo $serialNumber; ?></td>
                        <td><?php echo htmlspecialchars($row['usersEmail']); ?></td>
                        <td><?php echo htmlspecialchars($row['usersuid']); ?></td>
                    </tr>
                    <?php
                    $serialNumber++;
                    endforeach;
                    ?>
                </tbody>
            </table>

            <div id="noResults" class="no-results">
                <p>No users found matching your search.</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.user-row');
            const noResults = document.getElementById('noResults');
            const userTable = document.getElementById('userTable');
            let visibleRows = 0;

            tableRows.forEach(row => {
                const email = row.cells[1].textContent.toLowerCase();
                const username = row.cells[2].textContent.toLowerCase();
                
                if (email.includes(searchTerm) || username.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (visibleRows === 0) {
                userTable.style.display = 'none';
                noResults.style.display = 'block';
            } else {
                userTable.style.display = 'table';
                noResults.style.display = 'none';
            }
        });
    </script>
</body>
</html>