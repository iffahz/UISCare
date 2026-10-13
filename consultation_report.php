<?php

require_once "counselor_check.php";
require_once "db.php";

$counselor_id = $_SESSION['user_id'];


// =========================================
// GET COUNSELOR INFORMATION
// =========================================

$counselor_sql = "
    SELECT fullname
    FROM `user`
    WHERE id = ?
";

$counselor_stmt = $conn->prepare($counselor_sql);

$counselor_stmt->bind_param(
    "i",
    $counselor_id
);

$counselor_stmt->execute();

$counselor_result = $counselor_stmt->get_result();

$counselor = $counselor_result->fetch_assoc();

$counselor_name = $counselor['fullname'] ?? '';


// =========================================
// FILTER
// =========================================

$student_filter = $_GET['student_id'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';


// =========================================
// GET STUDENTS
// Only students who have consultation
// records with this counselor
// =========================================

$students_sql = "
    SELECT DISTINCT
        s.id,
        s.student_id,
        s.fullname
    FROM consultation c

    INNER JOIN appointment a
        ON c.appointment_id = a.id

    INNER JOIN `user` s
        ON a.student_user_id = s.id

    WHERE a.counselor_user_id = ?

    ORDER BY s.fullname ASC
";

$students_stmt = $conn->prepare($students_sql);

$students_stmt->bind_param(
    "i",
    $counselor_id
);

$students_stmt->execute();

$students_result = $students_stmt->get_result();


// =========================================
// GET CONSULTATION REPORT
// =========================================

$sql = "
    SELECT
        c.id,
        s.student_id,
        s.fullname AS student_name,
        a.appointment_date,
        c.notes,
        c.created_at

    FROM consultation c

    INNER JOIN appointment a
        ON c.appointment_id = a.id

    INNER JOIN `user` s
        ON a.student_user_id = s.id

    WHERE a.counselor_user_id = ?
";


// =========================================
// FILTER BY STUDENT
// =========================================

if ($student_filter !== "") {

    $sql .= "
        AND s.id = ?
    ";

}


// =========================================
// FILTER BY DATE
// =========================================

if ($from_date !== "") {

    $sql .= "
        AND DATE(c.created_at) >= ?
    ";

}


if ($to_date !== "") {

    $sql .= "
        AND DATE(c.created_at) <= ?
    ";

}


$sql .= "
    ORDER BY c.created_at DESC
";


$stmt = $conn->prepare($sql);


// =========================================
// BIND PARAMETERS
// =========================================

if (
    $student_filter !== "" &&
    $from_date !== "" &&
    $to_date !== ""
) {

    $stmt->bind_param(
        "iiss",
        $counselor_id,
        $student_filter,
        $from_date,
        $to_date
    );

} elseif (
    $student_filter !== "" &&
    $from_date !== ""
) {

    $stmt->bind_param(
        "iis",
        $counselor_id,
        $student_filter,
        $from_date
    );

} elseif (
    $student_filter !== "" &&
    $to_date !== ""
) {

    $stmt->bind_param(
        "iis",
        $counselor_id,
        $student_filter,
        $to_date
    );

} elseif ($student_filter !== "") {

    $stmt->bind_param(
        "ii",
        $counselor_id,
        $student_filter
    );

} elseif (
    $from_date !== "" &&
    $to_date !== ""
) {

    $stmt->bind_param(
        "iss",
        $counselor_id,
        $from_date,
        $to_date
    );

} elseif ($from_date !== "") {

    $stmt->bind_param(
        "is",
        $counselor_id,
        $from_date
    );

} elseif ($to_date !== "") {

    $stmt->bind_param(
        "is",
        $counselor_id,
        $to_date
    );

} else {

    $stmt->bind_param(
        "i",
        $counselor_id
    );

}


$stmt->execute();

$result = $stmt->get_result();

$total_records = $result->num_rows;


// =========================================
// GET SELECTED STUDENT NAME
// =========================================

$selected_student_name = "";

if ($student_filter !== "") {

    $student_name_sql = "
        SELECT fullname
        FROM `user`
        WHERE id = ?
        AND role = 'student'
    ";

    $student_name_stmt = $conn->prepare($student_name_sql);

    $student_name_stmt->bind_param(
        "i",
        $student_filter
    );

    $student_name_stmt->execute();

    $student_name_result = $student_name_stmt->get_result();

    $selected_student = $student_name_result->fetch_assoc();

    $selected_student_name =
        $selected_student['fullname'] ?? '';

}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Consultation Report</title>

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


        /* ================================
           MAIN CONTENT
        ================================= */

        .main-content {

            width: 100%;

            max-width: 1200px;

            margin: 0 auto;

            padding: 30px;

        }


        /* ================================
           HEADER
        ================================= */

        .header {

            background: white;

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

            color: #666;

        }


        /* ================================
           FILTER
        ================================= */

        .filter-container {

            background: white;

            padding: 25px;

            border-radius: 10px;

            margin-bottom: 20px;

        }


        .filter-form {

            display: flex;

            gap: 15px;

            align-items: end;

            flex-wrap: wrap;

        }


        .form-group {

            display: flex;

            flex-direction: column;

        }


        .form-group label {

            margin-bottom: 7px;

            font-weight: bold;

        }


        .form-group input,
        .form-group select {

            padding: 10px;

            border: 1px solid #ccc;

            border-radius: 5px;

            min-width: 190px;

            background: white;

        }


        .btn {

            border: none;

            padding: 11px 18px;

            border-radius: 6px;

            color: white;

            cursor: pointer;

            text-decoration: none;

            font-weight: bold;

        }


        .btn-generate {

            background-color: #1565c0;

        }


        .btn-generate:hover {

            background-color: #0d47a1;

        }


        .btn-clear {

            background-color: #777;

        }


        .btn-clear:hover {

            background-color: #555;

        }


        .btn-print {

            background-color: #00897b;

        }


        .btn-print:hover {

            background-color: #00695c;

        }


        .btn-dashboard {

            background-color: #0f4c81;

            display: inline-block;

            margin-top: 10px;

        }


        .btn-dashboard:hover {

            background-color: #0b3d66;

        }


        /* ================================
           REPORT
        ================================= */

        .report-container {

            background: white;

            padding: 30px;

            border-radius: 10px;

        }


        .report-container h2 {

            color: #0f4c81;

            margin-top: 0;

        }


        .report-info {

            margin-bottom: 25px;

            line-height: 1.8;

        }


        .summary {

            background-color: #e3f2fd;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 20px;

        }


        /* ================================
           TABLE
        ================================= */

        table {

            width: 100%;

            border-collapse: collapse;

        }


        th {

            background-color: #0f4c81;

            color: white;

            padding: 12px;

            text-align: left;

        }


        td {

            padding: 12px;

            border-bottom: 1px solid #ddd;

            vertical-align: top;

        }


        tr:hover {

            background-color: #f5f5f5;

        }


        /* ================================
           PRINT
        ================================= */

        @media print {

            body {

                background: white;

            }


            .header,

            .filter-container,

            .no-print {

                display: none !important;

            }


            .main-content {

                max-width: 1200px;

                margin: 0 auto;

                padding: 30px;

                width: 100%;

            }


            .report-container {

                padding: 0;

                box-shadow: none;

            }


            table {

                page-break-inside: auto;

            }


            tr {

                page-break-inside: avoid;

                page-break-after: auto;

            }

        }

    </style>

</head>


<body>


<div class="main-content">


    <!-- ================================
         HEADER
    ================================= -->

    <div class="header">

        <div>

            <h1>
                Consultation Report
            </h1>

            <p>
                Generate and print your consultation records report.
            </p>

            <a
                href="counselor_dashboard.php"
                class="btn btn-dashboard no-print">

                ← Back to Dashboard

            </a>

        </div>


        <button
            onclick="window.print()"
            class="btn btn-print no-print">

            🖨 Print Report

        </button>

    </div>



    <!-- ================================
         FILTER
    ================================= -->

    <div class="filter-container no-print">

        <form
            method="GET"
            class="filter-form">


            <!-- STUDENT -->

            <div class="form-group">

                <label>
                    Student
                </label>

                <select name="student_id">

                    <option value="">
                        All Students
                    </option>


                    <?php while ($student = $students_result->fetch_assoc()): ?>

                        <option
                            value="<?php echo $student['id']; ?>"
                            <?php
                            echo (
                                $student_filter == $student['id']
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >

                            <?php

                            echo htmlspecialchars(
                                $student['student_id']
                                . " - "
                                . $student['fullname']
                            );

                            ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>



            <!-- FROM DATE -->

            <div class="form-group">

                <label>
                    From Date
                </label>

                <input
                    type="date"
                    name="from_date"
                    value="<?php echo htmlspecialchars($from_date); ?>"
                >

            </div>



            <!-- TO DATE -->

            <div class="form-group">

                <label>
                    To Date
                </label>

                <input
                    type="date"
                    name="to_date"
                    value="<?php echo htmlspecialchars($to_date); ?>"
                >

            </div>



            <!-- GENERATE -->

            <button
                type="submit"
                class="btn btn-generate">

                Generate Report

            </button>



            <!-- CLEAR -->

            <a
                href="consultation_report.php"
                class="btn btn-clear">

                Clear

            </a>


        </form>

    </div>



    <!-- ================================
         REPORT
    ================================= -->

    <div class="report-container">


        <h2>
            Student Consultation Report
        </h2>



        <!-- REPORT INFORMATION -->

        <div class="report-info">

            <strong>Counselor:</strong>

            <?php

            echo htmlspecialchars(
                $counselor_name
            );

            ?>

            <br>



            <!-- SELECTED STUDENT -->

            <strong>Student:</strong>

            <?php

            if ($selected_student_name !== "") {

                echo htmlspecialchars(
                    $selected_student_name
                );

            } else {

                echo "All Students";

            }

            ?>

            <br>



            <strong>Generated Date:</strong>

            <?php

            echo date("d M Y");

            ?>

            <br>



            <?php if ($from_date !== ""): ?>

                <strong>From:</strong>

                <?php

                echo date(
                    "d M Y",
                    strtotime($from_date)
                );

                ?>

                <br>

            <?php endif; ?>



            <?php if ($to_date !== ""): ?>

                <strong>To:</strong>

                <?php

                echo date(
                    "d M Y",
                    strtotime($to_date)
                );

                ?>

            <?php endif; ?>


        </div>



        <!-- TOTAL RECORD -->

        <div class="summary">

            <strong>

                Total Consultation Records:

                <?php

                echo $total_records;

                ?>

            </strong>

        </div>



        <!-- REPORT TABLE -->

        <table>

            <tr>

                <th>Student ID</th>

                <th>Student Name</th>

                <th>Appointment Date</th>

                <th>Consultation Notes</th>

                <th>Date Recorded</th>

            </tr>



            <?php if ($result->num_rows > 0): ?>


                <?php while ($row = $result->fetch_assoc()): ?>


                    <tr>


                        <!-- STUDENT ID -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row['student_id'] ?? ''
                            );

                            ?>

                        </td>



                        <!-- STUDENT NAME -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row['student_name'] ?? ''
                            );

                            ?>

                        </td>



                        <!-- APPOINTMENT DATE -->

                        <td>

                            <?php

                            if (!empty($row['appointment_date'])) {

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row['appointment_date']
                                    )
                                );

                            } else {

                                echo "-";

                            }

                            ?>

                        </td>



                        <!-- NOTES -->

                        <td>

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $row['notes'] ?? ''
                                )
                            );

                            ?>

                        </td>



                        <!-- DATE RECORDED -->

                        <td>

                            <?php

                            if (!empty($row['created_at'])) {

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row['created_at']
                                    )
                                );

                            } else {

                                echo "-";

                            }

                            ?>

                        </td>


                    </tr>


                <?php endwhile; ?>


            <?php else: ?>


                <tr>

                    <td
                        colspan="5"
                        style="
                            text-align:center;
                            padding:30px;
                        ">

                        No consultation records found.

                    </td>

                </tr>


            <?php endif; ?>


        </table>


    </div>


</div>


</body>

</html>