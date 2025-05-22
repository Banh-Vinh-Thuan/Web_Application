<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hung Temple - Nghia Lo - Tu Le - Mu Cang Chai - Sapa - Fansipan - Dien Bien - Moc Chau Tour</title>
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
    <h1>&nbsp;&nbsp;&nbsp;  Hung Temple - Nghia Lo - Tu Le - Mu Cang Chai - Sapa - Fansipan - Dien Bien - Moc Chau Tour </h1>
    <div class="gallery">
        <div class="big"><img src="taybac2/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="taybac2/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="taybac2/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="taybac2/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="taybac2/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Visit the sacred Hung Temple, an important spiritual and cultural site in Vietnamese history.</li>
                    <li>Admire the golden rice terraces in Tu Le and Mu Cang Chai during the harvest season.</li>
                    <li>Conquer Fansipan – the Roof of Indochina – with a spectacular cable car ride.</li>
                    <li>Explore Sapa’s unique ethnic culture and highland charm.</li>
                    <li>Enjoy a scenic and insightful journey through Dien Bien and the peaceful Moc Chau Plateau.</li>
                    <li>Small group tour with convenient departures from Ho Chi Minh City.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Ho Chi Minh City – Hanoi – Hung Temple – Nghia Lo</h3>
                    <p>Fly to Hanoi and travel to the historic Hung Temple. Continue to Nghia Lo to experience the charm of Thai ethnic culture.</p>
                </div>

                <div class="box2">
                    <h3>Day 2: Nghia Lo – Tu Le – Mu Cang Chai – Sapa</h3>
                    <p>Enjoy the stunning views of terraced fields in Tu Le and Mu Cang Chai, then head to Sapa for an overnight stay.</p>
                </div>

                <div class="box2">
                    <h3>Day 3: Fansipan – Dien Bien</h3>
                    <p>Ride the cable car to the peak of Fansipan Mountain. Afterward, journey through breathtaking mountain roads to reach Dien Bien.</p>
                </div>

                <div class="box2">
                    <h3>Day 4: Dien Bien – Moc Chau – Hanoi – Ho Chi Minh City</h3>
                    <p>Explore Moc Chau’s tea hills and green landscapes before flying back to Ho Chi Minh City from Hanoi.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">13,590,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">14,590,000 VND</p>
                    <a href="../booktour.php?cityid=10&tourid=2" class="booking-button">Booking now!</a>
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