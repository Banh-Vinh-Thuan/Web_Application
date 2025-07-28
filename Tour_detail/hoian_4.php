<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoi An Nature & Culture</title>
    <meta name="description" content="Experience the harmony of Hoi An's nature and culture through basket boat rides, river cruises, Marble Mountains exploration, and handicraft workshops.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Hoi An Nature & Culture: Cam Thanh Village - Thu Bon River Cruise - Marble Mountains - Local Handicraft Workshops</h1>

    <div class="gallery" id="lightgallery">
        <a href="hoian4/1.jpg" class="big">
            <img src="hoian4/1.jpg" alt="Bay Mau Coconut Forest" loading="lazy" width="800" height="600">
        </a>
        <a href="hoian4/2.jpg" class="small1">
            <img src="hoian4/2.jpg" alt="Boat on Thu Bon River" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian4/3.jpg" class="small2">
            <img src="hoian4/3.jpg" alt="Marble Mountains View" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian4/4.jpg" class="small3">
            <img src="hoian4/4.jpg" alt="Lantern Making Workshop" loading="lazy" width="400" height="300">
        </a>
        <a href="hoian4/5.jpg" class="small4">
            <img src="hoian4/5.jpg" alt="Cave at Marble Mountains" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <section class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore the peaceful Cam Thanh Village with a unique basket boat ride through lush nipa palm forests.</li>
                    <li>Cruise along the Thu Bon River and witness daily local life and scenic countryside.</li>
                    <li>Discover the Marble Mountains with sacred caves, temples, and panoramic views.</li>
                    <li>Participate in lantern-making and wood-carving workshops guided by local artisans.</li>
                    <li>Enjoy round-trip bus transport from Da Nang and stay in comfortable accommodations.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>

                <article class="box2">
                    <h3>Day 1: Da Nang – Hoi An</h3>
                    <p>Depart from Da Nang and arrive in Hoi An. Check in to your hotel, enjoy a welcome dinner, and take a leisurely evening stroll through the illuminated Ancient Town.</p>
                </article>

                <article class="box2">
                    <h3>Day 2: Cam Thanh Village – Handicraft Workshops</h3>
                    <p>Visit Cam Thanh Village in the morning for a basket boat ride and interaction with local fishermen. In the afternoon, join workshops to create your own lantern or wooden artwork.</p>
                </article>

                <article class="box2">
                    <h3>Day 3: Thu Bon River Cruise – Marble Mountains</h3>
                    <p>Take a scenic boat ride along the Thu Bon River visiting nearby craft villages. In the afternoon, explore Marble Mountains, its pagodas and natural caves, with stunning views of Da Nang Bay.</p>
                </article>

                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Da Nang</h3>
                    <p>Spend a relaxing morning shopping or sightseeing. After checking out, return to Da Nang by bus, ending your memorable cultural escape in Hoi An.</p>
                </article>
            </div>
        </section>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,150,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,230,000 VND</p>
                    <a href="/tour/booking?cityid=17&tourid=32" class="booking-button">Booking now!</a>
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
