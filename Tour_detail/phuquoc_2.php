<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phu Quoc Island Adventure</title>
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
    <h1>Phu Quoc Island Adventure: Phu Quoc National Park - Fish Sauce Factory - Coconut Prison - Sunset Beach</h1>
    <div class="gallery" id="lightgallery">
        <a href="phuquoc2/1.jpg" class="big"><img src="phuquoc2/1.jpg" alt="Phu Quoc Island"></a>
        <a href="phuquoc2/2.jpg" class="small1"><img src="phuquoc2/2.jpg" alt="Phu Quoc Beach"></a>
        <a href="phuquoc2/3.jpg" class="small2"><img src="phuquoc2/3.jpg" alt="Vin Wonders Phu Quoc"></a>
        <a href="phuquoc2/4.jpg" class="small3"><img src="phuquoc2/4.jpg" alt="Vin Safari Phu Quoc"></a>
        <a href="phuquoc2/5.jpg" class="small4"><img src="phuquoc2/5.jpg" alt="Hon Thom Cable Car"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore the lush landscapes of Phu Quoc National Park, home to diverse flora and fauna.</li>
                    <li>Visit a traditional Fish Sauce Factory and learn about this iconic local product’s production.</li>
                    <li>Discover the history of Coconut Prison, a significant site from the Vietnam War era.</li>
                    <li>Relax and enjoy the stunning views at Sunset Beach, a perfect spot for evening tranquility.</li>
                    <li>Enjoy a comfortable trip with direct flight departure from Ho Chi Minh City and quality hotel accommodations.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Ho Chi Minh City – Phu Quoc</h3>
                    <p>Fly from Ho Chi Minh City to Phu Quoc. Upon arrival, check in to your hotel and unwind. Spend your evening at leisure or explore the local area.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Phu Quoc National Park – Fish Sauce Factory</h3>
                    <p>Spend the morning exploring the rich biodiversity of Phu Quoc National Park with guided walks. In the afternoon, visit a traditional Fish Sauce Factory to see how this famous ingredient is made.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Coconut Prison – Sunset Beach</h3>
                    <p>Tour the historic Coconut Prison and learn about its role during the Vietnam War. Later, relax at Sunset Beach and enjoy breathtaking views as the sun sets over the sea.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Leisure Morning – Return to Ho Chi Minh City</h3>
                    <p>Enjoy a free morning to relax or shop for souvenirs before checking out. Transfer to the airport for your return flight to Ho Chi Minh City, concluding your Phu Quoc Island adventure.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">5,790,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">6,744,186 VND</p>
                    <a href="/tour/booking?cityid=16&tourid=26" class="booking-button">Booking now!</a>
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