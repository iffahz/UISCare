<?php

require_once "admin_check.php";
require_once "db.php";


/* =========================================
   SEARCH COUNSELOR
========================================= */

$search = "";

if (isset($_GET['search'])) {

    $search = trim($_GET['search']);

}


/* =========================================
   GET COUNSELOR
========================================= */

$sql = "
    SELECT
        id,
        fullname,
        email,
        phone,
        status
    FROM `user`
    WHERE role = 'counselor'
";


/* =========================================
   SEARCH CONDITION
========================================= */

if ($search !== "") {

    $sql .= "
        AND (
            fullname LIKE ?
            OR email LIKE ?
            OR phone LIKE ?
        )
    ";

}


$sql .= "
    ORDER BY fullname ASC
";


$stmt = $conn->prepare($sql);


/* =========================================
   BIND SEARCH
========================================= */

if ($search !== "") {

    $search_param = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $search_param,
        $search_param,
        $search_param
    );

}


$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html>

<head>

    <title>Manage Counselor</title>


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

            margin-bottom: 20px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .page-header h1 {

            margin: 0;

            color: #0f4c81;

        }


        /* =====================================
           ADD BUTTON
        ===================================== */

        .btn-add {

            background-color: #1e88e5;

            color: white;

            padding: 11px 16px;

            border-radius: 7px;

            text-decoration: none;

            font-weight: bold;

        }


        .btn-add:hover {

            background-color: #0f4c81;

        }


        /* =====================================
           SEARCH
        ===================================== */

        .search-container {

            background-color: white;

            padding: 20px 25px;

            border-radius: 10px;

            margin-bottom: 20px;

        }


        .search-form {

            display: flex;

            gap: 10px;

        }


        .search-input {

            flex: 1;

            padding: 11px 14px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 14px;

            outline: none;

        }


        .search-input:focus {

            border-color: #1e88e5;

        }


        .btn-search {

            background-color: #1565c0;

            color: white;

            border: none;

            padding: 11px 18px;

            border-radius: 6px;

            cursor: pointer;

            font-weight: bold;

        }


        .btn-search:hover {

            background-color: #0d47a1;

        }


        .btn-clear {

            background-color: #777;

            color: white;

            padding: 11px 18px;

            border-radius: 6px;

            text-decoration: none;

            font-weight: bold;

        }


        .btn-clear:hover {

            background-color: #555;

        }


        /* =====================================
           SEARCH RESULT MESSAGE
        ===================================== */

        .search-result {

            margin-top: 12px;

            color: #666;

            font-size: 14px;

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


        /* =====================================
           TABLE
        ===================================== */

        table {

            width: 100%;

            border-collapse: collapse;

        }


        th {

            background-color: #0f4c81;

            color: white;

            padding: 14px;

            text-align: left;

        }


        td {

            padding: 14px;

            border-bottom: 1px solid #ddd;

        }


        tr:hover {

            background-color: #f1f5f9;

        }


        /* =====================================
           STATUS
        ===================================== */

        .status {

            padding: 6px 12px;

            border-radius: 6px;

            font-weight: bold;

            font-size: 13px;

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
           ACTION BUTTONS
        ===================================== */

        .btn {

            display: inline-block;

            padding: 7px 11px;

            border-radius: 6px;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

            margin-right: 4px;

        }


        .btn-view {

            background-color: #dbeafe;

            color: #1565c0;

        }


        .btn-view:hover {

            background-color: #bfdbfe;

        }


        .btn-edit {

            background-color: #fef3c7;

            color: #92400e;

        }


        .btn-edit:hover {

            background-color: #fde68a;

        }


        .btn-danger {

            background-color: #fee2e2;

            color: #991b1b;

        }


        .btn-danger:hover {

            background-color: #fecaca;

        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 800px) {

            .sidebar {

                width: 180px;

            }


            .sidebar a {

                width: 150px;

            }


            .main-content {

                margin-left: 180px;

                padding: 20px;

            }


            .page-header {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

            }


            .search-form {

                flex-wrap: wrap;

            }


        }

    </style>

</head>


<body>


<?php include "admin_sidebar.php"; ?>


<div class="main-content">


    <!-- =====================================
         PAGE HEADER
    ====================================== -->

    <div class="page-header">

        <h1>
            Manage Counselor
        </h1>


        <a
            href="add.counselor.php"
            class="btn-add">

            + Add Counselor

        </a>

    </div>


    <!-- =====================================
         SEARCH
    ====================================== -->

    <div class="search-container">

        <form
            method="GET"
            action="manage_counselor.php"
            class="search-form">


            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search counselor by name, email or phone..."
                value="<?php echo htmlspecialchars($search); ?>"
            >


            <button
                type="submit"
                class="btn-search">

                🔍 Search

            </button>


            <?php if ($search !== ""): ?>

                <a
                    href="manage_counselor.php"
                    class="btn-clear">

                    Clear

                </a>

            <?php endif; ?>


        </form>


        <?php if ($search !== ""): ?>

            <div class="search-result">

                Search result for:
                <strong>
                    <?php echo htmlspecialchars($search); ?>
                </strong>

            </div>

        <?php endif; ?>

    </div>


    <!-- =====================================
         COUNSELOR TABLE
    ====================================== -->

    <div class="table-container">

        <table>

            <tr>

                <th>
                    Name
                </th>

                <th>
                    Email
                </th>

                <th>
                    Phone
                </th>

                <th>
                    Status
                </th>

                <th>
                    Action
                </th>

            </tr>


            <?php if ($result->num_rows > 0): ?>


                <?php while ($row = $result->fetch_assoc()): ?>

                <tr>


                    <!-- NAME -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['fullname'] ?? ''
                        );

                        ?>

                    </td>


                    <!-- EMAIL -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['email'] ?? ''
                        );

                        ?>

                    </td>


                    <!-- PHONE -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['phone'] ?? ''
                        );

                        ?>

                    </td>


                    <!-- STATUS -->

                    <td>

                        <span class="status
                            <?php

                            echo htmlspecialchars(
                                $row['status'] ?? 'inactive'
                            );

                            ?>">

                            <?php

                            echo ucfirst(
                                htmlspecialchars(
                                    $row['status'] ?? 'inactive'
                                )
                            );

                            ?>

                        </span>

                    </td>


                    <!-- ACTION -->

                    <td>


                        <a
                            class="btn btn-view"
                            href="view_counselor.php?id=<?php echo $row['id']; ?>">

                            View

                        </a>


                        <a
                            class="btn btn-edit"
                            href="edit_counselor.php?id=<?php echo $row['id']; ?>">

                            Edit

                        </a>


                        <a
                            class="btn btn-danger"
                            href="delete_counselor.php?id=<?php echo $row['id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this counselor?');">

                            Delete

                        </a>


                    </td>

                </tr>

                <?php endwhile; ?>


            <?php else: ?>


                <tr>

                    <td
                        colspan="5"
                        style="text-align:center; padding:30px;">

                        <?php if ($search !== ""): ?>

                            No counselor found for
                            "<?php echo htmlspecialchars($search); ?>".

                        <?php else: ?>

                            No counselor records found.

                        <?php endif; ?>

                    </td>

                </tr>


            <?php endif; ?>


        </table>

    </div>


</div>


</body>

</html>