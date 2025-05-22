<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ha Giang Adventure & Culture</title>
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
    <h1>Ha Giang Adventure & Culture: Ma Pi Leng Pass - Dong Van Plateau - Lung Cu Flag Tower - Ethnic Villages</h1>
    <div class="gallery">
        <div class="big"><img src="hagiang1/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="hagiang1/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="hagiang1/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="hagiang1/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="hagiang1/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore the breathtaking Ma Pi Leng Pass, one of Vietnam’s most spectacular mountain passes with stunning views.</li>
                    <li>Discover the unique landscape of Dong Van Karst Plateau, a UNESCO Global Geopark.</li>
                    <li>Visit the historic Lung Cu Flag Tower, symbolizing the northernmost point of Vietnam.</li>
                    <li>Immerse yourself in the vibrant culture of local ethnic villages, experiencing traditional lifestyles and customs.</li>
                    <li>Enjoy a convenient and comfortable journey with departure from Hanoi, staying in quality hotels along the way.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Hanoi – Ha Giang</h3>
                    <p>Depart from Hanoi by bus. Arrive in Ha Giang and check into your hotel. Spend the evening preparing for the adventure ahead or exploring the local town.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Ma Pi Leng Pass – Dong Van Plateau</h3>
                    <p>Experience the majestic Ma Pi Leng Pass with panoramic views. Continue to Dong Van Plateau to explore the unique karst landscape and visit local markets.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Lung Cu Flag Tower – Ethnic Villages</h3>
                    <p>Visit Lung Cu Flag Tower, the northernmost point of Vietnam. Discover nearby ethnic minority villages, learning about their customs and daily life.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Leisure Morning – Return to Hanoi</h3>
                    <p>Enjoy a relaxed morning in Ha Giang before departing back to Hanoi by bus, concluding your Ha Giang adventure.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">6,890,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,830,000 VND</p>
                    <a href="../booktour.php?cityid=18&tourid=33" class="booking-button">Booking now!</a>
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