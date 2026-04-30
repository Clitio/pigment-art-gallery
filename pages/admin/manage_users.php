<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM user WHERE user_ID = $id");
    header("Location: manage_users.php");
}

$users = mysqli_query($conn, "SELECT * FROM user");
?>

<!DOCTYPE html>
<html>
<head><title>Manage Users</title></head>
<body>
    <h2>Users</h2>
    <a href="admin_dashboard.php">Return</a>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Date of Birth</th>
            <th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($users)): ?>
        <tr>
            <td><?php echo $row['user_ID']; ?></td>
            <td><?php echo $row['f_Name'] . " " . $row['l_Name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['dOb']; ?></td>
            <td>
                <a href="manage_users.php?delete=<?php echo $row['user_ID']; ?>" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>