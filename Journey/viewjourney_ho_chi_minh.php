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
    <title>Ho Chi Minh Travel</title>
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
    <h1>Ho Chi Minh</h1>
    <p>
    Ho Chi Minh City in the spring is alive with vibrant streets, blooming flowers, and the energetic rhythm of daily life. The city comes to life with the aroma of street food, the sounds of motorbikes, and the warm smiles of the locals, making spring in Ho Chi Minh a unique and colorful experience.
    </p>
    <p>
    Register for a <strong>Ho Chi Minh City</strong> tour with VietTransit, and you can explore the following popular destinations: <strong>Ho Chi Minh City</strong>... To learn more about Ho Chi Minh City, please refer to <a href="/Travel tips/traveltip_hcm.php">Ho Chi Minh City Travel Tips</a>.
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
                    data-price="4590000" 
                    data-duration="4" 
                    data-destination="Ho Chi Minh" 
                    data-departure="Ho Chi Minh City" 
                    data-transport="bus">
                    <div class="tour-image">
                        <img src="../tourphotoID/5.jpg" alt="Cu Chi - Mekong Delta Tour">
                        <span class="discount-badge">-5.2%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Discover Ho Chi Minh - Cu Chi Tunnels - Mekong Delta</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 4 days 3 nights</li>
                                <li><span class="icon">📅</span> Daily</li>
                                <li><span class="icon">🚌</span> Bus</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 25</li>
                                <li><span class="icon">🚩</span> Departure: Ho Chi Minh City</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">4,590,000 ₫ <span class="original-price">4,840,000 ₫</span></p>
                                <a href="/Tour_detail/hcm_1.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tour-card" 
                    data-price="890000" 
                    data-duration="1" 
                    data-destination="Ho Chi Minh" 
                    data-departure="Ho Chi Minh City" 
                    data-transport="walking">
                    <div class="tour-image">
                        <img src="../tourphotoID/6.jpg" alt="Ho Chi Minh Food Tour">
                        <span class="discount-badge">-4.5%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Ho Chi Minh City Street Food and Culture Walk</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> Half Day</li>
                                <li><span class="icon">📅</span> Afternoon</li>
                                <li><span class="icon">🚶</span> Walking</li>
                                <li><span class="icon">🏨</span> N/A</li>
                                <li><span class="icon">👥</span> 15</li>
                                <li><span class="icon">🚩</span> Departure: Ho Chi Minh City</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">890,000 ₫ <span class="original-price">933,000 ₫</span></p>
                                <a href="/Tour_detail/hcm_2.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>
                
               <div class="tour-card" 
                    data-price="980000" 
                    data-duration="1" 
                    data-destination="Ho Chi Minh" 
                    data-departure="Ho Chi Minh City" 
                    data-transport="bus">
                    <div class="tour-image">
                        <img src="../tourphotoID/7.jpg" alt="Museum and Art Tour">
                        <span class="discount-badge">-3.8%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Museum and Contemporary Art Tour in Ho Chi Minh</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 2 day</li>
                                <li><span class="icon">📅</span> Weekdays</li>
                                <li><span class="icon">🚌</span> Bus</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Ho Chi Minh City</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">980,000 ₫ <span class="original-price">1,019,000 ₫</span></p>
                                <a href="/Tour_detail/hcm_3.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tour-card" 
                    data-price="1050000" 
                    data-duration="1" 
                    data-destination="Ho Chi Minh" 
                    data-departure="Ho Chi Minh City" 
                    data-transport="bus-boat">
                    <div class="tour-image">
                        <img src="../tourphotoID/8.jpg" alt="Mekong Delta Day Tour">
                        <span class="discount-badge">-4.5%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Mekong Delta Tour Departing from Ho Chi Minh City</h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 1 day</li>
                                <li><span class="icon">📅</span> Daily</li>
                                <li><span class="icon">🚌</span> Bus + Boat</li>
                                <li><span class="icon">🏨</span> N/A</li>
                                <li><span class="icon">👥</span> 25</li>
                                <li><span class="icon">🚩</span> Departure: Ho Chi Minh City</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">1,050,000 ₫ <span class="original-price">1,100,000 ₫</span></p>
                                <a href="/Tour_detail/hcm_4.php" class="btn">See More</a>
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