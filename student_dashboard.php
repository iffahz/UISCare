<?php

require_once "student_check.php";
require_once "db.php";

$student_id = $_SESSION['user_id'];


// =========================================
// GET STUDENT NAME
// =========================================

$stmt = $conn->prepare("
    SELECT 
        id,
        student_id,
        fullname
    FROM `user`
    WHERE id = ?
    AND role = 'student'
");

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();

$student_name = $student['fullname'] ?? 'Student';


// =========================================
// UPCOMING APPOINTMENTS
// =========================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM appointment
    WHERE student_user_id = ?
    AND appointment_date >= CURDATE()
    AND status = 'approved'
");

$stmt->bind_param("i", $student_id);
$stmt->execute();

$upcoming = $stmt->get_result()->fetch_assoc()['total'];


// =========================================
// PENDING APPOINTMENTS
// =========================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM appointment
    WHERE student_user_id = ?
    AND status = 'pending'
");

$stmt->bind_param("i", $student_id);
$stmt->execute();

$pending = $stmt->get_result()->fetch_assoc()['total'];


// =========================================
// COMPLETED CONSULTATIONS
// =========================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM consultation c
    INNER JOIN appointment a
        ON c.appointment_id = a.id
    WHERE a.student_user_id = ?
");

$stmt->bind_param("i", $student_id);
$stmt->execute();

$completed = $stmt->get_result()->fetch_assoc()['total'];


// =========================================
// TOTAL APPOINTMENTS
// =========================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM appointment
    WHERE student_user_id = ?
    AND status NOT IN ('cancelled', 'rejected')
");

$stmt->bind_param("i", $student_id);
$stmt->execute();

$total_appointments = $stmt->get_result()->fetch_assoc()['total'];


// =========================================
// GET NEXT APPOINTMENT
// =========================================

$stmt = $conn->prepare("
    SELECT
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.reason,
        a.status,
        c.fullname AS counselor_name
    FROM appointment a
    INNER JOIN `user` c
        ON a.counselor_user_id = c.id
    WHERE a.student_user_id = ?
    AND a.status = 'approved'
    AND (
        a.appointment_date > CURDATE()
        OR (
            a.appointment_date = CURDATE()
            AND a.appointment_time >= CURTIME()
        )
    )
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 1
");

$stmt->bind_param("i", $student_id);
$stmt->execute();

$next_appointment = $stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Student Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #dbeafe;
        }

        /* =========================
                SIDEBAR
        ========================= */

        .navbar {
            position: fixed;
            left: 0;
            top: 0;
            width: 220px;
            height: 100vh;
            background: #0f4c81;
            padding: 25px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 1000;
        }


        /* LOGO */

        .logo {
            margin-bottom: 35px;
        }

        .logo img {
            width: 150px;
        }


        /* MENU */

        .menu {
            width: 100%;

            display: flex;
            flex-direction: column;

            gap: 12px;

            align-items: center;
        }

        .menu a {
            width: 170px;

            padding: 12px 18px;

            color: white;

            text-decoration: none;

            border-radius: 8px;
        }   

        .menu a:hover,
        .menu a.active {
            background: #1e88e5;
        }


        /* LOGOUT - BOTTOM */

        .navbar > .logout {
            width: 170px;

            margin-top: auto;
            margin-left: auto;
            margin-right: auto;

            padding: 12px;

            text-align: center;

            background: #e53935;

            color: white;

            text-decoration: none;

            border-radius: 8px;
        }

        .navbar > .logout:hover {
            background: #c62828;
        }

        .main {
            margin-left: 220px;
            padding: 30px;
            min-heigth: 100vh;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            color: #0f4c81;
        }

        .header p {
            color: #666;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            border-left: 5px solid #1e88e5;
        }

        .card h3 {
            color: #0f4c81;
            font-size: 16px;
            margin-top: 0;
        }

        .number {
            font-size: 35px;
            font-weight: bold;
            color: #1565c0;
        }


        /* =========================================
           NEXT APPOINTMENT
        ========================================= */

        .next-appointment {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 25px;
            border-left: 5px solid #1e88e5;
        }

        .next-appointment h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #0f4c81;
            font-size: 20px;
        }

        .appointment-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-box {
            background: #f5f9ff;
            padding: 15px;
            border-radius: 8px;
        }

        .info-box .label {
            font-size: 13px;
            color: #777;
            margin-bottom: 5px;
        }

        .info-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #0f4c81;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            background: #dbeafe;
            color: #1565c0;
            border-radius: 20px;
            font-size: 13px;
        }

        .view-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #1e88e5;
            color: white;
            text-decoration: none;
            border-radius: 7px;
        }

        .view-btn:hover {
            background: #0f4c81;
        }

        .no-appointment {
            color: #777;
            margin: 0;
            padding: 10px 0;
        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media(max-width:900px) {

            .stats {
                grid-template-columns: repeat(2,1fr);
            }

            .appointment-info {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<?php include "student_sidebar.php"; ?>


<div class="main">

    <!-- HEADER -->

    <div class="header">

        <h1>
            Student Dashboard
        </h1>

        <p>
            Welcome,
            <strong>
                <?php echo htmlspecialchars($student_name); ?>
            </strong>
        </p>

    </div>


    <!-- STATISTICS -->

    <div class="stats">

        <div class="card">

            <h3>
                Upcoming Appointment
            </h3>

            <div class="number">
                <?php echo $upcoming; ?>
            </div>

        </div>


        <div class="card">

            <h3>
                Pending Appointment
            </h3>

            <div class="number">
                <?php echo $pending; ?>
            </div>

        </div>


        <div class="card">

            <h3>
                Completed Consultations
            </h3>

            <div class="number">
                <?php echo $completed; ?>
            </div>

        </div>


        <div class="card">

            <h3>
                Total Appointments
            </h3>

            <div class="number">
                <?php echo $total_appointments; ?>
            </div>

        </div>

    </div>


    <!-- NEXT APPOINTMENT -->

    <div class="next-appointment">

        <h2>
            Next Appointment
        </h2>


        <?php if ($next_appointment): ?>

            <div class="appointment-info">

                <div class="info-box">

                    <div class="label">
                        Counselor
                    </div>

                    <div class="value">
                        <?php echo htmlspecialchars($next_appointment['counselor_name']); ?>
                    </div>

                </div>


                <div class="info-box">

                    <div class="label">
                        Date
                    </div>

                    <div class="value">

                        <?php
                        echo date(
                            "d F Y",
                            strtotime($next_appointment['appointment_date'])
                        );
                        ?>

                    </div>

                </div>


                <div class="info-box">

                    <div class="label">
                        Time
                    </div>

                    <div class="value">

                        <?php
                        echo date(
                            "h:i A",
                            strtotime($next_appointment['appointment_time'])
                        );
                        ?>

                    </div>

                </div>


                <div class="info-box">

                    <div class="label">
                        Reason
                    </div>

                    <div class="value">
                        <?php echo htmlspecialchars($next_appointment['reason']); ?>
                    </div>

                </div>

            </div>


            <div style="margin-top: 15px;">

                <span class="status">
                    Approved
                </span>

            </div>


            <a
                href="view_appointment.php?id=<?php echo $next_appointment['id']; ?>"
                class="view-btn"
            >
                View Appointment
            </a>


        <?php else: ?>

            <p class="no-appointment">
                No upcoming appointment.
            </p>

        <?php endif; ?>

    </div>


</div>

</body>
</html>