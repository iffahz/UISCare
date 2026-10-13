<?php

require_once "student_check.php";
require_once "db.php";

$student_id = $_SESSION['user_id'];

$message = "";
$message_type = "";


// =========================================
// GET STUDENT DATA
// =========================================

$sql = "
    SELECT
        id,
        fullname,
        student_id,
        email,
        phone,
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

$student = $result->fetch_assoc();


// =========================================
// CHECK STUDENT
// =========================================

if (!$student) {

    die("Student profile not found.");

}


// =========================================
// UPLOAD PROFILE PICTURE
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['upload_picture'])
) {

    if (
        isset($_FILES['profile_picture'])
        && $_FILES['profile_picture']['error'] === 0
    ) {

        $file = $_FILES['profile_picture'];

        $allowed_types = [
            'image/jpeg',
            'image/png',
            'image/jpg',
            'image/webp'
        ];


        // =========================================
        // CHECK FILE TYPE
        // =========================================

        if (!in_array($file['type'], $allowed_types)) {

            $message =
                "Only JPG, JPEG, PNG and WEBP images are allowed.";

            $message_type = "error";

        }


        // =========================================
        // CHECK FILE SIZE
        // =========================================

        elseif ($file['size'] > 5 * 1024 * 1024) {

            $message =
                "Image size must be less than 5MB.";

            $message_type = "error";

        }


        else {

            $extension = strtolower(
                pathinfo(
                    $file['name'],
                    PATHINFO_EXTENSION
                )
            );


            // =========================================
            // CREATE NEW FILENAME
            // =========================================

            $new_filename =
                "student_" .
                $student_id .
                "_" .
                time() .
                "." .
                $extension;


            // =========================================
            // UPLOAD DIRECTORY
            // =========================================

            $upload_dir =
                __DIR__ . "/uploads/profile/";


            if (!is_dir($upload_dir)) {

                mkdir(
                    $upload_dir,
                    0777,
                    true
                );

            }


            $target_file =
                $upload_dir . $new_filename;


            // =========================================
            // MOVE UPLOADED FILE
            // =========================================

            if (
                move_uploaded_file(
                    $file['tmp_name'],
                    $target_file
                )
            ) {


                // =========================================
                // DELETE OLD PROFILE PICTURE
                // =========================================

                if (
                    !empty($student['profile_picture'])
                ) {

                    $old_picture =
                        __DIR__ .
                        "/uploads/profile/" .
                        basename(
                            $student['profile_picture']
                        );


                    if (
                        file_exists($old_picture)
                        && is_file($old_picture)
                    ) {

                        unlink($old_picture);

                    }

                }


                // =========================================
                // SAVE NEW PROFILE PICTURE
                // =========================================

                $stmt = $conn->prepare("
                    UPDATE `user`
                    SET profile_picture = ?
                    WHERE id = ?
                    AND role = 'student'
                ");

                $stmt->bind_param(
                    "si",
                    $new_filename,
                    $student_id
                );


                if ($stmt->execute()) {

                    $student['profile_picture'] =
                        $new_filename;

                    $message =
                        "Profile picture updated successfully.";

                    $message_type =
                        "success";

                }

                else {

                    // Delete uploaded image
                    // if database update fails

                    if (
                        file_exists($target_file)
                    ) {

                        unlink($target_file);

                    }


                    $message =
                        "Failed to save profile picture.";

                    $message_type =
                        "error";

                }

            }

            else {

                $message =
                    "Failed to upload image.";

                $message_type =
                    "error";

            }

        }

    }

    else {

        $message =
            "Please select an image.";

        $message_type =
            "error";

    }

}


// =========================================
// REMOVE PROFILE PICTURE
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['remove_picture'])
) {

    // =========================================
    // DELETE IMAGE FROM FOLDER
    // =========================================

    if (
        !empty($student['profile_picture'])
    ) {

        $old_picture =
            __DIR__ .
            "/uploads/profile/" .
            basename(
                $student['profile_picture']
            );


        if (
            file_exists($old_picture)
            && is_file($old_picture)
        ) {

            unlink($old_picture);

        }

    }


    // =========================================
    // REMOVE IMAGE FROM DATABASE
    // =========================================

    $stmt = $conn->prepare("
        UPDATE `user`
        SET profile_picture = NULL
        WHERE id = ?
        AND role = 'student'
    ");

    $stmt->bind_param(
        "i",
        $student_id
    );


    if ($stmt->execute()) {

        $student['profile_picture'] = null;

        $message =
            "Profile picture removed successfully.";

        $message_type =
            "success";

    }

    else {

        $message =
            "Failed to remove profile picture.";

        $message_type =
            "error";

    }

}


// =========================================
// UPDATE PROFILE
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['update_profile'])
) {

    $fullname =
        trim($_POST['fullname']);

    $email =
        trim($_POST['email']);

    $phone =
        trim($_POST['phone']);


    // =========================================
    // CHECK REQUIRED FIELDS
    // =========================================

    if (
        empty($fullname)
        || empty($email)
    ) {

        $message =
            "Full Name and Email are required.";

        $message_type =
            "error";

    }

    else {


        // =========================================
        // CHECK EMAIL
        // =========================================

        $check = $conn->prepare("
            SELECT id
            FROM `user`
            WHERE email = ?
            AND id != ?
        ");

        $check->bind_param(
            "si",
            $email,
            $student_id
        );

        $check->execute();

        $check_result =
            $check->get_result();


        if (
            $check_result->num_rows > 0
        ) {

            $message =
                "This email is already being used.";

            $message_type =
                "error";

        }

        else {


            // =========================================
            // UPDATE STUDENT PROFILE
            // =========================================

            $stmt = $conn->prepare("
                UPDATE `user`
                SET
                    fullname = ?,
                    email = ?,
                    phone = ?
                WHERE id = ?
                AND role = 'student'
            ");

            $stmt->bind_param(
                "sssi",
                $fullname,
                $email,
                $phone,
                $student_id
            );


            if ($stmt->execute()) {

                $message =
                    "Profile updated successfully.";

                $message_type =
                    "success";


                // Update displayed data

                $student['fullname'] =
                    $fullname;

                $student['email'] =
                    $email;

                $student['phone'] =
                    $phone;

            }

            else {

                $message =
                    "Failed to update profile.";

                $message_type =
                    "error";

            }

        }

    }

}


// =========================================
// CHANGE PASSWORD
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['change_password'])
) {

    $current_password =
        $_POST['current_password'];

    $new_password =
        $_POST['new_password'];

    $confirm_password =
        $_POST['confirm_password'];


    // =========================================
    // CHECK EMPTY
    // =========================================

    if (
        empty($current_password)
        || empty($new_password)
        || empty($confirm_password)
    ) {

        $message =
            "Please fill in all password fields.";

        $message_type =
            "error";

    }


    // =========================================
    // CHECK PASSWORD MATCH
    // =========================================

    elseif (
        $new_password !== $confirm_password
    ) {

        $message =
            "New password and confirm password do not match.";

        $message_type =
            "error";

    }


    // =========================================
    // CHECK PASSWORD LENGTH
    // =========================================

    elseif (
        strlen($new_password) < 8
    ) {

        $message =
            "Password must be at least 8 characters.";

        $message_type =
            "error";

    }


    else {


        // =========================================
        // GET CURRENT PASSWORD
        // =========================================

        $stmt = $conn->prepare("
            SELECT password
            FROM `user`
            WHERE id = ?
            AND role = 'student'
        ");

        $stmt->bind_param(
            "i",
            $student_id
        );

        $stmt->execute();

        $result =
            $stmt->get_result();

        $user =
            $result->fetch_assoc();


        // =========================================
        // VERIFY PASSWORD
        // =========================================

        if (
            !$user
            || !password_verify(
                $current_password,
                $user['password']
            )
        ) {

            $message =
                "Current password is incorrect.";

            $message_type =
                "error";

        }

        else {


            // =========================================
            // HASH NEW PASSWORD
            // =========================================

            $hashed_password =
                password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );


            // =========================================
            // UPDATE PASSWORD
            // =========================================

            $stmt = $conn->prepare("
                UPDATE `user`
                SET password = ?
                WHERE id = ?
                AND role = 'student'
            ");

            $stmt->bind_param(
                "si",
                $hashed_password,
                $student_id
            );


            if ($stmt->execute()) {

                $message =
                    "Password changed successfully.";

                $message_type =
                    "success";

            }

            else {

                $message =
                    "Failed to update password.";

                $message_type =
                    "error";

            }

        }

    }

}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Student Profile</title>

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


        /* =====================================
           HEADER
        ===================================== */

        .header {

            background-color: white;

            padding: 25px;

            border-radius: 10px;

            margin-bottom: 25px;

        }


        .header h1 {

            margin: 0;

            color: #0f4c81;

        }


        .header p {

            color: #666;

            margin-bottom: 0;

        }


        /* =====================================
           PROFILE CARD
        ===================================== */

        .profile-card {

            background-color: white;

            padding: 30px;

            border-radius: 10px;

            margin-bottom: 25px;

        }


        /* =====================================
           PROFILE TOP
        ===================================== */

        .profile-top {

            display: flex;

            align-items: center;

            gap: 25px;

            padding-bottom: 25px;

            border-bottom: 1px solid #ddd;

            margin-bottom: 25px;

        }


        /* =====================================
           PROFILE PICTURE
        ===================================== */

        .profile-picture {

            width: 110px;

            height: 110px;

            border-radius: 10px;

            background-color: #b5c7e3;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 45px;

            color: #0f4c81;

            font-weight: bold;

            overflow: hidden;

            flex-shrink: 0;

        }


        .profile-picture img {

            width: 100%;

            height: 100%;

            object-fit: contain;

        }


        /* =====================================
           PROFILE NAME
        ===================================== */

        .profile-name h2 {

            margin: 0 0 8px;

            color: #0f4c81;

        }


        .profile-name p {

            margin: 0;

            color: #666;

        }


        /* =====================================
           MESSAGE
        ===================================== */

        .message {

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

        }


        .success {

            background-color: #dcfce7;

            color: #166534;

        }


        .error {

            background-color: #fee2e2;

            color: #b91c1c;

        }


        /* =====================================
           FORM
        ===================================== */

        .form-group {

            margin-bottom: 18px;

        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

            color: #0f4c81;

        }


        .form-group input {

            width: 100%;

            padding: 11px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 14px;

        }


        .form-group input:focus {

            outline: none;

            border-color: #1e88e5;

        }


        .role-input,
        .student-id-input {

            background-color: #f1f5f9;

        }


        /* =====================================
           BUTTON
        ===================================== */

        .btn {

            border: none;

            padding: 11px 18px;

            border-radius: 6px;

            color: white;

            cursor: pointer;

            font-weight: bold;

        }


        .btn-update {

            background-color: #1e88e5;

        }


        .btn-update:hover {

            background-color: #0f4c81;

        }


        .btn-password {

            background-color: #7e57c2;

        }


        .btn-password:hover {

            background-color: #5e35b1;

        }


        .btn-remove {

            background-color: #dc2626;

        }


        .btn-remove:hover {

            background-color: #b91c1c;

        }


        /* =====================================
           PASSWORD SECTION
        ===================================== */

        .password-section {

            background-color: white;

            padding: 30px;

            border-radius: 10px;

        }


        .password-section h2 {

            color: #0f4c81;

            margin-top: 0;

        }


        /* =====================================
           BACK BUTTON
        ===================================== */

        .btn-back {

            display: inline-block;

            margin-top: 15px;

            padding: 11px 18px;

            background-color: #64748b;

            color: white;

            text-decoration: none;

            border-radius: 6px;

            font-weight: bold;

        }


        .btn-back:hover {

            background-color: #475569;

        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 700px) {

            .main-content {

                padding: 20px;

            }


            .profile-top {

                flex-direction: column;

                align-items: flex-start;

            }

        }

    </style>

</head>


<body>


<!-- =====================================
     MAIN CONTENT
===================================== -->

<div class="main-content">


    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="header">

        <h1>
            My Profile
        </h1>

        <p>
            View and update your student profile.
        </p>

    </div>


    <!-- =====================================
         PROFILE
    ====================================== -->

    <div class="profile-card">


        <!-- =====================================
             PROFILE TOP
        ====================================== -->

        <div class="profile-top">


            <div class="profile-picture">

                <?php if (!empty($student['profile_picture'])): ?>

                    <img
                        src="uploads/profile/<?php
                            echo htmlspecialchars(
                                $student['profile_picture']
                            );
                        ?>"
                        alt="Profile Picture">

                <?php else: ?>

                    <?php

                    echo strtoupper(
                        substr(
                            $student['fullname'],
                            0,
                            1
                        )
                    );

                    ?>

                <?php endif; ?>

            </div>


            <div class="profile-name">

                <h2>

                    <?php

                    echo htmlspecialchars(
                        $student['fullname']
                    );

                    ?>

                </h2>


                <p>
                    Student
                </p>

            </div>


        </div>


        <!-- =====================================
             MESSAGE
        ====================================== -->

        <?php if (!empty($message)): ?>

            <div class="message
                <?php echo htmlspecialchars($message_type); ?>">

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- =====================================
             PROFILE PICTURE UPLOAD
        ====================================== -->

        <form
            method="POST"
            enctype="multipart/form-data"
            style="margin-bottom: 10px;">

            <div class="form-group">

                <label>
                    Profile Picture
                </label>


                <input
                    type="file"
                    name="profile_picture"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    required>

            </div>


            <button
                type="submit"
                name="upload_picture"
                class="btn btn-update">

                Upload Picture

            </button>

        </form>


        <!-- =====================================
             REMOVE PROFILE PICTURE
        ====================================== -->

        <?php if (!empty($student['profile_picture'])): ?>

            <form
                method="POST"
                style="margin-bottom: 25px;">

                <button
                    type="submit"
                    name="remove_picture"
                    class="btn btn-remove"
                    onclick="return confirm('Are you sure you want to remove your profile picture?');">

                    Remove Picture

                </button>

            </form>

        <?php endif; ?>


        <!-- =====================================
             UPDATE PROFILE
        ====================================== -->

        <form method="POST">


            <div class="form-group">

                <label>
                    Full Name
                </label>


                <input
                    type="text"
                    name="fullname"
                    value="<?php

                        echo htmlspecialchars(
                            $student['fullname']
                        );

                    ?>"
                    required>

            </div>


            <div class="form-group">

                <label>
                    Student ID
                </label>


                <input
                    type="text"
                    value="<?php

                        echo htmlspecialchars(
                            $student['student_id'] ?? ''
                        );

                    ?>"
                    class="student-id-input"
                    readonly>

            </div>


            <div class="form-group">

                <label>
                    Email
                </label>


                <input
                    type="email"
                    name="email"
                    value="<?php

                        echo htmlspecialchars(
                            $student['email']
                        );

                    ?>"
                    required>

            </div>


            <div class="form-group">

                <label>
                    Phone
                </label>


                <input
                    type="text"
                    name="phone"
                    value="<?php

                        echo htmlspecialchars(
                            $student['phone'] ?? ''
                        );

                    ?>">

            </div>


            <div class="form-group">

                <label>
                    Role
                </label>


                <input
                    type="text"
                    value="Student"
                    class="role-input"
                    readonly>

            </div>


            <button
                type="submit"
                name="update_profile"
                class="btn btn-update">

                Update Profile

            </button>


        </form>


    </div>


    <!-- =====================================
         CHANGE PASSWORD
    ====================================== -->

    <div class="password-section">


        <h2>
            Change Password
        </h2>


        <form method="POST">


            <div class="form-group">

                <label>
                    Current Password
                </label>


                <input
                    type="password"
                    name="current_password"
                    required>

            </div>


            <div class="form-group">

                <label>
                    New Password
                </label>


                <input
                    type="password"
                    name="new_password"
                    minlength="8"
                    required>

            </div>


            <div class="form-group">

                <label>
                    Confirm New Password
                </label>


                <input
                    type="password"
                    name="confirm_password"
                    minlength="8"
                    required>

            </div>


            <button
                type="submit"
                name="change_password"
                class="btn btn-password">

                Change Password

            </button>


            <br>


            <a
                href="student_dashboard.php"
                class="btn-back">

                ← Back to Dashboard

            </a>


        </form>


    </div>


</div>


</body>

</html>
