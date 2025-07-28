<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hung Temple - Nghia Lo - Tu Le - Mu Cang Chai - Sapa - Fansipan - Dien Bien - Moc Chau Tour</title>
    <link rel="stylesheet" href="../css/tour.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.0/css/lightgallery.css">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<main>
    <h1>&nbsp;&nbsp;&nbsp;  Hung Temple - Nghia Lo - Tu Le - Mu Cang Chai - Sapa - Fansipan - Dien Bien - Moc Chau Tour </h1>
     <div class="gallery" id="lightgallery">
        <a href="taybac2/1.jpg" class="big"><img src="taybac2/1.jpg" alt="Hung Temple"></a>
        <a href="taybac2/2.jpg" class="small1"><img src="taybac2/2.jpg" alt="O Quy Ho Hill"></a>
        <a href="taybac2/3.jpg" class="small2"><img src="taybac2/3.jpg" alt="Phansipang Mountain"></a>
        <a href="taybac2/4.jpg" class="small3"><img src="taybac2/4.jpg" alt="Dien Bien Phu Victory Statue"></a>
        <a href="taybac2/5.jpg" class="small4"><img src="taybac2/5.jpg" alt="Moc Chau Flower Field"></a>
    </div>

    <div class="content-columns">
        <div class="left-column">
            <div class="box">
                <h2>Why This Tour Is Attractive</h2>
                <ul>
                    <li>Visit the sacred Hung Temple, an important spiritual and cultural site in Vietnamese history.</li>
                    <li>Admire the golden rice terraces in Tu Le and Mu Cang Chai during the harvest season.</li>
                    <li>Conquer Fansipan – the Roof of Indochina – with a spectacular cable car ride.</li>
                    <li>Explore Sapa’s unique ethnic culture and highland charm.</li>
                    <li>Enjoy a scenic and insightful journey through Dien Bien and the peaceful Moc Chau Plateau.</li>
                    <li>Small group tour with convenient departures from Ho Chi Minh City.</li>
                </ul>
            </div>

            <div class="box">
                <h2>Itinerary</h2>
                <div class="box2">
                    <h3>Day 1: Ho Chi Minh City – Hanoi – Hung Temple – Nghia Lo</h3>
                    <p>Fly to Hanoi and travel to the historic Hung Temple. Continue to Nghia Lo to experience the charm of Thai ethnic culture.</p>
                </div>

                <div class="box2">
                    <h3>Day 2: Nghia Lo – Tu Le – Mu Cang Chai – Sapa</h3>
                    <p>Enjoy the stunning views of terraced fields in Tu Le and Mu Cang Chai, then head to Sapa for an overnight stay.</p>
                </div>

                <div class="box2">
                    <h3>Day 3: Fansipan – Dien Bien</h3>
                    <p>Ride the cable car to the peak of Fansipan Mountain. Afterward, journey through breathtaking mountain roads to reach Dien Bien.</p>
                </div>

                <div class="box2">
                    <h3>Day 4: Dien Bien – Moc Chau – Hanoi – Ho Chi Minh City</h3>
                    <p>Explore Moc Chau’s tea hills and green landscapes before flying back to Ho Chi Minh City from Hanoi.</p>
                </div>
            </div>
        </div>

        <div class="right-column">
            <div class="box3">
                <div class="button">
                    <h3 style="display: inline;">Price From</h3>
                    <p style="color: red; font-weight: bold; display: inline;">13,590,000 VND</p>
                    <p style="text-decoration: line-through; color: gray;">14,590,000 VND</p>
                    <a href="/tour/booking?cityid=10&tourid=2" class="booking-button">Booking now!</a>
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