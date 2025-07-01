<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hanoi - Overnight Train to Hue - Imperial City - Khai Dinh Tomb - Dong Ba Market - Relaxing Cruise on Perfume
        River </title>
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <link rel="stylesheet" href="../css/tour.css">
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
        <h1>Hanoi - Overnight Train to Hue - Imperial City - Khai Dinh Tomb - Dong Ba Market - Relaxing Cruise on
            Perfume River </h1>
    <div class="gallery" id="lightgallery">
        <a href="Hue4/1.jpg" class="big"><img src="Hue4/1.jpg" alt="Hai Van Hills"></a>
        <a href="Hue4/2.jpg" class="small1"><img src="Hue4/2.jpg" alt="Dai Noi Hue"></a>
        <a href="Hue4/3.jpg" class="small2"><img src="Hue4/3.jpg" alt="KhaiDinh Masolium"></a>
        <a href="Hue4/4.jpg" class="small3"><img src="Hue4/4.jpg" alt="Dong Ba Market"></a>
        <a href="Hue4/5.jpg" class="small4"><img src="Hue4/5.jpg" alt="Boat in Huong River"></a>
    </div>

        <div class="content-columns">
            <div class="left-column">
                <div class="box">
                    <h2>Why This Tour Is Attractive</h2>
                    <ul>
                        <li>Experience a scenic overnight train journey from Hanoi to Hue.</li>
                        <li>Explore the majestic Imperial City and the ornate Khai Dinh Tomb.</li>
                        <li>Shop like a local at the bustling Dong Ba Market.</li>
                        <li>Relax with a peaceful boat cruise along the poetic Perfume River.</li>
                        <li>Stay at a comfortable 3-star hotel in the heart of Hue.</li>
                        <li>Convenient departure from Hanoi with train travel included.</li>
                    </ul>
                </div>

                <div class="box">
                    <h2>Itinerary</h2>
                    <div class="box2">
                        <h3>Day 1: Hanoi – Overnight Train to Hue</h3>
                        <p>Depart from Hanoi in the evening aboard a comfortable overnight train bound for Hue, enjoying
                            the changing scenery of northern Vietnam.</p>
                    </div>
                    <div class="box2">
                        <h3>Day 2: Arrival in Hue – Imperial City Exploration</h3>
                        <p>Arrive in Hue and check in to your 3-star hotel. Begin your exploration of Hue’s rich history
                            with a visit to the Imperial City, a UNESCO World Heritage Site.</p>
                    </div>
                    <div class="box2">
                        <h3>Day 3: Khai Dinh Tomb – Dong Ba Market – Perfume River Cruise</h3>
                        <p>Visit the impressive Khai Dinh Tomb, then shop for local specialties at Dong Ba Market. In
                            the afternoon, unwind on a tranquil boat cruise along the Perfume River.</p>
                    </div>
                    <div class="box2">
                        <h3>Day 4: Free Time – Train Back or Optional Extension</h3>
                        <p>Enjoy some free time to explore Hue at your leisure before checking out. Return options
                            include a train ride back to Hanoi or an optional travel extension.</p>
                    </div>
                </div>
            </div>

            <div class="right-column">
                <div class="box3">
                    <div class="button">
                        <h3 style="display: inline;">Price From</h3>
                        <p style="color: red; font-weight: bold; display: inline;">5,290,000 VND</p>
                        <p style="text-decoration: line-through; color: gray;">6,080,460 VND</p>
                        <a href="/tour/booking?cityid=13&tourid=16" class="booking-button">Booking now!</a>
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