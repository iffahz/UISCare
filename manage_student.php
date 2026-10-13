<?php

require_once "admin_check.php";
require_once "db.php";


/* =========================================
   SEARCH STUDENT
========================================= */

$search = "";

if (isset($_GET['search'])) {

    $search = trim($_GET['search']);

}


/* =========================================
   STATUS FILTER
========================================= */

$status_filter = "";

if (isset($_GET['status'])) {

    $status_filter = trim($_GET['status']);

}


/* =========================================
   PAGINATION
========================================= */

$records_per_page = 10;

$page = isset($_GET['page'])
    ? (int)$_GET['page']
    : 1;

if ($page < 1) {

    $page = 1;

}

$offset = ($page - 1) * $records_per_page;


/* =========================================
   COUNT TOTAL STUDENTS
========================================= */

$count_sql = "
    SELECT COUNT(*) AS total
    FROM `user`
    WHERE role = 'student'
";


/* SEARCH CONDITION */

if ($search !== "") {

    $count_sql .= "
        AND (
            student_id LIKE ?
            OR fullname LIKE ?
            OR email LIKE ?
            OR phone LIKE ?
        )
    ";

}


/* STATUS CONDITION */

if (
    $status_filter === "active" ||
    $status_filter === "inactive"
) {

    $count_sql .= "
        AND status = ?
    ";

}


$count_stmt = $conn->prepare($count_sql);


/* =========================================
   BIND COUNT PARAMETERS
========================================= */

if (
    $search !== "" &&
    (
        $status_filter === "active" ||
        $status_filter === "inactive"
    )
) {

    $search_param = "%" . $search . "%";

    $count_stmt->bind_param(
        "sssss",
        $search_param,
        $search_param,
        $search_param,
        $search_param,
        $status_filter
    );

} elseif ($search !== "") {

    $search_param = "%" . $search . "%";

    $count_stmt->bind_param(
        "ssss",
        $search_param,
        $search_param,
        $search_param,
        $search_param
    );

} elseif (
    $status_filter === "active" ||
    $status_filter === "inactive"
) {

    $count_stmt->bind_param(
        "s",
        $status_filter
    );

}


$count_stmt->execute();

$count_result = $count_stmt->get_result();

$count_row = $count_result->fetch_assoc();

$total_records = (int)$count_row['total'];

$total_pages = ceil(
    $total_records / $records_per_page
);


/* =========================================
   GET STUDENTS
========================================= */

$sql = "
    SELECT
        id,
        student_id,
        fullname,
        email,
        phone,
        status
    FROM `user`
    WHERE role = 'student'
";


/* =========================================
   SEARCH CONDITION
========================================= */

if ($search !== "") {

    $sql .= "
        AND (
            student_id LIKE ?
            OR fullname LIKE ?
            OR email LIKE ?
            OR phone LIKE ?
        )
    ";

}


/* =========================================
   STATUS CONDITION
========================================= */

if (
    $status_filter === "active" ||
    $status_filter === "inactive"
) {

    $sql .= "
        AND status = ?
    ";

}


$sql .= "
    ORDER BY fullname ASC
    LIMIT ? OFFSET ?
";


$stmt = $conn->prepare($sql);


/* =========================================
   BIND STUDENT PARAMETERS
========================================= */

if (
    $search !== "" &&
    (
        $status_filter === "active" ||
        $status_filter === "inactive"
    )
) {

    $search_param = "%" . $search . "%";

    $stmt->bind_param(
        "sssssii",
        $search_param,
        $search_param,
        $search_param,
        $search_param,
        $status_filter,
        $records_per_page,
        $offset
    );

} elseif ($search !== "") {

    $search_param = "%" . $search . "%";

    $stmt->bind_param(
        "ssssii",
        $search_param,
        $search_param,
        $search_param,
        $search_param,
        $records_per_page,
        $offset
    );

} elseif (
    $status_filter === "active" ||
    $status_filter === "inactive"
) {

    $stmt->bind_param(
        "sii",
        $status_filter,
        $records_per_page,
        $offset
    );

} else {

    $stmt->bind_param(
        "ii",
        $records_per_page,
        $offset
    );

}


$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Manage Student</title>


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

        }


        .page-header h1 {

            margin: 0;

            color: #0f4c81;

        }


        /* =====================================
           FILTER
        ===================================== */

        .filter-container {

            background-color: white;

            padding: 20px 25px;

            border-radius: 10px;

            margin-bottom: 20px;

        }


        .filter-form {

            display: flex;

            gap: 10px;

            align-items: center;

            flex-wrap: wrap;

        }


        .search-input {

            flex: 1;

            min-width: 250px;

            padding: 11px 14px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 14px;

            outline: none;

        }


        .search-input:focus {

            border-color: #1e88e5;

        }


        .status-filter {

            padding: 11px 14px;

            border: 1px solid #ccc;

            border-radius: 6px;

            background-color: white;

            font-size: 14px;

            min-width: 150px;

        }


        /* =====================================
           BUTTONS
        ===================================== */

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
           SEARCH RESULT
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
           TABLE HEADER
        ===================================== */

        .table-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 15px;

        }


        .table-header h2 {

            margin: 0;

            color: #0f4c81;

            font-size: 20px;

        }


        .record-count {

            color: #666;

            font-size: 14px;

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

            padding: 13px;

            text-align: left;

            font-size: 14px;

        }


        td {

            padding: 13px;

            border-bottom: 1px solid #e5e7eb;

            font-size: 14px;

        }


        tr:hover {

            background-color: #f1f5f9;

        }


        /* =====================================
           STATUS
        ===================================== */

        .status {

            display: inline-block;

            padding: 5px 10px;

            border-radius: 6px;

            font-size: 13px;

            font-weight: bold;

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
           ACTION BUTTON
        ===================================== */

        .btn {

            display: inline-block;

            padding: 7px 11px;

            margin-right: 4px;

            border-radius: 6px;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        /* VIEW */

        .btn-view {

            background-color: #dbeafe;

            color: #1565c0;

        }


        .btn-view:hover {

            background-color: #bfdbfe;

        }


        /* EDIT */

        .btn-edit {

            background-color: #fef3c7;

            color: #92400e;

        }


        .btn-edit:hover {

            background-color: #fde68a;

        }


        /* DELETE */

        .btn-delete {

            background-color: #fee2e2;

            color: #b91c1c;

        }


        .btn-delete:hover {

            background-color: #fecaca;

        }


        /* =====================================
           PAGINATION
        ===================================== */

        .pagination {

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 6px;

            margin-top: 25px;

        }


        .pagination a {

            min-width: 36px;

            height: 36px;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 0 10px;

            border-radius: 6px;

            text-decoration: none;

            color: #1565c0;

            background-color: #dbeafe;

            font-weight: bold;

            font-size: 14px;

        }


        .pagination a:hover {

            background-color: #bfdbfe;

        }


        .pagination a.active {

            background-color: #1565c0;

            color: white;

        }


        .pagination a.disabled {

            background-color: #e5e7eb;

            color: #999;

            pointer-events: none;

        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 900px) {

            .sidebar {

                width: 190px;

            }


            .sidebar a {

                width: 160px;

            }


            .main-content {

                margin-left: 190px;

            }

        }


        @media (max-width: 600px) {

            .sidebar {

                width: 170px;

            }


            .sidebar a {

                width: 145px;

                font-size: 13px;

            }


            .main-content {

                margin-left: 170px;

                padding: 20px;

            }


            .filter-form {

                flex-direction: column;

                align-items: stretch;

            }


            .search-input {

                width: 100%;

            }


            .status-filter {

                width: 100%;

            }


            .btn-search,
            .btn-clear {

                text-align: center;

                width: 100%;

            }


            .table-header {

                flex-direction: column;

                align-items: flex-start;

                gap: 8px;

            }

        }

    </style>

</head>


<body>


<?php include "admin_sidebar.php"; ?>


<div class="main-content">


    <!-- =====================================
         PAGE HEADER
    ===================================== -->

    <div class="page-header">

        <h1>
            Manage Student
        </h1>

    </div>


    <!-- =====================================
         FILTER
    ===================================== -->

    <div class="filter-container">

        <form
            method="GET"
            action="manage_student.php"
            class="filter-form">


            <!-- SEARCH -->

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search student by ID, name, email or phone..."
                value="<?php echo htmlspecialchars($search); ?>"
            >


            <!-- STATUS -->

            <select
                name="status"
                class="status-filter">

                <option
                    value="">
                    All Status
                </option>

                <option
                    value="active"
                    <?php
                    echo $status_filter === "active"
                        ? "selected"
                        : "";
                    ?>
                >
                    Active
                </option>

                <option
                    value="inactive"
                    <?php
                    echo $status_filter === "inactive"
                        ? "selected"
                        : "";
                    ?>
                >
                    Inactive
                </option>

            </select>


            <!-- SEARCH BUTTON -->

            <button
                type="submit"
                class="btn-search">

                🔍 Search

            </button>


            <!-- CLEAR -->

            <?php if (
                $search !== "" ||
                $status_filter !== ""
            ): ?>

                <a
                    href="manage_student.php"
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

                <?php if ($status_filter !== ""): ?>

                    &nbsp; | &nbsp;

                    Status:

                    <strong>

                        <?php echo ucfirst($status_filter); ?>

                    </strong>

                <?php endif; ?>

            </div>

        <?php elseif ($status_filter !== ""): ?>

            <div class="search-result">

                Showing students with status:

                <strong>

                    <?php echo ucfirst($status_filter); ?>

                </strong>

            </div>

        <?php endif; ?>


    </div>


    <!-- =====================================
         STUDENT TABLE
    ===================================== -->

    <div class="table-container">


        <div class="table-header">

            <h2>
                Student List
            </h2>

            <div class="record-count">

                Total:

                <strong>
                    <?php echo $total_records; ?>
                </strong>

                student(s)

            </div>

        </div>


        <table>

            <tr>

                <th>
                    Student ID
                </th>

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


                        <!-- STUDENT ID -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row['student_id'] ?? ''
                            );

                            ?>

                        </td>


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


                            <!-- VIEW -->

                            <a
                                class="btn btn-view"
                                href="view_student.php?id=<?php echo $row['id']; ?>">

                                View

                            </a>


                            <!-- EDIT -->

                            <a
                                class="btn btn-edit"
                                href="edit_student.php?id=<?php echo $row['id']; ?>">

                                Edit

                            </a>


                            <!-- DELETE -->

                            <a
                                class="btn btn-delete"
                                href="delete_student.php?id=<?php echo $row['id']; ?>"
                                onclick="return confirm('Delete this student?');">

                                Delete

                            </a>


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

                            No student found for
                            "<?php echo htmlspecialchars($search); ?>".

                        <?php elseif ($status_filter !== ""): ?>

                            No
                            <?php echo htmlspecialchars($status_filter); ?>
                            student records found.

                        <?php else: ?>

                            No student records found.

                        <?php endif; ?>

                    </td>

                </tr>


            <?php endif; ?>


        </table>



        <!-- =====================================
             PAGINATION
        ===================================== -->

        <?php if ($total_pages > 1): ?>

            <div class="pagination">


                <!-- PREVIOUS -->

                <?php if ($page > 1): ?>

                    <a
                        href="?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&page=<?php echo $page - 1; ?>">

                        ‹

                    </a>

                <?php else: ?>

                    <a class="disabled">
                        ‹
                    </a>

                <?php endif; ?>



                <!-- PAGE NUMBERS -->

                <?php for (
                    $i = 1;
                    $i <= $total_pages;
                    $i++
                ): ?>

                    <a
                        href="?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&page=<?php echo $i; ?>"
                        class="<?php echo $i == $page ? 'active' : ''; ?>">

                        <?php echo $i; ?>

                    </a>

                <?php endfor; ?>



                <!-- NEXT -->

                <?php if ($page < $total_pages): ?>

                    <a
                        href="?search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&page=<?php echo $page + 1; ?>">

                        ›

                    </a>

                <?php else: ?>

                    <a class="disabled">
                        ›
                    </a>

                <?php endif; ?>


            </div>

        <?php endif; ?>


    </div>


</div>


</body>

</html>