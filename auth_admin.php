<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    session_unset();
    session_destroy();

    header("Location: student_login.php");
    exit();
}

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > 900) {
        session_unset();
        session_destroy();

        header("Location: login.php?timeout=1");
        exit();
    }
}

$_SESSION['last_activity'] = time();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>