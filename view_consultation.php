<?php

require_once "student_check.php";
require_once "../db.php";

$student_id = $_SESSION['user_id'];

$consultation_id = intval(
    $_GET['id'] ?? 0
);


$stmt = $conn->prepare("
    SELECT
        c.notes,
        c.created_at,
        u.fullname AS counselor_name,
        a.appointment_date

    FROM consultation c

    INNER JOIN `user` u
        ON c.counselor_user_id = u.id

    LEFT JOIN appointment a
        ON c.appointment_id = a.id

    WHERE c.id = ?
    AND c.student_user_id = ?
");

$stmt->bind_param(
    "ii",
    $consultation_id,
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();

$consultation = $result->fetch_assoc();


if (!$consultation) {

    die("Consultation record not found.");

}

?>

<!DOCTYPE html>
<html>

<head>

<title>View Consultation</title>

<style>

body {
    margin: 0;
    font-family: Arial;
    background: #dbeafe;
}

.main {
    margin-left: 250px;
    padding: 30px;
}

.card {
    background: white;
    padding: 30px;
    max-width: 700px;
    border-radius: 10px;
}

.row {
    padding: 15px 0;
    border-bottom: 1px solid #ddd;
}

.label {
    font-weight: bold;
    color: #0f4c81;
}

.notes {
    background: #f1f5f9;
    padding: 20px;
    margin-top: 10px;
    border-radius: 8px;
    line-height: 1.6;
}

.btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 16px;
    background: #607d8b;
    color: white;
    text-decoration: none;
    border-radius: 6px;
}

</style>

</head>

<body>

<?php include "student_sidebar.php"; ?>


<div class="main">

<div class="card">

<h1>Consultation Details</h1>


<div class="row">

<span class="label">
Counselor:
</span>

<?php echo htmlspecialchars(
    $consultation['counselor_name']
); ?>

</div>


<div class="row">

<span class="label">
Date:
</span>

<?php

if (!empty($consultation['appointment_date'])) {

    echo date(
        "d M Y",
        strtotime(
            $consultation['appointment_date']
        )
    );

} else {

    echo "-";

}

?>

</div>


<div class="row">

<span class="label">
Notes:
</span>

<div class="notes">

<?php echo nl2br(
    htmlspecialchars(
        $consultation['notes'] ?? ''
    )
); ?>

</div>

</div>


<div class="row">

<span class="label">
Date Recorded:
</span>

<?php

echo !empty($consultation['created_at'])
    ? date(
        "d M Y h:i A",
        strtotime(
            $consultation['created_at']
        )
    )
    : "-";

?>

</div>


<a
    href="consultation_records.php"
    class="btn">

    Back

</a>

</div>

</div>

</body>

</html>