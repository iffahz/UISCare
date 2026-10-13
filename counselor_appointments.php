<?php

require_once "counselor_check.php";
require_once "db.php";

$counselor_id = $_SESSION['user_id'];


// =========================================
// HANDLE APPOINTMENT ACTION
// =========================================

if (isset($_GET['action']) && isset($_GET['id'])) {

    $action = $_GET['action'];
    $appointment_id = (int) $_GET['id'];

    // Make sure appointment belongs to this counselor
    $check = $conn->prepare("
        SELECT id
        FROM appointment
        WHERE id = ?
        AND counselor_user_id = ?
    ");

    $check->bind_param(
        "ii",
        $appointment_id,
        $counselor_id
    );

    $check->execute();

    $check_result = $check->get_result();


    if ($check_result->num_rows > 0) {

        // APPROVE
        if ($action === "approve") {

            $stmt = $conn->prepare("
                UPDATE appointment
                SET status = 'approved'
                WHERE id = ?
                AND counselor_user_id = ?
            ");

            $stmt->bind_param(
                "ii",
                $appointment_id,
                $counselor_id
            );

            $stmt->execute();
            $stmt->close();
        }


        // REJECT
        elseif ($action === "reject") {

            $stmt = $conn->prepare("
                UPDATE appointment
                SET status = 'rejected'
                WHERE id = ?
                AND counselor_user_id = ?
            ");

            $stmt->bind_param(
                "ii",
                $appointment_id,
                $counselor_id
            );

            $stmt->execute();
            $stmt->close();
        }


        // CANCEL
        elseif ($action === "cancel") {

            $stmt = $conn->prepare("
                UPDATE appointment
                SET status = 'cancelled'
                WHERE id = ?
                AND counselor_user_id = ?
            ");

            $stmt->bind_param(
                "ii",
                $appointment_id,
                $counselor_id
            );

            $stmt->execute();
            $stmt->close();
        }


        // COMPLETED
        elseif ($action === "completed") {

            $stmt = $conn->prepare("
                UPDATE appointment
                SET status = 'completed'
                WHERE id = ?
                AND counselor_user_id = ?
            ");

            $stmt->bind_param(
                "ii",
                $appointment_id,
                $counselor_id
            );

            $stmt->execute();
            $stmt->close();
        }
    }

    $check->close();

    header("Location: counselor_appointments.php");
    exit();
}


// =========================================
// SEARCH / FILTER
// =========================================

$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';


// =========================================
// PAGINATION
// =========================================

$limit = 10;

$page = isset($_GET['page'])
    ? max(1, (int) $_GET['page'])
    : 1;

$offset = ($page - 1) * $limit;


// =========================================
// BUILD FILTER CONDITIONS
// =========================================

$where = "
    WHERE a.counselor_user_id = ?
";

$params = [$counselor_id];
$types = "i";


// SEARCH STUDENT NAME / STUDENT ID

if ($search !== '') {

    $where .= "
        AND (
            u.fullname LIKE ?
            OR u.student_id LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ss";
}


// STATUS FILTER

if ($status_filter !== '') {

    $where .= "
        AND a.status = ?
    ";

    $params[] = $status_filter;

    $types .= "s";
}


// DATE FILTER

if ($date_filter !== '') {

    $where .= "
        AND a.appointment_date = ?
    ";

    $params[] = $date_filter;

    $types .= "s";
}


// =========================================
// COUNT TOTAL APPOINTMENTS
// =========================================

$count_sql = "
    SELECT COUNT(*) AS total
    FROM appointment a
    INNER JOIN `user` u
        ON a.student_user_id = u.id
    $where
";

$count_stmt = $conn->prepare($count_sql);

$count_stmt->bind_param(
    $types,
    ...$params
);

$count_stmt->execute();

$total_appointments = $count_stmt
    ->get_result()
    ->fetch_assoc()['total'];

$count_stmt->close();


// =========================================
// TOTAL PAGES
// =========================================

$total_pages = max(
    1,
    ceil($total_appointments / $limit)
);


// Prevent page from exceeding total pages

if ($page > $total_pages) {

    $page = $total_pages;

    $offset = ($page - 1) * $limit;
}


// =========================================
// GET APPOINTMENTS FOR TABLE
// =========================================

$sql = "
    SELECT

        a.id,

        a.appointment_date,

        a.appointment_time,

        a.reason,

        a.status,

        u.student_id,

        u.fullname AS student_name

    FROM appointment a

    INNER JOIN `user` u
        ON a.student_user_id = u.id

    $where

    ORDER BY
        a.appointment_date ASC,
        a.appointment_time ASC

    LIMIT ? OFFSET ?
";

$params_table = $params;

$params_table[] = $limit;
$params_table[] = $offset;

$types_table = $types . "ii";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    $types_table,
    ...$params_table
);

$stmt->execute();

$result = $stmt->get_result();


// =========================================
// GET ALL APPOINTMENTS FOR CALENDAR
// =========================================

$calendar_events = [];

$calendar_sql = "

    SELECT

        a.id,

        a.appointment_date,

        a.appointment_time,

        a.reason,

        a.status,

        u.student_id,

        u.fullname AS student_name

    FROM appointment a

    INNER JOIN `user` u
        ON a.student_user_id = u.id

    WHERE a.counselor_user_id = ?

    ORDER BY
        a.appointment_date ASC,
        a.appointment_time ASC

";

$calendar_stmt = $conn->prepare($calendar_sql);

$calendar_stmt->bind_param(
    "i",
    $counselor_id
);

$calendar_stmt->execute();

$calendar_result =
    $calendar_stmt->get_result();


while (
    $appointment =
    $calendar_result->fetch_assoc()
) {

    $start_datetime =
        $appointment['appointment_date']
        . 'T'
        . $appointment['appointment_time'];


    $end_datetime = date(
        'Y-m-d\TH:i:s',
        strtotime(
            $start_datetime . ' +30 minutes'
        )
    );


    // STATUS CLASS

    $status_class = 'event-pending';


    if (
        $appointment['status'] === 'approved'
    ) {

        $status_class =
            'event-approved';

    } elseif (
        $appointment['status'] === 'rejected'
    ) {

        $status_class =
            'event-rejected';

    } elseif (
        $appointment['status'] === 'cancelled'
    ) {

        $status_class =
            'event-cancelled';

    } elseif (
        $appointment['status'] === 'completed'
    ) {

        $status_class =
            'event-completed';
    }


    $calendar_events[] = [

        'id' =>
            $appointment['id'],

        'title' =>
            $appointment['student_name'],

        'start' =>
            $start_datetime,

        'end' =>
            $end_datetime,

        'classNames' => [
            $status_class
        ],

        'extendedProps' => [

            'student_id' =>
                $appointment['student_id'],

            'student_name' =>
                $appointment['student_name'],

            'reason' =>
                $appointment['reason'],

            'status' =>
                $appointment['status'],

            'time' =>
                date(
                    'h:i A',
                    strtotime(
                        $appointment['appointment_time']
                    )
                )
        ]
    ];
}


$calendar_stmt->close();


// =========================================
// CONVERT CALENDAR EVENTS TO JSON
// =========================================

$calendar_events_json = json_encode(
    $calendar_events,
    JSON_UNESCAPED_SLASHES
);

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Counselor Appointments</title>


    <!-- FULLCALENDAR -->

    <link
        href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.css"
        rel="stylesheet"
    >

    <script
        src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.18/index.global.min.js">
    </script>


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #dbeafe;
            color: #333;
            overflow-x: hidden;
        }

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
// =================================
// MODAL ACTION BUTTONS
// =================================

const actionsElement =
    document.getElementById(
        'modalActions'
    );

actionsElement.innerHTML = "";


// PENDING
if (props.status === 'pending') {

    actionsElement.innerHTML = `

        <a
            href="?action=approve&id=${event.id}"
            class="modal-approve"
        >
            Approve
        </a>

        <a
            href="?action=reject&id=${event.id}"
            class="modal-reject"
            onclick="return confirm('Reject this appointment?');"
        >
            Reject
        </a>

    `;

}


// APPROVED
else if (props.status === 'approved') {

    actionsElement.innerHTML = `

        <a
            href="?action=completed&id=${event.id}"
            class="modal-completed"
            onclick="return confirm('Mark this appointment as completed?');"
        >
            Mark Completed
        </a>

        <a
            href="?action=cancel&id=${event.id}"
            class="modal-cancel"
            onclick="return confirm('Cancel this appointment?');"
        >
            Cancel
        </a>

    `;

}// =================================
// MODAL ACTION BUTTONS
// =================================

const actionsElement =
    document.getElementById(
        'modalActions'
    );

actionsElement.innerHTML = "";


// PENDING
if (props.status === 'pending') {

    actionsElement.innerHTML = `

        <a
            href="?action=approve&id=${event.id}"
            class="modal-approve"
        >
            Approve
        </a>

        <a
            href="?action=reject&id=${event.id}"
            class="modal-reject"
            onclick="return confirm('Reject this appointment?');"
        >
            Reject
        </a>

    `;

}


// APPROVED
else if (props.status === 'approved') {

    actionsElement.innerHTML = `

        <a
            href="?action=completed&id=${event.id}"
            class="modal-completed"
            onclick="return confirm('Mark this appointment as completed?');"
        >
            Mark Completed
        </a>

        <a
            href="?action=cancel&id=${event.id}"
            class="modal-cancel"
            onclick="return confirm('Cancel this appointment?');"
        >
            Cancel
        </a>

    `;

}// =================================
// MODAL ACTION BUTTONS
// =================================

const actionsElement =
    document.getElementById(
        'modalActions'
    );

actionsElement.innerHTML = "";


// PENDING
if (props.status === 'pending') {

    actionsElement.innerHTML = `

        <a
            href="?action=approve&id=${event.id}"
            class="modal-approve"
        >
            Approve
        </a>

        <a
            href="?action=reject&id=${event.id}"
            class="modal-reject"
            onclick="return confirm('Reject this appointment?');"
        >
            Reject
        </a>

    `;

}


// APPROVED
else if (props.status === 'approved') {

    actionsElement.innerHTML = `

        <a
            href="?action=completed&id=${event.id}"
            class="modal-completed"
            onclick="return confirm('Mark this appointment as completed?');"
        >
            Mark Completed
        </a>

        <a
            href="?action=cancel&id=${event.id}"
            class="modal-cancel"
            onclick="return confirm('Cancel this appointment?');"
        >
            Cancel
        </a>

    `;

}// =================================
// MODAL ACTION BUTTONS
// =================================

const actionsElement =
    document.getElementById(
        'modalActions'
    );

actionsElement.innerHTML = "";


// PENDING
if (props.status === 'pending') {

    actionsElement.innerHTML = `

        <a
            href="?action=approve&id=${event.id}"
            class="modal-approve"
        >
            Approve
        </a>

        <a
            href="?action=reject&id=${event.id}"
            class="modal-reject"
            onclick="return confirm('Reject this appointment?');"
        >
            Reject
        </a>

    `;

}


// APPROVED
else if (props.status === 'approved') {

    actionsElement.innerHTML = `

        <a
            href="?action=completed&id=${event.id}"
            class="modal-completed"
            onclick="return confirm('Mark this appointment as completed?');"
        >
            Mark Completed
        </a>

        <a
            href="?action=cancel&id=${event.id}"
            class="modal-cancel"
            onclick="return confirm('Cancel this appointment?');"
        >
            Cancel
        </a>

    `;

}// =================================
// MODAL ACTION BUTTONS
// =================================

const actionsElement =
    document.getElementById(
        'modalActions'
    );

actionsElement.innerHTML = "";


// PENDING
if (props.status === 'pending') {

    actionsElement.innerHTML = `

        <a
            href="?action=approve&id=${event.id}"
            class="modal-approve"
        >
            Approve
        </a>

        <a
            href="?action=reject&id=${event.id}"
            class="modal-reject"
            onclick="return confirm('Reject this appointment?');"
        >
            Reject
        </a>

    `;

}


// APPROVED
else if (props.status === 'approved') {

    actionsElement.innerHTML = `

        <a
            href="?action=completed&id=${event.id}"
            class="modal-completed"
            onclick="return confirm('Mark this appointment as completed?');"
        >
            Mark Completed
        </a>

        <a
            href="?action=cancel&id=${event.id}"
            class="modal-cancel"
            onclick="return confirm('Cancel this appointment?');"
        >
            Cancel
        </a>

    `;

}// =================================
// MODAL ACTION BUTTONS
// =================================

const actionsElement =
    document.getElementById(
        'modalActions'
    );

actionsElement.innerHTML = "";


// PENDING
if (props.status === 'pending') {

    actionsElement.innerHTML = `

        <a
            href="?action=approve&id=${event.id}"
            class="modal-approve"
        >
            Approve
        </a>

        <a
            href="?action=reject&id=${event.id}"
            class="modal-reject"
            onclick="return confirm('Reject this appointment?');"
        >
            Reject
        </a>

    `;

}


// APPROVED
else if (props.status === 'approved') {

    actionsElement.innerHTML = `

        <a
            href="?action=completed&id=${event.id}"
            class="modal-completed"
            onclick="return confirm('Mark this appointment as completed?');"
        >
            Mark Completed
        </a>

        <a
            href="?action=cancel&id=${event.id}"
            class="modal-cancel"
            onclick="return confirm('Cancel this appointment?');"
        >
            Cancel
        </a>

    `;

        }}

        .menu a:hover,
        .menu a.active {
            background-color: #1e88e5;
        }

        .navbar > a.logout {
            width: 180px;
            margin-top: auto;
            margin-left: auto;
            margin-right: auto;
            padding: 12px 18px;
            text-align: center;
            text-decoration: none;
            background-color: #e53935;
            color: white;
            border-radius: 8px;
        }

        .navbar > a.logout:hover {
            background-color: #c62828;
        }

        /* =====================================
           MAIN CONTENT
        ===================================== */

        .main-content {
            margin-left: 220px;
            padding: 30px;
            width: calc(100% - 220px);
            min-height: 100vh;
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
           CALENDAR SECTION
        ===================================== */

        .calendar-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            width: 100%;
        }


        .calendar-section h2 {
            margin: 0 0 20px 0;
            color: #0f4c81;
            font-size: 22px;
        }


        #calendar {
            width: 100%;
            background-color: white;
        }


        .fc {
            font-family: Arial, sans-serif;
        }


        .fc .fc-toolbar-title {
            font-size: 22px;
            color: #0f4c81;
            font-weight: bold;
        }


        .fc .fc-button {
            background-color: #0f4c81;
            border-color: #0f4c81;
            font-weight: bold;
        }


        .fc .fc-button:hover {
            background-color: #1e88e5;
            border-color: #1e88e5;
        }


        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #1e88e5;
            border-color: #1e88e5;
        }


        .fc .fc-button:disabled {
            opacity: 0.6;
        }


        /* TIME GRID */

        .fc-timegrid-slot {
            height: 45px !important;
        }


        .fc-timegrid-axis {
            width: 75px;
        }


        .fc-timegrid-slot-label {
            font-size: 12px;
            color: #555;
        }


        .fc-timegrid-col {
            background-color: #ffffff;
        }


        .fc-timegrid-col.fc-day-today {
            background-color: #f4f9ff;
        }


        /* EVENTS */

        .fc-event {
            border: none !important;
            border-radius: 6px !important;
            padding: 5px 7px !important;
            cursor: pointer;
            font-size: 12px;
            overflow: hidden;
        }


        .fc-event-title {
            font-weight: bold;
        }


        .fc-event.event-pending {
            background-color: #f59e0b !important;
            border-left: 4px solid #d97706 !important;
        }


        .fc-event.event-approved {
            background-color: #22c55e !important;
            border-left: 4px solid #15803d !important;
        }


        .fc-event.event-rejected {
            background-color: #ef4444 !important;
            border-left: 4px solid #b91c1c !important;
        }


        .fc-event.event-cancelled {
            background-color: #64748b !important;
            border-left: 4px solid #475569 !important;
        }


        .fc-event.event-completed {
            background-color: #3b82f6 !important;
            border-left: 4px solid #1d4ed8 !important;
        }


        /* CALENDAR LEGEND */

        .calendar-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }


        .legend {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: #555;
        }


        .legend-box {
            width: 14px;
            height: 14px;
            border-radius: 3px;
        }


        .legend-pending {
            background-color: #f59e0b;
        }


        .legend-approved {
            background-color: #22c55e;
        }


        .legend-completed {
            background-color: #3b82f6;
        }


        .legend-cancelled {
            background-color: #64748b;
        }


        .legend-rejected {
            background-color: #ef4444;
        }


        /* =====================================
           TABLE SECTION
        ===================================== */

        .table-section {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            width: 100%;
        }


        .table-section h2 {
            color: #0f4c81;
            margin-top: 0;
            margin-bottom: 20px;
        }


        /* =====================================
           FILTER
        ===================================== */

        .filter-box {
            background-color: #f8fafc;
            padding: 18px;
            border-radius: 8px;
            margin-bottom: 20px;
        }


        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: end;
        }


        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }


        .filter-group label {
            font-size: 13px;
            font-weight: bold;
            color: #0f4c81;
        }


        .filter-group input,
        .filter-group select {
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            background-color: white;
            min-width: 170px;
        }


        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #1e88e5;
        }


        .filter-btn {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            background-color: #1e88e5;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }


        .filter-btn:hover {
            background-color: #0f4c81;
        }


        .reset-btn {
            padding: 10px 18px;
            border-radius: 6px;
            background-color: #64748b;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }


        .reset-btn:hover {
            background-color: #475569;
        }


        /* =====================================
           TABLE
        ===================================== */

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
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
           STATUS
        ===================================== */

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 13px;
        }


        .pending {
            background-color: #fff3cd;
            color: #856404;
        }


        .approved {
            background-color: #dcfce7;
            color: #166534;
        }


        .rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }


        .cancelled {
            background-color: #f1f5f9;
            color: #475569;
        }


        .completed {
            background-color: #dbeafe;
            color: #1565c0;
        }


        /* =====================================
           BUTTON
        ===================================== */

        .btn {
            display: inline-block;
            padding: 7px 10px;
            margin: 2px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 12px;
            border: none;
            white-space: nowrap;
        }


        .btn-view {
            background-color: #1565c0;
        }


        .btn-approve {
            background-color: #2e7d32;
        }


        .btn-reject {
            background-color: #e53935;
        }


        .btn-cancel {
            background-color: #ef6c00;
        }


        .btn-completed {
            background-color: #6a1b9a;
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
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }


        .pagination a {
            background-color: #f1f5f9;
            color: #0f4c81;
        }


        .pagination a:hover {
            background-color: #dbeafe;
        }


        .pagination .active {
            background-color: #1e88e5;
            color: white;
            font-weight: bold;
        }


        .pagination .disabled {
            color: #aaa;
            background-color: #f8fafc;
        }


        .page-info {
            text-align: center;
            margin-top: 15px;
            color: #666;
            font-size: 13px;
        }


        /* =====================================
           APPOINTMENT MODAL
        ===================================== */

        .appointment-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }


        .appointment-modal-content {
            background-color: white;
            width: 100%;
            max-width: 450px;
            border-radius: 12px;
            padding: 25px;
            position: relative;
            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.2);
        }


        .modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            color: #777;
            cursor: pointer;
        }


        .modal-close:hover {
            color: #333;
        }


        .appointment-modal-content h3 {
            margin-top: 0;
            color: #0f4c81;
        }


        .appointment-info {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 15px;
        }


        .appointment-info p {
            margin: 10px 0;
            font-size: 14px;
        }


        .appointment-info strong {
            color: #0f4c81;
        }


        .modal-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 12px;
        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 900px) {

            .main-content {
                margin-left: 220px;
                width: calc(100% - 220px);
                padding: 20px;
            }


            .calendar-section {
                padding: 15px;
            }


            .fc .fc-toolbar {
                flex-direction: column;
                gap: 10px;
            }


            .fc .fc-toolbar-title {
                font-size: 18px;
            }


            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }


            .filter-group input,
            .filter-group select {
                width: 100%;
            }

        }


        @media (max-width: 600px) {

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }


            .navbar {
                display: none;
            }


            .calendar-section {
                padding: 10px;
            }


            .fc .fc-button {
                font-size: 11px;
                padding: 5px 8px;
            }

        }

    </style>

</head>


<body>


<?php include "counselor_sidebar.php"; ?>


<div class="main-content">


    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="header">

        <h1>
            Appointments
        </h1>

        <p>
            View and manage your student appointments.
        </p>

    </div>


    <!-- =====================================
         CALENDAR
    ====================================== -->

    <div class="calendar-section">

        <h2>
            Appointment Schedule
        </h2>


        <div id="calendar"></div>


        <div class="calendar-info">

            <div class="legend">
                <span class="legend-box legend-pending"></span>
                Pending
            </div>


            <div class="legend">
                <span class="legend-box legend-approved"></span>
                Approved
            </div>


            <div class="legend">
                <span class="legend-box legend-completed"></span>
                Completed
            </div>


            <div class="legend">
                <span class="legend-box legend-cancelled"></span>
                Cancelled
            </div>


            <div class="legend">
                <span class="legend-box legend-rejected"></span>
                Rejected
            </div>

        </div>

    </div>


    <!-- =====================================
         APPOINTMENT TABLE
    ====================================== -->

    <div class="table-section">

        <h2>
            Appointment List
        </h2>


        <!-- FILTER -->

        <div class="filter-box">

            <form
                method="GET"
                action="counselor_appointments.php"
                class="filter-form"
            >

                <div class="filter-group">

                    <label>
                        Search Student
                    </label>

                    <input
                        type="text"
                        name="search"
                        placeholder="Name or Student ID"
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                </div>


                <div class="filter-group">

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <option value="">
                            All Status
                        </option>

                        <option
                            value="pending"
                            <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>
                        >
                            Pending
                        </option>

                        <option
                            value="approved"
                            <?php echo ($status_filter === 'approved') ? 'selected' : ''; ?>
                        >
                            Approved
                        </option>

                        <option
                            value="completed"
                            <?php echo ($status_filter === 'completed') ? 'selected' : ''; ?>
                        >
                            Completed
                        </option>

                        <option
                            value="cancelled"
                            <?php echo ($status_filter === 'cancelled') ? 'selected' : ''; ?>
                        >
                            Cancelled
                        </option>

                        <option
                            value="rejected"
                            <?php echo ($status_filter === 'rejected') ? 'selected' : ''; ?>
                        >
                            Rejected
                        </option>

                    </select>

                </div>


                <div class="filter-group">

                    <label>
                        Appointment Date
                    </label>

                    <input
                        type="date"
                        name="date"
                        value="<?php echo htmlspecialchars($date_filter); ?>"
                    >

                </div>


                <div class="filter-group">

                    <button
                        type="submit"
                        class="filter-btn"
                    >
                        Search
                    </button>

                </div>


                <div class="filter-group">

                    <a
                        href="counselor_appointments.php"
                        class="reset-btn"
                    >
                        Reset
                    </a>

                </div>

            </form>

        </div>


        <!-- TABLE -->

        <div class="table-wrapper">

            <table>

                <tr>

                    <th>Date</th>

                    <th>Time</th>

                    <th>Student Name</th>

                    <th>Appointment Reason</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>


                <?php

                if ($result->num_rows > 0):

                    while (
                        $row =
                        $result->fetch_assoc()
                    ):

                ?>


                <tr>


                    <!-- DATE -->

                    <td>

                        <?php

                        echo date(
                            "d M Y",
                            strtotime(
                                $row['appointment_date']
                            )
                        );

                        ?>

                    </td>


                    <!-- TIME -->

                    <td>

                        <?php

                        echo date(
                            "h:i A",
                            strtotime(
                                $row['appointment_time']
                            )
                        );

                        ?>

                        -

                        <?php

                        echo date(
                            "h:i A",
                            strtotime(
                                $row['appointment_time']
                                . " +30 minutes"
                            )
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

                        <span class="status <?php

                            echo htmlspecialchars(
                                $row['status']
                            );

                        ?>">

                            <?php

                            echo ucfirst(
                                $row['status']
                            );

                            ?>

                        </span>

                    </td>


                    <!-- ACTION -->

                    <td>


                        <!-- VIEW -->

                        <a
                            href="counselor_view_appointment.php?id=<?php echo $row['id']; ?>"
                            class="btn btn-view"
                        >
                            View
                        </a>


                        <!-- PENDING -->

                        <?php if ($row['status'] === 'pending'): ?>

                            <a
                                href="?action=approve&id=<?php echo $row['id']; ?>"
                                class="btn btn-approve"
                            >
                                Approve
                            </a>


                            <a
                                href="?action=reject&id=<?php echo $row['id']; ?>"
                                class="btn btn-reject"
                                onclick="return confirm('Reject this appointment?');"
                            >
                                Reject
                            </a>

                        <?php endif; ?>


                        <!-- APPROVED -->

                        <?php if ($row['status'] === 'approved'): ?>

                            <a
                                href="?action=completed&id=<?php echo $row['id']; ?>"
                                class="btn btn-completed"
                                onclick="return confirm('Mark this appointment as completed?');"
                            >
                                Mark Completed
                            </a>


                            <a
                                href="?action=cancel&id=<?php echo $row['id']; ?>"
                                class="btn btn-cancel"
                                onclick="return confirm('Cancel this appointment?');"
                            >
                                Cancel
                            </a>

                        <?php endif; ?>


                    </td>


                </tr>


                <?php

                    endwhile;

                else:

                ?>


                <tr>

                    <td
                        colspan="6"
                        style="text-align:center; padding:30px;"
                    >

                        No appointments found.

                    </td>

                </tr>


                <?php endif; ?>


            </table>

        </div>


        <!-- PAGINATION -->

        <?php if ($total_pages > 1): ?>

            <div class="pagination">


                <!-- PREVIOUS -->

                <?php if ($page > 1): ?>

                    <a
                        href="?<?php
                            echo http_build_query([
                                'search' => $search,
                                'status' => $status_filter,
                                'date' => $date_filter,
                                'page' => $page - 1
                            ]);
                        ?>"
                    >
                        ←
                    </a>

                <?php else: ?>

                    <span class="disabled">
                        ←
                    </span>

                <?php endif; ?>


                <!-- PAGE NUMBERS -->

                <?php

                $start_page =
                    max(1, $page - 2);

                $end_page =
                    min(
                        $total_pages,
                        $page + 2
                    );


                for (
                    $i = $start_page;
                    $i <= $end_page;
                    $i++
                ):

                ?>


                    <?php if ($i == $page): ?>

                        <span class="active">
                            <?php echo $i; ?>
                        </span>

                    <?php else: ?>

                        <a
                            href="?<?php
                                echo http_build_query([
                                    'search' => $search,
                                    'status' => $status_filter,
                                    'date' => $date_filter,
                                    'page' => $i
                                ]);
                            ?>"
                        >
                            <?php echo $i; ?>
                        </a>

                    <?php endif; ?>


                <?php endfor; ?>


                <!-- NEXT -->

                <?php if ($page < $total_pages): ?>

                    <a
                        href="?<?php
                            echo http_build_query([
                                'search' => $search,
                                'status' => $status_filter,
                                'date' => $date_filter,
                                'page' => $page + 1
                            ]);
                        ?>"
                    >
                        →
                    </a>

                <?php else: ?>

                    <span class="disabled">
                        →
                    </span>

                <?php endif; ?>


            </div>

        <?php endif; ?>


        <!-- PAGE INFO -->

        <div class="page-info">

            Showing

            <?php
            echo $total_appointments > 0
                ? ($offset + 1)
                : 0;
            ?>

            -

            <?php
            echo min(
                $offset + $limit,
                $total_appointments
            );
            ?>

            of

            <?php echo $total_appointments; ?>

            appointments

        </div>


    </div>


</div>


<!-- =========================================
     APPOINTMENT MODAL
========================================== -->

<div
    class="appointment-modal"
    id="appointmentModal"
>

    <div class="appointment-modal-content">

        <span
            class="modal-close"
            onclick="closeAppointmentModal()"
        >
            &times;
        </span>


        <h3>
            Appointment Details
        </h3>


        <div class="appointment-info">

            <p>
                <strong>Student:</strong>
                <span id="modalStudent"></span>
            </p>


            <p>
                <strong>Student ID:</strong>
                <span id="modalStudentId"></span>
            </p>


            <p>
                <strong>Date:</strong>
                <span id="modalDate"></span>
            </p>


            <p>
                <strong>Time:</strong>
                <span id="modalTime"></span>
                -
                <span id="modalEndTime"></span>
            </p>


            <p>
                <strong>Reason:</strong>
                <span id="modalReason"></span>
            </p>


            <p>
                <strong>Status:</strong>
                <span
                    id="modalStatus"
                    class="modal-status"
                ></span>
            </p>
            <div 
                id="modalActions" 
                class="modal-actions">
            </div>

        </div>

    </div>

</div>


<script>


// =========================================
// CALENDAR EVENTS
// =========================================

const calendarEvents =
    <?php echo $calendar_events_json; ?>;


// =========================================
// INITIALIZE CALENDAR
// =========================================

document.addEventListener(
    'DOMContentLoaded',
    function() {

        const calendarElement =
            document.getElementById('calendar');


        const calendar =
            new FullCalendar.Calendar(
                calendarElement,
                {

                    initialView:
                        'timeGridWeek',


                    headerToolbar: {

                        left:
                            'prev,next today',

                        center:
                            'title',

                        right:
                            'dayGridMonth,timeGridWeek,timeGridDay'
                    },


                    buttonText: {

                        today:
                            'Today',

                        month:
                            'Month',

                        week:
                            'Week',

                        day:
                            'Day'
                    },


                    events:
                        calendarEvents,


                    slotLabelFormat: {

                        hour:
                            'numeric',

                        minute:
                            '2-digit',

                        hour12:
                            true
                    },


                    eventTimeFormat: {

                        hour:
                            'numeric',

                        minute:
                            '2-digit',

                        hour12:
                            true
                    },


                    slotMinTime:
                        '08:00:00',

                    slotMaxTime:
                        '17:00:00',


                    slotDuration:
                        '00:30:00',


                    snapDuration:
                        '00:30:00',


                    defaultTimedEventDuration:
                        '00:30:00',


                    allDaySlot:
                        false,


                    weekends:
                        false,


                    businessHours: [

                        {
                            daysOfWeek: [1, 2, 3, 4],
                            startTime: '08:30',
                            endTime: '13:00'
                        },

                        {
                            daysOfWeek: [1, 2, 3, 4],
                            startTime: '14:00',
                            endTime: '16:30'
                        },

                        {
                            daysOfWeek: [5],
                            startTime: '08:30',
                            endTime: '12:00'
                        },

                        {
                            daysOfWeek: [5],
                            startTime: '15:00',
                            endTime: '16:30'
                        }

                    ],


                    height:
                        'auto',


                    scrollTime:
                        '08:00:00',


                    firstDay:
                        1,


                    // =================================
                    // EVENT CLICK
                    // =================================

                    eventClick:
                        function(info) {

                            const event =
                                info.event;

                            const props =
                                event.extendedProps;


                            document.getElementById(
                                'modalStudent'
                            ).textContent =
                                props.student_name;


                            document.getElementById(
                                'modalStudentId'
                            ).textContent =
                                props.student_id;


                            const eventDate =
                                event.start;


                            const dateText =
                                eventDate.toLocaleDateString(
                                    'en-GB',
                                    {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric'
                                    }
                                );


                            document.getElementById(
                                'modalDate'
                            ).textContent =
                                dateText;


                            const startTime =
                                event.start;


                            const startText =
                                startTime.toLocaleTimeString(
                                    'en-US',
                                    {
                                        hour: 'numeric',
                                        minute: '2-digit'
                                    }
                                );


                            document.getElementById(
                                'modalTime'
                            ).textContent =
                                startText;


                            const endTime =
                                event.end;


                            const endText =
                                endTime.toLocaleTimeString(
                                    'en-US',
                                    {
                                        hour: 'numeric',
                                        minute: '2-digit'
                                    }
                                );


                            document.getElementById(
                                'modalEndTime'
                            ).textContent =
                                endText;


                            document.getElementById(
                                'modalReason'
                            ).textContent =
                                props.reason;


                            const statusElement =
                                document.getElementById(
                                    'modalStatus'
                                );


                            statusElement.textContent =
                                props.status
                                    .charAt(0)
                                    .toUpperCase()
                                +
                                props.status.slice(1);


                            statusElement.className =
                                'modal-status';


                            if (
                                props.status === 'pending'
                            ) {

                                statusElement.classList.add(
                                    'pending'
                                );

                            } else if (
                                props.status === 'approved'
                            ) {

                                statusElement.classList.add(
                                    'approved'
                                );

                            } else if (
                                props.status === 'completed'
                            ) {

                                statusElement.classList.add(
                                    'completed'
                                );

                            } else if (
                                props.status === 'cancelled'
                            ) {

                                statusElement.classList.add(
                                    'cancelled'
                                );

                            } else if (
                                props.status === 'rejected'
                            ) {

                                statusElement.classList.add(
                                    'rejected'
                                );
                            }


                            document.getElementById(
                                'appointmentModal'
                            ).style.display =
                                'flex';

                        }

                });


        calendar.render();

    });


// =========================================
// CLOSE MODAL
// =========================================

function closeAppointmentModal() {

    document.getElementById(
        'appointmentModal'
    ).style.display =
        'none';
}


// =========================================
// CLOSE WHEN CLICK OUTSIDE
// =========================================

window.addEventListener(
    'click',
    function(event) {

        const modal =
            document.getElementById(
                'appointmentModal'
            );


        if (
            event.target === modal
        ) {

            closeAppointmentModal();

        }

    }
);

</script>


</body>

</html>