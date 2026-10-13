<?php

require_once "admin_check.php";
require_once "db.php";


// =========================================
// CHECK ID
// =========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_counselor.php");
    exit();
}

$id = intval($_GET['id']);


// =========================================
// GET COUNSELOR
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
        status,
        profile_picture
    FROM `user`
    WHERE id = ?
    AND role = 'counselor'
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();


// Counselor tidak dijumpai
if ($result->num_rows === 0) {
    header("Location: manage_counselor.php");
    exit();
}

$counselor = $result->fetch_assoc();


// =========================================
// PROFILE PICTURE
// =========================================

if (!empty($counselor['profile_picture'])) {

    $profile_image =
        "uploads/profile/" .
        $counselor['profile_picture'];

} else {

    $profile_image = "";

}

?>

<!DOCTYPE html>
<html>

<head>

    <title>View Counselor</title>

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
           MAIN
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
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 25px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .header h1 {
            margin: 0;
            color: #0f4c81;
        }


        /* =====================================
           PROFILE CARD
        ===================================== */

        .profile-card {
            background-color: white;
            padding: 35px;
            border-radius: 12px;

            max-width: 800px;

            margin: 0 auto;

            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }


        /* =====================================
           PROFILE HEADER
        ===================================== */

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }


        /* PROFILE PICTURE */

        .profile-picture {
            width: 110px;
            height: 110px;

            border-radius: 50%;

            margin: 0 auto 15px;

            overflow: hidden;

            background-color: #dbeafe;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 3px solid #bfdbfe;
        }


        .profile-picture img {
            width: 100%;
            height: 100%;

            object-fit: contain;

            display: block;
        }


        .default-icon {
            font-size: 50px;
            color: #1565c0;
        }


        .profile-header h2 {
            margin: 10px 0 5px;
            color: #0f4c81;
        }


        .profile-header p {
            margin: 0;
            color: #777;
        }


        /* =====================================
           INFORMATION
        ===================================== */

        .info-container {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 20px;
        }


        .info-box {
            background-color: #f8fafc;

            padding: 18px;

            border-radius: 8px;

            border-left: 4px solid #2196f3;
        }


        .info-box label {
            display: block;

            font-size: 13px;

            color: #777;

            margin-bottom: 7px;

            font-weight: bold;
        }


        .info-box p {
            margin: 0;

            font-size: 16px;

            color: #333;

            word-break: break-word;
        }


        /* =====================================
           STATUS
        ===================================== */

        .status {
            display: inline-block;

            padding: 6px 14px;

            border-radius: 20px;

            font-weight: bold;

            font-size: 14px;
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
           BUTTONS
        ===================================== */

        .actions {
            margin-top: 30px;

            display: flex;

            gap: 10px;

            justify-content: center;
        }


        .btn {
            padding: 11px 20px;

            border-radius: 7px;

            text-decoration: none;

            font-weight: bold;

            border: none;

            cursor: pointer;

            transition: 0.2s;
        }


        .btn-back {
            background-color: #64748b;
            color: white;
        }


        .btn-back:hover {
            background-color: #475569;
        }


        .btn-edit {
            background-color: #1e88e5;
            color: white;
        }


        .btn-edit:hover {
            background-color: #0f4c81;
        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 700px) {

            .main-content {
                padding: 20px;
            }


            .info-container {
                grid-template-columns: 1fr;
            }


            .profile-card {
                padding: 25px;
            }


            .actions {
                flex-direction: column;
            }


            .btn {
                text-align: center;
            }

        }

    </style>

</head>


<body>


<div class="main-content">


    <!-- =====================================
         HEADER
    ===================================== -->

    <div class="header">

        <h1>
            Counselor Profile
        </h1>

    </div>


    <!-- =====================================
         PROFILE CARD
    ===================================== -->

    <div class="profile-card">


        <!-- PROFILE HEADER -->

        <div class="profile-header">


            <!-- PROFILE PICTURE -->

            <div class="profile-picture">

                <?php if (!empty($profile_image)): ?>

                    <img
                        src="<?php echo htmlspecialchars($profile_image); ?>"
                        alt="Counselor Profile Picture">

                <?php else: ?>

                    <span class="default-icon">
                        👤
                    </span>

                <?php endif; ?>

            </div>


            <!-- NAME -->

            <h2>

                <?php
                echo htmlspecialchars(
                    $counselor['fullname'] ?? ''
                );
                ?>

            </h2>


            <p>
                Counselor
            </p>


        </div>


        <!-- =====================================
             INFORMATION
        ===================================== -->

        <div class="info-container">


            <!-- FULL NAME -->

            <div class="info-box">

                <label>
                    Full Name
                </label>

                <p>

                    <?php
                    echo htmlspecialchars(
                        $counselor['fullname'] ?? ''
                    );
                    ?>

                </p>

            </div>


            <!-- EMAIL -->

            <div class="info-box">

                <label>
                    Email
                </label>

                <p>

                    <?php
                    echo htmlspecialchars(
                        $counselor['email'] ?? ''
                    );
                    ?>

                </p>

            </div>


            <!-- PHONE -->

            <div class="info-box">

                <label>
                    Phone
                </label>

                <p>

                    <?php
                    echo htmlspecialchars(
                        $counselor['phone'] ?? ''
                    );
                    ?>

                </p>

            </div>

            <!-- LICENSE -->

<div class="info-box">

    <label>
        License / Registration No.
    </label>

    <p>

        <?php
        echo htmlspecialchars(
            $counselor['license_no'] ?? 'Not provided'
        );
        ?>

    </p>

</div>


<!-- QUALIFICATION -->

<div class="info-box">

    <label>
        Qualification
    </label>

    <p>

        <?php
        echo htmlspecialchars(
            $counselor['qualification'] ?? 'Not provided'
        );
        ?>

    </p>

</div>


<!-- SPECIALIZATION -->

<div class="info-box">

    <label>
        Specialization
    </label>

    <p>

        <?php
        echo htmlspecialchars(
            $counselor['specialization'] ?? 'Not provided'
        );
        ?>

    </p>

</div>



            <!-- STATUS -->

            <div class="info-box">

                <label>
                    Status
                </label>

                <p>

                    <span class="status
                        <?php
                        echo strtolower(
                            $counselor['status'] ?? 'inactive'
                        );
                        ?>">

                        <?php
                        echo ucfirst(
                            $counselor['status'] ?? 'Inactive'
                        );
                        ?>

                    </span>

                </p>

            </div>


        </div>


        <!-- =====================================
             ACTIONS
        ===================================== -->

        <div class="actions">


            <a
                href="manage_counselor.php"
                class="btn btn-back">

                Back

            </a>


            <a
                href="edit_counselor.php?id=<?php echo $counselor['id']; ?>"
                class="btn btn-edit">

                Edit Counselor

            </a>


        </div>


    </div>


</div>


</body>

</html>