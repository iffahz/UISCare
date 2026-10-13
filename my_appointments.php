<?php

require_once "student_check.php";
require_once "db.php";

$student_id = $_SESSION['user_id'];


// =========================================
// GET STUDENT APPOINTMENTS
// =========================================

$sql = "
    SELECT
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.reason,
        a.status,
        c.fullname AS counselor_name
    FROM appointment a
    INNER JOIN `user` c
        ON a.counselor_user_id = c.id
    WHERE a.student_user_id = ?
    ORDER BY
        a.appointment_date DESC,
        a.appointment_time DESC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

<title>My Appointments</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #dbeafe;
}


/* =========================
   MAIN CONTENT
========================= */

.main {
    margin: 0 auto;
    width: 100%;
    max-width: 1200px;
    padding: 30px;
}


/* =========================
   PAGE TITLE
========================= */

.main h1 {
    margin-top: 0;
    margin-bottom: 25px;
    color: #0f4c81;
}


/* =========================
   TABLE CONTAINER
========================= */

.container {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    overflow-x: auto;
    max-width: 1200px;
    margin: 0 auto;
}


/* =========================
   TABLE
========================= */

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #0f4c81;
    color: white;
    padding: 12px;
    text-align: left;
}

td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

tr:hover {
    background: #f5f9ff;
}


/* =========================
   ACTION BUTTON
========================= */

.btn {
    display: inline-block;
    padding: 7px 11px;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 12px;
    margin-right: 5px;
}

.view {
    background: #1565c0;
}

.view:hover {
    background: #0d47a1;
}

.cancel {
    background: #e53935;
}

.cancel:hover {
    background: #c62828;
}


/* =========================
   STATUS
========================= */

.status {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
}

.pending {
    background: #fff3cd;
    color: #856404;
}

.approved {
    background: #d4edda;
    color: #155724;
}

.rejected,
.cancelled {
    background: #f8d7da;
    color: #721c24;
}

.completed {
    background: #dbeafe;
    color: #1565c0;
}


/* =========================
   CANCELLATION MESSAGE
========================= */

.cancel-unavailable {
    color: #777;
    font-size: 12px;
}


/* =========================
   BACK BUTTON
========================= */

.back-container {
    text-align: right;
    margin-top: 20px;
}

.back-btn {
    display: inline-block;
    padding: 10px 18px;
    background: #0f4c81;
    color: white;
    text-decoration: none;
    border-radius: 7px;
    font-size: 14px;
    font-weight: bold;
    transition: 0.2s;
}

.back-btn:hover {
    background: #1565c0;
}


/* =========================
   MOBILE
========================= */

@media (max-width: 768px) {

    .main {
        margin-left: 0;
        padding: 20px;
    }

    .container {
        padding: 15px;
    }

    .back-container {
        text-align: right;
    }

}

</style>

</head>


<body>

<div class="main">

    <h1>My Appointments</h1>


    <div class="container">

        <table>

            <tr>

                <th>Date</th>

                <th>Time</th>

                <th>Counselor</th>

                <th>Reason</th>

                <th>Status</th>

                <th>Action</th>

            </tr>


            <?php if ($result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                <?php

                // =========================================
                // CHECK CANCELLATION PERIOD
                // Student can cancel 3 days or more before
                // the appointment date.
                // =========================================

                $today = new DateTime();

                $appointment_date = new DateTime(
                    $row['appointment_date']
                );

                $days_difference = $today->diff(
                    $appointment_date
                )->days;


                // Check whether appointment is in the future
                $is_future = $appointment_date > $today;


                // Student can cancel only when:
                // 1. Status is pending or approved
                // 2. Appointment is in the future
                // 3. At least 3 days before appointment

                $can_cancel =
                    (
                        $row['status'] === 'pending' ||
                        $row['status'] === 'approved'
                    )
                    &&
                    $is_future
                    &&
                    $days_difference >= 3;

                ?>

                <tr>

                    <td>

                        <?php

                        echo date(
                            "d M Y",
                            strtotime($row['appointment_date'])
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo date(
                            "h:i A",
                            strtotime($row['appointment_time'])
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['counselor_name']
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['reason']
                        );

                        ?>

                    </td>


                    <td>

                        <span class="status <?php echo $row['status']; ?>">

                            <?php

                            echo ucfirst(
                                $row['status']
                            );

                            ?>

                        </span>

                    </td>


                    <td>

                        <!-- VIEW BUTTON -->

                        <a
                            href="view_appointment.php?id=<?php echo $row['id']; ?>"
                            class="btn view">

                            View

                        </a>


                        <!-- CANCEL BUTTON -->

                        <?php if ($can_cancel): ?>

                            <a
                                href="cancel_appointment.php?id=<?php echo $row['id']; ?>"
                                class="btn cancel"
                                onclick="return confirm('Are you sure you want to cancel this appointment?');">

                                Cancel

                            </a>

                        <?php elseif (
                            ($row['status'] === 'pending' ||
                             $row['status'] === 'approved')
                            &&
                            $is_future
                            &&
                            $days_difference < 3
                        ): ?>

                            <span class="cancel-unavailable">

                                Cancellation unavailable

                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

                <?php endwhile; ?>


            <?php else: ?>

                <tr>

                    <td
                        colspan="6"
                        style="text-align:center;">

                        No appointments found.

                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>


    <!-- BACK BUTTON -->

    <div class="back-container">

        <a
            href="student_dashboard.php"
            class="back-btn">

            ← Back

        </a>

    </div>

</div>

</body>

</html>