<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Moc Chau - Pha Din Pass - Son La - Dien Bien - 5 Days 4 Nights</title>
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

    <h1>&nbsp;&nbsp;&nbsp; Moc Chau - Pha Din Pass - Son La - Dien Bien </h1>
    <p></p>
    <!-- Gallery placed above the columns -->
    <div class="gallery">
        <div class="big"><img src="taybac4/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="taybac4/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="taybac4/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="taybac4/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="taybac4/5.jpg" alt="Small 4"></div>
    </div>
    <p></p>
    <!-- Columns start here -->
    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Admire the poetic beauty of Moc Chau Plateau with green tea hills and blooming flower valleys.</li>
                    <li>Pass through the legendary Pha Din Pass – one of Vietnam’s four great mountain passes.</li>
                    <li>Explore the historical battlefield of Dien Bien Phu and the local ethnic cultures.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Da Nang – Hanoi – Moc Chau</h3>
                    <p>Flight to Hanoi and travel to Moc Chau. Visit tea plantations and enjoy the peaceful scenery.</p>
                </div>

                <div class="box2">
                    <h3>Day 2: Moc Chau – Pha Din Pass – Son La</h3>
                    <p>Drive through the majestic Pha Din Pass, stop for photos and explore Son La city.</p>
                </div>

                <div class="box2">
                    <h3>Day 3: Son La – Dien Bien</h3>
                    <p>Visit Son La prison, then continue to Dien Bien to explore relics from the famous battle.</p>
                </div>

                <div class="box2">
                    <h3>Day 4: Dien Bien Exploration</h3>
                    <p>Tour Dien Bien Phu Historical Victory Museum, A1 Hill, and Muong Thanh Field.</p>
                </div>

                <div class="box2">
                    <h3>Day 5: Dien Bien – Hanoi – Da Nang</h3>
                    <p>Return to Hanoi and fly back to Da Nang. End of journey.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">10,545,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">11,400,000 VND</p>
                    <a href="../booktour.php?cityid=10&tourid=4" class="booking-button">Booking now!</a>
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
        </div>
    </div>
    <div class="credit">©2025 VietTransit</div>
</section>
</footer>

</html>
