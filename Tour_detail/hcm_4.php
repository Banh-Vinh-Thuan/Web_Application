<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mekong Delta Tour Departing from Ho Chi Minh City </title>
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

    <h1>&nbsp;&nbsp;&nbsp; Mekong Delta Tour Departing from Ho Chi Minh City</h1>
    <div class="gallery">
        <div class="big"><img src="hcm4/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="hcm4/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="hcm4/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="hcm4/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="hcm4/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Escape the city for a day and experience the peaceful life of the Mekong Delta.</li>
                    <li>Enjoy scenic boat rides along winding rivers and lush canals.</li>
                    <li>Visit traditional villages, workshops, and tropical fruit orchards.</li>
                    <li>Sample delicious local cuisine and interact with friendly locals.</li>
                    <li>Convenient daily departures with comfortable bus and boat transport.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Morning: Departure from Ho Chi Minh City</h3>
                    <p>Depart by bus from Ho Chi Minh City to the Mekong Delta region. Enjoy the rural scenery along the way.</p>
                </div>
                <div class="box2">
                    <h3>Midday: Boat Ride & Local Experience</h3>
                    <p>Take a relaxing boat ride through the canals, visit a local village, and explore traditional workshops and tropical orchards.</p>
                </div>
                <div class="box2">
                    <h3>Afternoon: Lunch & Cultural Activities</h3>
                    <p>Enjoy a traditional Vietnamese lunch, followed by cultural performances or cycling through the countryside (optional).</p>
                </div>
                <div class="box2">
                    <h3>Evening: Return to Ho Chi Minh City</h3>
                    <p>Head back to the city by bus, arriving in the late afternoon or early evening.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
               <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">1,050,000  ₫</p>
                    <p style="text-decoration: line-through; color: gray;">1,100,000 ₫</p>
                    <a href="../booktour.php?cityid=11&tourid=8" class="booking-button">Booking now!</a>
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
