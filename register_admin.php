<?php
session_start();

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "websecproject1"
);

if (!$conn) {
    die("Database connection failed.");
}

$message = "";
$error = "";

$fullname = "";
$email = "";
$username = "";

if (isset($_POST['register'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!preg_match("/^[a-zA-Z\s]+$/", $fullname)) {

        $error = "Full name can only contain letters and spaces.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Invalid email format.";

    } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {

        $error = "Username can only contain letters, numbers and underscore.";

    } elseif (
        !preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/',
            $password
        )
    ) {

        $error =
            "Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character.";

    } elseif ($password != $confirm_password) {

        $error = "Password and Confirm Password do not match.";
    }

    if (empty($error)) {

        $checkUser = mysqli_prepare(
            $conn,
            "SELECT user_id
             FROM users
             WHERE username=?"
        );

        mysqli_stmt_bind_param(
            $checkUser,
            "s",
            $username
        );

        mysqli_stmt_execute($checkUser);
        mysqli_stmt_store_result($checkUser);

        if (mysqli_stmt_num_rows($checkUser) > 0) {

            $error = "Username already exists.";
        }
    }

    if (empty($error)) {

        $checkAdminEmail = mysqli_prepare(
            $conn,
            "SELECT admin_id
             FROM admin
             WHERE email=?"
        );

        mysqli_stmt_bind_param(
            $checkAdminEmail,
            "s",
            $email
        );

        mysqli_stmt_execute($checkAdminEmail);
        mysqli_stmt_store_result($checkAdminEmail);

        if (mysqli_stmt_num_rows($checkAdminEmail) > 0) {

            $error = "Email already registered.";
        }
    }

    if (empty($error)) {

        mysqli_begin_transaction($conn);

        try {

            $hashed_password =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $stmt1 = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (username,password,role,failed_attempts,lock_until)
                VALUES(?,?,'admin',0,NULL)"
            );

            mysqli_stmt_bind_param(
                $stmt1,
                "ss",
                $username,
                $hashed_password
            );

            if (!mysqli_stmt_execute($stmt1)) {
                throw new Exception("Failed to save user data.");
            }

            $user_id = mysqli_insert_id($conn);

            $stmt2 = mysqli_prepare(
                $conn,
                "INSERT INTO admin
                (user_id,fullname,email)
                VALUES(?,?,?)"
            );

            mysqli_stmt_bind_param(
                $stmt2,
                "iss",
                $user_id,
                $fullname,
                $email
            );

            if (!mysqli_stmt_execute($stmt2)) {
                throw new Exception("Failed to save admin data.");
            }

            mysqli_commit($conn);

            $_SESSION['success_message'] =
                "Admin registration successful. Please login.";

            header("Location: login.php");
            exit();

        } catch (Exception $e) {

            mysqli_rollback($conn);

            $error = "Admin registration failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Registration</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }

        body {
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:linear-gradient(135deg,#1565c0,#42a5f5);
            padding:20px;
        }

        .card {
            width:500px;
            background:white;
            padding:35px;
            border-radius:20px;
            box-shadow:0 15px 40px rgba(0,0,0,.2);
        }

        h2 {
            text-align:center;
            color:#1565c0;
            margin-bottom:25px;
        }

        .input-group {
            margin-bottom:15px;
        }

        label {
            display:block;
            margin-bottom:6px;
            font-size:14px;
            font-weight:500;
        }

        input {
            width:100%;
            padding:12px;
            border:1px solid #ddd;
            border-radius:10px;
        }

        input:focus {
            outline:none;
            border-color:#1565c0;
        }

        .btn {
            width:100%;
            padding:13px;
            border:none;
            background:#1565c0;
            color:white;
            border-radius:10px;
            cursor:pointer;
            font-weight:600;
        }

        .btn:hover {
            background:#0d47a1;
        }

        .error {
            background:#ffebee;
            color:#c62828;
            padding:10px;
            border-radius:8px;
            margin-bottom:15px;
            text-align:center;
        }

        .login-link {
            text-align:center;
            margin-top:15px;
        }

        .login-link a {
            color:#1565c0;
            text-decoration:none;
            font-weight:600;
        }
    </style>
</head>

<body>

<div class="card">

    <h2>Admin Registration</h2>

    <?php if ($error != "") { ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php } ?>

    <form method="POST">

        <div class="input-group">
            <label>Full Name</label>
            <input
                type="text"
                name="fullname"
                value="<?= htmlspecialchars($fullname) ?>"
                required>
        </div>

        <div class="input-group">
            <label>Email</label>
            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($email) ?>"
                required>
        </div>

        <div class="input-group">
            <label>Username</label>
            <input
                type="text"
                name="username"
                value="<?= htmlspecialchars($username) ?>"
                required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input
                type="password"
                name="password"
                required>

            <small style="color:#666;font-size:12px;">
                Password must contain at least 8 characters,
                1 uppercase letter,
                1 lowercase letter,
                1 number and
                1 special character.
            </small>
        </div>

        <div class="input-group">
            <label>Confirm Password</label>
            <input
                type="password"
                name="confirm_password"
                required>
        </div>

        <button
            type="submit"
            name="register"
            class="btn">
            Register Admin
        </button>

    </form>

    <div class="login-link">
        Already have an account?
        <a href="login.php">Login Here</a>
    </div>

</div>

</body>
</html>