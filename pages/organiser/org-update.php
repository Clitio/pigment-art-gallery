<?php 
require_once 'dbconnect.php';


$success = "";
$errors  = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title        = trim($_POST["e_Title"]);
    $location     = trim($_POST["e_Location"]);
    $date         = $_POST["e_Date"];
    $time         = $_POST["e_Time"];
    $price        = $_POST["e_Price"];
    $description  = trim($_POST["e_Description"]);

    
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Event</title>
</head>
<body>  

    <h2>Update Event</h2>

    <nav>
        <a href="org-dashboard.php">Dashboard</a> |
        <a href="org-update.php">Edit Events</a> |
        <a href="org-add.php">Add Event</a>
        <a href="org-list.php">My Events</a>
    </nav>

    <?php if ($success): ?>
        <p style="color: green;"><?= $success ?></p>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Title</label><br>
        <input type="text" name="e_Title" required><br><br>

        <label>Location</label><br>
        <input type="text" name="e_Location"><br><br>

        <label>Date</label><br>
        <input type="date" name="e_Date"><br><br>

        <label>Time</label><br>
        <input type="time" name="e_Time"><br><br>

        <label>Price</label><br>
        <input type="number" name="e_Price" step="0.01" min="0"><br><br>

        <label>Description</label><br>
        <textarea name="e_Description" rows="4" cols="40"></textarea><br><br>

        <label>Image</label><br>
        <input type="file" name="e_Image" accept="image/*"><br><br>

        <button type="submit">Update Event</button>
    </form>
</body>
</html>
