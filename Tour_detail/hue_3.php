<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hue Cultural Discovery – Imperial Citadel – Thien Mu Pagoda – Perfume River – Truong Tien Bridge – Local
        Cuisine</title>
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
        <h1>Hue Cultural Discovery – Imperial Citadel – Thien Mu Pagoda – Perfume River – Truong Tien Bridge – Local
            Cuisine</h1>
    <div class="gallery" id="lightgallery">
        <a href="Hue3/1.jpg" class="big"><img src="Hue3/1.jpg" alt="Thuy Xuan Village"></a>
        <a href="Hue3/2.jpg" class="small1"><img src="Hue3/2.jpg" alt="Dai Noi Hue"></a>
        <a href="Hue3/3.jpg" class="small2"><img src="Hue3/3.jpg" alt="Thien Mu Pagoda"></a>
        <a href="Hue3/4.jpg" class="small3"><img src="Hue3/4.jpg" alt="Truong Tien Bridge"></a>
        <a href="Hue3/5.jpg" class="small4"><img src="Hue3/5.jpg" alt="Hue Local Foods"></a>
    </div>

        <div class="content-columns">
            <div class="left-column">
                <div class="box">
                    <h2>Why This Tour Is Attractive</h2>
                    <ul>
                        <li>Immerse yourself in the cultural richness of Hue, Vietnam’s former imperial capital.</li>
                        <li>Explore the historic Imperial Citadel and iconic Thien Mu Pagoda.</li>
                        <li>Enjoy a scenic boat ride along the poetic Perfume River.</li>
                        <li>Walk across the symbolic Truong Tien Bridge and admire its French architecture.</li>
                        <li>Savor authentic Hue cuisine with unique local flavors.</li>
                        <li>Comfortable travel with flights departing from Can Tho.</li>
                    </ul>
                </div>

                <div class="box">
                    <h2>Itinerary</h2>
                    <div class="box2">
                        <h3>Day 1: Can Tho – Hue – City Exploration</h3>
                        <p>Fly from Can Tho to Hue. Upon arrival, visit the Imperial Citadel and stroll along the
                            peaceful streets of the ancient capital.</p>
                    </div>
                    <div class="box2">
                        <h3>Day 2: Thien Mu Pagoda – Perfume River Cruise</h3>
                        <p>Visit Thien Mu Pagoda, one of the oldest religious sites in Hue. Enjoy a tranquil cruise on
                            the Perfume River and admire riverside temples and scenery.</p>
                    </div>
                    <div class="box2">
                        <h3>Day 3: Truong Tien Bridge – Local Food Tour</h3>
                        <p>Walk across the historic Truong Tien Bridge and discover Hue's hidden culinary gems on a
                            guided food tour through local markets and eateries.</p>
                    </div>
                    <div class="box2">
                        <h3>Day 4: Free Time – Departure</h3>
                        <p>Spend your last morning exploring the city at your own pace or shopping for souvenirs before
                            returning to Can Tho by flight.</p>
                    </div>
                </div>
            </div>

            <div class="right-column">
                <div class="box3">
                    <div class="button">
                        <h3 style="display: inline;">Price From</h3>
                        <p style="color: red; font-weight: bold; display: inline;">7,590,000 VND</p>
                        <p style="text-decoration: line-through; color: gray;">8,923,500 VND</p>
                        <a href="/tour/booking?cityid=13&tourid=15" class="booking-button">Booking now!</a>
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