<?php
session_start();
require_once '../../dbconnect.php'; 

if (!isset($_SESSION['user_ID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../../login.php");
    exit();
}

$user_ID = $_SESSION['user_ID'];

// Fetch user data
$sql_user = "SELECT f_Name, l_Name, email FROM user WHERE user_ID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_ID);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();

// Fetch bookings with event_ID for the cancel feature
$sql_bookings = "SELECT e.event_ID, e.e_Title, e.e_Date, e.e_Location
                 FROM tickets t
                 JOIN event e ON t.event_ID = e.event_ID
                 WHERE t.user_ID = ?";
$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param("i", $user_ID);
$stmt_bookings->execute();
$bookings = $stmt_bookings->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Panel - Pigment Art Gallery</title>
    <style>
        body, html {
            margin: 0; padding: 0; min-height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: white;
        }
        #bg-video {
            position: fixed; top: 0; left: 0;
            min-width: 100%; min-height: 100%;
            z-index: -2; object-fit: cover;
        }
        .video-overlay {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }
        body {
            display: flex; flex-direction: column;
            align-items: center; min-height: 100vh;
            padding: 40px 20px; box-sizing: border-box;
        }
        .page { width: 760px; max-width: 100%; }
        .welcome {
            padding: 30px; background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px; text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .nav-links { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 18px; }
        
        /* Links style */
        a { color: #ffcc00; text-decoration: none; font-weight: bold; transition: 0.3s; }
        a:hover { text-shadow: 0 0 10px #ffcc00; }

        .card-container { display: flex; flex-direction: column; gap: 15px; margin-top: 25px; }
        .card {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px; background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        }
        .btn-cancel {
            color: #ff4444; border: 1px solid #ff4444;
            padding: 5px 12px; border-radius: 5px; font-size: 0.75rem;
            text-transform: uppercase;
        }
        .btn-cancel:hover { background: #ff4444; color: white; }
        h2 { margin-top: 35px; text-align: center; letter-spacing: 1px; }
        .status-msg { color: #00f2ff; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>

    <video autoplay muted loop id="bg-video">
        <source src="../../assets/aquarela_bg.mp4" type="video/mp4">
    </video>

    <div class="video-overlay"></div>

    <div class="page">
        <div class="welcome">
            <?php if(isset($_GET['status']) && $_GET['status'] == 'cancelled'): ?>
                <p class="status-msg">Reservation successfully removed.</p>
            <?php endif; ?>

            <h1>Hello, <?php echo htmlspecialchars($user_data['f_Name']); ?>!</h1>
            <p>Email: <?php echo htmlspecialchars($user_data['email']); ?></p>
            
            <div class="nav-links">
                <!-- Restored Update Profile Link -->
                <a href="user-update.php">Update Profile</a>
                <a href="../../catalog.php">Book New Events</a>
                <a href="../../logout.php" style="color: #ff4444;">Logout</a>
            </div>
        </div>

        <h2>My Bookings</h2>

        <div class="card-container">
            <?php if ($bookings->num_rows > 0): ?>
                <?php while ($ticket = $bookings->fetch_assoc()): ?>
                    <div class="card">
                        <div>
                            <h3 style="margin:0;"><?php echo htmlspecialchars($ticket['e_Title']); ?></h3>
                            <p style="margin: 5px 0 0 0; font-size: 0.9rem; opacity: 0.8;">
                                📅 <?php echo date('d/m/Y', strtotime($ticket['e_Date'])); ?> | 📍 <?php echo htmlspecialchars($ticket['e_Location']); ?>
                            </p>
                        </div>
                        <a href="../../cancel_reservation.php?id=<?php echo $ticket['event_ID']; ?>" 
                           class="btn-cancel" 
                           onclick="return confirm('Are you sure you want to cancel this booking?');">
                           Cancel
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="justify-content: center;">
                    <p>No events booked yet. <a href="../../catalog.php">Browse the gallery.</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>