<?php
include "auth_admin.php";

$conn = mysqli_connect("localhost", "root", "", "websecproject1");

if (!$conn) {
    die("Database connection failed.");
}

$students = mysqli_query(
    $conn,
    "SELECT 
        s.student_id,
        s.fullname,
        s.matric_no,
        s.email,
        u.username,
        u.role
     FROM students s
     INNER JOIN users u
        ON s.user_id = u.user_id
     ORDER BY s.student_id DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student List</title>

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
        <a href="dashboard_admin.php">Dashboard</a>
        <a href="admin_events.php">Manage Events</a>
        <a href="admin_students.php">Students</a>
        <a href="admin_registrations.php">Registrations</a>

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

    <div class="card">
        <h1>Registered Students</h1>
        <p class="small-text">
            This page displays all student accounts registered in the Event Management System.
        </p>

        <?php if ($students && mysqli_num_rows($students) > 0) { ?>

            <table>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Matric Number</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                </tr>

                <?php while ($student = mysqli_fetch_assoc($students)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($student['student_id']); ?></td>
                        <td><?= htmlspecialchars($student['fullname']); ?></td>
                        <td><?= htmlspecialchars($student['matric_no']); ?></td>
                        <td><?= htmlspecialchars($student['email']); ?></td>
                        <td><?= htmlspecialchars($student['username']); ?></td>
                        <td>
                            <span class="badge">
                                <?= htmlspecialchars($student['role']); ?>
                            </span>
                        </td>
                    </tr>
                <?php } ?>

            </table>

        <?php } else { ?>

            <p>No students found.</p>

        <?php } ?>
    </div>

</div>

</body>
</html>