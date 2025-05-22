<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hoi An Heritage & Beach</title>
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
    <h1>Hoi An Heritage & Beach: Ancient Town - My Son Sanctuary - An Bang Beach - Traditional Cooking Class</h1>
    <div class="gallery">
        <div class="big"><img src="hoian3/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="hoian3/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="hoian3/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="hoian3/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="hoian3/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover the historic charm of Hoi An Ancient Town, a UNESCO World Heritage Site with preserved architecture and rich cultural heritage.</li>
                    <li>Visit the sacred My Son Sanctuary, an ancient Champa temple complex nestled in the lush mountains of Quang Nam.</li>
                    <li>Relax at An Bang Beach, one of the most beautiful beaches in Vietnam with soft white sand and clear blue waters.</li>
                    <li>Participate in a traditional Vietnamese cooking class and learn to prepare local dishes with fresh ingredients.</li>
                    <li>Travel conveniently from Hanoi by bus and enjoy a comfortable stay in quality hotel accommodations.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Hanoi – Hoi An</h3>
                    <p>Depart from Hanoi by bus. Arrive in Hoi An in the evening, check in to your hotel, and enjoy a welcome dinner with local specialties.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Ancient Town – Cooking Class</h3>
                    <p>In the morning, explore Hoi An Ancient Town with its iconic architecture and cultural landmarks. In the afternoon, take part in a hands-on cooking class and learn to make traditional Vietnamese dishes.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: My Son Sanctuary – An Bang Beach</h3>
                    <p>Take a morning excursion to My Son Sanctuary and discover its spiritual and historical significance. In the afternoon, unwind at An Bang Beach with free time for swimming and relaxation.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Leisure Morning – Return to Hanoi</h3>
                    <p>Enjoy a relaxing morning to shop or take a final stroll around the town. After checking out, return to Hanoi by bus, ending your memorable Hoi An heritage and beach adventure.</p>
                </div>
            </div>

        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">6,290,000  VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,140,000 VND</p>
                    <a href="../booktour.php?cityid=17&tourid=31" class="booking-button">Booking now!</a>
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