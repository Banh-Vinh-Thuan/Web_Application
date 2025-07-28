<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mekong Delta Tour Departing from Ho Chi Minh City</title>
    <meta name="description" content="Discover the peaceful charm of the Mekong Delta on this full-day tour from Ho Chi Minh City. Includes boat rides, village visits, local cuisine, and more.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Mekong Delta Tour Departing from Ho Chi Minh City</h1>

    <div class="gallery" id="lightgallery">
        <a href="hcm4/1.jpg" class="big">
            <img src="hcm4/1.jpg" alt="Long Xuyen Floating Market" loading="lazy" width="800" height="600">
        </a>
        <a href="hcm4/2.jpg" class="small1">
            <img src="hcm4/2.jpg" alt="Tra Su Forest 1" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm4/3.jpg" class="small2">
            <img src="hcm4/3.jpg" alt="Tra Su Forest 2" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm4/4.jpg" class="small3">
            <img src="hcm4/4.jpg" alt="Bún Mắm - Mekong Special Dish" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm4/5.jpg" class="small4">
            <img src="hcm4/5.jpg" alt="Clay Pot Rice" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Escape the city and enjoy the peaceful life of the Mekong Delta.</li>
                    <li>Scenic boat rides along winding rivers and lush canals.</li>
                    <li>Visit traditional villages, workshops, and tropical orchards.</li>
                    <li>Delicious local cuisine and authentic cultural experiences.</li>
                    <li>Daily departures with convenient transport by bus and boat.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Morning: Departure from Ho Chi Minh City</h3>
                    <p>Depart by bus to the Mekong Delta, passing through peaceful rural landscapes.</p>
                </article>
                <article class="box2">
                    <h3>Midday: Boat Ride & Local Experience</h3>
                    <p>Enjoy a boat ride through lush canals, visit a local village and traditional workshops, and taste seasonal tropical fruits.</p>
                </article>
                <article class="box2">
                    <h3>Afternoon: Lunch & Cultural Activities</h3>
                    <p>Savor a local lunch with regional dishes, followed by cultural performances or optional countryside cycling.</p>
                </article>
                <article class="box2">
                    <h3>Evening: Return to Ho Chi Minh City</h3>
                    <p>Relax on the journey back to Ho Chi Minh City, arriving in the early evening.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">1,050,000 ₫</p>
                    <p style="text-decoration: line-through; color: gray;">1,100,000 ₫</p>
                    <a href="/tour/booking?cityid=11&tourid=8" class="booking-button">Booking now!</a>
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
