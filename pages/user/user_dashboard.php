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
    <title>My Panel - Pigment Art Gallery</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 40px 20px;
            font-family: sans-serif;
            box-sizing: border-box;
        }
        .page {
            width: 760px;
            max-width: 100%;
        }
        .welcome {
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 10px;
            text-align: center;
        }
        .nav-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 18px;
        }
        .card-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .card {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        h2 { margin-top: 35px; text-align: center; }
    </style>
</head>
<body>

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
