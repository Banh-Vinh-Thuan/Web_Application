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
            <h2>About Nha Trang</h2>
            <p>Nha Trang is a coastal city in central Vietnam, known for its beautiful beaches, crystal-clear waters, and vibrant marine life. With a mix of natural beauty and modern tourism, it's a popular destination for both local and international travelers.</p>
        </section>

        <section class="section">
            <h2>Transportation</h2>
            <p>You can reach Nha Trang by plane via Cam Ranh International Airport, just 35 km away from the city center. Trains and buses from major cities like Ho Chi Minh City or Hanoi also connect conveniently to Nha Trang.</p>
            <p>Inside the city, taxis, motorbike rentals, and cyclos are available. Cable cars and boats provide access to nearby islands like Hon Tre and Hon Mun.</p>
        </section>

        <section class="section">
            <h2>Best Time to Visit</h2>
            <p>The best time to visit Nha Trang is from January to August, with dry and sunny weather ideal for beach activities and island hopping. September to December sees occasional rains, but tourism remains active.</p>
            <p>April and May offer great visibility for diving and snorkeling.</p>
        </section>

        <section class="section">
            <h2>Places to Visit</h2>
            <p><strong>Day 1:</strong> Relax at Nha Trang Beach, visit the Po Nagar Cham Towers, and explore Long Son Pagoda. In the evening, enjoy a seafood dinner along Tran Phu Street.</p>
            <p><strong>Day 2:</strong> Take a boat tour to nearby islands like Hon Mun and Hon Tam. Enjoy snorkeling, coral watching, and swimming in clear waters. Visit the Nha Trang Oceanography Institute.</p>
            <p><strong>Day 3:</strong> Discover VinWonders Nha Trang on Hon Tre Island with cable car access. Enjoy amusement rides, water park, and aquarium. End the day with a mud bath or hot mineral spring spa at Thap Ba or I-Resort.</p>
        </section>

        <section class="section">
            <h2>Accommodation & Cuisine</h2>
            <p>Nha Trang offers a range of accommodations from beachfront resorts to budget guesthouses. Popular areas include Tran Phu Street and Nguyen Thien Thuat Street.</p>
            <p>Don't miss these local dishes:</p>
            <ul>
                <li>Nha Trang seafood (grilled squid, clams, shrimp)</li>
                <li>Bun cha ca (fish cake noodle soup)</li>
                <li>Nem nướng Ninh Hòa (grilled pork rolls)</li>
                <li>Banh can (mini rice pancakes)</li>
            </ul>
        </section>

        <section class="section">
            <h2>Shopping & Souvenirs</h2>
            <p>Visit Dam Market for dried seafood, bird's nest products, and local crafts. Other souvenirs include seaweed, handmade seashell jewelry, and Khanh Hoa aloe vera cosmetics.</p>
        </section>

        <section class="section">
            <h2>Tips & Cautions</h2>
            <p>Bring sunscreen, swimwear, and flip-flops for beach activities. Watch out for strong sun during midday. Bargain when shopping at local markets. Keep an eye on personal belongings at crowded beach areas.</p>
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