<?php
session_start();
require_once 'dbconnect.php'; 


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_ID = $_SESSION['user_ID'];


$sql_user = "SELECT f_Name, l_Name, email FROM user WHERE user_ID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_ID);
$stmt_user->execute();
$user_data = $stmt_user->get_result();

if ($result_user->num_rows === 0) {
    die("Error: User not found on database.");
}

$user_data = $result_user->fetch_assoc();

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
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>My Panel - Pigment Art Gallery</title>
    <style>
        .card { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 8px; }
        .welcome { background-color: #f4f4f4; padding: 20px; border-radius: 8px; }
    </style>
</head>
<body>

    <div class="welcome">
        <h1>Hello, <?php echo htmlspecialchars($user_data['f_Name']); ?>!</h1>
        <p>Email: <?php echo htmlspecialchars($user_data['email']); ?></p>
       <a href="../../logout.php">Logout</a> | <a href="booking.php">Book new events</a>
    </div>

    <h2>My bookings</h2>

    <?php if ($bookings->num_rows > 0): ?>
        <?php while($ticket = $bookings->fetch_assoc()): ?>
            <div class="card">
                <h3>🎨 <?php echo htmlspecialchars($ticket['e_Title']); ?></h3>
                <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($ticket['e_Date'])); ?></p>
                <p><strong>Local:</strong> <?php echo htmlspecialchars($ticket['e_Location']); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You didn't book any event <a href="booking.php">Click here to see your gallery.</a></p>
    <?php endif; ?>

</body>
</html>