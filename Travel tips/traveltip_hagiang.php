<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tay Bac Travel</title>
    <link rel="stylesheet" href="../css/traveltips.css">
    <style>
        /* Ensure content is not overlapped by fixed header */
        .main-content {
            margin-top: 100px; /* Adjust based on header height */
            padding: 20px;
        }
    </style>
</head>
<body>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <input type="checkbox" name="" id="toggler">
    <label for="toggler" class="fas fa-bars"></label>

    <a href="#" class="logo">
        <img src="/images/logo.png" alt="VietTransit Logo">
        <span>VietTransit</span>
    </a>

    <nav class="navbar">
        <a href="/Login/loggedinhome.php">Home</a>
        <?php
            echo "<a href='../Login/profile.php'>Hello, " . $_SESSION['usersuid'] . "!</a>";
            echo '<a href="../home.php">Logout</a>';
        ?>
    </nav>

    <div class="icons">
        <span data-tooltip="Favourites" data-flow="top"> 
            <a href="profile.php" class="fas fa-heart"></a>
        </span>
        <span data-tooltip="Cart" data-flow="top"> 
            <a href="/Payment Interface/receiptlist.php" class="fas fa-shopping-cart"></a>
        </span>
        <span data-tooltip="Profile" data-flow="top">
            <a href="profile.php" class="fas fa-user"></a>
        </span>
    </div>
</header>

<body>
    <main class="container">
        <section class="section">
            <h2>About Ha Giang</h2>
            <p>Ha Giang is a mountainous province in the northernmost part of Vietnam, known for its rugged landscapes, winding mountain passes, and vibrant ethnic minority cultures. It’s a dream destination for adventurous travelers seeking off-the-beaten-path experiences.</p>
        </section>

        <section class="section">
            <h2>Transportation</h2>
            <p>Ha Giang is about 300 km from Hanoi. You can travel by sleeper bus (8–10 hours) from Hanoi to Ha Giang City. From there, motorbike rental is the most popular and flexible way to explore the region.</p>
            <p>For those less comfortable riding, local tours with experienced drivers are available. Road conditions can be challenging, so drive carefully.</p>
        </section>

        <section class="section">
            <h2>Best Time to Visit</h2>
            <p>The best time to visit Ha Giang is from September to November (buckwheat flower season) and March to May (pleasant weather and blooming season). Avoid the rainy season (June–August) due to landslides and slippery roads.</p>
            <p>October and November are especially scenic, with blooming buckwheat flowers covering the mountain slopes.</p>
        </section>

        <section class="section">
            <h2>Places to Visit</h2>
            <p><strong>Day 1:</strong> Arrive in Ha Giang City and begin the loop to Quan Ba Heaven Gate, Twin Mountains, and stay overnight in Yen Minh or Dong Van.</p>
            <p><strong>Day 2:</strong> Visit Dong Van Old Quarter, Lung Cu Flagpole (the northernmost point of Vietnam), and explore H’Mong villages. Stay in Dong Van town.</p>
            <p><strong>Day 3:</strong> Travel through Ma Pi Leng Pass – one of the most beautiful mountain passes in Vietnam – and explore Meo Vac. Return to Ha Giang City or extend the trip to Du Gia for waterfalls and homestay experiences.</p>
        </section>

        <section class="section">
            <h2>Accommodation & Cuisine</h2>
            <p>Ha Giang offers cozy homestays in ethnic villages and small hotels in towns. Dong Van and Meo Vac are popular for overnight stays.</p>
            <p>Local dishes to try:</p>
            <ul>
                <li>Thang co (horse meat stew – traditional H’Mong dish)</li>
                <li>Au tau porridge (cháo ấu tẩu)</li>
                <li>Men men (steamed cornmeal)</li>
                <li>Smoked buffalo meat (thịt trâu gác bếp)</li>
            </ul>
        </section>

        <section class="section">
            <h2>Shopping & Souvenirs</h2>
            <p>Buy handmade brocade textiles, ethnic clothing, buckwheat flower honey, and herbal tea. Weekly markets like Dong Van and Meo Vac Sunday markets are perfect for local products and cultural interaction.</p>
        </section>

        <section class="section">
            <h2>Tips & Cautions</h2>
            <p>Bring warm clothing, especially in winter. Roads are steep and narrow—drive cautiously. Be respectful of local customs and ask for permission before taking photos. Cell signal can be weak in remote areas—download offline maps in advance.</p>
        </section>
    </main>

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
                <a href="viewjourney_taybac.php">Tay Bac</a>
                <a href="viewjourney_hcm.php">Ho Chi Minh</a>
                <a href="viewjourney_phuquoc.php">Phu Quoc</a>
                <a href="viewjourney_hue.php">Hue</a>
            </div>
            <div class="box">
                <h3>contact info</h3>
                <a href="https://github.com/socolate12345/Travel-Booking-Website">GitHub</a>
                <img src="./images/payment.png" alt="">
            </div>
        </div>
        <div class="credit">©2025 VietTransit</div>
    </section>
</body>
</html>