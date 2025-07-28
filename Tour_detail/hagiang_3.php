<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ha Giang Nature & Culture: Lung Cu Flagpole - Nho Que River Boat Ride - Ma Pi Leng Pass - Minority Villages</title>
    <meta name="description" content="Embark on a 4-day journey to Ha Giang featuring the Lung Cu Flagpole, Nho Que River, Ma Pi Leng Pass, and vibrant minority villages.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Ha Giang Nature & Culture: Lung Cu Flagpole - Nho Que River Boat Ride - Ma Pi Leng Pass - Minority Villages</h1>

    <div class="gallery" id="lightgallery">
        <a href="hagiang3/1.jpg" class="big">
            <img src="hagiang3/1.jpg" alt="Lung Cu Flagpole" loading="lazy" width="800" height="600">
        </a>
        <a href="hagiang3/2.jpg" class="small1">
            <img src="hagiang3/2.jpg" alt="Nho Que River" loading="lazy" width="400" height="300">
        </a>
        <a href="hagiang3/3.jpg" class="small2">
            <img src="hagiang3/3.jpg" alt="Nho Que River Cliffs" loading="lazy" width="400" height="300">
        </a>
        <a href="hagiang3/4.jpg" class="small3">
            <img src="hagiang3/4.jpg" alt="Ma Pi Leng Pass" loading="lazy" width="400" height="300">
        </a>
        <a href="hagiang3/5.jpg" class="small4">
            <img src="hagiang3/5.jpg" alt="Minority Village" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Visit Lung Cu Flagpole, the northernmost point of Vietnam with panoramic views.</li>
                    <li>Enjoy a boat ride along the crystal-clear Nho Que River through dramatic gorges.</li>
                    <li>Drive through Ma Pi Leng Pass, one of Vietnam’s most stunning mountain roads.</li>
                    <li>Discover minority village life through immersive homestay experiences.</li>
                    <li>Depart from Hanoi with a fully organized itinerary and quality accommodation.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Hanoi – Ha Giang</h3>
                    <p>Depart from Hanoi by bus, enjoying scenic countryside en route to Ha Giang. Check in and relax at a local hotel or homestay.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Lung Cu Flagpole – Minority Villages</h3>
                    <p>Visit Lung Cu Flagpole for breathtaking views. Explore nearby ethnic villages and learn about traditional life.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Nho Que River – Ma Pi Leng Pass</h3>
                    <p>Take a boat ride along the Nho Que River and then travel through the winding Ma Pi Leng Pass with multiple photo stops.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Morning Leisure – Return to Hanoi</h3>
                    <p>Enjoy a slow morning in Ha Giang before boarding the return bus to Hanoi, ending your cultural journey.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,190,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,110,000 VND</p>
                    <a href="/tour/booking?cityid=18&tourid=35" class="booking-button">Booking now!</a>
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
