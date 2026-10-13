<?php

require_once "counselor_check.php";
require_once "db.php";


// =========================================
// GET COUNSELOR ID
// =========================================

$counselor_id = $_SESSION['user_id'];


// =========================================
// TODAY'S APPOINTMENTS
// =========================================

$sql = "
    SELECT COUNT(*) AS total
    FROM appointment
    WHERE counselor_user_id = ?
    AND appointment_date = CURDATE()
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor_id);
$stmt->execute();

$result = $stmt->get_result();
$today_appointments = $result->fetch_assoc()['total'];


// =========================================
// PENDING APPOINTMENTS
// =========================================

$sql = "
    SELECT COUNT(*) AS total
    FROM appointment
    WHERE counselor_user_id = ?
    AND status = 'pending'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor_id);
$stmt->execute();

$result = $stmt->get_result();
$pending_appointments = $result->fetch_assoc()['total'];


// =========================================
// UPCOMING APPOINTMENTS
// =========================================

$sql = "
    SELECT COUNT(*) AS total
    FROM appointment
    WHERE counselor_user_id = ?
    AND appointment_date >= CURDATE()
    AND status IN ('pending', 'approved')
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor_id);
$stmt->execute();

$result = $stmt->get_result();
$upcoming_appointments = $result->fetch_assoc()['total'];


// =========================================
// COMPLETED CONSULTATIONS
// =========================================

$sql = "
    SELECT COUNT(*) AS total
    FROM consultation
    WHERE counselor_user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor_id);
$stmt->execute();

$result = $stmt->get_result();
$completed_consultations = $result->fetch_assoc()['total'];


// =========================================
// TOTAL STUDENTS
// =========================================

$sql = "
    SELECT COUNT(DISTINCT student_user_id) AS total
    FROM appointment
    WHERE counselor_user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor_id);
$stmt->execute();

$result = $stmt->get_result();
$total_students = $result->fetch_assoc()['total'];


// =========================================
// GET COUNSELOR NAME
// =========================================

$sql = "
    SELECT fullname
    FROM `user`
    WHERE id = ?
    AND role = 'counselor'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $counselor_id);
$stmt->execute();

$result = $stmt->get_result();
$counselor = $result->fetch_assoc();

$counselor_name = $counselor['fullname'] ?? 'Counselor';

?>

<!DOCTYPE html>
<html>

<head>

    <title>Counselor Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #dbeafe;
            color: #333;
        }


/* =====================================
   SIDEBAR
===================================== */

.navbar {
    position: fixed;
    left: 0;
    top: 0;

    width: 220px;
    height: 100vh;

    background-color: #0f4c81;

    padding: 25px 0;

    display: flex;
    flex-direction: column;

    z-index: 1000;
}


/* LOGO */

.logo {
    width: 100%;

    display: flex;
    justify-content: center;
    align-items: center;

    margin-bottom: 35px;
}

.logo img {
    width: 150px;
    height: auto;

    display: block;
}


/* MENU */

.menu {
    width: 100%;

    display: flex;
    flex-direction: column;

    gap: 12px;
}

.menu a {
    width: 180px;

    margin: 0 auto;

    padding: 12px 18px;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    font-size: 15px;
}

.menu a:hover,
.menu a.active {
    background-color: #1e88e5;
}


/* LOGOUT */

.navbar > a.logout {
    width: 180px;

    margin-top: auto;
    margin-left: auto;
    margin-right: auto;

    padding: 12px 18px;

    text-align: center;

    text-decoration: none;

    background-color: #e53935;

    color: white;

    border-radius: 8px;
}

.navbar > a.logout:hover {
    background-color: #c62828;
}

        /* =====================================
           MAIN CONTENT
        ===================================== */

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            padding: 30px;
        }


        /* =====================================
           HEADER
        ===================================== */

        .header {
            background-color: white;

            padding: 25px;

            border-radius: 10px;

            margin-bottom: 25px;

            display: flex;

            justify-content: space-between;

            align-items: center;
        }


        .header h1 {
            margin: 0;

            color: #0f4c81;
        }


        .header p {
            margin: 5px 0 0;

            color: #666;
        }


        .welcome {
            font-size: 16px;

            color: #0f4c81;

            font-weight: bold;
        }


        /* =====================================
           DASHBOARD CARDS
        ===================================== */

        .dashboard-section {
            background-color: white;

            padding: 25px;

            border-radius: 10px;
        }


        .dashboard-section h2 {
            color: #0f4c81;

            margin-top: 0;

            margin-bottom: 20px;
        }


        .stats {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }


        .stat-box {
            background-color: #b5c7e3;

            padding: 22px;

            border-radius: 10px;

            transition: 0.2s;

            min-height: 130px;
        }


        .stat-box:hover {
            transform: translateY(-3px);
        }


        .stat-box h3 {
            margin: 0 0 15px;

            color: #0d47a1;

            font-size: 16px;
        }


        .stat-number {
            font-size: 34px;

            font-weight: bold;

            color: #1565c0;
        }


        /* Different card colours */

        .stat-box:nth-child(1) {
            border-left: 5px solid #2196f3;
        }


        .stat-box:nth-child(2) {
            border-left: 5px solid #f9a825;
        }


        .stat-box:nth-child(3) {
            border-left: 5px solid #26a69a;
        }


        .stat-box:nth-child(4) {
            border-left: 5px solid #7e57c2;
        }


        .stat-box:nth-child(5) {
            border-left: 5px solid #ef6c00;
        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 900px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

        }


        @media (max-width: 600px) {

            .navbar {
                width: 180px;
                margin-left: 20px;
            }

            .main-content {
                margin-left: 200px;

                padding: 20px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<!-- =====================================
     SIDEBAR
===================================== -->

<?php include "counselor_sidebar.php"; ?>


<!-- =====================================
     MAIN CONTENT
===================================== -->

<div class="main-content">


    <!-- HEADER -->

    <div class="header">

        <div>

            <h1>
                Counselor Dashboard
            </h1>

            <p>
                Manage your appointments and consultations.
            </p>

        </div>


        <div class="welcome">

            Welcome,
            <?php echo htmlspecialchars($counselor_name); ?>

        </div>

    </div>


    <!-- =====================================
         STATISTICS
    ====================================== -->

    <div class="dashboard-section">

        <h2>
            Overview
        </h2>


        <div class="stats">


            <!-- TODAY -->

            <div class="stat-box">

                <h3>
                    Today's Appointments
                </h3>

                <div class="stat-number">

                    <?php
                    echo $today_appointments;
                    ?>

                </div>

            </div>


            <!-- PENDING -->

            <div class="stat-box">

                <h3>
                    Pending Appointments
                </h3>

                <div class="stat-number">

                    <?php
                    echo $pending_appointments;
                    ?>

                </div>

            </div>


            <!-- UPCOMING -->

            <div class="stat-box">

                <h3>
                    Upcoming Appointments
                </h3>

                <div class="stat-number">

                    <?php
                    echo $upcoming_appointments;
                    ?>

                </div>

            </div>


            <!-- COMPLETED -->

            <div class="stat-box">

                <h3>
                    Completed Consultations
                </h3>

                <div class="stat-number">

                    <?php
                    echo $completed_consultations;
                    ?>

                </div>

            </div>


            <!-- STUDENTS -->

            <div class="stat-box">

                <h3>
                    Total Students
                </h3>

                <div class="stat-number">

                    <?php
                    echo $total_students;
                    ?>

                </div>

            </div>


        </div>

    </div>


</div>


</body>

</html>