<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Da Nang - Phong Nha - La Vang - Hue - Ba Na Hills - Hoi An</title>
    <meta name="description" content="Explore Central Vietnam’s gems including Phong Nha Cave, La Vang, Hue, Ba Na Hills, and Hoi An in this 5-day cultural and natural journey.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>

<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Da Nang - Phong Nha Cave - La Vang - Hue - Ba Na Hills - Hoi An</h1>

    <div class="gallery" id="lightgallery">
        <a href="hue1/1.jpg" class="big">
            <img src="hue1/1.jpg" alt="Dragon Bridge, Da Nang" loading="lazy" width="800" height="600">
        </a>
        <a href="hue1/2.jpg" class="small1">
            <img src="hue1/2.jpg" alt="Phong Nha Cave" loading="lazy" width="400" height="300">
        </a>
        <a href="hue1/3.jpg" class="small2">
            <img src="hue1/3.jpg" alt="La Vang Pilgrimage Center" loading="lazy" width="400" height="300">
        </a>
        <a href="hue1/4.jpg" class="small3">
            <img src="hue1/4.jpg" alt="Golden Bridge - Ba Na Hills" loading="lazy" width="400" height="300">
        </a>
        <a href="hue1/5.jpg" class="small4">
            <img src="hue1/5.jpg" alt="Japanese Covered Bridge, Hoi An" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <section class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Marvel at the magnificent Phong Nha Cave with underground rivers and rock formations.</li>
                    <li>Visit the sacred La Vang Church, a historic Catholic pilgrimage site.</li>
                    <li>Explore the Imperial City of Hue and its cultural heritage.</li>
                    <li>Ascend Ba Na Hills and stroll along the world-famous Golden Bridge.</li>
                    <li>Wander through lantern-lit streets and ancient architecture in Hoi An.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>

                <article class="box2">
                    <h3>Day 1: Arrival in Da Nang</h3>
                    <p>Arrive in Da Nang, check into your hotel, and enjoy free time to explore the city’s vibrant nightlife or local cuisine.</p>
                </article>

                <article class="box2">
                    <h3>Day 2: Phong Nha Cave & La Vang</h3>
                    <p>Embark on a day trip to explore the UNESCO-listed Phong Nha Cave, then visit the historic La Vang Pilgrimage Center in Quang Tri.</p>
                </article>

                <article class="box2">
                    <h3>Day 3: Discover Hue</h3>
                    <p>Spend the day in Hue, visiting the Imperial City, Thien Mu Pagoda, and enjoying traditional dishes such as Bun Bo Hue.</p>
                </article>

                <article class="box2">
                    <h3>Day 4: Ba Na Hills & Golden Bridge</h3>
                    <p>Take the cable car to Ba Na Hills, experience the famous Golden Bridge, and enjoy various attractions in the French Village.</p>
                </article>

                <article class="box2">
                    <h3>Day 5: Hoi An Ancient Town</h3>
                    <p>Wrap up the journey in Hoi An, walking through its lantern-lit streets, visiting temples, and shopping for local handicrafts before returning to Da Nang.</p>
                </article>
            </div>
        </section>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">6,990,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,943,182 VND</p>
                    <a href="/tour/booking?cityid=13&tourid=13" class="booking-button">Booking now!</a>
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
