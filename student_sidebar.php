<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="navbar">

    <div class="logo">

        <img
            src="images/logo_counseling_system.png"
            alt="UISCare"
        >

    </div>


    <div class="menu">

        <a
            href="student_dashboard.php"
            class="<?php echo $current_page == 'student_dashboard.php' ? 'active' : ''; ?>">

            Dashboard

        </a>


        <a
            href="book_appointment.php"
            class="<?php echo $current_page == 'book_appointment.php' ? 'active' : ''; ?>">

            Book Appointment

        </a>


        <a
            href="my_appointments.php"
            class="<?php echo $current_page == 'my_appointments.php' ? 'active' : ''; ?>">

            My Appointments

        </a>


        <a
            href="consultation_records.php"
            class="<?php echo $current_page == 'consultation_records.php' ? 'active' : ''; ?>">

            Consultation Records

        </a>


        <a
            href="student_profile.php"
            class="<?php echo $current_page == 'student_profile.php' ? 'active' : ''; ?>">

            Profile

        </a>

    </div>


    <a href="logout.php" class="logout">
        Logout
    </a>

</div>

<style> 
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
            font-size: 22px; } 
            
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
            font-weight: bold; }
            
        .sidebar a.logout { 
            margin-top: auto; 
            background-color: #e53935; 
            text-align: center;
        } 
        
        .sidebar a.logout:hover { 
            background-color: #c62828; 
        } 
        
        </style>