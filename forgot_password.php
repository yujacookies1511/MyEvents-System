<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "websecproject1");

if (!$conn) {
    die("Database connection failed.");
}

$error = "";
$message = "";
$show_otp = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* REQUEST OTP */
if (isset($_POST['request_otp'])) {

    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid request.");
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT 
            u.user_id,
            u.username,
            u.role,
            COALESCE(s.email, a.email) AS email
         FROM users u
         LEFT JOIN students s ON u.user_id = s.user_id
         LEFT JOIN admin a ON u.user_id = a.user_id
         WHERE u.username = ?
         AND (s.email = ? OR a.email = ?)"
    );

    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);
        $otp = random_int(100000, 999999);

        $_SESSION['reset_user_id'] = $user['user_id'];
        $_SESSION['reset_username'] = $user['username'];
        $_SESSION['reset_otp_hash'] = password_hash($otp, PASSWORD_DEFAULT);
        $_SESSION['reset_otp_expiry'] = time() + 300;
        $_SESSION['otp_verified'] = false;

        $message = "OTP generated successfully. Please use the security verification code below.";
        $show_otp = $otp;

    } else {
        $error = "Invalid username or email.";
    }
}

/* VERIFY OTP */
if (isset($_POST['verify_otp'])) {

    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid request.");
    }

    $otp = trim($_POST['otp']);

    if (
        empty($_SESSION['reset_otp_hash']) ||
        empty($_SESSION['reset_otp_expiry'])
    ) {
        $error = "OTP session expired. Please request a new OTP.";

    } elseif (time() > $_SESSION['reset_otp_expiry']) {

        $error = "OTP expired. Please request a new OTP.";

        unset($_SESSION['reset_user_id']);
        unset($_SESSION['reset_username']);
        unset($_SESSION['reset_otp_hash']);
        unset($_SESSION['reset_otp_expiry']);
        unset($_SESSION['otp_verified']);

    } elseif (password_verify($otp, $_SESSION['reset_otp_hash'])) {

        $_SESSION['otp_verified'] = true;
        $message = "OTP verified successfully. You may now reset your password.";

    } else {
        $error = "Invalid OTP code.";
    }
}

/* RESET PASSWORD */
if (isset($_POST['reset_password'])) {

    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid request.");
    }

    if (
        empty($_SESSION['reset_user_id']) ||
        empty($_SESSION['otp_verified']) ||
        $_SESSION['otp_verified'] !== true
    ) {
        $error = "Unauthorized password reset request. Please verify OTP first.";

    } else {

        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (
            !preg_match(
                '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                $password
            )
        ) {

            $error = "Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character.";

        } elseif ($password !== $confirm_password) {

            $error = "Password and Confirm Password do not match.";

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE users
                 SET password = ?,
                     failed_attempts = 0,
                     lock_until = NULL
                 WHERE user_id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $hashed_password,
                $_SESSION['reset_user_id']
            );

            if (mysqli_stmt_execute($stmt)) {

                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_username']);
                unset($_SESSION['reset_otp_hash']);
                unset($_SESSION['reset_otp_expiry']);
                unset($_SESSION['otp_verified']);

                $_SESSION['success_message'] =
                    "Password reset successful. Please login.";

                header("Location: login.php");
                exit();

            } else {
                $error = "Failed to reset password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1565C0, #42A5F5);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .card {
            width: 520px;
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,.2);
        }

        h2 {
            text-align: center;
            color: #1565C0;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 25px;
            color: #555;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        input:focus {
            outline: none;
            border-color: #1565C0;
        }

        .btn {
            width: 100%;
            padding: 13px;
            background: #1565C0;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 5px;
        }

        .btn:hover {
            background: #0D47A1;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }

        .otp-box {
            background: #E3F2FD;
            border-left: 6px solid #1565C0;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 18px;
            text-align: center;
        }

        .otp-box h3 {
            color: #0D47A1;
            margin-bottom: 8px;
        }

        .otp-code {
            font-size: 34px;
            font-weight: bold;
            color: #1565C0;
            letter-spacing: 6px;
            margin: 12px 0;
        }

        .otp-note {
            font-size: 13px;
            color: #555;
        }

        .password-rules {
            margin-top: 10px;
            font-size: 13px;
            background: #F5FAFF;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #E3F2FD;
        }

        .password-rules p {
            margin: 5px 0;
        }

        .valid {
            color: #2E7D32;
            font-weight: bold;
        }

        .invalid {
            color: #C62828;
        }

        .link {
            text-align: center;
            margin-top: 15px;
        }

        .link a {
            color: #1565C0;
            font-weight: bold;
            text-decoration: none;
        }

        hr {
            margin: 25px 0;
            border: none;
            border-top: 1px solid #ddd;
        }

        small {
            color: #666;
            font-size: 12px;
        }
    </style>
</head>

<body>

<div class="card">

    <h2>Forgot Password</h2>
    <p class="subtitle">Multi-Factor Password Reset</p>

    <?php if ($message != "") { ?>
        <div class="success">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <?php if ($show_otp != "") { ?>
        <div class="otp-box">
            <h3>Security Verification Code</h3>
            <p>Your One-Time Password is:</p>

            <div class="otp-code">
                <?= htmlspecialchars($show_otp); ?>
            </div>

            <p class="otp-note">
                This OTP is valid for 5 minutes.
            </p>
        </div>
    <?php } ?>

    <form method="POST">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="input-group">
            <label>Username</label>
            <input
                type="text"
                name="username"
                required>
        </div>

        <div class="input-group">
            <label>Registered Email</label>
            <input
                type="email"
                name="email"
                required>
        </div>

        <button
            type="submit"
            name="request_otp"
            class="btn">
            Request OTP
        </button>
    </form>

    <hr>

    <form method="POST">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="input-group">
            <label>OTP Code</label>
            <input
                type="text"
                name="otp"
                maxlength="6"
                placeholder="Enter OTP code"
                required>
        </div>

        <button
            type="submit"
            name="verify_otp"
            class="btn">
            Verify OTP
        </button>
    </form>

    <hr>

    <form method="POST">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="input-group">
            <label>New Password</label>
            <input
                type="password"
                name="password"
                id="password"
                onkeyup="checkPassword()"
                required>

            <div class="password-rules">
                <p id="length" class="invalid">✗ At least 8 characters</p>
                <p id="uppercase" class="invalid">✗ One uppercase letter</p>
                <p id="lowercase" class="invalid">✗ One lowercase letter</p>
                <p id="number" class="invalid">✗ One number</p>
                <p id="special" class="invalid">✗ One special character</p>
            </div>
        </div>

        <div class="input-group">
            <label>Confirm New Password</label>
            <input
                type="password"
                name="confirm_password"
                required>
        </div>

        <button
            type="submit"
            name="reset_password"
            class="btn">
            Reset Password
        </button>
    </form>

    <div class="link">
        <a href="login.php">Back to Login</a>
    </div>

</div>

<script>
function checkPassword() {
    let password = document.getElementById("password").value;

    updateRule("length", password.length >= 8, "At least 8 characters");
    updateRule("uppercase", /[A-Z]/.test(password), "One uppercase letter");
    updateRule("lowercase", /[a-z]/.test(password), "One lowercase letter");
    updateRule("number", /[0-9]/.test(password), "One number");
    updateRule("special", /[@$!%*?&]/.test(password), "One special character");
}

function updateRule(id, condition, text) {
    let element = document.getElementById(id);

    if (condition) {
        element.className = "valid";
        element.innerHTML = "✓ " + text;
    } else {
        element.className = "invalid";
        element.innerHTML = "✗ " + text;
    }
}
</script>

</body>
</html>