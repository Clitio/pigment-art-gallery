<?php 
session_start();
require_once 'dbconnect.php';
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'organiser') {
        header("Location: ../../login.php");
        exit();
    }
    $organiser_ID = $_SESSION['user_id'];
    $success = "";
    $errors  = [];
    $o_Name = '';
    $o_Company = '';

   $stmt = mysqli_prepare($conn, 'SELECT o_Name, o_Company FROM organiser WHERE organiser_ID = ?');
    mysqli_stmt_bind_param($stmt, 'i', $organiser_ID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $o_Name, $o_Company);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $stmt2 = mysqli_prepare($conn, 'SELECT COUNT(*)  AS total_events FROM event WHERE organiser_ID = ?');
    mysqli_stmt_bind_param($stmt2, 'i', $organiser_ID);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_bind_result($stmt2, $total_events);
    mysqli_stmt_fetch($stmt2);
    mysqli_stmt_close($stmt2);




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organiser Dashboard</title>
</head>
<body> 
    
    <h1>Welcome, <?php echo htmlspecialchars($o_Name); ?> from <?php echo htmlspecialchars($o_Company); ?>!</h1>

    <nav>
        <a href="organiser_dashboard.php">Dashboard</a> |
        <a href="organiser_add.php">Add Event</a>   |
        <a href="organiser_list.php">My Events</a>  |
        <a href="../../logout.php">Logout</a>
    </nav>


    <p>You have created <strong><?= $total_events; ?></strong> event(s).</p>

</body>
</html> 