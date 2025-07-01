<?php

if (isset($_POST["submit"])) {

    $email = $_POST["email"];
    $username = $_POST["uid"];
    $pwd = $_POST["pwd"];
    $pwdconfirm = $_POST["pwdconfirm"];

    require_once '../dbconnect.php';
    require_once 'functions-inc.php';

    // Function to show alert and stop script
    function showAlertAndStop($message) {
        echo "<script>alert('$message'); window.history.back();</script>";
        exit();
    }

    if (emptyInputSignup($email, $username, $pwd, $pwdconfirm) !== false) {
        showAlertAndStop("Please fill in all fields.");
    }

    if (invalidUid($username) !== false) {
        showAlertAndStop("Invalid username format.");
    }

    if (invalidEmail($email) !== false) {
        showAlertAndStop("Invalid email address.");
    }

    if (pwdMatch($pwd, $pwdconfirm) !== false) {
        showAlertAndStop("Passwords do not match.");
    }

    if (uidExists($conn, $username, $email) !== false) {
        showAlertAndStop("Username or email is already taken.");
    }

    createUser($conn, $email, $username, $pwd);
    echo "<script>alert('Account created successfully!'); window.location.href = '../mainLogin/animlogin.php';</script>";
    exit();

} else {
    // Direct access to script
    echo "<script>alert('Invalid access.'); window.location.href = '../mainLogin/animlogin.php';</script>";
    exit();
}
