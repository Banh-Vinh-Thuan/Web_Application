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
            <h2>About Hoi An</h2>
            <p>Hoi An is a UNESCO World Heritage Site located in Quang Nam Province, Central Vietnam. Known for its ancient town, lantern-lit streets, and cultural fusion of East and West, Hoi An is a charming destination filled with history and romance.</p>
        </section>

        <section class="section">
            <h2>Transportation</h2>
            <p>The nearest airport is Da Nang International Airport, about 30 km from Hoi An. From the airport, you can take a taxi, shuttle, or private car to reach the town. Trains from Hanoi or Ho Chi Minh City arrive at Da Nang Railway Station.</p>
            <p>Within Hoi An, you can explore the Old Town on foot or rent a bicycle to get around easily. For nearby beaches or villages, taxis and motorbikes are available.</p>
        </section>

        <section class="section">
            <h2>Best Time to Visit</h2>
            <p>The best time to visit Hoi An is from February to April when the weather is dry and cool. Avoid October and November as they are prone to flooding.</p>
            <p>Visit during the Lantern Festival (held on the full moon each month) to experience Hoi An's magical atmosphere with lit lanterns and cultural performances.</p>
        </section>

        <section class="section">
            <h2>Places to Visit</h2>
            <p><strong>Day 1:</strong> Explore the Ancient Town – visit the Japanese Covered Bridge, Tan Ky Old House, and Fujian Assembly Hall. Enjoy the lantern-lit streets at night and try traditional food at riverside restaurants.</p>
            <p><strong>Day 2:</strong> Visit Tra Que Vegetable Village and take a cooking class. In the afternoon, relax at An Bang Beach or Cua Dai Beach. End the day with a sunset boat ride on the Thu Bon River.</p>
            <p><strong>Day 3:</strong> Take a half-day tour to My Son Sanctuary (a UNESCO site) or join a bicycle tour through rice fields and coconut forests in Cam Thanh Village.</p>
        </section>

        <section class="section">
            <h2>Accommodation & Cuisine</h2>
            <p>Hoi An offers a variety of stays, from charming homestays to boutique resorts near the beach or the old town.</p>
            <p>Local specialties to try:</p>
            <ul>
                <li>Cao Lau (pork noodle with herbs)</li>
                <li>White rose dumplings (bánh bao bánh vạc)</li>
                <li>Quang noodles (mì Quảng)</li>
                <li>Hoi An chicken rice (cơm gà Hội An)</li>
            </ul>
        </section>

        <section class="section">
            <h2>Shopping & Souvenirs</h2>
            <p>Hoi An is famous for custom-tailored clothes, leather goods, silk lanterns, and handmade ceramics. The Night Market on Nguyen Hoang Street is a great place to shop and experience local culture.</p>
        </section>

        <section class="section">
            <h2>Tips & Cautions</h2>
            <p>Wear comfortable shoes for walking in the old town. Bargaining is common in markets. Watch out for sudden rain showers—bring an umbrella or raincoat. Respect local customs and dress modestly when visiting temples or historical sites.</p>
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