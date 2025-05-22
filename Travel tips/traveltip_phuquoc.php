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
            <h2>About Phu Quoc</h2>
            <p>Phu Quoc, Vietnam’s largest island, is located in the Gulf of Thailand. Known for its pristine beaches, turquoise waters, and luxurious resorts, Phu Quoc is a tropical paradise ideal for relaxation, adventure, and seafood cuisine.</p>
        </section>

        <section class="section">
            <h2>Transportation</h2>
            <p>You can fly directly to Phu Quoc International Airport from Hanoi, Ho Chi Minh City, and other major cities. Ferries and high-speed boats are also available from Rach Gia or Ha Tien ports in the Mekong Delta.</p>
            <p>On the island, motorbikes, taxis, and car rentals are commonly used. Many resorts also offer shuttle services.</p>
        </section>

        <section class="section">
            <h2>Best Time to Visit</h2>
            <p>The best time to visit Phu Quoc is during the dry season from November to April, when the sea is calm and the weather is sunny and pleasant.</p>
            <p>Rainy season (May to October) can still be enjoyable, especially for travelers seeking fewer crowds and lower prices.</p>
        </section>

        <section class="section">
            <h2>Places to Visit</h2>
            <p><strong>Day 1:</strong> Relax at Long Beach (Bai Truong), visit Dinh Cau Night Market, and explore local temples. Enjoy sunset cocktails on the beach.</p>
            <p><strong>Day 2:</strong> Visit the south: swim at Bai Sao (Star Beach), tour the Phu Quoc Prison Museum, and explore the Coconut Tree Prison. Take the Hon Thom cable car for panoramic views.</p>
            <p><strong>Day 3:</strong> Explore the north: VinWonders amusement park, Vinpearl Safari, and the Grand World complex. Optionally, visit a pepper farm, fish sauce factory, or a pearl farm.</p>
        </section>

        <section class="section">
            <h2>Accommodation & Cuisine</h2>
            <p>Phu Quoc offers a wide range of accommodations from budget guesthouses to luxury beach resorts, especially along Long Beach, Ong Lang, and Bai Khem.</p>
            <p>Must-try dishes in Phu Quoc:</p>
            <ul>
                <li>Herring salad (gỏi cá trích)</li>
                <li>Grilled sea urchin (cầu gai nướng)</li>
                <li>Phu Quoc seafood hotpot</li>
                <li>Fresh crab, squid, and shrimp from night markets</li>
            </ul>
        </section>

        <section class="section">
            <h2>Shopping & Souvenirs</h2>
            <p>Buy Phu Quoc fish sauce, pepper, pearls, dried seafood, and local handicrafts. The night markets and local stores offer a wide selection of gifts and specialties.</p>
        </section>

        <section class="section">
            <h2>Tips & Cautions</h2>
            <p>Bring sunscreen and swimwear. Book island tours in advance during peak season. Be cautious when swimming in areas without lifeguards. Respect local customs when visiting temples or fishing villages.</p>
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