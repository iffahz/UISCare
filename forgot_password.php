<?php

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

$error = "";
$success = "";

$verified = false;
$user_id = null;


// =====================================
// VERIFY STUDENT ID + MOTHER'S NAME
// =====================================

if (isset($_POST['verify'])) {

    $student_id = trim($_POST['student_id'] ?? '');
    $mother_name = trim($_POST['mother_name'] ?? '');

    if (empty($student_id) || empty($mother_name)) {

        $error = "Please enter your Student ID and Mother's Name.";

    } else {

        // Check database
        $stmt = $conn->prepare("
            SELECT id
            FROM `user`
            WHERE student_id = ?
            AND mother_name = ?
            AND role = 'student'
            LIMIT 1
        ");

        $stmt->bind_param(
            "ss",
            $student_id,
            $mother_name
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            $verified = true;
            $user_id = $user['id'];

        } else {

            $error = "Student ID and Mother's Name do not match.";
        }

        $stmt->close();
    }
}


// =====================================
// RESET PASSWORD
// =====================================

if (isset($_POST['reset_password'])) {

    $user_id = intval($_POST['user_id'] ?? 0);

    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill in all password fields.";

        $verified = true;

    }

    elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

        $verified = true;

    }

    elseif (strlen($password) < 8) {

        $error = "Password must be at least 8 characters.";

        $verified = true;

    }

    else {

        // Hash new password
        $hashed_password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        // Update password
        $update = $conn->prepare("
            UPDATE `user`
            SET password = ?
            WHERE id = ?
            AND role = 'student'
        ");

        $update->bind_param(
            "si",
            $hashed_password,
            $user_id
        );


        if ($update->execute() && $update->affected_rows > 0) {

            $success = "Password has been successfully reset.";

            $verified = false;

        } else {

            $error = "Unable to reset password. Please try again.";

            $verified = true;
        }

        $update->close();
    }
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Forgot Password - Counseling Booking System</title>


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
}


.reset-container {

    width: 400px;

    background: white;

    padding: 35px;

    border-radius: 15px;

    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}


.login-logo {

    text-align: center;

    margin-bottom: 20px;
}


.login-logo img {

    width: 180px;

    height: auto;

    object-fit: contain;
}


h1 {

    text-align: center;

    color: #263238;

    margin-bottom: 8px;

    font-size: 25px;
}


.subtitle {

    text-align: center;

    color: #78909c;

    margin-bottom: 25px;

    font-size: 14px;
}


.form-group {

    margin-bottom: 18px;
}


.form-group label {

    display: block;

    margin-bottom: 7px;

    font-weight: bold;

    color: #37474f;
}


.form-group input {

    width: 100%;

    padding: 12px;

    border: 1px solid #ccc;

    border-radius: 7px;

    font-size: 15px;
}


.form-group input:focus {

    outline: none;

    border-color: #1976d2;
}


.reset-btn {

    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 7px;

    background: #1976d2;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}


.reset-btn:hover {

    background: #1565c0;
}


.verify-btn {

    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 7px;

    background: #1976d2;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}


.verify-btn:hover {

    background: #1565c0;
}


.error {

    background: #ffebee;

    color: #c62828;

    padding: 12px;

    border-radius: 7px;

    margin-bottom: 20px;

    text-align: center;

    font-size: 14px;
}


.success {

    background: #e8f5e9;

    color: #2e7d32;

    padding: 12px;

    border-radius: 7px;

    margin-bottom: 20px;

    text-align: center;

    font-size: 14px;
}


.back-login {

    text-align: center;

    margin-top: 20px;
}


.back-login a {

    color: #1976d2;

    text-decoration: none;

    font-weight: bold;

    font-size: 14px;
}


.back-login a:hover {

    text-decoration: underline;
}


.verified-box {

    background: #e8f5e9;

    color: #2e7d32;

    padding: 10px;

    border-radius: 7px;

    margin-bottom: 20px;

    text-align: center;

    font-size: 14px;

    font-weight: bold;
}

</style>

</head>


<body>


<div class="reset-container">


    <!-- LOGO -->

    <div class="login-logo">

        <img
            src="images/logo_counseling_system.png"
            alt="Counseling System Logo"
        >

    </div>


    <h1>Forgot Password?</h1>


    <p class="subtitle">

        Verify your details before resetting your password.

    </p>


    <!-- ERROR -->

    <?php if (!empty($error)): ?>

        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- SUCCESS -->

    <?php if (!empty($success)): ?>

        <div class="success">

            <?php echo htmlspecialchars($success); ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($success)): ?>

        <!-- PASSWORD RESET SUCCESS -->

        <div class="back-login">

            <a href="login.php">
                ← Back to Login
            </a>

        </div>


    <?php elseif (!$verified): ?>


        <!-- ================================= -->
        <!-- STEP 1 : VERIFY USER -->
        <!-- ================================= -->

        <form method="POST">


            <div class="form-group">

                <label>Student ID</label>

                <input
                    type="text"
                    name="student_id"
                    placeholder="Enter your student ID"
                    value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Mother's Name</label>

                <input
                    type="text"
                    name="mother_name"
                    placeholder="Enter your mother's name"
                    value="<?php echo htmlspecialchars($_POST['mother_name'] ?? ''); ?>"
                    required
                >

            </div>


            <button
                type="submit"
                name="verify"
                class="verify-btn">

                Verify Details

            </button>


        </form>


    <?php else: ?>


        <!-- ================================= -->
        <!-- STEP 2 : PASSWORD RESET -->
        <!-- ================================= -->

        <div class="verified-box">

            ✓ Student details verified successfully.

        </div>


        <form method="POST">


            <!-- Keep user ID -->

            <input
                type="hidden"
                name="user_id"
                value="<?php echo $user_id; ?>"
            >


            <div class="form-group">

                <label>New Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter new password"
                    minlength="8"
                    required
                >

            </div>


            <div class="form-group">

                <label>Confirm New Password</label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm new password"
                    minlength="8"
                    required
                >

            </div>


            <button
                type="submit"
                name="reset_password"
                class="reset-btn">

                Reset Password

            </button>


        </form>


    <?php endif; ?>


    <?php if (empty($success)): ?>

        <div class="back-login">

            <a href="login.php">
                ← Back to Login
            </a>

        </div>

    <?php endif; ?>


</div>


</body>

</html>