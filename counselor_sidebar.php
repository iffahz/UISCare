<div class="navbar">

<div class="logo">

    <img
        src="images/logo_counseling_system.png"
        alt="UISCare Logo"
        class="logo-image">

</div>

    <div class="menu">

        <a href="counselor_dashboard.php"
           class="<?php echo basename($_SERVER['PHP_SELF']) == 'counselor_dashboard.php' ? 'active' : ''; ?>">
            Dashboard
        </a>

        <a href="counselor_appointments.php"
           class="<?php echo basename($_SERVER['PHP_SELF']) == 'counselor_appointments.php' ? 'active' : ''; ?>">
            Appointments
        </a>

        <a href="add_consultation.php"
           class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_consultation.php' ? 'active' : ''; ?>">
            Book Appointment
        </a>

        <a href="counselor_consultation.php"
           class="<?php echo basename($_SERVER['PHP_SELF']) == 'counselor_consultation.php' ? 'active' : ''; ?>">
            Consultation Records
        </a>

        <a href="counselor_profile.php"
           class="<?php echo basename($_SERVER['PHP_SELF']) == 'counselor_profile.php' ? 'active' : ''; ?>">
            My Profile
        </a>

    </div>

    <a href="logout.php" class="logout">
        Logout
    </a>

</div>

<style>

.logo {
    margin-bottom: 35px;
    text-align: center;
}

.logo-image {
    width: 180px;
    max-height: 80px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

</style>