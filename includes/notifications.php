<?php
function createNotification($user_id, $title, $message, $type = 'info') {
    global $conn;
    $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $title, $message, $type);
    return mysqli_stmt_execute($stmt);
}

function getUnreadNotifications($user_id) {
    global $conn;
    $sql = "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 10";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function markAsRead($notification_id) {
    global $conn;
    $sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $notification_id);
    return mysqli_stmt_execute($stmt);
}
?>
