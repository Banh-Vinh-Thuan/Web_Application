<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phu Yen - Mui Dien Sunrise - Vung Ro Bay - Bai Mon Beach - Local Seafood Tasting </title>
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
    <h1>&nbsp;&nbsp;&nbsp;Phu Yen - Mui Dien Sunrise - Vung Ro Bay - Bai Mon Beach - Local Seafood Tasting </h1>
    <div class="gallery">
        <div class="big"><img src="phuyen2/1.jpg" alt="Big Image"></div>
        <div class="small1"><img src="phuyen2/2.jpg" alt="Small 1"></div>
        <div class="small2"><img src="phuyen2/3.jpg" alt="Small 2"></div>
        <div class="small3"><img src="phuyen2/4.jpg" alt="Small 3"></div>
        <div class="small4"><img src="phuyen2/5.jpg" alt="Small 4"></div>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Witness a stunning sunrise at Mui Dien – the easternmost point of Vietnam.</li>
                    <li>Cruise through the scenic Vung Ro Bay, rich in natural beauty and historical significance.</li>
                    <li>Unwind on the pristine sands of Bai Mon Beach with turquoise waters and gentle waves.</li>
                    <li>Savor authentic local seafood fresh from the coast.</li>
                    <li>Stay in a luxurious 4-star beach resort for ultimate relaxation and comfort.</li>
                    <li>Convenient flight departure from Ho Chi Minh City for easy travel access.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Ho Chi Minh City – Phu Yen</h3>
                    <p>Fly from Ho Chi Minh City to Phu Yen. Upon arrival, check in to your 4-star beachfront resort and relax by the sea.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Mui Dien Sunrise – Bai Mon Beach</h3>
                    <p>Wake up early to catch the spectacular sunrise at Mui Dien. Then spend time swimming and relaxing at Bai Mon Beach nearby.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Vung Ro Bay – Local Seafood Tasting</h3>
                    <p>Embark on a boat tour of Vung Ro Bay, known for its clear waters and wartime stories. End the day with a seafood tasting experience at a local restaurant.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Free Morning – Return to Ho Chi Minh City</h3>
                    <p>Enjoy a leisurely morning at the resort before heading to the airport for your flight back to Ho Chi Minh City.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">7,790,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">9,273,810 VND</p>
                    <a href="../booktour.php?cityid=14&tourid=18" class="booking-button">Booking now!</a>
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