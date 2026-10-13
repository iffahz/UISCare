<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>UISCare - Counseling Booking System</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #dbeafe;
            color: #1f2937;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            width: 100%;
            height: 78px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 7%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo img {
            width: 165px;
            height: auto;
            display: block;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .nav-menu a {
            text-decoration: none;
            color: #374151;
            font-size: 15px;
            font-weight: 600;
            transition: 0.3s;
        }

        .nav-menu a:hover {
            color: #1976d2;
        }

        .login-btn {
            background: #1976d2;
            color: white !important;
            padding: 11px 23px;
            border-radius: 8px;
        }

        .login-btn:hover {
            background: #125ea8;
        }


        /* =========================
           HERO SECTION
        ========================= */

        .hero {
            min-height: calc(100vh - 78px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 70px 7%;
        }

        .hero-container {
            width: 100%;
            max-width: 1150px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 60px;
        }

        .hero-content {
            flex: 1;
        }

        .welcome-text {
            color: #1976d2;
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .hero-content h1 {
            font-size: 48px;
            line-height: 1.15;
            color: #123b5d;
            margin-bottom: 20px;
        }

        .hero-content h1 span {
            color: #1976d2;
        }

        .hero-content p {
            font-size: 17px;
            line-height: 1.8;
            color: #4b5563;
            max-width: 600px;
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
        }

        .primary-btn,
        .secondary-btn {
            text-decoration: none;
            padding: 14px 27px;
            border-radius: 9px;
            font-size: 15px;
            font-weight: bold;
            transition: 0.3s;
        }

        .primary-btn {
            background: #1976d2;
            color: white;
        }

        .primary-btn:hover {
            background: #125ea8;
            transform: translateY(-2px);
        }

        .secondary-btn {
            background: white;
            color: #1976d2;
            border: 2px solid #1976d2;
        }

        .secondary-btn:hover {
            background: #1976d2;
            color: white;
        }


        /* =========================
           HERO CARD
        ========================= */

        .hero-card-container {
            flex: 0 0 390px;
            display: flex;
            justify-content: center;
        }

        .hero-card {
            width: 360px;
            background: white;
            padding: 35px;
            border-radius: 22px;
            box-shadow: 0 15px 40px rgba(30, 64, 175, 0.15);
            text-align: center;
        }

        .hero-card-logo {
            width: 210px;
            margin-bottom: 20px;
        }

        .hero-card h3 {
            color: #123b5d;
            font-size: 22px;
            margin-bottom: 12px;
        }

        .hero-card p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
        }


        /* =========================
           ABOUT SECTION
        ========================= */

        .section {
            background: white;
            padding: 80px 7%;
        }

        .section-title {
            text-align: center;
            margin-bottom: 45px;
        }

        .section-title h2 {
            color: #123b5d;
            font-size: 32px;
            margin-bottom: 12px;
        }

        .section-title p {
            color: #6b7280;
            font-size: 15px;
        }

        .features {
            max-width: 1100px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
        }

        .feature-card {
            background: #f8fbff;
            border: 1px solid #e5edf6;
            border-radius: 15px;
            padding: 30px 22px;
            text-align: center;
            transition: 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .feature-icon {
            width: 55px;
            height: 55px;
            background: #dbeafe;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 25px;
        }

        .feature-card h3 {
            font-size: 17px;
            color: #123b5d;
            margin-bottom: 10px;
        }

        .feature-card p {
            font-size: 13px;
            line-height: 1.6;
            color: #6b7280;
        }


        /* =========================
           HOW IT WORKS
        ========================= */

        .how-section {
            background: #dbeafe;
            padding: 80px 7%;
        }

        .steps {
            max-width: 1000px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        .step {
            text-align: center;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: #1976d2;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 18px;
            font-weight: bold;
        }

        .step h3 {
            color: #123b5d;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .step p {
            color: #5f6b78;
            font-size: 13px;
            line-height: 1.6;
        }


        /* =========================
           CTA
        ========================= */

        .cta {
            background: white;
            padding: 75px 20px;
            text-align: center;
        }

        .cta h2 {
            color: #123b5d;
            font-size: 31px;
            margin-bottom: 15px;
        }

        .cta p {
            color: #6b7280;
            margin-bottom: 25px;
            font-size: 15px;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {
            background: #0f4c81;
            color: white;
            padding: 35px 7%;
        }

        .footer-content {
            max-width: 1150px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
        }

        .footer-logo img {
            width: 150px;
            background: white;
            padding: 7px;
            border-radius: 7px;
        }

        .footer-text {
            text-align: right;
        }

        .footer-text p {
            font-size: 13px;
            margin-bottom: 6px;
        }

        .copyright {
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.2);
            margin-top: 25px;
            padding-top: 20px;
            font-size: 12px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .navbar {
                padding: 0 4%;
            }

            .nav-menu {
                gap: 15px;
            }

            .hero-container {
                flex-direction: column;
                text-align: center;
            }

            .hero-content p {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .features,
            .steps {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero-card-container {
                flex: auto;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-text {
                text-align: center;
            }
        }


        @media (max-width: 600px) {

            .navbar {
                height: auto;
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero {
                padding: 50px 20px;
            }

            .hero-content h1 {
                font-size: 36px;
            }

            .hero-card {
                width: 100%;
            }

            .features,
            .steps {
                grid-template-columns: 1fr;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .primary-btn,
            .secondary-btn {
                width: 100%;
            }
        }

    </style>
</head>

<body>


<!-- =========================
     NAVIGATION BAR
========================= -->

<header class="navbar">

    <div class="logo">
        <a href="index.php">
            <img src="images/logo_counseling_system.png"
                 alt="UISCare Counseling System">
        </a>
    </div>

    <nav class="nav-menu">

        <a href="#home">Home</a>

        <a href="#about">About</a>

        <a href="#services">Services</a>

        <a href="#how-it-works">How It Works</a>

        <a href="#contact">Contact</a>

        <a href="login.php" class="login-btn">Login</a>

    </nav>

</header>


<!-- =========================
     HERO
========================= -->

<section class="hero" id="home">

    <div class="hero-container">

        <div class="hero-content">

            <div class="welcome-text">
                Welcome to UISCare
            </div>

            <h1>
                Your Well-being<br>
                <span>Matters.</span>
            </h1>

            <p>
                UISCare Counseling System provides a simple and convenient
                way for students to manage counseling appointments and
                connect with counselors.
            </p>

            <div class="hero-buttons">

                <a href="register.php" class="primary-btn">
                    Get Started
                </a>

                <a href="login.php" class="secondary-btn">
                    Login
                </a>

            </div>

        </div>


        <div class="hero-card-container">

            <div class="hero-card">

                <img src="images/logo_counseling_system.png"
                     alt="UISCare Logo"
                     class="hero-card-logo">

                <h3>
                    Counseling Made Easier
                </h3>

                <p>
                    Book your counseling appointment,
                    manage your scheduled sessions and
                    keep track of your counseling records
                    in one place.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- =========================
     SERVICES
========================= -->

<section class="section" id="services">

    <div class="section-title">

        <h2>Our Services</h2>

        <p>
            Everything you need to manage your counseling appointments.
        </p>

    </div>


    <div class="features">


        <div class="feature-card">

            <div class="feature-icon">
                📅
            </div>

            <h3>
                Book Appointment
            </h3>

            <p>
                Schedule a counseling session
                with an available counselor.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                👩‍💼
            </div>

            <h3>
                Choose Counselor
            </h3>

            <p>
                View available counselors and
                choose the one that suits your needs.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                📋
            </div>

            <h3>
                Manage Appointment
            </h3>

            <p>
                View, update and manage your
                scheduled counseling appointments.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                💬
            </div>

            <h3>
                Consultation Records
            </h3>

            <p>
                View your completed consultation
                records through the system.
            </p>

        </div>


    </div>

</section>


<!-- =========================
     ABOUT
========================= -->

<section class="section" id="about">

    <div class="section-title">

        <h2>About UISCare</h2>

        <p>
            A centralized counseling booking platform for students and counselors.
        </p>

    </div>


    <div style="
        max-width: 900px;
        margin: auto;
        text-align: center;
        color: #5f6b78;
        line-height: 1.9;
        font-size: 15px;
    ">

        <p>
            UISCare Counseling System is designed to simplify the
            management of counseling appointments and consultation
            records. The system allows students to conveniently
            request counseling appointments while helping counselors
            manage appointments and consultation records efficiently.
        </p>

    </div>

</section>


<!-- =========================
     HOW IT WORKS
========================= -->

<section class="how-section" id="how-it-works">

    <div class="section-title">

        <h2>How It Works</h2>

        <p>
            Get started with counseling in just a few simple steps.
        </p>

    </div>


    <div class="steps">


        <div class="step">

            <div class="step-number">
                1
            </div>

            <h3>
                Register
            </h3>

            <p>
                Create your student account
                to access the system.
            </p>

        </div>


        <div class="step">

            <div class="step-number">
                2
            </div>

            <h3>
                Login
            </h3>

            <p>
                Login to your account
                using your registered email.
            </p>

        </div>


        <div class="step">

            <div class="step-number">
                3
            </div>

            <h3>
                Book Appointment
            </h3>

            <p>
                Select a counselor and
                schedule your appointment.
            </p>

        </div>


        <div class="step">

            <div class="step-number">
                4
            </div>

            <h3>
                Attend Session
            </h3>

            <p>
                Attend your scheduled
                counseling session.
            </p>

        </div>


    </div>

</section>


<!-- =========================
     CALL TO ACTION
========================= -->

<section class="cta" id="contact">

    <h2>
        Ready to Get Started?
    </h2>

    <p>
        Access UISCare and manage your counseling appointments easily.
    </p>

    <a href="register.php" class="primary-btn">
        Create an Account
    </a>

</section>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <div class="footer-content">

        <div class="footer-logo">

            <img src="images/logo_counseling_system.png"
                 alt="UISCare Counseling System">

        </div>


        <div class="footer-text">

            <p>
                <strong>UISCare Counseling System</strong>
            </p>

            <p>
                Counseling Booking System
            </p>

            <p>
                For students and counselors
            </p>

        </div>

    </div>


    <div class="copyright">

        © <?php echo date("Y"); ?> UISCare Counseling System.
        All Rights Reserved.

    </div>

</footer>


</body>
</html>