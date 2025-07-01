<?php
include '../../dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cityid = $_POST["cityid"];
    $tour_name = $_POST["tour_name"];
    $description = $_POST["description"];
    $duration_days = $_POST["duration_days"];
    $price_per_person = $_POST["price_per_person"];
    $season = $_POST["season"];

    $stmt = $conn->prepare("INSERT INTO tours (cityid, tour_name, description, duration_days, price_per_person, season) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $cityid, $tour_name, $description, $duration_days, $price_per_person, $season);
    $stmt->execute();

    header("Location: ../adminviewtour.php");
    exit();
}
?>