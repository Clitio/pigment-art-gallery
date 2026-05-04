<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user"));
$total_events = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM event"));
$total_organisers = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM organiser"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pigment - Admin Dashboard</title>
    <style>
        :root { --accent: #ffcc00; --sidebar-bg: #1a1a1a; --text-light: #f4f4f4; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background-color: #f9f9f9; }
        
        .sidebar { width: 260px; height: 100vh; position: fixed; color: white; overflow: hidden; z-index: 10; }
        .sidebar-video { position: absolute; top: 0; left: 0; height: 100%; width: 100%; object-fit: cover; z-index: -1; filter: brightness(0.3); }
        .sidebar-content { padding: 30px 20px; }
        .sidebar h2 { color: var(--accent); letter-spacing: 2px; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1); transition: 0.3s; }
        .sidebar a:hover { color: var(--accent); padding-left: 10px; }
        
        .main-content { margin-left: 260px; padding: 40px; width: 100%; }
        .stats-container { display: flex; gap: 20px; }
        .stat-card { 
            background: white; padding: 30px; border-radius: 12px; flex: 1; 
            text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-top: 5px solid var(--accent); 
        }
        .stat-card h3 { color: #666; margin: 0; font-size: 0.9rem; text-transform: uppercase; }
        .stat-card p { font-size: 2.5rem; font-weight: bold; margin: 10px 0; color: #333; }
    </style>
</head>
<body>
    <div class="sidebar">
        <video autoplay muted loop class="sidebar-video"><source src="../../assets/aquarela_bg.mp4" type="video/mp4"></video>
        <div class="sidebar-content">
            <h2>PIGMENT</h2>
            <p style="font-size: 0.8rem; opacity: 0.7;">Admin: <?php echo $_SESSION['email']; ?></p>
            <hr style="opacity: 0.2;">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_users.php">Manage Users</a>
            <a href="manage_organisers.php">Manage Organisers</a>
            <a href="manage_events.php">Manage Events</a>
            <a href="../../logout.php" style="color: #ff5555; margin-top: 30px;">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1>System Overview</h1>
        <div class="stats-container">
            <div class="stat-card"><h3>Users</h3><p><?php echo $total_users; ?></p></div>
            <div class="stat-card" style="border-top-color: #00f2ff;"><h3>Organisers</h3><p><?php echo $total_organisers; ?></p></div>
            <div class="stat-card" style="border-top-color: #ff4d4d;"><h3>Events</h3><p><?php echo $total_events; ?></p></div>
        </div>
    </div>
</body>
</html>