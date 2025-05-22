<?php
session_start();
require_once '../dbconnect.php';

if (!isset($_SESSION["usersid"])) {
    header("Location: loggedinhome.php");
    exit();
}

if (!isset($_GET['cityid'])) {
    header("Location: index.php?error=no_city_selected");
    exit();
}

$userid = $_SESSION["usersid"];
$cityid = intval($_GET['cityid']);

// Kiểm tra xem đã yêu thích chưa
$sql_check = "SELECT * FROM favorites WHERE usersid = ? AND cityid = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $userid, $cityid);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result) > 0) {
    // Nếu đã yêu thích → XÓA
    $sql_delete = "DELETE FROM favorites WHERE usersid = ? AND cityid = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "ii", $userid, $cityid);
    mysqli_stmt_execute($stmt_delete);
    mysqli_stmt_close($stmt_delete);

    header("Location: loggedinhome.php?message=removed");
    exit();
} else {
    // Nếu chưa yêu thích → THÊM
    $sql_insert = "INSERT INTO favorites (usersid, cityid) VALUES (?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "ii", $userid, $cityid);

    if (mysqli_stmt_execute($stmt_insert)) {
        header("Location: loggedinhome.php?message=added");
    } else {
        header("Location: loggedinhome.php?error=insert_failed");
    }
    mysqli_stmt_close($stmt_insert);
}

mysqli_stmt_close($stmt_check);
mysqli_close($conn);
?>
