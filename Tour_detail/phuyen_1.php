<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phu Yen Hidden Gems: Da Dia Reef - Bai Xep - Mang Lang Church - Nhan Tower</title>
    <meta name="description" content="Explore the hidden gems of Phu Yen with this 4-day tour featuring Da Dia Reef, Bai Xep, Mang Lang Church, and Nhan Tower.">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Phu Yen Hidden Gems: Da Dia Reef - Bai Xep - Mang Lang Church - Nhan Tower</h1>

    <div class="gallery" id="lightgallery">
        <a href="Phuyen1/1.jpg" class="big"><img src="Phuyen1/1.jpg" alt="Da Dia Reef, Phu Yen" loading="lazy"></a>
        <a href="Phuyen1/2.jpg" class="small1"><img src="Phuyen1/2.jpg" alt="Bai Xep Beach Village" loading="lazy"></a>
        <a href="Phuyen1/3.jpg" class="small2"><img src="Phuyen1/3.jpg" alt="Mang Lang Church" loading="lazy"></a>
        <a href="Phuyen1/4.jpg" class="small3"><img src="Phuyen1/4.jpg" alt="Nhan Tower - Cham Architecture" loading="lazy"></a>
        <a href="Phuyen1/5.jpg" class="small4"><img src="Phuyen1/5.jpg" alt="Phu Yen Coastal Views" loading="lazy"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <section class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Marvel at the rare hexagonal rock formations of Da Dia Reef.</li>
                    <li>Unwind at the tranquil Bai Xep beach with golden sands.</li>
                    <li>Visit the 19th-century Mang Lang Church — a historic Catholic landmark.</li>
                    <li>Discover Nhan Tower, a Cham relic offering sweeping views of Tuy Hoa city.</li>
                    <li>Experience a scenic coastal train ride from Nha Trang to Phu Yen.</li>
                </ul>
            </section>

            <section class="box">
                <h2>Itinerary</h2>
                <article class="box2">
                    <h3>Day 1: Nha Trang – Phu Yen by Train</h3>
                    <p>Board the train from Nha Trang and enjoy a relaxing coastal journey to Phu Yen. Upon arrival, check in and explore the nearby beach area.</p>
                </article>
                <article class="box2">
                    <h3>Day 2: Da Dia Reef – Mang Lang Church</h3>
                    <p>Visit the awe-inspiring Da Dia Reef with its unique basalt columns, then explore Mang Lang Church, one of Vietnam’s oldest Catholic churches.</p>
                </article>
                <article class="box2">
                    <h3>Day 3: Bai Xep – Nhan Tower</h3>
                    <p>Spend the morning at Bai Xep beach, followed by a visit to the Cham-style Nhan Tower overlooking Tuy Hoa.</p>
                </article>
                <article class="box2">
                    <h3>Day 4: Free Time – Return to Nha Trang</h3>
                    <p>Enjoy some leisure time or shopping before catching the train back to Nha Trang, concluding your memorable trip in Phu Yen.</p>
                </article>
            </section>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">5,990,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">7,304,878 VND</p>
                    <a href="/tour/booking?cityid=14&tourid=17" class="booking-button">Booking now!</a>
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
