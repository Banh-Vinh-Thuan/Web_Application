<?php
include '../dbconnect.php';

$sql = "SELECT cityid, city FROM cities ORDER BY cityid ASC";
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
    <title>View Cities</title>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cities List</h1>
            <a href="admindashboard.php" class="back-btn">Back to Dashboard</a>
        </div>

        <div class="table-wrapper">
            <?php if (empty($data)): ?>
            <div class="no-data">
                <p>No cities found in the database.</p>
            </div>
            <?php else: ?>
            <table class="cities-table">
                <thead>
                    <tr>
                        <th>City ID</th>
                        <th>City Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= $row["cityid"] ?></td>
                        <td><?= htmlspecialchars($row["city"]) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>