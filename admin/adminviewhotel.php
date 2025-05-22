<?php
include '../dbconnect.php';

// Handle add/update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hotelid = $_POST["hotelid"];
    $hotel = $_POST["hotel"];
    $cityid = $_POST["cityid"];
    $cost = $_POST["cost"];
    $amenities = $_POST["amenities"];
    $ratings = $_POST["ratings"];

    if ($hotelid) {
        $stmt = $conn->prepare("UPDATE hotels SET hotel=?, cityid=?, cost=?, amenities=?, ratings=? WHERE hotelid=?");
        $stmt->bind_param("sidsii", $hotel, $cityid, $cost, $amenities, $ratings, $hotelid);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO hotels (hotel, cityid, cost, amenities, ratings) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sidsi", $hotel, $cityid, $cost, $amenities, $ratings);
        $stmt->execute();
    }

    header("Location: adminviewhotel.php");
    exit();
}

// Handle delete
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $conn->query("DELETE FROM hotels WHERE hotelid = $id");
    header("Location: adminviewhotel.php");
    exit();
}

// Fetch hotels
$result = $conn->query("SELECT * FROM hotels");

// Fetch cities for dropdown
$cities_result = $conn->query("SELECT cityid, city FROM cities");
$city_options = [];
while ($city = $cities_result->fetch_assoc()) {
    $city_options[$city['cityid']] = $city['city'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Hotels</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="../css/adminviewhotel.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-3xl font-bold mb-6 text-center">Hotel List</h2>
        <div class="overflow-x-auto mb-10">
            <table class="min-w-full bg-white border border-gray-300 shadow-sm rounded-lg text-sm">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="p-3 border">ID</th>
                        <th class="p-3 border">Hotel Name</th>
                        <th class="p-3 border">City</th>
                        <th class="p-3 border">Cost</th>
                        <th class="p-3 border">Amenities</th>
                        <th class="p-3 border">Ratings</th>
                        <th class="p-3 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 border text-center"><?= $row["hotelid"] ?></td>
                        <td class="p-3 border"><?= htmlspecialchars($row["hotel"]) ?></td>
                        <td class="p-3 border"><?= $row["cityid"] ?> - <?= $city_options[$row["cityid"]] ?? "Unknown" ?></td>
                        <td class="p-3 border text-right"><?= number_format($row["cost"]) ?></td>
                        <td class="p-3 border"><?= nl2br(htmlspecialchars($row["amenities"])) ?></td>
                        <td class="p-3 border text-center"><?= $row["ratings"] ?></td>
                        <td class="p-3 border text-center">
                            <a href="?edit=<?= $row["hotelid"] ?>" class="text-blue-600 hover:underline mr-2">Edit</a> |
                            <a href="?delete=<?= $row["hotelid"] ?>" class="text-red-600 hover:underline ml-2" onclick="return confirm('Delete this hotel?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <?php
        $edit = null;
        if (isset($_GET["edit"])) {
            $id = (int)$_GET["edit"];
            $edit = $conn->query("SELECT * FROM hotels WHERE hotelid = $id")->fetch_assoc();
        }
        ?>

        <div class="max-w-2xl mx-auto bg-white p-10 rounded-lg shadow-md">

            <h2 class="text-2xl font-bold mb-6 text-center"><?= $edit ? "Edit Hotel #" . $edit["hotelid"] : "Add New Hotel" ?></h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="hotelid" value="<?= $edit["hotelid"] ?? '' ?>">

                <div>
                    <label class="block mb-1 font-semibold" for="hotel">Hotel Name</label>
                    <input id="hotel" name="hotel" type="text" value="<?= htmlspecialchars($edit["hotel"] ?? '') ?>" class="w-full border border-gray-300 px-3 py-2 rounded" required />
                </div>

                <div>
                    <label class="block mb-1 font-semibold" for="cityid">City</label>
                    <select id="cityid" name="cityid" class="w-full border border-gray-300 px-3 py-2 rounded" required>
                        <?php foreach ($city_options as $id => $name) { ?>
                            <option value="<?= $id ?>" <?= ($edit["cityid"] ?? '') == $id ? "selected" : "" ?>>
                                <?= "$id - $name" ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 font-semibold" for="cost">Cost</label>
                    <input id="cost" name="cost" type="number" value="<?= $edit["cost"] ?? '' ?>" class="w-full border border-gray-300 px-3 py-2 rounded" required />
                </div>

                <div>
                    <label class="block mb-1 font-semibold" for="amenities">Amenities</label>
                    <textarea id="amenities" name="amenities" class="w-full border border-gray-300 px-3 py-2 rounded" rows="3"><?= htmlspecialchars($edit["amenities"] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block mb-1 font-semibold" for="ratings">Ratings (1-5)</label>
                    <input id="ratings" name="ratings" type="number" min="1" max="5" value="<?= $edit["ratings"] ?? '' ?>" class="w-full border border-gray-300 px-3 py-2 rounded" required />
                </div>

                <div class="flex justify-between pt-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                        <?= $edit ? "Update" : "Add New" ?>
                    </button>
                </div>
            </form>
        </div>


        <div class="text-center mt-10">
            <button type="button" onclick="window.location.href='admindashboard.php'" class="back-button">
                Back to Dashboard
            </button>
        </div>
    </div>
</body>
</html>
