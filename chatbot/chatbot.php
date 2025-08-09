<?php
// chatbot.php - Enhanced Professional, Database-Driven Chatbot API
header('Content-Type: application/json');
include '../dbconnect.php'; // Ensure this path is correct

// --- Helper Functions ---

/**
 * Generates the correct image path for a tour based on city and tour ID.
 * This now matches the logic from viewjourney.php exactly.
 */
function generateImagePath($cityString, $tourId) {
    // This mapping must match viewjourney.php exactly
    $imageMap = [
        'hcm' => [5, 6, 7, 8],
        'nhatrang' => [9, 10, 11, 12],
        'hue' => [13, 14, 15, 16],
        'phuyen' => [17, 18, 19, 20],
        'dalat' => [21, 22, 23, 24],
        'phuquoc' => [25, 26, 27, 28],
        'hoian' => [29, 30, 31, 32],
        'hagiang' => [33, 34, 35, 36],
        'taybac' => [1, 2, 3, 4]
    ];
    
    $imageIds = $imageMap[$cityString] ?? [1, 2, 3, 4]; // Default fallback
    $imageId = $imageIds[($tourId - 1) % count($imageIds)]; 
    
    return "../tourphotoID/{$imageId}.jpg";
}

/**
 * Extracts a known location and its ID from the user's query.
 * @param string $query The user's input string.
 * @param mysqli $conn The database connection.
 * @return array|null An array containing cityid and city name, or null if not found.
 */
function extractLocation($query, $conn) {
    $query = strtolower($query);
    $cities_sql = "SELECT cityid, city FROM cities";
    $result = $conn->query($cities_sql);
    $cities = $result->fetch_all(MYSQLI_ASSOC);

    foreach ($cities as $city) {
        $cityNameLower = strtolower($city['city']);
        $aliases = [$cityNameLower];
        if ($cityNameLower === 'ho chi minh city') {
            $aliases[] = 'hcmc';
            $aliases[] = 'saigon';
        }
        if ($cityNameLower === 'da lat') {
            $aliases[] = 'dalat';
        }

        foreach ($aliases as $alias) {
            if (strpos($query, $alias) !== false) {
                return ['id' => $city['cityid'], 'name' => $city['city']];
            }
        }
    }
    return null;
}

/**
 * Generates HTML for a list of tour cards.
 */
function generateTourCardsHTML($tours, $cityName) {
    $html = "<div class='bot-response-cards'>";
    $html .= "<h4>Here are some popular tours in " . htmlspecialchars($cityName) . ":</h4>";

    // Correct mapping from database city IDs to URL string
    $cityIdToString = [
        10 => 'taybac',     // Northern Vietnam
        11 => 'hcm',        // Ho Chi Minh City  
        12 => 'nhatrang',   // Nha Trang
        13 => 'central',    // Central Vietnam (not used in current tours)
        14 => 'phuyen',     // Phu Yen
        15 => 'dalat',      // Dalat
        16 => 'phuquoc',    // Phu Quoc
        17 => 'hoian',      // Hoi An
        18 => 'hagiang',    // Ha Giang
        19 => 'hue'         // Hue
    ];

    foreach ($tours as $tour) {
        $tour_id = htmlspecialchars($tour['tourid']);
        $city_id_db = $tour['cityid']; 
        $city_slug = $cityIdToString[$city_id_db] ?? 'hcm'; // Default fallback
        
        $imagePath = generateImagePath($city_slug, $tour['tourid']);

        // Calculate discount display (15% discount shown)
        $originalPrice = $tour['price_per_person'] / 0.85; // Reverse 15% discount
        
        $html .= "
        <a href='../Journey/tour_detail.php?tourid={$tour_id}&cityid={$city_slug}' class='bot-card-link' target='_blank'>
            <div class='bot-card'>
                <div class='bot-card-image'>
                    <img src='{$imagePath}' alt='Tour Image' onerror=\"this.src='../images/no-image-placeholder.jpg'\">
                    <div class='bot-discount-badge'>-15%</div>
                </div>
                <div class='bot-card-details'>
                    <div class='bot-card-title'>" . htmlspecialchars($tour['tour_name']) . "</div>
                    <div class='bot-card-price'>
                        <span class='current-price'>" . number_format($tour['price_per_person']) . " ₫</span>
                        <span class='original-price'>" . number_format($originalPrice) . " ₫</span>
                    </div>
                </div>
            </div>
        </a>";
    }
    $html .= "</div>";
    return $html;
}

/**
 * Generates HTML for a list of hotel cards.
 * @param array $hotels An array of hotel data from the database.
 * @param string $cityName The name of the city for the heading.
 * @return string The generated HTML.
 */
function generateHotelCardsHTML($hotels, $cityName) {
    $html = "<div class='bot-response-cards'>";
    $html .= "<h4>Here are some highly-rated hotels in " . htmlspecialchars($cityName) . ":</h4>";

    foreach ($hotels as $hotel) {
        $hotel_id = htmlspecialchars($hotel['hotelid']);
        $imagePath = "../hotelphotoID/{$hotel_id}.jpg";
        
        // Calculate discount display (7% discount shown, matching hoteldescription.php)
        $discountedPrice = $hotel['cost'] * 0.93; // 7% discount

        $html .= "
        <a href='../hotelinfo/hoteldescription.php?hotel_id={$hotel_id}' class='bot-card-link' target='_blank'>
            <div class='bot-card'>
                <div class='bot-card-image'>
                    <img src='{$imagePath}' alt='Hotel Image' onerror=\"this.src='../images/no-image-placeholder.jpg'\">
                    <div class='bot-discount-badge'>-7%</div>
                </div>
                <div class='bot-card-details'>
                    <div class='bot-card-title'>" . htmlspecialchars($hotel['hotel']) . "</div>
                    <div class='bot-card-rating'>" . str_repeat('⭐', $hotel['ratings']) . "</div>
                    <div class='bot-card-price'>
                        <span class='current-price'>" . number_format($discountedPrice) . " ₫</span>
                        <span class='original-price'>" . number_format($hotel['cost']) . " ₫</span>
                        <span class='per-night'> / night</span>
                    </div>
                </div>
            </div>
        </a>";
    }
    $html .= "</div>";
    return $html;
}

/**
 * NEW: Generates HTML for flight cards
 */
function generateFlightCardsHTML($cityName) {
    $html = "<div class='bot-response-cards'>";
    $html .= "<h4>Popular flight routes from " . htmlspecialchars($cityName) . ":</h4>";
    
    // Define proper flight routes based on the origin city
    $flightRoutes = [
        'Ho Chi Minh City' => [
            ['to' => 'Hanoi', 'price' => rand(1200000, 1800000), 'airline' => 'Vietnam Airlines', 'duration' => '2h 15m'],
            ['to' => 'Da Nang', 'price' => rand(900000, 1400000), 'airline' => 'VietJet Air', 'duration' => '1h 25m']
        ],
        'Hanoi' => [
            ['to' => 'Ho Chi Minh City', 'price' => rand(1200000, 1800000), 'airline' => 'Vietnam Airlines', 'duration' => '2h 15m'],
            ['to' => 'Da Nang', 'price' => rand(800000, 1300000), 'airline' => 'Bamboo Airways', 'duration' => '1h 20m']
        ],
        'Nha Trang' => [
            ['to' => 'Ho Chi Minh City', 'price' => rand(900000, 1400000), 'airline' => 'VietJet Air', 'duration' => '1h 15m'],
            ['to' => 'Hanoi', 'price' => rand(1100000, 1600000), 'airline' => 'Vietnam Airlines', 'duration' => '1h 45m']
        ],
        'Da Nang' => [
            ['to' => 'Ho Chi Minh City', 'price' => rand(900000, 1400000), 'airline' => 'VietJet Air', 'duration' => '1h 25m'],
            ['to' => 'Hanoi', 'price' => rand(800000, 1300000), 'airline' => 'Bamboo Airways', 'duration' => '1h 20m']
        ],
        'Phu Quoc' => [
            ['to' => 'Ho Chi Minh City', 'price' => rand(800000, 1200000), 'airline' => 'VietJet Air', 'duration' => '1h 10m'],
            ['to' => 'Hanoi', 'price' => rand(1300000, 1700000), 'airline' => 'Vietnam Airlines', 'duration' => '2h 35m']
        ]
    ];
    
    // Get flights for the current city, or use default routes
    $flights = $flightRoutes[$cityName] ?? [
        ['to' => 'Ho Chi Minh City', 'price' => rand(900000, 1400000), 'airline' => 'VietJet Air', 'duration' => '1h 30m'],
        ['to' => 'Hanoi', 'price' => rand(1100000, 1600000), 'airline' => 'Vietnam Airlines', 'duration' => '2h 00m']
    ];

    foreach ($flights as $flight) {
        $html .= "
        <a href='../Flight/flight.php' class='bot-card-link' target='_blank'>
            <div class='bot-card flight-card'>
                <div class='bot-card-header'>
                    <div class='airline-info'>
                        <span class='airline-name'>" . htmlspecialchars($flight['airline']) . "</span>
                        <span class='flight-duration'>" . $flight['duration'] . "</span>
                    </div>
                </div>
                <div class='bot-card-details'>
                    <div class='flight-route'>
                        <span class='route-from'>" . htmlspecialchars($cityName) . "</span>
                        <span class='route-arrow'>→</span>
                        <span class='route-to'>" . htmlspecialchars($flight['to']) . "</span>
                    </div>
                    <div class='bot-card-price'>
                        <span class='current-price'>" . number_format($flight['price']) . " ₫</span>
                        <span class='per-person'>per person</span>
                    </div>
                </div>
            </div>
        </a>";
    }
    
    $html .= "</div>";
    return $html;
}

/**
 * NEW: Check if query mentions unavailable destinations
 */
function checkUnavailableDestinations($query, $conn) {
    $unavailableDestinations = [
        'can tho', 'cantho', 'mekong delta',
        'vung tau', 'vungtau', 
        'cat ba', 'catba',
        'mui ne', 'muine', 'phan thiet',
        'cao bang', 'caobang',
        'sa pa', 'sapa'
    ];
    
    $query = strtolower($query);
    foreach ($unavailableDestinations as $destination) {
        if (strpos($query, $destination) !== false) {
            return $destination;
        }
    }
    return null;
}

/**
 * NEW: Generate alternative suggestions for unavailable destinations
 */
function generateAlternativeSuggestions($unavailableDestination, $conn) {
    $alternatives = [
        'can tho' => [11, 16], // Ho Chi Minh City, Phu Quoc
        'cantho' => [11, 16],
        'mekong delta' => [11, 16],
        'vung tau' => [12, 16], // Nha Trang, Phu Quoc
        'vungtau' => [12, 16],
        'cat ba' => [18, 19], // Ha Giang, Hue
        'catba' => [18, 19],
        'mui ne' => [12, 14], // Nha Trang, Phu Yen
        'muine' => [12, 14],
        'phan thiet' => [12, 14],
        'cao bang' => [18, 10], // Ha Giang, Northern Vietnam
        'caobang' => [18, 10],
        'sa pa' => [18, 10], // Ha Giang, Northern Vietnam
        'sapa' => [18, 10]
    ];

    $suggestionIds = $alternatives[$unavailableDestination] ?? [11, 12]; // Default to HCM and Nha Trang
    
    $placeholders = str_repeat('?,', count($suggestionIds) - 1) . '?';
    $stmt = $conn->prepare("SELECT * FROM tours WHERE cityid IN ($placeholders) ORDER BY price_per_person LIMIT 4");
    $stmt->bind_param(str_repeat('i', count($suggestionIds)), ...$suggestionIds);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// --- Main Logic ---

$input = json_decode(file_get_contents('php://input'), true);
$userQuery = isset($input['query']) ? trim(strtolower($input['query'])) : '';

if (empty($userQuery)) {
    echo json_encode(['reply' => "Please ask a question.", 'isHTML' => false]);
    exit;
}

$location = extractLocation($userQuery, $conn);
$intent = 'unknown';

// Determine intent based on keywords
if (strpos($userQuery, 'tour') !== false || strpos($userQuery, 'package') !== false || strpos($userQuery, 'journey') !== false) {
    $intent = 'find_tour';
} elseif (strpos($userQuery, 'hotel') !== false || strpos($userQuery, 'accommodation') !== false || strpos($userQuery, 'stay') !== false) {
    $intent = 'find_hotel';
} elseif (strpos($userQuery, 'flight') !== false || strpos($userQuery, 'plane') !== false || strpos($userQuery, 'airline') !== false || strpos($userQuery, 'fly') !== false) {
    $intent = 'find_flight';
} elseif (strpos($userQuery, 'discount') !== false || strpos($userQuery, 'promotion') !== false || strpos($userQuery, 'offer') !== false || strpos($userQuery, 'deal') !== false || strpos($userQuery, 'sale') !== false) {
    $intent = 'discount_inquiry';
} elseif (in_array($userQuery, ['hi', 'hello', 'hey'])) {
    $intent = 'greeting';
}

// Check for unavailable destinations
$unavailableDestination = checkUnavailableDestinations($userQuery, $conn);

$response = [];

switch ($intent) {
    case 'find_tour':
        if ($unavailableDestination) {
            $alternatives = generateAlternativeSuggestions($unavailableDestination, $conn);
            $suggestion_html = "";
            
            if (!empty($alternatives)) {
                // Group tours by city for better presentation
                $toursByCity = [];
                foreach ($alternatives as $tour) {
                    $cityNames = [
                        10 => 'Northern Vietnam', 11 => 'Ho Chi Minh City', 12 => 'Nha Trang',
                        13 => 'Central Vietnam', 14 => 'Phu Yen', 15 => 'Dalat',
                        16 => 'Phu Quoc', 17 => 'Hoi An', 18 => 'Ha Giang', 19 => 'Hue'
                    ];
                    $cityName = $cityNames[$tour['cityid']] ?? 'Unknown';
                    $toursByCity[$cityName][] = $tour;
                }
                
                $suggestion_html .= "<div class='bot-response-cards'>";
                $suggestion_html .= "<h4>We don't have tours to " . ucfirst($unavailableDestination) . " yet. Here are some amazing alternatives:</h4>";
                
                foreach ($toursByCity as $cityName => $cityTours) {
                    $suggestion_html .= generateTourCardsHTML(array_slice($cityTours, 0, 2), $cityName);
                }
                $suggestion_html .= "</div>";
            }
            
            $response['reply'] = "<p class='suggestion-text'>We don't currently offer tours to " . ucfirst($unavailableDestination) . ". However, you might love these incredible destinations with similar attractions!</p>" . $suggestion_html;
            $response['isHTML'] = true;
            
        } elseif ($location) {
            $stmt = $conn->prepare("SELECT * FROM tours WHERE cityid = ? ORDER BY price_per_person LIMIT 4");
            $stmt->bind_param("i", $location['id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $tours = $result->fetch_all(MYSQLI_ASSOC);
                $response['reply'] = generateTourCardsHTML($tours, $location['name']);
                $response['isHTML'] = true;
            } else {
                $suggestionCityId = 11; // Suggest Ho Chi Minh City
                $stmt = $conn->prepare("SELECT * FROM tours WHERE cityid = ? ORDER BY price_per_person LIMIT 2");
                $stmt->bind_param("i", $suggestionCityId);
                $stmt->execute();
                $suggested_tours = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                $suggestion_html = generateTourCardsHTML($suggested_tours, 'Ho Chi Minh City');
                $response['reply'] = "<p class='suggestion-text'>We don't have tours available for " . htmlspecialchars($location['name']) . " yet. However, you might enjoy these popular tours in nearby Ho Chi Minh City!</p>" . $suggestion_html;
                $response['isHTML'] = true;
            }
        } else {
            $response['reply'] = "Certainly! Which city or destination are you interested in for tours? We offer amazing packages to Ho Chi Minh City, Nha Trang, Phu Quoc, Hoi An, Ha Giang, Hue, Dalat, and Phu Yen.";
            $response['isHTML'] = false;
        }
        break;

    case 'find_hotel':
        if ($unavailableDestination) {
            $response['reply'] = "We don't currently have hotel listings for " . ucfirst($unavailableDestination) . ". However, we offer excellent accommodations in Ho Chi Minh City, Nha Trang, Phu Quoc, Hoi An, Ha Giang, Hue, Dalat, and Phu Yen. Would you like to explore hotels in any of these destinations?";
            $response['isHTML'] = false;
        } elseif ($location) {
            $stmt = $conn->prepare("SELECT * FROM hotels WHERE cityid = ? ORDER BY ratings DESC LIMIT 4");
            $stmt->bind_param("i", $location['id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $hotels = $result->fetch_all(MYSQLI_ASSOC);
                $response['reply'] = generateHotelCardsHTML($hotels, $location['name']);
                $response['isHTML'] = true;
            } else {
                $response['reply'] = "I'm sorry, I couldn't find any hotels in " . htmlspecialchars($location['name']) . ". Would you like to search in another city like Ho Chi Minh City, Nha Trang, or Phu Quoc?";
                $response['isHTML'] = false;
            }
        } else {
            $response['reply'] = "Of course! To find the best hotels, which city are you planning to visit? We have accommodations in Ho Chi Minh City, Nha Trang, Phu Quoc, Hoi An, Ha Giang, Hue, Dalat, and Phu Yen.";
            $response['isHTML'] = false;
        }
        break;
        
    case 'find_flight':
        if ($location) {
            $flightCards = generateFlightCardsHTML($location['name']);
            $response['reply'] = $flightCards . "<p style='margin-top: 15px;'>🔍 <a href='../Flight/flight.php' style='color: #007bff; text-decoration: underline;' target='_blank'>Search more flights and book your tickets here!</a></p>";
            $response['isHTML'] = true;
        } else {
            $response['reply'] = "I can help you find flights! We offer domestic flights connecting major Vietnamese cities including Ho Chi Minh City, Hanoi, Da Nang, Nha Trang, and Phu Quoc. Which route are you interested in? You can also <a href='../Flight/flight.php' style='color: #007bff; text-decoration: underline;' target='_blank'>search and book flights directly here</a>.";
            $response['isHTML'] = true;
        }
        break;
        
    case 'discount_inquiry':
        $discountInfo = "
        <div class='discount-info'>
            <h4>🎉 Current Promotions & Discounts:</h4>
            <div style='margin: 15px 0;'>
                <h5>🏖️ Tour Packages:</h5>
                <p>• <strong>15% OFF</strong> on all tour packages</p>
                <p>• Special seasonal rates available</p>
                <p>• Group discounts for 4+ travelers</p>
            </div>
            <div style='margin: 15px 0;'>
                <h5>🏨 Hotel Bookings:</h5>
                <p>• <strong>7% OFF</strong> on all hotel reservations</p>
                <p>• Extended stay discounts available</p>
                <p>• Premium amenities included</p>
            </div>
            <div style='margin: 15px 0;'>
                <h5>✈️ Flight Bookings:</h5>
                <p>• Competitive prices on domestic flights</p>
                <p>• Business class upgrades available</p>
                <p>• Round-trip package deals</p>
            </div>
            <div style='margin-top: 20px; padding: 10px; background: #f8f9fa; border-left: 4px solid #28a745;'>
                <p><strong>💡 Tip:</strong> Book your complete package (tour + hotel + flight) for maximum savings!</p>
            </div>
        </div>";
        
        $response['reply'] = $discountInfo;
        $response['isHTML'] = true;
        break;
        
    case 'greeting':
        $response['reply'] = "Hello! I'm VietTransit's AI assistant. I can help you find tours, hotels, and flights across Vietnam. We currently offer services in Ho Chi Minh City, Nha Trang, Phu Quoc, Hoi An, Ha Giang, Hue, Dalat, and Phu Yen. What would you like to explore today?";
        $response['isHTML'] = false;
        break;

    default:
        // Check if query mentions unavailable destination without specific intent
        if ($unavailableDestination) {
            $response['reply'] = "We don't currently offer services to " . ucfirst($unavailableDestination) . ". However, we have amazing tours, hotels, and flights available in Ho Chi Minh City, Nha Trang, Phu Quoc, Hoi An, Ha Giang, Hue, Dalat, and Phu Yen. Would you like to explore any of these destinations?";
            $response['isHTML'] = false;
        } else {
            $chatbotData = json_decode(file_get_contents('chatbot.json'), true);
            $defaultResponses = $chatbotData['default_responses'];
            $response['reply'] = $defaultResponses[array_rand($defaultResponses)];
            $response['isHTML'] = false;
        }
        break;
}

echo json_encode($response);
$conn->close();
?>