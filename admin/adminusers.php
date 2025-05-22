<?php
include '../dbconnect.php';

// Fetch admin login data
$sql = "SELECT srno, Admin_Name FROM admin_login";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../css/adminusers.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>Admin Login Data</title>
</head>
<body>
    <a href="admindashboard.php" class="back-button">Back to Dashboard</a>
    <div class="table-container">
        <h2>All Admins</h2>
        <p class="small-font">(Total Admins: <?php echo $result->num_rows; ?>)</p>
        <table class="data-table">
            <tr>
                <th>Sr No.</th>
                <th>Admin Name</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['srno'] . "</td>";
                    echo "<td>" . $row['Admin_Name'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>No records found.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
