<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nha Trang - Hon Mun Island Snorkeling - Institute of Oceanography - Vinpearl Cable Car - Long Son Pagoda - Local Seafood Feast </title>
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
    <h1> Nha Trang - Hon Mun Island Snorkeling - Institute of Oceanography - Vinpearl Cable Car - Long Son Pagoda - Local Seafood Feast </h1>
    <div class="gallery" id="lightgallery">
        <a href="Nhatrang3/1.jpg" class="big"><img src="Nhatrang3/1.jpg" alt="Hon Mun Nha Trang"></a>
        <a href="Nhatrang3/2.jpg" class="small1"><img src="Nhatrang3/2.jpg" alt="Nha Trang Aquarium"></a>
        <a href="Nhatrang3/3.jpg" class="small2"><img src="Nhatrang3/3.jpg" alt="Nha Trang Cable Car"></a>
        <a href="Nhatrang3/4.jpg" class="small3"><img src="Nhatrang3/4.jpg" alt="Long Son Pagoda"></a>
        <a href="Nhatrang3/5.jpg" class="small4"><img src="Nhatrang3/5.jpg" alt="Nha Trang Seafood"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Snorkel among vibrant coral reefs at the famous Hon Mun Island.</li>
                    <li>Visit Vietnam’s oldest marine research center – the Institute of Oceanography.</li>
                    <li>Take a scenic ride on the Vinpearl Cable Car across Nha Trang Bay.</li>
                    <li>Admire the architecture and spiritual ambiance of Long Son Pagoda.</li>
                    <li>Indulge in a delicious local seafood feast by the beach.</li>
                    <li>Relax in comfort at a 4-star beachfront hotel with stunning ocean views.</li>
                    <li>Convenient departure from Ho Chi Minh City by flight.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Ho Chi Minh City – Nha Trang – Institute of Oceanography</h3>
                    <p>Fly from Ho Chi Minh City to Nha Trang. Begin your journey with a visit to the Institute of Oceanography to explore marine biodiversity. Check in to your beachfront 4-star hotel and enjoy free time to relax.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Hon Mun Island Snorkeling – Vinpearl Cable Car – Seafood Feast</h3>
                    <p>Embark on a snorkeling adventure at Hon Mun Island, known for its clear waters and vibrant coral reefs. In the afternoon, enjoy a panoramic cable car ride to Vinpearl. End the day with a delightful local seafood feast.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Long Son Pagoda – Departure</h3>
                    <p>Visit the historic Long Son Pagoda, then have some free time for beach relaxation or shopping before flying back to Ho Chi Minh City.</p>
                </div>
            </div>

        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                <h3 style="display: inline;">Price From</h3>
                <p style="color: red; font-weight: bold; display: inline;">3,190,000 VND</p>
                <p style="text-decoration: line-through; color: gray;">3,752,900 VND</p>
                <a href="/tour/booking?cityid=12&tourid=11" class="booking-button">Booking now!</a>
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