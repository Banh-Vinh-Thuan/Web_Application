<?php
require_once __DIR__ . '/lib/flight/Flight.php';

$serverName = "localhost";
$dbUsername = "root";
$dbPassword = "4444";
$dbName = "travelscapes";

$conn = mysqli_connect($serverName, $dbUsername, $dbPassword, $dbName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

Flight::set('db', $conn);

?>
