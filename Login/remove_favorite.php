
<?php
session_start();
require_once '../dbconnect.php';


if (!isset($_SESSION["usersid"]) || !isset($_POST["cityid"])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION["usersid"];
$cityid = intval($_POST["cityid"]);

$sql = "DELETE FROM favorites WHERE usersid = ? AND cityid = ?";
$stmt = mysqli_stmt_init($conn);

if (!mysqli_stmt_prepare($stmt, $sql)) {
    echo "Lỗi khi xóa thành phố yêu thích.";
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $userid, $cityid);
mysqli_stmt_execute($stmt);

header("Location: profile.php"); // hoặc trang bạn đang dùng để hiển thị
exit();
?>
=======
<?php
session_start();
require_once 'dbh-inc.php';

if (!isset($_SESSION["usersid"]) || !isset($_POST["cityid"])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION["usersid"];
$cityid = intval($_POST["cityid"]);

$sql = "DELETE FROM favorites WHERE usersid = ? AND cityid = ?";
$stmt = mysqli_stmt_init($conn);

if (!mysqli_stmt_prepare($stmt, $sql)) {
    echo "Lỗi khi xóa thành phố yêu thích.";
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $userid, $cityid);
mysqli_stmt_execute($stmt);

header("Location: profile.php"); // hoặc trang bạn đang dùng để hiển thị
exit();
?>

