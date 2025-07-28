<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ha Giang Nature & Culture: Dong Van Karst Plateau - Lung Cam Cultural Village - Ma Pi Leng Panorama - Local Craft Workshops</title>
    <meta name="description" content="Embark on a cultural journey to Ha Giang with visits to the Dong Van Karst Plateau, Lung Cam Village, Ma Pi Leng Panorama, and traditional craft workshops.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Ha Giang Nature & Culture: Dong Van Karst Plateau - Lung Cam Cultural Village - Ma Pi Leng Panorama - Local Craft Workshops</h1>

    <div class="gallery" id="lightgallery">
        <a href="hagiang4/1.jpg" class="big">
            <img src="hagiang4/1.jpg" alt="Dong Van District" loading="lazy" width="800" height="600">
        </a>
        <a href="hagiang4/2.jpg" class="small1">
            <img src="hagiang4/2.jpg" alt="Tam Giac Mach Village" loading="lazy" width="400" height="300">
        </a>
        <a href="hagiang4/3.jpg" class="small2">
            <img src="hagiang4/3.jpg" alt="Lung Cam Village" loading="lazy" width="400" height="300">
        </a>
        <a href="hagiang4/4.jpg" class="small3">
            <img src="hagiang4/4.jpg" alt="Ma Pi Leng Panorama" loading="lazy" width="400" height="300">
        </a>
        <a href="hagiang4/5.jpg" class="small4">
            <img src="hagiang4/5.jpg" alt="Ethnic Craft Workshop" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Explore the stunning Dong Van Karst Plateau, a UNESCO Global Geopark with breathtaking limestone landscapes.</li>
                    <li>Visit Lung Cam Cultural Village to experience traditional H’Mong houses and vibrant ethnic life.</li>
                    <li>Admire panoramic views from Ma Pi Leng Pass, one of Vietnam’s most iconic highland roads.</li>
                    <li>Join interactive local craft workshops featuring traditional weaving, embroidery, and handmade art.</li>
                    <li>Comfortable and enriching travel experience with organized transport from Ho Chi Minh City.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Ho Chi Minh City – Ha Giang</h3>
                    <p>Depart from Ho Chi Minh City and travel to Ha Giang. Upon arrival, check in to your hotel or homestay and rest for the upcoming adventure.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Dong Van Karst Plateau – Lung Cam Village</h3>
                    <p>Discover the rugged beauty of Dong Van Karst Plateau and enjoy cultural exploration at Lung Cam Village, known for its historic homes and ethnic diversity.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Ma Pi Leng Panorama – Local Craft Workshops</h3>
                    <p>Marvel at the Ma Pi Leng mountain pass, followed by engaging in hands-on ethnic crafts guided by local artisans.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Leisure Time – Return to Ho Chi Minh City</h3>
                    <p>Spend your last morning in Ha Giang at leisure—shop, sip coffee, or relax before heading back to Ho Chi Minh City by bus.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">7,150,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">8,400,000 VND</p>
                    <a href="/tour/booking?cityid=18&tourid=36" class="booking-button">Booking now!</a> 
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
