<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ha Giang Adventure & Culture</title>
    <meta name="description" content="Experience the breathtaking Ma Pi Leng Pass, Dong Van Plateau, Lung Cu Flag Tower, and immerse in the ethnic culture of Ha Giang.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Ha Giang Adventure & Culture: Ma Pi Leng Pass - Dong Van Plateau - Lung Cu Flag Tower - Ethnic Villages</h1>

    <div class="gallery" id="lightgallery">
        <a href="hagiang1/1.jpg" class="big">
            <img src="hagiang1/1.jpg" alt="Stunning view over Dong Van District in Ha Giang" loading="lazy">
        </a>
        <a href="hagiang1/2.jpg" class="small1">
            <img src="hagiang1/2.jpg" alt="Breathtaking Ma Pi Leng Pass" loading="lazy">
        </a>
        <a href="hagiang1/3.jpg" class="small2">
            <img src="hagiang1/3.jpg" alt="Lung Cu Flag Tower, northernmost point of Vietnam" loading="lazy">
        </a>
        <a href="hagiang1/4.jpg" class="small3">
            <img src="hagiang1/4.jpg" alt="Winding roads of Bac Sum Slope" loading="lazy">
        </a>
        <a href="hagiang1/5.jpg" class="small4">
            <img src="hagiang1/5.jpg" alt="Meo Vac District – Rugged karst scenery" loading="lazy">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore the breathtaking Ma Pi Leng Pass, one of Vietnam’s most spectacular mountain roads.</li>
                    <li>Discover Dong Van Karst Plateau, a UNESCO Global Geopark with dramatic limestone formations.</li>
                    <li>Visit the Lung Cu Flag Tower – Vietnam’s symbolic northernmost landmark.</li>
                    <li>Experience traditional lifestyles in ethnic minority villages across Ha Giang province.</li>
                    <li>Enjoy a comfortable and safe journey from Hanoi, with quality hotel stays included.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Hanoi – Ha Giang</h3>
                    <p>Depart from Hanoi by bus. Upon arrival in Ha Giang, check into your hotel and enjoy an evening exploring or relaxing before the adventure begins.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Ma Pi Leng Pass – Dong Van Plateau</h3>
                    <p>Travel through Ma Pi Leng Pass and marvel at panoramic views of mountain ridges and river valleys. Continue to Dong Van Plateau, visiting local markets and heritage villages.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Lung Cu Flag Tower – Ethnic Villages</h3>
                    <p>Reach Lung Cu Flag Tower and take in expansive views from Vietnam’s northern frontier. Then visit surrounding ethnic villages to connect with local culture and history.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Hanoi</h3>
                    <p>Enjoy a relaxed morning in Ha Giang. After check-out, return by bus to Hanoi, completing your memorable northern journey.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,890,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,830,000 VND</p>
                    <a href="/tour/booking?cityid=18&tourid=33" class="booking-button">Booking now!</a>
                </div>
            </div>

            <div class="box">
                <h3>Contact Support</h3>
                <p>📞 Hotline: <a href="tel:19192025">1919 2025</a><br>
                   ✉️ Email: <a href="mailto:viettransit.support@mail.com">viettransit.support@mail.com</a></p>
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
