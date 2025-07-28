<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hue Travel</title>
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
            <h2>About Hue</h2>
            <p>Hue, the former imperial capital of Vietnam, is located in central Vietnam on the banks of the Perfume River. It is known for its rich cultural heritage, ancient architecture, and royal history from the Nguyen Dynasty.</p>
        </section>

        <section class="section">
            <h2>Transportation</h2>
            <p>You can reach Hue by plane via Phu Bai International Airport, located about 15 km from the city center. There are also train connections from Hanoi and Ho Chi Minh City, offering scenic views along the way.</p>
            <p>Within Hue, you can get around by bicycle, cyclo, motorbike, or taxi. A boat ride along the Perfume River is also a memorable way to explore the city’s sights.</p>
        </section>

        <section class="section">
            <h2>Best Time to Visit</h2>
            <p>The ideal time to visit Hue is from January to April, when the weather is mild and dry. Summer months (May–August) are hot and humid, while the rainy season occurs from September to December.</p>
            <p>The biennial Hue Festival, held in even-numbered years, is a great time to experience traditional music, art, and royal customs.</p>
        </section>

        <section class="section">
            <h2>Places to Visit</h2>
            <p><strong>Day 1:</strong> Visit the Imperial City (Citadel), Flag Tower, and the Nine Dynastic Urns. Stop by Thien Mu Pagoda and enjoy a dragon boat ride on the Perfume River in the evening.</p>
            <p><strong>Day 2:</strong> Explore the royal tombs of Minh Mang, Khai Dinh, and Tu Duc. In the afternoon, relax at Thanh Toan Bridge or visit local handicraft villages like Sinh Village (folk painting).</p>
            <p><strong>Day 3:</strong> Depending on your interests, take a day trip to Bach Ma National Park or the Tam Giang Lagoon. Enjoy traditional court music (Nha Nhac) in the evening.</p>
        </section>

        <section class="section">
            <h2>Accommodation & Cuisine</h2>
            <p>Hue offers various accommodations from riverside resorts to charming homestays near the Citadel. Streets like Le Loi and Nguyen Cong Tru are popular for lodging.</p>
            <p>Must-try dishes include:</p>
            <ul>
                <li>Bun bo Hue (spicy beef noodle soup)</li>
                <li>Com hen (clam rice)</li>
                <li>Banh beo, banh loc, banh nam (traditional rice cakes)</li>
                <li>Nem lui (grilled pork skewers)</li>
            </ul>
        </section>

        <section class="section">
            <h2>Shopping & Souvenirs</h2>
            <p>Hue is famous for conical hats (non la), handmade incense, royal-inspired artwork, and sesame candy. Dong Ba Market is a popular place to buy souvenirs and local specialties.</p>
        </section>

        <section class="section">
            <h2>Tips & Cautions</h2>
            <p>Wear modest clothing when visiting temples and tombs. Be prepared for rain if visiting during the wet season. Try guided tours to better understand the rich history of the former imperial city.</p>
        </section>
    </main>
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>