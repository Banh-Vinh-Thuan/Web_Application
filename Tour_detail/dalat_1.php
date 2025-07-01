<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dalat Dreamy Getaway: Valley of Love - Datanla Waterfall - Langbiang Mountain - Local Flower Gardens</title>
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
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
    <h1>Dalat Dreamy Getaway: Valley of Love - Datanla Waterfall - Langbiang Mountain - Local Flower Gardens</h1>
    <div class="gallery" id="lightgallery">
        <a href="dalat1/1.jpg" class="big"><img src="dalat1/1.jpg" alt="Dalat Valley"></a>
        <a href="dalat1/2.jpg" class="small1"><img src="dalat1/2.jpg" alt="Dalat Flower Garden"></a>
        <a href="dalat1/3.jpg" class="small2"><img src="dalat1/3.jpg" alt="Langbiang mountain"></a>
        <a href="dalat1/4.jpg" class="small3"><img src="dalat1/4.jpg" alt="Dalat Night Market"></a>
        <a href="dalat1/5.jpg" class="small4"><img src="dalat1/5.jpg" alt="Dalat Night Market"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Experience the cool, romantic atmosphere of Dalat – Vietnam’s “City of Eternal Spring.”</li>
                    <li>Visit iconic landmarks such as Xuan Huong Lake, Valley of Love, and Dalat Flower Park.</li>
                    <li>Explore the unique architecture of Linh Phuoc Pagoda and Bao Dai Palace.</li>
                    <li>Discover French colonial heritage and colorful flower gardens throughout the city.</li>
                    <li>Enjoy a convenient and comfortable trip with departure from Ho Chi Minh City.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Ho Chi Minh City – Dalat</h3>
                    <p>Depart from Ho Chi Minh City and arrive in Dalat. Check in to your hotel and take a relaxing walk around Xuan Huong Lake in the evening.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: City Highlights Tour</h3>
                    <p>Visit Dalat Flower Park, the iconic Valley of Love, and explore the architectural beauty of Linh Phuoc Pagoda. Enjoy lunch at a local restaurant.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Culture & Nature Discovery</h3>
                    <p>Tour Bao Dai Palace to learn about the last emperor of Vietnam. Stroll through the French Quarter and relax at a scenic hillside café.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Free Time – Return to Ho Chi Minh City</h3>
                    <p>Spend the morning shopping at the Dalat Market or enjoying a cup of local coffee. After check-out, return to Ho Chi Minh City by bus.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">5,290,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">6,223,529 VND</p>
                    <a href="/tour/booking?cityid=15&tourid=21" class="booking-button">Booking now!</a>
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