<?php

session_start();


// =========================================
// CHECK LOGIN
// =========================================

if (!isset($_SESSION['user_id'])) {

    header("Location: /COUNSELING/login.php");
    exit();

}


// =========================================
// CHECK ROLE
// =========================================

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'counselor'
) {

    header("Location: /COUNSELING/login.php");
    exit();

}

?>