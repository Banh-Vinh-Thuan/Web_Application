<?php
include '../dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tourid = $_POST["tourid"];
    $cityid = $_POST["cityid"];
    $tour_name = $_POST["tour_name"];
    $description = $_POST["description"];
    $duration_days = $_POST["duration_days"];
    $price_per_person = $_POST["price_per_person"];
    $season = $_POST["season"];

    if ($tourid) {
        $stmt = $conn->prepare("UPDATE tours SET cityid=?, tour_name=?, description=?, duration_days=?, price_per_person=?, season=? WHERE tourid=?");
        $stmt->bind_param("issiisi", $cityid, $tour_name, $description, $duration_days, $price_per_person, $season, $tourid);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO tours (cityid, tour_name, description, duration_days, price_per_person, season) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issiis", $cityid, $tour_name, $description, $duration_days, $price_per_person, $season);
        $stmt->execute();
    }
    header("Location: adminviewtour.php");
    exit();
}

if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $conn->query("DELETE FROM tours WHERE tourid = $id");
    header("Location: adminviewtour.php");
    exit();
}

$tours = $conn->query("SELECT * FROM tours");
$cities = $conn->query("SELECT cityid, city FROM cities");
$city_options = [];
while ($row = $cities->fetch_assoc()) {
    $city_options[$row['cityid']] = $row['city'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Tour Management</title>
    <link rel="stylesheet" type="text/css" href="../css/adminviewtour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<h2>Tour List</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>City</th>
            <th>Tour Name</th>
            <th>Description</th>
            <th>Duration (days)</th>
            <th>Price/Person</th>
            <th>Season</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $tours->fetch_assoc()) { ?>
        <tr>
            <td><?= $row["tourid"] ?></td>
            <td><?= $row["cityid"] ?> - <?= htmlspecialchars($city_options[$row["cityid"]] ?? "Unknown") ?></td>
            <td><?= htmlspecialchars($row["tour_name"]) ?></td>
            <td><?= nl2br(htmlspecialchars($row["description"])) ?></td>
            <td><?= $row["duration_days"] ?></td>
            <td><?= number_format($row["price_per_person"]) ?></td>
            <td><?= htmlspecialchars($row["season"]) ?></td>
            <td><?= $row["created_at"] ?></td>
            <td>
                <a href="?edit=<?= $row["tourid"] ?>">Edit</a> |
                <a href="?delete=<?= $row["tourid"] ?>" onclick="return confirm('Are you sure you want to delete this tour?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php
$edit = null;
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $edit = $conn->query("SELECT * FROM tours WHERE tourid = $id")->fetch_assoc();
}
?>

<div class="form-container">
    <h2 style="text-align:center; margin-bottom: 30px;">
      <?= $edit ? "Edit Tour #" . $edit["tourid"] : "Add New Tour" ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="tourid" value="<?= $edit["tourid"] ?? '' ?>">

        <label>City:</label>
        <select name="cityid" required>
            <?php foreach ($city_options as $id => $name): ?>
                <option value="<?= $id ?>" <?= ($edit["cityid"] ?? '') == $id ? 'selected' : '' ?>>
                    <?= htmlspecialchars("$id - $name") ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Tour Name:</label>
        <input name="tour_name" type="text" value="<?= htmlspecialchars($edit["tour_name"] ?? '') ?>" required>

        <label>Description:</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($edit["description"] ?? '') ?></textarea>

        <label>Duration (days):</label>
        <input type="number" name="duration_days" value="<?= $edit["duration_days"] ?? '' ?>" required>

        <label>Price per Person:</label>
        <input type="number" name="price_per_person" value="<?= $edit["price_per_person"] ?? '' ?>" required>

        <label>Season:</label>
        <input name="season" type="text" value="<?= htmlspecialchars($edit["season"] ?? '') ?>" required>

        <input type="submit" value="<?= $edit ? "Update Tour" : "Add Tour" ?>">
    </form>
</div>

<button class="btn-back" onclick="window.location.href='admindashboard.php'">Back to Dashboard</button>

</body>
</html>
