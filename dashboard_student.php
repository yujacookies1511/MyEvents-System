<?php
include "auth_check.php";

$conn = mysqli_connect("localhost", "root", "", "websecproject1");

if (!$conn) {
    die("Database connection failed.");
}

$user_id = $_SESSION['user_id'] ?? 0;

$total_events = 0;
$total_registrations = 0;
$upcoming_events = 0;
$student_id = 0;

/* Get current student's student_id using logged-in user_id */
$getStudent = mysqli_prepare(
    $conn,
    "SELECT student_id
     FROM students
     WHERE user_id = ?"
);

if ($getStudent) {
    mysqli_stmt_bind_param($getStudent, "i", $user_id);
    mysqli_stmt_execute($getStudent);

    $studentResult = mysqli_stmt_get_result($getStudent);

    if ($studentData = mysqli_fetch_assoc($studentResult)) {
        $student_id = $studentData['student_id'];
    }

    mysqli_stmt_close($getStudent);
}

/* Total Events */
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM events");

if ($result) {
    $total_events = mysqli_fetch_assoc($result)['total'];
}

/* Total Registrations for CURRENT student only */
if ($student_id > 0) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM registrations
         WHERE student_id = ?"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            $total_registrations = mysqli_fetch_assoc($result)['total'];
        }

        mysqli_stmt_close($stmt);
    }
}

/* Upcoming Events */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM events
     WHERE event_date >= CURDATE()"
);

if ($result) {
    $upcoming_events = mysqli_fetch_assoc($result)['total'];
}

/* Upcoming Events List */
$events = mysqli_query(
    $conn,
    "SELECT event_id,
            event_name,
            event_date,
            event_time_from,
            event_time_to,
            venue
     FROM events
     WHERE event_date >= CURDATE()
     ORDER BY event_date ASC
     LIMIT 5"
);

$page_title = "Student Dashboard";
include "student_header.php";
?>

<div class="hero-card">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></h1>

    <p>
        Explore upcoming events, view your profile, and manage your event participation.
    </p>

    <p class="small-text">
        Logged in as: <?= htmlspecialchars($_SESSION['role']); ?>
    </p>
</div>

<div class="grid">
    <div class="stat-card">
        <h3>Total Events</h3>

        <div class="stat-number">
            <?= htmlspecialchars($total_events); ?>
        </div>

        <p class="small-text">
            Events available in the system
        </p>
    </div>

    <div class="stat-card">
        <h3>Upcoming Events</h3>

        <div class="stat-number">
            <?= htmlspecialchars($upcoming_events); ?>
        </div>

        <p class="small-text">
            Events scheduled from today onward
        </p>
    </div>

    <div class="stat-card">
        <h3>My Registrations</h3>

        <div class="stat-number">
            <?= htmlspecialchars($total_registrations); ?>
        </div>

        <p class="small-text">
            Your registered events
        </p>
    </div>
</div>

<div class="card">
    <h2>Upcoming Events</h2>

    <?php if ($events && mysqli_num_rows($events) > 0) { ?>

        <table>
            <tr>
                <th>Event Name</th>
                <th>Date</th>
                <th>Time</th>
                <th>Venue</th>
            </tr>

            <?php while ($event = mysqli_fetch_assoc($events)) { ?>
                <tr>
                    <td><?= htmlspecialchars($event['event_name']); ?></td>

                    <td><?= htmlspecialchars($event['event_date']); ?></td>

                    <td>
                        <?= htmlspecialchars(date("h:i A", strtotime($event['event_time_from']))); ?>
                        -
                        <?= htmlspecialchars(date("h:i A", strtotime($event['event_time_to']))); ?>
                    </td>

                    <td><?= htmlspecialchars($event['venue']); ?></td>
                </tr>
            <?php } ?>
        </table>

    <?php } else { ?>

        <p>No upcoming events available.</p>

    <?php } ?>
</div>

<div class="card">
    <h2>Quick Actions</h2>

    <div class="quick-actions">
        <a href="profile_student.php" class="btn">View Profile</a>
        <a href="register_event.php" class="btn">Register Event</a>
        <a href="my_registrations.php" class="btn">My Registrations</a>
    </div>
</div>

<?php include "student_footer.php"; ?>
