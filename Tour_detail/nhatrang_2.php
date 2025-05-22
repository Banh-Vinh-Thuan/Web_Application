<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nha Trang - Pottery & Seashell Workshop, Ancient Nha Trang & More</title>
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
    <h1>&nbsp;&nbsp;&nbsp; Nha Trang - Pottery & Seashell Workshop, Ancient Nha Trang & More</h1>
    <div class="gallery">
        <div class="big"><img src="nhatrang2/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="nhatrang2/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="nhatrang2/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="nhatrang2/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="nhatrang2/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Visit Bau Truc Pottery Village, one of Southeast Asia’s oldest pottery villages.</li>
                    <li>Explore the rich cultural heritage of ancient Nha Trang and Thap Ba Ponagar.</li>
                    <li>Unwind at the pristine Nhu Tien Beach and experience family-friendly fun at VinWonders.</li>
                    <li>Create your own souvenirs during a unique Pottery & Seashell Workshop Experience.</li>
                    <li>Stay at a comfortable 4-star hotel and enjoy a well-curated travel experience departing from Da Nang.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Da Nang – Nha Trang – Bau Truc Pottery Village</h3>
                    <p>Depart from Da Nang and arrive in Nha Trang. Visit Bau Truc Pottery Village for a hands-on cultural experience. Check in to your 4-star hotel and enjoy free time in the evening.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Ancient Nha Trang – Thap Ba Ponagar – Pottery & Seashell Workshop</h3>
                    <p>Discover the beauty of ancient Nha Trang with a guided visit to Thap Ba Ponagar. In the afternoon, take part in the Pottery & Seashell Workshop and craft your own coastal-inspired creations.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Nhu Tien Beach – VinWonders – Return to Da Nang</h3>
                    <p>Enjoy a relaxing morning at Nhu Tien Beach. Then, spend your afternoon exploring the exciting attractions at VinWonders before returning to Da Nang.</p>
                </div>
            </div>

        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                <h3 style="display: inline;">Price From</h3>
                <p style="color: red; font-weight: bold; display: inline;">2,690,000 VND</p>
                <p style="text-decoration: line-through; color: gray;">3,280,488 VND</p>
                <a href="../booktour.php?cityid=12&tourid=10" class="booking-button">Booking now!</a>
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