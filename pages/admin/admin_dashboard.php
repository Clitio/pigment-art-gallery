<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user"));
$total_events = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM event"));
$total_organisers = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM organiser"));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pigment - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; display: flex; }
        .sidebar { width: 250px; height: 100vh; background: #333; color: white; padding: 20px; position: fixed; }
        .main-content { margin-left: 290px; padding: 20px; width: 100%; }
        .stats-container { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { padding: 20px; background: #f4f4f4; border-radius: 8px; flex: 1; text-align: center; border: 1px solid #ddd; }
        .sidebar a { color: white; text-decoration: none; display: block; margin: 15px 0; }
        .sidebar a:hover { color: #ffcc00; }
        .logout { color: #ff5555 !important; margin-top: 50px !important; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Pigment Admin</h2>
        <p>Olá, <?php echo $_SESSION['email']; ?></p>
        <hr>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php">Manage User</a>
        <a href="manage_organisers.php">Manage Organizers</a>
        <a href="manage_events.php">Manage Events</a>
        <a href="../../logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Control Panel</h1>
        <p>Overview about the system</p>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="stat-card">
                <h3>Organizers</h3>
                <p><?php echo $total_organisers; ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Events</h3>
                <p><?php echo $total_events; ?></p>
            </div>
        </div>
    </div>

</body>
</html>