<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nha Trang - Yang Bay Eco Park - Mud Bath - Dam Market - Ponagar - Coastal Road</title>
    <meta name="description" content="Discover Yang Bay Eco Park, relax with a mud bath, visit Dam Market and Thap Ba Ponagar, and enjoy Nha Trang’s scenic coastal roads. Depart from Ho Chi Minh City.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Nha Trang - Yang Bay Eco Park - Mud Bath - Dam Market - Ponagar - Coastal Road</h1>

    <div class="gallery" id="lightgallery">
        <a href="Nhatrang4/1.jpg" class="big">
            <img src="Nhatrang4/1.jpg" alt="Yang Bay Waterfall" loading="lazy" width="800" height="600">
        </a>
        <a href="Nhatrang4/2.jpg" class="small1">
            <img src="Nhatrang4/2.jpg" alt="Nha Trang Mud Bath" loading="lazy" width="400" height="300">
        </a>
        <a href="Nhatrang4/3.jpg" class="small2">
            <img src="Nhatrang4/3.jpg" alt="Dam Market Shopping" loading="lazy" width="400" height="300">
        </a>
        <a href="Nhatrang4/4.jpg" class="small3">
            <img src="Nhatrang4/4.jpg" alt="Coastal Road View" loading="lazy" width="400" height="300">
        </a>
        <a href="Nhatrang4/5.jpg" class="small4">
            <img src="Nhatrang4/5.jpg" alt="Thap Ba Ponagar Temple" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover the natural beauty and cultural charm of Yang Bay Eco Park.</li>
                    <li>Relax and rejuvenate with a traditional Nha Trang mud bath experience.</li>
                    <li>Visit Thap Ba Ponagar, a historical Cham temple complex.</li>
                    <li>Explore Dam Market, a vibrant hub for local specialties and souvenirs.</li>
                    <li>Enjoy a scenic drive along Nha Trang’s picturesque coastal roads.</li>
                    <li>Stay at a comfortable 3-star hotel conveniently located near the beach.</li>
                    <li>Easy and quick flight departure from Ho Chi Minh City.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Ho Chi Minh City – Nha Trang – Yang Bay Eco Park</h3>
                    <p>Fly from Ho Chi Minh City to Nha Trang. Upon arrival, travel to Yang Bay Eco Park to immerse yourself in lush nature, waterfalls, and cultural experiences. Check in to your 3-star hotel for an evening at leisure.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Mud Bath – Thap Ba Ponagar – Coastal Road</h3>
                    <p>Start the day with a relaxing mud bath session. Visit the sacred Thap Ba Ponagar temple, then enjoy a scenic coastal drive with photo stops. Return to the hotel and unwind or explore the local night scene.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Dam Market – Return to Ho Chi Minh City</h3>
                    <p>In the morning, visit Dam Market to shop for local products and souvenirs. Afterward, transfer to the airport for your return flight to Ho Chi Minh City.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">2,890,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">3,481,200 VND</p>
                    <a href="/tour/booking?cityid=12&tourid=12" class="booking-button">Booking now!</a>
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
