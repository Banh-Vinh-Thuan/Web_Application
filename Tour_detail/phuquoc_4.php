<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phu Quoc Relax & Discover: Ong Lang Beach - Fish Sauce Village - Night Market</title>
    <meta name="description" content="Enjoy 4 days in Phu Quoc visiting Ong Lang Beach, Pepper Farm, Fish Sauce Village, and the lively Night Market.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Phu Quoc Relax & Discover: Ong Lang Beach - Pepper Farm - Fish Sauce Village - Night Market</h1>

    <div class="gallery" id="lightgallery">
        <a href="phuquoc4/1.jpg" class="big">
            <img src="phuquoc4/1.jpg" alt="Ong Lang Beach" loading="lazy" width="800" height="600">
        </a>
        <a href="phuquoc4/2.jpg" class="small1">
            <img src="phuquoc4/2.jpg" alt="Fish Sauce Village" loading="lazy">
        </a>
        <a href="phuquoc4/3.jpg" class="small2">
            <img src="phuquoc4/3.jpg" alt="Fish Sauce Barrels" loading="lazy">
        </a>
        <a href="phuquoc4/4.jpg" class="small3">
            <img src="phuquoc4/4.jpg" alt="Phu Quoc Night Market" loading="lazy">
        </a>
        <a href="phuquoc4/5.jpg" class="small4">
            <img src="phuquoc4/5.jpg" alt="Phu Quoc GrandWorld" loading="lazy">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Relax on the serene Ong Lang Beach, known for its peaceful atmosphere and clear waters.</li>
                    <li>Visit a local Pepper Farm to explore Phu Quoc’s iconic spice cultivation.</li>
                    <li>Discover the traditional Fish Sauce Village and its time-honored fermentation process.</li>
                    <li>Enjoy Phu Quoc Night Market with vibrant local life, cuisine, and crafts.</li>
                    <li>Travel comfortably by bus from Hanoi and stay in well-rated accommodations.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Hanoi – Phu Quoc</h3>
                    <p>Depart from Hanoi by bus and travel to Phu Quoc. Upon arrival, check into the hotel and relax. Enjoy your evening exploring the local area or unwinding at the beach.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Ong Lang Beach – Pepper Farm</h3>
                    <p>Start your day relaxing at Ong Lang Beach. In the afternoon, head to a local Pepper Farm and gain insight into Phu Quoc's pepper production.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Fish Sauce Village – Night Market</h3>
                    <p>Explore the Fish Sauce Village, home to the island's traditional craft. In the evening, experience the lively atmosphere of the Phu Quoc Night Market.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Hanoi</h3>
                    <p>Enjoy a free morning for personal time or shopping. Check out and board the return bus to Hanoi, concluding your Phu Quoc Relax & Discover tour.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,390,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,100,000 VND</p>
                    <a href="/tour/booking?cityid=16&tourid=28" class="booking-button">Booking now!</a>
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
