<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phu Yen - Quy Nhon - Ky Co - Bai Xep - Ganh Da Dia Tour</title>
    <meta name="description" content="Explore the scenic beaches and geological wonders of Phu Yen and Quy Nhon including Ky Co, Bai Xep, and Ganh Da Dia. Enjoy local cuisine and culture.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Phu Yen - Quy Nhon - Ky Co - Bai Xep - Ganh Da Dia Tour</h1>

    <div class="gallery" id="lightgallery">
        <a href="Phuyen3/1.jpg" class="big"><img src="Phuyen3/1.jpg" alt="Quy Nhon Coastal Landscape" loading="lazy"></a>
        <a href="Phuyen3/2.jpg" class="small1"><img src="Phuyen3/2.jpg" alt="Vung Ro Beach View" loading="lazy"></a>
        <a href="Phuyen3/3.jpg" class="small2"><img src="Phuyen3/3.jpg" alt="Ky Co Beach Aerial" loading="lazy"></a>
        <a href="Phuyen3/4.jpg" class="small3"><img src="Phuyen3/4.jpg" alt="Ganh Da Dia Rock Formations" loading="lazy"></a>
        <a href="Phuyen3/5.jpg" class="small4"><img src="Phuyen3/5.jpg" alt="Local Seafood Dishes" loading="lazy"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore two of Central Vietnam’s top coastal cities: Phu Yen and Quy Nhon.</li>
                    <li>Swim at the idyllic Ky Co and Bai Xep beaches, perfect for relaxation and photography.</li>
                    <li>Visit the unique Ganh Da Dia – a natural wonder of basalt rock formations.</li>
                    <li>Experience authentic Central Vietnamese seafood and cuisine.</li>
                    <li>Stay at top-rated beachside hotels for comfort and convenience.</li>
                    <li>Depart easily from Hanoi with round-trip flights included.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Hanoi → Phu Yen</h3>
                    <p>Fly from Hanoi to Phu Yen. Transfer to your hotel and enjoy a welcome dinner of regional dishes.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Ganh Da Dia – Bai Xep</h3>
                    <p>Discover Ganh Da Dia’s striking hexagonal columns, then relax at Bai Xep Beach — a serene spot known from the movie "Yellow Flowers on Green Grass".</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Transfer to Quy Nhon – Ky Co</h3>
                    <p>Drive along the coast to Quy Nhon. Take a boat to Ky Co Beach, known for turquoise waters and dramatic cliffs. Optional snorkeling available.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Leisure – Return to Hanoi</h3>
                    <p>Enjoy your morning at leisure before returning to the airport for your flight back to Hanoi.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">8,490,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">9,758,621 VND</p>
                    <a href="/tour/booking?cityid=14&tourid=19" class="booking-button">Booking now!</a>
                </div>
            </div>

            <div class="box">
                <h3>Contact Support</h3>
                <p>📞 Hotline: <a href="tel:19192025">1919 2025</a><br>✉️ Email: <a href="mailto:viettransit.support@mail.com">viettransit.support@mail.com</a></p>
            </div>

            <div class="box">
                <h3>Why Book Online?</h3>
                <ul>
                    <li>Safe & Secure</li>
                    <li>Convenient & Time-saving</li>
                    <li>No hidden fees</li>
                    <li>Exclusive deals</li>
                </ul>
            </div>

            <div class="box">
                <h3>Trusted Tour</h3>
                <p>Founded in 2025<br>Leading travel brand<br>Nationally recognized</p>
            </div>
        </aside>
    </div>
</main>

<?php include __DIR__ . '/../footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/lightgallery.min.js"></script>
<script>
    lightGallery(document.getElementById('lightgallery'), {
        thumbnail: true,
        animateThumb: true,
        showThumbByDefault: true,
        mode: 'lg-slide',
        download: false,
        share: false
    });
</script>

</body>
</html>
