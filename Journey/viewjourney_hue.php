<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hue Travel</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="../css/viewjourney.css">
    <style>
        /* Ensure content is not overlapped by fixed header */
        .main-content {
            margin-top: 100px; /* Adjust based on header height */
            padding: 20px;
        }
    </style>
</head>
<body>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <input type="checkbox" name="" id="toggler">
    <label for="toggler" class="fas fa-bars"></label>

    <a href="#" class="logo">
        <img src="/images/logo.png" alt="VietTransit Logo">
        <span>VietTransit</span>
    </a>

    <nav class="navbar">
        <a href="/Login/loggedinhome.php">Home</a>
        <?php
            echo "<a href='../Login/profile.php'>Hello, " . $_SESSION['usersuid'] . "!</a>";
            echo '<a href="../home.php">Logout</a>';
        ?>
    </nav>

    <div class="icons">
        <span data-tooltip="Profile" data-flow="top">
            <a href="profile.php" class="fas fa-user"></a>
        </span>
    </div>
</header>

<div class="main-content">
    <h1>HUE TRAVEL</h1>
    <p>
        Hue, the ancient imperial capital of Vietnam, is a peaceful city rich in history, culture, and architectural beauty. Nestled along the banks of the Perfume River, Hue enchants visitors with its centuries-old citadels, royal tombs, and serene pagodas, all echoing the grandeur of the Nguyen Dynasty.
    </p>
    <p>
        Register for a <strong>Hue</strong> tour with Vietravel and immerse yourself in the city's heritage with visits to the Imperial City, Thien Mu Pagoda, and traditional garden houses. To learn more about Hue’s royal legacy and tranquil charm, please refer to <a href="/Travel tips/traveltip_hue.php">Hue Travel Tips</a>.
    </p>

    <div class="content-container">
        <div class="filter-sidebar">
            <h2>Filter Options</h2>
            
            <div class="filter-group">
                <h3>Price Range</h3>
                <div class="range-slider">
                    <input type="range" min="1000000" max="20000000" value="1000000" class="range-min">
                    <input type="range" min="1000000" max="20000000" value="20000000" class="range-max">
                </div>
                <div class="price-inputs">
                    <input type="number" value="1000000" min="1000000" max="20000000" class="min-price"> -
                    <input type="number" value="20000000" min="1000000" max="20000000" class="max-price">
                </div>
            </div>
            
            <div class="filter-group">
                <h3>Duration</h3>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="duration" value="1-3"> 1-3 days</label>
                    <label><input type="checkbox" name="duration" value="4-7"> 4-7 days</label>
                    <label><input type="checkbox" name="duration" value="8+"> 8+ days</label>
                </div>
            </div>
            
            <div class="filter-group">
                <h3>Departure Point</h3>
                <select class="departure-select">
                    <option value="">All Departure Points</option>
                    <option value="Ho Chi Minh City">Ho Chi Minh City</option>
                    <option value="Hanoi">Hanoi</option>
                    <option value="Da Nang">Da Nang</option>
                    <option value="Can Tho">Can Tho</option>
                    <option value="Hai Phong">Hai Phong</option>
                </select>
            </div>
            
            <div class="filter-group">
                <h3>Departure Date</h3>
                <input type="date" class="departure-date">
            </div>
            
            <div class="filter-group">
                <h3>Transportation</h3>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="transport" value="flight"> Flight</label>
                    <label><input type="checkbox" name="transport" value="bus"> Bus</label>
                    <label><input type="checkbox" name="transport" value="train"> Train</label>
                </div>
            </div>
            
            <button class="filter-button" id="apply-filter">Apply Filters</button>
            <button class="reset-button" id="reset-filter">Reset Filters</button>
        </div>

        <div class="tour-content">
            <div class="tour-container">
                <div class="tour-card" 
                    data-price="6990000" 
                    data-duration="5" 
                    data-destination="Hue" 
                    data-departure="Ho Chi Minh City" 
                    data-transport="bus">
                    <div class="tour-image">
                        <img src="../tourphotoID/13.jpg" alt="Central Vietnam Tour">
                        <span class="discount-badge">-12%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Da Nang - Phong Nha Cave - La Vang - Hue - Ba Na Hills - Hoi An</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 5 days 4 nights</li>
                                <li><span class="icon">📅</span> 21/05, 23/05, 26/05</li>
                                <li><span class="icon">🚌</span> Bus</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Ho Chi Minh City</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">6,990,000 ₫ <span class="original-price">7,943,182 ₫</span></p>
                                <a href="/Tour_detail/hue_1.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tour-card" 
                    data-price="23590000" 
                    data-duration="10" 
                    data-destination="Hue" 
                    data-departure="Ho Chi Minh City" 
                    data-transport="bus">
                    <div class="tour-image">
                        <img src="../tourphotoID/14.jpg" alt="North-Central Vietnam Grand Tour">
                        <span class="discount-badge">-10%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Hanoi - Sapa - Fansipan - Ha Long - Ninh Binh - Da Nang - Son Tra - Ba Na Hills - Golden Bridge - Hoi An - La Vang - Hue - Phong Nha</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 10 days 9 nights</li>
                                <li><span class="icon">📅</span> 17/05, 24/05, 31/05</li>
                                <li><span class="icon">🚌</span> Bus</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Ho Chi Minh City</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">23,590,000 ₫ <span class="original-price">26,211,111 ₫</span></p>
                                <a href="/Tour_detail/hue_2.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tour-card" 
                    data-price="7590000" 
                    data-duration="4" 
                    data-destination="Hue" 
                    data-departure="Can Tho" 
                    data-transport="flight">
                    <div class="tour-image">
                        <img src="../tourphotoID/15.jpg" alt="Hue Cultural Discovery Tour">
                        <span class="discount-badge">-15%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Hue Cultural Discovery – Imperial Citadel – Thien Mu Pagoda – Perfume River – Truong Tien Bridge – Local Cuisine</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 4 days 3 nights</li>
                                <li><span class="icon">📅</span> 18/05, 25/05, 01/06</li>
                                <li><span class="icon">✈️</span> Flight</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Can Tho</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">7,590,000 ₫ <span class="original-price">8,923,500 ₫</span></p>
                                <a href="/Tour_detail/hue_3.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tour-card" 
                    data-price="5290000" 
                    data-duration="4" 
                    data-destination="Hue" 
                    data-departure="Hanoi" 
                    data-transport="train">
                    <div class="tour-image">
                        <img src="../tourphotoID/16.jpg" alt="Heritage Train Journey to Hue">
                        <span class="discount-badge">-13%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Hanoi - Overnight Train to Hue - Imperial City - Khai Dinh Tomb - Dong Ba Market - Relaxing Cruise on Perfume River</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 4 days 3 nights</li>
                                <li><span class="icon">📅</span> 18/05, 25/05, 01/06</li>
                                <li><span class="icon">🚆</span> Train</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Hanoi</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">5,290,000 ₫ <span class="original-price">6,080,460 ₫</span></p>
                                <a href="/Tour_detail/hue_4.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<section class="footer">
    <div class="box-container">
        <div class="box">
            <h3>Quick links</h3>
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#products">Places</a>
            <a href="#review">Review</a>
        </div>
        <div class="box">
            <h3>Extra links</h3>
            <a href="/Login/profile.php">My account</a>
            <a href="/Payment Interface/receiptlist.php">My List</a>
            <a href="/Login/profile.php">My favorite</a>
        </div>
        <div class="box">
            <h3>Popular Travel Locations</h3>
            <a href="viewjourney_taybac.php">Tay Bac</a>
            <a href="viewjourney_hcm.php">Ho Chi Minh</a>
            <a href="viewjourney_phuquoc.php">Phu Quoc</a>
            <a href="viewjourney_hue.php">Hue</a>
        </div>
        <div class="box">
            <h3>contact info</h3>
            <a href="https://github.com/socolate12345/Travel-Booking-Website">GitHub</a>
            <img src="./images/payment.png" alt="">
        </div>
    </div>
    <div class="credit">©2025 VietTransit</div>
</section>
</body>
</html>