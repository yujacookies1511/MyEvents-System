<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
?>