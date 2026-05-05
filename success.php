<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['user_ID']) || !isset($_GET['event_id'])) {
    header("Location: catalog.php");
    exit();
}

$event_id = intval($_GET['event_id']);

// Fetch the event title just to show it in the message
$stmt = $conn->prepare("SELECT e_Title FROM event WHERE event_ID = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event_name = $stmt->get_result()->fetch_assoc()['e_Title'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmed | Pigment</title>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; font-family: 'Segoe UI', sans-serif; color: white; overflow: hidden; }
        .bg-video { position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 0; }
        
        .container {
            position: relative; z-index: 1;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100vh; text-align: center;
        }

        .success-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 50px;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 500px;
            transform: translateY(20px);
            animation: slideUp 0.8s forwards;
        }

        @keyframes slideUp {
            to { transform: translateY(0); opacity: 1; }
        }

        .icon { font-size: 4rem; color: #00f2ff; margin-bottom: 20px; }
        h1 { margin: 0 0 10px 0; letter-spacing: 2px; }
        p { opacity: 0.8; margin-bottom: 30px; line-height: 1.6; }

        .btn-group { display: flex; gap: 15px; justify-content: center; }
        .btn {
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .btn-dash { background: #00f2ff; color: #000; }
        .btn-dash:hover { background: #fff; transform: scale(1.05); }
        .btn-back { border: 1px solid rgba(255,255,255,0.3); color: #fff; }
        .btn-back:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <video autoplay muted loop class="bg-video">
        <source src="assets/aquarela_bg.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>

    <div class="container">
        <div class="success-card">
            <div class="icon">✓</div>
            <h1>Reservation Confirmed!</h1>
            <p>Your spot for <strong><?php echo htmlspecialchars($event_name); ?></strong> has been secured. We've added the ticket to your collection.</p>
            
            <div class="btn-group">
                <a href="pages/user/user_dashboard.php" class="btn btn-dash">View My Dashboard</a>
                <a href="catalog.php" class="btn btn-back">Return to Gallery</a>
            </div>
        </div>
    </div>
</body>
</html>