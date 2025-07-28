<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tay Bac Travel</title>
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
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>