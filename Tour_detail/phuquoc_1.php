<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phu Quoc Paradise Escape</title>
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
    <h1>&nbsp;&nbsp;&nbsp;Phu Quoc Paradise Escape: Sao Beach - Vinpearl Safari - Night Market - Pepper Farm</h1>
    <div class="gallery" id="lightgallery">
        <a href="phuquoc1/1.jpg" class="big"><img src="phuquoc1/1.jpg" alt="Hon Thom Beach"></a>
        <a href="phuquoc1/2.jpg" class="small1"><img src="phuquoc1/2.jpg" alt="Phu Quoc Beach"></a>
        <a href="phuquoc1/3.jpg" class="small2"><img src="phuquoc1/3.jpg" alt="Phu Quoc Beach"></a>
        <a href="phuquoc1/4.jpg" class="small3"><img src="phuquoc1/4.jpg" alt="Suoi Tranh Waterfall"></a>
        <a href="phuquoc1/5.jpg" class="small4"><img src="phuquoc1/5.jpg" alt="Phu Quoc Night Market"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Relax on the pristine Sao Beach, known for its white sand and clear turquoise waters.</li>
                    <li>Experience the exciting Vinpearl Safari, home to diverse wildlife in natural habitats.</li>
                    <li>Explore the vibrant Phu Quoc Night Market with delicious local food and unique souvenirs.</li>
                    <li>Visit a traditional Pepper Farm to learn about one of Phu Quoc’s famous agricultural products.</li>
                    <li>Enjoy a comfortable trip with direct flight departure from Ho Chi Minh City and quality hotel accommodations.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Ho Chi Minh City – Phu Quoc</h3>
                    <p>Take a flight from Ho Chi Minh City to Phu Quoc. Upon arrival, check in to your hotel and relax. Spend the evening exploring the lively Phu Quoc Night Market.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Sao Beach – Vinpearl Safari</h3>
                    <p>Enjoy a morning at the beautiful Sao Beach, soaking up the sun and swimming in the clear waters. In the afternoon, visit Vinpearl Safari to see exotic animals and learn about conservation efforts.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Pepper Farm – Free Time</h3>
                    <p>Tour a local Pepper Farm to discover how this island specialty is cultivated. Spend the afternoon at leisure, relaxing or exploring on your own.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Leisure Morning – Return to Ho Chi Minh City</h3>
                    <p>Enjoy a free morning to relax or shop for souvenirs before checking out. Transfer to the airport for your return flight to Ho Chi Minh City, concluding your Phu Quoc paradise escape.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">6,290,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,136,364 VND</p>
                    <a href="/tour/booking?cityid=16&tourid=25" class="booking-button">Booking now!</a>
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