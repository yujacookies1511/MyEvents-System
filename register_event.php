<?php
include "auth_check.php";

$conn = mysqli_connect("localhost", "root", "", "websecproject1");

if (!$conn) {
    die("Database connection failed.");
}

$message = "";
$error = "";

$user_id = $_SESSION['user_id'];

$getStudent = mysqli_prepare(
    $conn,
    "SELECT student_id FROM students WHERE user_id=?"
);

mysqli_stmt_bind_param($getStudent, "i", $user_id);
mysqli_stmt_execute($getStudent);

$studentResult = mysqli_stmt_get_result($getStudent);
$studentData = mysqli_fetch_assoc($studentResult);

if (!$studentData) {
    die("Student profile not found.");
}

$student_id = $studentData['student_id'];

if (isset($_POST['register_event'])) {
    $event_id = intval($_POST['event_id']);

    $check = mysqli_prepare(
        $conn,
        "SELECT registration_id
         FROM registrations
         WHERE student_id=? AND event_id=?"
    );

    mysqli_stmt_bind_param($check, "ii", $student_id, $event_id);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        $error = "You have already registered for this event.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO registrations (student_id, event_id)
             VALUES (?, ?)"
        );

        mysqli_stmt_bind_param($stmt, "ii", $student_id, $event_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Event registration successful.";
        } else {
            $error = "Registration failed.";
        }
    }
}

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
     WHERE event_date >= CURDATE()
     ORDER BY event_date ASC"
);

$page_title = "Register Event";
include "student_header.php";
?>

<div class="card">
    <h1>Register for Event</h1>

    <?php if ($message != "") { ?>
        <div class="success"><?= htmlspecialchars($message); ?></div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php } ?>

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

                    <td>
                        <form method="POST">
                            <input
                                type="hidden"
                                name="event_id"
                                value="<?= htmlspecialchars($event['event_id']); ?>"
                            >

                            <button
                                type="submit"
                                name="register_event"
                                class="btn">
                                Register
                            </button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>No upcoming events available.</p>
    <?php } ?>
</div>

<?php include "student_footer.php"; ?>