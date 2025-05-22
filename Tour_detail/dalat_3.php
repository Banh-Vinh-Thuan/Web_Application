<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dalat Flower & Highlands Adventure: Valley of Love - Langbiang Peak - Dalat Railway - Hydrangea Garden</title>
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
    <h1>Dalat Flower & Highlands Adventure: Valley of Love - Langbiang Peak - Dalat Railway - Hydrangea Garden</h1>
    <div class="gallery">
        <div class="big"><img src="dalat3/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="dalat3/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="dalat3/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="dalat3/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="dalat3/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore Dalat's most iconic flower-themed attractions and highland adventures.</li>
                    <li>Stroll through the romantic Valley of Love and colorful Hydrangea Garden.</li>
                    <li>Conquer Langbiang Peak for breathtaking panoramic views of Dalat and the highlands.</li>
                    <li>Enjoy a nostalgic ride on the historic Dalat Railway through scenic countryside.</li>
                    <li>Convenient flight departure from Da Nang and quality hotel accommodation included.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Da Nang – Dalat</h3>
                    <p>Take a flight from Da Nang to Dalat. Upon arrival, check in to your hotel and relax. Spend the evening visiting the Dalat Night Market for local food and souvenirs.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Valley of Love – Langbiang Peak</h3>
                    <p>Begin the day at the romantic Valley of Love with lush gardens and lakeside views. In the afternoon, head to Langbiang Peak for a jeep ride to the summit and panoramic views of the Central Highlands.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Dalat Railway – Hydrangea Garden</h3>
                    <p>Enjoy a nostalgic ride on the vintage Dalat Railway. Then visit the Hydrangea Garden, a stunning location filled with vibrant seasonal blooms – perfect for nature lovers and photography enthusiasts.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Leisure Morning – Return to Da Nang</h3>
                    <p>Have a relaxing morning at your own pace. After checking out, transfer to the airport for your return flight to Da Nang, ending your colorful Dalat adventure.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">6,290,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,400,000 VND</p>
                    <a href="../booktour.php?cityid=15&tourid=23" class="booking-button">Booking now!</a>
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