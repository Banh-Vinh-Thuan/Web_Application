<?php
// Database connection parameters
include 'dbconnect.php';

// Initialize variables
$cityId = isset($_GET['city_id']) ? $_GET['city_id'] : null;

// Fetch the city name
$cityName = '';
if ($cityId) {
    $citySql = "SELECT city FROM cities WHERE cityid = $cityId";
    $cityResult = $conn->query($citySql);

    if ($cityResult->num_rows > 0) {
        $row = $cityResult->fetch_assoc();
        $cityName = $row['city'];
    }
}

// Initialize filter variables, prioritizing GET parameters for initial load
$selectedCost = isset($_GET['cost']) && $_GET['cost'] !== 'All' ? $_GET['cost'] : (isset($_POST['cost']) ? $_POST['cost'] : 'All');
$selectedRatings = isset($_GET['ratings']) && $_GET['ratings'] !== 'All' ? $_GET['ratings'] : (isset($_POST['ratings']) ? $_POST['ratings'] : 'All');

// Filter SQL conditions
$costCondition = '';
$ratingsCondition = '';

if ($selectedCost !== 'All') {
    switch ($selectedCost) {
        case 'less':
            $costCondition = 'cost < 1000000';
            break;
        case 'between':
            $costCondition = 'cost >= 1000000 AND cost <= 2000000';
            break;
        case 'above':
            $costCondition = 'cost > 2000000';
            break;
    }
}

if ($selectedRatings !== 'All') {
    switch ($selectedRatings) {
        case '1star':
            $ratingsCondition = 'ratings = 1';
            break;
        case '2star':
            $ratingsCondition = 'ratings = 2';
            break;
        case '3star':
            $ratingsCondition = 'ratings = 3';
            break;
        case '4star':
            $ratingsCondition = 'ratings = 4';
            break;
        case '5star':
            $ratingsCondition = 'ratings = 5';
            break;
    }
}

// Build the SQL query
$sql = "SELECT * FROM hotels WHERE cityid = $cityId";
if ($costCondition) {
    $sql .= " AND " . $costCondition;
}
if ($ratingsCondition) {
    $sql .= " AND " . $ratingsCondition;
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel information</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
     <link rel="stylesheet" href="./css/hotels.css">
</head>
<body>
    <header>
        <a href="#" class="logo">VietTransit</a>
    </header>

    <div class="main-content">
        <h1>Hotels in <?php echo htmlspecialchars($cityName); ?></h1>

        <div class="content-container">
            <!-- Filter Sidebar -->
            <div class="filter-sidebar">
                <h2>Filter Options</h2>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?city_id=' . $cityId); ?>">
                    <div class="filter-group">
                        <h3>Price Range</h3>
                        <div class="checkbox-group">
                            <label><input type="radio" name="cost" value="All" <?php if ($selectedCost === 'All') echo 'checked'; ?>> All</label>
                            <label><input type="radio" name="cost" value="less" <?php if ($selectedCost === 'less') echo 'checked'; ?>> Under 1.000.000₫</label>
                            <label><input type="radio" name="cost" value="between" <?php if ($selectedCost === 'between') echo 'checked'; ?>> 1.000.000₫ - 2.000.000₫</label>
                            <label><input type="radio" name="cost" value="above" <?php if ($selectedCost === 'above') echo 'checked'; ?>> Above 2.000.000₫</label>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3>Ratings</h3>
                        <div class="checkbox-group">
                            <label><input type="radio" name="ratings" value="All" <?php if ($selectedRatings === 'All') echo 'checked'; ?>> All</label>
                            <label><input type="radio" name="ratings" value="1star" <?php if ($selectedRatings === '1star') echo 'checked'; ?>> 1 Star ⭐</label>
                            <label><input type="radio" name="ratings" value="2star" <?php if ($selectedRatings === '2star') echo 'checked'; ?>> 2 Star ⭐⭐</label>
                            <label><input type="radio" name="ratings" value="3star" <?php if ($selectedRatings === '3star') echo 'checked'; ?>> 3 Star ⭐⭐⭐</label>
                            <label><input type="radio" name="ratings" value="4star" <?php if ($selectedRatings === '4star') echo 'checked'; ?>> 4 Star ⭐⭐⭐⭐</label>
                            <label><input type="radio" name="ratings" value="5star" <?php if ($selectedRatings === '5star') echo 'checked'; ?>> 5 Star ⭐⭐⭐⭐⭐</label>
                        </div>
                    </div>
                    <button type="submit" class="filter-button">Apply Filters</button>
                    <button type="reset" class="reset-button">Reset Filters</button>
                </form>
            </div>

            <!-- Tour Content -->
            <div class="tour-content">
                <div class="tour-container">
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $hotelId = htmlspecialchars($row['hotelid']);
                            $imagePath = "hotelphotoID/{$hotelId}.jpg";
                            $discount = rand(3, 5); // Random discount for demo
                            $discountedPrice = $row['cost'] * (1 - $discount / 100);
                            $amenities = htmlspecialchars($row['amenities']);
                            ?>
                            <div class="tour-card">
                                <div class="tour-image">
                                    <img src="<?php echo $imagePath; ?>" alt="Hotel Image">
                                    <span class="discount-badge"><?php echo $discount; ?>%</span>
                                </div>
                                <div class="tour-details">
                                    <h3><?php echo htmlspecialchars($row['hotel']); ?></h3>
                                    <div class="tour-info">
                                        <ul>
                                            <li><span class="icon">⭐</span> Rating: <?php echo htmlspecialchars($row['ratings']); ?> Stars</li>
                                            <li><span class="icon">📅</span> Available: Daily</li>
                                            <li><span class="icon2">🏨</span> Amenities: <?php echo $amenities; ?></li>
                                        </ul>
                                    </div>
                                    <div class="price-action">
                                        <div>
                                            <span class="price"><?php echo number_format($discountedPrice, 0, ',', '.'); ?> ₫</span>
                                            <span class="original-price"><?php echo number_format($row['cost'], 0, ',', '.'); ?> ₫</span>
                                        </div>
                                        <a href="booking.php?hotel_id=<?php echo $hotelId; ?>" class="btn">Book now</a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p>No hotels found in " . htmlspecialchars($cityName) . "</p>";
                    }

                    // Close the database connection
                    $conn->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>