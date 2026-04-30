<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM organiser WHERE organiser_ID = $id");
    header("Location: manage_organisers.php");
}

$query = "SELECT * FROM organiser";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Manage Organisers</title>
</head>
<body>
    <h2>Organisers</h2>
    <p><a href="admin_dashboard.php">Return to Panel</a></p>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Company</th>
                <th>E-mail</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($org = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $org['organiser_ID']; ?></td>
                <td><?php echo $org['o_Name']; ?></td>
                <td><?php echo $org['o_Company']; ?></td>
                <td><?php echo $org['o_email']; ?></td>
                <td>
                    <a href="manage_organisers.php?delete=<?php echo $org['organiser_ID']; ?>" 
                       onclick="return confirm('Are you sure? This will delete all events from this organiser')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>