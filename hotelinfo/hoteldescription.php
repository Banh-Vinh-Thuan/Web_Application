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

// Read hotel location data for map
$location_data = file_get_contents('hoteladdress.json');
$hotel_locations = json_decode($location_data, true);

// Find main hotel and nearby hotels
$mainHotel = null;
$nearbyHotels = [];

foreach ($hotel_locations as $hotelLocation) {
    if ($hotelLocation['hotelid'] == $hotelId) {
        $mainHotel = $hotelLocation;
    } else {
        $nearbyHotels[] = $hotelLocation;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $hotel ? htmlspecialchars($hotel['hotel']) : 'Hotel Tour'; ?></title>
    <link rel="stylesheet" href="../css/hotelinfo.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- LightGallery CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
    
    <!-- AOS Animation Library -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
</head>
<body>
<?php include __DIR__ . '/../header.php'; ?>

<main class="main-content">
    <!-- Hero Section -->
    <section class="hero-section" data-aos="fade-up">
        <div class="hero-content">
            <?php if ($hotel): ?>
                <h1 class="hero-title"><?php echo htmlspecialchars($hotel['hotel']); ?></h1>
                <p class="hero-address">📍 <?php echo $address ? htmlspecialchars($address['address']) : 'Address not found'; ?></p>
            <?php else: ?>
                <h1 class="hero-title">Hotel Tour</h1>
                <p class="hero-address">📍 Address not available</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Image Gallery Section -->
    <section class="gallery-section" data-aos="fade-up" data-aos-delay="200">
        <div class="container">
            <div class="gallery-grid" id="lightgallery">
                <?php if ($hotel): ?>
                    <a href="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.1.jpg" class="gallery-item main-image">
                        <img src="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.1.jpg" alt="Hotel Image 1">
                        <div class="image-overlay">
                            <span class="view-icon">🔍</span>
                        </div>
                    </a>
                    <a href="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.2.jpg" class="gallery-item">
                        <img src="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.2.jpg" alt="Hotel Image 2">
                        <div class="image-overlay">
                            <span class="view-icon">🔍</span>
                        </div>
                    </a>
                    <a href="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.3.jpg" class="gallery-item">
                        <img src="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.3.jpg" alt="Hotel Image 3">
                        <div class="image-overlay">
                            <span class="view-icon">🔍</span>
                        </div>
                    </a>
                    <a href="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.4.jpg" class="gallery-item">
                        <img src="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.4.jpg" alt="Hotel Image 4">
                        <div class="image-overlay">
                            <span class="view-icon">🔍</span>
                        </div>
                    </a>
                    <a href="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.5.jpg" class="gallery-item">
                        <img src="<?php echo $hotelId; ?>/<?php echo $hotelId; ?>.5.jpg" alt="Hotel Image 5">
                        <div class="image-overlay">
                            <span class="view-icon">🔍</span>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="no-images">
                        <p>No hotel images available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="content-section">
        <div class="container">
            <div class="content-grid">
                <!-- Left Column -->
                <div class="left-column">
                    <!-- Hotel Description -->
                    <div class="content-card" data-aos="fade-right">
                        <div class="card-header">
                            <h2 class="card-title">🏨 Hotel Description</h2>
                        </div>
                        <div class="card-content">
                            <p class="description-text">
                                <?php 
                                echo htmlspecialchars($hotel['description'] ?? 
                                'Indulge in the perfect blend of comfort, elegance, and world-class hospitality at this premier hotel, where every detail is designed to exceed your expectations. From beautifully appointed rooms and suites to exceptional service that caters to your every need, guests are treated to a truly luxurious experience.') 
                                . '<br><br>' . 
                                htmlspecialchars('Enjoy breathtaking views of the surrounding landscape, whether you are relaxing in your room, dining at the gourmet restaurant, or unwinding in the rooftop lounge. Whether you are traveling for business or leisure, this hotel offers an unparalleled stay marked by sophistication, tranquility, and unforgettable moments.');
                                ?>
                            </p>
                        </div>
                    </div>

                    <!-- Amenities Section -->
                    <div class="content-card" data-aos="fade-right" data-aos-delay="100">
                        <div class="card-header">
                            <h2 class="card-title">✨ Most Popular Amenities</h2>
                        </div>
                        <div class="card-content">
                            <div class="amenities-grid">
                                <?php
                                $amenities = $hotel['amenities'] ?? '';
                                $amenities_list = array_map('trim', explode(',', $amenities));
                                $amenity_icons = [
                                    'Wifi' => '🛜', 'Tivi' => '🖥️', 'Parking' => '🅿️', 'Bar' => '🍹',
                                    'Pool' => '🏞️', 'Airport' => '✈️', 'Spa' => '💆🏻‍♀️', 'Breakfast' => '🍳'
                                ];
                                $displayed_amenities = [];
                                foreach ($amenities_list as $amenity) {
                                    if (array_key_exists($amenity, $amenity_icons)) {
                                        echo "<div class='amenity-item'>";
                                        echo "<span class='amenity-icon'>" . $amenity_icons[$amenity] . "</span>";
                                        echo "<span class='amenity-name'>" . $amenity . "</span>";
                                        echo "</div>";
                                    }
                                }
                                if (empty($displayed_amenities) && empty($amenities)) {
                                    echo '<p class="no-amenities">No amenities available.</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Hotel Rating -->
                    <div class="content-card rating-card" data-aos="fade-right" data-aos-delay="200">
                        <div class="card-content">
                            <div class="rating-display">
                                <span class="rating-stars">
                                    <?php
                                    $rating = $hotel['ratings'] ?? 0;
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<span class="star filled">⭐</span>';
                                        } else {
                                            echo '<span class="star">☆</span>';
                                        }
                                    }
                                    ?>
                                </span>
                                <span class="rating-text">Hotel Quality <?php echo htmlspecialchars($hotel['ratings'] ?? '0'); ?>/5</span>
                            </div>
                        </div>
                    </div>

                    <!-- Room Prices -->
                    <div class="content-card" data-aos="fade-right" data-aos-delay="300">
                        <div class="card-header">
                            <h2 class="card-title">🛏️ Room Types & Prices</h2>
                        </div>
                        <div class="card-content">
                            <div class="room-types-grid">
                                <?php
                                $basePrice = $hotel['cost'] ?? 0;
                                $deluxePrice = $basePrice + 200000;
                                $suitePrice = $basePrice + 500000;
                                ?>
                                <div class="room-type">
                                    <div class="room-icon">🏢</div>
                                    <h3 class="room-name">Standard Room</h3>
                                    <p class="room-desc">Two Single Beds</p>
                                    <div class="room-price"><?php echo number_format($basePrice); ?> VND</div>
                                </div>
                                <div class="room-type">
                                    <div class="room-icon">✨</div>
                                    <h3 class="room-name">Deluxe Room</h3>
                                    <p class="room-desc">One Single + One Double Bed</p>
                                    <div class="room-price"><?php echo number_format($deluxePrice); ?> VND</div>
                                </div>
                                <div class="room-type">
                                    <div class="room-icon">⚜️</div>
                                    <h3 class="room-name">Suite Room</h3>
                                    <p class="room-desc">Large King Size Bed</p>
                                    <div class="room-price"><?php echo number_format($suitePrice); ?> VND</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="right-column">
                    <!-- Booking Card -->
                    <div class="booking-card" data-aos="fade-left">
                        <div class="booking-header">
                            <h3 class="booking-title">💰 Price From</h3>
                            <div class="price-display">
                                <span class="current-price"><?php echo number_format($discountedPrice); ?> VND</span>
                                <span class="original-price"><?php echo number_format($originalPrice); ?> VND</span>
                            </div>
                            <div class="discount-badge">7% OFF</div>
                        </div>
                        <a href="/hotel/booking?hotel_id=<?php echo $hotelId; ?>" class="booking-button">
                            <span>Book Now</span>
                            <span class="button-icon">→</span>
                        </a>
                    </div>

                    Location Map
                    <div class="content-card map-card" data-aos="fade-left" data-aos-delay="100">
                        <div class="card-header">
                            <h2 class="card-title">📍 Location Map</h2>
                        </div>
                        <div class="card-content">
                            <?php if ($mainHotel): ?>
                                <div id="smallMap" class="small-map">
                                    <div class="map-container">
                                        <div id="mapSmall"></div>
                                        <div class="map-overlay">
                                            <button id="expandMap" class="expand-btn">🔍 View Large Map</button>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="no-map">Map not available.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Why Book -->
                    <div class="content-card" data-aos="fade-left" data-aos-delay="200">
                        <div class="card-header">
                            <h2 class="card-title">🎯 Why Book With Us?</h2>
                        </div>
                        <div class="card-content">
                            <div class="benefits-list">
                                <div class="benefit-item">
                                    <span class="benefit-icon">🔒</span>
                                    <span class="benefit-text">Safe & Secure Booking</span>
                                </div>
                                <div class="benefit-item">
                                    <span class="benefit-icon">💎</span>
                                    <span class="benefit-text">Exclusive Deals</span>
                                </div>
                                <div class="benefit-item">
                                    <span class="benefit-icon">⚡</span>
                                    <span class="benefit-text">Instant Confirmation</span>
                                </div>
                                <div class="benefit-item">
                                    <span class="benefit-icon">🆓</span>
                                    <span class="benefit-text">No Hidden Fees</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trust Badge -->
                    <div class="content-card trust-card" data-aos="fade-left" data-aos-delay="300">
                        <div class="card-header">
                            <h2 class="card-title">🏆 Trusted Since 2025</h2>
                        </div>
                        <div class="card-content">
                            <p class="trust-text">Nationally recognized for excellence and reliability in travel booking services.</p>
                            <div class="trust-badges">
                                <span class="trust-badge">⭐ 4.8/5 Rating</span>
                                <span class="trust-badge">🔐 SSL Secured</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Full screen map modal -->
<div id="mapModal" class="map-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🗺️ Hotel Location & Nearby Hotels</h3>
            <span class="close-btn" id="closeModal">&times;</span>
        </div>
        <div class="modal-body">
            <div id="mapLarge"></div>
            <div id="hotelInfo" class="hotel-info-panel">
                <!-- Hotel information will be populated here -->
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
<script>
// Initialize AOS
AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    offset: 100
});

// Hotel data from PHP
const mainHotel = <?php echo json_encode($mainHotel); ?>;
const nearbyHotels = <?php echo json_encode($nearbyHotels); ?>;

// Initialize map functionality
if (mainHotel) {
    initializeMapSystem();
}

function initializeMapSystem() {
    // Custom icons
    const mainHotelIcon = L.divIcon({
        className: 'custom-marker main-hotel',
        html: '<div class="marker-pin main-pin">📍</div>',
        iconSize: [40, 40],
        iconAnchor: [20, 40]
    });

    const nearbyHotelIcon = L.divIcon({
        className: 'custom-marker nearby-hotel',
        html: '<div class="marker-pin nearby-pin">🏨</div>',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });

    // Initialize small map
    let smallMap = L.map('mapSmall', {
        zoomControl: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        boxZoom: false,
        keyboard: false,
        dragging: false
    }).setView([mainHotel.coordinates.latitude, mainHotel.coordinates.longitude], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(smallMap);

    // Add main hotel marker to small map
    L.marker([mainHotel.coordinates.latitude, mainHotel.coordinates.longitude], {
        icon: mainHotelIcon
    }).addTo(smallMap);

    // Large map variable
    let largeMap = null;

    // Expand map functionality
    document.getElementById('expandMap').addEventListener('click', function() {
        document.getElementById('mapModal').style.display = 'flex';
        setTimeout(() => {
            initializeLargeMap();
        }, 100);
    });

    // Close modal
    document.getElementById('closeModal').addEventListener('click', function() {
        document.getElementById('mapModal').style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('mapModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    function initializeLargeMap() {
        if (largeMap) {
            largeMap.remove();
        }

        largeMap = L.map('mapLarge').setView([mainHotel.coordinates.latitude, mainHotel.coordinates.longitude], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(largeMap);

        // Add main hotel marker
        const mainMarker = L.marker([mainHotel.coordinates.latitude, mainHotel.coordinates.longitude], {
            icon: mainHotelIcon
        }).addTo(largeMap);

        mainMarker.on('click', function() {
            showHotelInfo(mainHotel, true);
        });

        // Add nearby hotel markers
        nearbyHotels.forEach(hotel => {
            const marker = L.marker([hotel.coordinates.latitude, hotel.coordinates.longitude], {
                icon: nearbyHotelIcon
            }).addTo(largeMap);

            marker.on('click', function() {
                showHotelInfo(hotel, false);
            });
        });

        // Show main hotel info by default
        showHotelInfo(mainHotel, true);
    }

    function showHotelInfo(hotel, isMainHotel) {
        const infoPanel = document.getElementById('hotelInfo');
        const hotelType = isMainHotel ? 'Current Hotel' : 'Nearby Hotel';
        const typeClass = isMainHotel ? 'main-hotel-badge' : 'nearby-hotel-badge';

        const attractionsHtml = hotel.nearby_attractions.map(attraction => 
            `<div class="attraction-item">
                <span class="attraction-name">${attraction.name}</span>
                <span class="attraction-distance">${attraction.distance}</span>
            </div>`
        ).join('');

        infoPanel.innerHTML = `
            <div class="hotel-info-card">
                <div class="hotel-header">
                    <div class="hotel-image">
                        <img src="${hotel.image}" alt="${hotel.name}" onerror="this.src='https://via.placeholder.com/100x80?text=No+Image'">
                    </div>
                    <div class="hotel-basic-info">
                        <div class="hotel-type-badge ${typeClass}">${hotelType}</div>
                        <h4 class="hotel-name">${hotel.name}</h4>
                        <div class="hotel-rating">
                            <span class="stars">${generateStars(hotel.rating)}</span>
                            <span class="rating-number">${hotel.rating}</span>
                        </div>
                        <div class="hotel-price">${formatPrice(hotel.price)}/night</div>
                    </div>
                </div>
                <div class="hotel-address">
                    <strong>📍 Address:</strong> ${hotel.address}
                </div>
                <div class="attractions-section">
                    <h5>🎯 Nearby Attractions:</h5>
                    <div class="attractions-list">
                        ${attractionsHtml}
                    </div>
                </div>
            </div>
        `;
    }

    function generateStars(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = (rating - fullStars) >= 0.5;
        let stars = '';
        
        for (let i = 0; i < fullStars; i++) {
            stars += '⭐';
        }
        if (hasHalfStar) {
            stars += '⭐';
        }
        
        return stars;
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price) + ' VND';
    }
}

// Initialize LightGallery
if (document.getElementById('lightgallery')) {
    lightGallery(document.getElementById('lightgallery'), {
        plugins: [],
        speed: 500,
        licenseKey: 'your_license_key'
    });
}
</script>
</body>
</html>