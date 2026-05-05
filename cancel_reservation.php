<?php
session_start();
require_once 'dbconnect.php';

// 1. Ensure user is logged in
if (!isset($_SESSION['user_ID'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $event_id = intval($_GET['id']);
    $user_id = $_SESSION['user_ID'];

    // 2. Delete using the composite key (event_id + user_id)
    $sql = "DELETE FROM tickets WHERE event_ID = ? AND user_ID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $event_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Success: Redirect back to dashboard
            header("Location: pages/user/user_dashboard.php?status=cancelled");
        } else {
            header("Location: pages/user/user_dashboard.php?status=error");
        }
        mysqli_stmt_close($stmt);
    }
} else {
    header("Location: pages/user/user_dashboard.php");
}

mysqli_close($conn);
?>