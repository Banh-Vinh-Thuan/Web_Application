<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phu Yen - Mui Dien Sunrise - Vung Ro Bay - Bai Mon Beach - Seafood Tour</title>
    <meta name="description" content="Experience the beauty of Phu Yen with a sunrise at Mui Dien, cruise Vung Ro Bay, relax at Bai Mon Beach, and enjoy fresh local seafood.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Phu Yen - Mui Dien Sunrise - Vung Ro Bay - Bai Mon Beach - Seafood Tour</h1>

    <div class="gallery" id="lightgallery">
        <a href="Phuyen2/1.jpg" class="big"><img src="Phuyen2/1.jpg" alt="Mui Dien Lighthouse at Sunrise" loading="lazy"></a>
        <a href="Phuyen2/2.jpg" class="small1"><img src="Phuyen2/2.jpg" alt="Scenic Coastal Road to Bai Mon" loading="lazy"></a>
        <a href="Phuyen2/3.jpg" class="small2"><img src="Phuyen2/3.jpg" alt="Bai Mon Beach" loading="lazy"></a>
        <a href="Phuyen2/4.jpg" class="small3"><img src="Phuyen2/4.jpg" alt="Seafood Dining in Phu Yen" loading="lazy"></a>
        <a href="Phuyen2/5.jpg" class="small4"><img src="Phuyen2/5.jpg" alt="Dam Market Local Culture" loading="lazy"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Watch Vietnam's first sunrise at Mui Dien – the easternmost tip of the mainland.</li>
                    <li>Uncover the wartime stories and scenery of Vung Ro Bay by boat.</li>
                    <li>Bask in the beauty of Bai Mon Beach with clear waters and gentle waves.</li>
                    <li>Enjoy an authentic seafood feast, freshly caught from local waters.</li>
                    <li>Relax in a luxurious 4-star beach resort throughout your stay.</li>
                    <li>Fly directly from Ho Chi Minh City to Phu Yen for convenience.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Ho Chi Minh City → Phu Yen</h3>
                    <p>Take a direct flight from Ho Chi Minh City to Phu Yen. Upon arrival, transfer to your resort for check-in and leisure time by the sea.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Mui Dien Sunrise – Bai Mon Beach</h3>
                    <p>Wake up early for a scenic hike to Mui Dien Lighthouse and admire the sunrise. Spend the rest of the morning enjoying Bai Mon Beach nearby.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Vung Ro Bay – Seafood Experience</h3>
                    <p>Enjoy a boat ride through Vung Ro Bay, a site of natural beauty and historical significance. Finish with a fresh seafood tasting session.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Free Time – Return to HCMC</h3>
                    <p>Relax at the resort or visit a local market before returning to Ho Chi Minh City by plane.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">7,790,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">9,273,810 VND</p>
                    <a href="/tour/booking?cityid=14&tourid=18" class="booking-button">Booking now!</a>
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
