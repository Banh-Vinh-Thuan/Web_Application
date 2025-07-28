<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Northwest in Rice Harvest Season - Nghia Lo - Mu Cang Chai - Sapa - Fansipan - Lai Chau - Dien Bien - Moc Chau</title>
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>Nghia Lo - Mu Cang Chai - Sapa - Fansipan - Lai Chau - Dien Bien - Moc Chau</h1>

    <!-- Gallery -->
    <div class="gallery" id="lightgallery">
        <a href="taybac/1.jpg" class="big"><img src="taybac/1.jpg" alt="O Quy Ho Hill" loading="lazy"></a>
        <a href="taybac/2.jpg" class="small1"><img src="taybac/2.jpg" alt="O Quy Ho Hill" loading="lazy"></a>
        <a href="taybac/3.jpg" class="small2"><img src="taybac/3.jpg" alt="Pu Sam Cap Cave" loading="lazy"></a>
        <a href="taybac/4.jpg" class="small3"><img src="taybac/4.jpg" alt="Dien Bien City" loading="lazy"></a>
        <a href="taybac/5.jpg" class="small4"><img src="taybac/5.jpg" alt="Dien Bien Phu Victory Statue" loading="lazy"></a>
    </div>

    <!-- Content Columns -->
    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Marvel at golden terraced rice fields during the stunning harvest season in Northern Vietnam.</li>
                    <li>Visit remote highland regions including Nghia Lo, Mu Cang Chai, and Lai Chau for authentic local experiences.</li>
                    <li>Conquer the peak of Fansipan – the "Roof of Indochina" – by cable car or trekking.</li>
                    <li>Discover the cultural richness of ethnic minorities such as H’mong, Thai, and Dao.</li>
                    <li>Relax in the cool climate and natural beauty of Moc Chau Plateau and Dien Bien countryside.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>

                <div class="box2">
                    <h3>Day 1: Hanoi – Nghia Lo</h3>
                    <p>Depart from Hanoi and travel to Nghia Lo. Enjoy the peaceful mountain scenery and explore ethnic Thai villages.</p>
                </div>

                <div class="box2">
                    <h3>Day 2: Nghia Lo – Mu Cang Chai</h3>
                    <p>Journey through scenic mountain passes to Mu Cang Chai, famous for its breathtaking terraced rice fields in harvest season.</p>
                </div>

                <div class="box2">
                    <h3>Day 3: Mu Cang Chai – Sapa</h3>
                    <p>Head to Sapa, explore local markets and enjoy the cool mountain air. Visit Cat Cat Village or relax in town.</p>
                </div>

                <div class="box2">
                    <h3>Day 4: Fansipan – Lai Chau</h3>
                    <p>Take a cable car to Fansipan Peak, then continue on a scenic drive to Lai Chau, with stops at waterfalls and hill tribe villages.</p>
                </div>

                <div class="box2">
                    <h3>Day 5: Lai Chau – Dien Bien – Moc Chau</h3>
                    <p>Explore the historical sites in Dien Bien before heading to the lush green plateaus of Moc Chau.</p>
                </div>

                <div class="box2">
                    <h3>Day 6: Moc Chau – Hanoi</h3>
                    <p>Visit tea plantations and flower valleys in Moc Chau, then return to Hanoi, ending your memorable Northern adventure.</p>
                </div>
            </div>
        </div>

        <aside class="right-column">
            <div class="box3">
                <div class="button">
                    <h3>Price From</h3>
                    <p style="color: red; font-weight: bold;">12,179,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">12,779,000 VND</p>
                    <a href="/tour/booking?cityid=10&tourid=1" class="booking-button">Booking now!</a>
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
