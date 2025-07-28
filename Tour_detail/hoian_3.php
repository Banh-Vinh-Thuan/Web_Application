<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoi An Heritage & Beach</title>
    <meta name="description" content="Explore Hoi An's ancient charm, My Son Sanctuary, relax on An Bang Beach, and join a traditional cooking class. Includes hotel & transport.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Hoi An Heritage & Beach: Ancient Town - My Son Sanctuary - An Bang Beach - Traditional Cooking Class</h1>

    <div class="gallery" id="lightgallery">
        <a href="hoian3/1.jpg" class="big">
            <img src="hoian3/1.jpg" alt="Hoi An Old Quarter" loading="lazy" width="800" height="600">
        </a>
        <a href="hoian3/2.jpg" class="small1">
            <img src="hoian3/2.jpg" alt="My Son Ruins" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian3/3.jpg" class="small2">
            <img src="hoian3/3.jpg" alt="An Bang Beach" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian3/4.jpg" class="small3">
            <img src="hoian3/4.jpg" alt="Traditional Cooking Class Activity" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian3/5.jpg" class="small4">
            <img src="hoian3/5.jpg" alt="Cooking Class Ingredients" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <section class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover the historic charm of Hoi An Ancient Town – a UNESCO World Heritage Site.</li>
                    <li>Visit the sacred My Son Sanctuary, an ancient Champa temple complex in Quang Nam.</li>
                    <li>Relax at An Bang Beach, one of Vietnam’s most serene and beautiful beaches.</li>
                    <li>Join a traditional Vietnamese cooking class with local chefs using fresh ingredients.</li>
                    <li>Enjoy round-trip transportation from Hanoi and quality hotel accommodations.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>

                <article class="box2">
                    <h3>Day 1: Hanoi – Hoi An</h3>
                    <p>Depart by bus from Hanoi and arrive in Hoi An in the evening. Check in and enjoy a welcome dinner with local specialties.</p>
                </article>

                <article class="box2">
                    <h3>Day 2: Ancient Town – Cooking Class</h3>
                    <p>Explore Hoi An's Ancient Town in the morning. In the afternoon, join a hands-on cooking class with guidance from local chefs.</p>
                </article>

                <article class="box2">
                    <h3>Day 3: My Son Sanctuary – An Bang Beach</h3>
                    <p>Visit the ancient My Son ruins in the morning. Spend your afternoon relaxing at An Bang Beach with time for swimming and sunbathing.</p>
                </article>

                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Hanoi</h3>
                    <p>Enjoy free time to shop or stroll. After check-out, return to Hanoi by bus, ending your heritage and beach adventure.</p>
                </article>
            </div>
        </section>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,290,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,140,000 VND</p>
                    <a href="/tour/booking?cityid=17&tourid=31" class="booking-button">Booking now!</a>
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
