<?php

require_once "admin_check.php";
require_once "db.php";

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_student.php");
    exit();
}

$student_id = intval($_GET['id']);

// Check whether the student exists
$sql = "SELECT id, fullname FROM `user` WHERE id = ? AND role = 'student'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: manage_student.php?message=not_found");
    exit();
}

$student = $result->fetch_assoc();
$stmt->close();


// Delete student
$sql = "DELETE FROM `user` WHERE id = ? AND role = 'student'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);

if ($stmt->execute()) {
    $stmt->close();

    header("Location: manage_student.php?message=deleted");
    exit();

} else {
    $stmt->close();

    header("Location: manage_student.php?message=error");
    exit();
}
?>