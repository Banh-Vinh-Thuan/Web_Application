<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nha Trang - Tháp Bà Ponagar, Nhũ Tiên Beach & More</title>
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
    <h1>&nbsp;&nbsp;&nbsp; Nha Trang - Tháp Bà Ponagar, Nhũ Tiên Beach & More</h1>
    <div class="gallery">
        <div class="big"><img src="nhatrang1/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="nhatrang1/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="nhatrang1/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="nhatrang1/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="nhatrang1/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover Nha Trang’s rich cultural heritage and stunning landscapes.</li>
                    <li>Experience luxurious stays at a 4-star hotel with premium amenities.</li>
                    <li>Visit the iconic Tháp Bà Ponagar, relax at Nhũ Tiên Beach, and enjoy thrilling rides at VinWonders.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Arrival & City Exploration</h3>
                    <p>Arrive in Nha Trang, check into the hotel, and visit Tháp Bà Ponagar - a historic Cham temple complex.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Nhu Tien Beach & VinWonders</h3>
                    <p>Relax at Nhu Tien Beach in the morning, then head to VinWonders for a day of fun and adventure.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Bau Truc Pottery Village & Departure</h3>
                    <p>Visit the traditional Bau Truc Pottery Village and explore the new expressway before returning home.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">2,390,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">2,987,500</p>
                <a href="../booktour.php?cityid=12&tourid=9" class="booking-button">Booking now!</a>
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