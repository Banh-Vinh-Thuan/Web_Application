<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saigon City Tour - Duc Ba Church, Nguyen Hue Street, Landmark 81, Bui Vien, Dinh Doc Lap</title>
    <meta name="description" content="Explore Saigon's top attractions in this 3-day city tour. Visit Duc Ba Church, Nguyen Hue Street, Landmark 81, Bui Vien nightlife, and the historic Dinh Doc Lap.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Saigon City Tour - Duc Ba Church, Nguyen Hue Street, Landmark 81, Bui Vien, Dinh Doc Lap</h1>
    
    <div class="gallery" id="lightgallery">
        <a href="hcm2/1.jpg" class="big">
            <img src="hcm2/1.jpg" alt="Saigon Street Food" loading="lazy" width="800" height="600">
        </a>
        <a href="hcm2/2.jpg" class="small1">
            <img src="hcm2/2.jpg" alt="Nguyen Hue Street" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm2/3.jpg" class="small2">
            <img src="hcm2/3.jpg" alt="Landmark 81" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm2/4.jpg" class="small3">
            <img src="hcm2/4.jpg" alt="Ben Thanh Market" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm2/5.jpg" class="small4">
            <img src="hcm2/5.jpg" alt="Dinh Doc Lap" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover Saigon's iconic landmarks over three days of exploration.</li>
                    <li>Experience the majestic architecture of Duc Ba Church and the vibrant Nguyen Hue Street.</li>
                    <li>Enjoy panoramic city views from Landmark 81 and the bustling nightlife of Bui Vien Walking Street.</li>
                    <li>Explore the historical significance of Dinh Doc Lap and shop at Ben Thanh Market.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Duc Ba Church & Nguyen Hue Street</h3>
                    <p>Start your journey with a visit to the iconic Duc Ba Church followed by a stroll along Nguyen Hue Street, a lively walking area filled with cafes and street performers.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Landmark 81 & Bui Vien Walking Street</h3>
                    <p>Ascend Landmark 81 for panoramic city views, then head to Bui Vien for a vibrant evening filled with food, music, and nightlife.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Dinh Doc Lap & Ben Thanh Market</h3>
                    <p>Conclude the tour with a visit to the historic Dinh Doc Lap and shop for souvenirs at Ben Thanh Market.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">890,000 ₫</p>
                    <p style="text-decoration: line-through; color: gray;">933,000 ₫</p>
                    <a href="/tour/booking?cityid=11&tourid=6" class="booking-button">Booking now!</a>
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
