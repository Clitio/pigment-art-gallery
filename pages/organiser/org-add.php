<?php
require_once 'dbconnect.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title        = trim($_POST["e_Title"]);
    $location     = trim($_POST["e_Location"]);
    $date         = $_POST["e_Date"];
    $time         = $_POST["e_Time"];
    $price        = $_POST["e_Price"];
    $description  = trim($_POST["e_Description"]);
    $organiser_ID = 1; // temporary until login is built

    $imagePath = "";
    if (isset($_FILES["e_Image"]) && $_FILES["e_Image"]["error"] == 0) {
        $uploadDir = "../../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename   = time() . "_" . basename($_FILES["e_Image"]["name"]);
        $targetPath = $uploadDir . $filename;

        $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
        if (!in_array($_FILES["e_Image"]["type"], $allowedTypes)) {
            $error = "Only JPG, PNG, GIF, and WEBP images are allowed.";
        } elseif (!move_uploaded_file($_FILES["e_Image"]["tmp_name"], $targetPath)) {
            $error = "Failed to upload image.";
        } else {
            $imagePath = "uploads/" . $filename;
        }
    }

    if ($error == "") {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO event (e_Title, e_Location, e_Date, e_Time, e_Price, e_Description, e_Image, organiser_ID)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssssdssi",
            $title, $location, $date, $time, $price, $description, $imagePath, $organiser_ID
        );

        if (mysqli_stmt_execute($stmt)) {
            $success = "Event created successfully!";
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Event</title>
</head>
<body>

    <h2>Create New Event</h2>

    <?php if ($success): ?>
        <p style="color: green;"><?= $success ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: red;"><?= $error ?></p>
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

        <button type="submit">Create Event</button>
    </form>

</body>
</html>
