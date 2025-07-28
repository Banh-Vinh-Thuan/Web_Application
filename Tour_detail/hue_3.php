<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hue Cultural Discovery – Imperial Citadel, Thien Mu Pagoda, Perfume River, Local Cuisine</title>
    <meta name="description" content="Discover Hue’s imperial heritage through a 4-day tour featuring the Imperial Citadel, Thien Mu Pagoda, Perfume River cruise, and traditional cuisine.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" href="../images/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>

<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Hue Cultural Discovery – Imperial Citadel – Thien Mu Pagoda – Perfume River – Truong Tien Bridge – Local Cuisine</h1>

    <div class="gallery" id="lightgallery">
        <a href="Hue3/1.jpg" class="big"><img src="Hue3/1.jpg" alt="Thuy Xuan Village" loading="lazy" width="800" height="600"></a>
        <a href="Hue3/2.jpg" class="small1"><img src="Hue3/2.jpg" alt="Imperial Citadel of Hue" loading="lazy" width="400" height="300"></a>
        <a href="Hue3/3.jpg" class="small2"><img src="Hue3/3.jpg" alt="Thien Mu Pagoda" loading="lazy" width="400" height="300"></a>
        <a href="Hue3/4.jpg" class="small3"><img src="Hue3/4.jpg" alt="Truong Tien Bridge" loading="lazy" width="400" height="300"></a>
        <a href="Hue3/5.jpg" class="small4"><img src="Hue3/5.jpg" alt="Hue Local Cuisine" loading="lazy" width="400" height="300"></a>
    </div>

    <div class="content-columns">
        <section class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Immerse in Hue’s rich imperial culture and spiritual landmarks.</li>
                    <li>Visit the Imperial Citadel, a UNESCO World Heritage Site.</li>
                    <li>Admire the historic Thien Mu Pagoda and cruise the Perfume River.</li>
                    <li>Walk the French-built Truong Tien Bridge and explore local life.</li>
                    <li>Delight in authentic Hue cuisine – from royal dishes to street food.</li>
                    <li>Convenient flights from Can Tho make travel smooth and easy.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>

                <article class="box2">
                    <h3>Day 1: Can Tho – Hue – City Exploration</h3>
                    <p>Flight from Can Tho to Hue. Visit the Imperial Citadel and explore the charm of this ancient capital.</p>
                </article>

                <article class="box2">
                    <h3>Day 2: Thien Mu Pagoda – Perfume River Cruise</h3>
                    <p>Discover Thien Mu Pagoda, then enjoy a peaceful cruise along the Perfume River to see historic sites from the water.</p>
                </article>

                <article class="box2">
                    <h3>Day 3: Truong Tien Bridge – Hue Local Food Tour</h3>
                    <p>Cross Truong Tien Bridge and take a guided tour to sample the best Hue has to offer – from bánh bèo to bún bò Huế.</p>
                </article>

                <article class="box2">
                    <h3>Day 4: Free Time – Return Flight</h3>
                    <p>Spend the morning shopping or relaxing before your flight back to Can Tho.</p>
                </article>
            </div>
        </section>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">7,590,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">8,923,500 VND</p>
                    <a href="/tour/booking?cityid=13&tourid=15" class="booking-button">Booking now!</a>
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
