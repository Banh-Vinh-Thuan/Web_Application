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
    <h1>Ha Giang Nature & Culture: Lung Cu Flagpole - Nho Que River Boat Ride - Ma Pi Leng Pass - Minority Villages</h1>
    <div class="gallery" id="lightgallery">
        <a href="hagiang3/1.jpg" class="big"><img src="hagiang3/1.jpg" alt=" Lung Cu Flagpole"></a>
        <a href="hagiang3/2.jpg" class="small1"><img src="hagiang3/2.jpg" alt="Nho Que River"></a>
        <a href="hagiang3/3.jpg" class="small2"><img src="hagiang3/3.jpg" alt="Nho Que River"></a>
        <a href="hagiang3/4.jpg" class="small3"><img src="hagiang3/4.jpg" alt="Ma Li Peng Hill"></a>
        <a href="hagiang3/5.jpg" class="small4"><img src="hagiang3/5.jpg" alt="Ta Phin Village"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Visit Lung Cu Flagpole, the iconic northernmost point of Vietnam with panoramic mountain views.</li>
                    <li>Enjoy a scenic boat ride along the crystal-clear Nho Que River, winding through breathtaking limestone cliffs.</li>
                    <li>Experience the dramatic landscapes of Ma Pi Leng Pass, one of Vietnam’s most spectacular mountain roads.</li>
                    <li>Discover the rich traditions and vibrant culture of local ethnic minority villages through homestay and community visits.</li>
                    <li>Travel comfortably with a well-organized itinerary departing from Hanoi, staying in quality hotels and homestays.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Hanoi – Ha Giang</h3>
                    <p>Depart from Hanoi by bus and journey through scenic countryside to Ha Giang. Check in to your hotel or homestay and prepare for the adventure ahead.</p>
                </div>
                <div class="box2">
                    <h3>Day 2: Lung Cu Flagpole – Minority Villages</h3>
                    <p>Visit Lung Cu Flagpole to admire sweeping views. Explore nearby ethnic minority villages and learn about their unique customs and daily life.</p>
                </div>
                <div class="box2">
                    <h3>Day 3: Nho Que River Boat Ride – Ma Pi Leng Pass</h3>
                    <p>Take a memorable boat ride on the Nho Que River, surrounded by towering limestone cliffs. Then, travel along the breathtaking Ma Pi Leng Pass, stopping for photo opportunities and sightseeing.</p>
                </div>
                <div class="box2">
                    <h3>Day 4: Leisure Morning – Return to Hanoi</h3>
                    <p>Enjoy a relaxing morning in Ha Giang before departing back to Hanoi by bus, concluding your nature and culture exploration.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">6,190,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,110,000 VND</p>
                    <a href="/tour/booking?cityid=18&tourid=35" class="booking-button">Booking now!</a>
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