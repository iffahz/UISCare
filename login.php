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
   LOGIN PROCESS
================================ */

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];


    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare("
            SELECT
                id,
                student_id,
                fullname,
                email,
                password,
                role,
                status
            FROM `user`
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();


            /* CHECK ACCOUNT STATUS */

            if ($user['status'] !== 'active') {

                $error = "Your account is inactive.";

            }


            /* CHECK PASSWORD */

            elseif (
                password_verify(
                    $password,
                    $user['password']
                )
            ) {

                /* CREATE SESSION */

                $_SESSION['user_id'] =
                    $user['id'];

                $_SESSION['student_id'] =
                    $user['student_id'];

                $_SESSION['fullname'] =
                    $user['fullname'];

                $_SESSION['email'] =
                    $user['email'];

                $_SESSION['role'] =
                    $user['role'];


                /* ===============================
                   REDIRECT BASED ON ROLE
                ================================ */

                if ($user['role'] === 'admin') {

                    header(
                        "Location: admin_dashboard.php"
                    );

                    exit();

                }

                elseif ($user['role'] === 'student') {

                    header(
                        "Location: student_dashboard.php"
                    );

                    exit();

                }

                elseif ($user['role'] === 'counselor') {

                    header(
                        "Location: counselor_dashboard.php"
                    );

                    exit();

                }

            }

            else {

                $error = "Invalid email or password.";

            }

        }

        else {

            $error = "Invalid email or password.";

        }

        $stmt->close();

    }
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Login - Counseling Booking System</title>


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


.login-container {

    width: 400px;

    background: white;

    padding: 35px;

    border-radius: 15px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.1);

}

.login-logo {
    text-align: center;
    margin-bottom: 20px;
}

.login-logo img {
    width: 200px;
    height: auto;
    object-fit: contain;
}


.login-container h1 {

    text-align: center;

    color: #263238;

    margin-bottom: 8px;

}


.subtitle {

    text-align: center;

    color: #78909c;

    margin-bottom: 30px;

}


.form-group {

    margin-bottom: 20px;

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


.login-btn {

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


.login-btn:hover {

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


.register {

    text-align: center;

    margin-top: 20px;

    color: #607d8b;

}


.register a {

    color: #1976d2;
    text-decoration: none;
    font-weight: bold;

}


.forgot-password {
    text-align: center;
    margin-top: 10px;
}

.forgot-password a {
    color: #1976d2;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
}

.forgot-password a:hover {
    text-decoration: underline;
}

</style>

</head>


<body>


<div class="login-container">

<div class="login-logo">
    <img src="images/logo_counseling_system.png" alt="Counseling System Logo">
</div>

    <?php if (!empty($error)): ?>

        <div class="error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <div class="form-group">

            <label>Email</label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required
            >

        </div>


        <div class="form-group">

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >

        </div>

        <button
            type="submit"
            class="login-btn">

            Login

        </button>


    </form>


    <div class="register">

        Don't have an account?

        <a href="register.php">
            Register as Student
        </a>

    </div>
    
    <div class="forgot-password">
        
        <a href="forgot_password.php">
            Forgot Password?
        </a>    
    </div>
        

</div>


</body>

</html>