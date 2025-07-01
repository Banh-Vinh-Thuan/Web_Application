<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nha Trang Travel</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="../css/viewjourney.css">
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
    <h1>NHA TRANG TRAVEL</h1>
    <p>
        Nha Trang, a coastal gem of Vietnam, is renowned for its pristine beaches, turquoise waters, and vibrant marine life. This seaside city offers a perfect blend of natural beauty and cultural heritage, with attractions ranging from ancient Cham temples to luxury resorts and amusement parks.
    </p>
    <p>
        Register for a <strong>Nha Trang</strong> tour with Vietravel and discover highlights such as Tháp Bà Ponagar, Nhũ Tiên Beach, VinWonders, and traditional pottery villages. To learn more about Nha Trang's unique charm and travel experiences, please refer to <a href="/Travel tips/traveltip.php?tip=nhatrang" style="color: #007bff; text-decoration: underline !important;">Nha Trang Travel Tips</a>.
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
                    data-price="2390000" 
                    data-duration="3" 
                    data-destination="Nha Trang" 
                    data-departure="Ho Chi Minh City" 
                    data-transport="bus">
                    <div class="tour-image">
                        <img src="../tourphotoID/9.jpg" alt="Nha Trang Tour">
                        <span class="discount-badge">-20%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Nha Trang - Thap Ba Ponagar - Nhu Tien Beach - VinWonders - Bau Truc Pottery Village </h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 3 days 2 nights</li>
                                <li><span class="icon">📅</span> 12/05, 19/05, 26/05</li>
                                <li><span class="icon">🚌</span> Bus</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Ho Chi Minh City</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">2,390,000 ₫ <span class="original-price">2,987,500 ₫</span></p>
                                <a href="/Tour_detail/nhatrang_1.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tour-card" 
                    data-price="2690000" 
                    data-duration="3" 
                    data-destination="Nha Trang" 
                    data-departure="Da Nang" 
                    data-transport="bus">
                    <div class="tour-image">
                        <img src="../tourphotoID/10.jpg" alt="Nha Trang Tour">
                        <span class="discount-badge">-18%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Nha Trang - Bau Truc Pottery Village - Ancient Nha Trang - Thap Ba Ponagar - Nhu Tien Beach - VinWonders - Pottery & Seashell Workshop Experience </h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 3 days 2 nights</li>
                                <li><span class="icon">📅</span> 12/05, 09/06, 16/06</li>
                                <li><span class="icon">🚌</span> Bus</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Da Nang</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">2,690,000 ₫ <span class="original-price">3,280,488 ₫</span></p>
                                <a href="/Tour_detail/nhatrang_2.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tour-card" 
                    data-price="3190000" 
                    data-duration="3" 
                    data-destination="Nha Trang" 
                    data-departure="Ho Chi Minh City" 
                    data-transport="flight">
                    <div class="tour-image">
                        <img src="../tourphotoID/11.jpg" alt="Nha Trang Beach Adventure">
                        <span class="discount-badge">-15%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Nha Trang - Hon Mun Island Snorkeling - Institute of Oceanography - Vinpearl Cable Car - Long Son Pagoda - Local Seafood Feast </h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 3 days 2 nights</li>
                                <li><span class="icon">📅</span> 18/05, 25/05, 01/06</li>
                                <li><span class="icon">✈️</span> Flight</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Ho Chi Minh City</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">3,190,000 ₫ <span class="original-price">3,752,900 ₫</span></p>
                                <a href="/Tour_detail/nhatrang_3.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tour-card" 
                    data-price="2890000" 
                    data-duration="3" 
                    data-destination="Nha Trang" 
                    data-departure="Can Tho" 
                    data-transport="flight">
                    <div class="tour-image">
                        <img src="../tourphotoID/12.jpg" alt="Nha Trang Nature & Culture Tour">
                        <span class="discount-badge">-17%</span>
                    </div>
                    <div class="tour-details">
                        <h3>Nha Trang - Yang Bay Eco Park - Mud Bath Experience - Dam Market - Thap Ba Ponagar - Coastal Road Discovery </h3>
                        <div class="tour-info">
                            <ul>
                                <li><span class="icon">⏰</span> 3 days 2 nights</li>
                                <li><span class="icon">📅</span> 20/05, 27/05, 03/06</li>
                                <li><span class="icon">✈️</span> Flight</li>
                                <li><span class="icon">🏨</span> Hotel</li>
                                <li><span class="icon">👥</span> 20</li>
                                <li><span class="icon">🚩</span> Departure: Can Tho</li>
                            </ul>
                            <div class="price-action">
                                <p class="price">2,890,000 ₫ <span class="original-price">3,481,200 ₫</span></p>
                                <a href="/Tour_detail/nhatrang_4.php" class="btn">See More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
<?php include __DIR__ . '/../footer.php'; ?> 
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