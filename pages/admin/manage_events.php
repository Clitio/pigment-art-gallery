<?php
session_start();
require_once '../../dbconnect.php'; // Adjusted path for pages/admin/ folder

// ACCESS CONTROL[cite: 13, 16]
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// DELETE LOGIC[cite: 13]
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM event WHERE event_ID = $id");
    header("Location: manage_events.php");
    exit();
}

// FETCH LOGIC[cite: 13]
$sql = "SELECT e.*, o.o_Name FROM event e 
        LEFT JOIN organiser o ON e.organiser_ID = o.organiser_ID";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pigment - Manage Events</title>
    <style>
        :root { 
            --accent: #ffcc00; 
            --bg-gradient: linear-gradient(135deg, #0f2027, #203a43, #2c5364); 
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0; 
            display: flex; 
            background: var(--bg-gradient); 
            color: white; 
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar { width: 260px; height: 100vh; position: fixed; color: white; overflow: hidden; z-index: 10; }
        .sidebar-video { position: absolute; top: 0; left: 0; height: 100%; width: 100%; object-fit: cover; z-index: -1; filter: brightness(0.3); }
        .sidebar-content { padding: 30px 20px; position: relative; }
        .sidebar h2 { color: var(--accent); letter-spacing: 2px; margin-bottom: 30px; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.1); transition: 0.3s; }
        .sidebar a:hover { color: var(--accent); padding-left: 10px; }

        /* Main Content & Table */
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        
        .table-container { 
            background: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(15px); 
            padding: 30px; 
            border-radius: 20px; 
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 45px rgba(0,0,0,0.2);
        }

        table { width: 100%; border-collapse: collapse; }

        th { 
            color: var(--accent); 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            font-size: 0.8rem; 
            padding: 20px 15px;
            text-align: left;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        td { padding: 20px 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }

        /* Modern Pill Buttons[cite: 9] */
        .btn-delete { 
            color: #ff4d4d; 
            text-decoration: none; 
            padding: 8px 16px; 
            border: 1px solid #ff4d4d; 
            border-radius: 50px; 
            transition: all 0.3s ease;
            font-size: 0.85rem;
            display: inline-block;
        }

        .btn-delete:hover { 
            background: #ff4d4d; 
            color: white; 
            box-shadow: 0 0 15px rgba(255, 77, 77, 0.4); 
        }

        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; margin: 0px}
    </style>
</head>
<body>

    <div class="sidebar">
        <video autoplay muted loop class="sidebar-video">
            <source src="../../assets/aquarela_bg.mp4" type="video/mp4">
        </video>
        <div class="sidebar-content">
            <h2>PIGMENT</h2>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_users.php">Manage Users</a>
            <a href="manage_organisers.php">Manage Organisers</a>
            <a href="manage_events.php" style="color: var(--accent);">Manage Events</a>
            <a href="../../logout.php" style="color: #ff5555; margin-top: 40px; border: none;">Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="header-flex">
            <h1>Event Management</h1>
            <span style="opacity: 0.6; font-size: 0.9rem;">Total Events: <?php echo mysqli_num_rows($result); ?></span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Price</th>
                        <th>Organizer</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($event = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($event['e_Title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($event['e_Location']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($event['e_Date'])); ?></td>
                        <td>€ <?php echo number_format($event['e_Price'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($event['o_Name'] ?? 'System/Admin'); ?></td>
                        <td>
                            <a href="manage_events.php?delete=<?php echo $event['event_ID']; ?>" 
                               class="btn-delete"
                               onclick="return confirm('Sure you want to delete this event?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>