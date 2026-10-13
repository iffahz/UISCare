<?php

require_once "counselor_check.php";
require_once "db.php";


// =========================================
// CHECK APPOINTMENT ID
// =========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: appointment.php");
    exit();

}

$appointment_id = intval($_GET['id']);

$counselor_id = $_SESSION['user_id'];


// =========================================
// GET APPOINTMENT DETAILS
// =========================================

$sql = "
    SELECT
        a.id,
        a.appointment_date,
        a.status,
        a.reason,
        a.created_at,

        s.student_id,
        s.fullname AS student_name,
        s.email AS student_email,
        s.phone AS student_phone,

        c.fullname AS counselor_name

    FROM appointment a

    INNER JOIN `user` s
        ON a.student_user_id = s.id

    INNER JOIN `user` c
        ON a.counselor_user_id = c.id

    WHERE a.id = ?
    AND a.counselor_user_id = ?
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $appointment_id,
    $counselor_id
);

$stmt->execute();

$result = $stmt->get_result();


// =========================================
// CHECK RECORD
// =========================================

if ($result->num_rows === 0) {

    echo "
        <script>
            alert('Appointment not found.');
            window.location.href='appointment.php';
        </script>
    ";

    exit();

}


$appointment = $result->fetch_assoc();

?>

<!DOCTYPE html>

<html>

<head>

    <title>View Appointment</title>

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
           MAIN CONTENT
        ===================================== */

        .main-content {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        .header,
        .card,
        .action-area {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        /* =====================================
           HEADER
        ===================================== */

        .header {

            background-color: white;

            padding: 25px;

            border-radius: 10px;

            margin-bottom: 20px;

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


        /* =====================================
           BUTTON
        ===================================== */

        .btn {

            display: inline-block;

            padding: 10px 16px;

            border-radius: 6px;

            text-decoration: none;

            color: white;

            font-weight: bold;

        }


        .btn-back {

            background-color: #777;

        }


        .btn-back:hover {

            background-color: #555;

        }


        .btn-consultation {

            background-color: #00897b;

        }


        .btn-consultation:hover {

            background-color: #00695c;

        }


        /* =====================================
           CARD
        ===================================== */

        .card {

            background-color: white;

            border-radius: 10px;

            padding: 25px;

            margin-bottom: 20px;

        }


        .card h2 {

            margin-top: 0;

            color: #0f4c81;

            border-bottom: 2px solid #dbeafe;

            padding-bottom: 12px;

        }


        /* =====================================
           INFORMATION ROW
        ===================================== */

        .info-row {

            display: flex;

            padding: 13px 0;

            border-bottom: 1px solid #eee;

        }


        .info-row:last-child {

            border-bottom: none;

        }


        .info-label {

            width: 220px;

            font-weight: bold;

            color: #555;

        }


        .info-value {

            flex: 1;

            color: #333;

        }


        /* =====================================
           STATUS
        ===================================== */

        .status {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;

        }


        .status-pending {

            background-color: #fff3cd;

            color: #856404;

        }


        .status-confirmed {

            background-color: #dbeafe;

            color: #1e40af;

        }


        .status-completed {

            background-color: #d1fae5;

            color: #065f46;

        }


        .status-cancelled {

            background-color: #fee2e2;

            color: #991b1b;

        }


        .status-default {

            background-color: #e5e7eb;

            color: #374151;

        }


        /* =====================================
           ACTION
        ===================================== */

        .action-area {

            background-color: white;

            padding: 20px 25px;

            border-radius: 10px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .action-area p {

            margin: 0;

            color: #666;

        }


        .action-buttons {

            display: flex;

            gap: 10px;

        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 900px) {

            .main-content {
                width: calc(100% - 230px);
                margin-left: 230px;

            }

        }


        @media (max-width: 700px) {

            .header {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

            }


            .info-row {

                flex-direction: column;

                gap: 5px;

            }


            .info-label {

                width: 100%;

            }


            .action-area {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

            }

        }


        /* =====================================
           PRINT
        ===================================== */

        @media print {

            .navbar,
            .no-print {

                display: none;

            }

           .main-content {
                width: 100%;
                margin: 0 auto;
                padding: 30px;
                max-width: 1200px;
            }      

            body {

                background-color: white;

            }

        }

    </style>

</head>


<body>

<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="header">

        <div>

            <h1>
                Appointment Details
            </h1>

            <p>
                View appointment information and student details.
            </p>

        </div>
    </div>
    <!-- =====================================
         APPOINTMENT INFORMATION
    ====================================== -->

    <div class="card">

        <h2>
            Appointment Information
        </h2>


        <!-- Appointment ID -->

        <div class="info-row">

            <div class="info-label">
                Appointment ID
            </div>

            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $appointment['id']
                );

                ?>

            </div>

        </div>


        <!-- Appointment Date -->

        <div class="info-row">

            <div class="info-label">
                Appointment Date
            </div>

            <div class="info-value">

                <?php

                if (!empty($appointment['appointment_date'])) {

                    echo date(
                        "d M Y",
                        strtotime(
                            $appointment['appointment_date']
                        )
                    );

                } else {

                    echo "-";

                }

                ?>

            </div>

        </div>


        <!-- Appointment Time -->

        <div class="info-row">

            <div class="info-label">
                Appointment Time
            </div>

            <div class="info-value">

                <?php

                if (!empty($appointment['appointment_date'])) {

                    echo date(
                        "h:i A",
                        strtotime(
                            $appointment['appointment_date']
                        )
                    );

                } else {

                    echo "-";

                }

                ?>

            </div>

        </div>


        <!-- Status -->

        <div class="info-row">

            <div class="info-label">
                Status
            </div>

            <div class="info-value">


                <?php

                $status =
                    strtolower(
                        trim(
                            $appointment['status'] ?? ''
                        )
                    );


                $status_class = "status-default";


                if ($status === "pending") {

                    $status_class = "status-pending";

                } elseif ($status === "confirmed") {

                    $status_class = "status-confirmed";

                } elseif ($status === "completed") {

                    $status_class = "status-completed";

                } elseif (
                    $status === "cancelled" ||
                    $status === "canceled"
                ) {

                    $status_class = "status-cancelled";

                }

                ?>


                <span
                    class="status <?php echo $status_class; ?>">

                    <?php

                    echo htmlspecialchars(
                        ucfirst(
                            $appointment['status'] ?? 'Unknown'
                        )
                    );

                    ?>

                </span>

            </div>

        </div>


        <!-- Reason -->

        <div class="info-row">

            <div class="info-label">
                Reason
            </div>

            <div class="info-value">

                <?php

                echo nl2br(
                    htmlspecialchars(
                        $appointment['reason'] ?? '-'
                    )
                );

                ?>

            </div>

        </div>


        <!-- Booking Date -->

        <div class="info-row">

            <div class="info-label">
                Booking Date
            </div>

            <div class="info-value">

                <?php

                if (!empty($appointment['created_at'])) {

                    echo date(
                        "d M Y, h:i A",
                        strtotime(
                            $appointment['created_at']
                        )
                    );

                } else {

                    echo "-";

                }

                ?>

            </div>

        </div>


    </div>



    <!-- =====================================
         STUDENT INFORMATION
    ====================================== -->

    <div class="card">

        <h2>
            Student Information
        </h2>


        <!-- Student ID -->

        <div class="info-row">

            <div class="info-label">
                Student ID
            </div>

            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $appointment['student_id'] ?? '-'
                );

                ?>

            </div>

        </div>


        <!-- Student Name -->

        <div class="info-row">

            <div class="info-label">
                Student Name
            </div>

            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $appointment['student_name'] ?? '-'
                );

                ?>

            </div>

        </div>


        <!-- Student Email -->

        <div class="info-row">

            <div class="info-label">
                Email
            </div>

            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $appointment['student_email'] ?? '-'
                );

                ?>

            </div>

        </div>


        <!-- Student Phone -->

        <div class="info-row">

            <div class="info-label">
                Phone
            </div>

            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $appointment['student_phone'] ?? '-'
                );

                ?>

            </div>

        </div>

    </div>



    <!-- =====================================
         COUNSELOR INFORMATION
    ====================================== -->

    <div class="card">

        <h2>
            Counselor Information
        </h2>


        <div class="info-row">

            <div class="info-label">
                Counselor Name
            </div>

            <div class="info-value">

                <?php

                echo htmlspecialchars(
                    $appointment['counselor_name'] ?? '-'
                );

                ?>

            </div>

        </div>

    </div>



    <!-- =====================================
         ACTION
    ====================================== -->

    <div class="action-area no-print">

        <div class="action-buttons">


            <a
                href="counselor_appointments.php"
                class="btn btn-back">

                Back to List

            </a>

        </div>

    </div>


</div>


</body>

</html>