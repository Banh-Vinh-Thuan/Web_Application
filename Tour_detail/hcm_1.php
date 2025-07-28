<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Tour - 4-Hour Double-Decker Bus Saigon - Cholon & Culinary Experience</title>
    <meta name="description" content="Explore Ho Chi Minh City, Cu Chi Tunnels, and the Mekong Delta with our 4-day tour. Discover culture, history, and local cuisine in a comfortable travel experience.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Discover Ho Chi Minh - Cu Chi Tunnels - Mekong Delta</h1>
    
    <div class="gallery" id="lightgallery">
        <a href="hcm1/1.jpg" class="big">
            <img src="hcm1/1.jpg" alt="Sai Gon Post Office" loading="lazy" width="800" height="600">
        </a>
        <a href="hcm1/2.jpg" class="small1">
            <img src="hcm1/2.jpg" alt="Cathedral Church" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm1/3.jpg" class="small2">
            <img src="hcm1/3.jpg" alt="Ben Thanh Market" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm1/4.jpg" class="small3">
            <img src="hcm1/4.jpg" alt="Cu Chi Tunnel" loading="lazy" width="400" height="300">
        </a>
        <a href="hcm1/5.jpg" class="small4">
            <img src="hcm1/5.jpg" alt="Mekong River" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Dive into Vietnam’s history at the Cu Chi Tunnels</li>
                    <li>Experience the vibrant local life of the Mekong Delta</li>
                    <li>Explore iconic landmarks of Ho Chi Minh City</li>
                    <li>Enjoy comfortable travel and hotel accommodations</li>
                    <li>Perfect for small groups with daily departures</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Ho Chi Minh City Highlights</h3>
                    <p>Discover the city's colonial charm with visits to Notre-Dame Cathedral, the Central Post Office, and local markets.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Cu Chi Tunnels</h3>
                    <p>Travel to the Cu Chi Tunnels, explore underground passageways, and learn about wartime resilience.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Mekong Delta Exploration</h3>
                    <p>Cruise along the Mekong River, visit floating markets and local workshops, and enjoy traditional Mekong cuisine.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Free Time & Departure</h3>
                    <p>Relax or explore the city at your own pace before returning home.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">4,590,000 ₫</p>
                    <p style="text-decoration: line-through; color: gray;">4,840,000 ₫</p>
                    <a href="/tour/booking?cityid=11&tourid=5" class="booking-button">Booking now!</a>
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
