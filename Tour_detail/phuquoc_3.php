<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phu Quoc Island Serenity: Sao Beach - Pepper Farm - Night Market - Sunset at Dinh Cau</title>
    <meta name="description" content="Relax in Phu Quoc with this serene 4-day tour to Sao Beach, Pepper Farm, the vibrant Night Market, and a peaceful sunset at Dinh Cau.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Phu Quoc Island Serenity: Sao Beach - Pepper Farm - Night Market - Sunset at Dinh Cau</h1>
    
    <div class="gallery" id="lightgallery">
        <a href="phuquoc3/1.jpg" class="big">
            <img src="phuquoc3/1.jpg" alt="Sao Beach" loading="lazy" width="800" height="600">
        </a>
        <a href="phuquoc3/2.jpg" class="small1">
            <img src="phuquoc3/2.jpg" alt="Pepper Farm" loading="lazy" width="400" height="300">
        </a>
        <a href="phuquoc3/3.jpg" class="small2">
            <img src="phuquoc3/3.jpg" alt="Phu Quoc Seafood" loading="lazy" width="400" height="300">
        </a>
        <a href="phuquoc3/4.jpg" class="small3">
            <img src="phuquoc3/4.jpg" alt="Phu Quoc Night Market" loading="lazy" width="400" height="300">
        </a>
        <a href="phuquoc3/5.jpg" class="small4">
            <img src="phuquoc3/5.jpg" alt="Dinh Cau" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Relax on the pristine Sao Beach, famous for its white sand and crystal-clear waters.</li>
                    <li>Visit a local Pepper Farm and learn about the cultivation of Phu Quoc’s renowned pepper.</li>
                    <li>Explore the bustling Phu Quoc Night Market with diverse local foods and unique souvenirs.</li>
                    <li>Enjoy a serene sunset at Dinh Cau, a scenic and cultural highlight of the island.</li>
                    <li>Travel comfortably with direct flight from Da Nang and stay in quality hotel accommodations.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Da Nang – Phu Quoc</h3>
                    <p>Fly from Da Nang to Phu Quoc. Upon arrival, check into your hotel and relax. Spend the evening exploring the vibrant Phu Quoc Night Market.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Sao Beach – Pepper Farm</h3>
                    <p>Enjoy a morning at the beautiful Sao Beach. In the afternoon, visit a local Pepper Farm and learn about its importance to the island's economy.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Leisure & Sunset at Dinh Cau</h3>
                    <p>Spend the day at your leisure. In the evening, witness a stunning sunset at Dinh Cau and explore the nearby market area.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Free Morning – Return to Da Nang</h3>
                    <p>Enjoy a relaxing morning before check-out. Transfer to the airport for your return flight to Da Nang, concluding your peaceful Phu Quoc getaway.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,490,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,475,000 VND</p>
                    <a href="/tour/booking?cityid=16&tourid=27" class="booking-button">Booking now!</a>
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
