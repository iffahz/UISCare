<?php

require_once "admin_check.php";
require_once "db.php";

$message = "";


/* =========================================
   ADD COUNSELOR
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';


    /* CHECK REQUIRED FIELDS */

    if (
        empty($fullname) ||
        empty($email) ||
        empty($password)
    ) {

        $message = "Please fill in all required fields.";

    } else {


        /* CHECK EMAIL */

        $check = $conn->prepare("
            SELECT id
            FROM `user`
            WHERE email = ?
        ");

        $check->bind_param("s", $email);

        $check->execute();

        $result = $check->get_result();


        if ($result->num_rows > 0) {

            $message = "Email already exists.";

        } else {


            /* HASH PASSWORD */

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /* INSERT COUNSELOR */

            $stmt = $conn->prepare("
                INSERT INTO `user`
                (
                    fullname,
                    email,
                    phone,
                    password,
                    role,
                    status
                )
                VALUES (?, ?, ?, ?, 'counselor', 'active')
            ");


            $stmt->bind_param(
                "ssss",
                $fullname,
                $email,
                $phone,
                $hashed_password
            );


            if ($stmt->execute()) {

                header(
                    "Location: manage_counselor.php"
                );

                exit();

            } else {

                $message = "Failed to add counselor.";

            }

            $stmt->close();

        }

        $check->close();

    }

}

?>


<!DOCTYPE html>

<html>

<head>

    <title>Add Counselor</title>


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
           PAGE HEADER
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


        /* =====================================
           FORM CONTAINER
        ===================================== */

        .form-container {

            background-color: white;

            padding: 30px;

            border-radius: 10px;

            max-width: 700px;

        }


        /* =====================================
           FORM GROUP
        ===================================== */

        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            color: #0f4c81;

            font-weight: bold;

        }


        .form-group input {

            width: 100%;

            padding: 12px;

            border: 1px solid #cbd5e1;

            border-radius: 7px;

            font-size: 15px;

            outline: none;

        }


        .form-group input:focus {

            border-color: #1e88e5;

            box-shadow:
                0 0 0 2px
                rgba(30,136,229,0.15);

        }


        /* =====================================
           BUTTON
        ===================================== */

        .form-buttons {

            margin-top: 25px;

        }


        .btn-submit {

            background-color: #1e88e5;

            color: white;

            border: none;

            padding: 12px 20px;

            border-radius: 7px;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

        }


        .btn-submit:hover {

            background-color: #0f4c81;

        }


        .btn-cancel {

            display: inline-block;

            background-color: #e5e7eb;

            color: #333;

            padding: 12px 20px;

            border-radius: 7px;

            text-decoration: none;

            margin-left: 8px;

        }


        .btn-cancel:hover {

            background-color: #d1d5db;

        }


        /* =====================================
           MESSAGE
        ===================================== */

        .message {

            background-color: #fee2e2;

            color: #991b1b;

            padding: 12px 15px;

            border-radius: 7px;

            margin-bottom: 20px;

        }


        /* =====================================
           REQUIRED
        ===================================== */

        .required {

            color: #e53935;

        }

    </style>

</head>


<body>


<?php include "admin_sidebar.php"; ?>


<div class="main-content">


    <!-- =====================================
         HEADER
    ===================================== -->

    <div class="page-header">

        <h1>
            Add Counselor
        </h1>

    </div>


    <!-- =====================================
         MESSAGE
    ===================================== -->

    <?php if ($message): ?>

        <div class="message">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- =====================================
         FORM
    ===================================== -->

    <div class="form-container">

        <form method="POST">


            <!-- FULL NAME -->

            <div class="form-group">

                <label>

                    Full Name
                    <span class="required">*</span>

                </label>

                <input
                    type="text"
                    name="fullname"
                    value="<?php
                        echo htmlspecialchars(
                            $_POST['fullname'] ?? ''
                        );
                    ?>"
                    required>

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label>

                    Email
                    <span class="required">*</span>

                </label>

                <input
                    type="email"
                    name="email"
                    value="<?php
                        echo htmlspecialchars(
                            $_POST['email'] ?? ''
                        );
                    ?>"
                    required>

            </div>


            <!-- PHONE -->

            <div class="form-group">

                <label>
                    Phone
                </label>

                <input
                    type="text"
                    name="phone"
                    value="<?php
                        echo htmlspecialchars(
                            $_POST['phone'] ?? ''
                        );
                    ?>">

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label>

                    Password
                    <span class="required">*</span>

                </label>

                <input
                    type="password"
                    name="password"
                    required>

            </div>


            <!-- BUTTONS -->

            <div class="form-buttons">

                <button
                    type="submit"
                    class="btn-submit">

                    Add Counselor

                </button>


                <a
                    href="manage_counselor.php"
                    class="btn-cancel">

                    Cancel

                </a>

            </div>


        </form>

    </div>


</div>


</body>

</html>