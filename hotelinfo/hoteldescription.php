<?php
session_start();

// Database connection
include '../dbconnect.php';

// Get hotelid from URL
$hotelId = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;

// Fetch hotel data
$hotel = null;
if ($hotelId) {
    $sql = "SELECT * FROM hotels WHERE hotelid = $hotelId";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $hotel = $result->fetch_assoc();
    }
    $conn->close();
}

// Read hoteladdress.json
$json_data = file_get_contents('hoteladdress.json');
$hotel_addresses = json_decode($json_data, true);
$address = null;
foreach ($hotel_addresses as $addr) {
    if ($addr['hotelid'] == $hotelId) {
        $address = $addr;
        break;
    }
}

// Calculate discounted price (7% off)
$originalPrice = $hotel ? $hotel['cost'] : 14590000;
$discountedPrice = $originalPrice * 0.93;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $hotel ? htmlspecialchars($hotel['hotel']) : 'Hotel Tour'; ?></title>
    <link rel="stylesheet" href="../css/hotelinfo.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <!-- Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- LightGallery CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
</head>
<body>
<header>
    <div class="header-container">
        <a href="#" class="logo">
            <img src="../images/logo.png" alt="VietTransit Logo">
            <span>VietTransit</span>
        </a>
        <nav class="navbar">
            <a href="/Login/loggedinhome.php">Home</a>
            <?php
                if (isset($_SESSION['usersuid'])) {
                    echo "<a href='/Login/profile.php'>Hello, " . htmlspecialchars($_SESSION['usersuid']) . "!</a>";
                }
                echo '<a href="../home.php">Logout</a>';
            ?>
        </nav>
    </div>
</header>

<main>
    <?php if ($hotel): ?>
        <h1>  <?php echo htmlspecialchars($hotel['hotel']); ?></h1>
        <p>   <?php echo $address ? htmlspecialchars($address['address']) : 'Address not found'; ?></p>
    <?php else: ?>
        <h1>Hotel Tour</h1>
        <p>Address not available</p>
    <?php endif; ?>
    <div class="gallery" id="lightgallery">
        <?php if ($hotel): ?>
            <a href="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.1.jpg" class="big"><img src="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.1.jpg" alt="Hotel Image 1"></a>
            <a href="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.2.jpg" class="small1"><img src="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.2.jpg" alt="Hotel Image 2"></a>
            <a href="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.3.jpg" class="small2"><img src="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.3.jpg" alt="Hotel Image 3"></a>
            <a href="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.4.jpg" class="small3"><img src="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.4.jpg" alt="Hotel Image 4"></a>
            <a href="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.5.jpg" class="small4"><img src="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.5.jpg" alt="Hotel Image 5"></a>
        <?php else: ?>
            <p>No hotel images available.</p>
        <?php endif; ?>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <div class="box2">
                    <h3>Hotel Description</h3>
                    <p>
                        <?php 
                        echo htmlspecialchars($hotel['description'] ?? 
                        'Indulge in the perfect blend of comfort, elegance, and world-class hospitality at this premier hotel, where every detail is designed to exceed your expectations. From beautifully appointed rooms and suites to exceptional service that caters to your every need, guests are treated to a truly luxurious experience.') 
                        . '<br><br>' . 
                        htmlspecialchars('Enjoy breathtaking views of the surrounding landscape, whether you are relaxing in your room, dining at the gourmet restaurant, or unwinding in the rooftop lounge. Whether you are traveling for business or leisure, this hotel offers an unparalleled stay marked by sophistication, tranquility, and unforgettable moments.');
                        ?>
                    </p>
                </div>
            </div>
            <div class="box">
                <div class="box2">
                    <h3>Most Popular Amenities</h3>
                    <?php
                    $amenities = $hotel['amenities'] ?? '';
                    $amenities_list = array_map('trim', explode(',', $amenities));
                    $amenity_icons = [
                        'Wifi' => '🛜', 'Tivi' => '🖥️', 'Parking' => '🅿️', 'Bar' => '🍹',
                        'Pool' => '🏞️', 'Airport' => '✈️', 'Spa' => '💆🏻‍♀️🧖🏻', 'Breakfast' => '🍳🍽️🥞'
                    ];
                    $displayed_amenities = [];
                    foreach ($amenities_list as $amenity) {
                        if (array_key_exists($amenity, $amenity_icons)) {
                            $displayed_amenities[] = $amenity_icons[$amenity] . ' ' . $amenity;
                        }
                    }
                    echo !empty($displayed_amenities) ? '<p>' . implode(' ', $displayed_amenities) . '</p>' : '<p>No amenities available.</p>';
                    ?>
                </div>
                <div class="box2">
                    <h3>Hotel Quality <?php echo htmlspecialchars($hotel['ratings']); ?> ⭐</h3>
                </div>
                <div class="box2">
                    <h3>See Room Price</h3>
                    <?php
                    $basePrice = $hotel['cost'] ?? 0;
                    $deluxePrice = $basePrice + 200000;
                    $suitePrice = $basePrice + 500000;
                    ?>
                    <div style="display: flex; justify-content: space-around; align-items: flex-start; gap: 20px;">
                        <div>
                            <strong>🏢 Standard Hotel Room</strong><br><br>
                            <div>Two Single Beds:</div><br>
                            <div><?php echo number_format($basePrice); ?> VND</div>
                        </div>
                        <div>
                            <strong>✨ Deluxe Hotel Room</strong><br><br>
                            <div>One Single Bed, One Double Bed:</div><br>
                            <div><?php echo number_format($deluxePrice); ?> VND</div>
                        </div>
                        <div>
                            <strong>⚜️ Suite Hotel Room</strong><br><br>
                            <div>A Large King Size Bed:</div><br>
                            <div><?php echo number_format($suitePrice); ?> VND</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;"><?php echo number_format($discountedPrice); ?> VND</p>
                    <p style="text-decoration: line-through; color: gray;"><?php echo number_format($originalPrice); ?> VND</p>
                    <a href="/hotel/booking?hotel_id=<?php echo $hotelId; ?>" class="booking-button">Book now</a>
                </div>
            </div>
            <div class="box">
                <h3>Location Map</h3>
                <?php if ($address): ?>
                    <div id="map"></div>
                <?php else: ?>
                    <p>Map not available.</p>
                <?php endif; ?>
            </div>
            <div class="box">
                <h3>Why Book Hotel On Our Website?</h3>
                <p>Our service is safe and secure, offering a convenient and time-saving experience. We ensure transparency with no hidden fees and provide access to exclusive deals you won't find elsewhere.</p>
            </div>
            <div class="box">
                <h3>Trusted Website</h3>
                <p>Since its founding in 2025, our website has become a leading travel brand and is nationally recognized for its excellence and reliability.</p>
            </div>
        </div>
    </div>

    <!-- Modal for map -->
    <div id="mapModal" class="modal">
        <span class="close">×</span>
        <div id="modalMap"></div>
    </div>
</main>

<footer>
    <section class="footer">
        <div class="box-container">
            <div class="box2">
                <h3>Extra links</h3>
                <a href="../Login/profile.php">My account</a>
                <a href="../Payment Interface/receiptlist.php">My List</a>
                <a href="../Login/profile.php">My favorite</a>
            </div>
            <div class="box2">
                <h3>Popular Travel Locations</h3>
                <a href="../Journey/viewjourney_tay_bac.php">Tay Bac</a>
                <a href="../Journey/viewjourney_ho_chi_minh.php">Ho Chi Minh</a>
                <a href="../Journey/viewjourney_phu_quoc.php">Phu Quoc</a>
                <a href="../Journey/viewjourney_hue.php">Hue</a>
            </div>
            <div class="box2">
                <h3>Contact Info</h3>
                <a href="https://github.com/socolate12345/Travel-Booking-Website">GitHub</a>
                <img src="./images/payment.png" alt="">
            </div>
        </div>
        <div class="credit">©2025 VietTransit</div>
    </section>
</footer>

<script>
// Initialize LightGallery
document.addEventListener('DOMContentLoaded', function() {
    lightGallery(document.getElementById('lightgallery'), {
        speed: 500,
        plugins: [],
        mode: 'lg-slide',
        thumbnail: true,
        zoom: true,
        download: false
    });
});

// Initialize Leaflet map
<?php if ($address): ?>
var map = L.map('map').setView([<?php echo $address['coordinates']['latitude']; ?>, <?php echo $address['coordinates']['longitude']; ?>], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);
L.marker([<?php echo $address['coordinates']['latitude']; ?>, <?php echo $address['coordinates']['longitude']; ?>])
    .addTo(map)
    .bindPopup('<?php echo htmlspecialchars($address['address']); ?>');

// Map modal functionality
var mapModal = document.getElementById('mapModal');
var modalMap = document.getElementById('modalMap');
var closeBtn = mapModal.getElementsByClassName('close')[0];

map.addEventListener('click', function() {
    mapModal.style.display = 'flex';
    var modalLeafletMap = L.map('modalMap').setView([<?php echo $address['coordinates']['latitude']; ?>, <?php echo $address['coordinates']['longitude']; ?>], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(modalLeafletMap);
    L.marker([<?php echo $address['coordinates']['latitude']; ?>, <?php echo $address['coordinates']['longitude']; ?>])
        .addTo(modalLeafletMap)
        .bindPopup('<?php echo htmlspecialchars($address['address']); ?>').openPopup();
});

closeBtn.addEventListener('click', function() {
    mapModal.style.display = 'none';
});

window.addEventListener('click', function(event) {
    if (event.target == mapModal) {
        mapModal.style.display = 'none';
    }
});
<?php endif; ?>
</script>
</body>
</html>