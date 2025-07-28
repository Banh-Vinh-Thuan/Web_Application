<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoi An Ancient Town Discovery</title>
    <meta name="description" content="Explore Hoi An Ancient Town with a 4-day tour featuring the Old Quarter, Japanese Bridge, Riverside Market, and magical Lantern Festival.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Hoi An Ancient Town Discovery: Old Quarter - Japanese Bridge - Riverside Market - Lantern Festival</h1>

    <div class="gallery" id="lightgallery">
        <a href="hoian1/1.jpg" class="big">
            <img src="hoian1/1.jpg" alt="Hoi An Old Quarter Street View" loading="lazy" width="800" height="600">
        </a>
        <a href="hoian1/2.jpg" class="small1">
            <img src="hoian1/2.jpg" alt="Hoi An Ancient Town Architecture" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian1/3.jpg" class="small2">
            <img src="hoian1/3.jpg" alt="Lantern Festival in Hoi An" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian1/4.jpg" class="small3">
            <img src="hoian1/4.jpg" alt="Japanese Covered Bridge" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian1/5.jpg" class="small4">
            <img src="hoian1/5.jpg" alt="Boat Ride in Hoi An River" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore the charming streets of Hoi An Ancient Town, a UNESCO World Heritage Site.</li>
                    <li>Visit the iconic Japanese Covered Bridge symbolizing cultural harmony.</li>
                    <li>Enjoy the bustling Riverside Market with authentic local experiences.</li>
                    <li>Experience the magical atmosphere of the Lantern Festival at night.</li>
                    <li>Relax with convenient transport from Da Nang and comfortable accommodation.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Da Nang – Hoi An</h3>
                    <p>Travel from Da Nang to Hoi An by bus. Check in and explore the ancient streets by evening, enjoying a traditional dinner.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Old Quarter – Japanese Covered Bridge</h3>
                    <p>Visit historical landmarks including ancient houses and the iconic bridge, and dive into Hoi An’s rich cultural fusion.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Riverside Market – Lantern Festival</h3>
                    <p>Shop and interact with locals in the market by day, then enjoy the vibrant lantern-lit town by night.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Da Nang</h3>
                    <p>Free time to shop or relax in the morning. After check-out, return to Da Nang and end your cultural journey.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,490,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,475,000 VND</p>
                    <a href="/tour/booking?cityid=17&tourid=29" class="booking-button">Booking now!</a>
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
