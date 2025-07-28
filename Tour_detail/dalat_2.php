<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dalat Nature & Culture: Tuyen Lam Lake - Truc Lam Zen Monastery - Clay Tunnel - Night Market</title>
    <meta name="description" content="Explore Dalat's peaceful nature and spiritual sites with this 4-day tour featuring Tuyen Lam Lake, Truc Lam Zen Monastery, Clay Tunnel, and the lively Night Market.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Dalat Nature & Culture: Tuyen Lam Lake - Truc Lam Zen Monastery - Clay Tunnel - Night Market</h1>

    <div class="gallery" id="lightgallery">
        <a href="dalat2/1.jpg" class="big">
            <img src="dalat2/1.jpg" alt="Center of Dalat city view" loading="lazy" width="800" height="600">
        </a>
        <a href="dalat2/2.jpg" class="small1">
            <img src="dalat2/2.jpg" alt="Datanla Waterfall cascade" loading="lazy" width="400" height="300">
        </a>
        <a href="dalat2/3.jpg" class="small2">
            <img src="dalat2/3.jpg" alt="Central Highland scenic nature" loading="lazy" width="400" height="300">
        </a>
        <a href="dalat2/4.jpg" class="small3">
            <img src="dalat2/4.jpg" alt="Dalat Clay Tunnel sculpture" loading="lazy" width="400" height="300">
        </a>
        <a href="dalat2/5.jpg" class="small4">
            <img src="dalat2/5.jpg" alt="Dalat Clay Tunnel cultural artworks" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover the spiritual beauty of Truc Lam Zen Monastery and peaceful nature at Tuyen Lam Lake.</li>
                    <li>Experience Dalat's cool climate and romantic landscapes – Vietnam's "City of Eternal Spring."</li>
                    <li>Explore unique art and architecture at the Dalat Clay Tunnel.</li>
                    <li>Enjoy a relaxing and enriching trip with convenient transportation from Ho Chi Minh City.</li>
                    <li>Engage in free time to explore cafes, gardens, and local culture.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Ho Chi Minh City – Dalat</h3>
                    <p>Depart from Ho Chi Minh City and arrive in Dalat. Check in to your hotel and enjoy a relaxing evening exploring the Dalat Night Market.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Tuyen Lam Lake – Truc Lam Zen Monastery</h3>
                    <p>Start your day with a peaceful walk around Tuyen Lam Lake. Take a cable car ride or drive to Truc Lam Zen Monastery and enjoy panoramic views of pine forests.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Dalat Clay Tunnel & Free Exploration</h3>
                    <p>Visit the famous Clay Tunnel and admire artistic sculptures depicting Dalat’s history. Spend the afternoon exploring cafes, gardens, or relaxing at your leisure.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Ho Chi Minh City</h3>
                    <p>Enjoy a free morning for personal activities or souvenir shopping. Check out and return to Ho Chi Minh City by afternoon.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">5,690,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">6,465,909 VND</p>
                    <a href="/tour/booking?cityid=15&tourid=22" class="booking-button">Booking now!</a>
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