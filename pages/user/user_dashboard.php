<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['user_ID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../../login.php");
    exit();
}

$user_ID = $_SESSION['user_ID'];

$sql_user = "SELECT f_Name, l_Name, email FROM user WHERE user_ID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_ID);
$stmt_user->execute();
$user_query_user = $stmt_user->get_result();

if ($user_query_user->num_rows === 0) {
    die("Error: User not found on database.");
}

$user_data = $user_query_user->fetch_assoc();

$sql_bookings = "SELECT e.e_Title, e.e_Date, e.e_Location
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Panel - Pigment Art Gallery</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
        }

        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -2;
            object-fit: cover;
        }

        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            z-index: -1;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .page {
            width: 760px;
            max-width: 100%;
        }

        .welcome {
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        a {
            color: #ffcc00;
            text-decoration: none;
            font-weight: bold;
        }

        .card-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .card {
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        }

        h2 { margin-top: 35px; text-align: center; }
    </style>
</head>
<body>

    <video autoplay muted loop id="bg-video">
        <source src="../../assets/aquarela_bg.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <div class="video-overlay"></div>

    <div class="page">
        <div class="welcome">
            <h1>Hello, <?php echo htmlspecialchars($user_data['f_Name']); ?>!</h1>
            <p>Email: <?php echo htmlspecialchars($user_data['email']); ?></p>
            <div class="nav-links">
                <a href="../../logout.php">Logout</a>
                <a href="booking.php">Book new events</a>
                <a href="user-update.php">Update profile</a>
            </div>
        </div>

        <h2>My bookings</h2>

        <div class="card-container">
            <?php if ($bookings->num_rows > 0): ?>
                <?php while ($ticket = $bookings->fetch_assoc()): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($ticket['e_Title']); ?></h3>
                        <p><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($ticket['e_Date'])); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($ticket['e_Location']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <p>You didn't book any event <a href="booking.php">Click here to see your gallery.</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
