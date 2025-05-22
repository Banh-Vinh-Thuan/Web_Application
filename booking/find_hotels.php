<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Find Travel Hotel</title>
  <link rel="icon" type="image/png" href="../images/favicon.png">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff;
      color: #333;
    }

    .main-header {
      background-color: #003580;
      color: white;
      padding: 25px 15px 15px;
      text-align: center;
    }

    .header-content {
      max-width: 1125px;
      margin-left: auto;
      margin-right: auto;
      margin-top: 30px;
      text-align: left;
    }

    .main-header h1 {
      margin-bottom: 5px;
      font-weight: bold;
    }

    .main-header p {
      font-size: 18px;
    }

    .search-bar {
      background-color: white;
      border: 3px solid #febb02;
      border-radius: 10px;
      padding: 10px;
      max-width: 1100px;
      margin-top: 50px;
      margin-right: auto;
      margin-bottom: -50px;
      margin-left: auto;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .search-input {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .search-input input[type="text"],
    .search-input select {
      padding: 12px;
      border: none;
      border-radius: 5px;
      font-size: 14px;
      background-color: #f0f0f0;
      flex: 1;
    }

    .search-button {
      background-color: #0071c2;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      font-size: 16px;
      transition: background-color 0.2s ease-in-out;
    }

    .search-button:hover {
      background-color: #005a9e;
    }

    .suggestion-section {
      padding: 30px 20px;
      max-width: 1125px;
      margin: 0 auto;
    }

    .suggestion-section h2 {
      font-size: 22px;
      font-weight: bold;
      margin-bottom: 20px;
      margin-top: 40px;
    }

    .suggestion-list {
      display: flex;
      gap: 20px;
      overflow-x: auto;
    }

    .suggestion-item {
      min-width: 250px;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      flex-shrink: 0;
      overflow: hidden;
    }

    .image-container {
      position: relative;
    }

    .image-container img {
      width: 100%;
      height: 199px;
      object-fit: contain;
    }

    .rating {
      position: absolute;
      bottom: 10px;
      right: 10px;
      background-color: #003580;
      color: white;
      padding: 6px 10px;
      border-radius: 5px;
      font-size: 16px;
      font-weight: bold;
    }

    .booking-badge {
      display: none;
    }

    .wishlist-button {
      position: absolute;
      top: 10px;
      right: 10px;
      background: white;
      border-radius: 50%;
      border: none;
      width: 30px;
      height: 30px;
      font-size: 16px;
      cursor: pointer;
      opacity: 0.8;
    }

    .wishlist-button:hover {
      opacity: 1;
    }

    .suggestion-item h3 {
      margin: 10px;
      font-size: 1em;
      font-weight: bold;
    }

    .location {
      margin: 0 10px;
      color: #555;
      font-size: 0.9em;
    }

    .review-info {
      padding: 10px;
      border-top: 1px solid #eee;
      font-size: 0.9em;
      color: #555;
    }

    .review-info .review-count {
      font-weight: bold;
      color: #003580;
    }

    .rating-count {
      color: #999;
    }

    .star {
      color: #ffd700;
      font-size: 16px;
    }
  </style>
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
          // Map rating values to view_hotels.php expected format
          var ratingMap = {
            "1": "1star",
            "2": "2star",
            "3": "3star",
            "4": "4star",
            "5": "5star",
            "": "All"
          };
          var mappedRating = ratingMap[rating];

          // Map price values to view_hotels.php expected format
          var priceMap = {
            "under": "less",
            "range": "between",
            "above": "above",
            "": "All"
          };
          var mappedPrice = priceMap[price];

          // Construct the URL with city_id, ratings, and cost
          var url = `../view_hotels.php?city_id=${cityId}&ratings=${mappedRating}&cost=${mappedPrice}`;
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
      <div class="suggestion-item">
        <div class="image-container">
          <img src="../hotelphotoID/10.jpg" alt="Sunny B Hotel">
          <div class="rating">
            <span class="score">4</span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3> Gem Market Guest House </h3>
        <p class="location">Tay Bac Region, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="review-count">900.000đ</span>
            <a href="../booking.php?hotel_id=10">Book now</a>
          </div>
        </div>
      </div>

      <div class="suggestion-item">
        <div class="image-container">
          <img src="../hotelphotoID/43.jpg" alt="Son Ca Motel">
          <div class="rating">
            <span class="score">4</span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3>Sole Case Phu Quoc</h3>
        <p class="location">Phu Quoc, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="review-count">1.852.000đ</span>
            <a href="../booking.php?hotel_id=43">Book now</a>
          </div>
        </div>
      </div>
      <div class="suggestion-item">
        <div class="image-container">
          <img src="../hotelphotoID/47.jpg" alt="The Scarlett Boutique Hotel">
          <div class="rating">
            <span class="score">3</span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3>Mira Home</h3>
        <p class="location">Phu Quoc, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="review-count">1.008.000đ</span>
            <a href="../booking.php?hotel_id=47">Book now</a>
          </div>
        </div>
      </div>
      <div class="suggestion-item">
        <div class="image-container">
          <img src="../hotelphotoID/89.jpg" alt="Charm House Hue">
          <div class="rating">
            <span class="score">5</span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3>Sky Bay Lodge</h3>
        <p class="location">Ha Giang, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="review-count">970.000đ</span>
            <a href="../booking.php?hotel_id=89">Book now</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</body>

</html>