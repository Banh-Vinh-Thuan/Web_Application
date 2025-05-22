<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nha Trang - Yang Bay Eco Park - Mud Bath Experience - Dam Market - Thap Ba Ponagar - Coastal Road Discovery </title>
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
    <h1>Nha Trang - Yang Bay Eco Park - Mud Bath Experience - Dam Market - Thap Ba Ponagar - Coastal Road Discovery </h1>
    <div class="gallery">
        <div class="big"><img src="nhatrang4/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="nhatrang4/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="nhatrang4/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="nhatrang4/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="nhatrang4/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover the natural beauty and cultural charm of Yang Bay Eco Park.</li>
                    <li>Relax and rejuvenate with a traditional Nha Trang mud bath experience.</li>
                    <li>Visit Thap Ba Ponagar, a historical Cham temple complex.</li>
                    <li>Explore Dam Market, a vibrant hub for local specialties and souvenirs.</li>
                    <li>Enjoy a scenic drive along Nha Trang’s picturesque coastal roads.</li>
                    <li>Stay at a comfortable 3-star hotel conveniently located near the beach.</li>
                    <li>Easy and quick flight departure from Ho Chi Minh City.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Ho Chi Minh City – Nha Trang – Yang Bay Eco Park</h3>
                    <p>Fly from Ho Chi Minh City to Nha Trang. Upon arrival, travel to Yang Bay Eco Park to immerse yourself in lush nature, waterfalls, and cultural experiences. Check in to your 3-star hotel for an evening at leisure.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Mud Bath Experience – Thap Ba Ponagar – Coastal Road</h3>
                    <p>Start the day with a relaxing mud bath session. Visit the sacred Thap Ba Ponagar temple, then enjoy a scenic coastal drive with multiple photo stops. Return to the hotel and unwind or explore the local night scene.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Dam Market – Departure</h3>
                    <p>In the morning, visit Dam Market to shop for local products and souvenirs. Afterward, transfer to the airport for your return flight to Ho Chi Minh City.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">2,890,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">3,481,200 VND</p>
                    <a href="../booktour.php?cityid=12&tourid=12" class="booking-button">Booking now!</a>
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