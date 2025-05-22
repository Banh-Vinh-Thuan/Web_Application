<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Find Travel Tour</title>
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

    .search-input input[type="text"] {
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
  </style>
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
      <div class="suggestion-item">
        <div class="image-container">
          <img src="../tourphotoID/3.jpg" alt="Tay Bac Tour">
          <div class="rating">
            <span class="score">3 days</span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3>Sapa - Fansipan - Y Ty - Bat Xat </h3>
        <p class="location">Tay Bac, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="review-count">9.405.000đ</span>
            <a href="../Tour_detail/taybac_3.php">More information</a>
          </div>
        </div>
      </div>
      <div class="suggestion-item">
        <div class="image-container">
          <img src="../tourphotoID/8.jpg" alt="Ho Chi Minh Tour">
          <div class="rating">
            <span class="score">1 day</span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3>Mekong Delta Tour </h3>
        <p class="location">Ho Chi Minh City, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
           <span class="review-count">1.050.000đ</span>
            <a href="../Tour_detail/hcm_4.php">More information</a>
          </div>
        </div>
      </div>
      <div class="suggestion-item">
        <div class="image-container">
          <img src="../tourphotoID/13.jpg" alt="Hue Tour">
          <div class="rating">
            <span class="score">5 days</span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3>Da Nang - Phong Nha Cave - La Vang..</h3>
        <p class="location">Hue, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="review-count">6.990.000đ</span>
            <a href="../Tour_detail/hue_1.php">More information</a>
          </div>
        </div>
      </div>
      <div class="suggestion-item">
        <div class="image-container">
          <img src="../tourphotoID/20.jpg" alt="Phu Yen Tour">
          <div class="rating">
            <span class="score">5 days</span>
          </div>
          <button class="wishlist-button">❤️</button>
        </div>
        <h3>Phu Yen - Tuy Hoa - Ganh Da Dia...</h3>
        <p class="location">Phu Yen, Vietnam</p>
        <div class="review-info">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="review-count">6.690.000đ</span>
            <a href="../Tour_detail/phuyen_4.php">More information</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</body>
</html>