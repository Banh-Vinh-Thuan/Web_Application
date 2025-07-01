<?php
include '../../dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hotelid = $_POST["hotelid"];
    $hotel = $_POST["hotel"];
    $cityid = $_POST["cityid"];
    $cost = $_POST["cost"];
    $amenities = $_POST["amenities"];
    $ratings = $_POST["ratings"];

    $stmt = $conn->prepare("UPDATE hotels SET hotel=?, cityid=?, cost=?, amenities=?, ratings=? WHERE hotelid=?");
    $stmt->bind_param("sidsii", $hotel, $cityid, $cost, $amenities, $ratings, $hotelid);
    $stmt->execute();

    header("Location: ../adminviewhotel.php");
    exit();
}
?>