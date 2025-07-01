<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phu Yen Hidden Gems: Da Dia Reef - Bai Xep - Mang Lang Church - Nhan Tower </title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="../css/tour.css">
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
    <h1>&nbsp;&nbsp;&nbsp;Phu Yen Hidden Gems: Da Dia Reef - Bai Xep - Mang Lang Church - Nhan Tower </h1>
    <div class="gallery" id="lightgallery">
        <a href="Phuyen1/1.jpg" class="big"><img src="Phuyen1/1.jpg" alt="Da Dia Reef"></a>
        <a href="Phuyen1/2.jpg" class="small1"><img src="Phuyen1/2.jpg" alt="Bai Xep Beach Village"></a>
        <a href="Phuyen1/3.jpg" class="small2"><img src="Phuyen1/3.jpg" alt="Phu Yen Chruch"></a>
        <a href="Phuyen1/4.jpg" class="small3"><img src="Phuyen1/4.jpg" alt="Paka Jatrang"></a>
        <a href="Phuyen1/5.jpg" class="small4"><img src="Phuyen1/5.jpg" alt="Phu Yen Beach"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Marvel at the breathtaking Da Dia Reef with its rare hexagonal rock formations.</li>
                    <li>Relax on the peaceful golden sands of Bai Xep beach.</li>
                    <li>Visit the historic Mang Lang Church, one of Vietnam’s oldest Catholic sites.</li>
                    <li>Explore the ancient Nhan Tower with panoramic views of Tuy Hoa city.</li>
                    <li>Enjoy a scenic train ride along Vietnam’s stunning coastal railway.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Nha Trang – Phu Yen by Train</h3>
                    <p>Depart from Nha Trang by train and arrive in Phu Yen. Check in to the hotel and enjoy an evening stroll along the coastline.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Da Dia Reef – Mang Lang Church</h3>
                    <p>Visit the iconic Da Dia Reef and continue to Mang Lang Church to learn about local history and architecture.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Bai Xep – Nhan Tower</h3>
                    <p>Relax at Bai Xep beach before exploring Nhan Tower, an ancient Cham structure overlooking the city.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Free Time – Return to Nha Trang</h3>
                    <p>Enjoy free time in the morning for last-minute sightseeing or shopping before boarding the train back to Nha Trang.</p>
                </div>
            </div>

        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">5,990,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,304,878 VND</p>
                    <a href="/tour/booking?cityid=14&tourid=17" class="booking-button">Booking now!</a>
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