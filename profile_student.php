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

<style>
    .profile-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .profile-card {
        background: white;
        border-radius: 24px;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
    }

    .profile-banner {
        background: linear-gradient(135deg, #1565C0, #42A5F5);
        padding: 40px;
        color: white;
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .profile-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: white;
        color: #1565C0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: bold;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .profile-banner h1 {
        margin: 0;
        font-size: 34px;
    }

    .profile-banner p {
        margin-top: 8px;
        font-size: 16px;
        opacity: 0.95;
    }

    .profile-body {
        padding: 35px 40px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 22px;
    }

    .info-box {
        background: #F4F9FF;
        border-left: 5px solid #1565C0;
        padding: 20px;
        border-radius: 14px;
    }

    .info-box .label {
        display: block;
        color: #1565C0;
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .info-box .value {
        color: #263238;
        font-size: 17px;
        word-break: break-word;
    }

    .role-badge {
        display: inline-block;
        background: #E3F2FD;
        color: #0D47A1;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: bold;
        text-transform: capitalize;
    }

    .profile-footer-note {
        margin-top: 28px;
        background: #E8F5E9;
        color: #2E7D32;
        padding: 16px 20px;
        border-radius: 12px;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .profile-banner {
            flex-direction: column;
            text-align: center;
        }

        .profile-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile-container">
    <?php if ($student) { 
        $initial = strtoupper(substr($student['fullname'], 0, 1));
    ?>
        <div class="profile-card">

            <div class="profile-banner">
                <div class="profile-avatar">
                    <?= htmlspecialchars($initial); ?>
                </div>

                <div>
                    <h1><?= htmlspecialchars($student['fullname']); ?></h1>
                    <p>Student Profile Information</p>
                </div>
            </div>

            <div class="profile-body">
                <div class="profile-grid">

                    <div class="info-box">
                        <span class="label">Full Name</span>
                        <span class="value"><?= htmlspecialchars($student['fullname']); ?></span>
                    </div>

                    <div class="info-box">
                        <span class="label">Matric Number</span>
                        <span class="value"><?= htmlspecialchars($student['matric_no']); ?></span>
                    </div>

                    <div class="info-box">
                        <span class="label">Email Address</span>
                        <span class="value"><?= htmlspecialchars($student['email']); ?></span>
                    </div>

                    <div class="info-box">
                        <span class="label">Username</span>
                        <span class="value"><?= htmlspecialchars($student['username']); ?></span>
                    </div>

                    <div class="info-box">
                        <span class="label">Role</span>
                        <span class="role-badge"><?= htmlspecialchars($student['role']); ?></span>
                    </div>

                </div>

                <div class="profile-footer-note">
                    Your profile information is linked to your student account.
                </div>
            </div>

        </div>
    <?php } else { ?>
        <div class="profile-card" style="padding: 35px;">
            <h1>Profile data not found.</h1>
        </div>
    <?php } ?>
</div>

<?php include "student_footer.php"; ?>
