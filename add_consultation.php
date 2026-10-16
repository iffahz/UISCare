<?php

require_once "counselor_check.php";
require_once "db.php";

$counselor_id = $_SESSION['user_id'];
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = $_POST['student_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    if (
        empty($student_id) ||
        empty($appointment_date) ||
        empty($appointment_time) ||
        empty($reason)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } else {

        $date_timestamp = strtotime($appointment_date);

        if ($date_timestamp === false) {

            $message = "Invalid appointment date.";
            $message_type = "error";

        } else {

            $day_of_week = date('N', $date_timestamp);

            if ($day_of_week >= 6) {

                $message =
                    "Appointments are not available on Saturday and Sunday.";

                $message_type = "error";

            } else {

                $appointment_time =
                    date(
                        'H:i:s',
                        strtotime($appointment_time)
                    );

                $allowed_slots = [];


                // Monday - Thursday
                if ($day_of_week >= 1 && $day_of_week <= 4) {

                    $allowed_slots = [

                        '08:30:00',
                        '09:00:00',
                        '09:30:00',
                        '10:00:00',
                        '10:30:00',
                        '11:00:00',
                        '11:30:00',
                        '12:00:00',

                        '14:00:00',
                        '14:30:00',
                        '15:00:00',
                        '15:30:00',
                        '16:00:00'

                    ];

                }


                // Friday
                elseif ($day_of_week == 5) {

                    $allowed_slots = [

                        '08:30:00',
                        '09:00:00',
                        '09:30:00',
                        '10:00:00',
                        '10:30:00',
                        '11:00:00',
                        '11:30:00',

                        '15:00:00',
                        '15:30:00',
                        '16:00:00'

                    ];

                }

                if (
                    !in_array(
                        $appointment_time,
                        $allowed_slots
                    )
                ) {

                    $message =
                        "The selected time is outside the counselor's working hours.";

                    $message_type = "error";

                } else {

                    $student_check = $conn->prepare("

                        SELECT id

                        FROM `user`

                        WHERE id = ?

                        AND role = 'student'

                        AND status = 'active'

                    ");

                    $student_check->bind_param(
                        "i",
                        $student_id
                    );

                    $student_check->execute();

                    $student_result =
                        $student_check->get_result();


                    if ($student_result->num_rows === 0) {

                        $message =
                            "Invalid student selected.";

                        $message_type = "error";

                    } else {

                        $check = $conn->prepare("

                            SELECT id

                            FROM appointment

                            WHERE counselor_user_id = ?

                            AND appointment_date = ?

                            AND appointment_time = ?

                            AND status IN ('pending', 'approved')

                        ");

                        $check->bind_param(
                            "iss",
                            $counselor_id,
                            $appointment_date,
                            $appointment_time
                        );

                        $check->execute();

                        $check_result =
                            $check->get_result();


                        if ($check_result->num_rows > 0) {

                            $message =
                                "This time slot is already booked.";

                            $message_type = "error";

                        } else {

                            $insert = $conn->prepare("

                                INSERT INTO appointment
                                (
                                    student_user_id,
                                    counselor_user_id,
                                    appointment_date,
                                    appointment_time,
                                    reason,
                                    status
                                )

                                VALUES (?, ?, ?, ?, ?, 'approved')

                            ");

                            $insert->bind_param(
                                "iisss",
                                $student_id,
                                $counselor_id,
                                $appointment_date,
                                $appointment_time,
                                $reason
                            );


                            if ($insert->execute()) {

                                header(
                                    "Location: counselor_appointments.php"
                                );

                                exit();

                            } else {

                                $message =
                                    "Failed to book appointment.";

                                $message_type = "error";

                            }

                            $insert->close();

                        }

                        $check->close();

                    }

                    $student_check->close();

                }

            }

        }

    }

}

$students_sql = "

    SELECT
        id,
        student_id,
        fullname

    FROM `user`

    WHERE role = 'student'

    AND status = 'active'

    ORDER BY fullname ASC

";

$students_stmt =
    $conn->prepare($students_sql);

$students_stmt->execute();

$students =
    $students_stmt->get_result();

$existing_sql = "

    SELECT
        appointment_date,
        appointment_time

    FROM appointment

    WHERE counselor_user_id = ?

    AND status IN ('pending', 'approved')

";

$existing_stmt =
    $conn->prepare($existing_sql);

$existing_stmt->bind_param(
    "i",
    $counselor_id
);

$existing_stmt->execute();

$existing_result =
    $existing_stmt->get_result();


$booked_slots = [];

while (
    $row =
    $existing_result->fetch_assoc()
) {

    $booked_slots[] = [

        'date' =>
            $row['appointment_date'],

        'time' =>
            substr(
                $row['appointment_time'],
                0,
                5
            )

    ];

}

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Book Appointment</title>

    <style>

        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #dbeafe;
            color: #333;
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
        }    
        .menu a:hover,
        .menu a.active {
            background-color: #1e88e5;
        }
        .navbar > .logout {
            width: 180px;
            margin-top: auto;
            margin-left: auto;
            margin-right: auto;
            padding: 12px 18px;
            text-align: center;
            background-color: #e53935;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .navbar > .logout:hover {
            background-color: #c62828;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 750px;
        }
        h1 {
            color: #0f4c81;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .description {
            color: #666;
            margin-bottom: 25px;
        }
        .message {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .message.error {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        label {
            display: block;
            margin-top: 18px;
            margin-bottom: 7px;
            font-weight: bold;
            color: #0f4c81;
        }
        input,
        select,
        textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #1e88e5;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .time-info {
            margin-top: 8px;
            padding: 12px;
            background-color: #eff6ff;
            color: #0f4c81;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.6;
        }
        .button-container {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }
        button {
            background-color: #1e88e5;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #0f4c81;
        }
        .back-btn {
            display: inline-block;
            padding: 11px 20px;
            background-color: #64748b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .back-btn:hover {
            background-color: #475569;
        }

        @media (max-width: 600px) {

            .main-content {

                margin-left: 0;

                padding: 15px;

            }

        }

    </style>

</head>

<body>

<?php include "counselor_sidebar.php"; ?>


<div class="main-content">


    <div class="form-container">


        <h1>
            Book Appointment
        </h1>


        <p class="description">
            Book a new appointment for a student.
        </p>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            id="appointmentForm"
        >

            <label>
                Student
            </label>

            <select
                name="student_id"
                required
            >

                <option value="">
                    -- Select Student --
                </option>


                <?php while (
                    $student =
                    $students->fetch_assoc()
                ): ?>

                    <option
                        value="<?php echo $student['id']; ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $student['fullname']
                        );
                        ?>

                        -

                        <?php
                        echo htmlspecialchars(
                            $student['student_id']
                        );
                        ?>

                    </option>

                <?php endwhile; ?>

            </select>

            <label>
                Appointment Date
            </label>

            <input
                type="date"
                name="appointment_date"
                id="appointment_date"
                required
            >

            <label>
                Appointment Time
            </label>

            <select
                name="appointment_time"
                id="appointment_time"
                required
            >

                <option value="">
                    -- Select Date First --
                </option>

            </select>


            <div class="time-info">

                <strong>Working Hours:</strong><br>

                Monday - Thursday:
                8:30 AM - 12:30 PM
                &
                2:00 PM - 4:30 PM

                <br>

                Friday:
                8:30 AM - 12:00 PM
                &
                3:00 PM - 4:30 PM

                <br>

                Saturday & Sunday:
                No appointments

            </div>

            <label>
                Appointment Reason
            </label>

            <textarea
                name="reason"
                placeholder="Enter appointment reason..."
                required
            ></textarea>

            <div class="button-container">

                <button
                    type="submit"
                >
                    Book Appointment
                </button>


                <a
                    href="counselor_appointments.php"
                    class="back-btn"
                >
                    Back
                </a>

            </div>

        </form>

    </div>

</div>

<script>

const bookedSlots =
    <?php
    echo json_encode(
        $booked_slots,
        JSON_UNESCAPED_SLASHES
    );
    ?>;

    
const dateInput =
    document.getElementById(
        'appointment_date'
    );


const timeSelect =
    document.getElementById(
        'appointment_time'
    );

const today =
    new Date();

const year =
    today.getFullYear();

const month =
    String(
        today.getMonth() + 1
    ).padStart(2, '0');

const day =
    String(
        today.getDate()
    ).padStart(2, '0');

dateInput.min =
    year + '-' +
    month + '-' +
    day;


dateInput.addEventListener(
    'change',
    function() {

        const selectedDate =
            this.value;


        timeSelect.innerHTML =
            '';


        if (!selectedDate) {

            const option =
                document.createElement(
                    'option'
                );

            option.value = '';

            option.textContent =
                '-- Select Date First --';

            timeSelect.appendChild(
                option
            );

            return;
        }

        const date =
            new Date(
                selectedDate + 'T00:00:00'
            );

        const dayOfWeek =
            date.getDay();

        if (
            dayOfWeek === 0 ||
            dayOfWeek === 6
        ) {

            const option =
                document.createElement(
                    'option'
                );

            option.value = '';

            option.textContent =
                'No appointments available';

            timeSelect.appendChild(
                option
            );

            return;
        }

        let slots = [];


        // Monday - Thursday
        if (
            dayOfWeek >= 1 &&
            dayOfWeek <= 4
        ) {

            slots = [

                '08:30',
                '09:00',
                '09:30',
                '10:00',
                '10:30',
                '11:00',
                '11:30',
                '12:00',

                '14:00',
                '14:30',
                '15:00',
                '15:30',
                '16:00'

            ];

        }


        // Friday
        else if (
            dayOfWeek === 5
        ) {

            slots = [

                '08:30',
                '09:00',
                '09:30',
                '10:00',
                '10:30',
                '11:00',
                '11:30',

                '15:00',
                '15:30',
                '16:00'

            ];

        }

        const defaultOption =
            document.createElement(
                'option'
            );

        defaultOption.value = '';

        defaultOption.textContent =
            '-- Select Time --';

        timeSelect.appendChild(
            defaultOption
        );

        slots.forEach(
            function(time) {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    time;

                const parts =
                    time.split(':');

                let hour =
                    parseInt(
                        parts[0]
                    );

                const minute =
                    parts[1];

                const period =
                    hour >= 12
                        ? 'PM'
                        : 'AM';

                let displayHour =
                    hour % 12;

                if (
                    displayHour === 0
                ) {
                    displayHour = 12;
                }

                const displayTime =
                    displayHour +
                    ':' +
                    minute +
                    ' ' +
                    period;

                const isBooked =
                    bookedSlots.some(
                        function(slot) {

                            return (
                                slot.date ===
                                selectedDate
                                &&
                                slot.time ===
                                time
                            );

                        }
                    );

                if (isBooked) {
                    option.textContent =
                        displayTime +
                        ' - BOOKED';
                    option.disabled =
                        true;
                } else {
                    option.textContent =
                        displayTime;
                }
                timeSelect.appendChild(
                    option
                );

            }
        );

    }
);

</script>

</body>

</html>
