<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phu Yen - Tuy Hoa - Ganh Da Dia - Vung Ro Bay - Mang Lang Church Tour</title>
    <meta name="description" content="Discover Phu Yen's coastal charm, Ganh Da Dia’s rare rock formations, and Vung Ro Bay's historic waters with a scenic train ride from Da Nang.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Phu Yen - Tuy Hoa - Ganh Da Dia - Vung Ro Bay - Mang Lang Church</h1>

    <div class="gallery" id="lightgallery">
        <a href="Phuyen4/1.jpg" class="big"><img src="Phuyen4/1.jpg" alt="Dai Lanh Lighthouse in Phu Yen" loading="lazy"></a>
        <a href="Phuyen4/2.jpg" class="small1"><img src="Phuyen4/2.jpg" alt="Nghinh Phong Tower" loading="lazy"></a>
        <a href="Phuyen4/3.jpg" class="small2"><img src="Phuyen4/3.jpg" alt="Crab Noodles - Local Cuisine" loading="lazy"></a>
        <a href="Phuyen4/4.jpg" class="small3"><img src="Phuyen4/4.jpg" alt="Bánh Bột Lọc Rice Dish" loading="lazy"></a>
        <a href="Phuyen4/5.jpg" class="small4"><img src="Phuyen4/5.jpg" alt="Mang Lang Church Phu Yen" loading="lazy"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Discover the peaceful beauty of Phu Yen and its coastal capital Tuy Hoa.</li>
                    <li>Visit Ganh Da Dia – a rare volcanic rock formation and a geological wonder.</li>
                    <li>Take a boat ride through Vung Ro Bay, rich in wartime stories and marine scenery.</li>
                    <li>Explore Mang Lang Church – one of Vietnam's oldest Catholic churches.</li>
                    <li>Experience a scenic train journey from Da Nang to the southern coast.</li>
                    <li>All logistics arranged for a stress-free, comfortable adventure.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>

                <article class="box2">
                    <h3>Day 1: Da Nang → Phu Yen by Train</h3>
                    <p>Enjoy a picturesque train ride hugging the coast. Arrive in Tuy Hoa, check in, and unwind with local food.</p>
                </article>

                <article class="box2">
                    <h3>Day 2: Ganh Da Dia – Mang Lang Church</h3>
                    <p>Morning trip to Ganh Da Dia’s unique rock structures, followed by an afternoon visit to historic Mang Lang Church.</p>
                </article>

                <article class="box2">
                    <h3>Day 3: Vung Ro Bay Exploration</h3>
                    <p>Boat tour of Vung Ro Bay — hear stories of wartime logistics and enjoy fresh air and sea views.</p>
                </article>

                <article class="box2">
                    <h3>Day 4: Tuy Hoa City Tour</h3>
                    <p>Relax or explore the city. Optional street food tour in the evening to discover local flavors.</p>
                </article>

                <article class="box2">
                    <h3>Day 5: Return to Da Nang</h3>
                    <p>Check out, board the train back to Da Nang with scenic stops along the way. End of tour.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,690,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,779,070 VND</p>
                    <a href="/tour/booking?cityid=14&tourid=20" class="booking-button">Booking now!</a>
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
