<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phu Yen - Quy Nhon - Ky Co - Bai Xep - Ganh Da Dia</title>
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
    <h1>&nbsp;&nbsp;&nbsp;Phu Yen - Quy Nhon - Ky Co - Bai Xep - Ganh Da Dia</h1>
    <div class="gallery">
        <div class="big"><img src="phuyen3/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="phuyen3/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="phuyen3/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="phuyen3/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="phuyen3/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover two coastal gems of Central Vietnam: Phu Yen and Quy Nhon.</li>
                    <li>Relax on the picturesque beaches of Ky Co and Bai Xep with crystal-clear waters.</li>
                    <li>Marvel at the extraordinary hexagonal rock formations of Ganh Da Dia.</li>
                    <li>Enjoy authentic Central Vietnamese cuisine and fresh seafood.</li>
                    <li>Comfortable stay in quality hotels along the coast.</li>
                    <li>Convenient flight departure from Hanoi for a seamless travel experience.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Hanoi – Phu Yen</h3>
                    <p>Fly from Hanoi to Phu Yen. Upon arrival, check in to your hotel and relax. Enjoy a welcome dinner featuring local specialties.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Ganh Da Dia – Bai Xep</h3>
                    <p>Visit the fascinating Ganh Da Dia with its natural basalt columns. Spend the afternoon at Bai Xep beach, famous for its golden sand and calm waters.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Transfer to Quy Nhon – Ky Co Beach</h3>
                    <p>Travel to Quy Nhon and enjoy a day trip to Ky Co Beach, known for its turquoise sea and dramatic cliffs. Optional snorkeling or boat ride available.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Free Morning – Return to Hanoi</h3>
                    <p>Have free time in the morning to explore the local area or relax before flying back to Hanoi.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">8,490,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">9,758,621 VND</p>
                    <a href="../booktour.php?cityid=14&tourid=19" class="booking-button">Booking now!</a>
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