<?php

require_once "student_check.php";
require_once "db.php";

$student_user_id = $_SESSION['user_id'];


$sql = "
    SELECT
        c.id,
        a.appointment_date,
        u.fullname AS counselor_name,
        c.notes,
        c.created_at

    FROM consultation c

    INNER JOIN appointment a
        ON c.appointment_id = a.id

    INNER JOIN `user` u
        ON a.counselor_user_id = u.id

    WHERE a.student_user_id = ?

    ORDER BY c.created_at DESC
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $student_user_id
);

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
    background: #dbeafe;
}


/* =========================
   MAIN CONTENT
========================= */

.main {
    padding: 30px;
    min-height: 100vh;
}


/* =========================
   PAGE TITLE
========================= */

.main h1 {
    max-width: 1200px;
    margin: 0 auto 25px;

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
   BACK BUTTON
========================= */

.back-container {
    max-width: 1200px;

    margin: 20px auto 0;

    text-align: right;
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
        padding: 20px;
    }

    .container {
        padding: 15px;
    }

    .main h1 {
        margin-bottom: 20px;
    }

}

</style>

</head>

<body>


<div class="main">

    <h1>
        Consultation Records
    </h1>


    <div class="container">

        <table>

            <tr>

                <th>Date</th>

                <th>Counselor</th>

                <th>Notes</th>

                <th>Date Recorded</th>

            </tr>


            <?php if ($result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                <tr>

                    <td>

                        <?php

                        if (!empty($row['appointment_date'])) {

                            echo date(
                                "d M Y",
                                strtotime($row['appointment_date'])
                            );

                        } else {

                            echo "-";

                        }

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

                        $notes = $row['notes'] ?? '';

                        if (strlen($notes) > 60) {

                            echo htmlspecialchars(
                                substr($notes, 0, 60)
                            ) . "...";

                        } else {

                            echo htmlspecialchars($notes);

                        }

                        ?>

                    </td>


                    <td>

                        <?php

                        if (!empty($row['created_at'])) {

                            echo date(
                                "d M Y",
                                strtotime($row['created_at'])
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
                        colspan="4"
                        style="text-align:center;">

                        No consultation records found.

                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>


    <!-- BACK TO DASHBOARD -->

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
```
