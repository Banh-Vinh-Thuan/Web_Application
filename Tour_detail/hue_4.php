<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hanoi to Hue Cultural Train Journey – Imperial City, Khai Dinh Tomb, Dong Ba Market & Perfume River</title>
    <meta name="description" content="Experience a scenic train journey from Hanoi to Hue, explore historical sites like the Imperial City and Khai Dinh Tomb, and cruise the Perfume River.">
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>

<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Hanoi to Hue Cultural Train Journey – Imperial City, Khai Dinh Tomb, Dong Ba Market & Perfume River</h1>

    <div class="gallery" id="lightgallery">
        <a href="Hue4/1.jpg" class="big"><img src="Hue4/1.jpg" alt="Hai Van Hills" loading="lazy" width="800" height="600"></a>
        <a href="Hue4/2.jpg" class="small1"><img src="Hue4/2.jpg" alt="Imperial City Hue" loading="lazy" width="400" height="300"></a>
        <a href="Hue4/3.jpg" class="small2"><img src="Hue4/3.jpg" alt="Khai Dinh Tomb" loading="lazy" width="400" height="300"></a>
        <a href="Hue4/4.jpg" class="small3"><img src="Hue4/4.jpg" alt="Dong Ba Market" loading="lazy" width="400" height="300"></a>
        <a href="Hue4/5.jpg" class="small4"><img src="Hue4/5.jpg" alt="Cruise on Perfume River" loading="lazy" width="400" height="300"></a>
    </div>

    <div class="content-columns">
        <section class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Scenic overnight train journey from Hanoi to Hue.</li>
                    <li>Visit the majestic Imperial City – a UNESCO Heritage Site.</li>
                    <li>Discover the elaborate Khai Dinh Tomb.</li>
                    <li>Shop like a local at Dong Ba Market.</li>
                    <li>Relax with a serene Perfume River cruise.</li>
                    <li>Enjoy comfortable 3-star accommodation in central Hue.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>

                <article class="box2">
                    <h3>Day 1: Hanoi – Overnight Train to Hue</h3>
                    <p>Depart in the evening aboard a sleeper train. Enjoy views of the countryside on your journey south.</p>
                </article>

                <article class="box2">
                    <h3>Day 2: Arrival in Hue – Imperial City Exploration</h3>
                    <p>Check into your hotel and explore the Imperial City, home to Vietnam’s Nguyen Dynasty.</p>
                </article>

                <article class="box2">
                    <h3>Day 3: Khai Dinh Tomb – Dong Ba Market – Perfume River Cruise</h3>
                    <p>Visit the stunning Khai Dinh Tomb, shop for souvenirs and street snacks at Dong Ba Market, then unwind on a boat along the Perfume River.</p>
                </article>

                <article class="box2">
                    <h3>Day 4: Free Time – Return Journey or Extension</h3>
                    <p>Enjoy a relaxed morning before your return train to Hanoi or opt for a travel extension.</p>
                </article>
            </div>
        </section>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">5,290,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">6,080,460 VND</p>
                    <a href="/tour/booking?cityid=13&tourid=16" class="booking-button">Booking now!</a>
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
