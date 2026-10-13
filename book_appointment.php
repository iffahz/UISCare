<?php

require_once "student_check.php";
require_once "db.php";

$student_id = $_SESSION['user_id'];
$message = "";

// =========================================
// GET COUNSELORS
// =========================================

$counselors = $conn->query("
    SELECT 
        id,
        fullname,
        email,
        phone,
        profile_picture
    FROM `user`
    WHERE role = 'counselor'
    AND status = 'active'
    ORDER BY fullname ASC
");


// =========================================
// BOOK APPOINTMENT
// =========================================

if (isset($_POST['book'])) {

    $counselor_id = (int) ($_POST['counselor_id'] ?? 0);
    $date = $_POST['appointment_date'] ?? "";
    $time = $_POST['appointment_time'] ?? "";
    $reason = trim($_POST['reason'] ?? "");


    // =========================================
    // VALIDATE EMPTY FIELDS
    // =========================================

    if (
        empty($counselor_id) ||
        empty($date) ||
        empty($time) ||
        empty($reason)
    ) {

        $message = "Please fill in all fields.";

    } else {

        // =========================================
        // VALIDATE DATE
        // =========================================

        $date_timestamp = strtotime($date);

        if ($date_timestamp === false) {

            $message = "Invalid appointment date.";

        } else {

            // 1 = Monday
            // 2 = Tuesday
            // 3 = Wednesday
            // 4 = Thursday
            // 5 = Friday
            // 6 = Saturday
            // 7 = Sunday

            $day = date('N', $date_timestamp);

            $validTime = false;


            // =========================================
            // MONDAY - THURSDAY
            // =========================================

            if ($day >= 1 && $day <= 4) {

                // Morning
                if (
                    $time >= "08:30:00" &&
                    $time <= "13:00:00"
                ) {

                    $validTime = true;

                }

                // Afternoon
                elseif (
                    $time >= "14:00:00" &&
                    $time <= "16:30:00"
                ) {

                    $validTime = true;

                }

            }


            // =========================================
            // FRIDAY
            // =========================================

            elseif ($day == 5) {

                // Morning
                if (
                    $time >= "08:30:00" &&
                    $time <= "12:00:00"
                ) {

                    $validTime = true;

                }

                // Afternoon
                elseif (
                    $time >= "15:00:00" &&
                    $time <= "16:30:00"
                ) {

                    $validTime = true;

                }

            }


            // =========================================
            // SATURDAY & SUNDAY
            // =========================================

            else {

                $validTime = false;

            }


            // =========================================
            // CHECK VALID TIME
            // =========================================

            if (!$validTime) {

                $message =
                    "The selected date or time is outside the available booking hours.";

            } else {

                // =========================================
                // CHECK DUPLICATE COUNSELOR TIME
                // =========================================

                $stmt = $conn->prepare("
                    SELECT id
                    FROM appointment
                    WHERE counselor_user_id = ?
                    AND appointment_date = ?
                    AND appointment_time = ?
                    AND status IN ('pending', 'approved', 'completed')
                ");

                $stmt->bind_param(
                    "iss",
                    $counselor_id,
                    $date,
                    $time
                );

                $stmt->execute();

                $check = $stmt->get_result();


                if ($check->num_rows > 0) {

                    $message =
                        "This time slot is already booked.";

                } else {

                    // =========================================
                    // INSERT APPOINTMENT
                    // =========================================

                    $stmt = $conn->prepare("
                        INSERT INTO appointment
                        (
                            student_user_id,
                            counselor_user_id,
                            appointment_date,
                            appointment_time,
                            reason,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?, 'pending')
                    ");

                    $stmt->bind_param(
                        "iisss",
                        $student_id,
                        $counselor_id,
                        $date,
                        $time,
                        $reason
                    );


                    if ($stmt->execute()) {

                        header("Location: my_appointments.php");
                        exit();

                    } else {

                        $message =
                            "Failed to book appointment.";

                    }

                }

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Book Appointment</title>


<style>

/* =========================================
   RESET
========================================= */

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;

    font-family: Arial, sans-serif;
}


/* =========================================
   BODY
========================================= */

body {
    background: #dbeafe;
}


/* =====================================================
   SIDEBAR
===================================================== */

.navbar {
    position: fixed;
    left: 0;
    top: 0;

    width: 220px;
    height: 100vh;

    background: #0f4c81;

    padding: 25px 0;

    display: flex;
    flex-direction: column;
    align-items: center;

    z-index: 1000;
}


/* LOGO */

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


/* MENU */

.menu {
    width: 100%;

    display: flex;
    flex-direction: column;

    gap: 12px;

    align-items: center;
}

.menu a {
    width: 170px;

    padding: 12px 18px;

    color: white;

    text-decoration: none;

    border-radius: 8px;

    transition: 0.2s;
}


/* ACTIVE + HOVER */

.menu a:hover,
.menu a.active {
    background: #1e88e5;
}


/* LOGOUT */

.navbar > .logout {
    width: 170px;
    margin-top: auto;
    margin-left: auto;
    margin-right: auto;
    padding: 12px;
    text-align: center;
    background: #e53935;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: 0.2s;
}

.navbar > .logout:hover {
    background: #c62828;
}

/* MAIN CONTENT */

.main {
    margin-left: 250px;
    padding: 30px;
    min-height: 100vh;
}


/* =========================================
   PAGE HEADER
========================================= */

.page-header {

    background: white;

    padding: 25px 30px;

    border-radius: 12px;

    margin-bottom: 25px;

    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);

}

.page-header h1 {

    margin: 0 0 8px;

    color: #0f4c81;

    font-size: 28px;

}

.page-header p {

    margin: 0;

    color: #666;

    font-size: 15px;

}


/* =========================================
   MESSAGE
========================================= */

.message {

    background: #ffebee;

    color: #c62828;

    padding: 14px 18px;

    border-radius: 8px;

    margin-bottom: 20px;

    border-left: 5px solid #e53935;

}


/* =========================================
   COUNSELOR GRID
========================================= */

.counselor-grid {

    display: grid;

    grid-template-columns: repeat(
        auto-fit,
        minmax(270px, 1fr)
    );

    gap: 25px;

}


/* =========================================
   COUNSELOR CARD
========================================= */

.counselor-card {

    background: white;

    border-radius: 12px;

    padding: 25px;

    text-align: left;

    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);

    transition: 0.25s;

}

.counselor-card:hover {

    transform: translateY(-5px);

    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);

}


/* =========================================
   PROFILE PICTURE
========================================= */

.profile-picture {

    width: 150px;

    height: 150px;

    border-radius: 10px;

    object-fit: contain;

    display: block;

    margin: 0 auto 20px;

    border: 5px solid #e3f2fd;

    background: #e3f2fd;

}


/* =========================================
   COUNSELOR NAME
========================================= */

.counselor-card h2 {

    margin: 0 0 18px;

    text-align: center;

    color: #0f4c81;

    font-size: 20px;

}


/* =========================================
   COUNSELOR INFORMATION
========================================= */

.info {

    margin-bottom: 10px;

    color: #555;

    font-size: 14px;

    line-height: 1.5;

    word-break: break-word;

}

.info strong {

    color: #0f4c81;

}


/* =========================================
   APPOINTMENT BUTTON
========================================= */

.appointment-btn {

    width: 100%;

    margin-top: 15px;

    padding: 13px;

    border: none;

    border-radius: 7px;

    background: #1e88e5;

    color: white;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.2s;

}

.appointment-btn:hover {

    background: #0f4c81;

}


/* =========================================
   MODAL
========================================= */

.modal {

    display: none;

    position: fixed;

    z-index: 2000;

    left: 0;
    top: 0;

    width: 100%;
    height: 100%;

    background: rgba(0, 0, 0, 0.55);

    align-items: center;

    justify-content: center;

    padding: 20px;

}


/* =========================================
   MODAL CONTENT
========================================= */

.modal-content {

    background: white;

    width: 100%;

    max-width: 650px;

    max-height: 90vh;

    overflow-y: auto;

    border-radius: 14px;

    padding: 30px;

    position: relative;

    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);

}


/* =========================================
   CLOSE BUTTON
========================================= */

.close {

    position: absolute;

    right: 22px;
    top: 15px;

    font-size: 28px;

    font-weight: bold;

    color: #777;

    cursor: pointer;

}

.close:hover {

    color: #e53935;

}


/* =========================================
   MODAL TITLE
========================================= */

.modal-content h1 {

    margin: 0 0 25px;

    color: #0f4c81;

    font-size: 25px;

}


/* =========================================
   SELECTED COUNSELOR
========================================= */

.selected-counselor {

    display: flex;

    align-items: center;

    gap: 18px;

    background: #f0f7ff;

    padding: 15px;

    border-radius: 10px;

    margin-bottom: 25px;

}

.selected-counselor img {

    width: 80px;

    height: 80px;

    object-fit: cover;

    border-radius: 10px;

    flex-shrink: 0;

}

.selected-info {

    min-width: 0;

}

.selected-info h2 {

    margin: 0 0 7px;

    color: #0f4c81;

    font-size: 18px;

    word-break: break-word;

}

.selected-info p {

    margin: 3px 0;

    color: #666;

    font-size: 13px;

    word-break: break-word;

}


/* =========================================
   FORM
========================================= */

.form-group {

    margin-bottom: 18px;

}

.form-group label {

    display: block;

    margin-bottom: 7px;

    font-weight: bold;

    color: #0f4c81;

}

.form-group input,
.form-group select {

    width: 100%;

    padding: 12px;

    border: 1px solid #ccc;

    border-radius: 7px;

    font-size: 14px;

    background: white;

}

.form-group input:focus,
.form-group select:focus {

    outline: none;

    border-color: #1e88e5;

    box-shadow:
        0 0 0 2px
        rgba(30, 136, 229, 0.1);

}


/* =========================================
   TIME INFORMATION
========================================= */

.time-info {

    margin: 5px 0 15px;

    color: #666;

    font-size: 14px;

    line-height: 1.5;

}


/* =========================================
   TIME SLOTS
========================================= */

.time-slots {

    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 10px;

    margin-top: 10px;

}

.time-slot {

    padding: 12px 8px;

    border: 2px solid #d6e4f0;

    background: #ffffff;

    color: #333;

    border-radius: 8px;

    cursor: pointer;

    font-size: 14px;

    font-weight: 500;

    transition: 0.2s;

}

.time-slot:hover {

    border-color: #1e88e5;

    background: #e3f2fd;

}

.time-slot.selected {

    background: #1e88e5;

    color: white;

    border-color: #1e88e5;

}


/* =========================================
   FORM BUTTONS
========================================= */

.form-buttons {

    display: flex;

    gap: 10px;

    margin-top: 25px;

}

.book-btn {

    flex: 1;

    padding: 13px;

    border: none;

    border-radius: 7px;

    background: #1e88e5;

    color: white;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

    transition: 0.2s;

}

.book-btn:hover {

    background: #0f4c81;

}


.cancel-btn {

    padding: 13px 25px;

    border: none;

    border-radius: 7px;

    background: #607d8b;

    color: white;

    font-size: 15px;

    cursor: pointer;

    transition: 0.2s;

}

.cancel-btn:hover {

    background: #455a64;

}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 900px) {

    .navbar {

        position: relative;

        width: 100%;

        height: auto;

        min-height: auto;

        padding: 20px 15px;

    }


    .logo {

        margin-bottom: 20px;

    }


    .menu a {

        text-align: center;

    }


    .navbar > .logout {

        margin-top: 15px;

        text-align: center;

    }


    .main {

        margin-left: 0;

        width: 100%;

        padding: 20px;

    }


    .counselor-grid {

        grid-template-columns: 1fr;

    }

}


/* =========================================
   SMALL SCREEN
========================================= */

@media (max-width: 500px) {

    .main {

        padding: 15px;

    }


    .page-header {

        padding: 20px;

    }


    .page-header h1 {

        font-size: 23px;

    }


    .counselor-card {

        padding: 20px;

    }


    .profile-picture {

        width: 130px;

        height: 130px;

    }


    .modal {

        padding: 10px;

    }


    .modal-content {

        padding: 22px;

    }


    .selected-counselor {

        gap: 12px;

    }


    .selected-counselor img {

        width: 65px;

        height: 65px;

        border-radius: 8px;

    }


    .time-slots {

        grid-template-columns: repeat(2, 1fr);

    }


    .form-buttons {

        flex-direction: column;

    }


    .cancel-btn {

        width: 100%;

    }

}

</style>

</head>


<body>


<!-- =========================================
     STUDENT SIDEBAR
========================================= -->

<?php include "student_sidebar.php"; ?>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main">


    <!-- =========================================
         PAGE HEADER
    ========================================= -->

    <div class="page-header">

        <h1>
            Book Counseling Appointment
        </h1>

        <p>
            Choose a counselor and make an appointment.
        </p>

    </div>


    <!-- =========================================
         MESSAGE
    ========================================= -->

    <?php if ($message): ?>

        <div class="message">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- =========================================
         COUNSELOR GRID
    ========================================= -->

    <div class="counselor-grid">


        <?php while ($counselor = $counselors->fetch_assoc()): ?>


            <?php

            // =========================================
            // PROFILE PICTURE PATH
            // =========================================

            $imagePath = "uploads/profile/default.png";

            if (!empty($counselor['profile_picture'])) {

                $fileName =
                    basename(
                        $counselor['profile_picture']
                    );

                $fullPath =
                    __DIR__ .
                    "/uploads/profile/" .
                    $fileName;

                if (file_exists($fullPath)) {

                    $imagePath =
                        "uploads/profile/" .
                        $fileName;

                }

            }

            ?>


            <!-- COUNSELOR CARD -->

            <div class="counselor-card">


                <!-- PROFILE PICTURE -->

                <img
                    src="<?php
                        echo htmlspecialchars($imagePath);
                    ?>"
                    alt="Counselor"
                    class="profile-picture"
                >


                <!-- NAME -->

                <h2>

                    <?php
                    echo htmlspecialchars(
                        $counselor['fullname']
                    );
                    ?>

                </h2>


                <!-- EMAIL -->

                <div class="info">

                    <strong>
                        Email:
                    </strong>

                    <br>

                    <?php

                    echo !empty($counselor['email'])
                        ? htmlspecialchars(
                            $counselor['email']
                        )
                        : "Not available";

                    ?>

                </div>


                <!-- PHONE -->

                <div class="info">

                    <strong>
                        Phone:
                    </strong>

                    <br>

                    <?php

                    echo !empty($counselor['phone'])
                        ? htmlspecialchars(
                            $counselor['phone']
                        )
                        : "Not available";

                    ?>

                </div>


                <!-- APPOINTMENT BUTTON -->

                <button
                    type="button"
                    class="appointment-btn"
                    onclick="openAppointment(
                        '<?php
                        echo htmlspecialchars(
                            $counselor['id'],
                            ENT_QUOTES
                        );
                        ?>',
                        '<?php
                        echo htmlspecialchars(
                            $counselor['fullname'],
                            ENT_QUOTES
                        );
                        ?>',
                        '<?php
                        echo htmlspecialchars(
                            $counselor['email'] ?? '',
                            ENT_QUOTES
                        );
                        ?>',
                        '<?php
                        echo htmlspecialchars(
                            $counselor['phone'] ?? '',
                            ENT_QUOTES
                        );
                        ?>',
                        '<?php
                        echo htmlspecialchars(
                            $imagePath,
                            ENT_QUOTES
                        );
                        ?>'
                    )"
                >

                    Make an Appointment

                </button>


            </div>


        <?php endwhile; ?>


    </div>


</div>



<!-- =========================================
     APPOINTMENT MODAL
========================================= -->

<div
    id="appointmentModal"
    class="modal"
>


    <div class="modal-content">


        <!-- CLOSE -->

        <span
            class="close"
            onclick="closeAppointment()"
        >

            &times;

        </span>


        <!-- TITLE -->

        <h1>

            Make an Appointment

        </h1>


        <!-- SELECTED COUNSELOR -->

        <div class="selected-counselor">


            <img
                id="modalImage"
                src=""
                alt="Counselor"
            >


            <div class="selected-info">

                <h2 id="modalName">
                    Counselor
                </h2>


                <p id="modalEmail">
                    Email
                </p>


                <p id="modalPhone">
                    Phone
                </p>

            </div>


        </div>


        <!-- =========================================
             BOOKING FORM
        ========================================= -->

        <form method="POST">


            <!-- COUNSELOR ID -->

            <input
                type="hidden"
                name="counselor_id"
                id="modalCounselorId"
            >


            <!-- =========================================
                 APPOINTMENT DATE
            ========================================= -->

            <div class="form-group">

                <label for="appointment_date">

                    Appointment Date

                </label>


                <input
                    type="date"
                    id="appointment_date"
                    name="appointment_date"
                    required
                    min="<?php
                        echo date('Y-m-d');
                    ?>"
                    onchange="loadTimeSlots()"
                >

            </div>


            <!-- =========================================
                 APPOINTMENT TIME
            ========================================= -->

            <div class="form-group">

                <label>

                    Appointment Time

                </label>


                <p
                    id="time-info"
                    class="time-info"
                >

                    Please select a date first.

                </p>


                <div
                    id="time-slots"
                    class="time-slots"
                >

                    <!-- Time buttons appear here -->

                </div>


                <!-- Hidden input -->

                <input
                    type="hidden"
                    name="appointment_time"
                    id="appointment_time"
                    required
                >

            </div>


            <!-- =========================================
                 REASON
            ========================================= -->

            <div class="form-group">

                <label>

                    Appointment Reason

                </label>


                <select
                    name="reason"
                    required
                >

                    <option value="">

                        -- Select Reason --

                    </option>


                    <option value="Akademi">

                        Akademi

                    </option>


                    <option value="Keluarga">

                        Keluarga

                    </option>


                    <option value="Kesihatan">

                        Kesihatan

                    </option>


                    <option value="Kewangan">

                        Kewangan

                    </option>


                    <option value="Sosial">

                        Sosial

                    </option>


                    <option value="Kendiri">

                        Kendiri

                    </option>


                    <option value="Rujukan">

                        Rujukan

                    </option>


                    <option value="Prosiding">

                        Prosiding

                    </option>

                </select>

            </div>


            <!-- =========================================
                 BUTTONS
            ========================================= -->

            <div class="form-buttons">


                <button
                    type="submit"
                    name="book"
                    class="book-btn"
                >

                    Book Appointment

                </button>


                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeAppointment()"
                >

                    Cancel

                </button>


            </div>


        </form>


    </div>


</div>



<script>


// =========================================
// OPEN APPOINTMENT MODAL
// =========================================

function openAppointment(
    id,
    name,
    email,
    phone,
    image
) {

    document.getElementById(
        "modalCounselorId"
    ).value = id;


    document.getElementById(
        "modalName"
    ).textContent = name;


    document.getElementById(
        "modalEmail"
    ).textContent =
        email
        ? email
        : "Email not available";


    document.getElementById(
        "modalPhone"
    ).textContent =
        phone
        ? phone
        : "Phone not available";


    document.getElementById(
        "modalImage"
    ).src = image;


    // Reset date and time

    document.getElementById(
        "appointment_date"
    ).value = "";


    document.getElementById(
        "appointment_time"
    ).value = "";


    document.getElementById(
        "time-slots"
    ).innerHTML = "";


    document.getElementById(
        "time-info"
    ).textContent =
        "Please select a date first.";


    document.getElementById(
        "appointmentModal"
    ).style.display = "flex";

}


// =========================================
// CLOSE APPOINTMENT MODAL
// =========================================

function closeAppointment() {

    document.getElementById(
        "appointmentModal"
    ).style.display = "none";

}


// =========================================
// CLOSE WHEN CLICK OUTSIDE MODAL
// =========================================

window.onclick = function(event) {

    const modal =
        document.getElementById(
            "appointmentModal"
        );

    if (event.target === modal) {

        closeAppointment();

    }

};


// =========================================
// LOAD TIME SLOTS
// =========================================

function loadTimeSlots() {

    const dateInput =
        document.getElementById(
            "appointment_date"
        );

    const slotsContainer =
        document.getElementById(
            "time-slots"
        );

    const timeInput =
        document.getElementById(
            "appointment_time"
        );

    const timeInfo =
        document.getElementById(
            "time-info"
        );


    const selectedDate =
        dateInput.value;


    // Reset

    slotsContainer.innerHTML = "";

    timeInput.value = "";


    if (!selectedDate) {

        timeInfo.textContent =
            "Please select a date first.";

        return;

    }


    // =========================================
    // GET DAY
    // =========================================

    const date =
        new Date(
            selectedDate + "T00:00:00"
        );


    const day =
        date.getDay();


    /*
        0 = Sunday
        1 = Monday
        2 = Tuesday
        3 = Wednesday
        4 = Thursday
        5 = Friday
        6 = Saturday
    */


    // =========================================
    // WEEKEND
    // =========================================

    if (
        day === 0 ||
        day === 6
    ) {

        timeInfo.textContent =
            "Booking is not available on Saturday and Sunday.";

        return;

    }


    // =========================================
    // TIME SLOTS
    // =========================================

    let slots = [];


    // =========================================
    // MONDAY - THURSDAY
    // =========================================

    if (
        day >= 1 &&
        day <= 4
    ) {

        slots = [

            "08:30",
            "09:00",
            "09:30",
            "10:00",
            "10:30",
            "11:00",
            "11:30",
            "12:00",
            "12:30",
            "13:00",

            "14:00",
            "14:30",
            "15:00",
            "15:30",
            "16:00",
            "16:30"

        ];


        timeInfo.textContent =
            "Available time: 8:30 AM – 1:00 PM and 2:00 PM – 4:30 PM";

    }


    // =========================================
    // FRIDAY
    // =========================================

    else if (day === 5) {

        slots = [

            "08:30",
            "09:00",
            "09:30",
            "10:00",
            "10:30",
            "11:00",
            "11:30",
            "12:00",

            "15:00",
            "15:30",
            "16:00",
            "16:30"

        ];


        timeInfo.textContent =
            "Available time: 8:30 AM – 12:00 PM and 3:00 PM – 4:30 PM";

    }


    // =========================================
    // CREATE TIME BUTTONS
    // =========================================

    slots.forEach(
        function(time) {

            const button =
                document.createElement(
                    "button"
                );


            button.type = "button";

            button.className =
                "time-slot";


            button.textContent =
                formatTime(time);


            button.dataset.time =
                time;


            button.onclick =
                function() {


                    // Remove selected
                    // from all buttons

                    document
                        .querySelectorAll(
                            ".time-slot"
                        )
                        .forEach(
                            function(slot) {

                                slot.classList.remove(
                                    "selected"
                                );

                            }
                        );


                    // Select current button

                    button.classList.add(
                        "selected"
                    );


                    // Save time

                    timeInput.value =
                        time + ":00";

                };


            slotsContainer.appendChild(
                button
            );

        }
    );

}


// =========================================
// FORMAT TIME
// =========================================

function formatTime(time) {

    const parts =
        time.split(":");


    let hour =
        parseInt(parts[0]);


    const minute =
        parts[1];


    let period =
        "AM";


    if (hour >= 12) {

        period = "PM";

    }


    let displayHour =
        hour % 12;


    if (displayHour === 0) {

        displayHour = 12;

    }


    return (
        displayHour +
        ":" +
        minute +
        " " +
        period
    );

}

</script>


</body>

</html>