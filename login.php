<?php
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
// Cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
$conn = mysqli_connect("localhost", "root", "", "websecproject1");

if (!$conn) {
    die("Database connection failed.");
}

$error = "";

if (isset($_GET['timeout'])) {
    $error = "Session expired. Please login again.";
}

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $captcha = trim($_POST['captcha'] ?? "");

    if (
        empty($_SESSION['captcha_code']) ||
        strtoupper($captcha) !== $_SESSION['captcha_code']
    ) {
        $error = "Incorrect CAPTCHA code.";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT user_id, username, password, role, failed_attempts, lock_until
             FROM users
             WHERE username=?"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            if (!empty($user['lock_until'])) {

                $checkLock = mysqli_prepare(
                    $conn,
                    "SELECT IF(NOW() < ?, 1, 0) AS is_locked"
                );

                mysqli_stmt_bind_param($checkLock, "s", $user['lock_until']);
                mysqli_stmt_execute($checkLock);

                $lockResult = mysqli_stmt_get_result($checkLock);
                $lockData = mysqli_fetch_assoc($lockResult);

                if ($lockData['is_locked'] == 1) {
                    $error = "Your account is temporarily locked. Please try again later.";
                } else {
                    $unlock = mysqli_prepare(
                        $conn,
                        "UPDATE users
                         SET failed_attempts=0,
                             lock_until=NULL
                         WHERE user_id=?"
                    );

                    mysqli_stmt_bind_param($unlock, "i", $user['user_id']);
                    mysqli_stmt_execute($unlock);

                    $user['failed_attempts'] = 0;
                    $user['lock_until'] = NULL;
                }
            }

            if ($error == "") {

                if (password_verify($password, $user['password'])) {

                    session_regenerate_id(true);

                    $reset = mysqli_prepare(
                        $conn,
                        "UPDATE users
                         SET failed_attempts=0,
                             lock_until=NULL
                         WHERE user_id=?"
                    );

                    mysqli_stmt_bind_param($reset, "i", $user['user_id']);
                    mysqli_stmt_execute($reset);

                    $_SESSION['last_activity'] = time();
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    unset($_SESSION['captcha_code']);

                    if ($user['role'] == 'student') {
                        header("Location: dashboard_student.php");
                        exit();
                    } elseif ($user['role'] == 'admin') {
                        header("Location: dashboard_admin.php");
                        exit();
                    } else {
                        header("Location: dashboard_student.php");
                        exit();
                    }

                } else {

                    $attempts = $user['failed_attempts'] + 1;

                    if ($attempts >= 5) {

                        $lock = mysqli_prepare(
                            $conn,
                            "UPDATE users
                             SET failed_attempts=0,
                                 lock_until=DATE_ADD(NOW(), INTERVAL 5 MINUTE)
                             WHERE user_id=?"
                        );

                        mysqli_stmt_bind_param($lock, "i", $user['user_id']);
                        mysqli_stmt_execute($lock);

                        $error = "Too many failed login attempts. Account locked for 5 minutes.";

                    } else {

                        $update = mysqli_prepare(
                            $conn,
                            "UPDATE users
                             SET failed_attempts=?
                             WHERE user_id=?"
                        );

                        mysqli_stmt_bind_param(
                            $update,
                            "ii",
                            $attempts,
                            $user['user_id']
                        );

                        mysqli_stmt_execute($update);

                        $remaining = 5 - $attempts;

                        $error = "Invalid login attempt. Remaining attempts: " . $remaining;
                    }
                }
            }

        } else {
            $error = "Invalid username or password.";
        }
    }

    if ($error != "") {
        unset($_SESSION['captcha_code']);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #1565C0, #42A5F5);
            padding: 20px;
        }

        .card {
            width: 420px;
            background: #FFFFFF;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .2);
        }

        h2 {
            text-align: center;
            color: #1565C0;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #333333;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
            color: #333333;
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

        .captcha-box {
            background: #E3F2FD;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 10px;
        }

        .captcha-box img {
            height: 50px;
        }

        .refresh-captcha {
            text-align: center;
            margin-bottom: 10px;
        }

        .refresh-captcha a {
            font-size: 13px;
            color: #1565C0;
            text-decoration: none;
            font-weight: 600;
        }

        .btn {
            width: 100%;
            padding: 13px;
            border: none;
            background: #1565C0;
            color: #FFFFFF;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 5px;
        }

        .btn:hover {
            background: #0D47A1;
        }

        .register-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .register-link a {
            color: #1565C0;
            text-decoration: none;
            font-weight: 600;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .modal-box {
            width: 360px;
            background: #FFFFFF;
            border-radius: 18px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .25);
            animation: pop .25s ease;
        }

        @keyframes pop {
            from {
                transform: scale(.85);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .modal-icon {
            width: 55px;
            height: 55px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: #ffebee;
            color: #c62828;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 600;
        }

        .modal-box h3 {
            color: #333333;
            margin-bottom: 8px;
        }

        .modal-box p {
            color: #666666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .modal-btn {
            width: 100%;
            padding: 12px;
            border: none;
            background: #1565C0;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .modal-btn:hover {
            background: #0D47A1;
        }
    </style>
</head>

<body>

<div class="card">

    <h2>Login</h2>
    <p class="subtitle">Authentication Module</p>

    <form method="POST" autocomplete="off">

        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="input-group">
            <label>CAPTCHA Verification</label>

            <div class="captcha-box">
                <img src="captcha.php?rand=<?= time(); ?>" id="captchaImage" alt="CAPTCHA">
            </div>

            <div class="refresh-captcha">
                <a href="#" onclick="refreshCaptcha(); return false;">
                    Refresh CAPTCHA
                </a>
            </div>

            <input
                type="text"
                name="captcha"
                placeholder="Enter CAPTCHA code"
                required>
        </div>
        <div> <a href="forgot_password.php">Forgot Password?</a></div>

        <button type="submit" name="login" class="btn">
            Login
        </button>

    </form>

    <div class="register-link">
        Don't have an account?
        <a href="register_student.php">Register Here</a>

    </div>

</div>

<div class="modal-overlay" id="errorModal">
    <div class="modal-box">
        <div class="modal-icon">!</div>
        <h3>Login Notice</h3>
        <p id="errorText"></p>
        <button class="modal-btn" onclick="closeModal()">OK</button>
    </div>
</div>

<script>
    function closeModal() {
        document.getElementById("errorModal").style.display = "none";
    }

    function refreshCaptcha() {
        document.getElementById("captchaImage").src =
            "captcha.php?rand=" + Math.random();
    }

    <?php if ($error != "") { ?>
        document.getElementById("errorText").innerText =
            "<?= htmlspecialchars($error) ?>";
        document.getElementById("errorModal").style.display = "flex";
    <?php } ?>
</script>

</body>
</html>
