<?php
include '../../dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hotel = $_POST["hotel"];
    $cityid = $_POST["cityid"];
    $cost = $_POST["cost"];
    $amenities = $_POST["amenities"];
    $ratings = $_POST["ratings"];

    $stmt = $conn->prepare("INSERT INTO hotels (hotel, cityid, cost, amenities, ratings) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sidsis", $hotel, $cityid, $cost, $amenities, $ratings);
    $stmt->execute();

    header("Location: ../adminviewhotel.php");
    exit();
}
?>