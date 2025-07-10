<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VietTransit</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../header.php'; ?>

<section class="home" id="home">
    <div class="video-container">
        <video id="bgVideo" autoplay muted loop playsinline>
            <source id="videoSource" src="" type="video/mp4">
        </video>
    </div>
    <div class="content">
        <span><strong>Incredible Vietnam:</strong></span>
        <p>Where Every Place is a Story, Every Journey an Adventure.</p>
        <a href="#products" class="btn">Travel Now</a>
    </div>
</section>

<script>
function changeVideoBackground() {
    const hour = new Date().getHours();
    const videoSrc = (hour >= 6 && hour < 18) ? "/images/daytime.mp4" : "/images/nighttime.mp4";
    document.getElementById("videoSource").src = videoSrc;
    document.getElementById("bgVideo").load();
}
changeVideoBackground();
</script>

<section class="about" id="about">
    <h1 class="heading"><span>about</span> us</h1>
    <div class="row">
        <div class="video-container">
            <video src="../images/about-vid.mp4" loop autoplay muted></video>
            <h3>Best Places</h3>
        </div>
        <div class="content">
            <h3>why choose us?</h3>
            <p>What truly sets us apart is our deep understanding of travel within Vietnam and our unwavering dedication to your satisfaction. Whether you're planning a peaceful retreat to Da Lat, a beach escape in Phu Quoc, or a cultural adventure in Hanoi and Hue, our 24/7 local support team is always here to help. We offer competitive prices and exclusive deals on top Vietnamese destinations, making your journey affordable and unforgettable. When you choose our travel website, you're not just booking a trip — you're joining a community that values authentic experiences and hassle-free service. Let us take care of the details, so you can focus on discovering the beauty of Vietnam.</p>
            <a href="#review" class="btn">learn more</a>
        </div>
    </div>
</section>

<section class="icons-container">
    <div class="icons">
        <img src="../images/icon-1.png" alt="">
        <div class="info">
            <h3>free booking</h3>
            <span>on all orders</span>
        </div>
    </div>
    <div class="icons">
        <img src="../images/icon-2.png" alt="">
        <div class="info">
            <h3>10 days returns</h3>
            <span>moneyback guarantee</span>
        </div>
    </div>
    <div class="icons">
        <img src="../images/icon-3.png" alt="">
        <div class="info">
            <h3>offer & gifts</h3>
            <span>on all orders</span>
        </div>
    </div>
    <div class="icons">
        <img src="../images/icon-4.png" alt="">
        <div class="info">
            <h3>secure paymens</h3>
            <span>protected by Razorpay</span>
        </div>
    </div>
</section>

<section class="products" id="products">
    <h1 class="heading">Popular <span>Places</span></h1>
    <div class="box-container">
        <?php
        $destinations = [
            ['id' => 10, 'name' => 'Tay Bac', 'image' => 'taybac.jpg', 'url' => 'taybac', 'desc' => 'The land of mountains and cultural identity'],
            ['id' => 11, 'name' => 'Ho Chi Minh', 'image' => 'hcm.jpg', 'url' => 'hcm', 'desc' => 'The city that never sleeps'],
            ['id' => 12, 'name' => 'Nha Trang', 'image' => 'nhatrang.jpg', 'url' => 'nhatrang', 'desc' => 'The blue gem of Central Vietnam'],
            ['id' => 13, 'name' => 'Hue', 'image' => 'hue.jpg', 'url' => 'hue', 'desc' => 'Where nostalgia lives in every corner'],
            ['id' => 14, 'name' => 'Phu Yen', 'image' => 'phuyen.jpg', 'url' => 'phuyen', 'desc' => 'The land of unspoiled beauty'],
            ['id' => 15, 'name' => 'Da Lat', 'image' => 'dalat.jpg', 'url' => 'dalat', 'desc' => 'Where flowers bloom all year round'],
            ['id' => 16, 'name' => 'Phu Quoc', 'image' => 'phuquoc.jpg', 'url' => 'phuquoc', 'desc' => 'Pristine beauty between sky and sea'],
            ['id' => 17, 'name' => 'Hoi An', 'image' => 'hoian.jpg', 'url' => 'hoian', 'desc' => 'Where the past and present walk together'],
            ['id' => 18, 'name' => 'Ha Giang', 'image' => 'hagiang.jpg', 'url' => 'hagiang', 'desc' => 'Where misty mountains rise between earth and sky']
        ];
        
        foreach ($destinations as $dest): ?>
        <div class="box">
            <div class="image">
                <img src="../images/<?= $dest['image'] ?>" alt="">
                <div class="icons">
                    <a href="add_favorite.php?cityid=<?= $dest['id'] ?>" class="favorite-btn"><i class="fas fa-heart"></i></a>
                    <a href="../Journey/viewjourney.php?id=<?= $dest['url'] ?>" class="cart-btn">View Tour</a>
                    <a href="../hotelinfo/view_hotels.php?city_id=<?= $dest['id'] ?>" class="cart-btn">View Hotel</a>
                </div>
            </div>
            <div class="content">
                <h3><?= $dest['name'] ?></h3>
                <div class="price"><?= $dest['desc'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="review" id="review">
    <h1 class="heading">customer's <span>review</span></h1>
    <div class="box-container">
        <?php
        $reviews = [
            ['stars' => 5, 'text' => 'Booking my dream trip to the stunning landscapes of Tây Bắc with VietTransit was a breeze – seamless and stress-free!', 'img' => 'pic-1.jpg', 'name' => 'Thanh Trung', 'location' => 'Ho Chi Minh'],
            ['stars' => 5, 'text' => 'Thanks to Travelscapes, I explored the breathtaking beauty of Phu Yen with ease, and the deals were unbeatable!', 'img' => 'pic-2.png', 'name' => 'Thuong Vo', 'location' => 'Ha Noi'],
            ['stars' => 5, 'text' => 'Made my adventure to the charming Da Lat unforgettable, with expert guidance and fantastic itineraries.', 'img' => 'pic-3.jpg', 'name' => 'Thinh Le', 'location' => 'Binh Phuoc']
        ];
        
        foreach ($reviews as $review): ?>
        <div class="box">
            <div class="stars">
                <?php for ($i = 0; $i < $review['stars']; $i++): ?>
                <i class="fas fa-star"></i>
                <?php endfor; ?>
            </div>
            <p><?= $review['text'] ?></p>
            <div class="user">
                <img src="../images/<?= $review['img'] ?>" alt="<?= $review['name'] ?> pfp">
                <div class="user-info">
                    <h3><?= $review['name'] ?></h3>
                    <span><?= $review['location'] ?></span>
                </div>
            </div>
            <span class="fas fa-quote-right"></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>