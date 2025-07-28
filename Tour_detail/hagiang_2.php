<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ha Giang Explorer</title>
    <meta name="description" content="Explore Quan Ba Heaven Gate, Dong Van Plateau, Meo Vac, and enjoy a unique homestay experience in the heart of Ha Giang.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Ha Giang Explorer: Quan Ba Heaven Gate - Dong Van Karst Plateau - Meo Vac - Local Homestay Experience</h1>

    <div class="gallery" id="lightgallery">
        <a href="hagiang2/1.jpg" class="big">
            <img src="hagiang2/1.jpg" alt="View of Ma Pi Leng Pass in Ha Giang" loading="lazy">
        </a>
        <a href="hagiang2/2.jpg" class="small1">
            <img src="hagiang2/2.jpg" alt="Kayaking on Nho Que River" loading="lazy">
        </a>
        <a href="hagiang2/3.jpg" class="small2">
            <img src="hagiang2/3.jpg" alt="Lung Cu Flag Tower, Vietnam's northernmost point" loading="lazy">
        </a>
        <a href="hagiang2/4.jpg" class="small3">
            <img src="hagiang2/4.jpg" alt="Dong Van ancient street and stone houses" loading="lazy">
        </a>
        <a href="hagiang2/5.jpg" class="small4">
            <img src="hagiang2/5.jpg" alt="Tam Giac Mach Flower Garden in bloom" loading="lazy">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Marvel at panoramic views from Quan Ba Heaven Gate, the "Gateway to Heaven" of Ha Giang.</li>
                    <li>Explore Dong Van Karst Plateau – a UNESCO Global Geopark with striking rock formations.</li>
                    <li>Visit Meo Vac, a beautiful town nestled among limestone peaks and deep valleys.</li>
                    <li>Enjoy a unique overnight stay in a local homestay, learning ethnic customs and cuisine firsthand.</li>
                    <li>Convenient travel with accommodations ranging from cozy hotels to traditional stilt houses.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Hanoi – Ha Giang</h3>
                    <p>Depart early from Hanoi by comfortable bus. Arrive in Ha Giang in the afternoon, check in to a hotel or homestay, and relax for the evening.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Quan Ba Heaven Gate – Dong Van Plateau</h3>
                    <p>Travel north to Quan Ba and pass through the iconic Heaven Gate. Continue through breathtaking Dong Van Karst Plateau, stopping at ethnic markets and photo spots.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Meo Vac – Local Homestay Experience</h3>
                    <p>Journey to Meo Vac via Ma Pi Leng Pass, known for its world-class views. Arrive at your homestay and immerse in traditional music, food, and conversation with your hosts.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Leisure Morning – Return to Hanoi</h3>
                    <p>Enjoy a relaxing breakfast before returning by bus to Hanoi, bringing your Ha Giang experience to a memorable close.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,990,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,860,000 VND</p>
                    <a href="/tour/booking?cityid=18&tourid=34" class="booking-button">Booking now!</a>
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
