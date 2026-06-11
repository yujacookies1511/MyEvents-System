<?php
include "auth_admin.php";
include "activity_log.php";

$conn = mysqli_connect("localhost", "root", "", "websecproject1");

if (!$conn) {
    die("Database connection failed.");
}

$message = "";
$error = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ADD EVENT */
if (isset($_POST['add_event'])) {

    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid request.");
    }

    $event_name = trim($_POST['event_name']);
    $event_description = trim($_POST['event_description']);
    $event_date = $_POST['event_date'];
    $event_time_from = $_POST['event_time_from'];
    $event_time_to = $_POST['event_time_to'];
    $venue = trim($_POST['venue']);

    if (
        $event_name == "" ||
        $event_description == "" ||
        $event_date == "" ||
        $event_time_from == "" ||
        $event_time_to == "" ||
        $venue == ""
    ) {
        $error = "All fields are required.";
    } elseif ($event_time_to <= $event_time_from) {
        $error = "End time must be later than start time.";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO events
            (event_name, event_description, event_date, event_time_from, event_time_to, venue)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssssss",
            $event_name,
            $event_description,
            $event_date,
            $event_time_from,
            $event_time_to,
            $venue
        );

        if (mysqli_stmt_execute($stmt)) {

            addActivityLog(
                $conn,
                $_SESSION['user_id'],
                $_SESSION['username'],
                $_SESSION['role'],
                "ADD_EVENT",
                "Admin added event: " . $event_name
            );

            $message = "Event added successfully.";

        } else {
            $error = "Failed to add event.";
        }
    }
}

/* UPDATE EVENT */
if (isset($_POST['update_event'])) {

    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid request.");
    }

    $event_id = intval($_POST['event_id']);
    $event_name = trim($_POST['event_name']);
    $event_description = trim($_POST['event_description']);
    $event_date = $_POST['event_date'];
    $event_time_from = $_POST['event_time_from'];
    $event_time_to = $_POST['event_time_to'];
    $venue = trim($_POST['venue']);

    if (
        $event_name == "" ||
        $event_description == "" ||
        $event_date == "" ||
        $event_time_from == "" ||
        $event_time_to == "" ||
        $venue == ""
    ) {
        $error = "All fields are required.";
    } elseif ($event_time_to <= $event_time_from) {
        $error = "End time must be later than start time.";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE events
             SET event_name=?,
                 event_description=?,
                 event_date=?,
                 event_time_from=?,
                 event_time_to=?,
                 venue=?
             WHERE event_id=?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssi",
            $event_name,
            $event_description,
            $event_date,
            $event_time_from,
            $event_time_to,
            $venue,
            $event_id
        );

        if (mysqli_stmt_execute($stmt)) {

            addActivityLog(
                $conn,
                $_SESSION['user_id'],
                $_SESSION['username'],
                $_SESSION['role'],
                "UPDATE_EVENT",
                "Admin updated event ID: " . $event_id . " (" . $event_name . ")"
            );

            $message = "Event updated successfully.";

        } else {
            $error = "Failed to update event.";
        }
    }
}

/* DELETE EVENT */
if (isset($_POST['delete_event'])) {

    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid request.");
    }

    $event_id = intval($_POST['event_id']);

    $getEvent = mysqli_prepare(
        $conn,
        "SELECT event_name
         FROM events
         WHERE event_id=?"
    );

    mysqli_stmt_bind_param($getEvent, "i", $event_id);
    mysqli_stmt_execute($getEvent);

    $eventResult = mysqli_stmt_get_result($getEvent);
    $eventData = mysqli_fetch_assoc($eventResult);

    $deleted_event_name = $eventData ? $eventData['event_name'] : "Unknown Event";

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM events
         WHERE event_id=?"
    );

    mysqli_stmt_bind_param($stmt, "i", $event_id);

    if (mysqli_stmt_execute($stmt)) {

        addActivityLog(
            $conn,
            $_SESSION['user_id'],
            $_SESSION['username'],
            $_SESSION['role'],
            "DELETE_EVENT",
            "Admin deleted event ID: " . $event_id . " (" . $deleted_event_name . ")"
        );

        $message = "Event deleted successfully.";

    } else {
        $error = "Failed to delete event.";
    }
}

/* EDIT MODE */
$edit_event = null;

if (isset($_GET['edit'])) {

    $event_id = intval($_GET['edit']);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT event_id,
                event_name,
                event_description,
                event_date,
                event_time_from,
                event_time_to,
                venue
         FROM events
         WHERE event_id=?"
    );

    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $edit_event = mysqli_fetch_assoc($result);
}

/* EVENT LIST */
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
     ORDER BY event_date ASC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Events</title>

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
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        h1, h2 {
            color: #1565C0;
            margin-top: 0;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .time-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn {
            background: #1565C0;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #0D47A1;
        }

        .btn-danger {
            background: #c62828;
        }

        .btn-danger:hover {
            background: #8e0000;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-weight: bold;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
            vertical-align: top;
        }

        tr:hover {
            background: #F5FAFF;
        }

        .action-row {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .delete-form {
            margin: 0;
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

            .time-row {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 20px;
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

    <div class="card">
        <h1><?= $edit_event ? "Edit Event" : "Add Event"; ?></h1>

        <?php if ($message != "") { ?>
            <div class="success"><?= htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if ($error != "") { ?>
            <div class="error"><?= htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
            >

            <?php if ($edit_event) { ?>
                <input
                    type="hidden"
                    name="event_id"
                    value="<?= htmlspecialchars($edit_event['event_id']); ?>"
                >
            <?php } ?>

            <div class="input-group">
                <label>Event Name</label>
                <input
                    type="text"
                    name="event_name"
                    value="<?= htmlspecialchars($edit_event['event_name'] ?? ''); ?>"
                    required>
            </div>

            <div class="input-group">
                <label>Event Description</label>
                <textarea name="event_description" required><?= htmlspecialchars($edit_event['event_description'] ?? ''); ?></textarea>
            </div>

            <div class="input-group">
                <label>Event Date</label>
                <input
                    type="date"
                    name="event_date"
                    value="<?= htmlspecialchars($edit_event['event_date'] ?? ''); ?>"
                    required>
            </div>

            <div class="time-row">
                <div class="input-group">
                    <label>Time From</label>
                    <input
                        type="time"
                        name="event_time_from"
                        value="<?= htmlspecialchars($edit_event['event_time_from'] ?? ''); ?>"
                        required>
                </div>

                <div class="input-group">
                    <label>Time To</label>
                    <input
                        type="time"
                        name="event_time_to"
                        value="<?= htmlspecialchars($edit_event['event_time_to'] ?? ''); ?>"
                        required>
                </div>
            </div>

            <div class="input-group">
                <label>Venue</label>
                <input
                    type="text"
                    name="venue"
                    value="<?= htmlspecialchars($edit_event['venue'] ?? ''); ?>"
                    required>
            </div>

            <?php if ($edit_event) { ?>
                <button type="submit" name="update_event" class="btn">
                    Update Event
                </button>

                <a href="admin_events.php" class="btn">Cancel</a>
            <?php } else { ?>
                <button type="submit" name="add_event" class="btn">
                    Add Event
                </button>
            <?php } ?>

        </form>
    </div>

    <div class="card">
        <h2>Event List</h2>

        <?php if ($events && mysqli_num_rows($events) > 0) { ?>
            <table>
                <tr>
                    <th>Event Name</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Venue</th>
                    <th>Action</th>
                </tr>

                <?php while ($event = mysqli_fetch_assoc($events)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($event['event_name']); ?></td>

                        <td><?= htmlspecialchars($event['event_description']); ?></td>

                        <td><?= htmlspecialchars($event['event_date']); ?></td>

                        <td>
                            <?= htmlspecialchars(date("h:i A", strtotime($event['event_time_from']))); ?>
                            -
                            <?= htmlspecialchars(date("h:i A", strtotime($event['event_time_to']))); ?>
                        </td>

                        <td><?= htmlspecialchars($event['venue']); ?></td>

                        <td>
                            <div class="action-row">
                                <a
                                    href="admin_events.php?edit=<?= htmlspecialchars($event['event_id']); ?>"
                                    class="btn">
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    class="delete-form"
                                    onsubmit="return confirm('Are you sure you want to delete this event?');">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="event_id"
                                        value="<?= htmlspecialchars($event['event_id']); ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_event"
                                        class="btn btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <p>No events found.</p>
        <?php } ?>
    </div>

</div>

</body>
</html>
