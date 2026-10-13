<?php
session_start();

/* ===============================

   DATABASE CONNECTION
================================ */

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "counseling_booking_system"
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


/* ===============================
   REGISTER PROCESS
================================ */

$error = "";
$success = "";
$clear_password = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = trim($_POST['student_id']);
    $fullname   = trim($_POST['fullname']);
    $email      = trim($_POST['email']);
    $mother_name = trim($_POST['mother_name']);
    $phone      = trim($_POST['phone']);
    $password   = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    /* ===============================
       VALIDATION
    ================================ */

    if (
        empty($student_id) ||
        empty($fullname) ||
        empty($email) ||
        empty($mother_name) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill in all required fields.";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    }

    elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";
        $clear_password = true;
    }

    elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";
        $clear_password = true;

    }

    else {

        /* ===============================
           CHECK STUDENT ID / EMAIL
        ================================ */

        $check = $conn->prepare("
            SELECT id
            FROM `user`
            WHERE student_id = ?
               OR email = ?
            LIMIT 1
        ");

        $check->bind_param(
            "ss",
            $student_id,
            $email
        );

        $check->execute();

        $result = $check->get_result();


        if ($result->num_rows > 0) {

            $error =
                "Student ID or email already exists.";

        }

        else {

            /* ===============================
               HASH PASSWORD
            ================================ */

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /* ===============================
               INSERT STUDENT
            ================================ */

            $stmt = $conn->prepare("
                INSERT INTO `user`
                (
                    student_id,
                    fullname,
                    email,
                    mother_name,
                    phone,
                    password,
                    role,
                    status
                )
                VALUES
                (?, ?, ?, ?, ?, ?, 'student', 'active')
            ");

            $stmt->bind_param(
                "ssssss",
                $student_id,
                $fullname,
                $email,
                $mother_name,
                $phone,
                $hashed_password
            );


            if ($stmt->execute()) {

                $success =
                    "Registration successful! You can now login.";

            }

            else {

                $error =
                    "Registration failed. Please try again.";

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

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Student Registration</title>


<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


body {

    font-family: Arial, sans-serif;

    background: #eef2f5;

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 30px;

}


.register-container {

    width: 500px;

    background: white;

    padding: 35px;

    border-radius: 15px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.1);

}


.register-container h1 {

    text-align: center;

    color: #263238;

    margin-bottom: 8px;

}


.subtitle {

    text-align: center;

    color: #78909c;

    margin-bottom: 25px;

}


.form-group {

    margin-bottom: 17px;

}


.form-group label {

    display: block;

    margin-bottom: 7px;

    font-weight: bold;

    color: #37474f;

}


.form-group input {

    width: 100%;

    padding: 11px;

    border: 1px solid #ccc;

    border-radius: 7px;

    font-size: 15px;

}


.form-group input:focus {

    outline: none;

    border-color: #1976d2;

}


.required {

    color: #d32f2f;

}


.register-btn {

    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 7px;

    background: #1976d2;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    margin-top: 5px;

}


.register-btn:hover {

    background: #1565c0;

}


.error {

    background: #ffebee;

    color: #c62828;

    padding: 12px;

    border-radius: 7px;

    margin-bottom: 20px;

    text-align: center;

}


.success {

    background: #e8f5e9;

    color: #2e7d32;

    padding: 12px;

    border-radius: 7px;

    margin-bottom: 20px;

    text-align: center;

}


.login-link {

    text-align: center;

    margin-top: 20px;

    color: #607d8b;

}


.login-link a {

    color: #1976d2;

    text-decoration: none;

    font-weight: bold;

}

</style>

</head>


<body>


<div class="register-container">


    <h1>Student Registration</h1>

    <p class="subtitle">
        Create your Counseling Booking System account
    </p>


    <!-- ERROR -->

    <?php if (!empty($error)): ?>

        <div class="error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <!-- SUCCESS -->

    <?php if (!empty($success)): ?>

        <div class="success">

            <?php
            echo htmlspecialchars($success);
            ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <!-- STUDENT ID -->

        <div class="form-group">

            <label>
                Student ID
                <span class="required">*</span>
            </label>

            <input
                type="text"
                name="student_id"
                placeholder="Example: STU001"
                value="<?php
                echo htmlspecialchars(
                    $_POST['student_id'] ?? ''
                );
                ?>"
                required
            >

        </div>


        <!-- FULL NAME -->

        <div class="form-group">

            <label>
                Full Name
                <span class="required">*</span>
            </label>

            <input
                type="text"
                name="fullname"
                placeholder="Enter your full name"
                value="<?php
                echo htmlspecialchars(
                    $_POST['fullname'] ?? ''
                );
                ?>"
                required
            >

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
                placeholder="example@gmail.com"
                value="<?php
                echo htmlspecialchars(
                    $_POST['email'] ?? ''
                );
                ?>"
                required
            >

        </div>

    <div class="form-group">

        <label>Mother's Name</label>

        <input
            type="text"
            name="mother_name"
            placeholder="Enter your mother's name"
            value="<?php
            echo htmlspecialchars(
                $_POST['mother_name'] ?? ''
            );
            ?>"
            required
        >

    </div>


        <!-- PHONE -->

        <div class="form-group">

            <label>Phone Number</label>

            <input
                type="text"
                name="phone"
                placeholder="Example: 0123456789"
                value="<?php
                echo htmlspecialchars(
                    $_POST['phone'] ?? ''
                );
                ?>"
            >

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
                placeholder="Minimum 6 characters"
                required
            >

        </div>


        <!-- CONFIRM PASSWORD -->

        <div class="form-group">

            <label>
                Confirm Password
                <span class="required">*</span>
            </label>

            <input
                type="password"
                name="confirm_password"
                placeholder="Re-enter your password"
                required
            >

        </div>


        <!-- BUTTON -->

        <button
            type="submit"
            class="register-btn">

            Register

        </button>


    </form>


    <div class="login-link">

        Already have an account?

        <a href="login.php">
            Login here
        </a>

    </div>


</div>


</body>

</html>