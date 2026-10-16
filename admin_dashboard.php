<?php

require_once "admin_check.php";
require_once "db.php";

$student_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM `user`
     WHERE role = 'student'"
);

$student_data = mysqli_fetch_assoc($student_query);

$total_students = $student_data['total'];

$counselor_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM `user`
     WHERE role = 'counselor'"
);

$counselor_data = mysqli_fetch_assoc($counselor_query);

$total_counselors = $counselor_data['total'];

$appointment_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM appointment"
);

$appointment_data = mysqli_fetch_assoc($appointment_query);

$total_appointments = $appointment_data['total'];

$consultation_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM consultation"
);

$consultation_data = mysqli_fetch_assoc($consultation_query);

$total_consultations = $consultation_data['total'];

$pending_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM appointment
     WHERE status = 'pending'"
);

$pending_data = mysqli_fetch_assoc($pending_query);

$total_pending = $pending_data['total'];

?>

<!DOCTYPE html>

<html>

<head>

    <title>Admin Dashboard</title>


    <style>

        * {
            box-sizing: border-box;
        }
        body {
            background-color: #dbeafe;
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100vh;
            background-color: #0f4c81;
            padding: 25px 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        .sidebar h2 {
            color: white;
            text-align: center;
            margin: 0 0 30px 0;
            font-size: 22px;
        }
        .sidebar a {
            display: block;
            width: 180px;
            margin: 0 auto 10px auto;
            padding: 12px 18px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
        }
        .sidebar a:hover {
            background-color: #1e88e5;
        }
        .sidebar a.active {
            background-color: #1e88e5;
            font-weight: bold;
        }
        .sidebar a.logout {
            margin-top: auto;
            background-color: #e53935;
            text-align: center;
        }
        .sidebar a.logout:hover {
            background-color: #c62828;
        }
        .main-content {
            margin-left: 220px;
            padding: 30px;
            min-height: 100vh;
        }
        .dashboard-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
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
            padding: 20px;
            border-radius: 8px;
            transition: 0.2s;
        }
        .stat-box:hover {
            transform: translateY(-3px);
        }
        .stat-box h3 {
            margin-top: 0;
            color: #0d47a1;
            font-size: 16px;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #1565c0;
        }
        .stat-box:nth-child(1) {
            border-left: 5px solid #2196f3;
        }
        .stat-box:nth-child(2) {
            border-left: 5px solid #26a69a;
        }
        .stat-box:nth-child(3) {
            border-left: 5px solid #7e57c2;
        }
        .stat-box:nth-child(4) {
            border-left: 5px solid #ef6c00;
        }
        .stat-box:nth-child(5) {
            border-left: 5px solid #f9a825;
        }
        .create-btn {
            float: right;
            background-color: #1e88e5;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        .create-btn:hover {
            background-color: #0f4c81;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #0f4c81;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom:
                1px solid #ddd;
        }
        tr:hover {
            background-color: #f1f5f9;
        }
        .role {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .counselor {
            background-color: #dbeafe;
            color: #1565c0;
        }
        .staff {
            background-color: #dcfce7;
            color: #166534;
        }
        @media (max-width: 900px) {
            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }
        }
        @media (max-width: 600px) {
            .navbar {
                width: 180px;
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

<?php include("admin_sidebar.php"); ?>

<div class="main-content">

    <div class="dashboard-section">

        <h2>
            Admin Dashboard
        </h2>

        <div class="stats">

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

            <div class="stat-box">

                <h3>
                    Total Counselors
                </h3>

                <div class="stat-number">

                    <?php
                    echo $total_counselors;
                    ?>

                </div>

            </div>

            <div class="stat-box">

                <h3>
                    Total Appointments
                </h3>

                <div class="stat-number">

                    <?php
                    echo $total_appointments;
                    ?>

                </div>

            </div>
            
            <div class="stat-box">

                <h3>
                    Consultation Records
                </h3>

                <div class="stat-number">

                    <?php
                    echo $total_consultations;
                    ?>

                </div>

            </div>

            <div class="stat-box">

                <h3>
                    Pending Appointments
                </h3>

                <div class="stat-number">

                    <?php
                    echo $total_pending;
                    ?>

                </div>

            </div>

        </div>

    </div>



    
