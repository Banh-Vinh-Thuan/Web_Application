<?php
include '../../dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tourid = $_POST["tourid"];

    $stmt = $conn->prepare("DELETE FROM tours WHERE tourid=?");
    $stmt->bind_param("i", $tourid);
    
    if ($stmt->execute()) {
        header("Location: ../adminviewtour.php?success=deleted");
    } else {
        header("Location: ../adminviewtour.php?error=delete_failed");
    }
    
    $stmt->close();
    $conn->close();
    exit();
}
?>  