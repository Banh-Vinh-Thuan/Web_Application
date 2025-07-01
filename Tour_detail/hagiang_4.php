<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ha Giang Nature & Culture</title>
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
    <h1>Ha Giang Nature & Culture: Dong Van Karst Plateau - Lung Cam Cultural Village - Ma Pi Leng Panorama - Local Craft Workshops</h1>
    <div class="gallery" id="lightgallery">
        <a href="hagiang4/1.jpg" class="big"><img src="hagiang4/1.jpg" alt="Dong Van District"></a>
        <a href="hagiang4/2.jpg" class="small1"><img src="hagiang4/2.jpg" alt="Tam Giac Mach Village"></a>
        <a href="hagiang4/3.jpg" class="small2"><img src="hagiang4/3.jpg" alt="Lung Cam Village"></a>
 <a href="hagiang3/4.jpg" class="small3"><img src="hagiang3/4.jpg" alt="Ma Li Peng Hill"></a>
        <a href="hagiang4/4.jpg" class="small3"><img src="hagiang4/4.jpg" alt="Ma Li Peng Hill"></a>
        <a href="hagiang4/5.jpg" class="small4"><img src="hagiang4/5.jpg" alt="Sewing Traditions"></a>
    </div>
    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore the spectacular Dong Van Karst Plateau, a UNESCO Global Geopark with dramatic limestone formations.</li>
                    <li>Visit Lung Cam Cultural Village to experience traditional H’Mong architecture and authentic local lifestyles.</li>
                    <li>Admire the panoramic views from Ma Pi Leng Pass, one of the most iconic mountain passes in Vietnam.</li>
                    <li>Engage in hands-on experiences at local craft workshops, learning about weaving, embroidery, and ethnic artistry.</li>
                    <li>Enjoy a comfortable and enriching trip with departure from Ho Chi Minh City, staying in a mix of hotels and homestays.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h4>Day 1: Ho Chi Minh City – Ha Giang</h4>
                    <p>Depart from Ho Chi Minh City and travel to Ha Giang. Upon arrival, check in to your accommodation and rest up for the journey ahead.</p>
                </div>
                <div class="box2">
                    <h4>Day 2: Dong Van Karst Plateau – Lung Cam Village</h4>
                    <p>Explore the Dong Van Karst Plateau’s stunning terrain. Visit Lung Cam Cultural Village, home to centuries-old H’Mong houses and colorful cultural heritage.</p>
                </div>
                <div class="box2">
                    <h4>Day 4: Ma Pi Leng Panorama – Local Craft Workshops</h4>
                    <p>Take in breathtaking views from Ma Pi Leng Panorama. In the afternoon, participate in local craft workshops led by ethnic artisans.</p>
                </div>
                <div class="box2">
                    <h4>Day 4: Free Time – Return to Ho Chi Minh City</h4>
                    <p>Enjoy some leisure time in Ha Giang for shopping or sightseeing before heading back to Ho Chi Minh City by bus, concluding your cultural journey.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box4">
                <div class="button">
                    <h4 style="display: inline;">Price From</h4>
                    <p style="color: red; font-weight: bold; display: inline;">7,150,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">8,400,000 VND</p>
                    <a href="/tour/booking?cityid=18&tourid=46" class="booking-button">Booking now!</a>
                </div>
            </div>

            <div class="box">
                <h4>Contact Support</h4>
                <p>📞 Hotline: 1919 2025<br>✉️ Email: viettransit.support@mail.com</p>
            </div>

            <div class="box">
                <h4>Why Book Online?</h4>
                <ul>
                    <li>Safe & Secure</li>
                    <li>Convenient & Time-saving</li>
                    <li>No hidden fees</li>
                    <li>Exclusive deals</li>
                </ul>
            </div>

            <div class="box">
                <h4>Trusted Tour</h4>
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