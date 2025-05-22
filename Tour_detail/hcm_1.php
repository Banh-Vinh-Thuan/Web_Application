<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>City Tour - 4-Hour Double-Decker Bus Saigon - Cholon & Culinary Experience</title>
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

    <h1>&nbsp;&nbsp;&nbsp;  Discover Ho Chi Minh - Cu Chi Tunnels - Mekong Delta</h1>
    <div class="gallery">
        <div class="big"><img src="hcm1/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="hcm1/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="hcm1/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="hcm1/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="hcm1/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Dive into Vietnam’s history at the Cu Chi Tunnels</li>
                    <li>Experience the vibrant local life of the Mekong Delta</li>
                    <li>Explore iconic landmarks of Ho Chi Minh City</li>
                    <li>Enjoy comfortable travel and hotel accommodations</li>
                    <li>Perfect for small groups with daily departures</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Ho Chi Minh City Highlights</h3>
                    <p>Discover the city's colonial charm with visits to Notre-Dame Cathedral, the Central Post Office, and local markets.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Cu Chi Tunnels</h3>
                    <p>Travel to the Cu Chi Tunnels, explore underground passageways, and learn about wartime resilience.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Mekong Delta Exploration</h3>
                    <p>Cruise along the Mekong River, visit floating markets and local workshops, and enjoy traditional Mekong cuisine.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Free Time & Departure</h3>
                    <p>Relax or explore the city at your own pace before returning home.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">4,590,000 ₫</p>
                    <p style="text-decoration: line-through; color: gray;">4,840,000 ₫</p>
                    <a href="../booktour.php?cityid=11&tourid=5" class="booking-button">Booking now!</a>
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
