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
        $message = "Ingresso reservado com sucesso!";
    } else {
        if ($conn->errno == 1062) {
            $message = "You already have this event booked.";
        } else {
            $message = "Error to process your booking.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Booking</title>
</head>
<body>
    <h1>Booking tickets</h1>
    <?php if (isset($message)) echo "<p><strong>$message</strong></p>"; ?>
    <form action="booking.php" method="POST">
        <label>Choose the Event:</label>
        <select name="event_ID" required>
            <?php
            $sql = "SELECT event_ID, e_Title FROM event";
            $result = $conn->query($sql);
            while($row = $result->fetch_assoc()){
                echo "<option value='{$row['event_ID']}'>{$row['e_title']}</options>";
            }
            ?>
            </select>
            <button type="submit">Confirm booking</button>
</form>
</body>
</html>