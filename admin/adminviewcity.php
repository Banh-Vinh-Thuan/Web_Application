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
<html>
<head>
    <link rel="stylesheet" href="../css/adminviewcity.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>Manage Cities</title>
    <style>
        .back-red {
            display: block;
            margin: 30px auto;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            width: fit-content;
        }
        .back-red:hover {
            background-color: #c0392b;
        }
    </style>
    <script>
        function toggleDropdown(filterName) {
            var dropdownContent = document.getElementById(filterName + "Dropdown");
            dropdownContent.style.display = dropdownContent.style.display === "block" ? "none" : "block";
        }
    </script>
</head>
<body>

    <h1>City Management</h1>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <div class="filter-container">
            <h3>Filters</h3>
            <div class="filter-box">
                <!-- Region Filter -->
                <div class="custom-dropdown">
                    <span onclick="toggleDropdown('region')">Region</span>
                    <div id="regionDropdown" class="custom-dropdown-content">
                        <?php
                        $regions = ["All", "North", "North West", "South", "South East", "Central", "Mekong Delta"];
                        foreach ($regions as $region) {
                            $checked = in_array($region, $selectedRegions) ? "checked" : "";
                            echo "<label><input type='checkbox' name='region[]' value='$region' $checked>$region</label>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <div style="display: flex; align-items: center; padding-left: 10px;">
                    <input type="submit" value="Filter" class="filter-submit">
                </div>
            </div>
        </div>
    </form>
    <br>
    <h3>Available Cities</h3>
    <table border="1">
        <tr>
            <th>City ID</th>
            <th>City Name</th>
            <th>Region</th>
            <th>Action</th>
        </tr>
        <?php foreach ($data as $row): ?>
        <tr>
            <td><?= $row["cityid"] ?></td>
            <td><?= $row["city"] ?></td>
            <td><?= $row["region"] ?></td>
            <td>
                <a href='editcity.php?cityid=<?= $row["cityid"] ?>' class='edit-button'>Edit</a> |
                <a href='?delete=<?= $row["cityid"] ?>' class='delete-button'>Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php
    if (isset($_GET["delete"])) {
        echo "<div id='successMessage' class='success-message'>Journey deleted successfully.</div>";
    }
    ?>

    <br><br>
    <a href="addcity.php" class="insert-button">+ Add Journey</a>

    <a class="back-red" href="admindashboard.php">Back to Dashboard</a>

</body>
</html>