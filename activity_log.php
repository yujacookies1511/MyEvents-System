<?php
function addActivityLog($conn, $user_id, $username, $role, $action, $description) {

    $ip_address = $_SERVER['REMOTE_ADDR'];

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO activity_logs
        (user_id, username, role, action, description, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "isssss",
        $user_id,
        $username,
        $role,
        $action,
        $description,
        $ip_address
    );

    mysqli_stmt_execute($stmt);
}
?>