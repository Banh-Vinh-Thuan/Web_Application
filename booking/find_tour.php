<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Find Travel Tour</title>
  <link rel="icon" type="image/png" href="../images/favicon.png">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="../css/findtour&hotel.css">
  <script>
    $(document).ready(function () {
      // City name to ID mapping (matching viewjourney.php)
      const cityMapping = {
        'ho chi minh city': 'hcm',
        'hcm': 'hcm',
        'saigon': 'hcm',
        'dalat': 'dalat',
        'da lat': 'dalat',
        'nha trang': 'nhatrang',
        'nhatrang': 'nhatrang',
        'hoi an': 'hoian',
        'hoian': 'hoian',
        'phu quoc': 'phuquoc',
        'phuquoc': 'phuquoc',
        'northern vietnam': 'northern',
        'northern': 'northern',
        'north vietnam': 'northern',
        'hanoi': 'northern',
        'central vietnam': 'central',
        'central': 'central',
        'phu yen': 'phuyen',
        'phuyen': 'phuyen',
        'ha giang': 'hagiang',
        'hagiang': 'hagiang',
        'hue': 'hue',
        'tay bac': 'taybac',
        'taybac': 'taybac',
        'northwest vietnam': 'taybac',
        'northwest': 'taybac'
      };

      $("#city").on("input", function () {
        var query = $(this).val().trim();
        if (query.length >= 1) {
          $.ajax({
            url: "get_cities.php?type=tour",
            method: "GET",
            data: { q: query },
            dataType: "json",
            success: function (data) {
              console.log("Received data:", data);
              let list = data.map(item => `<option value="${item}">`).join("");
              $("#city_list").html(list);
            },
            error: function (xhr, status, error) {
              console.error("AJAX error:", status, error);
              $("#city_list").html("");
            }
          });
        } else {
          $("#city_list").html("");
        }
      });

      $("form").on("submit", function (e) {
        e.preventDefault();
        var city = $("#city").val().trim();
        
        if (city) {
          // Convert city name to lowercase for mapping
          var cityKey = city.toLowerCase();
          
          // Try to find exact match first
          var cityId = cityMapping[cityKey];
          
          // If no exact match, try partial matching
          if (!cityId) {
            for (const [key, value] of Object.entries(cityMapping)) {
              if (key.includes(cityKey) || cityKey.includes(key)) {
                cityId = value;
                break;
              }
            }
          }
          
          // If still no match, default to hcm or show error
          if (!cityId) {
            alert("Không tìm thấy điểm đến phù hợp. Vui lòng thử lại với tên thành phố khác.");
            return;
          }
          
          // Redirect to viewjourney.php with the correct city ID
          window.location.href = `../Journey/viewjourney.php?id=${cityId}`;
        } else {
          alert("Vui lòng nhập tên thành phố!");
        }
      });
    });
  </script>
</head>
<body>
  <?php include __DIR__ . '/../header.php'; ?>
  
  <section class="main-header">
    <div class="header-content">
      <h1>Find your favorite tour</h1>
      <p>Discover amazing tours in Vietnam...</p>
    </div>
    <form class="search-bar" method="GET">
      <div class="search-input">
        <input type="text" id="city" name="city" list="city_list" placeholder="Where do you want to go?" required>
        <datalist id="city_list"></datalist>
      </div>
      <button class="search-button" type="submit">Search</button>
    </form>
  </section>

  <section class="suggestion-section">
    <h2>Tour suggestion</h2>
    <div class="suggestion-list">
      <?php
        // Assuming database connection
        include '../dbconnect.php';
        
        // City ID to string mapping (for generating correct links)
        $cityIdToString = [
            10 => 'northern',  // Northern Vietnam (Hanoi, Sapa, etc.)
            11 => 'hcm',       // Ho Chi Minh City
            12 => 'nhatrang',  // Nha Trang
            13 => 'central',   // Central Vietnam
            14 => 'phuyen',    // Phu Yen
            15 => 'dalat',     // Dalat
            16 => 'phuquoc',   // Phu Quoc
            17 => 'hoian',     // Hoi An
            18 => 'hagiang',   // Ha Giang
            19 => 'hue'        // Hue
        ];
        
        // Debug: Let's also create a reverse mapping to check data consistency
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
        
        // Function to generate image path (same as viewjourney.php)
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
                'northern' => [1, 2, 3, 4] // Same as taybac
            ];
            
            $imageIds = $imageMap[$cityId] ?? [1, 2, 3, 4];
            $imageId = $imageIds[($tourId - 1) % count($imageIds)];
            
            return "../tourphotoID/{$imageId}.jpg";
        }

        // Query to get 4 random tours with city names
        $sql = "SELECT t.tourid, t.tour_name, t.duration_days, t.price_per_person, t.cityid, c.city as city_name 
                FROM tours t 
                JOIN cities c ON t.cityid = c.cityid 
                ORDER BY RAND() LIMIT 6";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            $tour_id = htmlspecialchars($row['tourid']);
            $tour_name = htmlspecialchars($row['tour_name']);
            // Truncate tour name to 30 characters
            $display_name = strlen($tour_name) > 30 ? substr($tour_name, 0, 27) . '...' : $tour_name;
            $duration = htmlspecialchars($row['duration_days']);
            $price = number_format($row['price_per_person'], 0, ',', '.') . 'đ';
            $city_name = htmlspecialchars($row['city_name']);
            $city_id = $row['cityid'];
            
            // Get the correct city string for the link
            $city_string = $cityIdToString[$city_id] ?? 'hcm';
            
            // Fix city display name based on tour content and city ID
            $display_city_name = $city_name;
            
            // Special handling for tours that might be in wrong city
            // If tour name contains northern destinations but city shows something else
            if ((strpos(strtolower($tour_name), 'hanoi') !== false || 
                 strpos(strtolower($tour_name), 'sapa') !== false || 
                 strpos(strtolower($tour_name), 'fansipan') !== false || 
                 strpos(strtolower($tour_name), 'halong') !== false) && 
                $city_id != 10) {
                // This should be Northern Vietnam
                $display_city_name = 'Northern Vietnam';
                $city_string = 'northern';
            }
            
            // Generate correct image path
            $imagePath = generateImagePath($city_string, $tour_id);
      ?>
      <div class="suggestion-item">
        <div class="image-container">
          <img src="<?php echo $imagePath; ?>" alt="<?php echo $tour_name; ?>">
          <div class="rating">
            <span class="score"><?php echo $duration; ?> days</span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3>
          <a href="../Journey/viewjourney.php?id=<?php echo $city_string; ?>">
            <?php echo $display_name; ?>
          </a>
        </h3>
        <p class="location"><?php echo $display_city_name; ?>, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="review-count"><?php echo $price; ?></span>
            <a href="../Journey/tour_detail.php?cityid=<?php echo $city_string; ?>&tourid=<?php echo $tour_id; ?>">Book now</a>
          </div>
        </div>
      </div>
      <?php
          }
        }
        $conn->close();
      ?>
    </div>
  </section>
  <?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>