<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phu Quoc Travel</title>
    <link rel="stylesheet" href="../css/viewjourney.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

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
    <h1>PHU QUOC TRAVEL</h1>
    <p>
    Phu Quoc is a tropical paradise renowned for its pristine beaches, crystal-clear waters, and lush green landscapes. This island offers a perfect blend of relaxation and adventure with vibrant coral reefs, pepper farms, bustling night markets, and tranquil fishing villages. Whether you seek sun-soaked shores or cultural experiences, Phu Quoc welcomes all travelers with its warm hospitality and natural beauty.
    </p>
    <p>
    Register for a <strong>Phu Quoc</strong> tour with VietTransit, and you can explore the following must-see destinations: <strong>Phu Quoc</strong>... To learn more about Phu Quoc, please refer to <a href="/Travel tips/traveltip_phuquoc.php">Phu Quoc Travel Tips</a>.
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
                    data-price="6290000" 
                    data-duration="4" 
                    data-destination="Phu Quoc" 
                    data-departure="Ho Chi Minh City" 
                    data-transport="flight">
                    <div class="tour-image">
                        <img src="../tourphotoID/25.jpg" alt="Phu Quoc Paradise Escape Tour">
                        <span class="discount-badge">-12%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Phu Quoc Paradise Escape: Sao Beach - Vinpearl Safari - Night Market - Pepper Farm</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 4 days 3 nights</li>
                                <li><span class="icon">📅</span> 24/05, 31/05, 07/06</li>
                                <li><span class="icon">✈️</span> Flight</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Ho Chi Minh City</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">6,290,000 ₫ <span class="original-price">7,136,364 ₫</span></p>
                                <a href="/Tour_detail/phuquoc_1.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tour-card" 
                    data-price="5790000" 
                    data-duration="4" 
                    data-destination="Phu Quoc" 
                    data-departure="Ho Chi Minh City" 
                    data-transport="flight">
                    <div class="tour-image">
                        <img src="../tourphotoID/26.jpg" alt="Phu Quoc Island Adventure Tour">
                        <span class="discount-badge">-14%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Phu Quoc Island Adventure: Phu Quoc National Park - Fish Sauce Factory - Coconut Prison - Sunset Beach</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 4 days 3 nights</li>
                                <li><span class="icon">📅</span> 25/05, 01/06, 08/06</li>
                                <li><span class="icon">✈️</span> Flight</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 22</li>
                                <li><span class="icon">🚩</span> Departure: Ho Chi Minh City</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">5,790,000 ₫ <span class="original-price">6,744,186 ₫</span></p>
                                <a href="/Tour_detail/phuquoc_2.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tour-card" 
                    data-price="6490000" 
                    data-duration="4" 
                    data-destination="Phu Quoc" 
                    data-departure="Da Nang" 
                    data-transport="flight">
                    <div class="tour-image">
                        <img src="../tourphotoID/27.jpg" alt="Phu Quoc Island Serenity Tour">
                        <span class="discount-badge">-13%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Phu Quoc Island Serenity: Sao Beach - Pepper Farm - Night Market - Sunset at Dinh Cau</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 4 days 3 nights</li>
                                <li><span class="icon">📅</span> 26/05, 02/06, 09/06</li>
                                <li><span class="icon">✈️</span> Flight</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Da Nang</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">6,490,000 ₫ <span class="original-price">7,475,000 ₫</span></p>
                                <a href="/Tour_detail/phuquoc_3.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tour-card" 
                    data-price="6390000" 
                    data-duration="4" 
                    data-destination="Phu Quoc" 
                    data-departure="Hanoi" 
                    data-transport="bus">
                    <div class="tour-image">
                        <img src="../tourphotoID/28.jpg" alt="Phu Quoc Relax & Discover Tour">
                        <span class="discount-badge">-10%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Phu Quoc Relax & Discover: Ong Lang Beach - Pepper Farm - Fish Sauce Village - Night Market</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 4 days 3 nights</li>
                                <li><span class="icon">📅</span> 24/05, 31/05, 07/06</li>
                                <li><span class="icon">🚌</span> Bus</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 22</li>
                                <li><span class="icon">🚩</span> Departure: Hanoi</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">6,390,000 ₫ <span class="original-price">7,100,000 ₫</span></p>
                                <a href="/Tour_detail/phuquoc_4.php" class="btn">See More</a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const rangeMin = document.querySelector('.range-min');
    const rangeMax = document.querySelector('.range-max');
    const minPrice = document.querySelector('.min-price');
    const maxPrice = document.querySelector('.max-price');
    const applyFilterBtn = document.getElementById('apply-filter');
    const resetFilterBtn = document.getElementById('reset-filter');
    const tourCards = document.querySelectorAll('.tour-card');
    
    // Update input fields when range sliders move
    rangeMin.addEventListener('input', function() {
        minPrice.value = this.value;
        if (parseInt(rangeMin.value) > parseInt(rangeMax.value)) {
            rangeMin.value = rangeMax.value;
            minPrice.value = rangeMax.value;
        }
    });
    
    rangeMax.addEventListener('input', function() {
        maxPrice.value = this.value;
        if (parseInt(rangeMax.value) < parseInt(rangeMin.value)) {
            rangeMax.value = rangeMin.value;
            maxPrice.value = rangeMin.value;
        }
    });
    
    // Update range sliders when input fields change
    minPrice.addEventListener('input', function() {
        rangeMin.value = this.value;
    });
    
    maxPrice.addEventListener('input', function() {
        rangeMax.value = this.value;
    });
    
    // Apply filters
    applyFilterBtn.addEventListener('click', function() {
        // Get filter values
        const minPriceValue = parseInt(minPrice.value);
        const maxPriceValue = parseInt(maxPrice.value);
        
        // Get selected durations
        const selectedDurations = [];
        document.querySelectorAll('input[name="duration"]:checked').forEach(input => {
            selectedDurations.push(input.value);
        });
        
        // Get selected destinations
        const selectedDestinations = [];
        document.querySelectorAll('input[name="destination"]:checked').forEach(input => {
            selectedDestinations.push(input.value);
        });
        
        // Get selected transportation types
        const selectedTransports = [];
        document.querySelectorAll('input[name="transport"]:checked').forEach(input => {
            selectedTransports.push(input.value);
        });
        
        // Get departure point
        const departurePoint = document.querySelector('.departure-select').value;
        
        // Get departure date
        const departureDate = document.querySelector('.departure-date').value;
        
        // Apply filters to each tour card
        tourCards.forEach(card => {
            let showCard = true;
            
            // Filter by price
            const tourPrice = parseInt(card.getAttribute('data-price'));
            if (tourPrice < minPriceValue || tourPrice > maxPriceValue) {
                showCard = false;
            }
            
            // Filter by duration
            if (selectedDurations.length > 0) {
                const tourDuration = parseInt(card.getAttribute('data-duration'));
                let durationMatch = false;
                
                selectedDurations.forEach(range => {
                    if (range === '1-3' && tourDuration >= 1 && tourDuration <= 3) {
                        durationMatch = true;
                    } else if (range === '4-7' && tourDuration >= 4 && tourDuration <= 7) {
                        durationMatch = true;
                    } else if (range === '8+' && tourDuration >= 8) {
                        durationMatch = true;
                    }
                });
                
                if (!durationMatch) {
                    showCard = false;
                }
            }
            
            // Filter by destination
            if (selectedDestinations.length > 0) {
                const tourDestination = card.getAttribute('data-destination');
                if (!selectedDestinations.includes(tourDestination)) {
                    showCard = false;
                }
            }
            
            // Filter by transportation
            if (selectedTransports.length > 0) {
                const tourTransport = card.getAttribute('data-transport');
                if (!selectedTransports.includes(tourTransport)) {
                    showCard = false;
                }
            }
            
            // Filter by departure point
            if (departurePoint && departurePoint !== '') {
                const tourDeparture = card.getAttribute('data-departure');
                if (tourDeparture !== departurePoint) {
                    showCard = false;
                }
            }
            
            // Show or hide card
            if (showCard) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // Reset filters
    resetFilterBtn.addEventListener('click', function() {
        // Reset range sliders and inputs
        rangeMin.value = 1000000;
        rangeMax.value = 20000000;
        minPrice.value = 1000000;
        maxPrice.value = 20000000;
        
        // Reset checkboxes
        document.querySelectorAll('input[type="checkbox"]').forEach(input => {
            input.checked = false;
        });
        
        // Reset select
        document.querySelector('.departure-select').value = '';
        
        // Reset date
        document.querySelector('.departure-date').value = '';
        
        // Show all tour cards
        tourCards.forEach(card => {
            card.style.display = 'flex';
        });
    });
});
</script>
</body>
</html>