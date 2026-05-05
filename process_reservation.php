<?php
session_start();
require_once 'dbconnect.php';

// 1. Security Check: Must be logged in
if (!isset($_SESSION['user_ID'])) {
    $event_id = $_POST['event_id'] ?? '';
    header("Location: event_details.php?id=$event_id&error=login_required");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);
    $user_id = $_SESSION['user_ID'];

    // 2. Prepare the insertion for the 'tickets' table
    $sql = "INSERT INTO tickets (event_ID, user_ID) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $event_id, $user_id);
        
        try {
            if (mysqli_stmt_execute($stmt)) {
                // SUCCESS: Redirect to the new success confirmation page
                // We pass the event_id so success.php can display the event title
                header("Location: success.php?event_id=" . $event_id);
            } else {
                // Handle manual error return (e.g., if try/catch isn't triggered)
                header("Location: event_details.php?id=$event_id&error=already_reserved");
            }
        } catch (Exception $e) {
            // Error 1062 (Duplicate entry) caught here
            header("Location: event_details.php?id=$event_id&error=already_reserved");
        }
        
        mysqli_stmt_close($stmt);
    }
} else {
    // If someone tries to access this file directly without POSTing
    header("Location: catalog.php");
}

mysqli_close($conn);
?>