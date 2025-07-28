<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoi An Cultural Experience</title>
    <meta name="description" content="Experience the cultural richness of Hoi An: visit Ancient Town, Riverside Market, traditional craft villages, and join the Lantern Festival.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Hoi An Cultural Experience: Ancient Town - Riverside Market - Craft Villages - Lantern Festival</h1>

    <div class="gallery" id="lightgallery">
        <a href="hoian2/1.jpg" class="big">
            <img src="hoian2/1.jpg" alt="Panoramic view of Hoi An Ancient Town" loading="lazy" width="800" height="600">
        </a>
        <a href="hoian2/2.jpg" class="small1">
            <img src="hoian2/2.jpg" alt="Old architecture of Hoi An Ancient Town" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian2/3.jpg" class="small2">
            <img src="hoian2/3.jpg" alt="My Son Sanctuary Ruins" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian2/4.jpg" class="small3">
            <img src="hoian2/4.jpg" alt="Hands-on activities at Tra Que Village" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian2/5.jpg" class="small4">
            <img src="hoian2/5.jpg" alt="Bay Mau Coconut Forest boat ride" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Walk through the historic Hoi An Ancient Town, a UNESCO World Heritage Site rich in cultural heritage.</li>
                    <li>Shop and eat like a local at the lively Riverside Market full of authentic Vietnamese flavors.</li>
                    <li>Explore traditional craft villages and try lantern-making, pottery, and more hands-on experiences.</li>
                    <li>Admire the Lantern Festival with colorful floating lights illuminating the entire riverside.</li>
                    <li>Convenient bus transportation from Hanoi and cozy accommodations included.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>

                <article class="box2">
                    <h3>Day 1: Hanoi – Hoi An</h3>
                    <p>Start your cultural journey with a morning bus from Hanoi to Hoi An. Check in and explore the Ancient Town by night with a delicious local dinner.</p>
                </article>

                <article class="box2">
                    <h3>Day 2: Ancient Town – Craft Villages</h3>
                    <p>Visit historical streets and landmarks, then head to nearby craft villages to learn about traditional lantern-making and pottery from local artisans.</p>
                </article>

                <article class="box2">
                    <h3>Day 3: Riverside Market – Lantern Festival</h3>
                    <p>Enjoy shopping at the Riverside Market in the morning. At night, take part in the magical Lantern Festival with floating lights and riverside beauty.</p>
                </article>

                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Hanoi</h3>
                    <p>Spend your morning at leisure—relax, shop, or enjoy a local coffee. After check-out, return to Hanoi by bus.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,390,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,100,000 VND</p>
                    <a href="/tour/booking?cityid=17&tourid=30" class="booking-button">Booking now!</a>
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
