<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Museum and Contemporary Art Tour in Ho Chi Minh</title>
    <meta name="description" content="Explore Vietnam's vibrant culture through museums and art galleries in this 2-day Ho Chi Minh City tour. Includes expert guides, transport, and immersive experiences.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Museum and Contemporary Art Tour in Ho Chi Minh</h1>

    <div class="gallery" id="lightgallery">
        <a href="hcm3/1.jpg" class="big">
            <img src="hcm3/1.jpg" alt="Museum Overview" loading="lazy" width="800" height="600">
        </a>
        <a href="hcm3/2.jpg" class="small1">
            <img src="hcm3/2.jpg" alt="Nguyen Hue Street" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm3/3.jpg" class="small2">
            <img src="hcm3/3.jpg" alt="Modern Art Space" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm3/4.jpg" class="small3">
            <img src="hcm3/4.jpg" alt="Ben Thanh Market" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm3/5.jpg" class="small4">
            <img src="hcm3/5.jpg" alt="Norodom Architecture" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Immerse yourself in Vietnam's art and culture scene.</li>
                    <li>Visit top museums and contemporary art galleries.</li>
                    <li>Expert guides offer in-depth cultural insights.</li>
                    <li>Comfortable bus transport and hotel accommodation.</li>
                    <li>Ideal for small groups on weekday getaways.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Historical & Fine Arts Museums</h3>
                    <p>Begin the tour with visits to the Ho Chi Minh City Museum and the Fine Arts Museum, learning about Vietnam's history and classical art.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Contemporary Art Spaces</h3>
                    <p>Explore dynamic modern art galleries and creative spaces like The Factory Contemporary Arts Centre, and engage with local artists.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">980,000 ₫</p>
                    <p style="text-decoration: line-through; color: gray;">1,019,000 ₫</p>
                    <a href="/tour/booking?cityid=11&tourid=7" class="booking-button">Booking now!</a>
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
