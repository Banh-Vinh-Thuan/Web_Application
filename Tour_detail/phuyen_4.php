<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phu Yen - Tuy Hoa - Ganh Da Dia - Vung Ro Bay - Mang Lang Church </title>
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
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
    <h1>&nbsp;&nbsp;&nbsp;Phu Yen - Tuy Hoa - Ganh Da Dia - Vung Ro Bay - Mang Lang Church </h1>
    <div class="gallery">
        <div class="big"><img src="phuyen4/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="phuyen4/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="phuyen4/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="phuyen4/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="phuyen4/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore the serene beauty of Phu Yen and the coastal charm of Tuy Hoa city.</li>
                    <li>Visit Ganh Da Dia, a rare geological wonder with unique hexagonal rock columns.</li>
                    <li>Cruise through Vung Ro Bay, a site rich in both history and natural beauty.</li>
                    <li>Discover Mang Lang Church, one of Vietnam’s oldest Catholic landmarks.</li>
                    <li>Experience an unforgettable train journey along the scenic coastline of Central Vietnam.</li>
                    <li>Departure from Da Nang with all transportation and accommodation arranged for comfort.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Da Nang – Phu Yen by Train</h3>
                    <p>Board a scenic train ride from Da Nang to Phu Yen. Enjoy coastal views along the way. Arrive in Tuy Hoa, check in to your hotel, and rest.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Ganh Da Dia – Mang Lang Church</h3>
                    <p>Explore the stunning Ganh Da Dia rock formations in the morning. In the afternoon, visit the historic Mang Lang Church and learn about its cultural significance.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Vung Ro Bay Exploration</h3>
                    <p>Take a boat tour through Vung Ro Bay, admire its clear blue waters and surrounding mountains. Discover its wartime stories and scenic charm.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Tuy Hoa City Tour</h3>
                    <p>Explore Tuy Hoa at your own pace — visit local markets, enjoy coastal views, or relax at the beach. Optional street food tour available in the evening.</p>
                </div>
                <div class="box2">
                    <h3>Day 5: Return to Da Nang</h3>
                    <p>Enjoy breakfast, check out, and board the train back to Da Nang, concluding your coastal adventure.</p>
                </div>
            </div>

        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">6,690,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,779,070 VND</p>
                    <a href="../booktour.php?cityid=14&tourid=20" class="booking-button">Booking now!</a>
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

<footer>
<section class="footer">
    <div class="box-container">
        <div class="box">
            <h3>Quick links</h3>
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#products">Places</a>
            <a href="#review">Review</a>
        </div>
        <div class="box">
            <h3>Extra links</h3>
            <a href="/Login/profile.php">My account</a>
            <a href="/Payment Interface/receiptlist.php">My List</a>
            <a href="/Login/profile.php">My favorite</a>
        </div>
        <div class="box">
            <h3>Popular Travel Locations</h3>
            <a href="/journey/viewjourney_taybac.php">Tay Bac</a>
            <a href="/journey/viewjourney_hcm.php">Ho Chi Minh</a>
            <a href="/journey/viewjourney_phuquoc.php">Phu Quoc</a>
            <a href="/journey/viewjourney_hue.php">Hue</a>
        </div>
        <div class="box">
            <h3>contact info</h3>
            <a href="https://github.com/socolate12345/Travel-Booking-Website">GitHub</a>
            <img src="./images/payment.png" alt="">
        </div>
    </div>
    <div class="credit">©2025 VietTransit</div>
</section>
</footer>
</html>