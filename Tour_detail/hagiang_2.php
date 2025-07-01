<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ha Giang Explore</title>
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
    <h1>Ha Giang Explorer: Quan Ba Heaven Gate - Dong Van Karst Plateau - Meo Vac - Local Homestay Experience</h1>
    <div class="gallery" id="lightgallery">
        <a href="hagiang2/1.jpg" class="big"><img src="hagiang2/1.jpg" alt="Ma Li Peng Hill"></a>
        <a href="hagiang2/2.jpg" class="small1"><img src="hagiang2/2.jpg" alt="Nho Que River"></a>
        <a href="hagiang2/3.jpg" class="small2"><img src="hagiang2/3.jpg" alt="Lung Cu Flag"></a>
        <a href="hagiang2/4.jpg" class="small3"><img src="hagiang2/4.jpg" alt="Dong Van Old Tunnel"></a>
        <a href="hagiang2/5.jpg" class="small4"><img src="hagiang2/5.jpg" alt="Tam Giac Mach Flower Garden"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Marvel at the stunning views from Quan Ba Heaven Gate, known as the "Gateway to Heaven" in Ha Giang.</li>
                    <li>Explore the unique and breathtaking Dong Van Karst Plateau, a UNESCO Global Geopark.</li>
                    <li>Visit Meo Vac, a picturesque town surrounded by dramatic limestone mountains and deep valleys.</li>
                    <li>Experience authentic local culture with an overnight stay at a traditional homestay, interacting with ethnic minorities.</li>
                    <li>Enjoy a comfortable and well-organized journey departing from Can Tho, with a mix of hotel and homestay accommodations.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Can Tho – Ha Giang</h3>
                    <p>Depart early from Can Tho by bus. Travel through scenic landscapes en route to Ha Giang. Arrive and check in to your hotel or homestay, then rest for the upcoming adventure.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Quan Ba Heaven Gate – Dong Van Karst Plateau</h3>
                    <p>Visit Quan Ba Heaven Gate and enjoy panoramic mountain views. Continue exploring the Dong Van Karst Plateau, discovering its extraordinary geology and local markets.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Meo Vac – Local Homestay Experience</h3>
                    <p>Travel to Meo Vac, admire the breathtaking scenery along the way. Spend the night at a local homestay, immersing yourself in the daily life and traditions of ethnic minorities.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Leisure Morning – Return to Can Tho</h3>
                    <p>Enjoy a relaxing morning in Ha Giang before departing by bus back to Can Tho, concluding your Ha Giang exploration.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">6,990,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,860,000 VND</p>
                    <a href="/tour/booking?cityid=18&tourid=34" class="booking-button">Booking now!</a>
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