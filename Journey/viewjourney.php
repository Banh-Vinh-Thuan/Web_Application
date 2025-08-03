<?php
// viewjourney.php - universal tour viewer (Database version)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get city ID from URL, default is 'hcm'
$cityId = $_GET['id'] ?? 'hcm';

// Include database connection
include '../dbconnect.php';

// Check database connection
if (!$conn) {
    echo "<h2>Database connection failed</h2>";
    exit;
}

// Complete city mapping based on your database
$cityStringToId = [
    'hcm' => 11,           // Ho Chi Minh City
    'dalat' => 15,         // Dalat
    'nhatrang' => 12,      // Nha Trang
    'hoian' => 17,         // Hoi An
    'phuquoc' => 16,       // Phu Quoc
    'northern' => 10,      // Northern Vietnam
    'central' => 13,       // Central Vietnam
    'phuyen' => 14,        // Phu Yen
    'hagiang' => 18,       // Ha Giang
    'hue' => 19,           // Hue
    'taybac' => 10,        // Tay Bac (Northern Vietnam)
];

// City names mapping
$cityNames = [
    10 => 'Northern Vietnam',
    11 => 'Ho Chi Minh City',
    12 => 'Nha Trang',
    13 => 'Central Vietnam',
    14 => 'Phu Yen',
    15 => 'Dalat',
    16 => 'Phu Quoc',
    17 => 'Hoi An',
    18 => 'Ha Giang',
    19 => 'Hue'
];

// Convert string cityId to numeric
$numericCityId = $cityStringToId[$cityId] ?? null;
if (!$numericCityId) {
    echo "<h2>Invalid city ID: $cityId</h2>";
    exit;
}

// Get city name for display
$displayCity = $cityNames[$numericCityId] ?? ucfirst($cityId);

// Fetch tours from database
$stmt = $conn->prepare("SELECT * FROM tours WHERE cityid = ? ORDER BY tourid");
$stmt->bind_param("i", $numericCityId);
$stmt->execute();
$result = $stmt->get_result();
$tours = $result->fetch_all(MYSQLI_ASSOC);

if (empty($tours)) {
    echo "<h2>No tours found for this destination</h2>";
    exit;
}

// City descriptions - you may want to move these to database as well
$cityDescriptions = [
    'dalat' => [
        "Dalat delights travelers with its mild climate, pine-covered hills, colorful flower gardens, and charming French-colonial villas. Nicknamed the \"City of Eternal Spring,\" it offers scenic lakes, waterfalls, and a romantic ambiance ideal for couples and nature lovers.",
        "Book a Dalat tour with VietTransit and discover iconic spots such as Xuan Huong Lake, Valley of Love, and more. For detailed insights, see our <a href=\"/Journey/traveltip.php?destination=dalat\" style=\"color: blue;\">Dalat Travel Tips</a>."
    ],
    'hagiang' => [
        "Ha Giang stirs the soul with its rugged mountain landscapes, terraced fields, and rich ethnic diversity. This remote northern province offers unforgettable experiences along the Dong Van Karst Plateau, Ma Pi Leng Pass, and traditional hill tribe villages.",
        "Book a Ha Giang tour with VietTransit to explore highlights like Lung Cu Flag Tower, Dong Van Old Town, and Ma Pi Leng Pass. See more in our <a href=\"/Journey/traveltip.php?destination=hagiang\" style=\"color: blue;\">Ha Giang Travel Tips</a>."
    ],
    'hcm' => [
        "Ho Chi Minh City buzzes with life — from blooming spring flowers to vibrant street culture. Known for its energy, culinary scene, and dynamic cityscape, it's a fascinating blend of modern progress and historical depth.",
        "Book a Ho Chi Minh City tour with VietTransit to visit iconic landmarks and vibrant neighborhoods. For details, view our <a href=\"/Journey/traveltip.php?destination=hcm\" style=\"color: blue;\">Ho Chi Minh City Travel Tips</a>."
    ],
    'hoian' => [
        "Hoi An is a beautifully preserved ancient town famed for its lantern-lit evenings, heritage buildings, and cultural fusion. This UNESCO-listed destination charms visitors with its timeless atmosphere and artisan spirit.",
        "Book a Hoi An tour with VietTransit to walk through its historic streets and riverside quarters. Learn more from our <a href=\"/Journey/traveltip.php?destination=hoian\" style=\"color: blue;\">Hoi An Travel Tips</a>."
    ],
    'hue' => [
        "Hue, Vietnam’s former imperial capital, exudes elegance and history. Nestled by the Perfume River, it features ancient citadels, royal tombs, and pagodas, reflecting the grandeur of the Nguyen Dynasty.",
        "Book a Hue tour with VietTransit and explore the Imperial City, Thien Mu Pagoda, and traditional garden houses. Discover more in our <a href=\"/Journey/traveltip.php?destination=hue\" style=\"color: blue;\">Hue Travel Tips</a>."
    ],
    'nhatrang' => [
        "Nha Trang dazzles with its sun-kissed beaches, turquoise waters, and rich marine biodiversity. This coastal city blends natural splendor with cultural landmarks and modern entertainment.",
        "Book a Nha Trang tour with VietTransit to explore Ponagar Tower, VinWonders, and local craft villages. For more, see our <a href=\"/Journey/traveltip.php?destination=nhatrang\" style=\"color: blue;\">Nha Trang Travel Tips</a>."
    ],
    'phuquoc' => [
        "Phu Quoc is a tropical island getaway known for its crystal-clear beaches, lush landscapes, and vibrant local life. From coral reefs to night markets, it offers both relaxation and discovery.",
        "Book a Phu Quoc tour with VietTransit and uncover its beaches, pepper farms, and cultural spots. Learn more from our <a href=\"/Journey/traveltip.php?destination=phuquoc\" style=\"color: blue;\">Phu Quoc Travel Tips</a>."
    ],
    'phuyen' => [
        "Phu Yen charms with its unspoiled beaches, dramatic cliffs, and gentle countryside. It offers a serene coastal escape with golden sunlight, clear waters, and authentic local hospitality.",
        "Book a Phu Yen tour with VietTransit to discover key attractions and untouched beauty. See more in our <a href=\"/Journey/traveltip.php?destination=phuyen\" style=\"color: blue;\">Phu Yen Travel Tips</a>."
    ],
    'taybac' => [
        "Northwest Vietnam in spring comes alive with peach and plum blossoms, flute music, and vibrant ethnic celebrations. A region rich in culture and color, it offers unforgettable highland experiences.",
        "Book a Northwest tour with VietTransit to visit blooming villages, traditional markets, and scenic mountain routes. For details, explore our <a href=\"/Journey/traveltip.php?destination=taybac\" style=\"color: blue;\">Northwest Travel Tips</a>."
    ]
];

$descriptions = $cityDescriptions[$cityId] ?? [
    "Discover the beauty and culture of " . $displayCity,
    "Join VietTransit for an unforgettable journey through " . $displayCity
];

// Function to calculate original price (reverse the discount)
function calculateOriginalPrice($currentPrice, $discountPercent = 15) {
    return $currentPrice / (1 - $discountPercent/100);
}

// Function to determine transport type based on tour name or other factors
function determineTransport($tourName, $departure, $cityId) {
    $tourName = strtolower($tourName);
    $departure = strtolower($departure);
    
    // Flight for long distances or specific keywords
    if (strpos($tourName, 'flight') !== false || 
        ($departure === 'ho chi minh city' && in_array($cityId, ['hagiang', 'hue', 'phuquoc'])) ||
        ($departure === 'hanoi' && in_array($cityId, ['phuquoc', 'nhatrang', 'hcm']))) {
        return 'flight';
    }
    
    // Train for specific routes
    if (strpos($tourName, 'train') !== false || 
        ($departure === 'hanoi' && $cityId === 'hue')) {
        return 'train';
    }
    
    // Walking for city tours
    if (strpos($tourName, 'walk') !== false || strpos($tourName, 'street') !== false) {
        return 'walking';
    }
    
    // Boat combination
    if (strpos($tourName, 'mekong') !== false || strpos($tourName, 'river') !== false) {
        return 'bus-boat';
    }
    
    // Default to bus
    return 'bus';
}

// Function to generate image path
function generateImagePath($cityId, $tourId) {
    // Map city string to image folder pattern
    $imageMap = [
        'hcm' => [5, 6, 7, 8],
        'nhatrang' => [9, 10, 11, 12],
        'hue' => [13, 14, 15, 16],
        'phuyen' => [17, 18, 19, 20],
        'dalat' => [21, 22, 23, 24],
        'phuquoc' => [25, 26, 27, 28],
        'hoian' => [29, 30, 31, 32],
        'hagiang' => [33, 34, 35, 36],
        'taybac' => [1, 2, 3, 4]
    ];
    
    $imageIds = $imageMap[$cityId] ?? [1, 2, 3, 4];
    $imageId = $imageIds[($tourId - 1) % count($imageIds)];
    
    return "../tourphotoID/{$imageId}.jpg";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($displayCity) ?> Travel</title>
  <link rel="stylesheet" href="../css/viewjourney.css">
  <link rel="icon" type="image/png" href="../images/favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../header.php'; ?>

<div class="main-content">
  <h1><?= strtoupper(htmlspecialchars($displayCity)) ?> TRAVEL</h1>

  <!-- City Description Section -->
  <div class="city-description">
    <?php foreach ($descriptions as $description): ?>
      <p><?= $description ?></p>
    <?php endforeach; ?>
  </div>

  <div class="content-container">
    <!-- Sidebar Filter -->
    <div class="filter-sidebar">
      <h2>Filter Options</h2>

      <div class="filter-group">
        <h3>Price Range</h3>
        <div class="range-slider">
          <input type="range" min="1000000" max="20000000" value="1000000" class="range-min">
          <input type="range" min="1000000" max="20000000" value="20000000" class="range-max">
        </div>
        <div class="price-inputs">
          <input type="number" value="1000000" min="1000000" max="20000000" class="min-price"> -
          <input type="number" value="20000000" min="1000000" max="20000000" class="max-price">
        </div>
      </div>

      <div class="filter-group">
        <h3>Duration</h3>
        <div class="checkbox-group">
          <label><input type="checkbox" name="duration" value="1-3"> 1-3 days</label>
          <label><input type="checkbox" name="duration" value="4-7"> 4-7 days</label>
          <label><input type="checkbox" name="duration" value="8+"> 8+ days</label>
        </div>
      </div>

      <div class="filter-group">
        <h3>Departure Point</h3>
        <select class="departure-select">
          <option value="">All Departure Points</option>
          <option value="Ho Chi Minh City">Ho Chi Minh City</option>
          <option value="Hanoi">Hanoi</option>
          <option value="Da Nang">Da Nang</option>
          <option value="Can Tho">Can Tho</option>
        </select>
      </div>

      <div class="filter-group">
        <h3>Transportation</h3>
        <div class="checkbox-group">
          <label><input type="checkbox" name="transport" value="flight"> Flight</label>
          <label><input type="checkbox" name="transport" value="bus"> Bus</label>
          <label><input type="checkbox" name="transport" value="train"> Train</label>
        </div>
      </div>

      <button class="filter-button" id="apply-filter">Apply Filters</button>
      <button class="reset-button" id="reset-filter">Reset Filters</button>
    </div>

    <!-- Tour List -->
    <div class="tour-container">
      <?php foreach ($tours as $index => $tour): 
        $originalPrice = calculateOriginalPrice($tour['price_per_person']);
        $discount = 15; // Default discount percentage
        $transport = determineTransport($tour['tour_name'], $tour['departure_point'] ?? 'Ho Chi Minh City', $cityId);
        $imagePath = generateImagePath($cityId, $tour['tourid']);
      ?>
        <div class="tour-card"
            data-price="<?= $tour['price_per_person'] ?>"
            data-duration="<?= $tour['duration_days'] ?>"
            data-destination="<?= $cityId ?>"
            data-departure="<?= htmlspecialchars($tour['departure_point'] ?? 'Ho Chi Minh City') ?>"
            data-transport="<?= $transport ?>">
          <div class="tour-image">
            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($tour['tour_name']) ?>">
            <span class="discount-badge">-<?= $discount ?>%</span>
          </div>
          <div class="tour-details">
            <h3><?= htmlspecialchars($tour['tour_name']) ?></h3>
            <ul>
              <li><span class="icon">⏰</span> <?= $tour['duration_days'] ?> days</li>
              <li><span class="icon">🚌</span> <?= ucfirst($transport) ?></li>
              <li><span class="icon">🚩</span> Departure: <?= htmlspecialchars($tour['departure_point'] ?? 'Ho Chi Minh City') ?></li>
            </ul>
            <div class="price-action">
              <p class="price"><?= number_format($tour['price_per_person'], 0, ',', '.') ?> ₫
                <span class="original-price"><?= number_format($originalPrice, 0, ',', '.') ?> ₫</span>
              </p>
              <a href="tour_detail.php?cityid=<?= $cityId ?>&tourid=<?= $tour['tourid'] ?>" class="btn">See More</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>

<!-- Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rangeMin = document.querySelector('.range-min');
  const rangeMax = document.querySelector('.range-max');
  const minPrice = document.querySelector('.min-price');
  const maxPrice = document.querySelector('.max-price');
  const applyFilterBtn = document.getElementById('apply-filter');
  const resetFilterBtn = document.getElementById('reset-filter');
  const tourCards = document.querySelectorAll('.tour-card');

  // Range input sync
  rangeMin.addEventListener('input', () => {
    minPrice.value = rangeMin.value;
    if (+rangeMin.value > +rangeMax.value) {
      rangeMin.value = rangeMax.value;
      minPrice.value = rangeMax.value;
    }
  });
  rangeMax.addEventListener('input', () => {
    maxPrice.value = rangeMax.value;
    if (+rangeMax.value < +rangeMin.value) {
      rangeMax.value = rangeMin.value;
      maxPrice.value = rangeMin.value;
    }
  });
  minPrice.addEventListener('input', () => rangeMin.value = minPrice.value);
  maxPrice.addEventListener('input', () => rangeMax.value = maxPrice.value);

  // Apply filter
  applyFilterBtn.addEventListener('click', () => {
    const minP = +minPrice.value;
    const maxP = +maxPrice.value;
    const durations = Array.from(document.querySelectorAll('input[name="duration"]:checked')).map(i => i.value);
    const transports = Array.from(document.querySelectorAll('input[name="transport"]:checked')).map(i => i.value);
    const departure = document.querySelector('.departure-select').value;

    tourCards.forEach(card => {
      const price = +card.getAttribute('data-price');
      const duration = +card.getAttribute('data-duration');
      const cardDeparture = card.getAttribute('data-departure');
      const cardTransport = card.getAttribute('data-transport');

      let show = true;

      // price filter
      if (price < minP || price > maxP) show = false;

      // duration filter
      if (durations.length > 0) {
        show = durations.some(d => {
          if (d === '1-3') return duration >= 1 && duration <= 3;
          if (d === '4-7') return duration >= 4 && duration <= 7;
          if (d === '8+') return duration >= 8;
        }) || false;
      }

      // transport
      if (transports.length > 0 && !transports.includes(cardTransport)) show = false;

      // departure
      if (departure && cardDeparture !== departure) show = false;

      card.style.display = show ? 'flex' : 'none';
    });
  });

  // Reset filters
  resetFilterBtn.addEventListener('click', () => {
    rangeMin.value = minPrice.value = 1000000;
    rangeMax.value = maxPrice.value = 20000000;
    document.querySelectorAll('input[type="checkbox"]').forEach(i => i.checked = false);
    document.querySelector('.departure-select').value = '';
    tourCards.forEach(card => card.style.display = 'flex');
  });
});
</script>
</body>
</html>