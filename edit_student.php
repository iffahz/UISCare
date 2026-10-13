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
        status
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
// UPDATE STUDENT
// =========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $status   = $_POST['status'] ?? 'inactive';


    // =====================================
    // VALIDATION
    // =====================================

    if ($fullname === '' || $email === '') {

        $error = "Full name and email are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (!in_array($status, ['active', 'inactive'])) {

        $error = "Invalid account status.";

    } else {


        // =====================================
        // CHECK EMAIL DUPLICATE
        // =====================================

        $check_email = $conn->prepare("
            SELECT id
            FROM `user`
            WHERE email = ?
            AND id != ?
        ");

        $check_email->bind_param(
            "si",
            $email,
            $student_id
        );

        $check_email->execute();

        $email_result = $check_email->get_result();


        if ($email_result->num_rows > 0) {

            $error = "This email is already used by another account.";

        } else {


            // =====================================
            // UPDATE STUDENT
            // =====================================

            $update_sql = "
                UPDATE `user`
                SET
                    fullname = ?,
                    email = ?,
                    phone = ?,
                    status = ?
                WHERE id = ?
                AND role = 'student'
            ";

            $update_stmt = $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "ssssi",
                $fullname,
                $email,
                $phone,
                $status,
                $student_id
            );


            if ($update_stmt->execute()) {

                header(
                    "Location: view_student.php?id="
                    . $student_id
                    . "&updated=1"
                );

                exit();

            } else {

                $error = "Failed to update student information.";

            }

        }

    }

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Edit Student</title>


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

            margin: 8px 0 0 0;

            color: #666;

        }


        /* =====================================
           FORM CONTAINER
        ===================================== */

        .form-container {

            background-color: white;

            padding: 30px;

            border-radius: 10px;

            max-width: 800px;

        }


        /* =====================================
           FORM
        ===================================== */

        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-weight: bold;

            color: #374151;

        }


        .form-group input,

        .form-group select {

            width: 100%;

            padding: 11px 13px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 14px;

            outline: none;

        }


        .form-group input:focus,

        .form-group select:focus {

            border-color: #1e88e5;

        }


        /* =====================================
           READ ONLY STUDENT ID
        ===================================== */

        .readonly {

            background-color: #f1f5f9;

            color: #64748b;

            cursor: not-allowed;

        }


        .help-text {

            display: block;

            margin-top: 6px;

            font-size: 12px;

            color: #777;

        }


        /* =====================================
           ERROR MESSAGE
        ===================================== */

        .error-message {

            background-color: #fee2e2;

            color: #991b1b;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

        }


        /* =====================================
           BUTTON
        ===================================== */

        .button-container {

            display: flex;

            gap: 10px;

            margin-top: 25px;

        }


        .btn {

            display: inline-block;

            padding: 10px 18px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;

            border: none;

            cursor: pointer;

        }


        .btn-save {

            background-color: #1e88e5;

            color: white;

        }


        .btn-save:hover {

            background-color: #0f4c81;

        }


        .btn-cancel {

            background-color: #64748b;

            color: white;

        }


        .btn-cancel:hover {

            background-color: #475569;

        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 700px) {

            .sidebar {

                width: 190px;

            }


            .sidebar a {

                width: 160px;

            }


            .main-content {

                margin-left: 190px;

                padding: 20px;

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
            Edit Student
        </h1>

        <p>
            Update student account information.
        </p>

    </div>


    <!-- FORM -->

    <div class="form-container">


        <!-- ERROR -->

        <?php if (isset($error)): ?>

            <div class="error-message">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="edit_student.php?id=<?php echo $student_id; ?>">


            <!-- =====================================
                 STUDENT ID
            ====================================== -->

            <div class="form-group">

                <label>
                    Student ID
                </label>

                <input
                    type="text"
                    value="<?php echo htmlspecialchars(
                        $student['student_id'] ?? ''
                    ); ?>"
                    class="readonly"
                    readonly
                >

                <span class="help-text">

                    Student ID cannot be changed.

                </span>

            </div>


            <!-- =====================================
                 FULL NAME
            ====================================== -->

            <div class="form-group">

                <label for="fullname">
                    Full Name
                </label>

                <input
                    type="text"
                    id="fullname"
                    name="fullname"
                    value="<?php echo htmlspecialchars(
                        $student['fullname'] ?? ''
                    ); ?>"
                    required
                >

            </div>


            <!-- =====================================
                 EMAIL
            ====================================== -->

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars(
                        $student['email'] ?? ''
                    ); ?>"
                    required
                >

            </div>


            <!-- =====================================
                 PHONE
            ====================================== -->

            <div class="form-group">

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="<?php echo htmlspecialchars(
                        $student['phone'] ?? ''
                    ); ?>"
                    placeholder="Enter phone number"
                >

            </div>


            <!-- =====================================
                 STATUS
            ====================================== -->

            <div class="form-group">

                <label for="status">
                    Account Status
                </label>

                <select
                    id="status"
                    name="status"
                    required>

                    <option
                        value="active"
                        <?php
                        echo (
                            ($student['status'] ?? '')
                            === 'active'
                        )
                            ? 'selected'
                            : '';
                        ?>>

                        Active

                    </option>


                    <option
                        value="inactive"
                        <?php
                        echo (
                            ($student['status'] ?? '')
                            === 'inactive'
                        )
                            ? 'selected'
                            : '';
                        ?>>

                        Inactive

                    </option>

                </select>

            </div>


            <!-- =====================================
                 BUTTON
            ====================================== -->

            <div class="button-container">


                <button
                    type="submit"
                    class="btn btn-save">

                    Save Changes

                </button>


                <a
                    href="view_student.php?id=<?php echo $student_id; ?>"
                    class="btn btn-cancel">

                    Cancel

                </a>


            </div>


        </form>


    </div>


</div>


</body>

</html>