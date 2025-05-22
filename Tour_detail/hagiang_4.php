<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ha Giang Nature & Culture</title>
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
    <h1>Ha Giang Nature & Culture: Dong Van Karst Plateau - Lung Cam Cultural Village - Ma Pi Leng Panorama - Local Craft Workshops</h1>
    <div class="gallery">
        <div class="big"><img src="hagiang4/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="hagiang4/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="hagiang4/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="hagiang4/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="hagiang4/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore the spectacular Dong Van Karst Plateau, a UNESCO Global Geopark with dramatic limestone formations.</li>
                    <li>Visit Lung Cam Cultural Village to experience traditional H’Mong architecture and authentic local lifestyles.</li>
                    <li>Admire the panoramic views from Ma Pi Leng Pass, one of the most iconic mountain passes in Vietnam.</li>
                    <li>Engage in hands-on experiences at local craft workshops, learning about weaving, embroidery, and ethnic artistry.</li>
                    <li>Enjoy a comfortable and enriching trip with departure from Ho Chi Minh City, staying in a mix of hotels and homestays.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Ho Chi Minh City – Ha Giang</h3>
                    <p>Depart from Ho Chi Minh City and travel to Ha Giang. Upon arrival, check in to your accommodation and rest up for the journey ahead.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Dong Van Karst Plateau – Lung Cam Village</h3>
                    <p>Explore the Dong Van Karst Plateau’s stunning terrain. Visit Lung Cam Cultural Village, home to centuries-old H’Mong houses and colorful cultural heritage.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Ma Pi Leng Panorama – Local Craft Workshops</h3>
                    <p>Take in breathtaking views from Ma Pi Leng Panorama. In the afternoon, participate in local craft workshops led by ethnic artisans.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Free Time – Return to Ho Chi Minh City</h3>
                    <p>Enjoy some leisure time in Ha Giang for shopping or sightseeing before heading back to Ho Chi Minh City by bus, concluding your cultural journey.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">7,150,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">8,300,000 VND</p>
                    <a href="../booktour.php?cityid=18&tourid=36" class="booking-button">Booking now!</a>
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