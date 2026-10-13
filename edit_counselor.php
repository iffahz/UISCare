<?php

require_once "admin_check.php";
require_once "db.php";

$message = "";
$message_type = "";


// =========================================
// CHECK ID
// =========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: manage_counselor.php");
    exit();

}

$id = intval($_GET['id']);


// =========================================
// UPDATE COUNSELOR
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname       = trim($_POST['fullname']);
    $email          = trim($_POST['email']);
    $phone          = trim($_POST['phone']);
    $license_no     = trim($_POST['license_no']);
    $qualification  = trim($_POST['qualification']);
    $specialization = trim($_POST['specialization']);
    $status         = $_POST['status'];


    // Check required fields

    if (empty($fullname) || empty($email)) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } else {


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
            $id
        );

        $check->execute();

        $check_result = $check->get_result();


        if ($check_result->num_rows > 0) {

            $message = "This email is already used by another account.";
            $message_type = "error";

        } else {


            // =========================================
            // UPDATE
            // =========================================

            $stmt = $conn->prepare("
    UPDATE `user`
    SET
        fullname = ?,
        email = ?,
        phone = ?,
        license_no = ?,
        qualification = ?,
        specialization = ?,
        status = ?
    WHERE id = ?
    AND role = 'counselor'
");

$stmt->bind_param(
    "sssssssi",
    $fullname,
    $email,
    $phone,
    $license_no,
    $qualification,
    $specialization,
    $status,
    $id
);


            if ($stmt->execute()) {

                header(
                    "Location: view_counselor.php?id=" . $id
                );

                exit();

            } else {

                $message = "Failed to update counselor.";
                $message_type = "error";

            }

        }

    }

}


// =========================================
// GET COUNSELOR DATA
// =========================================

$stmt = $conn->prepare("
    SELECT
        id,
        fullname,
        email,
        phone,
        license_no,
        qualification,
        specialization,
        status
    FROM `user`
    WHERE id = ?
    AND role = 'counselor'
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    header("Location: manage_counselor.php");
    exit();

}


$counselor = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Counselor</title>


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

            margin-left: 250px;

            padding: 30px;

        }


        /* =====================================
           HEADER
        ===================================== */

        .header {

            background-color: white;

            padding: 20px 25px;

            border-radius: 10px;

            margin-bottom: 25px;

        }


        .header h1 {

            margin: 0;

            color: #0f4c81;

        }


        /* =====================================
           FORM CONTAINER
        ===================================== */

        .form-container {

            background-color: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 750px;
            margin-right: auto;
            margin-left: auto;

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

            font-weight: bold;

            color: #0f4c81;

        }


        .form-group input,
        .form-group select {

            width: 100%;

            padding: 12px;

            border: 1px solid #cbd5e1;

            border-radius: 7px;

            font-size: 15px;

            outline: none;

        }


        .form-group input:focus,
        .form-group select:focus {

            border-color: #1e88e5;

        }


        /* =====================================
           MESSAGE
        ===================================== */

        .message {

            padding: 12px;

            border-radius: 7px;

            margin-bottom: 20px;

            font-weight: bold;

        }


        .message.error {

            background-color: #fee2e2;

            color: #991b1b;

        }


        /* =====================================
           BUTTONS
        ===================================== */

        .buttons {

            display: flex;

            gap: 10px;

            margin-top: 25px;

        }


        .btn {

            padding: 11px 20px;

            border-radius: 7px;

            text-decoration: none;

            border: none;

            font-size: 15px;

            font-weight: bold;

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
           NOTE
        ===================================== */

        .note {

            background-color: #eff6ff;

            border-left: 4px solid #2196f3;

            padding: 12px;

            margin-bottom: 25px;

            color: #1e3a8a;

        }

    </style>

</head>


<body>


<div class="main-content">


    <!-- HEADER -->

    <div class="header">

        <h1>
            Edit Counselor
        </h1>

    </div>


    <!-- FORM -->

    <div class="form-container">

            <div class="note">

            Admin can update the counselor's
            personal, professional and account information.

            </div>



        <?php if ($message): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- FULL NAME -->

            <div class="form-group">

                <label>
                    Full Name
                </label>

                <input
                    type="text"
                    name="fullname"
                    value="<?php
                    echo htmlspecialchars(
                        $counselor['fullname'] ?? ''
                    );
                    ?>"
                    required>

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label>
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="<?php
                    echo htmlspecialchars(
                        $counselor['email'] ?? ''
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
                        $counselor['phone'] ?? ''
                    );
                    ?>">

            </div>

            <!-- LICENSE / REGISTRATION NO. -->

<div class="form-group">

    <label>
        License / Registration No.
    </label>

    <input
        type="text"
        name="license_no"
        value="<?php
        echo htmlspecialchars(
            $counselor['license_no'] ?? ''
        );
        ?>"
        placeholder="Enter license or registration number">

</div>


<!-- QUALIFICATION -->

<div class="form-group">

    <label>
        Qualification
    </label>

    <input
        type="text"
        name="qualification"
        value="<?php
        echo htmlspecialchars(
            $counselor['qualification'] ?? ''
        );
        ?>"
        placeholder="e.g. Bachelor of Counselling">

</div>


<!-- SPECIALIZATION -->

<div class="form-group">

    <label>
        Specialization
    </label>

    <input
        type="text"
        name="specialization"
        value="<?php
        echo htmlspecialchars(
            $counselor['specialization'] ?? ''
        );
        ?>"
        placeholder="e.g. Student Counselling">

</div>


            <!-- STATUS -->

            <div class="form-group">

                <label>
                    Account Status
                </label>

                <select name="status">

                    <option
                        value="active"
                        <?php
                        echo ($counselor['status'] == 'active')
                            ? 'selected'
                            : '';
                        ?>>

                        Active

                    </option>


                    <option
                        value="inactive"
                        <?php
                        echo ($counselor['status'] == 'inactive')
                            ? 'selected'
                            : '';
                        ?>>

                        Inactive

                    </option>

                </select>

            </div>


            <!-- BUTTONS -->

            <div class="buttons">

                <button
                    type="submit"
                    class="btn btn-save">

                    Save Changes

                </button>


                <a
                    href="view_counselor.php?id=<?php echo $id; ?>"
                    class="btn btn-cancel">

                    Cancel

                </a>

            </div>


        </form>


    </div>


</div>


</body>

</html>