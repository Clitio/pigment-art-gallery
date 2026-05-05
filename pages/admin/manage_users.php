<?php
session_start();
require_once '../../dbconnect.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM user WHERE user_ID = $id");
    header("Location: manage_users.php");
    exit();
}

$users = mysqli_query($conn, "SELECT * FROM user");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pigment - Manage Users</title>
    <!-- CSS is identical to the one above for consistency[cite: 9] -->
    <style>
        :root { --accent: #ffcc00; --bg-gradient: linear-gradient(135deg, #0f2027, #203a43, #2c5364); }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg-gradient); color: white; min-height: 100vh; }
        .sidebar { width: 260px; height: 100vh; position: fixed; color: white; overflow: hidden; z-index: 10; }
        .sidebar-video { position: absolute; top: 0; left: 0; height: 100%; width: 100%; object-fit: cover; z-index: -1; filter: brightness(0.3); }
        .sidebar-content { padding: 30px 20px; position: relative; }
        .sidebar h2 { color: var(--accent); letter-spacing: 2px; margin-bottom: 30px; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar a:hover { color: var(--accent); padding-left: 10px; transition: 0.3s; }
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .table-container { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(15px); padding: 30px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 25px 45px rgba(0,0,0,0.2); }
        table { width: 100%; border-collapse: collapse; }
        th { color: var(--accent); text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; padding: 20px 15px; text-align: left; border-bottom: 2px solid rgba(255, 255, 255, 0.1); }
        td { padding: 20px 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .btn-delete { color: #ff4d4d; text-decoration: none; padding: 8px 16px; border: 1px solid #ff4d4d; border-radius: 50px; transition: all 0.3s ease; font-size: 0.85rem; display: inline-block; }
        .btn-delete:hover { background: #ff4d4d; color: white; box-shadow: 0 0 15px rgba(255, 77, 77, 0.4); }
    </style>
</head>
<body>
    <div class="sidebar">
        <video autoplay muted loop class="sidebar-video"><source src="../../assets/aquarela_bg.mp4" type="video/mp4"></video>
        <div class="sidebar-content">
            <h2>PIGMENT</h2>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_users.php" style="color: var(--accent);">Manage Users</a>
            <a href="manage_organisers.php">Manage Organisers</a>
            <a href="manage_events.php">Manage Events</a>
            <a href="../../logout.php" style="color: #ff5555; margin-top: 40px; border: none;">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1>User Management</h1>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Birthday</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td>#<?php echo $row['user_ID']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['f_Name'] . " " . $row['l_Name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo $row['dOb']; ?></td>
                        <td>
                            <a href="manage_users.php?delete=<?php echo $row['user_ID']; ?>" 
                               class="btn-delete"
                               onclick="return confirm('Delete this user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>