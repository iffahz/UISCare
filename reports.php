<?php

require_once "admin_check.php";
require_once "db.php";


/* =========================================
   APPOINTMENT STATUS
========================================= */

$status_labels = [];
$status_values = [];

$sql_status = "
    SELECT
        status,
        COUNT(*) AS total
    FROM appointment
    GROUP BY status
    ORDER BY status ASC
";

$result_status = $conn->query($sql_status);

if ($result_status) {

    while ($row = $result_status->fetch_assoc()) {

        $status_labels[] = ucfirst($row['status']);
        $status_values[] = (int)$row['total'];

    }

}


/* =========================================
   COUNSELING REASON
========================================= */

$reason_labels = [];
$reason_values = [];

$sql_reason = "
    SELECT
        reason,
        COUNT(*) AS total
    FROM appointment
    WHERE reason IS NOT NULL
    AND reason != ''
    GROUP BY reason
    ORDER BY total DESC
";

$result_reason = $conn->query($sql_reason);

if ($result_reason) {

    while ($row = $result_reason->fetch_assoc()) {

        $reason_labels[] = $row['reason'];
        $reason_values[] = (int)$row['total'];

    }

}


/* =========================================
   CONSULTATION BY COUNSELOR
========================================= */

$counselor_labels = [];
$counselor_values = [];

$sql_consultation = "
    SELECT
        u.fullname AS counselor_name,
        COUNT(c.id) AS total
    FROM consultation c

    INNER JOIN `user` u
        ON c.counselor_user_id = u.id

    GROUP BY c.counselor_user_id, u.fullname

    ORDER BY total DESC
";

$result_consultation = $conn->query($sql_consultation);

if ($result_consultation) {

    while ($row = $result_consultation->fetch_assoc()) {

        $counselor_labels[] = $row['counselor_name'];
        $counselor_values[] = (int)$row['total'];

    }

}


/* =========================================
   REPORT DATE
========================================= */

$report_date = date("d/m/Y");

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Report</title>


    <!-- Chart.js -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <style>

        /* =========================================
           GENERAL
        ========================================= */

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


        /* =========================================
           MAIN CONTENT
        ========================================= */

        .main-content {

            margin-left: 220px;

            padding: 30px;

            min-height: 100vh;

        }


        /* =========================================
           PAGE HEADER
        ========================================= */

        .page-header {

            background-color: white;

            padding: 20px 25px;

            border-radius: 10px;

            margin-bottom: 20px;

        }


        .page-header h1 {

            margin: 0;

            color: #0f4c81;

            font-size: 28px;

        }


        .page-header p {

            margin: 8px 0 0 0;

            color: #666;

            font-size: 14px;

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


/* =====================================
   SIDEBAR LINKS
===================================== */

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


/* =====================================
   REPORTS ACTIVE
===================================== */

.sidebar a[href="reports.php"] {

    background-color: #1e88e5;

    font-weight: bold;

}


/* =====================================
   LOGOUT
===================================== */

.sidebar a.logout {

    margin-top: auto;

    background-color: #e53935;

    text-align: center;

}


.sidebar a.logout:hover {

    background-color: #c62828;

}


/* =====================================
   SIDEBAR LOGO
===================================== */

.sidebar-logo {

    text-align: center;

    margin-bottom: 30px;

    padding: 0 15px;

}


.sidebar-logo img {

    width: 140px;

    height: auto;

    max-height: 100px;

    object-fit: contain;

}
        /* =========================================
           REPORT DATE
        ========================================= */

        .report-date {

            color: #666;

            font-size: 13px;

            margin-top: 8px;

        }


        /* =========================================
           PRINT BUTTON
        ========================================= */

        .report-actions {

            display: flex;

            justify-content: flex-end;

            margin-bottom: 20px;

        }


        .print-btn {

            background-color: #0f4c81;

            color: white;

            border: none;

            padding: 11px 20px;

            border-radius: 6px;

            font-size: 14px;

            cursor: pointer;

        }


        .print-btn:hover {

            background-color: #1e88e5;

        }


        /* =========================================
           CHART GRID
        ========================================= */

        .chart-grid {

            display: grid;

            grid-template-columns: 1fr;

            gap: 20px;

        }


        /* =========================================
           CHART CARD
        ========================================= */

        .chart-card {

            background-color: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow: 0 2px 8px rgba(0,0,0,0.08);

        }


        .chart-card h2 {

            margin: 0 0 6px 0;

            color: #0f4c81;

            font-size: 19px;

        }


        .chart-card p {

            margin: 0 0 20px 0;

            color: #666;

            font-size: 13px;

        }


        .chart-container {

            position: relative;

            height: 280px;

        }

        /* =========================================
           NO DATA
        ========================================= */

        .no-data {

            height: 280px;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #999;

            font-size: 14px;

        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media (max-width: 1000px) {

            .chart-grid {

                grid-template-columns: 1fr;

            }

        }


        @media (max-width: 600px) {

            .main-content {

                margin-left: 0;

                padding: 15px;

            }

        }


        /* =========================================
           PRINT STYLE
        ========================================= */

        @media print {

            body {

                background-color: white;

            }


            .sidebar,
            .report-actions {

                display: none !important;

            }


            .main-content {

                margin-left: 0;

                padding: 0;

            }


            .page-header {

                box-shadow: none;

                border: none;

                margin-bottom: 15px;

            }


            .page-header h1 {

                font-size: 24px;

            }


            .chart-card,
            .table-card {

                box-shadow: none;

                border: 1px solid #ddd;

                break-inside: avoid;

            }


            .chart-grid {

                grid-template-columns: 1fr 1fr;

            }


            .chart-container {

                height: 250px;

            }


            .table-card {

                margin-top: 20px;

            }


            .consultation-table th {

                background-color: #0f4c81 !important;

                color: white !important;

                -webkit-print-color-adjust: exact;

                print-color-adjust: exact;

            }

        }

    </style>

</head>


<body>


<!-- =========================================
     ADMIN SIDEBAR
========================================= -->

<?php include "admin_sidebar.php"; ?>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <h1>
            Admin Report
        </h1>


        <p>
            View appointment and consultation statistics.
        </p>


        <div class="report-date">

            Generated Date:
            <?php echo $report_date; ?>

        </div>

    </div>


    <!-- PRINT BUTTON -->

    <div class="report-actions">

        <button
            type="button"
            class="print-btn"
            onclick="window.print()"
        >

            🖨 Print Report

        </button>

    </div>


    <!-- =========================================
         CHARTS
    ========================================= -->

    <div class="chart-grid">


        <!-- APPOINTMENT STATUS -->

        <div class="chart-card">

            <h2>
                Appointment Status
            </h2>


            <p>
                Number of appointments based on status.
            </p>


            <?php if (count($status_labels) > 0): ?>

                <div class="chart-container">

                    <canvas id="appointmentStatusChart"></canvas>

                </div>

            <?php else: ?>

                <div class="no-data">

                    No appointment data available.

                </div>

            <?php endif; ?>

        </div>


        <!-- COUNSELING REASON -->

        <div class="chart-card">

            <h2>
                Counseling Reasons
            </h2>


            <p>
                Number of appointments based on counseling reasons.
            </p>


            <?php if (count($reason_labels) > 0): ?>

                <div class="chart-container">

                    <canvas id="reasonChart"></canvas>

                </div>

            <?php else: ?>

                <div class="no-data">

                    No counseling reason data available.

                </div>

            <?php endif; ?>

        </div>


        <!-- CONSULTATION BY COUNSELOR -->

        <div class="chart-card">

            <h2>
                Consultation Records by Counselor
            </h2>


            <p>
                Number of consultation records managed by each counselor.
            </p>


            <?php if (count($counselor_labels) > 0): ?>

                <div class="chart-container">

                    <canvas id="consultationChart"></canvas>

                </div>

            <?php else: ?>

                <div class="no-data">

                    No consultation records available.

                </div>

            <?php endif; ?>

        </div>


    </div>

<!-- =========================================
     CHART JAVASCRIPT
========================================= -->

<script>


/* =========================================
   APPOINTMENT STATUS CHART
========================================= */

<?php if (count($status_labels) > 0): ?>

new Chart(

    document.getElementById('appointmentStatusChart'),

    {

        type: 'doughnut',

        data: {

            labels:
                <?php echo json_encode($status_labels); ?>,

            datasets: [{

                label: 'Appointments',

                data:
                    <?php echo json_encode($status_values); ?>,

                backgroundColor: [

                    '#f59e0b',
                    '#3b82f6',
                    '#ef4444',
                    '#22c55e',
                    '#8b5cf6',
                    '#64748b'

                ],

                borderWidth: 1

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    position: 'bottom'

                }

            }

        }

    }

);

<?php endif; ?>


/* =========================================
   COUNSELING REASON CHART
========================================= */

<?php if (count($reason_labels) > 0): ?>

new Chart(

    document.getElementById('reasonChart'),

    {

        type: 'bar',

        data: {

            labels:
                <?php echo json_encode($reason_labels); ?>,

            datasets: [{

                label: 'Number of Appointments',

                data:
                    <?php echo json_encode($reason_values); ?>,

                backgroundColor: '#1e88e5',

                borderRadius: 8

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    display: false

                }

            },

            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {

                        precision: 0

                    }

                }

            }

        }

    }

);

<?php endif; ?>


/* =========================================
   CONSULTATION BY COUNSELOR
========================================= */

<?php if (count($counselor_labels) > 0): ?>

new Chart(

    document.getElementById('consultationChart'),

    {

        type: 'bar',

        data: {

            labels:
                <?php echo json_encode($counselor_labels); ?>,

            datasets: [{

                label: 'Consultation Records',

                data:
                    <?php echo json_encode($counselor_values); ?>,

                backgroundColor: '#0f4c81',

                borderRadius: 8

            }]

        },

        options: {

            indexAxis: 'y',

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    display: false

                }

            },

            scales: {

                x: {

                    beginAtZero: true,

                    ticks: {

                        precision: 0

                    }

                }

            }

        }

    }

);

<?php endif; ?>


</script>


</body>

</html>
