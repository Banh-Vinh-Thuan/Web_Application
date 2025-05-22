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
            <h2>About Phu Yen</h2>
            <p>Phu Yen, located in the South Central Coast of Vietnam, is known for its untouched natural beauty, peaceful beaches, and cinematic landscapes. This province gained popularity through the film "Yellow Flowers on the Green Grass."</p>
        </section>

        <section class="section">
            <h2>Transportation</h2>
            <p>You can reach Phu Yen by flight via Tuy Hoa Airport from Hanoi or Ho Chi Minh City. Alternatively, take trains or buses along National Highway 1A, which connects Phu Yen with major cities like Da Nang, Nha Trang, and Quy Nhon.</p>
            <p>Within the province, taxis and motorbike rentals are available for getting around and exploring coastal roads and hidden beaches.</p>
        </section>

        <section class="section">
            <h2>Best Time to Visit</h2>
            <p>The ideal time to visit Phu Yen is from January to August when the weather is dry and sunny, perfect for sightseeing and beach activities.</p>
            <p>Avoid the rainy season from September to December, as sudden showers and rough seas may affect travel plans.</p>
        </section>

        <section class="section">
            <h2>Places to Visit</h2>
            <p><strong>Day 1:</strong> Start with a sunrise at Dai Lanh Cape, then visit Mon Beach and explore the Mui Dien Lighthouse. Relax in the afternoon at Bai Xep Beach – a famous filming location.</p>
            <p><strong>Day 2:</strong> Explore Ganh Da Dia (Da Dia Reef), a unique natural rock formation. Continue to O Loan Lagoon for seafood lunch, and end the day at Mang Lang Church – one of the oldest churches in Vietnam.</p>
            <p><strong>Day 3:</strong> Head to Vung Ro Bay, take a boat ride if available, or go inland to enjoy the peaceful Tuy Hoa city and sample local cuisine before departure.</p>
        </section>

        <section class="section">
            <h2>Accommodation & Cuisine</h2>
            <p>Most accommodations are in Tuy Hoa city, ranging from budget homestays to beachfront resorts. Some coastal areas also offer eco-lodges and rustic bungalows for nature lovers.</p>
            <p>Must-try local dishes:</p>
            <ul>
                <li>O Loan Lagoon blood cockles (sò huyết đầm Ô Loan)</li>
                <li>Tuna eyeball hotpot (lẩu mắt cá ngừ đại dương)</li>
                <li>Chicken rice (cơm gà Phú Yên)</li>
                <li>Bánh hỏi lòng heo (fine rice vermicelli with pig organs)</li>
            </ul>
        </section>

        <section class="section">
            <h2>Shopping & Souvenirs</h2>
            <p>Popular souvenirs include Phu Yen fish sauce, dried tuna, rice paper, and handmade coconut products. Visit Tuy Hoa Market or seaside villages to purchase directly from locals.</p>
        </section>

        <section class="section">
            <h2>Tips & Cautions</h2>
            <p>Wear sun protection and bring plenty of water when exploring outdoor sites. Phu Yen is less commercialized, so prepare cash for local purchases and plan travel routes in advance for remote destinations.</p>
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