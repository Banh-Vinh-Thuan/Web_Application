<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hoi An Cultural Experience</title>
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
    <h1>Hoi An Cultural Experience: Ancient Town - Riverside Market - Traditional Craft Villages - Lantern Festival</h1>
    <div class="gallery">
        <div class="big"><img src="hoian2/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="hoian2/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="hoian2/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="hoian2/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="hoian2/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover the charm of Hoi An Ancient Town, a UNESCO World Heritage Site with centuries-old architecture and vibrant local life.</li>
                    <li>Experience the lively Riverside Market, where you can shop for local specialties and enjoy authentic street food.</li>
                    <li>Explore traditional craft villages to learn about lantern-making, pottery, and other unique Vietnamese artisanal traditions.</li>
                    <li>Immerse yourself in the magical Lantern Festival, with colorful lanterns illuminating the historic town and riverbanks.</li>
                    <li>Travel comfortably from Hanoi by bus and enjoy cozy accommodations throughout your journey.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Hanoi – Hoi An</h3>
                    <p>Depart from Hanoi by bus in the early morning. Arrive in Hoi An and check in to your hotel. Spend your evening exploring the Ancient Town and enjoy a local dinner.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Ancient Town – Craft Villages</h3>
                    <p>Stroll through the Ancient Town, visit cultural landmarks, and then explore traditional craft villages where you can try your hand at lantern-making and pottery.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Riverside Market – Lantern Festival</h3>
                    <p>Start your day at the Riverside Market for a taste of local life and souvenirs. In the evening, join the stunning Lantern Festival and admire the magical ambiance by the river.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Leisure Morning – Return to Hanoi</h3>
                    <p>Enjoy a relaxed morning with time to shop or explore on your own. After check-out, return to Hanoi by bus, concluding your cultural journey in Hoi An.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">6,390,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,100,000 VND</p>
                    <a href="../booktour.php?cityid=17&tourid=30" class="booking-button">Booking now!</a>
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