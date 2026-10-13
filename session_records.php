<?php

require_once "admin_check.php";
require_once "db.php";


// =========================================
// SEARCH
// =========================================

$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}


// =========================================
// PAGINATION
// =========================================

$records_per_page = 10;

$page = isset($_GET['page']) && is_numeric($_GET['page'])
    ? intval($_GET['page'])
    : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $records_per_page;


// =========================================
// COUNT RECORDS
// =========================================

$count_sql = "
    SELECT COUNT(*) AS total
    FROM appointment a

    INNER JOIN `user` s
        ON a.student_user_id = s.id

    INNER JOIN `user` c
        ON a.counselor_user_id = c.id

    WHERE
        s.student_id LIKE ?
        OR s.fullname LIKE ?
        OR c.fullname LIKE ?
";

$count_stmt = $conn->prepare($count_sql);

$search_value = "%" . $search . "%";

$count_stmt->bind_param(
    "sss",
    $search_value,
    $search_value,
    $search_value
);

$count_stmt->execute();

$count_result = $count_stmt->get_result();

$total_records = $count_result->fetch_assoc()['total'];

$total_pages = ceil($total_records / $records_per_page);


// =========================================
// GET SESSION RECORDS
// =========================================

$sql = "
    SELECT
        a.id,
        s.student_id,
        s.fullname AS student_name,
        c.fullname AS counselor_name,
        a.appointment_date,
        a.appointment_time,
        a.reason,
        a.status

    FROM appointment a

    INNER JOIN `user` s
        ON a.student_user_id = s.id

    INNER JOIN `user` c
        ON a.counselor_user_id = c.id

    WHERE
        s.student_id LIKE ?
        OR s.fullname LIKE ?
        OR c.fullname LIKE ?

    ORDER BY
        a.appointment_date DESC,
        a.appointment_time DESC

    LIMIT ? OFFSET ?
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sssii",
    $search_value,
    $search_value,
    $search_value,
    $records_per_page,
    $offset
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Session Records</title>

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

            margin-bottom: 25px;
        }


        .page-header h1 {
            margin: 0;

            color: #0f4c81;
        }


        /* =====================================
           SEARCH
        ===================================== */

        .search-container {
            background-color: white;

            padding: 20px;

            border-radius: 10px;

            margin-bottom: 20px;

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .search-container form {
            display: flex;

            width: 100%;

            gap: 10px;
        }


        .search-input {
            flex: 1;

            padding: 11px 14px;

            border: 1px solid #cbd5e1;

            border-radius: 7px;

            font-size: 14px;

            outline: none;
        }


        .search-input:focus {
            border-color: #1e88e5;
        }


        .search-btn {
            padding: 11px 20px;

            border: none;

            border-radius: 7px;

            background-color: #1e88e5;

            color: white;

            font-weight: bold;

            cursor: pointer;
        }


        .search-btn:hover {
            background-color: #0f4c81;
        }


        .clear-btn {
            padding: 11px 20px;

            border-radius: 7px;

            background-color: #64748b;

            color: white;

            text-decoration: none;

            font-weight: bold;
        }


        .clear-btn:hover {
            background-color: #475569;
        }


        /* =====================================
           RECORD INFO
        ===================================== */

        .record-info {
            margin-bottom: 15px;

            color: #475569;

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


        .status.pending {
            background-color: #fef3c7;

            color: #92400e;
        }


        .status.approved {
            background-color: #dbeafe;

            color: #1565c0;
        }


        .status.completed {
            background-color: #dcfce7;

            color: #166534;
        }


        .status.cancelled {
            background-color: #fee2e2;

            color: #991b1b;
        }


        .status.rejected {
            background-color: #fee2e2;

            color: #991b1b;
        }


        /* =====================================
           NO RECORD
        ===================================== */

        .no-record {
            text-align: center;

            padding: 30px;

            color: #777;
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

            flex-wrap: wrap;
        }


        .pagination a,
        .pagination span {
            min-width: 38px;

            padding: 9px 12px;

            text-align: center;

            border-radius: 6px;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;
        }


        .pagination a {
            background-color: #e2e8f0;

            color: #334155;
        }


        .pagination a:hover {
            background-color: #1e88e5;

            color: white;
        }


        .pagination .active {
            background-color: #1e88e5;

            color: white;
        }


        .pagination .disabled {
            background-color: #f1f5f9;

            color: #94a3b8;

            cursor: not-allowed;
        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 1000px) {

            .sidebar {
                width: 200px;
            }


            .sidebar a {
                width: 165px;
            }


            .main-content {
                margin-left: 200px;
            }

        }


        @media (max-width: 700px) {

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


            .search-container form {
                flex-direction: column;
            }


            .search-btn,
            .clear-btn {
                text-align: center;
            }

        }

    </style>

</head>


<body>


<?php include "admin_sidebar.php"; ?>


<div class="main-content">


    <!-- =====================================
         HEADER
    ===================================== -->

    <div class="page-header">

        <h1>
            Session Records
        </h1>

    </div>


    <!-- =====================================
         SEARCH
    ===================================== -->

    <div class="search-container">

        <form method="GET" action="session_records.php">

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search by Student ID, Student Name or Counselor Name..."
                value="<?php echo htmlspecialchars($search); ?>"
            >


            <button
                type="submit"
                class="search-btn">

                Search

            </button>


            <?php if (!empty($search)): ?>

                <a
                    href="session_records.php"
                    class="clear-btn">

                    Clear

                </a>

            <?php endif; ?>

        </form>

    </div>


    <!-- =====================================
         RECORD INFO
    ===================================== -->

    <div class="record-info">

        <?php if ($total_records > 0): ?>

            Showing
            <?php echo $offset + 1; ?>
            -
            <?php echo min(
                $offset + $records_per_page,
                $total_records
            ); ?>

            of
            <?php echo $total_records; ?>
            records

        <?php else: ?>

            No records found.

        <?php endif; ?>

    </div>


    <!-- =====================================
         SESSION TABLE
    ===================================== -->

    <div class="table-container">

        <table>

            <tr>

                <th>
                    Student ID
                </th>

                <th>
                    Student
                </th>

                <th>
                    Counselor
                </th>

                <th>
                    Date
                </th>

                <th>
                    Time
                </th>

                <th>
                    Reason
                </th>

                <th>
                    Status
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


                    <!-- STUDENT -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['student_name'] ?? ''
                        );
                        ?>

                    </td>


                    <!-- COUNSELOR -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['counselor_name'] ?? ''
                        );
                        ?>

                    </td>


                    <!-- DATE -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['appointment_date'] ?? ''
                        );
                        ?>

                    </td>


                    <!-- TIME -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['appointment_time'] ?? ''
                        );
                        ?>

                    </td>


                    <!-- REASON -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['reason'] ?? ''
                        );
                        ?>

                    </td>


                    <!-- STATUS -->

                    <td>

                        <span class="status
                            <?php
                            echo htmlspecialchars(
                                strtolower(
                                    $row['status'] ?? 'pending'
                                )
                            );
                            ?>">

                            <?php
                            echo ucfirst(
                                $row['status'] ?? 'pending'
                            );
                            ?>

                        </span>

                    </td>


                </tr>

                <?php endwhile; ?>


            <?php else: ?>

                <tr>

                    <td
                        colspan="7"
                        class="no-record">

                        No session records found.

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
                        href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">

                        &laquo;

                    </a>

                <?php else: ?>

                    <span class="disabled">
                        &laquo;
                    </span>

                <?php endif; ?>


                <!-- PAGE NUMBERS -->

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                    <?php if ($i == $page): ?>

                        <span class="active">
                            <?php echo $i; ?>
                        </span>

                    <?php else: ?>

                        <a
                            href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>">

                            <?php echo $i; ?>

                        </a>

                    <?php endif; ?>

                <?php endfor; ?>


                <!-- NEXT -->

                <?php if ($page < $total_pages): ?>

                    <a
                        href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">

                        &raquo;

                    </a>

                <?php else: ?>

                    <span class="disabled">
                        &raquo;
                    </span>

                <?php endif; ?>


            </div>

        <?php endif; ?>


    </div>


</div>


</body>

</html>