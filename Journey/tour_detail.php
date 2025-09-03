<?php
session_start();

// Get tour ID and city ID from URL parameters
$tourId = $_GET['tourid'] ?? null;
$cityId = $_GET['cityid'] ?? null;

if (!$tourId || !$cityId) {
    echo "<h2>Invalid tour parameters</h2>";
    exit;
}

// Include database connection
include '../dbconnect.php';

// Check database connection
if (!$conn) {
    echo "<h2>Database connection failed</h2>";
    exit;
}

// Complete city mapping based on your database
$cityStringToId = [
    'hcm' => 11,          
    'dalat' => 15,         
    'nhatrang' => 12,      
    'hoian' => 17,         
    'phuquoc' => 16,       
    'taybac' => 10,        
    'hue' => 13,       
    'phuyen' => 14,        
    'hagiang' => 18,       
    'danang' => 19,         
    'cantho' => 20,
    'hanoi' => 21       
];

// City names mapping
$cityNames = [
    10 => 'Tay Bac',
    11 => 'Ho Chi Minh City',
    12 => 'Nha Trang',
    13 => 'Hue',
    14 => 'Phu Yen',
    15 => 'Dalat',
    16 => 'Phu Quoc',
    17 => 'Hoi An',
    18 => 'Ha Giang',
    19 => 'Da Nang',
    20 => 'Can Tho',
    21 => 'Ha Noi'
];

// Mapping from database city IDs to folder prefixes - updated to match available cities
$cityIdToFolderPrefix = [
    10 => 'taybac',       
    11 => 'hcm',          
    12 => 'nhatrang',    
    13 => 'hue',     
    14 => 'phuyen',       
    15 => 'dalat',        
    16 => 'phuquoc',      
    17 => 'hoian',       
    18 => 'hagiang',     
    19 => 'danang',
    20 => 'cantho',
    21 => 'hanoi'
];

// Convert string cityId to numeric
$numericCityId = $cityStringToId[$cityId] ?? null;
if (!$numericCityId) {
    echo "<h2>Invalid city ID: $cityId</h2>";
    exit;
}

// Get city name for display
$cityName = $cityNames[$numericCityId] ?? ucfirst($cityId);

// Fetch tour details from database using mysqli
$stmt = $conn->prepare("SELECT * FROM tours WHERE tourid = ? AND cityid = ?");
$stmt->bind_param("ii", $tourId, $numericCityId);
$stmt->execute();
$result = $stmt->get_result();
$tour = $result->fetch_assoc();

if (!$tour) {
    echo "<h2>Tour not found</h2>";
    exit;
}

// Calculate discounted price (assuming 15% discount for display)
$discountPercent = 15;
$originalPrice = $tour['price_per_person'] / (1 - $discountPercent/100);

// In your generateImagePaths function, you could add error handling
function generateImagePaths($numericCityId, $tourId, $cityIdToFolderPrefix) {
    $folderPrefix = $cityIdToFolderPrefix[$numericCityId] ?? 'default';
    $folderName = $folderPrefix . $tourId;
    
    $images = [];
    for ($i = 1; $i <= 5; $i++) {
        $imagePath = "../Tour_detail/{$folderName}/{$i}.jpg";
        // Add fallback image if file doesn't exist
        if (!file_exists($imagePath)) {
            $imagePath = "../images/no-image-placeholder.jpg";
        }
        $images[] = $imagePath;
    }
    
    return $images;
}

// Parse description into bullet points
$descriptionLines = explode("\n", trim($tour['description']));
$attractions = array_filter($descriptionLines, function($line) {
    return !empty(trim($line));
});

// Generate itinerary based on duration
function generateItinerary($tourName, $duration, $attractions) {
    $itinerary = [];
    $attractionsList = array_slice($attractions, 0, min(count($attractions), $duration));
    
    for ($day = 1; $day <= $duration; $day++) {
        $dayTitle = "";
        $dayContent = "";
        
        if ($day == 1) {
            $dayTitle = "Day 1: Arrival and First Exploration";
            $dayContent = "Arrive at your destination and begin your journey. " . 
                         (isset($attractionsList[0]) ? $attractionsList[0] : "Explore the local area and get oriented.");
        } elseif ($day == $duration) {
            $dayTitle = "Day $day: Final Day and Departure";
            $dayContent = "Complete your adventure and prepare for departure. " .
                         (isset($attractionsList[$day-1]) ? $attractionsList[$day-1] : "Final exploration and return journey.");
        } else {
            $dayTitle = "Day $day: " . ($day == 2 ? "Main Attractions" : "Cultural Discovery");
            $dayContent = isset($attractionsList[$day-1]) ? $attractionsList[$day-1] : 
                         "Continue exploring the beautiful landscapes and cultural sites of the region.";
        }
        
        $itinerary[] = ['title' => $dayTitle, 'content' => $dayContent];
    }
    
    return $itinerary;
}

// Generate the data
$itinerary = generateItinerary($tour['tour_name'], $tour['duration_days'], $attractions);
$images = generateImagePaths($numericCityId, $tourId, $cityIdToFolderPrefix);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tour['tour_name']) ?></title>
    <meta name="description" content="<?= htmlspecialchars(substr($tour['description'], 0, 160)) ?>">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1><?= htmlspecialchars($tour['tour_name']) ?></h1>
    
    <div class="gallery" id="lightgallery">
        <?php foreach ($images as $index => $image): ?>
            <?php 
                $class = $index == 0 ? 'big' : 'small' . ($index);
                $alt = htmlspecialchars($tour['tour_name']) . ' - Image ' . ($index + 1);
            ?>
            <a href="<?= $image ?>" class="<?= $class ?>">
                <img src="<?= $image ?>" alt="<?= $alt ?>" loading="lazy" 
                     width="<?= $index == 0 ? '800' : '400' ?>" 
                     height="<?= $index == 0 ? '600' : '300' ?>">
            </a>
        <?php endforeach; ?>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <?php foreach ($attractions as $attraction): ?>
                        <li><?= htmlspecialchars(trim($attraction)) ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <?php foreach ($itinerary as $day): ?>
                    <article class="box2">
                        <h3><?= htmlspecialchars($day['title']) ?></h3>
                        <p><?= htmlspecialchars($day['content']) ?></p>
                    </article>
                <?php endforeach; ?>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;"><?= number_format($tour['price_per_person'], 0, ',', '.') ?> VND</p>
                    <p style="text-decoration: line-through; color: gray;"><?= number_format($originalPrice, 0, ',', '.') ?> VND</p>
                    <a href="/tour/booking?cityid=<?= $tour['cityid'] ?>&tourid=<?= $tour['tourid'] ?>" class="booking-button">Booking now!</a>
                </div>
            </div>

            <div class="box">
                <h3>Tour Information</h3>
                <ul>
                    <li><strong>Duration:</strong> <?= $tour['duration_days'] ?> days</li>
                    <li><strong>Season:</strong> <?= ucfirst($tour['season']) ?></li>
                </ul>
            </div>

            <div class="box">
                <h3>Contact Support</h3>
                <p>📞 Hotline: <a href="tel:19192025">1919 2025</a><br>✉️ Email: <a href="mailto:viettransit.support@mail.com">viettransit.support@mail.com</a></p>
            </div>

            <div class="box">
                <h3>Why Book Online?</h3>
                <ul>
                    <li>Safe & Secure</li>
                    <li>Convenient & Time-saving</li>
                    <li>No hidden fees</li>
                    <li>Exclusive deals</li>
                </ul>
            </div>

            <div class="box">
                <h3>Trusted Tour</h3>
                <p>Founded in 2025<br>Leading travel brand<br>Nationally recognized</p>
            </div>
        </aside>
    </div>
</main>

<?php include __DIR__ . '/../footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/lightgallery.min.js"></script>
<script>
    lightGallery(document.getElementById('lightgallery'), {
        thumbnail: true,
        animateThumb: true,
        showThumbByDefault: true,
        mode: 'lg-slide',
        download: false,
        share: false
    });
</script>
</body>
</html>