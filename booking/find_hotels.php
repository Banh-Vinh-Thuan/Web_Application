<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Find Travel Hotel</title>
  <link rel="icon" type="image/png" href="../images/favicon.png">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="../css/findhotel.css">
  <script>
    var cityMap = {};
    $(document).ready(function () {
      $("#destination").on("input", function () {
        var query = $(this).val().trim();
        if (query.length >= 1) {
          $.ajax({
            url: "get_hotel.php",
            method: "GET",
            data: { q: query },
            dataType: "json",
            success: function (data) {
              cityMap = {};
              let list = data.map(item => {
                cityMap[item.name] = item.id;
                return `<option value="${item.name}">`;
              }).join("");
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
        var cityName = $("#destination").val().trim();
        var cityId = cityMap[cityName];
        var rating = $("#rating").val();
        var price = $("#price-range").val();

        if (cityId) {
          var ratingMap = {
            "1": "1star",
            "2": "2star",
            "3": "3star",
            "4": "4star",
            "5": "5star",
            "": "All"
          };
          var mappedRating = ratingMap[rating];

          var priceMap = {
            "under": "less",
            "range": "between",
            "above": "above",
            "": "All"
          };
          var mappedPrice = priceMap[price];

          var url = `../hotelinfo/view_hotels.php?city_id=${cityId}&ratings=${mappedRating}&cost=${mappedPrice}`;
          window.location.href = url;
        } else {
          alert("City not available from the list");
        }
      });
    });
  </script>
</head>

<body>
  <header class="main-header">
    <div class="header-content">
      <h1 style="font-size: 50px">Find your next stay</h1>
      <p style="font-size: 20px">Search deals on hotels, homes, and much more...</p>
    </div>
    <form class="search-bar" method="GET">
      <div class="search-input">
        <input type="text" id="destination" name="city" list="city_list" placeholder="Where do you want to go ?" required>
        <datalist id="city_list"></datalist>
      </div>
      <div class="search-input">
        <select id="rating" name="rating">
          <option value="">Rating</option>
          <option value="1">1 Star ⭐</option>
          <option value="2">2 Star ⭐⭐</option>
          <option value="3">3 Star ⭐⭐⭐</option>
          <option value="4">4 Star ⭐⭐⭐⭐</option>
          <option value="5">5 Star ⭐⭐⭐⭐⭐</option>
        </select>
      </div>
      <div class="search-input">
        <select id="price-range" name="price">
          <option value="">Price range</option>
          <option value="under">Under 1.000.000đ</option>
          <option value="range">From 1.000.000đ - 2.000.000đ</option>
          <option value="above">Above 2.000.000đ</option>
        </select>
      </div>
      <button class="search-button" type="submit">Search</button>
    </form>
  </header>

  <section class="suggestion-section">
    <h2>Interested in these properties?</h2>
    <div class="suggestion-list">
      <?php
        // Assuming database connection
       include '../dbconnect.php';

        // Query to get 4 random hotels with city names
        $sql = "SELECT h.hotelid, h.hotel, h.cost, h.ratings, c.city as city_name 
                FROM hotels h 
                JOIN cities c ON h.cityid = c.cityid 
                ORDER BY RAND() LIMIT 4";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            $hotel_id = htmlspecialchars($row['hotelid']);
            $hotel_name = htmlspecialchars($row['hotel']);
            $cost = number_format($row['cost'], 0, ',', '.') . 'đ';
            $rating = htmlspecialchars($row['ratings']);
            $city_name = htmlspecialchars($row['city_name']);
      ?>
      <div class="suggestion-item">
        <div class="image-container">
          <img src="../hotelphotoID/<?php echo $hotel_id; ?>.jpg" alt="<?php echo $hotel_name; ?>">
          <div class="rating">
            <span class="score"><?php echo $rating; ?></span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3><?php echo $hotel_name; ?></h3>
        <p class="location"><?php echo $city_name; ?>, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="review-count"><?php echo $cost; ?></span>
            <a href="../hotelinfo/hoteldescription.php?hotel_id=<?php echo $hotel_id; ?>">Book now</a>
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