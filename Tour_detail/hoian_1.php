<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hoi An Ancient Town Discovery</title>
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
    <h1>Hoi An Ancient Town Discovery: Old Quarter - Japanese Bridge - Riverside Market - Lantern Festival</h1>
    <div class="gallery">
        <div class="big"><img src="hoian1/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="hoian1/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="hoian1/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="hoian1/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="hoian1/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Wander through the charming streets of Hoi An Ancient Town, a UNESCO World Heritage Site rich in history and culture.</li>
                    <li>Visit the iconic Japanese Covered Bridge, a symbol of Hoi An's architectural beauty and cultural fusion.</li>
                    <li>Explore the vibrant Riverside Market, filled with local delicacies, handmade goods, and bustling daily life.</li>
                    <li>Experience the magical Lantern Festival, where colorful lanterns light up the old town and riverbanks.</li>
                    <li>Enjoy a relaxing journey with bus transportation from Da Nang and comfortable hotel accommodations.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Da Nang – Hoi An</h3>
                    <p>Depart from Da Nang by bus and arrive in Hoi An. Check in to your hotel and relax. In the evening, enjoy a stroll through the Ancient Town and savor local dishes at a traditional restaurant.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Old Quarter – Japanese Covered Bridge</h3>
                    <p>Spend the day exploring the historical Old Quarter, including ancient houses, temples, and the famous Japanese Covered Bridge. Discover Hoi An’s multicultural heritage and local life.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Riverside Market – Lantern Festival</h3>
                    <p>Visit the Riverside Market in the morning to experience the local atmosphere and shop for souvenirs. In the evening, take part in the magical Lantern Festival and enjoy the town’s enchanting night scenery.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Leisure Morning – Return to Da Nang</h3>
                    <p>Have a relaxing morning with free time to explore or shop. After check-out, return to Da Nang by bus, marking the end of your cultural journey in Hoi An.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">6,490,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,475,000 VND</p>
                    <a href="../booktour.php?cityid=17&tourid=29" class="booking-button">Booking now!</a>
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