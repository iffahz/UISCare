<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">

    <!-- LOGO -->
    <div class="sidebar-logo">
        <img src="images/logo_counseling_system.png" alt="Counseling System Logo">
    </div>

    <a href="admin_dashboard.php"
       class="<?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>">
        Dashboard
    </a>

    <a href="manage_counselor.php"
       class="<?php echo $current_page == 'manage_counselor.php' ? 'active' : ''; ?>">
        Manage Counselor
    </a>

    <a href="manage_student.php"
       class="<?php echo $current_page == 'manage_student.php' ? 'active' : ''; ?>">
        Manage Student
    </a>

    <a href="session_records.php"
       class="<?php echo $current_page == 'session_records.php' ? 'active' : ''; ?>">
        Session Records
    </a>

    <a href="reports.php"
       class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
        Reports
    </a>

    <a href="logout.php" class="logout">
        Logout
    </a>

</div>

<style>

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

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    margin: 8px 15px;
    border-radius: 8px;
}

.sidebar a:hover {
    background-color: #1e88e5;
}

.sidebar a.active {
    background-color: #1e88e5;
    font-weight: bold;
}

.sidebar a.logout {
    background-color: #e53935;
}


.sidebar a.logout:hover {
    background-color: #c62828;
}

</style>
