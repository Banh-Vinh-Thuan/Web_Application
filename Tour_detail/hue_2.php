<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>North-Central Tour: Hanoi - Sapa - Fansipan - Ha Long - Ninh Binh - Da Nang - Son Tra - Ba Na Hills - Golden Bridge - Hoi An - La Vang - Hue - Phong Nha</title>
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
    <h1>North - Central Tour: Hanoi - Sapa - Fansipan - Ha Long - Ninh Binh - Da Nang - Son Tra - Ba Na Hills - Golden Bridge - Hoi An - La Vang - Hue - Phong Nha</h1>
    <div class="gallery">
        <div class="big"><img src="hue2/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="hue2/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="hue2/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="hue2/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="hue2/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Comprehensive journey through Vietnam’s cultural and natural highlights.</li>
                    <li>Conquer Fansipan, the highest peak in Indochina.</li>
                    <li>Marvel at Ha Long Bay, the Golden Bridge, and Hoi An’s ancient charm.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1-3: Hanoi - Sapa - Fansipan</h3>
                    <p>Explore the capital, then head to Sapa to conquer Fansipan Peak.</p>
                </div>
                <div class="box2">
                    <h3>Day 4-6: Ha Long - Ninh Binh</h3>
                    <p>Discover Ha Long Bay’s limestone karsts and the serene beauty of Ninh Binh.</p>
                </div>
                <div class="box2">
                    <h3>Day 7-8: Da Nang - Ba Na Hills - Hoi An</h3>
                    <p>Experience Son Tra Peninsula, the Golden Bridge, and the charm of Hoi An.</p>
                </div>
                <div class="box2">
                    <h3>Day 9-10: Hue - Phong Nha</h3>
                    <p>Visit Hue’s Imperial City and explore the caves at Phong Nha.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">23,590,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">26,211,111 VND</p>
                    <a href="../booktour.php?cityid=13&tourid=14" class="booking-button">Booking now!</a>
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