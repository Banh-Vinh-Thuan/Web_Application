<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phu Quoc Island Serenity</title>
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
    <h1>&nbsp;&nbsp;&nbsp;Phu Quoc Island Serenity: Sao Beach - Pepper Farm - Night Market - Sunset at Dinh Cau</h1>
    <div class="gallery">
        <div class="big"><img src="phuquoc3/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="phuquoc3/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="phuquoc3/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="phuquoc3/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="phuquoc3/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Relax on the pristine Sao Beach, famous for its white sand and crystal-clear waters.</li>
                    <li>Visit a local Pepper Farm and learn about the cultivation of Phu Quoc’s famous pepper.</li>
                    <li>Explore the bustling Phu Quoc Night Market with a variety of local foods and unique souvenirs.</li>
                    <li>Enjoy a serene sunset at Dinh Cau Night Market, a scenic and cultural highlight of the island.</li>
                    <li>Travel comfortably with direct flight departure from Da Nang and stay in quality hotel accommodations.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Da Nang – Phu Quoc</h3>
                    <p>Fly from Da Nang to Phu Quoc. Upon arrival, check in to your hotel and relax. Spend your evening exploring the vibrant Phu Quoc Night Market.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Sao Beach – Pepper Farm</h3>
                    <p>Enjoy a morning at the beautiful Sao Beach, soaking up the sun and swimming in the clear waters. In the afternoon, visit a Pepper Farm to discover the island’s famous spice cultivation.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Leisure & Sunset at Dinh Cau</h3>
                    <p>Spend a leisurely day at your own pace. In the evening, experience the stunning sunset at Dinh Cau Night Market, perfect for sightseeing and photography.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Free Morning – Return to Da Nang</h3>
                    <p>Enjoy a free morning to relax or shop before checking out. Transfer to the airport for your return flight to Da Nang, concluding your peaceful Phu Quoc island escape.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">6,490,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,475,000 VND</p>
                    <a href="../booktour.php?cityid=16&tourid=27" class="booking-button">Booking now!</a>
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