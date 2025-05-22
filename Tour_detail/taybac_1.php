<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Northwest in Rice Harvest Season - Nghia Lo - Mu Cang Chai - Sapa - Fansipan - Lai Chau - Dien Bien - Moc Chau</title>
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

    <h1>&nbsp;&nbsp;&nbsp; Nghia Lo - Mu Cang Chai - Sapa - Fansipan - Lai Chau - Dien Bien - Moc Chau</h1>
    <p></p>
    <!-- Gallery placed above the columns -->
    <div class="gallery">

        <div class="big"><img src="taybac/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="taybac/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="taybac/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="taybac/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="taybac/5.jpg" alt="Small 4"></div>
    </div>
    <p></p>
    <!-- Columns start here -->
    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Marvel at golden terraced rice fields during the stunning harvest season in Northern Vietnam.</li>
                    <li>Visit remote highland regions including Nghia Lo, Mu Cang Chai, and Lai Chau for authentic local experiences.</li>
                    <li>Conquer the peak of Fansipan – the "Roof of Indochina" – by cable car or trekking.</li>
                    <li>Discover the cultural richness of ethnic minorities such as H’mong, Thai, and Dao.</li>
                    <li>Relax in the cool climate and natural beauty of Moc Chau Plateau and Dien Bien countryside.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Hanoi – Nghia Lo</h3>
                    <p>Depart from Hanoi and travel to Nghia Lo. Enjoy the peaceful mountain scenery and explore ethnic Thai villages.</p>
                </div>

                <div class="box2">
                    <h3>Day 2: Nghia Lo – Mu Cang Chai</h3>
                    <p>Journey through scenic mountain passes to Mu Cang Chai, famous for its breathtaking terraced rice fields in harvest season.</p>
                </div>

                <div class="box2">
                    <h3>Day 3: Mu Cang Chai – Sapa</h3>
                    <p>Head to Sapa, explore local markets and enjoy the cool mountain air. Visit Cat Cat Village or relax in town.</p>
                </div>

                <div class="box2">
                    <h3>Day 4: Fansipan – Lai Chau</h3>
                    <p>Take a cable car to Fansipan Peak, then continue on a scenic drive to Lai Chau, with stops at waterfalls and hill tribe villages.</p>
                </div>

                <div class="box2">
                    <h3>Day 5: Lai Chau – Dien Bien – Moc Chau</h3>
                    <p>Explore the historical sites in Dien Bien before heading to the lush green plateaus of Moc Chau.</p>
                </div>

                <div class="box2">
                    <h3>Day 6: Moc Chau – Hanoi</h3>
                    <p>Visit tea plantations and flower valleys in Moc Chau, then return to Hanoi, ending your memorable Northern adventure.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">12,179,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">12,779,000 VND</p>
                    <a href="../booktour.php?cityid=10&tourid=1" class="booking-button">Booking now!</a>
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