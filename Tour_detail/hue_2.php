<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>North-Central Vietnam: Hanoi - Sapa - Ha Long - Ninh Binh - Da Nang - Hoi An - Hue - Phong Nha</title>
    <meta name="description" content="Embark on a 14-day journey across Vietnam, exploring Hanoi, Sapa, Ha Long Bay, Ninh Binh, Da Nang, Hoi An, Hue, and Phong Nha.">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>

<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>North - Central Vietnam Tour: Hanoi - Sapa - Fansipan - Ha Long - Ninh Binh - Da Nang - Hoi An - Hue - Phong Nha</h1>

    <div class="gallery" id="lightgallery">
        <a href="hue2/1.jpg" class="big">
            <img src="hue2/1.jpg" alt="Ba Na Hills" loading="lazy" width="800" height="600">
        </a>
        <a href="hue2/2.jpg" class="small1">
            <img src="hue2/2.jpg" alt="Ha Long Bay View" loading="lazy" width="400" height="300">
        </a>
        <a href="hue2/3.jpg" class="small2">
            <img src="hue2/3.jpg" alt="Cruise in Ha Long Bay" loading="lazy" width="400" height="300">
        </a>
        <a href="hue2/4.jpg" class="small3">
            <img src="hue2/4.jpg" alt="Ba Na Hills Garden" loading="lazy" width="400" height="300">
        </a>
        <a href="hue2/5.jpg" class="small4">
            <img src="hue2/5.jpg" alt="Golden Bridge in Da Nang" loading="lazy" width="400" height="300">
        </a>
    </div>

    <div class="content-columns">
        <section class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Comprehensive discovery of Northern and Central Vietnam's top destinations.</li>
                    <li>Adventure to Fansipan – the rooftop of Indochina via cable car or trek.</li>
                    <li>Enjoy the breathtaking beauty of Ha Long Bay and serenity of Ninh Binh.</li>
                    <li>Experience heritage cities like Hue and Hoi An, plus modern Da Nang and iconic Golden Bridge.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>

                <article class="box2">
                    <h3>Day 1-3: Hanoi - Sapa - Fansipan</h3>
                    <p>Start your journey in Hanoi, then head to the misty mountains of Sapa to conquer Fansipan Peak via cable car or trekking.</p>
                </article>

                <article class="box2">
                    <h3>Day 4-6: Ha Long Bay - Ninh Binh</h3>
                    <p>Explore the UNESCO-listed Ha Long Bay with a boat cruise, then visit the peaceful Tam Coc and ancient Hoa Lu in Ninh Binh.</p>
                </article>

                <article class="box2">
                    <h3>Day 7-8: Da Nang - Ba Na Hills - Hoi An</h3>
                    <p>Fly to Da Nang, visit Son Tra Peninsula, Ba Na Hills and walk the Golden Bridge. Explore the lantern-lit town of Hoi An by night.</p>
                </article>

                <article class="box2">
                    <h3>Day 9-10: La Vang - Hue - Phong Nha</h3>
                    <p>Stop at La Vang Church, tour Hue’s historic citadel, then head to Phong Nha to explore the spectacular cave systems.</p>
                </article>
            </div>
        </section>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">23,590,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">26,211,111 VND</p>
                    <a href="/tour/booking?cityid=13&tourid=14" class="booking-button">Booking now!</a>
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
