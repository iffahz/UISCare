<?php

require_once "counselor_check.php";
require_once "db.php";

$counselor_id = $_SESSION['user_id'];


// =========================================
// DELETE CONSULTATION RECORD
// =========================================

if (isset($_GET['delete'])) {

    $delete_id = intval($_GET['delete']);

    // Pastikan record tersebut memang milik counselor yang sedang login
    $delete_sql = "
        DELETE c
        FROM consultation c

        INNER JOIN appointment a
            ON c.appointment_id = a.id

        WHERE c.id = ?
        AND a.counselor_user_id = ?
    ";

    $delete_stmt = $conn->prepare($delete_sql);

    $delete_stmt->bind_param(
        "ii",
        $delete_id,
        $counselor_id
    );

    $delete_stmt->execute();

    $delete_stmt->close();

    header("Location: consultation_records.php?deleted=1");
    exit();
}


// =========================================
// SEARCH STUDENT
// =========================================

$search = "";

if (isset($_GET['search'])) {

    $search = trim($_GET['search']);

}


// =========================================
// GET CONSULTATION RECORDS
// =========================================

$sql = "
    SELECT
        c.id,
        s.student_id,
        s.fullname AS student_name,
        a.appointment_date,
        c.notes,
        c.status,
        c.created_at

    FROM consultation c

    INNER JOIN appointment a
        ON c.appointment_id = a.id

    INNER JOIN `user` s
        ON a.student_user_id = s.id

    WHERE a.counselor_user_id = ?
";


// =========================================
// SEARCH CONDITION
// =========================================

if ($search !== "") {

    $sql .= "
        AND (
            s.student_id LIKE ?
            OR s.fullname LIKE ?
        )
    ";

}

$sql .= "
    ORDER BY c.created_at DESC
";


$stmt = $conn->prepare($sql);


// =========================================
// BIND PARAMETERS
// =========================================

if ($search !== "") {

    $search_param = "%" . $search . "%";

    $stmt->bind_param(
        "iss",
        $counselor_id,
        $search_param,
        $search_param
    );

} else {

    $stmt->bind_param(
        "i",
        $counselor_id
    );

}


$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Consultation Records</title>

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
   SIDEBAR
===================================== */

.navbar {
    position: fixed;
    left: 0;
    top: 0;
    width: 220px;
    height: 100vh;
    background-color: #0f4c81;
    padding: 25px 0;
    display: flex;
    flex-direction: column;
    z-index: 1000;
}


/* LOGO */

.logo {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 35px;
}

.logo img {
    width: 150px;
    height: auto;
    display: block;
}


/* MENU */

.menu {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.menu a {
    width: 180px;
    margin: 0 auto;
    padding: 12px 18px;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 15px;
}

.menu a:hover,
.menu a.active {
    background-color: #1e88e5;
}


/* LOGOUT */

.navbar > .logout {
    width: 180px;
    margin-top: auto;
    margin-left: auto;
    margin-right: auto;
    padding: 12px 18px;
    text-align: center;
    background-color: #e53935;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}

.navbar > .logout:hover {
    background-color: #c62828;
}


        /* =====================================
           MAIN CONTENT
        ===================================== */

        .main-content {

            margin-left: 250px;
            min-height: 30px;
            padding: 30px;

        }


        /* =====================================
           HEADER
        ===================================== */

        .header {

            background-color: white;

            padding: 25px;

            border-radius: 10px;

            margin-bottom: 20px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .header h1 {

            margin: 0;

            color: #0f4c81;

        }


        .header p {

            margin-bottom: 0;

            color: #666;

        }


        /* =====================================
           HEADER BUTTONS
        ===================================== */

        .header-buttons {

            display: flex;

            gap: 10px;

            align-items: center;

        }


        .btn-add {

            background-color: #1e88e5;

            color: white;

            padding: 10px 16px;

            text-decoration: none;

            border-radius: 6px;

            font-weight: bold;

        }


        .btn-add:hover {

            background-color: #0f4c81;

        }


        .btn-report {

            background-color: #00897b;

            color: white;

            padding: 10px 16px;

            text-decoration: none;

            border-radius: 6px;

            font-weight: bold;

        }


        .btn-report:hover {

            background-color: #00695c;

        }


        /* =====================================
           SEARCH
        ===================================== */

        .search-container {

            background-color: white;

            padding: 20px;

            border-radius: 10px;

            margin-bottom: 20px;

        }


        .search-form {

            display: flex;

            gap: 10px;

        }


        .search-form input {

            flex: 1;

            padding: 11px 14px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 14px;

        }


        .search-btn {

            background-color: #1565c0;

            color: white;

            border: none;

            padding: 11px 18px;

            border-radius: 6px;

            cursor: pointer;

            font-weight: bold;

        }


        .search-btn:hover {

            background-color: #0d47a1;

        }


        .clear-btn {

            background-color: #777;

            color: white;

            padding: 11px 18px;

            border-radius: 6px;

            text-decoration: none;

            font-weight: bold;

        }


        .clear-btn:hover {

            background-color: #555;

        }


        /* =====================================
           SUCCESS MESSAGE
        ===================================== */

        .success-message {

            background-color: #d1fae5;

            color: #065f46;

            padding: 12px 15px;

            border-radius: 6px;

            margin-bottom: 20px;

        }


        /* =====================================
           TABLE CONTAINER
        ===================================== */

        .table-container {

            background-color: white;

            padding: 25px;

            border-radius: 10px;

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th {

            background-color: #0f4c81;

            color: white;

            padding: 13px;

            text-align: left;

        }


        td {

            padding: 13px;

            border-bottom: 1px solid #ddd;

        }


        tr:hover {

            background-color: #f1f5f9;

        }


        /* =====================================
           BUTTONS
        ===================================== */

        .btn {

            display: inline-block;

            padding: 7px 11px;

            margin: 2px;

            border-radius: 5px;

            text-decoration: none;

            color: white;

            font-size: 12px;

            border: none;

            cursor: pointer;

        }


        .btn-view {

            background-color: #1565c0;

        }


        .btn-view:hover {

            background-color: #0d47a1;

        }


        .btn-edit {

            background-color: #ef6c00;

        }


        .btn-edit:hover {

            background-color: #e65100;

        }


        .btn-delete {

            background-color: #e53935;

        }


        .btn-delete:hover {

            background-color: #c62828;

        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 900px) {

            .main-content {

                margin-left: 230px;

            }

            .header {

                flex-direction: column;

                align-items: flex-start;

                gap: 20px;

            }

            .header-buttons {

                flex-wrap: wrap;

            }

        }

    </style>

</head>


<body>


<!-- =========================================
     SIDEBAR
========================================= -->

<?php include "counselor_sidebar.php"; ?>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="header">

        <div>

            <h1>
                Consultation Records
            </h1>

            <p>
                Manage your student consultation records.
            </p>

        </div>


        <div class="header-buttons">


            <!-- GENERATE REPORT -->

            <a
                href="consultation_report.php"
                class="btn-report">

                📄 Generate Report

            </a>


            <!-- ADD CONSULTATION -->

            <a
                href="record_consultation.php"
                class="btn-add">

                + Add Consultation Record

            </a>


        </div>

    </div>



    <!-- =====================================
         SUCCESS MESSAGE
    ====================================== -->

    <?php if (isset($_GET['deleted'])): ?>

        <div class="success-message">

            Consultation record deleted successfully.

        </div>

    <?php endif; ?>



    <!-- =====================================
         SEARCH STUDENT
    ====================================== -->

    <div class="search-container">

        <form
            method="GET"
            action=""
            class="search-form">


            <input
                type="text"
                name="search"
                placeholder="Search by Student ID or Student Name..."
                value="<?php echo htmlspecialchars($search); ?>"
            >


            <button
                type="submit"
                class="search-btn">

                🔍 Search

            </button>


            <?php if ($search !== ""): ?>

  
            <?php endif; ?>


        </form>

    </div>



    <!-- =====================================
         TABLE
    ====================================== -->

    <div class="table-container">

        <table>

            <tr>

                <th>Student ID</th>

                <th>Student Name</th>

                <th>Appointment Date</th>

                <th>Notes</th>

                <th>Date Recorded</th>

            </tr>


            <?php if ($result->num_rows > 0): ?>


                <?php while ($row = $result->fetch_assoc()): ?>

                <tr>


                    <!-- STUDENT ID -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['student_id'] ?? ''
                        );

                        ?>

                    </td>



                    <!-- STUDENT NAME -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['student_name'] ?? ''
                        );

                        ?>

                    </td>



                    <!-- APPOINTMENT DATE -->

                    <td>

                        <?php

                        if (!empty($row['appointment_date'])) {

                            echo date(
                                "d M Y",
                                strtotime(
                                    $row['appointment_date']
                                )
                            );

                        } else {

                            echo "-";

                        }

                        ?>

                    </td>



                    <!-- NOTES -->

                    <td>

                        <?php

                        $notes =
                            $row['notes'] ?? '';


                        if (strlen($notes) > 50) {

                            echo htmlspecialchars(
                                substr($notes, 0, 50)
                            );

                            echo "...";

                        } else {

                            echo htmlspecialchars(
                                $notes
                            );

                        }

                        ?>

                    </td>



                    <!-- CREATED DATE -->

                    <td>

                        <?php

                        if (!empty($row['created_at'])) {

                            echo date(
                                "d M Y",
                                strtotime(
                                    $row['created_at']
                                )
                            );

                        } else {

                            echo "-";

                        }

                        ?>

                    </td>

                </tr>


                <?php endwhile; ?>


            <?php else: ?>


                <tr>

                    <td
                        colspan="6"
                        style="
                            text-align:center;
                            padding:30px;
                        ">

                        <?php if ($search !== ""): ?>

                            No consultation records found for
                            "<?php echo htmlspecialchars($search); ?>".

                        <?php else: ?>

                            No consultation records found.

                        <?php endif; ?>

                    </td>

                </tr>


            <?php endif; ?>


        </table>

    </div>


</div>


</body>

</html>