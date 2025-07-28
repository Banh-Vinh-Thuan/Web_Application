<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dalat Flower & Highlands Adventure: Valley of Love - Langbiang Peak - Dalat Railway - Hydrangea Garden</title>
    <meta name="description" content="Join the Dalat Flower & Highlands Adventure tour to explore Valley of Love, Langbiang Peak, Dalat Railway, and the stunning Hydrangea Garden.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Dalat Flower & Highlands Adventure: Valley of Love - Langbiang Peak - Dalat Railway - Hydrangea Garden</h1>

    <div class="gallery" id="lightgallery">
        <a href="dalat3/1.jpg" class="big">
            <img src="dalat3/1.jpg" alt="Dalat Flower Garden" loading="lazy" width="800" height="600">
        </a>
        <a href="dalat3/2.jpg" class="small1">
            <img src="dalat3/2.jpg" alt="Langbiang Mountain view" loading="lazy" width="400" height="300">
        </a>
        <a href="dalat3/3.jpg" class="small2">
            <img src="dalat3/3.jpg" alt="Historic Dalat Train Station" loading="lazy" width="400" height="300">
        </a>
        <a href="dalat3/4.jpg" class="small3">
            <img src="dalat3/4.jpg" alt="Truc Lam Zen Monastery surroundings" loading="lazy" width="400" height="300">
        </a>
        <a href="dalat3/5.jpg" class="small4">
            <img src="dalat3/5.jpg" alt="Traditional Dalat cuisine" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore Dalat's most iconic flower-themed attractions and highland adventures.</li>
                    <li>Stroll through the romantic Valley of Love and colorful Hydrangea Garden.</li>
                    <li>Conquer Langbiang Peak for breathtaking panoramic views of Dalat and the highlands.</li>
                    <li>Enjoy a nostalgic ride on the historic Dalat Railway through scenic countryside.</li>
                    <li>Convenient flight departure from Da Nang and quality hotel accommodation included.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Da Nang – Dalat</h3>
                    <p>Take a flight from Da Nang to Dalat. Upon arrival, check in to your hotel and relax. Spend the evening visiting the Dalat Night Market for local food and souvenirs.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Valley of Love – Langbiang Peak</h3>
                    <p>Begin the day at the romantic Valley of Love with lush gardens and lakeside views. In the afternoon, head to Langbiang Peak for a jeep ride to the summit and panoramic views of the Central Highlands.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Dalat Railway – Hydrangea Garden</h3>
                    <p>Enjoy a nostalgic ride on the vintage Dalat Railway. Then visit the Hydrangea Garden, a stunning location filled with vibrant seasonal blooms – perfect for nature lovers and photography enthusiasts.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Da Nang</h3>
                    <p>Have a relaxing morning at your own pace. After checking out, transfer to the airport for your return flight to Da Nang, ending your colorful Dalat adventure.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,290,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,400,000 VND</p>
                    <a href="/tour/booking?cityid=15&tourid=23" class="booking-button">Booking now!</a>
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
