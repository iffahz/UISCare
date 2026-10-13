<?php

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "counseling_booking_system"
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$fullname = "System Administrator";
$email = "admin@gmail.com";
$password = "admin123";

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$stmt = $conn->prepare("
    INSERT INTO `user`
    (
        fullname,
        email,
        password,
        role,
        status
    )
    VALUES
    (?, ?, ?, 'admin', 'active')
");

$stmt->bind_param(
    "sss",
    $fullname,
    $email,
    $hashed_password
);

if ($stmt->execute()) {

    echo "Admin account created successfully.";

} else {

    echo "Error: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>