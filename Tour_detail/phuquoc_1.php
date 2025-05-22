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
    <div class="gallery">
        <div class="big"><img src="phuquoc1/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="phuquoc1/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="phuquoc1/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="phuquoc1/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="phuquoc1/5.jpg" alt="Small 4"></div>
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
                    <a href="../booktour.php?cityid=16&tourid=25" class="booking-button">Booking now!</a>
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