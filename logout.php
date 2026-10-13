<?php

session_start();

// Buang semua session
$_SESSION = array();

// Hapuskan session
session_destroy();

// Kembali ke login
header("Location: login.php");
exit();

?>