<?php
session_start();
require_once 'dbconnect.php';

//ACCESS CONTROL: Verify user is logged in AND has the 'admin' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

//DELETE LOGIC: Check if a delete ID was passed in the URL (GET)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM event WHERE event_ID = $id");
    header("Location: manage_events.php");
}

//FETCH LOGIC: Get events and join with organiser table to show names instead of IDs
$sql = "SELECT e.*, o.o_Name FROM event e 
        LEFT JOIN organiser o ON e.organiser_ID = o.organiser_ID";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Manage Events</title>
</head>
<body>
    <h2>Events management</h2>
    <p><a href="admin_dashboard.php">Return</a></p>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Title</th>
                <th>Local</th>
                <th>Date</th>
                <th>Price</th>
                <th>Organizer</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($event = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $event['e_Title']; ?></td>
                <td><?php echo $event['e_Location']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($event['e_Date'])); ?></td>
                <td>€ <?php echo number_format($event['e_Price'], 2, ',', '.'); ?></td>
                <td><?php echo $event['o_Name'] ?? 'Sistema/Admin'; ?></td>
                <td>
                    <a href="manage_events.php?delete=<?php echo $event['event_ID']; ?>" 
                       onclick="return confirm('Sure you want to delete this event?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>