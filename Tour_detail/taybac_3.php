<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sapa - Fansipan - Y Ty - Bat Xat Rice Terraces Tour</title>
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>&nbsp;&nbsp;&nbsp; Sapa - Fansipan - Y Tý - Bát Xát Rice Terraces</h1>

    <!-- Gallery -->
    <div class="gallery" id="lightgallery">
        <a href="Taybac3/1.jpg" class="big"><img src="Taybac3/1.jpg" alt="Best View Cafe"></a>
        <a href="Taybac3/2.jpg" class="small1"><img src="Taybac3/2.jpg" alt="Phansipang Cable Car"></a>
        <a href="Taybac3/3.jpg" class="small2"><img src="Taybac3/3.jpg" alt="Hoang Su Phi Rice Field"></a>
        <a href="Taybac3/4.jpg" class="small3"><img src="Taybac3/4.jpg" alt="Bat Xat Rice Field"></a>
        <a href="Taybac3/5.jpg" class="small4"><img src="Taybac3/5.jpg" alt="Sewing Tradition"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Admire the magnificent terraced fields of Y Ty and Bat Xat, one of the most beautiful highland regions in Vietnam.</li>
                    <li>Conquer the Roof of Indochina – Fansipan – by cable car with stunning panoramic views.</li>
                    <li>Immerse yourself in the unique culture and lifestyle of the H'Mong and Ha Nhi ethnic groups.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Hanoi – Sa Pa – Fansipan</h3>
                    <p>Depart from Hanoi, arrive in Sa Pa. Take the Fansipan cable car and enjoy the incredible views from Vietnam’s highest peak.</p>
                </div>

                <div class="box2">
                    <h3>Day 2: Sa Pa – Y Ty – Bat Xat</h3>
                    <p>Journey to Y Ty, a remote highland commune with amazing rice terraces. Continue to Bat Xat for scenic photo stops and cultural experiences.</p>
                </div>

                <div class="box2">
                    <h3>Day 3: Bat Xat – Lao Cai – Hanoi</h3>
                    <p>Relax and explore local markets, then return to Hanoi with unforgettable memories of the Northwest region.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">9,405,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">9,900,000 VND</p>
                    <a href="/tour/booking?cityid=10&tourid=3" class="booking-button">Booking now!</a>
                </div>
            </div>
            <div class="box">
                <h3>Contact Support</h3>
                <p>📞 Hotline: 1919 2025<br>✉️ Email: viettransit.support@mail.com</p>
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
        </div>
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
