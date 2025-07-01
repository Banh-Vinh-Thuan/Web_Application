<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saigon City Tour - Duc Ba Church, Nguyen Hue Street, Landmark 81, Bui Vien, Dinh Doc Lap </title>
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
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

    <h1>&nbsp;&nbsp;&nbsp;  Saigon City Tour - Duc Ba Church, Nguyen Hue Street, Landmark 81, Bui Vien, Dinh Doc Lap </h1>
    <div class="gallery" id="lightgallery">
        <a href="hcm2/1.jpg" class="big"><img src="hcm2/1.jpg" alt="Saigon Street Food"></a>
        <a href="hcm2/2.jpg" class="small1"><img src="hcm2/2.jpg" alt="Nguyen Hue Street"></a>
        <a href="hcm2/3.jpg" class="small2"><img src="hcm2/3.jpg" alt="Landmark 81"></a>
        <a href="hcm2/4.jpg" class="small3"><img src="hcm2/4.jpg" alt="Ben Thanh Market"></a>
        <a href="hcm2/5.jpg" class="small4"><img src="hcm2/5.jpg" alt="Norodom"></a>
    </div>
    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover Saigon's iconic landmarks over three days of exploration.</li>
                    <li>Experience the majestic architecture of Duc Ba Church and the vibrant Nguyen Hue Street.</li>
                    <li>Enjoy panoramic city views from Landmark 81 and the bustling nightlife of Bui Vien Walking Street.</li>
                    <li>Explore the historical significance of Dinh Doc Lap.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Duc Ba Church & Nguyen Hue Street</h3>
                    <p>Start your journey with a visit to the iconic Duc Ba Church followed by a stroll along Nguyen Hue Street, a lively walking area filled with cafes and street performers.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Landmark 81 & Bui Vien Walking Street</h3>
                    <p>Ascend Landmark 81 for panoramic city views, then head to Bui Vien for a vibrant evening filled with food, music, and nightlife.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Dinh Doc Lap & Ben Thanh Market</h3>
                    <p>Conclude the tour with a visit to the historic Dinh Doc Lap and shop for souvenirs at Ben Thanh Market.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
               <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">890,000 ₫</p>
                    <p style="text-decoration: line-through; color: gray;">933,000 ₫</p>
                    <a href="/tour/booking?cityid=11&tourid=6" class="booking-button">Booking now!</a>
                </div>
            </div>
            <div class="box">
                <h3>Contact Support</h3>
                <p>📞 Hotline: 1919 2025<br>✉️ Email: viettransit.support@mail.com</p>
            </div>
            <div class="box">
                <h3>Why Book Online?</h3>
                <ul>
                    <li>Safe & Secure</li>
                    <li>Convenient & Time-saving</li>
                    <li>No hidden fees</li>
                    <li>Exclusive deals</li>
                </ul>
            </div>
            <div class="box">
                <h3>Trusted Tour</h3>
                <p>Founded in 2025<br>Leading travel brand<br>Nationally recognized</p>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/lightgallery.min.js"></script>
<script>
    lightGallery(document.getElementById('lightgallery'), {
        thumbnail: true,
        animateThumb: true,
        showThumbByDefault: true,
        mode: 'lg-slide',
        download: false,
        share: false
    });
</script>
</body>
</html>
