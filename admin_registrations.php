<?php
include "auth_admin.php";

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "websecproject1"
);

if (!$conn) {
    die("Database connection failed.");
}

$registrations = mysqli_query(
    $conn,
    "SELECT
        r.registration_id,
        r.registration_date,

        s.fullname,
        s.matric_no,

        e.event_name,
        e.event_date,
        e.event_time_from,
        e.event_time_to,
        e.venue

     FROM registrations r

     INNER JOIN students s
        ON r.student_id = s.student_id

     INNER JOIN events e
        ON r.event_id = e.event_id

     ORDER BY r.registration_date DESC"
);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Event Registrations</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #E3F2FD;
            color: #333333;
        }

        .navbar {
            background: #1565C0;
            color: white;
            padding: 20px 45px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .nav-menu {
            display: flex;
            gap: 22px;
            align-items: center;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .logout-form {
            margin: 0;
        }

        .logout-btn {
            background: #0D47A1;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        .logout-btn:hover {
            background: #08306b;
        }

        .container {
            padding: 40px;
        }

        .card {
            background: white;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        }

        h1 {
            color: #1565C0;
            margin-top: 0;
        }

        .small-text {
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th {
            background: #1565C0;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #F5FAFF;
        }

        .badge {
            background: #E3F2FD;
            color: #0D47A1;
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>

<body>

<div class="navbar">

    <h2>Event Management System</h2>

    <div class="nav-menu">

        <a href="dashboard_admin.php">
            Dashboard
        </a>

        <a href="admin_events.php">
            Manage Events
        </a>

        <a href="admin_students.php">
            Students
        </a>

        <a href="admin_registrations.php">
            Registrations
        </a>

        <form
            action="logout.php"
            method="POST"
            class="logout-form">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

            <button
                type="submit"
                class="logout-btn">
                Logout
            </button>

        </form>

    </div>

</div>

<div class="container">

    <div class="card">

        <h1>Event Registrations</h1>

        <p class="small-text">
            View all student registrations for events.
        </p>

        <?php if ($registrations && mysqli_num_rows($registrations) > 0) { ?>

            <table>

                <tr>
                    <th>Registration ID</th>
                    <th>Student Name</th>
                    <th>Matric Number</th>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Venue</th>
                    <th>Registered On</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($registrations)) { ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($row['registration_id']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['fullname']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['matric_no']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['event_name']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['event_date']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                date(
                                    "h:i A",
                                    strtotime($row['event_time_from'])
                                )
                            ); ?>

                            -

                            <?= htmlspecialchars(
                                date(
                                    "h:i A",
                                    strtotime($row['event_time_to'])
                                )
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['venue']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['registration_date']); ?>
                        </td>

                    </tr>

                <?php } ?>

            </table>

        <?php } else { ?>

            <p>No registrations found.</p>

        <?php } ?>

    </div>

</div>

</body>
</html>