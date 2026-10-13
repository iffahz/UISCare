<?php

require_once "counselor_check.php";
require_once "db.php";

$counselor_id = $_SESSION['user_id'];

$message = "";
$message_type = "";


// =========================================
// GET COUNSELOR DATA
// =========================================

$sql = "
    SELECT
        id,
        fullname,
        email,
        phone,
        role,
        profile_picture
    FROM `user`
    WHERE id = ?
    AND role = 'counselor'
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $counselor_id
);

$stmt->execute();

$result = $stmt->get_result();

$counselor = $result->fetch_assoc();

// =========================================
// REMOVE PROFILE PICTURE
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['remove_picture'])
) {

    if (!empty($counselor['profile_picture'])) {

        $old_picture =
            __DIR__ .
            "/uploads/profile/" .
            $counselor['profile_picture'];

        // Delete picture file
        if (file_exists($old_picture)) {
            unlink($old_picture);
        }

        // Remove picture filename from database
        $stmt = $conn->prepare("
            UPDATE `user`
            SET profile_picture = NULL
            WHERE id = ?
            AND role = 'counselor'
        ");

        $stmt->bind_param(
            "i",
            $counselor_id
        );

        if ($stmt->execute()) {

            $counselor['profile_picture'] = "";

            $message =
                "Profile picture removed successfully.";

            $message_type = "success";

        } else {

            $message =
                "Failed to remove profile picture.";

            $message_type = "error";

        }

        $stmt->close();

    } else {

        $message =
            "No profile picture to remove.";

        $message_type = "error";

    }
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

        if (!in_array($file['type'], $allowed_types)) {

            $message = "Only JPG, JPEG, PNG and WEBP images are allowed.";
            $message_type = "error";

        } elseif ($file['size'] > 5 * 1024 * 1024) {

            $message = "Image size must be less than 5MB.";
            $message_type = "error";

        } else {

            $extension = strtolower(
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );

            $new_filename =
                "counselor_" .
                $counselor_id .
                "_" .
                time() .
                "." .
                $extension;

            $upload_dir = __DIR__ . "/uploads/profile/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $target_file =
                $upload_dir . $new_filename;

            if (move_uploaded_file(
                $file['tmp_name'],
                $target_file
            )) {

                // Delete old picture
                if (
                    !empty($counselor['profile_picture'])
                    && file_exists(
                        __DIR__ .
                        "/uploads/profile/" .
                        $counselor['profile_picture']
                    )
                ) {

                    unlink(
                        __DIR__ .
                        "/uploads/profile/" .
                        $counselor['profile_picture']
                    );

                }


                // Save new picture filename
                $stmt = $conn->prepare("
                    UPDATE `user`
                    SET profile_picture = ?
                    WHERE id = ?
                    AND role = 'counselor'
                ");

                $stmt->bind_param(
                    "si",
                    $new_filename,
                    $counselor_id
                );

                if ($stmt->execute()) {

                    $counselor['profile_picture'] =
                        $new_filename;

                    $message =
                        "Profile picture updated successfully.";

                    $message_type = "success";

                } else {

                    $message =
                        "Failed to save profile picture.";

                    $message_type = "error";

                }

            } else {

                $message =
                    "Failed to upload image.";

                $message_type = "error";

            }

        }

    } else {

        $message =
            "Please select an image.";

        $message_type = "error";

    }

}



if (!$counselor) {

    die("Counselor profile not found.");

}


// =========================================
// UPDATE PROFILE
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['update_profile'])
) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);


    if (
        empty($fullname)
        || empty($email)
    ) {

        $message =
            "Full Name and Email are required.";

        $message_type = "error";

    } else {


        // Check email
        $check = $conn->prepare("
            SELECT id
            FROM `user`
            WHERE email = ?
            AND id != ?
        ");

        $check->bind_param(
            "si",
            $email,
            $counselor_id
        );

        $check->execute();

        $check_result = $check->get_result();


        if ($check_result->num_rows > 0) {

            $message =
                "This email is already being used.";

            $message_type = "error";

        } else {


            // Update profile
            $stmt = $conn->prepare("
                UPDATE `user`
                SET
                    fullname = ?,
                    email = ?,
                    phone = ?
                WHERE id = ?
                AND role = 'counselor'
            ");

            $stmt->bind_param(
                "sssi",
                $fullname,
                $email,
                $phone,
                $counselor_id
            );


            if ($stmt->execute()) {

                $message =
                    "Profile updated successfully.";

                $message_type = "success";


                // Update displayed data
                $counselor['fullname'] = $fullname;
                $counselor['email'] = $email;
                $counselor['phone'] = $phone;

            } else {

                $message =
                    "Failed to update profile.";

                $message_type = "error";

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


    if (
        empty($current_password)
        || empty($new_password)
        || empty($confirm_password)
    ) {

        $message =
            "Please fill in all password fields.";

        $message_type = "error";

    } elseif (
        $new_password !== $confirm_password
    ) {

        $message =
            "New password and confirm password do not match.";

        $message_type = "error";

    } elseif (
        strlen($new_password) < 8
    ) {

        $message =
            "Password must be at least 8 characters.";

        $message_type = "error";

    } else {


        // Get current hashed password
        $stmt = $conn->prepare("
            SELECT password
            FROM `user`
            WHERE id = ?
            AND role = 'counselor'
        ");

        $stmt->bind_param(
            "i",
            $counselor_id
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();


        // Verify current password
        if (
            !$user
            || !password_verify(
                $current_password,
                $user['password']
            )
        ) {

            $message =
                "Current password is incorrect.";

            $message_type = "error";

        } else {


            // Hash new password
            $hashed_password =
                password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );


            $stmt = $conn->prepare("
                UPDATE `user`
                SET password = ?
                WHERE id = ?
                AND role = 'counselor'
            ");

            $stmt->bind_param(
                "si",
                $hashed_password,
                $counselor_id
            );


            if ($stmt->execute()) {

                $message =
                    "Password changed successfully.";

                $message_type = "success";

            } else {

                $message =
                    "Failed to change password.";

                $message_type = "error";

            }

        }

    }

}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Counselor Profile</title>

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


        .role-input {

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

.btn-remove {
    background-color: #e53935;
}

.btn-remove:hover {
    background-color: #c62828;
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
           RESPONSIVE
        ===================================== */

        @media (max-width: 700px) {

            .main-content {

                margin-left: 200px;

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


    <!-- HEADER -->

    <div class="header">

        <h1>
            My Profile
        </h1>

        <p>
            View and update your counselor profile.
        </p>

    </div>


    <!-- =====================================
         PROFILE
    ====================================== -->

    <div class="profile-card">


        <!-- PROFILE TOP -->

        <div class="profile-top">


           <div class="profile-picture">

    <?php if (!empty($counselor['profile_picture'])): ?>

        <img
            src="uploads/profile/<?php
                echo htmlspecialchars(
                    $counselor['profile_picture']
                );
            ?>"
            alt="Profile Picture">

    <?php else: ?>

        <?php
        echo strtoupper(
            substr(
                $counselor['fullname'],
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
                        $counselor['fullname']
                    );
                    ?>

                </h2>


                <p>
                    Counselor
                </p>

            </div>


        </div>

 <!-- =====================================
     UPLOAD PROFILE PICTURE
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

<?php if (!empty($counselor['profile_picture'])): ?>

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

</form>


        <!-- MESSAGE -->

        <?php if (!empty($message)): ?>

            <div class="message
                <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- =================================
             UPDATE PROFILE FORM
        ================================== -->

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
                            $counselor['fullname']
                        );
                    ?>"
                    required>

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
                            $counselor['email']
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
                            $counselor['phone'] ?? ''
                        );
                    ?>">

            </div>


            <div class="form-group">

                <label>
                    Role
                </label>

                <input
                    type="text"
                    value="Counselor"
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

<a href="counselor_dashboard.php" class="btn-back">
    ← Back to Dashboard
</a>


        </form>


    </div>


</div>


</body>

</html>