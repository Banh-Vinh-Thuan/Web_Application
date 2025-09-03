<?php
session_start();

// Database connection
include '../dbconnect.php';

$userid = 0;
$user_name = '';
$user_email = '';

// Get search parameters
$from_cityid = isset($_GET['from']) ? (int)$_GET['from'] : null;
$to_cityid = isset($_GET['to']) ? (int)$_GET['to'] : null;
$departure_date = isset($_GET['departure']) ? $_GET['departure'] : date('Y-m-d', strtotime('+1 day'));
$return_date = isset($_GET['return']) ? $_GET['return'] : null;
$passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;
$class = isset($_GET['class']) ? $_GET['class'] : 'ECONOMY';

// Fetch cities from database
$cities = [];
$city_sql = "SELECT cityid, city, region FROM cities ORDER BY city ASC";
$city_result = $conn->query($city_sql);
if ($city_result->num_rows > 0) {
    while ($city_row = $city_result->fetch_assoc()) {
        // Generate airport codes based on city names - Fixed mapping
        $city_codes = [
            'Tay Bac' => 'TBA',
            'Ho Chi Minh' => 'SGN', 
            'Nha Trang' => 'CXR',
            'Hue' => 'HUI',
            'Phu Yen' => 'PXU',
            'Da Lat' => 'DLI',
            'Phu Quoc' => 'PQC',
            'Hoi An' => 'VCL',
            'Ha Giang' => 'VHG'
        ];
        
        $cities[$city_row['cityid']] = [
            'name' => $city_row['city'],
            'code' => $city_codes[$city_row['city']] ?? strtoupper(substr($city_row['city'], 0, 3)),
            'region' => $city_row['region']
        ];
    }
}

// Check if user is logged in and get user information
if (isset($_SESSION['usersid'])) {
    $userid = (int)$_SESSION['usersid'];
    
    // Get username and email from session first
    $user_name = $_SESSION['usersuid'] ?? '';
    $user_email = $_SESSION['usersEmail'] ?? '';
    
    // If not in session, fetch from database
    if (empty($user_name) || empty($user_email)) {
        $user_stmt = $conn->prepare("SELECT usersuid, usersEmail FROM login WHERE usersid = ?");
        if ($user_stmt) {
            $user_stmt->bind_param("i", $userid);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            if ($user_row = $user_result->fetch_assoc()) {
                $user_name = $user_row['usersuid'];
                $user_email = $user_row['usersEmail'];
                // Update session
                $_SESSION['usersuid'] = $user_name;
                $_SESSION['usersEmail'] = $user_email;
            }
            $user_stmt->close();
        }
    }
}

// Function to generate flights for a specific route and date
function generateFlightsForRoute($conn, $from_cityid, $to_cityid, $flight_date, $requested_class = null) {
    $airlines = ["Vietnam Airlines", "VietJet Air", "Bamboo Airways", "Pacific Airlines"];
    $flight_classes = ["Economy", "Business", "First Class"];
    $generated_flights = [];
    
    // Base prices for different classes
    $base_prices = [
        "Economy" => rand(800000, 1200000),
        "Business" => rand(1500000, 2200000), 
        "First Class" => rand(2500000, 3000000)
    ];
    
    // Price multipliers for different airlines
    $airline_multipliers = [
        "Vietnam Airlines" => 1.1,  // Premium airline
        "VietJet Air" => 0.9,       // Budget airline
        "Bamboo Airways" => 1.0,    // Standard
        "Pacific Airlines" => 0.95   // Slightly cheaper
    ];
    
    // Time-based price adjustments
    $time_multipliers = [
        'morning' => 1.0,    // Standard price
        'afternoon' => 0.95, // Slightly cheaper
        'evening' => 1.05    // Slightly more expensive
    ];
    
    // If a specific class is requested, prioritize that class but also include others
    if ($requested_class) {
        // Convert request format to match our database format
        $class_mapping = [
            'ECONOMY' => 'Economy',
            'BUSINESS' => 'Business', 
            'FIRST' => 'First Class'
        ];
        $preferred_class = $class_mapping[$requested_class] ?? 'Economy';
    }
    
    // Generate 5-6 flights for morning (6-12h)
    $morning_flights = rand(5, 6);
    for ($i = 0; $i < $morning_flights; $i++) {
        $airline = $airlines[array_rand($airlines)];
        
        // If a specific class is requested, use it for most flights, but add variety
        if ($requested_class && $i < $morning_flights - 2) {
            $flight_class = $preferred_class;
        } else {
            $flight_class = $flight_classes[array_rand($flight_classes)];
        }
        
        $hour = rand(6, 11);
        $minute = rand(0, 59);
        $dep_time = sprintf("%02d:%02d:00", $hour, $minute);
        
        $arr_hour = ($hour + 2) % 24;
        $arr_time = sprintf("%02d:%02d:00", $arr_hour, $minute);
        
        // Calculate logical price
        $base_price = $base_prices[$flight_class];
        $airline_adjustment = $airline_multipliers[$airline];
        $time_adjustment = $time_multipliers['morning'];
        $price = round($base_price * $airline_adjustment * $time_adjustment);
        
        $sql = "INSERT INTO flights (name_flight, departure_id, arrival_id, flight_date, departure_time, arrival_time, price, class) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("siisssis", $airline, $from_cityid, $to_cityid, $flight_date, $dep_time, $arr_time, $price, $flight_class);
            $stmt->execute();
            $flight_id = $conn->insert_id;
            $stmt->close();
            
            // Add to generated flights array
            $generated_flights[] = [
                'flight_id' => $flight_id,
                'airline' => $airline,
                'from_cityid' => $from_cityid,
                'to_cityid' => $to_cityid,
                'departure_date' => $flight_date,
                'departure_time' => $dep_time,
                'arrival_time' => $arr_time,
                'price' => $price,
                'class' => $flight_class
            ];
        }
    }
    
    // Generate 4-5 flights for afternoon (12-18h)
    $afternoon_flights = rand(4, 5);
    for ($i = 0; $i < $afternoon_flights; $i++) {
        $airline = $airlines[array_rand($airlines)];
        
        // If a specific class is requested, use it for most flights, but add variety
        if ($requested_class && $i < $afternoon_flights - 1) {
            $flight_class = $preferred_class;
        } else {
            $flight_class = $flight_classes[array_rand($flight_classes)];
        }
        
        $hour = rand(12, 17);
        $minute = rand(0, 59);
        $dep_time = sprintf("%02d:%02d:00", $hour, $minute);
        
        $arr_hour = ($hour + 2) % 24;
        $arr_time = sprintf("%02d:%02d:00", $arr_hour, $minute);
        
        // Calculate logical price
        $base_price = $base_prices[$flight_class];
        $airline_adjustment = $airline_multipliers[$airline];
        $time_adjustment = $time_multipliers['afternoon'];
        $price = round($base_price * $airline_adjustment * $time_adjustment);
        
        $sql = "INSERT INTO flights (name_flight, departure_id, arrival_id, flight_date, departure_time, arrival_time, price, class) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("siisssis", $airline, $from_cityid, $to_cityid, $flight_date, $dep_time, $arr_time, $price, $flight_class);
            $stmt->execute();
            $flight_id = $conn->insert_id;
            $stmt->close();
            
            // Add to generated flights array
            $generated_flights[] = [
                'flight_id' => $flight_id,
                'airline' => $airline,
                'from_cityid' => $from_cityid,
                'to_cityid' => $to_cityid,
                'departure_date' => $flight_date,
                'departure_time' => $dep_time,
                'arrival_time' => $arr_time,
                'price' => $price,
                'class' => $flight_class
            ];
        }
    }
    
    // Generate 3-4 flights for evening (18-23h)
    $evening_flights = rand(3, 4);
    for ($i = 0; $i < $evening_flights; $i++) {
        $airline = $airlines[array_rand($airlines)];
        
        // If a specific class is requested, use it for most flights, but add variety
        if ($requested_class && $i < $evening_flights - 1) {
            $flight_class = $preferred_class;
        } else {
            $flight_class = $flight_classes[array_rand($flight_classes)];
        }
        
        $hour = rand(18, 22);
        $minute = rand(0, 59);
        $dep_time = sprintf("%02d:%02d:00", $hour, $minute);
        
        $arr_hour = ($hour + 2) % 24;
        if ($arr_hour < $hour) {
            // Handle overnight flights
            $arr_time = sprintf("%02d:%02d:00", $arr_hour, $minute);
        } else {
            $arr_time = sprintf("%02d:%02d:00", $arr_hour, $minute);
        }
        
        // Calculate logical price
        $base_price = $base_prices[$flight_class];
        $airline_adjustment = $airline_multipliers[$airline];
        $time_adjustment = $time_multipliers['evening'];
        $price = round($base_price * $airline_adjustment * $time_adjustment);
        
        $sql = "INSERT INTO flights (name_flight, departure_id, arrival_id, flight_date, departure_time, arrival_time, price, class) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("siisssis", $airline, $from_cityid, $to_cityid, $flight_date, $dep_time, $arr_time, $price, $flight_class);
            $stmt->execute();
            $flight_id = $conn->insert_id;
            $stmt->close();
            
            // Add to generated flights array
            $generated_flights[] = [
                'flight_id' => $flight_id,
                'airline' => $airline,
                'from_cityid' => $from_cityid,
                'to_cityid' => $to_cityid,
                'departure_date' => $flight_date,
                'departure_time' => $dep_time,
                'arrival_time' => $arr_time,
                'price' => $price,
                'class' => $flight_class
            ];
        }
    }
    
    return $generated_flights;
}

// Fetch flights - Updated query with auto-generation
$flights = [];
if ($from_cityid && $to_cityid) {
    // Debug: Check if cities exist
    if (!isset($cities[$from_cityid]) || !isset($cities[$to_cityid])) {
        error_log("Invalid city IDs: from=$from_cityid, to=$to_cityid");
    }
    
    // First, try to find existing flights
    $sql = "SELECT flight_id, name_flight as airline, departure_id as from_cityid, arrival_id as to_cityid, 
                   flight_date as departure_date, departure_time, arrival_time, price, class 
            FROM flights 
            WHERE departure_id = ? 
            AND arrival_id = ? 
            AND flight_date = ?
            ORDER BY price ASC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iis", $from_cityid, $to_cityid, $departure_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Convert decimal price to integer for display
                $row['price'] = (int)$row['price'];
                $flights[] = $row;
            }
        }
        $stmt->close();
        
        // If no flights found, generate them automatically with class preference
        if (empty($flights)) {
            error_log("No flights found for route $from_cityid -> $to_cityid on $departure_date. Generating flights...");
            $generated_flights = generateFlightsForRoute($conn, $from_cityid, $to_cityid, $departure_date, $class);
            
            // Convert generated flights to the same format as database results
            foreach ($generated_flights as $flight) {
                $flights[] = [
                    'flight_id' => $flight['flight_id'],
                    'airline' => $flight['airline'],
                    'from_cityid' => $flight['from_cityid'],
                    'to_cityid' => $flight['to_cityid'],
                    'departure_date' => $flight['departure_date'],
                    'departure_time' => $flight['departure_time'],
                    'arrival_time' => $flight['arrival_time'],
                    'price' => (int)$flight['price'],
                    'class' => $flight['class']
                ];
            }
        }
        
        // Debug: Log search parameters and results
        error_log("Flight search: from=$from_cityid, to=$to_cityid, date=$departure_date, class=$class, found=" . count($flights) . " flights");
    } else {
        error_log("Failed to prepare flight search query: " . $conn->error);
    }
}

// Fetch return flights if round trip - Updated with auto-generation
$return_flights = [];
if ($return_date && $from_cityid && $to_cityid) {
    // First, try to find existing return flights
    $sql = "SELECT flight_id, name_flight as airline, departure_id as from_cityid, arrival_id as to_cityid, 
                   flight_date as departure_date, departure_time, arrival_time, price, class 
            FROM flights 
            WHERE departure_id = ? 
            AND arrival_id = ? 
            AND flight_date = ?
            ORDER BY price ASC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iis", $to_cityid, $from_cityid, $return_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Convert decimal price to integer for display
                $row['price'] = (int)$row['price'];
                $return_flights[] = $row;
            }
        }
        $stmt->close();
        
        // If no return flights found, generate them automatically with class preference
        if (empty($return_flights)) {
            error_log("No return flights found for route $to_cityid -> $from_cityid on $return_date. Generating flights...");
            $generated_return_flights = generateFlightsForRoute($conn, $to_cityid, $from_cityid, $return_date, $class);
            
            // Convert generated flights to the same format as database results
            foreach ($generated_return_flights as $flight) {
                $return_flights[] = [
                    'flight_id' => $flight['flight_id'],
                    'airline' => $flight['airline'],
                    'from_cityid' => $flight['from_cityid'],
                    'to_cityid' => $flight['to_cityid'],
                    'departure_date' => $flight['departure_date'],
                    'departure_time' => $flight['departure_time'],
                    'arrival_time' => $flight['arrival_time'],
                    'price' => (int)$flight['price'],
                    'class' => $flight['class']
                ];
            }
        }
    }
}

$conn->close();

function formatTime($time) {
    return date('H:i', strtotime($time));
}

function calculateDuration($departure, $arrival) {
    $dep = new DateTime($departure);
    $arr = new DateTime($arrival);
    $interval = $dep->diff($arr);
    
    // Handle overnight flights
    if ($arr < $dep) {
        $arr->add(new DateInterval('P1D'));
        $interval = $dep->diff($arr);
    }
    
    return $interval->format('%hh %im');
}

function getAirlineLogo($airline) {
    $logos = [
        'VietJet Air' => '✈️',
        'Vietnam Airlines' => '🛩️',
        'Bamboo Airways' => '🌿',
        'Pacific Airlines' => '🌊'
    ];
    return $logos[$airline] ?? '✈️';
}

function getClassIcon($class) {
    $icons = [
        'Economy' => '💺',
        'Business' => '🛋️',
        'First Class' => '👑'
    ];
    return $icons[$class] ?? '💺';
}

function formatClassDisplay($class) {
    $display = [
        'Economy' => 'Economy Class',
        'Business' => 'Business Class', 
        'First Class' => 'First Class'
    ];
    return $display[$class] ?? $class;
}

// Debug section - Remove this after testing
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<pre>";
    echo "Debug Information:\n";
    echo "From City ID: $from_cityid\n";
    echo "To City ID: $to_cityid\n";
    echo "Departure Date: $departure_date\n";
    echo "Selected Class: $class\n";
    echo "Cities array:\n";
    print_r($cities);
    echo "Flights found: " . count($flights) . "\n";
    if (!empty($flights)) {
        echo "First flight:\n";
        print_r($flights[0]);
    }
    echo "</pre>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Search</title>
    <link rel="stylesheet" href="../Flight/flight.css?v=<?= time() ?>">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.css">
</head>
<body>
<?php include __DIR__ . '/../header.php'; ?>

<!-- Hero Section with Integrated Search -->
<div class="hero-section" style="background-image: url('../Flight/images/background.jpg');">
    <div class="hero-overlay">
        <div class="hero-content">
            <h1>FLIGHT BOOKING</h1>
            
            <!-- Flight Search Form in Hero -->
            <div class="hero-search-card">
                <form class="flight-search-form" method="GET" action="">
                    <div class="search-row">
                        <div class="trip-type">
                            <input type="radio" id="roundtrip" name="trip_type" value="roundtrip" <?php echo $return_date ? 'checked' : ''; ?>>
                            <label for="roundtrip">Round Trip</label>
                            <input type="radio" id="oneway" name="trip_type" value="oneway" <?php echo !$return_date ? 'checked' : ''; ?>>
                            <label for="oneway">One Way</label>
                        </div>
                    </div>
                    
                    <div class="search-row">
                        <div class="input-group">
                            <label for="from">From</label>
                            <select name="from" id="from" required>
                                <option value="">Select departure city</option>
                                <?php foreach ($cities as $id => $city): ?>
                                    <option value="<?php echo $id; ?>" <?php echo $from_cityid == $id ? 'selected' : ''; ?>>
                                        <?php echo $city['name']; ?> (<?php echo $city['code']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="swap-btn">
                            <button type="button" id="swapCities">⇄</button>
                        </div>
                        
                        <div class="input-group">
                            <label for="to">To</label>
                            <select name="to" id="to" required>
                                <option value="">Select destination city</option>
                                <?php foreach ($cities as $id => $city): ?>
                                    <option value="<?php echo $id; ?>" <?php echo $to_cityid == $id ? 'selected' : ''; ?>>
                                        <?php echo $city['name']; ?> (<?php echo $city['code']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="input-group">
                            <label for="departure">Departure</label>
                            <input type="date" name="departure" id="departure" value="<?php echo $departure_date; ?>" required>
                        </div>
                        
                        <div class="input-group return-group">
                            <label for="return">Return</label>
                            <input type="date" name="return" id="return" value="<?php echo $return_date; ?>">
                        </div>
                        
                        <div class="input-group">
                            <label for="passengers">Passengers</label>
                            <select name="passengers" id="passengers">
                                <?php for ($i = 1; $i <= 9; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $passengers == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> <?php echo $i == 1 ? 'Adult' : 'Adults'; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="input-group">
                            <label for="class">Class</label>
                            <select name="class" id="class">
                                <option value="ECONOMY" <?php echo $class == 'ECONOMY' ? 'selected' : ''; ?>>Economy</option>
                                <option value="BUSINESS" <?php echo $class == 'BUSINESS' ? 'selected' : ''; ?>>Business</option>
                                <option value="FIRST" <?php echo $class == 'FIRST' ? 'selected' : ''; ?>>First Class</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="search-actions">
                        <button type="submit" class="search-btn">
                            <span>Search Flights</span>
                            <span class="search-icon">🔍</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php if ($from_cityid && $to_cityid): ?>
    <!-- Flight Results Section -->
    <section class="results-section">
        <!-- Results Header -->
        <div class="results-header">
            <div class="route-info">
                <h2 class="route-title">
                    <?php echo $cities[$from_cityid]['name']; ?> 
                    <span class="route-arrow">→</span> 
                    <?php echo $cities[$to_cityid]['name']; ?>
                </h2>
                <p class="route-details">
                    <?php echo date('D, M j, Y', strtotime($departure_date)); ?> • 
                    <?php echo count($flights); ?> flights found • 
                    <?php echo $passengers; ?> <?php echo $passengers == 1 ? 'passenger' : 'passengers'; ?>
                    <?php if ($class): ?>
                        • Preferred: <?php 
                        $class_display = [
                            'ECONOMY' => 'Economy Class',
                            'BUSINESS' => 'Business Class',
                            'FIRST' => 'First Class'
                        ];
                        echo $class_display[$class] ?? $class; 
                        ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="sort-options">
                <label for="sortBy">Sort by:</label>
                <select id="sortBy">
                    <option value="price">Price (Low to High)</option>
                    <option value="time">Departure Time</option>
                    <option value="duration">Duration</option>
                    <option value="airline">Airline</option>
                    <option value="class">Class</option>
                </select>
            </div>
        </div>

        <!-- Filter Sidebar and Results -->
        <div class="results-content">
            <aside class="filters-sidebar">
                <div class="filter-card">
                    <h3 class="filter-title">🔍 Filter Results</h3>
                    
                    <!-- Price Filter -->
                    <div class="filter-group">
                        <h4 class="filter-group-title">💰 Price Range</h4>
                        <div class="price-range">
                            <input type="range" id="minPrice" min="0" max="3000000" step="50000" value="0">
                            <input type="range" id="maxPrice" min="0" max="3000000" step="50000" value="3000000">
                            <div class="price-labels">
                                <span id="minPriceLabel">0 VND</span>
                                <span id="maxPriceLabel">3,000,000 VND</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Class Filter -->
                    <div class="filter-group">
                        <h4 class="filter-group-title">🎯 Flight Class</h4>
                        <div class="checkbox-group">
                            <?php 
                            $flight_classes = array_unique(array_column($flights, 'class'));
                            foreach ($flight_classes as $flight_class): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" class="class-filter" value="<?php echo $flight_class; ?>" checked>
                                    <span class="checkmark"></span>
                                    <span><?php echo getClassIcon($flight_class) . ' ' . formatClassDisplay($flight_class); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Airline Filter -->
                    <div class="filter-group">
                        <h4 class="filter-group-title">✈️ Airlines</h4>
                        <div class="checkbox-group">
                            <?php 
                            $airlines = array_unique(array_column($flights, 'airline'));
                            foreach ($airlines as $airline): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" class="airline-filter" value="<?php echo $airline; ?>" checked>
                                    <span class="checkmark"></span>
                                    <span><?php echo getAirlineLogo($airline) . ' ' . $airline; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Time Filter -->
                    <div class="filter-group">
                        <h4 class="filter-group-title">🕐 Departure Time</h4>
                        <div class="time-slots">
                            <label class="time-slot">
                                <input type="checkbox" class="time-filter" value="morning" checked>
                                <div class="time-slot-content">
                                    <span class="time-icon">🌅</span>
                                    <span class="time-label">Morning<br><small>06:00 - 12:00</small></span>
                                </div>
                            </label>
                            <label class="time-slot">
                                <input type="checkbox" class="time-filter" value="afternoon" checked>
                                <div class="time-slot-content">
                                    <span class="time-icon">☀️</span>
                                    <span class="time-label">Afternoon<br><small>12:00 - 18:00</small></span>
                                </div>
                            </label>
                            <label class="time-slot">
                                <input type="checkbox" class="time-filter" value="evening" checked>
                                <div class="time-slot-content">
                                    <span class="time-icon">🌆</span>
                                    <span class="time-label">Evening<br><small>18:00 - 24:00</small></span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <button class="clear-filters">Clear All Filters</button>
                </div>
            </aside>

            <!-- Flight List -->
            <div class="flights-list">
                <?php if (empty($flights)): ?>
                    <div class="no-flights">
                        <div class="no-flights-icon">✈️</div>
                        <h3>No flights found</h3>
                        <p>Try adjusting your search criteria or selecting different dates.</p>
                        <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
                            <p><strong>Debug:</strong> Searched for flights from city ID <?php echo $from_cityid; ?> to city ID <?php echo $to_cityid; ?> on <?php echo $departure_date; ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($flights as $index => $flight): ?>
                        <div class="flight-card" data-flight-id="<?php echo $flight['flight_id']; ?>">
                            <div class="flight-main-info">
                                <div class="airline-info">
                                    <div class="airline-logo">
                                        <?php echo getAirlineLogo($flight['airline']); ?>
                                    </div>
                                    <div class="airline-details">
                                        <div class="airline-name"><?php echo $flight['airline']; ?></div>
                                        <div class="flight-number">FL-<?php echo $flight['flight_id']; ?></div>
                                        <div class="flight-class"><?php echo getClassIcon($flight['class']) . ' ' . formatClassDisplay($flight['class']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="flight-route">
                                    <div class="departure">
                                        <div class="time"><?php echo formatTime($flight['departure_time']); ?></div>
                                        <div class="city"><?php echo $cities[$flight['from_cityid']]['code']; ?></div>
                                    </div>
                                    
                                    <div class="flight-path">
                                        <div class="duration">
                                            <?php echo calculateDuration($flight['departure_time'], $flight['arrival_time']); ?>
                                        </div>
                                        <div class="path-line">
                                            <div class="line"></div>
                                            <div class="plane-icon">✈️</div>
                                        </div>
                                        <div class="stops">Non-stop</div>
                                    </div>
                                    
                                    <div class="arrival">
                                        <div class="time"><?php echo formatTime($flight['arrival_time']); ?></div>
                                        <div class="city"><?php echo $cities[$flight['to_cityid']]['code']; ?></div>
                                    </div>
                                </div>
                                
                                <div class="flight-price">
                                    <div class="price-amount"><?php echo number_format($flight['price']); ?> VND</div>
                                    <div class="price-per-person">per person</div>
                                    <button class="select-flight-btn" data-flight='<?php echo json_encode($flight); ?>'>
                                        Select Flight
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flight-details-toggle">
                                <button class="details-btn" data-flight-id="<?php echo $flight['flight_id']; ?>">
                                    Flight Details <span class="arrow">▼</span>
                                </button>
                            </div>
                            
                            <div class="flight-details" id="details-<?php echo $flight['flight_id']; ?>">
                                <div class="details-content">
                                    <div class="flight-class-info">
                                        <h4><?php echo getClassIcon($flight['class']); ?> Class Information</h4>
                                        <div class="class-benefits">
                                            <?php if ($flight['class'] == 'Economy'): ?>
                                                <div class="benefit-item">✓ Standard seat with basic amenities</div>
                                                <div class="benefit-item">✓ In-flight entertainment</div>
                                                <div class="benefit-item">✓ Complimentary snacks and beverages</div>
                                            <?php elseif ($flight['class'] == 'Business'): ?>
                                                <div class="benefit-item">✓ Premium seat with extra legroom</div>
                                                <div class="benefit-item">✓ Priority boarding and check-in</div>
                                                <div class="benefit-item">✓ Enhanced dining experience</div>
                                                <div class="benefit-item">✓ Access to business lounge</div>
                                                <div class="benefit-item">✓ Extra baggage allowance</div>
                                            <?php elseif ($flight['class'] == 'First Class'): ?>
                                                <div class="benefit-item">✓ Luxury suite with lie-flat bed</div>
                                                <div class="benefit-item">✓ Personal butler service</div>
                                                <div class="benefit-item">✓ Gourmet dining with wine selection</div>
                                                <div class="benefit-item">✓ Exclusive first-class lounge access</div>
                                                <div class="benefit-item">✓ Premium baggage allowance</div>
                                                <div class="benefit-item">✓ Chauffeur service (selected airports)</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="baggage-info">
                                        <h4>🎒 Baggage Information</h4>
                                        <?php if ($flight['class'] == 'Economy'): ?>
                                            <div class="baggage-item">
                                                <span>Cabin Baggage:</span>
                                                <span>7kg included</span>
                                            </div>
                                            <div class="baggage-item">
                                                <span>Checked Baggage:</span>
                                                <span>20kg included</span>
                                            </div>
                                        <?php elseif ($flight['class'] == 'Business'): ?>
                                            <div class="baggage-item">
                                                <span>Cabin Baggage:</span>
                                                <span>10kg included</span>
                                            </div>
                                            <div class="baggage-item">
                                                <span>Checked Baggage:</span>
                                                <span>30kg included</span>
                                            </div>
                                        <?php elseif ($flight['class'] == 'First Class'): ?>
                                            <div class="baggage-item">
                                                <span>Cabin Baggage:</span>
                                                <span>15kg included</span>
                                            </div>
                                            <div class="baggage-item">
                                                <span>Checked Baggage:</span>
                                                <span>40kg included</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="amenities-info">
                                        <h4>✨ In-flight Amenities</h4>
                                        <div class="amenities-list">
                                            <span class="amenity">📶 WiFi</span>
                                            <span class="amenity">🍽️ Meals</span>
                                            <span class="amenity">📺 Entertainment</span>
                                            <?php if ($flight['class'] != 'Economy'): ?>
                                                <span class="amenity">🔌 Power Outlets</span>
                                                <span class="amenity">🛏️ Extra Comfort</span>
                                            <?php endif; ?>
                                            <?php if ($flight['class'] == 'First Class'): ?>
                                                <span class="amenity">🍾 Premium Bar</span>
                                                <span class="amenity">👔 Personal Service</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="policies-info">
                                        <h4>📋 Policies</h4>
                                        <div class="policy-item">
                                            <span>✅ Free cancellation within 24 hours</span>
                                        </div>
                                        <?php if ($flight['class'] != 'Economy'): ?>
                                            <div class="policy-item">
                                                <span>✅ Free seat selection</span>
                                            </div>
                                            <div class="policy-item">
                                                <span>✅ Flexible change policy</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="policy-item">
                                                <span>⚠️ Seat selection fee may apply</span>
                                            </div>
                                            <div class="policy-item">
                                                <span>⚠️ Change fee applies for date modifications</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Popular Routes Section -->
    <section class="popular-routes">
        <h2 class="section-title">🔥 Popular Routes</h2>
        <div class="routes-grid">
            <div class="route-card">
                <div class="route-info">
                    <div class="route-cities">Ho Chi Minh City → Hanoi</div>
                    <div class="route-price">From 1,200,000 VND</div>
                </div>
                <div class="route-image">✈️</div>
            </div>
            
            <div class="route-card">
                <div class="route-info">
                    <div class="route-cities">Ho Chi Minh City → Da Nang</div>
                    <div class="route-price">From 950,000 VND</div>
                </div>
                <div class="route-image">✈️</div>
            </div>
            
            <div class="route-card">
                <div class="route-info">
                    <div class="route-cities">Hanoi → Nha Trang</div>
                    <div class="route-price">From 1,100,000 VND</div>
                </div>
                <div class="route-image">✈️</div>
            </div>
            
            <div class="route-card">
                <div class="route-info">
                    <div class="route-cities">Da Nang → Phu Quoc</div>
                    <div class="route-price">From 1,350,000 VND</div>
                </div>
                <div class="route-image">✈️</div>
            </div>
        </div>
    </section>
</div>

<!-- Flight Selection Modal - Enhanced Layout -->
<div id="flightModal" class="flight-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✈️ Confirm Your Flight Selection</h3>
            <span class="close-btn" id="closeFlightModal">&times;</span>
        </div>
        
        <div class="modal-body">
            <!-- Left Column - Flight Information -->
            <div class="flight-info-column">
                <div class="flight-info-title">
                    <i class="fas fa-plane"></i>
                    Flight Details
                </div>
                
                <div id="selectedFlightInfo"></div>
                
                <!-- Booking Summary Section -->
                <div class="booking-summary">
                    <h4><i class="fas fa-receipt"></i> Booking Summary</h4>
                    <div class="summary-row">
                        <span>Flight Class:</span>
                        <span id="flightClass">Economy</span>
                    </div>
                    <div class="summary-row">
                        <span>Base Price (per person):</span>
                        <span id="basePrice">0 VND</span>
                    </div>
                    <div class="summary-row">
                        <span>Number of Passengers:</span>
                        <span id="passengerCount"><?php echo $passengers; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">0 VND</span>
                    </div>
                    <div class="summary-row">
                        <span>Taxes & Fees:</span>
                        <span id="taxes">0 VND</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Price:</span>
                        <span id="totalPrice">0 VND</span>
                    </div>
                </div>
            </div>
            
            
            <!-- Right Column - User Information -->
            <div class="user-info-column">
                <div class="user-info-title">
                    <i class="fas fa-user-circle"></i>
                    Passenger Information
                </div>
                
                <div class="user-form-grid">
                    <div class="form-group">
                        <label for="passenger_name">
                            <i class="fas fa-user"></i> Full Name
                        </label>
                        <input type="text" id="passenger_name" 
                               value="<?php echo htmlspecialchars($user_name); ?>" 
                               <?php echo $userid ? 'readonly' : ''; ?> 
                               placeholder="Enter full name" required>
                        <?php if (!$userid): ?>
                            <small class="form-note">Please login for auto-fill or enter manually</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="passenger_email">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" id="passenger_email" 
                               value="<?php echo htmlspecialchars($user_email); ?>" 
                               <?php echo $userid ? 'readonly' : ''; ?> 
                               placeholder="Enter email address" required>
                        <?php if (!$userid): ?>
                            <small class="form-note">Please login for auto-fill or enter manually</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="passenger_phone">
                            <i class="fas fa-phone"></i> Contact Number <span style="color: #e74c3c;">*</span>
                        </label>
                        <input type="text" id="passenger_phone" 
                               placeholder="Enter your phone number" 
                               required>
                        <small class="form-note">Required for flight confirmation</small>
                    </div>
                </div>
                
                <!-- Button Container - New -->
                <div class="button-container">
                    <button class="cancel-btn" id="cancelBooking">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button class="confirm-btn" id="confirmBooking">
                        <i class="fas fa-credit-card"></i>
                        Continue to Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>

<script>
// Trip type toggle
document.addEventListener('DOMContentLoaded', function() {
    const roundTripRadio = document.getElementById('roundtrip');
    const oneWayRadio = document.getElementById('oneway');
    const returnGroup = document.querySelector('.return-group');
    
    function toggleReturnDate() {
        if (oneWayRadio.checked) {
            returnGroup.style.opacity = '0.5';
            returnGroup.style.pointerEvents = 'none';
            document.getElementById('return').value = '';
        } else {
            returnGroup.style.opacity = '1';
            returnGroup.style.pointerEvents = 'auto';
        }
    }
    
    roundTripRadio.addEventListener('change', toggleReturnDate);
    oneWayRadio.addEventListener('change', toggleReturnDate);
    
    // Initial state
    toggleReturnDate();
    
    // Swap cities
    document.getElementById('swapCities').addEventListener('click', function() {
        const fromSelect = document.getElementById('from');
        const toSelect = document.getElementById('to');
        
        const fromValue = fromSelect.value;
        fromSelect.value = toSelect.value;
        toSelect.value = fromValue;
    });
    
    // Flight details toggle
    document.querySelectorAll('.details-btn').forEach(button => {
        button.addEventListener('click', function() {
            const flightId = this.dataset.flightId;
            const details = document.getElementById(`details-${flightId}`);
            const arrow = this.querySelector('.arrow');
            
            if (details.style.display === 'block') {
                details.style.display = 'none';
                arrow.textContent = '▼';
            } else {
                details.style.display = 'block';
                arrow.textContent = '▲';
            }
        });
    });
    
    // Flight selection
    document.querySelectorAll('.select-flight-btn').forEach(button => {
        button.addEventListener('click', function() {
            const flight = JSON.parse(this.dataset.flight);
            showFlightModal(flight);
        });
    });
    
    // Modal functionality
    const modal = document.getElementById('flightModal');
    const closeBtn = document.getElementById('closeFlightModal');
    const cancelBtn = document.getElementById('cancelBooking');
    
    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    cancelBtn.addEventListener('click', () => modal.style.display = 'none');
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Confirm booking - redirect to payment
    document.getElementById('confirmBooking').addEventListener('click', function() {
        const selectedFlight = window.selectedFlightData;
        if (selectedFlight) {
            // Create a form to submit flight data to payment page
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../Flight/flight_payment.php';
            
            // Add flight data as hidden inputs
            const flightData = {
                flight_id: selectedFlight.flight_id,
                airline: selectedFlight.airline,
                from_city: selectedFlight.from_cityid,
                to_city: selectedFlight.to_cityid,
                departure_date: selectedFlight.departure_date,
                departure_time: selectedFlight.departure_time,
                arrival_time: selectedFlight.arrival_time,
                price_per_person: selectedFlight.price,
                passengers: <?php echo $passengers; ?>,
                class: selectedFlight.class || 'Economy'
            };
            
            for (const key in flightData) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = flightData[key];
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    });
    
    // Filter functionality
    setupFilters();
});

function showFlightModal(flight) {
    const modal = document.getElementById('flightModal');
    const flightInfo = document.getElementById('selectedFlightInfo');
    
    const cities = <?php echo json_encode($cities); ?>;
    const passengers = <?php echo $passengers; ?>;
    const basePrice = parseInt(flight.price);
    const subtotal = basePrice * passengers;
    const taxes = Math.floor(subtotal * 0.1); // 10% taxes
    const totalPrice = subtotal + taxes;
    
    // Store flight data globally for payment redirect
    window.selectedFlightData = flight;
    
    // Get class icons and display names
    const classIcons = {
        'Economy': '💺',
        'Business': '🛋️',
        'First Class': '👑'
    };
    
    const classDisplayNames = {
        'Economy': 'Economy Class',
        'Business': 'Business Class',
        'First Class': 'First Class'
    };
    
    flightInfo.innerHTML = `
        <div class="selected-flight">
            <div class="flight-header">
                <h4>${flight.airline}</h4>
                <span class="flight-number">FL-${flight.flight_id}</span>
                <span class="flight-class-badge">${classIcons[flight.class] || '💺'} ${classDisplayNames[flight.class] || flight.class}</span>
            </div>
            <div class="flight-route-modal">
                <div class="departure-modal">
                    <div class="time">${formatTime(flight.departure_time)}</div>
                    <div class="city">${cities[flight.from_cityid].name}</div>
                </div>
                <div class="arrow">→</div>
                <div class="arrival-modal">
                    <div class="time">${formatTime(flight.arrival_time)}</div>
                    <div class="city">${cities[flight.to_cityid].name}</div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('flightClass').innerHTML = `${classIcons[flight.class] || '💺'} ${classDisplayNames[flight.class] || flight.class}`;
    document.getElementById('basePrice').textContent = formatPrice(basePrice);
    document.getElementById('passengerCount').textContent = passengers + (passengers === 1 ? ' passenger' : ' passengers');
    document.getElementById('subtotal').textContent = formatPrice(subtotal);
    document.getElementById('taxes').textContent = formatPrice(taxes);
    document.getElementById('totalPrice').textContent = formatPrice(totalPrice);
    
    modal.style.display = 'flex';
}

function formatTime(time) {
    const date = new Date(`2000-01-01 ${time}`);
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit', 
        hour12: false 
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + ' VND';
}

function setupFilters() {
    // Price range filters
    const minPriceSlider = document.getElementById('minPrice');
    const maxPriceSlider = document.getElementById('maxPrice');
    const minPriceLabel = document.getElementById('minPriceLabel');
    const maxPriceLabel = document.getElementById('maxPriceLabel');
    
    function updatePriceLabels() {
        minPriceLabel.textContent = formatPrice(parseInt(minPriceSlider.value));
        maxPriceLabel.textContent = formatPrice(parseInt(maxPriceSlider.value));
        filterFlights();
    }
    
    minPriceSlider.addEventListener('input', updatePriceLabels);
    maxPriceSlider.addEventListener('input', updatePriceLabels);
    
    // Class filters
    document.querySelectorAll('.class-filter').forEach(checkbox => {
        checkbox.addEventListener('change', filterFlights);
    });
    
    // Airline filters
    document.querySelectorAll('.airline-filter').forEach(checkbox => {
        checkbox.addEventListener('change', filterFlights);
    });
    
    // Time filters
    document.querySelectorAll('.time-filter').forEach(checkbox => {
        checkbox.addEventListener('change', filterFlights);
    });
    
    // Clear filters
    document.querySelector('.clear-filters').addEventListener('click', function() {
        // Reset all filters
        minPriceSlider.value = 0;
        maxPriceSlider.value = 3000000;
        updatePriceLabels();
        
        document.querySelectorAll('.class-filter').forEach(cb => cb.checked = true);
        document.querySelectorAll('.airline-filter').forEach(cb => cb.checked = true);
        document.querySelectorAll('.time-filter').forEach(cb => cb.checked = true);
        
        filterFlights();
    });
}

function filterFlights() {
    const flightCards = document.querySelectorAll('.flight-card');
    const minPrice = parseInt(document.getElementById('minPrice').value);
    const maxPrice = parseInt(document.getElementById('maxPrice').value);
    
    const selectedClasses = Array.from(document.querySelectorAll('.class-filter:checked'))
        .map(cb => cb.value);
    
    const selectedAirlines = Array.from(document.querySelectorAll('.airline-filter:checked'))
        .map(cb => cb.value);
    
    const selectedTimes = Array.from(document.querySelectorAll('.time-filter:checked'))
        .map(cb => cb.value);
    
    flightCards.forEach(card => {
        const priceElement = card.querySelector('.price-amount');
        const price = parseInt(priceElement.textContent.replace(/[^\d]/g, ''));
        const airline = card.querySelector('.airline-name').textContent;
        const flightClass = card.querySelector('.flight-class').textContent.split(' ').slice(1).join(' '); // Remove icon
        const departureTime = card.querySelector('.departure .time').textContent;
        
        // Check price range
        const priceMatch = price >= minPrice && price <= maxPrice;
        
        // Check class
        const classMatch = selectedClasses.some(selectedClass => flightClass.includes(selectedClass));
        
        // Check airline
        const airlineMatch = selectedAirlines.includes(airline);
        
        // Check time
        const hour = parseInt(departureTime.split(':')[0]);
        let timeMatch = false;
        
        selectedTimes.forEach(timeSlot => {
            if (timeSlot === 'morning' && hour >= 6 && hour < 12) timeMatch = true;
            if (timeSlot === 'afternoon' && hour >= 12 && hour < 18) timeMatch = true;
            if (timeSlot === 'evening' && hour >= 18) timeMatch = true;
        });
        
        // Show/hide card based on filters
        if (priceMatch && classMatch && airlineMatch && timeMatch) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Sort functionality
document.getElementById('sortBy').addEventListener('change', function() {
    const sortBy = this.value;
    const flightsList = document.querySelector('.flights-list');
    const flightCards = Array.from(document.querySelectorAll('.flight-card'));
    
    flightCards.sort((a, b) => {
        switch (sortBy) {
            case 'price':
                const priceTextA = a.querySelector('.price-amount').textContent;
                const priceTextB = b.querySelector('.price-amount').textContent;
                // Extract numbers only and convert to integer
                const priceA = parseInt(priceTextA.replace(/[^\d]/g, ''));
                const priceB = parseInt(priceTextB.replace(/[^\d]/g, ''));
                return priceA - priceB;
                
            case 'time':
                const timeA = a.querySelector('.departure .time').textContent;
                const timeB = b.querySelector('.departure .time').textContent;
                // Convert time to minutes for proper comparison
                const minutesA = timeToMinutes(timeA);
                const minutesB = timeToMinutes(timeB);
                return minutesA - minutesB;
                
            case 'duration':
                const durationA = a.querySelector('.duration').textContent;
                const durationB = b.querySelector('.duration').textContent;
                // Convert duration to minutes for comparison
                const durationMinutesA = durationToMinutes(durationA);
                const durationMinutesB = durationToMinutes(durationB);
                return durationMinutesA - durationMinutesB;
                
            case 'airline':
                const airlineA = a.querySelector('.airline-name').textContent.toLowerCase();
                const airlineB = b.querySelector('.airline-name').textContent.toLowerCase();
                return airlineA.localeCompare(airlineB);
                
            case 'class':
                const classA = a.querySelector('.flight-class').textContent.toLowerCase();
                const classB = b.querySelector('.flight-class').textContent.toLowerCase();
                // Sort by class hierarchy: Economy < Business < First Class
                const classOrder = { 'economy': 1, 'business': 2, 'first': 3 };
                const orderA = classOrder[classA.split(' ')[1]] || 1;
                const orderB = classOrder[classB.split(' ')[1]] || 1;
                return orderA - orderB;
                
            default:
                return 0;
        }
    });
    
    // Clear and re-append sorted cards
    flightsList.innerHTML = '';
    flightCards.forEach(card => flightsList.appendChild(card));
});

// Helper function to convert time string to minutes
function timeToMinutes(timeString) {
    const [hours, minutes] = timeString.split(':').map(Number);
    return hours * 60 + minutes;
}

// Helper function to convert duration string to minutes
function durationToMinutes(durationString) {
    // Parse duration like "2h 30m" or "1h 45m"
    const hourMatch = durationString.match(/(\d+)h/);
    const minuteMatch = durationString.match(/(\d+)m/);
    
    const hours = hourMatch ? parseInt(hourMatch[1]) : 0;
    const minutes = minuteMatch ? parseInt(minuteMatch[1]) : 0;
    
    return hours * 60 + minutes;
}

document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('passenger_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\-\s]/g, '');
        });
    }
});

// Updated confirm booking function - replace the existing one in flight.php
document.getElementById('confirmBooking').addEventListener('click', function() {
    const selectedFlight = window.selectedFlightData;
    const passengerName = document.getElementById('passenger_name').value.trim();
    const passengerEmail = document.getElementById('passenger_email').value.trim();
    const passengerPhone = document.getElementById('passenger_phone').value.trim();
    
    // Validation
    if (!passengerName) {
        alert('Please enter passenger name');
        document.getElementById('passenger_name').focus();
        return;
    }
    
    if (!passengerEmail) {
        alert('Please enter email address');
        document.getElementById('passenger_email').focus();
        return;
    }
    
    if (!passengerPhone) {
        alert('Please enter contact number');
        document.getElementById('passenger_phone').focus();
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(passengerEmail)) {
        alert('Please enter a valid email address');
        document.getElementById('passenger_email').focus();
        return;
    }
    
    // Phone validation (basic)
    if (!passengerPhone.trim()) {
        alert('Please enter your phone number');
        document.getElementById('passenger_phone').focus();
        return;
    }
    
    if (selectedFlight) {
        // Create a form to submit flight data to payment page
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = './flight_payment.php'; // Updated path - same directory as flight.php
        
        // Add flight data as hidden inputs
        const flightData = {
            flight_id: selectedFlight.flight_id,
            airline: selectedFlight.airline,
            from_city: selectedFlight.from_cityid,
            to_city: selectedFlight.to_cityid,
            departure_date: selectedFlight.departure_date,
            departure_time: selectedFlight.departure_time,
            arrival_time: selectedFlight.arrival_time,
            price_per_person: selectedFlight.price,
            passengers: <?php echo $passengers; ?>,
            class: selectedFlight.class || 'Economy',
            passenger_name: passengerName,
            passenger_email: passengerEmail,
            passenger_phone: passengerPhone
        };
        
        for (const key in flightData) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = flightData[key];
            form.appendChild(input);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
});

</script>
</body>
</html>