<?php

require_once "counselor_check.php";
require_once "db.php";

$counselor_id = $_SESSION['user_id'];

$error = "";


// =========================================
// SET CURRENT DATE
// =========================================

$consultation_date = date('Y-m-d');


// =========================================
// SAVE CONSULTATION RECORD
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    // Automatically set status to Completed
    $status = "Completed";


    // =========================================
    // VALIDATION
    // =========================================

    if (
        $appointment_id <= 0 ||
        empty($notes)
    ) {

        $error = "Please complete all required fields.";

    } else {


        // =========================================
        // CHECK COMPLETED APPOINTMENT
        // =========================================

        $check_sql = "
            SELECT id
            FROM appointment
            WHERE id = ?
            AND counselor_user_id = ?
            AND status = 'completed'
        ";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "ii",
            $appointment_id,
            $counselor_id
        );

        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {

            $error = "Invalid completed appointment selected.";

        } else {


            // =========================================
            // CHECK DUPLICATE CONSULTATION
            // =========================================

            $duplicate_sql = "
                SELECT id
                FROM consultation
                WHERE appointment_id = ?
            ";

            $duplicate_stmt =
                $conn->prepare($duplicate_sql);

            $duplicate_stmt->bind_param(
                "i",
                $appointment_id
            );

            $duplicate_stmt->execute();

            $duplicate_result =
                $duplicate_stmt->get_result();


            if ($duplicate_result->num_rows > 0) {

                $error =
                    "A consultation record already exists for this appointment.";

            } else {


                // =========================================
                // INSERT CONSULTATION RECORD
                // =========================================

                $insert_sql = "
                    INSERT INTO consultation
                    (
                        appointment_id,
                        counselor_user_id,
                        consultation_date,
                        notes,
                        status,
                        created_at
                    )
                    VALUES (?, ?, ?, ?, ?, NOW())
                ";

                $insert_stmt =
                    $conn->prepare($insert_sql);


                $insert_stmt->bind_param(
                    "iisss",
                    $appointment_id,
                    $counselor_id,
                    $consultation_date,
                    $notes,
                    $status
                );


                if ($insert_stmt->execute()) {

                    /*
                     * Successfully saved.
                     * Go back to consultation records.
                     */

    echo "<script>
        alert('Consultation record saved successfully!');
        window.location.href = '/COUNSELING/record_consultation.php';
    </script>";

    exit();

} else {

    $error = "Failed to save consultation record.";

}


                $insert_stmt->close();

            }


            $duplicate_stmt->close();

        }


        $check_stmt->close();

    }

}


// =========================================
// GET COMPLETED APPOINTMENTS
// =========================================

$sql = "
    SELECT
        a.id,
        s.student_id,
        s.fullname AS student_name,
        a.appointment_date,
        a.appointment_time,
        a.reason

    FROM appointment a

    INNER JOIN `user` s
        ON a.student_user_id = s.id

    LEFT JOIN consultation c
        ON a.id = c.appointment_id

    WHERE a.counselor_user_id = ?
    AND a.status = 'completed'
    AND c.id IS NULL

    ORDER BY
        a.appointment_date DESC,
        a.appointment_time DESC
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $counselor_id
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Record Consultation</title>


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
   COUNSELOR SIDEBAR
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

.navbar > .logout {
    width: 180px;
    margin-top: auto;
    margin-left: auto;
    margin-right: auto;
    padding: 12px 18px;
    text-align: center;
    background-color: #e53935;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}

.navbar > .logout:hover {
    background-color: #c62828;
}


/* =====================================
   MAIN CONTENT
===================================== */

.main-content {
    margin-left: 220px;
    padding: 30px;
    min-height: 100vh;
}

        /* =====================================
           HEADER
        ===================================== */

        .header {

            background-color: white;

            padding: 25px;

            border-radius: 10px;

            margin-bottom: 20px;

        }


        .header h1 {

            margin: 0;

            color: #0f4c81;

        }


        .header p {

            margin-top: 8px;

            margin-bottom: 0;

            color: #666;

        }


        /* =====================================
           FORM CONTAINER
        ===================================== */

        .form-container {

            background-color: white;

            padding: 30px;

            border-radius: 10px;

            max-width: 900px;

            margin: 0 auto;

        }


        /* =====================================
           ERROR MESSAGE
        ===================================== */

        .error-message {

            background-color: #fee2e2;

            color: #b91c1c;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

        }


        /* =====================================
           FORM GROUP
        ===================================== */

        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            color: #0f4c81;

            font-weight: bold;

        }


        .required {

            color: #e53935;

        }


        select,
        textarea {

            width: 100%;

            padding: 12px 14px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 14px;

            font-family: Arial, sans-serif;

        }


        select:focus,
        textarea:focus {

            outline: none;

            border-color: #1e88e5;

        }


        textarea {

            min-height: 180px;

            resize: vertical;

        }


        /* =====================================
           CONSULTATION DATE
        ===================================== */

        .date-display {

            width: 100%;

            padding: 12px 14px;

            border: 1px solid #ccc;

            border-radius: 6px;

            background-color: #f1f5f9;

            color: #475569;

            font-size: 14px;

        }


        /* =====================================
           APPOINTMENT INFORMATION
        ===================================== */

        .appointment-info {

            background-color: #eff6ff;

            border-left: 4px solid #1e88e5;

            padding: 15px;

            border-radius: 6px;

            margin-bottom: 20px;

            display: none;

        }


        .appointment-info p {

            margin: 7px 0;

            font-size: 14px;

        }


        .appointment-info strong {

            color: #0f4c81;

        }


        /* =====================================
           STATUS
        ===================================== */

        .status-display {

            width: 100%;

            padding: 12px 14px;

            border: 1px solid #ccc;

            border-radius: 6px;

            background-color: #f1f5f9;

            color: #065f46;

            font-size: 14px;

            font-weight: bold;

        }


        /* =====================================
           BUTTONS
        ===================================== */

        .button-container {

            display: flex;

            justify-content: flex-end;

            gap: 10px;

            margin-top: 25px;

        }


        .btn {

            padding: 11px 18px;

            border-radius: 6px;

            text-decoration: none;

            border: none;

            cursor: pointer;

            font-weight: bold;

            font-size: 14px;

        }


        .btn-save {

            background-color: #1e88e5;

            color: white;

        }


        .btn-save:hover {

            background-color: #0f4c81;

        }


        .btn-cancel {

            background-color: #777;

            color: white;

        }


        .btn-cancel:hover {

            background-color: #555;

        }


        /* =====================================
           NO APPOINTMENT
        ===================================== */

        .no-appointment {

            background-color: #f8fafc;

            border: 1px solid #dbeafe;

            padding: 30px;

            text-align: center;

            border-radius: 8px;

            color: #64748b;

        }


        .no-appointment h3 {

            color: #0f4c81;

            margin-top: 0;

        }


        .no-appointment p {

            margin-bottom: 20px;

        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 900px) {

            .main-content {

                margin-left: 230px;

            }

        }


        @media (max-width: 600px) {

            .navbar {

                width: 190px;

            }


            .menu a,
            .navbar > a:last-child {

                width: 160px;

            }


            .main-content {

                margin-left: 210px;

                padding: 20px;

            }


            .form-container {

                padding: 20px;

            }


            .button-container {

                flex-direction: column;

            }


            .btn {

                text-align: center;

            }

        }

    </style>

</head>


<body>


<!-- =========================================
     COUNSELOR SIDEBAR
========================================= -->

<?php require_once "counselor_sidebar.php"; ?>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="header">

        <h1>
            Record Consultation
        </h1>

        <p>
            Record the details of a completed counseling session.
        </p>

    </div>


    <!-- =====================================
         FORM CONTAINER
    ===================================== -->

    <div class="form-container">


        <?php if (!empty($error)): ?>

            <div class="error-message">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <?php if ($result->num_rows > 0): ?>


            <form
                method="POST"
                onsubmit="return confirmSaveConsultation();"
            >


                <!-- =====================================
                     COMPLETED APPOINTMENT
                ===================================== -->

                <div class="form-group">

                    <label for="appointment_id">

                        Completed Appointment

                        <span class="required">*</span>

                    </label>


                    <select
                        name="appointment_id"
                        id="appointment_id"
                        required
                    >

                        <option value="">

                            -- Select Completed Appointment --

                        </option>


                        <?php while ($row = $result->fetch_assoc()): ?>

                            <option
                                value="<?php echo $row['id']; ?>"

                                data-student-id="<?php
                                    echo htmlspecialchars(
                                        $row['student_id']
                                    );
                                ?>"

                                data-student-name="<?php
                                    echo htmlspecialchars(
                                        $row['student_name']
                                    );
                                ?>"

                                data-date="<?php
                                    echo htmlspecialchars(
                                        $row['appointment_date']
                                    );
                                ?>"

                                data-time="<?php
                                    echo htmlspecialchars(
                                        $row['appointment_time']
                                    );
                                ?>"

                                data-reason="<?php
                                    echo htmlspecialchars(
                                        $row['reason']
                                    );
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $row['student_id']
                                );
                                ?>

                                -

                                <?php
                                echo htmlspecialchars(
                                    $row['student_name']
                                );
                                ?>

                                |

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row['appointment_date']
                                    )
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>


                    </select>

                </div>


                <!-- =====================================
                     APPOINTMENT INFORMATION
                ====================================== -->

                <div
                    class="appointment-info"
                    id="appointmentInfo"
                >


                    <p>

                        <strong>
                            Student ID:
                        </strong>

                        <span id="studentId"></span>

                    </p>


                    <p>

                        <strong>
                            Student Name:
                        </strong>

                        <span id="studentName"></span>

                    </p>


                    <p>

                        <strong>
                            Appointment Date:
                        </strong>

                        <span id="appointmentDate"></span>

                    </p>


                    <p>

                        <strong>
                            Appointment Time:
                        </strong>

                        <span id="appointmentTime"></span>

                    </p>


                    <p>

                        <strong>
                            Reason:
                        </strong>

                        <span id="appointmentReason"></span>

                    </p>


                </div>


                <!-- =====================================
                     CONSULTATION DATE
                ====================================== -->

                <div class="form-group">

                    <label>

                        Report Date

                    </label>


                    <div class="date-display">

                        <?php
                        echo date("d M Y");
                        ?>

                    </div>

                </div>


                <!-- =====================================
                     CONSULTATION NOTES
                ====================================== -->

                <div class="form-group">

                    <label for="notes">

                        Consultation Notes

                        <span class="required">*</span>

                    </label>


                    <textarea
                        name="notes"
                        id="notes"
                        placeholder="Enter consultation notes..."
                        required
                    ></textarea>

                </div>


                <!-- =====================================
                     STATUS
                ====================================== -->

                <div class="form-group">

                    <label>

                        Status

                    </label>


                    <div class="status-display">

                        Completed

                    </div>

                </div>


                <!-- =====================================
                     BUTTONS
                ===================================== -->

                <div class="button-container">


                    <a
                        href="consultation_records.php"
                        class="btn btn-cancel"
                    >

                        Cancel

                    </a>


                    <button
                        type="submit"
                        class="btn btn-save"
                    >

                        Save Consultation Record

                    </button>


                </div>


            </form>


        <?php else: ?>


            <div class="no-appointment">

                <h3>

                    No Completed Appointments

                </h3>


                <p>

                    There are no completed appointments
                    available to record a consultation.

                </p>


                <a
                    href="consultation_records.php"
                    class="btn btn-cancel"
                >

                    Back to Consultation Records

                </a>

            </div>


        <?php endif; ?>


    </div>


</div>


<script>


// =========================================
// CONFIRM BEFORE SAVE
// =========================================

function confirmSaveConsultation() {

    const confirmation = confirm(
        "Are you sure you want to save this consultation record?"
    );

    return confirmation;

}


// =========================================
// SHOW APPOINTMENT INFORMATION
// =========================================

const appointmentSelect =
    document.getElementById("appointment_id");


if (appointmentSelect) {

    appointmentSelect.addEventListener(
        "change",
        function () {

            const selectedOption =
                this.options[this.selectedIndex];


            const infoBox =
                document.getElementById(
                    "appointmentInfo"
                );


            if (this.value !== "") {


                document.getElementById(
                    "studentId"
                ).textContent =
                    selectedOption.dataset.studentId;


                document.getElementById(
                    "studentName"
                ).textContent =
                    selectedOption.dataset.studentName;


                document.getElementById(
                    "appointmentDate"
                ).textContent =
                    selectedOption.dataset.date;


                document.getElementById(
                    "appointmentTime"
                ).textContent =
                    selectedOption.dataset.time;


                document.getElementById(
                    "appointmentReason"
                ).textContent =
                    selectedOption.dataset.reason;


                infoBox.style.display = "block";


            } else {

                infoBox.style.display = "none";

            }

        }
    );

}

</script>


</body>

</html>