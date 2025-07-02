<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ho Chi Minh Travel</title>
    <link rel="stylesheet" href="../css/traveltips.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
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
        <span data-tooltip="Profile" data-flow="top">
            <a href="profile.php" class="fas fa-user"></a>
        </span>
    </div>
</header>

<body>
    <main class="container">
        <section class="section">
            <h2>About Ho Chi Minh City</h2>
            <p>Located in Southern Vietnam, Ho Chi Minh City is the country's largest and most dynamic metropolis. It's a key transportation hub, connecting provinces and serving as an international gateway.</p>
        </section>

        <section class="section">
            <h2>Transportation</h2>
            <p>The main airport is Tan Son Nhat International Airport, only 20 minutes by taxi from downtown. You can also travel by train from the North via <a href="http://vetau.com.vn" target="_blank">vetau.com.vn</a> or by inter-provincial buses like Mai Linh Express.</p>
            <p>Within the city, options include taxis, buses, and motorbike taxis. Bus fares are budget-friendly.</p>
        </section>

        <section class="section">
            <h2>Best Time to Visit</h2>
            <p>HCMC has two seasons: dry (Dec-May) and rainy (Jun-Nov). You can visit year-round, but avoid Tet (Lunar New Year) as the city quiets down with people returning to their hometowns.</p>
            <p>During Christmas and other festivals, the city is lively with lights and celebrations.</p>
        </section>

        <section class="section">
            <h2>Places to Visit</h2>
            <p><strong>Day 1:</strong> Visit landmarks in District 1 like Ben Thanh Market, Notre-Dame Cathedral, Central Post Office, and the Opera House. In the afternoon, check out Tao Dan Park, Turtle Lake, and Tan Dinh Market. Explore Chinatown in District 5 by evening.</p>
            <p><strong>Day 2:</strong> Take a bus to Cu Chi Tunnels, visit Ben Dinh or Ben Duoc, and 18 Betel Nut Villages. Explore Phu My Hung, Crescent Mall, and enjoy a night cruise on the Saigon River.</p>
            <p><strong>Day 3:</strong> Depending on interest, visit cultural parks (Suoi Tien, Dam Sen), Binh Quoi Village, or Can Gio Mangrove Forest if time permits.</p>
        </section>

        <section class="section">
            <h2>Accommodation & Cuisine</h2>
            <p>Stay in District 1 for walkability and nightlife. Recommended hotels include Aries Ben Thanh, Phung Hoang Gold Palace, and Ava Saigon 2.</p>
            <p>Enjoy Vietnamese and international cuisines. Don't miss local specialties like:</p>
            <ul>
                <li>Banh Mi Hoa Ma (Cao Thang St)</li>
                <li>Banh Mi Huynh Hoa (Le Thi Rieng St)</li>
                <li>Banh Mi Xiu Mai (Nguyen Thi Minh Khai St)</li>
            </ul>
        </section>

        <section class="section">
            <h2>Shopping & Souvenirs</h2>
            <p>Buy tropical fruits like mango, star apple, and green pomelo. Other great souvenirs: coffee, Soc Trang durian cakes, cashew nuts.</p>
        </section>

        <section class="section">
            <h2>Tips & Cautions</h2>
            <p>Most restaurants don't overcharge. Bargain when shopping in local markets, especially Ben Thanh.</p>
        </section>
    </main>
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>