<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nha Trang – Pottery & Seashell Workshop, Ancient Nha Trang & More</title>
    <meta name="description" content="Hands-on experiences in Nha Trang including Bau Truc Pottery Village, Ponagar Tower, and a unique seashell workshop. Includes 4-star hotel stay.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>

<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Nha Trang – Pottery & Seashell Workshop, Ancient Nha Trang & More</h1>

    <div class="gallery" id="lightgallery">
        <a href="nhatrang2/1.jpg" class="big"><img src="nhatrang2/1.jpg" alt="Bau Truc Pottery Village" loading="lazy" width="800" height="600"></a>
        <a href="nhatrang2/2.jpg" class="small1"><img src="nhatrang2/2.jpg" alt="Thap Ba Ponagar" loading="lazy" width="400" height="300"></a>
        <a href="nhatrang2/3.jpg" class="small2"><img src="nhatrang2/3.jpg" alt="Seashell Workshop" loading="lazy" width="400" height="300"></a>
        <a href="nhatrang2/4.jpg" class="small3"><img src="nhatrang2/4.jpg" alt="VinPearl Nha Trang" loading="lazy" width="400" height="300"></a>
        <a href="nhatrang2/5.jpg" class="small4"><img src="nhatrang2/5.jpg" alt="VinWonders Nha Trang" loading="lazy" width="400" height="300"></a>
    </div>

    <div class="content-columns">
        <section class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Visit Bau Truc Pottery Village – one of Southeast Asia’s oldest traditional craft villages.</li>
                    <li>Discover the history of ancient Nha Trang and explore the sacred Tháp Bà Ponagar complex.</li>
                    <li>Relax at Nhu Tien Beach and enjoy entertainment at VinWonders theme park.</li>
                    <li>Create unique keepsakes during a guided Pottery & Seashell Workshop session.</li>
                    <li>Stay at a well-rated 4-star hotel and depart comfortably from Da Nang.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Da Nang → Nha Trang → Bau Truc Pottery Village</h3>
                    <p>Depart from Da Nang, arrive in Nha Trang, and explore Bau Truc Pottery Village. Check in to a 4-star hotel and enjoy your evening at leisure.</p>
                </article>

                <article class="box2">
                    <h3>Day 2: Ponagar Towers & Seashell Workshop</h3>
                    <p>Visit Ponagar Cham Towers in the morning. In the afternoon, join our interactive Pottery & Seashell Workshop where you create your own coastal-themed souvenirs.</p>
                </article>

                <article class="box2">
                    <h3>Day 3: Nhu Tien Beach → VinWonders → Return to Da Nang</h3>
                    <p>Spend a relaxing morning at Nhu Tien Beach followed by an afternoon of family-friendly adventures at VinWonders before heading back to Da Nang.</p>
                </article>
            </div>
        </section>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">2,690,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">3,280,488 VND</p>
                    <a href="/tour/booking?cityid=12&tourid=10" class="booking-button">Booking now!</a>
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
                    <li>Exclusive online-only deals</li>
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
