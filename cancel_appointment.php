<?php

require_once "student_check.php";
require_once "db.php";

$student_id = $_SESSION['user_id'];

$appointment_id = intval(
    $_GET['id'] ?? 0
);


$stmt = $conn->prepare("
    UPDATE appointment
    SET status = 'cancelled'
    WHERE id = ?
    AND student_user_id = ?
    AND status IN ('pending', 'approved')
");

$stmt->bind_param(
    "ii",
    $appointment_id,
    $student_id
);

$stmt->execute();


header(
    "Location: my_appointments.php"
);

exit();

?>