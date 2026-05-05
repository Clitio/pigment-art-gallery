<?php 
session_start();
require_once 'dbconnect.php';
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'organiser') {
        header("Location: ../../login.php");
        exit();
    }
    $organiser_ID = $_SESSION['organiser_ID'];
    $success = "";
    $errors  = [];

    // Fetch events for the organiser
    $stmt = mysqli_prepare($conn,"SELECT event_ID, e_Title, e_Location, e_Date, e_Time, e_Price, e_Description FROM event WHERE organiser_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $organiser_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    


  
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Events</title>
</head>
<body>
    <h2>My Events</h2>

    <nav>
        <a href="organiser_dashboard.php">Dashboard</a> |
        <a href="organiser_add.php">Add Event</a> |
        <a href="organiser_list.php">My Events</a> |
        <a href="../../logout.php">Logout</a>
    </nav>

    <form method ="POST" action="organiser_list.php">
        <label for="search">Search Events:</label>
        <input type="text" id="search" name="search" placeholder="Enter event title...">
        <button type="submit">Search</button>

    </form>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Event ID</th>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['event_ID']); ?></td>
                        <td><?php echo htmlspecialchars($row['e_Title']); ?></td>
                        <td><?php echo htmlspecialchars($row['e_Location']); ?></td>
                        <td><?php echo htmlspecialchars($row['e_Date']); ?></td>
                        <td><?php echo htmlspecialchars($row['e_Time']); ?></td>
                        <td><?php echo htmlspecialchars($row['e_Price']); ?></td>
                        <td><?php echo htmlspecialchars($row['e_Description']); ?></td>
                        <td>
                            <a href ="organiser_update.php?event_ID=<?php echo htmlspecialchars($row['event_ID']); ?>">Edit</a>
                
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No events found.</p>
    <?php endif; ?>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
