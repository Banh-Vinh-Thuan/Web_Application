<?php

function emptyInputSignup($email, $username, $pwd, $pwdconfirm) {
    return empty($email) || empty($username) || empty($pwd) || empty($pwdconfirm);
}

function invalidUid($username) {
    return !preg_match("/^[a-zA-Z0-9]*$/", $username);
}

function invalidEmail($email) {
    return !filter_var($email, FILTER_VALIDATE_EMAIL);
}

function pwdMatch($pwd, $pwdconfirm) {
    return $pwd !== $pwdconfirm;
}

function uidExists($conn, $usernameOrEmail1, $usernameOrEmail2) {
    $sql = "SELECT * FROM login WHERE usersuid = ? OR usersEmail = ?;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("location: login.php?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $usernameOrEmail1, $usernameOrEmail2);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($resultData);

    mysqli_stmt_close($stmt);
    return $row ? $row : false;
}

function createUser($conn, $email, $username, $pwd) {
    $sql = "INSERT INTO login (usersEmail, usersuid, userspwd) VALUES (?, ?, ?);";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("location: login.php?error=stmtfailed");
        exit();
    }

    $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "sss", $email, $username, $hashedPwd);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("location: login.php?error=none");
    exit();
}

function emptyInputLogin($username, $pwd) {
    return empty($username) || empty($pwd);
}

function loginUser($conn, $username, $pwd) {
    $uidExists = uidExists($conn, $username, $username);

    if (!$uidExists) {
        header("location: login.php?error=invalidcredentials");
        exit();
    }

    if (!password_verify($pwd, $uidExists["userspwd"])) {
        header("location: login.php?error=wrongpassword");
        exit();
    }

    session_start();
    $_SESSION["usersid"] = $uidExists["usersid"];
    $_SESSION["usersuid"] = $uidExists["usersuid"];
    $_SESSION["username"] = $uidExists["usersuid"]; // ← THÊM DÒNG NÀY
    header("location: loggedinhome.php");
    exit();
}
