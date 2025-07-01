<?php
include '../../dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tourid = $_POST["tourid"];
    $cityid = $_POST["cityid"];
    $tour_name = $_POST["tour_name"];
    $description = $_POST["description"];
    $duration_days = $_POST["duration_days"];
    $price_per_person = $_POST["price_per_person"];
    $season = $_POST["season"];

    $stmt = $conn->prepare("UPDATE tours SET cityid=?, tour_name=?, description=?, duration_days=?, price_per_person=?, season=? WHERE tourid=?");
    $stmt->bind_param("ississi", $cityid, $tour_name, $description, $duration_days, $price_per_person, $season, $tourid);
    $stmt->execute();

    header("Location: ../adminviewtour.php");
    exit();
}
?>