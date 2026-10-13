<?php

require_once "admin_check.php";
require_once "db.php";


// Check ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_counselor.php");
    exit();
}

$id = intval($_GET['id']);


// Check counselor exists
$check = $conn->prepare("
    SELECT id
    FROM `user`
    WHERE id = ?
    AND role = 'counselor'
");

$check->bind_param("i", $id);
$check->execute();

$result = $check->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_counselor.php");
    exit();
}


// Delete counselor
$stmt = $conn->prepare("
    DELETE FROM `user`
    WHERE id = ?
    AND role = 'counselor'
");

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    header("Location: manage_counselor.php");
    exit();

} else {

    echo "Failed to delete counselor.";

}

?>