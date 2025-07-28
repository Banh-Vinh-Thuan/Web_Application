<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Da Lat Travel</title>
    <link rel="stylesheet" href="../css/traveltips.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php include __DIR__ . '/../header.php'; ?>

<body>
    <main class="container">
        <section class="section">
            <h2>About Da Lat</h2>
            <p>Da Lat, located in the Central Highlands of Vietnam, is known as the "City of Eternal Spring" due to its cool climate, pine forests, and flower gardens. A former French hill station, Da Lat blends European architecture with romantic scenery.</p>
        </section>

        <section class="section">
            <h2>Transportation</h2>
            <p>You can reach Da Lat by plane via Lien Khuong Airport, located about 30 km from the city center. Buses from Ho Chi Minh City, Nha Trang, or nearby cities are also available. The roads to Da Lat offer scenic views of mountain passes and valleys.</p>
            <p>Within the city, taxis, motorbike rentals, and electric carts are common ways to get around. Many attractions are within a short drive or walk from the city center.</p>
        </section>

        <section class="section">
            <h2>Best Time to Visit</h2>
            <p>The ideal time to visit Da Lat is from November to March, during the dry season when the weather is cool and flowers bloom. The Flower Festival, held every two years in December, is a major event worth attending.</p>
            <p>Spring (late January to March) features cherry blossoms and beautiful garden displays.</p>
        </section>

        <section class="section">
            <h2>Places to Visit</h2>
            <p><strong>Day 1:</strong> Visit Xuan Huong Lake, Da Lat Flower Park, and Lam Vien Square. Explore the French Quarter with colonial villas and have coffee at a hillside cafe.</p>
            <p><strong>Day 2:</strong> Discover Datanla Waterfall (via alpine coaster), Crazy House, and Truc Lam Zen Monastery by cable car. In the evening, stroll the Da Lat Night Market.</p>
            <p><strong>Day 3:</strong> Visit Langbiang Mountain for panoramic views and cultural encounters with local ethnic groups. Optional stops include Clay Tunnel, Valley of Love, or Domaine de Marie Church.</p>
        </section>

        <section class="section">
            <h2>Accommodation & Cuisine</h2>
            <p>Choose from charming homestays, French-inspired hotels, or romantic resorts with views of pine forests and valleys.</p>
            <p>Must-try dishes in Da Lat:</p>
            <ul>
                <li>Bánh tráng nướng (Vietnamese pizza)</li>
                <li>Lẩu gà lá é (chicken hotpot with basil)</li>
                <li>Soy milk at night markets</li>
                <li>Fresh strawberries and artichoke tea</li>
            </ul>
        </section>

        <section class="section">
            <h2>Shopping & Souvenirs</h2>
            <p>Popular items include dried fruits, Da Lat wine, jam, fresh flowers, and local handicrafts. The Night Market is ideal for souvenirs and street snacks.</p>
        </section>

        <section class="section">
            <h2>Tips & Cautions</h2>
            <p>Pack warm clothes, especially for evenings and early mornings. Bring cash for local markets. Watch out for foggy roads if you're riding a motorbike. Respect quiet zones at religious and cultural sites.</p>
        </section>
    </main>
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>