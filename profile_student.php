<?php
include "auth_check.php";

$conn = mysqli_connect("localhost", "root", "", "websecproject1");

if (!$conn) {
    die("Database connection failed.");
}

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT u.username, u.role, s.fullname, s.matric_no, s.email
     FROM users u
     INNER JOIN students s ON u.user_id = s.user_id
     WHERE u.user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

$page_title = "Student Profile";
include "student_header.php";
?>

<div class="card" style="max-width: 650px;">
    <h1>My Profile</h1>

    <?php if ($student) { ?>
        <div class="profile-row">
            <span class="label">Full Name:</span>
            <?= htmlspecialchars($student['fullname']); ?>
        </div>

        <div class="profile-row">
            <span class="label">Matric Number:</span>
            <?= htmlspecialchars($student['matric_no']); ?>
        </div>

        <div class="profile-row">
            <span class="label">Email:</span>
            <?= htmlspecialchars($student['email']); ?>
        </div>

        <div class="profile-row">
            <span class="label">Username:</span>
            <?= htmlspecialchars($student['username']); ?>
        </div>

        <div class="profile-row">
            <span class="label">Role:</span>
            <?= htmlspecialchars($student['role']); ?>
        </div>
    <?php } else { ?>
        <p>Profile data not found.</p>
    <?php } ?>
</div>

<?php include "student_footer.php"; ?>