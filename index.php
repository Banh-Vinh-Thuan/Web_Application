<?php
require_once './lib/flight/Flight.php';
require_once 'dbconnect.php'; // Include dbconnect.php first

Flight::map('db', function() {
    $conn = new mysqli('localhost', 'root', '4444', 'travelscapes'); // Adjust to your config
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    return $conn;
});

// Set views path (optional, for templates)

Flight::path(__DIR__.'/../');


// Define routes for tour-related files
Flight::route('GET /tour/booking', function() {
    require_once 'controllers/booktour_form.php'; // Handles GET to show tour booking form
});

Flight::route('POST /tour/booking/submit', function() {
    require_once 'controllers/booktour.php'; // Handles POST to process tour booking
});
Flight::route('GET /admin/tour/bookings', function() {
    require_once 'admin/adminviewtourbooking.php';
});


Flight::route('GET /payment/tour/redirect', function() {
    require_once 'payment_handler/tour_redirect.php';
});

Flight::route('POST /payment/tour/ipn', function() {
    require_once 'payment_handler/tour_ipn.php';
});

// Define routes for hotel-related files

Flight::route('GET /hotel/booking', function() {
    require_once 'controllers/bookhotel_form.php'; // Handles GET to show hotel booking form
});

Flight::route('POST /hotel/booking/submit', function() {
    require_once 'controllers/booking.php'; // Handles POST to process hotel booking
});

Flight::route('GET /payment/hotel/redirect', function() {
    require_once 'payment_handler/hotel_redirect.php';
});

Flight::route('POST /payment/hotel/ipn', function() {
    require_once 'payment_handler/hotel_ipn.php';
});

// Default route
Flight::route('/', function() {
    require_once 'home.php';
});

// 404 handler
Flight::map('notFound', function() {
    echo '404 - Page not found';
});

Flight::start();
?>