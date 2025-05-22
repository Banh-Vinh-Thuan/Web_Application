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
            <h2>About Northwest Vietnam</h2>
            <p>The Northwest region of Vietnam is known for its majestic mountains, ethnic diversity, and scenic terraced rice fields. Provinces like Lao Cai, Yen Bai, Son La, and Dien Bien offer stunning landscapes and cultural richness.</p>
        </section>

        <section class="section">
            <h2>Transportation</h2>
            <p>You can travel from Hanoi to the Northwest by car, motorbike, or bus. For destinations like Sapa, overnight trains from Hanoi to Lao Cai are popular. Sleeper buses and limousine vans are also available for comfort.</p>
            <p>Within the region, renting motorbikes is ideal for exploring remote villages and mountain passes like O Quy Ho or Khau Pha.</p>
        </section>

        <section class="section">
            <h2>Best Time to Visit</h2>
            <p>The best time to visit the Northwest is from September to November (harvest season) and March to May (blooming season). Avoid peak rainy months (July-August) due to landslides in mountainous areas.</p>
            <p>September offers golden rice terraces, while spring is filled with blooming peach and plum flowers across the hills.</p>
        </section>

        <section class="section">
            <h2>Places to Visit</h2>
            <p><strong>Day 1:</strong> Explore Sapa – visit Cat Cat Village, Fansipan Peak (via cable car), and the stone church in town. Enjoy the cool climate and local H'Mong culture.</p>
            <p><strong>Day 2:</strong> Travel to Mu Cang Chai (Yen Bai) via Khau Pha Pass – admire rice terraces at La Pan Tan, Che Cu Nha, and enjoy homestay experiences.</p>
            <p><strong>Day 3:</strong> Head to Moc Chau (Son La) – visit tea hills, Dai Yem Waterfall, and Na Ka Plum Valley. Depending on time, visit Dien Bien Phu historical sites.</p>
        </section>

        <section class="section">
            <h2>Accommodation & Cuisine</h2>
            <p>Stay in local homestays for an authentic experience or book hotels in towns like Sapa or Mu Cang Chai. Many ethnic-style lodges offer mountain views and traditional meals.</p>
            <p>Local specialties to try:</p>
            <ul>
                <li>Grilled stream fish (cá suối nướng)</li>
                <li>Buffalo meat in the kitchen (thịt trâu gác bếp)</li>
                <li>Sticky rice in bamboo tubes (cơm lam)</li>
                <li>Thắng cố – a traditional H’Mong dish</li>
            </ul>
        </section>

        <section class="section">
            <h2>Shopping & Souvenirs</h2>
            <p>Great souvenirs include brocade textiles, handmade silver jewelry, dried buffalo meat, herbal tea, and local honey. Visit highland markets for authentic products and interaction with locals.</p>
        </section>

        <section class="section">
            <h2>Tips & Cautions</h2>
            <p>Check the weather before traveling to avoid landslides. Dress warmly in winter months. Be respectful of ethnic customs and ask permission before taking photos. Cash is preferred in remote areas.</p>
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