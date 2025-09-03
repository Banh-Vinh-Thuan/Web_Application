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
    'hue' => 13,           // Central Vietnam
    'phuyen' => 14,        // Phu Yen
    'hagiang' => 18,       // Ha Giang
    'taybac' => 10,        // Tay Bac (Northern Vietnam)
    'danang' => 19,         // Da Nang
    'cantho' => 20,         // Can Tho
    'hanoi' => 21          // Ha Noi

];

// City names mapping
$cityNames = [
    10 => 'Northern Vietnam',
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
      "Nestled in the Central Highlands, Dalat captivates visitors with its eternal spring climate, rolling pine hills, and colonial French charm. The city is a dreamscape of misty mornings, blooming flower valleys, and peaceful lakes like Tuyen Lam and Xuan Huong. It's where romance meets adventure — perfect for couples, families, and nature enthusiasts.",
      "Embark on a Dalat tour with VietTransit to uncover iconic landmarks such as the Valley of Love, Bao Dai Palace, and the colorful Dalat Flower Garden. Discover helpful travel ideas in our <a href=\"/Journey/traveltip.php?destination=dalat\" style=\"color: #fff; text-decoration: underline;\">Dalat Travel Tips</a>."
  ],
  'hagiang' => [
      "Ha Giang — Vietnam's last frontier — invites the bold-hearted to traverse its winding passes and breathtaking cliffs. From the rugged Ma Pi Leng Pass to the soulful beauty of ethnic minority villages, this remote province delivers a cultural adventure set against majestic limestone karsts and golden rice terraces that touch the sky.",
      "Join VietTransit's Ha Giang tour to conquer Dong Van Karst Plateau, visit the Lung Cu Flag Tower, and embrace local traditions. Plan your journey with our <a href=\"/Journey/traveltip.php?destination=hagiang\" style=\"color: #fff; text-decoration: underline;\">Ha Giang Travel Tips</a>."
  ],
  'hcm' => [
      "Ho Chi Minh City pulsates with an electric rhythm — where street food aromas mingle with honking motorbikes and sky-piercing buildings. Beneath its modern pace lies a deep historical narrative, with French colonial architecture, wartime relics, and stories at every street corner. A melting pot of energy, flavor, and culture.",
      "Explore this southern powerhouse with VietTransit tours, from the War Remnants Museum to Ben Thanh Market and vibrant street alleys. Learn how to make the most of your visit in our <a href=\"/Journey/traveltip.php?destination=hcm\" style=\"color: #fff; text-decoration: underline;\">Ho Chi Minh City Travel Tips</a>."
  ],
  'hoian' => [
      "Step into a timeless world in Hoi An, where ochre walls whisper tales of centuries past, and lanterns float softly above ancient streets. This UNESCO World Heritage town is a treasure of Vietnamese, Chinese, and Japanese fusion — from old merchant houses to riverside temples and tailor-made elegance.",
      "Take a VietTransit journey through Hoi An to explore its rich past, shop for handcrafted goods, and taste world-renowned cuisine. Let our <a href=\"/Journey/traveltip.php?destination=hoian\" style=\"color: #fff; text-decoration: underline;\">Hoi An Travel Tips</a> guide your steps."
  ],
  'hue' => [
      "Hue whispers history through its moss-covered imperial walls, poetic Perfume River, and tranquil garden houses. Once the seat of the Nguyen Dynasty, the city remains an evocative blend of royal grandeur and spiritual serenity, adorned with citadels, tombs, and age-old pagodas.",
      "Travel with VietTransit through the heart of Vietnam's heritage — explore the Imperial City, Thien Mu Pagoda, and the tombs of kings. For a deeper look at what Hue offers, visit our <a href=\"/Journey/traveltip.php?destination=hue\" style=\"color: #fff; text-decoration: underline;\">Hue Travel Tips</a>."
  ],
  'nhatrang' => [
      "With golden beaches caressed by gentle waves, Nha Trang is Vietnam's coastal gem. It offers a vibrant marine world, luxurious resorts, and thrilling activities — from snorkeling in Hon Mun to soaking in hot mineral springs or admiring Cham architecture at Ponagar Towers.",
      "Choose a VietTransit tour to experience Nha Trang's tropical beauty, amusement parks, and authentic fishing villages. Get insider tips from our <a href=\"/Journey/traveltip.php?destination=nhatrang\" style=\"color: #fff; text-decoration: underline;\">Nha Trang Travel Tips</a>."
  ],
  'phuquoc' => [
      "Phu Quoc — Vietnam's island paradise — blends lush jungle, white-sand beaches, and a laid-back charm. From snorkeling coral reefs to wandering bustling night markets, it's an escape that balances natural wonders with island culture and comfort.",
      "Book a Phu Quoc tour with VietTransit and uncover pepper farms, fish sauce factories, secluded beaches, and island hopping adventures. Find more highlights in our <a href=\"/Journey/traveltip.php?destination=phuquoc\" style=\"color: #fff; text-decoration: underline;\">Phu Quoc Travel Tips</a>."
  ],
  'phuyen' => [
      "Often called the 'land of yellow flowers on green grass,' Phu Yen is a cinematic coastal province with raw beauty and quiet charm. From cliffs shaped by lava flows like Ganh Da Dia, to untouched bays and fishing villages, it's where simplicity and serenity shine brightest.",
      "Discover Phu Yen with VietTransit to explore Mang Lang Church, Bai Xep, and the soul-soothing countryside. See what awaits you in our <a href=\"/Journey/traveltip.php?destination=phuyen\" style=\"color: #fff; text-decoration: underline;\">Phu Yen Travel Tips</a>."
  ],
  'taybac' => [
      "Northwest Vietnam bursts into color with blooming peach blossoms and fluttering ethnic fabrics. In springtime, villages come alive with highland festivals, traditional music, and a rhythm deeply rooted in ancestral land and legend. It's a place for soul-searchers and mountain lovers alike.",
      "Let VietTransit guide you to the best of Northwest Vietnam — from Moc Chau's plateau to Sapa's cloud-kissed peaks. Dive into culture with our <a href=\"/Journey/traveltip.php?destination=taybac\" style=\"color: #fff; text-decoration: underline;\">Northwest Travel Tips</a>."
  ],
  'danang' => [
    "Da Nang — Vietnam's dynamic coastal hub — is famous for its golden beaches, iconic bridges, and a vibrant mix of modernity and tradition. From the spectacular Golden Bridge in Ba Na Hills to the sacred Marble Mountains and the lively Han River waterfront, Da Nang offers endless discoveries.",
    "Join a Da Nang tour with VietTransit to explore Son Tra Peninsula, My Khe Beach, and the lantern-lit charm of nearby Hoi An. Plan your journey with our <a href=\"/Journey/traveltip.php?destination=danang\" style=\"color: #fff; text-decoration: underline;\">Da Nang Travel Tips</a>."
  ],
  'cantho' => [
    "Can Tho — the vibrant heart of the Mekong Delta — is where winding waterways and lush orchards shape daily life. Famous for its bustling Cai Rang floating market, charming riverbanks, and warm hospitality, Can Tho blends tradition with a laid-back riverside vibe.",
    "Sail through the delta with VietTransit to explore floating markets, fruit gardens, and the cultural soul of the South. Discover more in our <a href=\"/Journey/traveltip.php?destination=cantho\" style=\"color: #fff; text-decoration: underline;\">Can Tho Travel Tips</a>."
  ],
    'hanoi' => [
    "Ha Noi — Vietnam's thousand-year-old capital — is a city where ancient heritage and modern life coexist. From the historic Old Quarter and tranquil Hoan Kiem Lake to the majestic Temple of Literature and the bustling night markets, Ha Noi offers a journey through culture, history, and vibrant street life.",
    "Join a Ha Noi tour with VietTransit to explore Ho Chi Minh Mausoleum, West Lake, and the flavorful world of Vietnamese cuisine. Plan your journey with our <a href=\"/Journey/traveltip.php?destination=hanoi\" style=\"color: #fff; text-decoration: underline;\">Ha Noi Travel Tips</a>."
  ]
];

// City hero images mapping
$cityHeroImages = [
    'hcm' => '../images/hero/hcm.jpg',
    'dalat' => '../images/hero/dalat.jpg',
    'nhatrang' => '../images/hero/nhatrang.jpg',
    'hoian' => '../images/hero/hoian.jpg',
    'phuquoc' => '../images/hero/phuquoc.jpg',
    'phuyen' => '../images/hero/phuyen.jpg',
    'hagiang' => '../images/hero/hagiang.jpg',
    'hue' => '../images/hero/hue.jpg',
    'taybac' => '../images/hero/taybac.jpg',
    'danang' => '../images/hero/danang.jpg',
    'cantho' => '../images/hero/cantho.jpg',
    'hanoi' => '../images/hero/hanoi.jpg'
];

$descriptions = $cityDescriptions[$cityId] ?? [
    "Discover the beauty and culture of " . $displayCity,
    "Join VietTransit for an unforgettable journey through " . $displayCity
];

$heroImage = $cityHeroImages[$cityId] ?? '../images/hero/default-hero.jpg';

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
        'taybac' => [1, 2, 3, 4],
        'danang' => [37, 38, 39, 40],
        'cantho' => [41, 42, 43, 44],
        'hanoi'  => [45, 46, 47, 48]
    ];
    
    $imageIds = $imageMap[$cityId] ?? [1, 2, 3, 4];
    $imageId = $imageIds[($tourId - 1) % count($imageIds)];
    
    return "../tourphotoID/{$imageId}.jpg";
}

// Function to truncate tour name for display
function truncateTourName($name, $maxLength = 65) {
    if (strlen($name) > $maxLength) {
        return substr($name, 0, $maxLength) . '...';
    }
    return $name;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($displayCity) ?> Travel</title>
  <link rel="stylesheet" href="../css/viewjourney.css?v=<?= time() ?>">
  <link rel="icon" type="image/png" href="../images/favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.css">
</head>
<body>
<?php include __DIR__ . '/../header.php'; ?>

<!-- Hero Section -->
<div class="hero-section" style="background-image: url('<?= $heroImage ?>');">
  <div class="hero-overlay">
    <div class="hero-content">
      <h1><?= strtoupper(htmlspecialchars($displayCity)) ?> TRAVEL</h1>
      <div class="hero-description">
        <?php foreach ($descriptions as $description): ?>
          <p><?= $description ?></p>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="main-content">
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
            <div class="tour-info">
              <ul>
                <li><span class="icon">⏰</span> <?= $tour['duration_days'] ?> days</li>
                <li><span class="icon">🚌</span> <?= ucfirst($transport) ?></li>
                <li><span class="icon">🚩</span> Departure: <?= htmlspecialchars($tour['departure_point'] ?? 'Ho Chi Minh City') ?></li>
              </ul>
            </div>
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