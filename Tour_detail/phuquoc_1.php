<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phu Quoc Paradise Escape</title>
    <meta name="description" content="Relax on Sao Beach, visit Vinpearl Safari, shop at Phu Quoc Night Market, and explore a pepper farm. Depart from Ho Chi Minh City for a tropical island getaway.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Phu Quoc Paradise Escape: Sao Beach - Vinpearl Safari - Night Market - Pepper Farm</h1>

    <div class="gallery" id="lightgallery">
        <a href="phuquoc1/1.jpg" class="big">
            <img src="phuquoc1/1.jpg" alt="Hon Thom Beach" loading="lazy" width="800" height="600">
        </a>
        <a href="phuquoc1/2.jpg" class="small1">
            <img src="phuquoc1/2.jpg" alt="Phu Quoc Beach" loading="lazy" width="400" height="300">
        </a>
        <a href="phuquoc1/3.jpg" class="small2">
            <img src="phuquoc1/3.jpg" alt="Tropical coastline" loading="lazy" width="400" height="300">
        </a>
        <a href="phuquoc1/4.jpg" class="small3">
            <img src="phuquoc1/4.jpg" alt="Suoi Tranh Waterfall" loading="lazy" width="400" height="300">
        </a>
        <a href="phuquoc1/5.jpg" class="small4">
            <img src="phuquoc1/5.jpg" alt="Phu Quoc Night Market" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Relax on the pristine Sao Beach, known for its white sand and clear turquoise waters.</li>
                    <li>Experience the exciting Vinpearl Safari, home to diverse wildlife in natural habitats.</li>
                    <li>Explore the vibrant Phu Quoc Night Market with delicious local food and unique souvenirs.</li>
                    <li>Visit a traditional Pepper Farm to learn about one of Phu Quoc’s famous agricultural products.</li>
                    <li>Enjoy a comfortable trip with direct flight from Ho Chi Minh City and 3-star hotel stays.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Ho Chi Minh City – Phu Quoc</h3>
                    <p>Take a flight from Ho Chi Minh City to Phu Quoc. Upon arrival, check in to your hotel and relax. Spend the evening exploring the lively Phu Quoc Night Market.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Sao Beach – Vinpearl Safari</h3>
                    <p>Enjoy a morning at the beautiful Sao Beach, soaking up the sun and swimming in the clear waters. In the afternoon, visit Vinpearl Safari to see exotic animals and learn about conservation efforts.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Pepper Farm – Free Time</h3>
                    <p>Tour a local Pepper Farm to discover how this island specialty is cultivated. Spend the afternoon at leisure, relaxing or exploring on your own.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Ho Chi Minh City</h3>
                    <p>Enjoy a free morning to relax or shop for souvenirs before checking out. Transfer to the airport for your return flight to Ho Chi Minh City, concluding your Phu Quoc paradise escape.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,290,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,136,364 VND</p>
                    <a href="/tour/booking?cityid=16&tourid=25" class="booking-button">Booking now!</a>
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
