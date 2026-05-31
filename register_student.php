<?php
session_start();

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "websecproject"
);

if (!$conn) {
    die("Database connection failed.");
}

$message = "";
$error = "";

$fullname = "";
$matric_no = "";
$email = "";
$username = "";

if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $matric_no = trim($_POST['matric_no']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation

    if (!preg_match("/^[a-zA-Z\s]+$/", $fullname)) {
        $error = "Full name can only contain letters and spaces.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (
        !preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            $password
        )
    ) {
        $error =
            "Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character.";
    } elseif ($password != $confirm_password) {
        $error = "Password and Confirm Password do not match.";
    }

    if (empty($error)) {
        // Check duplicate email

        $stmt = mysqli_prepare(
            $conn,
            "SELECT student_id FROM students WHERE email=?"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Email already registered.";
        }

        // Check duplicate matric number

        $stmt = mysqli_prepare(
            $conn,
            "SELECT student_id FROM students WHERE matric_no=?"
        );

        mysqli_stmt_bind_param($stmt, "s", $matric_no);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Matric number already exists.";
        }

        // Check username

        $check = mysqli_prepare(
            $conn,
            "SELECT user_id FROM users WHERE username=?"
        );

        mysqli_stmt_bind_param($check, "s", $username);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Username already exists.";
        }

        if (empty($error)) {
            $hashed_password =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $stmt1 = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (username,password,role)
                VALUES(?,?,'student')"
            );

            mysqli_stmt_bind_param(
                $stmt1,
                "ss",
                $username,
                $hashed_password
            );

            if (mysqli_stmt_execute($stmt1)) {
                $user_id =
                    mysqli_insert_id($conn);

                $stmt2 = mysqli_prepare(
                    $conn,
                    "INSERT INTO students
                    (user_id,fullname,matric_no,email)
                    VALUES(?,?,?,?)"
                );

                mysqli_stmt_bind_param(
                    $stmt2,
                    "isss",
                    $user_id,
                    $fullname,
                    $matric_no,
                    $email
                );

                if (mysqli_stmt_execute($stmt2)) {

                    $_SESSION['success_message'] =
                    "Registration successful. Please login.";

                    header("Location: student_login.php");
                    exit();
                } else {
                    $error =
                        "Failed to save student data.";
                }
            } else {
                $error =
                    "Registration failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Student Registration</title>

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
            background: linear-gradient(135deg, #1565c0, #42a5f5);
            padding: 20px;
        }

        .card {
            width: 500px;
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .2);
        }

        h2 {
            text-align: center;
            color: #1565c0;
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
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        input:focus {
            outline: none;
            border-color: #1565c0;
        }

        .btn {
            width: 100%;
            padding: 13px;
            border: none;
            background: #1565c0;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn:hover {
            background: #0d47a1;
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

        .login-link {
            text-align: center;
            margin-top: 15px;
        }

        .login-link a {
            color: #1565c0;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="card">

        <h2>Student Registration</h2>

        <?php if ($message != "") { ?>
            <div class="success">
                <?= $message ?>
            </div>
        <?php } ?>

        <?php if ($error != "") { ?>
            <div class="error">
                <?= $error ?>
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
                <label>Matric Number</label>
                <input
                    type="text"
                    name="matric_no"
                    value="<?= htmlspecialchars($matric_no) ?>"
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
                <input type="password" name="password" required>

                <small style="color:#666;font-size:12px;">
                    Password must contain at least 8 characters,
                    1 uppercase letter, 1 lowercase letter,
                    1 number and 1 special character.
                </small>
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" name="register" class="btn">
                Register
            </button>

        </form>

        <div class="login-link">
            Already have an account?
            <a href="student_login.php">Login Here</a>
        </div>

    </div>

</body>

</html>