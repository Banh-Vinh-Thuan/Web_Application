<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Find Travel Tour</title>
  <link rel="icon" type="image/png" href="../images/favicon.png">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="../css/findtour.css">
  <script>
    $(document).ready(function () {
      $("#city").on("input", function () {
        var query = $(this).val().trim();
        if (query.length >= 1) {
          $.ajax({
            url: "get_tour.php",
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
          var citySlug = city.toLowerCase().replace(/\s+/g, '_');
          window.location.href = `../Journey/viewjourney_${citySlug}.php`;
        } else {
          alert("Vui lòng nhập tên thành phố!");
        }
      });
    });
  </script>
</head>
<body>
  <header class="main-header">
    <div class="header-content">
      <h1 style="font-size: 50px">Find your favorite tour</h1>
      <p style="font-size: 20px">Discover amazing tours in Vietnam...</p>
    </div>
    <form class="search-bar" method="GET">
      <div class="search-input">
        <input type="text" id="city" name="city" list="city_list" placeholder="Where do you want to go ?" required>
        <datalist id="city_list"></datalist>
      </div>
      <button class="search-button" type="submit">Search</button>
    </form>
  </header>

  <section class="suggestion-section">
    <h2>Tour suggestion</h2>
    <div class="suggestion-list">
      <?php
        // Assuming database connection
       include '../dbconnect.php';
        // Query to get 4 random tours with city names
        $sql = "SELECT t.tourid, t.tour_name, t.duration_days, t.price_per_person, c.city as city_name 
                FROM tours t 
                JOIN cities c ON t.cityid = c.cityid 
                ORDER BY RAND() LIMIT 4";
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
            // Create slug for tour detail link
            $city_slug = strtolower($city_name);
            $city_slug = str_replace(' ', '_', $city_slug);
      ?>
      <div class="suggestion-item">
        <div class="image-container">
          <img src="../tourphotoID/<?php echo $tour_id; ?>.jpg" alt="<?php echo $tour_name; ?>">
          <div class="rating">
            <span class="score"><?php echo $duration; ?> days</span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3><?php echo $display_name; ?></h3>
        <p class="location"><?php echo $city_name; ?>, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="review-count"><?php echo $price; ?></span>
           
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