<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dalat Chill & Relax: Tuyen Lam Lake - Clay Tunnel - Fresh Garden - Coffee Farm Experience</title>
    <meta name="description" content="Escape to Dalat with our Chill & Relax tour. Visit Tuyen Lam Lake, Clay Tunnel, Fresh Garden, and enjoy a hands-on coffee farm experience.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Dalat Chill & Relax: Tuyen Lam Lake - Clay Tunnel - Fresh Garden - Coffee Farm Experience</h1>

    <div class="gallery" id="lightgallery">
        <a href="dalat4/1.jpg" class="big">
            <img src="dalat4/1.jpg" alt="Tuyen Lam Lake" loading="lazy" width="800" height="600">
        </a>
        <a href="dalat4/2.jpg" class="small1">
            <img src="dalat4/2.jpg" alt="Dalat Sculpture Tunnel" loading="lazy" width="400" height="300">
        </a>
        <a href="dalat4/3.jpg" class="small2">
            <img src="dalat4/3.jpg" alt="Fresh Garden Dalat" loading="lazy" width="400" height="300">
        </a>
        <a href="dalat4/4.jpg" class="small3">
            <img src="dalat4/4.jpg" alt="Tomato Harvest at Local Farm" loading="lazy" width="400" height="300">
        </a>
        <a href="dalat4/5.jpg" class="small4">
            <img src="dalat4/5.jpg" alt="Colorful Floral Displays at Fresh Garden" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Relax and unwind in Dalat’s serene natural settings and charming countryside.</li>
                    <li>Enjoy peaceful strolls around Tuyen Lam Lake and explore the artistic Clay Tunnel.</li>
                    <li>Visit Fresh Garden, a beautiful flower and vegetable garden showcasing Dalat’s agriculture.</li>
                    <li>Experience an authentic coffee farm tour to learn about local coffee cultivation and tasting.</li>
                    <li>Convenient bus departure from Ho Chi Minh City with comfortable hotel accommodation included.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Ho Chi Minh City – Dalat</h3>
                    <p>Depart by bus from Ho Chi Minh City to Dalat. Upon arrival, check in to your hotel and spend the evening relaxing or exploring Dalat Night Market at your leisure.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Tuyen Lam Lake – Clay Tunnel</h3>
                    <p>Start your day with a peaceful walk around the scenic Tuyen Lam Lake. Later, visit the Clay Tunnel to admire the creative sculptures that reflect Dalat’s culture and history.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Fresh Garden – Coffee Farm Experience</h3>
                    <p>Visit Fresh Garden to enjoy vibrant flower beds and local produce. In the afternoon, tour a nearby coffee farm to learn about coffee production and enjoy fresh coffee tasting.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Ho Chi Minh City</h3>
                    <p>Spend a relaxed morning at your own pace. After checking out, board the bus for your return trip to Ho Chi Minh City, concluding your peaceful Dalat getaway.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">5,790,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">6,579,545 VND</p>
                    <a href="/tour/booking?cityid=15&tourid=24" class="booking-button">Booking now!</a>
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
