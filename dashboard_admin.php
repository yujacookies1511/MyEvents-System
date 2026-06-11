<?php
include "auth_admin.php";

$conn = mysqli_connect("localhost", "root", "", "websecproject1");

if (!$conn) {
    die("Database connection failed.");
}

$total_events = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM events")
)['total'];

$total_students = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM students")
)['total'];

$total_registrations = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM registrations")
)['total'];

$events = mysqli_query(
    $conn,
    "SELECT event_id,
            event_name,
            event_description,
            event_date,
            event_time_from,
            event_time_to,
            venue
     FROM events
     ORDER BY event_date DESC
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

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
            color: white;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 22px;
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

        .hero-card {
            background: white;
            padding: 35px;
            border-radius: 18px;
            margin-bottom: 25px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        }

        .hero-card h1 {
            margin-top: 0;
            color: #1565C0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        }

        .stat-card h3 {
            color: #0D47A1;
            margin-top: 0;
        }

        .stat-number {
            font-size: 38px;
            font-weight: bold;
            color: #1565C0;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .card h2 {
            color: #1565C0;
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        th {
            background: #1565C0;
            color: white;
            padding: 13px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }

        tr:hover {
            background: #F5FAFF;
        }

        .description {
            max-width: 420px;
            color: #555;
        }

        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn {
            background: #1565C0;
            color: white;
            padding: 13px 20px;
            text-decoration: none;
            border-radius: 10px;
            display: inline-block;
            font-weight: bold;
        }

        .btn:hover {
            background: #0D47A1;
        }

        .small-text {
            color: #666;
            font-size: 14px;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

<div class="navbar">
    <h2>Event Management System</h2>

    <div class="nav-menu">
        <a href="dashboard_admin.php">Dashboard</a>
        <a href="admin_events.php">Manage Events</a>
        <a href="admin_students.php">Students</a>
        <a href="admin_registrations.php">Registrations</a>
        <a href="admin_activity_logs.php">Activity Logs</a>

        <form action="logout.php" method="POST" class="logout-form">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
            >
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</div>

<div class="container">

    <div class="hero-card">
        <h1>Welcome Admin, <?= htmlspecialchars($_SESSION['username']); ?></h1>
        <p>Manage events, students, and registrations in the Event Management System.</p>
        <p class="small-text">Role: <?= htmlspecialchars($_SESSION['role']); ?></p>
    </div>

    <div class="grid">
        <div class="stat-card">
            <h3>Total Events</h3>
            <div class="stat-number"><?= htmlspecialchars($total_events); ?></div>
        </div>

        <div class="stat-card">
            <h3>Total Students</h3>
            <div class="stat-number"><?= htmlspecialchars($total_students); ?></div>
        </div>

        <div class="stat-card">
            <h3>Total Registrations</h3>
            <div class="stat-number"><?= htmlspecialchars($total_registrations); ?></div>
        </div>
    </div>

    <div class="card">
        <h2>Latest Events</h2>

        <?php if ($events && mysqli_num_rows($events) > 0) { ?>
            <table>
                <tr>
                    <th>Event Name</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Venue</th>
                </tr>

                <?php while ($event = mysqli_fetch_assoc($events)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($event['event_name']); ?></td>

                        <td class="description">
                            <?= htmlspecialchars($event['event_description']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event['event_date']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(date("h:i A", strtotime($event['event_time_from']))); ?>
                            -
                            <?= htmlspecialchars(date("h:i A", strtotime($event['event_time_to']))); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($event['venue']); ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <p>No events found.</p>
        <?php } ?>
    </div>

    <div class="card">
        <h2>Quick Actions</h2>

        <div class="quick-actions">
            <a href="admin_events.php" class="btn">Manage Events</a>
            <a href="admin_students.php" class="btn">View Students</a>
            <a href="admin_registrations.php" class="btn">View Registrations</a>
        </div>
    </div>

</div>

</body>
</html>
