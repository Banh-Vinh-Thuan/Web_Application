<?php
include '../dbconnect.php';

if (!isset($_GET['cityid'])) {
    echo "City ID not provided.";
    exit;
}

$cityId = $_GET['cityid'];
$cityData = [];

// Lấy thông tin journey hiện tại
$sql = "SELECT * FROM cities WHERE cityid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cityId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $cityData = $result->fetch_assoc();
} else {
    echo "Journey not found.";
    exit;
}
$stmt->close();

// Cập nhật thông tin
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $city = $_POST["city"];
    $region = $_POST["region"];
    $season = $_POST["season"];
    $days = $_POST["days"];
    $cost = $_POST["cost"];

    $updateSql = "UPDATE cities SET city = ?, region = ?, season = ?, days = ?, cost = ? WHERE cityid = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssssdi", $city, $region, $season, $days, $cost, $cityId);

    if ($stmt->execute()) {
        echo "<script>alert('Journey updated successfully.'); window.location.href='adminviewcity.php';</script>";
    } else {
        echo "Update failed: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../css/addjourney.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>Admin - Edit Journey</title>
    <style>
        body {
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <form method="post">
        <h2>Edit City </h2><hr width="50%">

        <label for="city">City:</label>
        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($cityData['city']); ?>" required><br><br>
        
        <label for="region">Region:</label>
        <input type="text" id="region" name="region" value="<?php echo htmlspecialchars($cityData['region']); ?>" required><br><br>

        <label for="season">Season:</label>
        <input type="text" id="season" name="season" value="<?php echo htmlspecialchars($cityData['season']); ?>" required><br><br>

        <label for="days">Days:</label>
        <input type="number" id="days" name="days" value="<?php echo htmlspecialchars($cityData['days']); ?>" required><br><br>

        <label for="cost">Cost:</label>
        <input type="number" id="cost" name="cost" value="<?php echo htmlspecialchars($cityData['cost']); ?>" required><br><br>

        <input type="submit" value="Update Journey">
        <a href="adminviewcity.php" style="margin-left: 15px;">Cancel</a>
    </form>
</body>
</html>
