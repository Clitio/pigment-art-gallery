<?php
require_once 'dbconnect.php';
session_start();
if (!isset($_SESSION['user_ID'])){
    die("You need to be logged in to book.");
}

$user_ID = $_SESSION['user_ID'];

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $event_ID = $_POST['event_ID'];

    $stmt = $conn->prepare("INSERT INTO tickets (event_ID, user_ID) VALUES (?, ?)");
    $stmt->bind_param("ii", $event_ID, $user_ID);

    if ($stmt->execute()) {
        $message = "Ticket booked successfully!";
    } else {
        if ($conn->errno == 1062) {
            $message = "You already have this event booked.";
        } else {
            $message = "Error processing your booking.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking</title>
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
                justify-content: center;
                min-height: 100vh;
                padding: 40px 20px;
                box-sizing: border-box;
            }

            .card {
                width: 420px;
                max-width: calc(100% - 40px);
                padding: 40px;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(25px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 20px;
                text-align: center;
                box-sizing: border-box;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            }

            select {
                width: 100%;
                padding: 10px;
                margin: 10px 0 18px;
                border: none;
                border-radius: 5px;
                box-sizing: border-box;
            }
            button {
                width: 100%;
                padding: 12px;
                background: #ffcc00;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-weight: bold;
            }
            .actions { margin-top: 20px; }
            a { color: #ffcc00; text-decoration: none; }
        </style>
</head>
<body>
    <video autoplay muted loop id="bg-video">
        <source src="../../assets/aquarela_bg.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <div class="video-overlay"></div>

    <div class="card">
        <h1>Booking tickets</h1>
        <?php if (isset($message)) echo "<p><strong>$message</strong></p>"; ?>
        <form action="booking.php" method="POST" data-booking-form>
            <label>Choose the Event:</label>
            <select name="event_ID" required>
                <?php
                $sql = "SELECT event_ID, e_Title FROM event";
                $result = $conn->query($sql);
                while($row = $result->fetch_assoc()){
                    echo "<option value='{$row['event_ID']}'>{$row['e_Title']}</option>";
                }
                ?>
            </select>
            <button type="submit">Confirm booking</button>
        </form>
        <div class="actions">
            <a href="user_dashboard.php">Back to dashboard</a>
        </div>
    </div>
    <script src="user.js"></script>
</body>
</html>
