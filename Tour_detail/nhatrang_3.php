<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nha Trang – Snorkeling, Cable Car, Seafood & More</title>
    <meta name="description" content="Snorkel at Hon Mun, ride Vinpearl Cable Car, visit Long Son Pagoda, and enjoy local seafood in Nha Trang with a 4-star stay. Depart from Ho Chi Minh City.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>

<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Nha Trang – Hon Mun Snorkeling, Cable Car, Seafood & More</h1>

    <div class="gallery" id="lightgallery">
        <a href="Nhatrang3/1.jpg" class="big">
            <img src="Nhatrang3/1.jpg" alt="Hon Mun Island snorkeling" loading="lazy" width="800" height="600">
        </a>
        <a href="Nhatrang3/2.jpg" class="small1">
            <img src="Nhatrang3/2.jpg" alt="Institute of Oceanography Aquarium" loading="lazy" width="400" height="300">
        </a>
        <a href="Nhatrang3/3.jpg" class="small2">
            <img src="Nhatrang3/3.jpg" alt="Vinpearl Cable Car" loading="lazy" width="400" height="300">
        </a>
        <a href="Nhatrang3/4.jpg" class="small3">
            <img src="Nhatrang3/4.jpg" alt="Long Son Pagoda" loading="lazy" width="400" height="300">
        </a>
        <a href="Nhatrang3/5.jpg" class="small4">
            <img src="Nhatrang3/5.jpg" alt="Nha Trang seafood dinner" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Snorkel among vibrant coral reefs at Hon Mun Island.</li>
                    <li>Visit Vietnam’s oldest marine research hub – the Institute of Oceanography.</li>
                    <li>Ride the Vinpearl Cable Car over the bay for stunning sea views.</li>
                    <li>Explore the peaceful Long Son Pagoda and its iconic white Buddha.</li>
                    <li>Enjoy a mouth-watering local seafood feast by the beach.</li>
                    <li>Stay in a 4-star beachfront hotel with ocean views.</li>
                    <li>Fly conveniently from Ho Chi Minh City to Nha Trang and back.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>

                <article class="box2">
                    <h3>Day 1: HCMC → Nha Trang → Institute of Oceanography</h3>
                    <p>Fly from Ho Chi Minh City to Nha Trang. Explore marine biodiversity at the Institute of Oceanography. Check into your beachfront 4-star hotel and relax.</p>
                </article>

                <article class="box2">
                    <h3>Day 2: Snorkeling at Hon Mun – Vinpearl Cable Car – Seafood Feast</h3>
                    <p>Start your day snorkeling in the clear waters of Hon Mun Island. Ride the Vinpearl Cable Car in the afternoon, and enjoy a local seafood feast at sunset.</p>
                </article>

                <article class="box2">
                    <h3>Day 3: Long Son Pagoda – Return to HCMC</h3>
                    <p>Visit Long Son Pagoda. Enjoy some last-minute beach time or shopping before your return flight to Ho Chi Minh City.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">3,190,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">3,752,900 VND</p>
                    <a href="/tour/booking?cityid=12&tourid=11" class="booking-button">Booking now!</a>
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
                    <li>Exclusive online deals</li>
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
