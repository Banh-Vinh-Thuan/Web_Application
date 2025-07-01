<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>North-Central Tour: Hanoi - Sapa - Fansipan - Ha Long - Ninh Binh - Da Nang - Son Tra - Ba Na Hills - Golden
        Bridge - Hoi An - La Vang - Hue - Phong Nha</title>
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
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
        <h1>North - Central Tour: Hanoi - Sapa - Fansipan - Ha Long - Ninh Binh - Da Nang - Son Tra - Ba Na Hills -
            Golden Bridge - Hoi An - La Vang - Hue - Phong Nha</h1>
    <div class="gallery" id="lightgallery">
        <a href="hue2/1.jpg" class="big"><img src="hue2/1.jpg" alt="Ba Na Hils "></a>
        <a href="hue2/2.jpg" class="small1"><img src="hue2/2.jpg" alt="Ha Long Bay"></a>
        <a href="hue2/3.jpg" class="small2"><img src="hue2/3.jpg" alt="Ha Long Bay"></a>
        <a href="hue2/4.jpg" class="small3"><img src="hue2/4.jpg" alt="Ba Na Hils"></a>
        <a href="hue2/5.jpg" class="small4"><img src="hue2/5.jpg" alt="Golden Bridge"></a>
    </div>

        <div class="content-columns">
            <div class="left-column">
                <div class="box">
                    <h2>Why This Tour Is Attractive</h2>
                    <ul>
                        <li>Comprehensive journey through Vietnam’s cultural and natural highlights.</li>
                        <li>Conquer Fansipan, the highest peak in Indochina.</li>
                        <li>Marvel at Ha Long Bay, the Golden Bridge, and Hoi An’s ancient charm.</li>
                    </ul>
                </div>

                <div class="box">
                    <h2>Itinerary</h2>
                    <div class="box2">
                        <h3>Day 1-3: Hanoi - Sapa - Fansipan</h3>
                        <p>Explore the capital, then head to Sapa to conquer Fansipan Peak.</p>
                    </div>
                    <div class="box2">
                        <h3>Day 4-6: Ha Long - Ninh Binh</h3>
                        <p>Discover Ha Long Bay’s limestone karsts and the serene beauty of Ninh Binh.</p>
                    </div>
                    <div class="box2">
                        <h3>Day 7-8: Da Nang - Ba Na Hills - Hoi An</h3>
                        <p>Experience Son Tra Peninsula, the Golden Bridge, and the charm of Hoi An.</p>
                    </div>
                    <div class="box2">
                        <h3>Day 9-10: Hue - Phong Nha</h3>
                        <p>Visit Hue’s Imperial City and explore the caves at Phong Nha.</p>
                    </div>
                </div>
            </div>

            <div class="right-column">
                <div class="box3">
                    <div class="button">
                        <h3 style="display: inline;">Price From</h3>
                        <p style="color: red; font-weight: bold; display: inline;">23,590,000 VND</p>
                        <p style="text-decoration: line-through; color: gray;">26,211,111 VND</p>
                        <a href="/tour/booking?cityid=13&tourid=14" class="booking-button">Booking now!</a>
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
    <?php include __DIR__ . '/../footer.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/lightgallery.min.js"></script>
        <script>
            lightGallery(document.getElementById('lightgallery'), {
                thumbnail: true,
                animateThumb: true,
                showThumbByDefault: true,
                mode: 'lg-slide',
                download: false,
                share: false
            });
        </script>
    </body>

</html>