<?php

require_once "student_check.php";
require_once "db.php";

$student_id = $_SESSION['user_id'];

$appointment_id = intval(
    $_GET['id'] ?? 0
);


$stmt = $conn->prepare("
    SELECT
        a.appointment_date,
        a.appointment_time,
        a.reason,
        a.status,
        c.fullname AS counselor_name

    FROM appointment a

    INNER JOIN `user` c
        ON a.counselor_user_id = c.id

    WHERE a.id = ?
    AND a.student_user_id = ?
");

$stmt->bind_param(
    "ii",
    $appointment_id,
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();

$appointment = $result->fetch_assoc();


if (!$appointment) {

    die("Appointment not found.");

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Appointment Details</title>

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

.btn {
    display: inline-block;
    padding: 10px 15px;
    margin-top: 20px;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    background: #607d8b;
}

.cancel {
    background: #e53935;
}

</style>

</head>

<body>

<div class="main">

<div class="card">

<h1>Appointment Details</h1>


<div class="row">

<span class="label">
Counselor:
</span>

<?php echo htmlspecialchars(
    $appointment['counselor_name']
); ?>

</div>


<div class="row">

<span class="label">
Date:
</span>

<?php echo date(
    "d M Y",
    strtotime($appointment['appointment_date'])
); ?>

</div>


<div class="row">

<span class="label">
Time:
</span>

<?php echo date(
    "h:i A",
    strtotime($appointment['appointment_time'])
); ?>

</div>


<div class="row">

<span class="label">
Reason:
</span>

<?php echo htmlspecialchars(
    $appointment['reason']
); ?>

</div>


<div class="row">

<span class="label">
Status:
</span>

<?php echo ucfirst(
    $appointment['status']
); ?>

</div>


<a
    href="my_appointments.php"
    class="btn">

    Back

</a>


<?php if (
    $appointment['status'] === 'pending' ||
    $appointment['status'] === 'approved'
): ?>

<a
    href="cancel_appointment.php?id=<?php echo $appointment_id; ?>"
    class="btn cancel"
    onclick="return confirm('Cancel this appointment?');">

    Cancel Appointment

</a>

<?php endif; ?>


</div>

</div>

</body>

</html>