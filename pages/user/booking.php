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
        <title>Booking</title>
        <style>
            body {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                font-family: sans-serif;
            }
            .card {
                width: 420px;
                max-width: calc(100% - 40px);
                padding: 30px;
                border: 1px solid #ccc;
                border-radius: 10px;
                text-align: center;
                box-sizing: border-box;
            }
            select {
                width: 100%;
                padding: 8px;
                margin: 10px 0 18px;
                box-sizing: border-box;
            }
            button { padding: 10px 16px; cursor: pointer; }
            .actions { margin-top: 20px; }
        </style>
</head>
<body>
    <div class="card">
        <h1>Booking tickets</h1>
        <?php if (isset($message)) echo "<p><strong>$message</strong></p>"; ?>
        <form action="booking.php" method="POST">
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
</body>
</html>
