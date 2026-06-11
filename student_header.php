<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$current_page = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? "Event Management System";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars($page_title); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            background: #E3F2FD;
            color: #333333;
        }

        .main-content {
            flex: 1;
        }

        /* Navbar */

        .navbar {
            background: #1565C0;
            padding: 20px 45px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            color: white;
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-right a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 10px;
            transition: 0.3s;
        }

        .nav-right a:hover,
        .nav-right a.active {
            background: #0D47A1;
        }

        .logout-form {
            display: inline;
        }

        .logout-btn {
            background: #0D47A1;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: #08306b;
        }

        /* Container */

        .container {
            padding: 40px;
        }

        /* Cards */

        .card,
        .hero-card,
        .stat-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
        }

        .hero-card,
        .card {
            padding: 35px;
            margin-bottom: 25px;
        }

        .hero-card h1,
        .card h1,
        .card h2 {
            color: #1565C0;
            margin-bottom: 15px;
        }

        .hero-card p {
            font-size: 16px;
        }

        /* Statistics */

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 25px;
        }

        .stat-card {
            padding: 25px;
        }

        .stat-card h3 {
            color: #1565C0;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 42px;
            font-weight: bold;
            color: #1565C0;
        }

        /* Table */

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background: #1565C0;
            color: white;
            text-align: left;
            padding: 15px;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        table tr:hover {
            background: #f7fbff;
        }

        /* Buttons */

        .btn {
            background: #1565C0;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: 0.3s;
        }

        .btn:hover {
            background: #0D47A1;
        }

        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* Alert */

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        /* Badge */

        .badge {
            background: #E3F2FD;
            color: #1565C0;
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }

        /* Profile */

        .profile-row {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .label {
            font-weight: 600;
            color: #1565C0;
        }

        /* Responsive */

        @media (max-width: 900px) {

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .nav-right {
                flex-wrap: wrap;
                justify-content: center;
            }

            .grid {
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

        <div class="nav-right">

            <a href="dashboard_student.php"
                class="<?= $current_page == 'dashboard_student.php' ? 'active' : '' ?>">
                Dashboard
            </a>

            <a href="register_event.php"
                class="<?= $current_page == 'register_event.php' ? 'active' : '' ?>">
                Register Event
            </a>

            <a href="my_registrations.php"
                class="<?= $current_page == 'my_registrations.php' ? 'active' : '' ?>">
                My Registrations
            </a>

            <a href="profile_student.php"
                class="<?= $current_page == 'profile_student.php' ? 'active' : '' ?>">
                Profile
            </a>

            <form action="logout.php" method="POST" class="logout-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

                <button
                    type="submit"
                    class="logout-btn"
                    onclick="return confirm('Are you sure you want to logout?')">
                    Logout
                </button>

            </form>

        </div>

    </div>

    <div class="main-content">
        <div class="container">