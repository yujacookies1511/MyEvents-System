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

$logs = mysqli_query(
    $conn,
    "SELECT *
     FROM activity_logs
     ORDER BY created_at DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Activity Logs</title>

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

        .card {
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        }

        h1 {
            color: #1565C0;
            margin-top: 0;
        }

        .subtitle {
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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

        .action-add {
            color: #2E7D32;
            font-weight: bold;
        }

        .action-update {
            color: #EF6C00;
            font-weight: bold;
        }

        .action-delete {
            color: #C62828;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            padding: 25px;
            color: #666;
        }

        @media (max-width: 900px) {

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }

            .container {
                padding: 20px;
            }

            table {
                font-size: 14px;
            }
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

        <a href="admin_activity_logs.php">
            Activity Logs
        </a>

        <form
            action="logout.php"
            method="POST"
            class="logout-form">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
            >

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

        <h1>Admin Activity Logs</h1>

        <p class="subtitle">
            Monitor administrator actions for security auditing and accountability.
        </p>

        <?php if ($logs && mysqli_num_rows($logs) > 0) { ?>

            <table>

                <tr>
                    <th>Date / Time</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>

                <?php while ($log = mysqli_fetch_assoc($logs)) { ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($log['created_at']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($log['username']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($log['role']); ?>
                        </td>

                        <td>

                            <?php
                            if ($log['action'] == 'ADD_EVENT') {
                                echo '<span class="action-add">ADD_EVENT</span>';
                            }
                            elseif ($log['action'] == 'UPDATE_EVENT') {
                                echo '<span class="action-update">UPDATE_EVENT</span>';
                            }
                            elseif ($log['action'] == 'DELETE_EVENT') {
                                echo '<span class="action-delete">DELETE_EVENT</span>';
                            }
                            else {
                                echo htmlspecialchars($log['action']);
                            }
                            ?>

                        </td>

                        <td>
                            <?= htmlspecialchars($log['description']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($log['ip_address']); ?>
                        </td>

                    </tr>

                <?php } ?>

            </table>

        <?php } else { ?>

            <div class="empty">
                No activity logs found.
            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>