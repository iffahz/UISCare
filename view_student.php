<?php

require_once "admin_check.php";
require_once "db.php";


// =========================================
// GET STUDENT ID
// =========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: manage_student.php");
    exit();

}

$student_id = intval($_GET['id']);


// =========================================
// GET STUDENT INFORMATION
// =========================================

$sql = "
    SELECT
        id,
        student_id,
        fullname,
        email,
        phone,
        status,
        role,
        profile_picture
    FROM `user`
    WHERE id = ?
    AND role = 'student'
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();


// =========================================
// CHECK STUDENT EXISTS
// =========================================

if ($result->num_rows === 0) {

    header("Location: manage_student.php");
    exit();

}

$student = $result->fetch_assoc();


// =========================================
// PROFILE PICTURE
// =========================================

if (!empty($student['profile_picture'])) {

    $profile_image =
        "uploads/profile/" .
        $student['profile_picture'];

} else {

    $profile_image =
        "";

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>View Student</title>

    <style>

        /* =====================================
           RESET
        ===================================== */

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 0;

            font-family: Arial, sans-serif;

            background-color: #dbeafe;

            color: #333;

        }


        /* =====================================
           SIDEBAR
        ===================================== */

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


        /* LOGOUT */

        .sidebar a.logout {

            margin-top: auto;

            background-color: #e53935;

            text-align: center;

        }


        .sidebar a.logout:hover {

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

        .page-header {

            background-color: white;

            padding: 20px 25px;

            border-radius: 10px;

            margin-bottom: 25px;

        }


        .page-header h1 {

            margin: 0;

            color: #0f4c81;

        }


        .page-header p {

            margin-top: 8px;

            margin-bottom: 0;

            color: #666;

        }


        /* =====================================
           STUDENT CARD
        ===================================== */

        .student-card {

            background-color: white;

            padding: 30px;

            border-radius: 10px;

            max-width: 800px;

        }


        /* =====================================
           STUDENT TITLE
        ===================================== */

        .student-title {

            display: flex;

            align-items: center;

            gap: 20px;

            margin-bottom: 25px;

            padding-bottom: 20px;

            border-bottom: 1px solid #ddd;

        }


        /* =====================================
           PROFILE PICTURE
        ===================================== */

        .student-picture {

            width: 100px;

            height: 100px;

            border-radius: 50%;

            background-color: #dbeafe;

            display: flex;

            align-items: center;

            justify-content: center;

            overflow: hidden;

            flex-shrink: 0;

            border: 3px solid #dbeafe;

        }


        .student-picture img {

            width: 100%;

            height: 100%;

            object-fit: contain;

            display: block;

        }


        /* DEFAULT ICON */

        .default-icon {

            font-size: 40px;

        }


        .student-title h2 {

            margin: 0;

            color: #0f4c81;

        }


        .student-title p {

            margin: 5px 0 0 0;

            color: #666;

        }


        /* =====================================
           INFORMATION
        ===================================== */

        .info-grid {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 20px;

        }


        .info-box {

            background-color: #f8fafc;

            border: 1px solid #e5e7eb;

            border-radius: 8px;

            padding: 15px;

        }


        .info-box.full {

            grid-column: span 2;

        }


        .info-label {

            font-size: 13px;

            color: #666;

            margin-bottom: 7px;

            font-weight: bold;

        }


        .info-value {

            font-size: 15px;

            color: #333;

        }


        /* =====================================
           STATUS
        ===================================== */

        .status {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 6px;

            font-size: 13px;

            font-weight: bold;

        }


        .status.active {

            background-color: #dcfce7;

            color: #166534;

        }


        .status.inactive {

            background-color: #fee2e2;

            color: #991b1b;

        }


        /* =====================================
           BUTTON
        ===================================== */

        .button-container {

            margin-top: 25px;

            display: flex;

            gap: 10px;

        }


        .btn {

            display: inline-block;

            padding: 10px 18px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;

        }


        .btn-back {

            background-color: #64748b;

            color: white;

        }


        .btn-back:hover {

            background-color: #475569;

        }


        .btn-edit {

            background-color: #f59e0b;

            color: white;

        }


        .btn-edit:hover {

            background-color: #d97706;

        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 700px) {

            .main-content {

                margin-left: 190px;

                padding: 20px;

            }


            .sidebar {

                width: 190px;

            }


            .sidebar a {

                width: 160px;

            }


            .info-grid {

                grid-template-columns: 1fr;

            }


            .info-box.full {

                grid-column: span 1;

            }

        }

    </style>

</head>


<body>


<!-- =========================================
     SIDEBAR
========================================= -->

<?php include "admin_sidebar.php"; ?>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <!-- HEADER -->

    <div class="page-header">

        <h1>
            Student Information
        </h1>

        <p>
            View student account details.
        </p>

    </div>


    <!-- STUDENT CARD -->

    <div class="student-card">


        <!-- =====================================
             STUDENT TITLE
        ====================================== -->

        <div class="student-title">


            <!-- PROFILE PICTURE -->

            <div class="student-picture">

                <?php if (!empty($profile_image)): ?>

                    <img
                        src="<?php echo htmlspecialchars($profile_image); ?>"
                        alt="Student Profile Picture">

                <?php else: ?>

                    <span class="default-icon">
                        👤
                    </span>

                <?php endif; ?>

            </div>


            <!-- STUDENT NAME -->

            <div>

                <h2>

                    <?php

                    echo htmlspecialchars(
                        $student['fullname'] ?? ''
                    );

                    ?>

                </h2>


                <p>
                    Student Account
                </p>

            </div>

        </div>


        <!-- =====================================
             INFORMATION
        ====================================== -->

        <div class="info-grid">


            <!-- STUDENT ID -->

            <div class="info-box">

                <div class="info-label">
                    Student ID
                </div>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $student['student_id'] ?? '-'
                    );

                    ?>

                </div>

            </div>


            <!-- FULL NAME -->

            <div class="info-box">

                <div class="info-label">
                    Full Name
                </div>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $student['fullname'] ?? '-'
                    );

                    ?>

                </div>

            </div>


            <!-- EMAIL -->

            <div class="info-box">

                <div class="info-label">
                    Email
                </div>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $student['email'] ?? '-'
                    );

                    ?>

                </div>

            </div>


            <!-- PHONE -->

            <div class="info-box">

                <div class="info-label">
                    Phone Number
                </div>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $student['phone'] ?? '-'
                    );

                    ?>

                </div>

            </div>


            <!-- ROLE -->

            <div class="info-box">

                <div class="info-label">
                    Role
                </div>

                <div class="info-value">

                    <?php

                    echo ucfirst(
                        htmlspecialchars(
                            $student['role'] ?? 'student'
                        )
                    );

                    ?>

                </div>

            </div>


            <!-- STATUS -->

            <div class="info-box">

                <div class="info-label">
                    Account Status
                </div>

                <div class="info-value">

                    <?php

                    $status =
                        $student['status'] ?? 'inactive';

                    ?>

                    <span class="status <?php echo htmlspecialchars($status); ?>">

                        <?php

                        echo ucfirst(
                            htmlspecialchars($status)
                        );

                        ?>

                    </span>

                </div>

            </div>


        </div>


        <!-- =====================================
             BUTTONS
        ====================================== -->

        <div class="button-container">


            <a
                href="manage_student.php"
                class="btn btn-back">

                ← Back to Manage Student

            </a>


            <a
                href="edit_student.php?id=<?php echo $student['id']; ?>"
                class="btn btn-edit">

                Edit Student

            </a>


        </div>


    </div>


</div>


</body>

</html>