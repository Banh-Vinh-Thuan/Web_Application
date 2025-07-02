<?php
// viewjourney.php - universal tour viewer

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy city ID từ URL, mặc định là 'dalat'
$cityId = $_GET['id'] ?? 'hcm';

// Đọc dữ liệu từ file JSON
$tourJson = file_get_contents(__DIR__ . '/../Journey/tour.json');
$tourData = json_decode($tourJson, true);

// Kiểm tra cityId có tồn tại trong JSON không
if (!isset($tourData[$cityId])) {
    echo "<h2>Tour not found for city ID: $cityId</h2>";
    exit;
}

// Lấy dữ liệu city
$cityData = $tourData[$cityId];
$tours = $cityData['tours'];
$descriptions = $cityData['description'];

// Lấy tên hiển thị từ thuộc tính 'city' trong tour đầu tiên
$displayCity = $tours[0]['city'] ?? ucfirst($cityId);
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
    <div class="tour-content">
      <div class="tour-container">
        <?php foreach ($tours as $tour): ?>
          <div class="tour-card"
               data-price="<?= $tour['price'] ?>"
               data-duration="<?= $tour['duration'] ?>"
               data-destination="<?= $cityId ?>"
               data-departure="<?= $tour['departure'] ?>"
               data-transport="<?= $tour['transport'] ?>">
            <div class="tour-image">
              <img src="<?= $tour['image'] ?>" alt="<?= $tour['title'] ?>">
              <span class="discount-badge">-<?= $tour['discount'] ?>%</span>
            </div>
            <div class="tour-details">
              <h3><?= $tour['title'] ?></h3>
              <ul>
                <li><span class="icon">⏰</span> <?= $tour['duration'] ?> days</li>
                <li><span class="icon">🚌</span> <?= ucfirst($tour['transport']) ?></li>
                <li><span class="icon">🚩</span> Departure: <?= $tour['departure'] ?></li>
              </ul>
              <div class="price-action">
                <p class="price"><?= number_format($tour['price'], 0, ',', '.') ?> ₫
                  <span class="original-price"><?= number_format($tour['original_price'], 0, ',', '.') ?> ₫</span>
                </p>
                <a href="<?= $tour['link'] ?>" class="btn">See More</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
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