<?php
include "auth_check.php";

$conn = mysqli_connect("localhost", "root", "", "websecproject1");

if (!$conn) {
    die("Database connection failed.");
}

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

$stmt = mysqli_prepare(
    $conn,
    "SELECT r.registration_id,
            r.registration_date,
            e.event_name,
            e.event_description,
            e.event_date,
            e.event_time_from,
            e.event_time_to,
            e.venue
     FROM registrations r
     INNER JOIN events e
        ON r.event_id = e.event_id
     WHERE r.student_id = ?
     ORDER BY r.registration_date DESC"
);

mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$registrations = mysqli_stmt_get_result($stmt);

$page_title = "My Registrations";
include "student_header.php";
?>

<div class="card">
    <h1>My Registered Events</h1>

    <?php if ($registrations && mysqli_num_rows($registrations) > 0) { ?>
        <table>
            <tr>
                <th>Event Name</th>
                <th>Description</th>
                <th>Event Date</th>
                <th>Time</th>
                <th>Venue</th>
                <th>Registered On</th>
                <th>Status</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($registrations)) { ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($row['event_name']); ?>
                    </td>

                    <td class="description">
                        <?= htmlspecialchars($row['event_description']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['event_date']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(date("h:i A", strtotime($row['event_time_from']))); ?>
                        -
                        <?= htmlspecialchars(date("h:i A", strtotime($row['event_time_to']))); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['venue']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['registration_date']); ?>
                    </td>

                    <td>
                        <span class="badge">Registered</span>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>You have not registered for any event yet.</p>
        <a href="register_event.php" class="btn">Register Event</a>
    <?php } ?>
</div>

<?php include "student_footer.php"; ?>