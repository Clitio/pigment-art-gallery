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
$event_ID = $_GET['event_ID'] ?? null;

    $stmt = mysqli_prepare($conn,"SELECT event_ID, e_Title, e_Location, e_Date, e_Time, e_Price, e_Description, e_Image FROM event WHERE event_ID = ? AND organiser_ID = ?");
    mysqli_stmt_bind_param($stmt, "ii", $event_ID, $organiser_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);

if (!$event) {
    header("Location: organiser_list.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title        = trim($_POST["e_Title"]);
    $location     = trim($_POST["e_Location"]);
    $date         = $_POST["e_Date"];
    $time         = $_POST["e_Time"];
    $price        = $_POST["e_Price"];
    $description  = trim($_POST["e_Description"]);
    $event_ID     = $_GET['event_ID'];

        // Title
    if (strlen($title) == 0) {
        $errors[] = "Title is required.";
    } elseif (strlen($title) > 150) {
        $errors[] = "Title cannot exceed 150 characters.";
    }

    // Location
    if ($location === "") {
        $location = null;
    } elseif (strlen($location) > 255) {
        $errors[] = "Location cannot exceed 255 characters.";
    }

    // Date — only validate if provided
    if ($date !== "") {
        $parsedDate = DateTime::createFromFormat('Y-m-d', $date);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
            $errors[] = "Invalid date format.";
        }
    }

    // Time — only validate if provided
    if ($time !== "") {
        $parsedTime = DateTime::createFromFormat('H:i', $time);
        if (!$parsedTime || $parsedTime->format('H:i') !== $time) {
            $errors[] = "Invalid time format.";
        }
    }

    // Price — only validate if provided
    if ($price !== "") {
        if (!is_numeric($price) || (float)$price < 0) {
            $errors[] = "Price must be a positive number.";
        }
    }

    // Description
    if (strlen($description) > 1000) {
        $errors[] = "Description cannot exceed 1000 characters.";
    }


    // Image Validation: Check if an image file is uploaded, ensure it is a valid image type, and move it to the uploads directory.
    // got this logic from Claude code, but modified it to fit our database structure and requirements. 
    $imagePath = $event['e_Image']; // Default to existing image path
    if (isset($_FILES["e_Image"]) && $_FILES["e_Image"]["error"] == 0) {
        if ($_FILES["e_Image"]["size"] > 5 * 1024 * 1024) {
            $errors[] = "Image must be 5 MB or smaller.";
        } else {
            $finfo      = new finfo(FILEINFO_MIME_TYPE);
            $actualMime = $finfo->file($_FILES["e_Image"]["tmp_name"]);

            $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
            if (!in_array($actualMime, $allowedTypes)) {
                $errors[] = "Only JPG, PNG, GIF, and WEBP images are allowed.";
            } else {
                $uploadDir = __DIR__ . "/../../uploads/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename   = time() . "_" . basename($_FILES["e_Image"]["name"]);
                $targetPath = $uploadDir . $filename;

                if (!move_uploaded_file($_FILES["e_Image"]["tmp_name"], $targetPath)) {
                    $errors[] = "Failed to upload image.";
                } else {
                    $imagePath = "uploads/" . $filename;
                }
            }
        }
    }

// If no validation errors, proceed to update the event in the database
    
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "UPDATE event SET e_Title = ?, e_Location = ?, e_Date = ?, e_Time = ?, e_Price = ?, e_Description = ?, e_Image = ? WHERE event_ID = ? AND organiser_ID = ?");
        mysqli_stmt_bind_param($stmt, "ssssdssii", $title, $location, $date, $time, $price, $description, $imagePath, $event_ID, $organiser_ID);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Event updated successfully.";
            // Refresh event data after update
            $event = [
                'e_Title' => $title,
                'e_Location' => $location,
                'e_Date' => $date,
                'e_Time' => $time,
                'e_Price' => $price,
                'e_Description' => $description,
                'e_Image' => $imagePath
            ];
 
        } else {
            $errors[] = "Failed to update event. Please try again.";
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    
        mysqli_stmt_close($stmt);
    }
}


?>


<?php
$stmt_org = mysqli_prepare($conn, 'SELECT o_Name, o_Company FROM organiser WHERE organiser_ID = ?');
mysqli_stmt_bind_param($stmt_org, 'i', $organiser_ID);
mysqli_stmt_execute($stmt_org);
mysqli_stmt_bind_result($stmt_org, $o_Name, $o_Company);
mysqli_stmt_fetch($stmt_org);
mysqli_stmt_close($stmt_org);
$initials = strtoupper(substr($o_Name ?? 'O', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Pigment</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

    <video autoplay muted loop id="bg-video">
        <source src="../../assets/aquarela_bg.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <main class="page">
        <aside class="sidebar">
            <h1 class="brand">PIGMENT</h1>
            <div class="avatar"><?= htmlspecialchars($initials) ?></div>
            <h2 class="profile-name"><?= htmlspecialchars($o_Name) ?></h2>
            <p class="profile-company"><?= htmlspecialchars($o_Company) ?></p>

            <div class="nav-links">
                <a href="organiser_dashboard.php">Dashboard</a>
                <a href="organiser_list.php" class="active">My events</a>
                <a href="organiser_add.php">Add new event</a>
                <a href="../../logout.php" data-confirm-logout>Logout</a>
            </div>
        </aside>

        <section class="content-panel">
            <header class="form-header">
                <h1>Edit Event</h1>
                <p><?= htmlspecialchars($event['e_Title']) ?></p>
            </header>

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

            <form method="POST" enctype="multipart/form-data" class="event-form">
                <input type="hidden" name="event_ID" value="<?= htmlspecialchars($event_ID) ?>">

                <label>Title</label>
                <input type="text" name="e_Title" required value="<?= isset($event['e_Title']) ? htmlspecialchars($event['e_Title']) : '' ?>">

                <label>Location</label>
                <input type="text" name="e_Location" value="<?= isset($event['e_Location']) ? htmlspecialchars($event['e_Location']) : '' ?>">

                <div class="form-row">
                    <div>
                        <label>Date</label>
                        <input type="date" name="e_Date" value="<?= isset($event['e_Date']) ? htmlspecialchars($event['e_Date']) : '' ?>">
                    </div>
                    <div>
                        <label>Time</label>
                        <input type="time" name="e_Time" value="<?= isset($event['e_Time']) ? htmlspecialchars($event['e_Time']) : '' ?>">
                    </div>
                </div>

                <label>Price (€)</label>
                <input type="number" name="e_Price" step="0.01" min="0" value="<?= isset($event['e_Price']) ? htmlspecialchars($event['e_Price']) : '' ?>">

                <label>Description</label>
                <textarea name="e_Description" rows="5"><?= isset($event['e_Description']) ? htmlspecialchars($event['e_Description']) : '' ?></textarea>

                <label>Cover Image</label>
                <?php if (!empty($event['e_Image'])): ?>
                    <img src="../../<?= htmlspecialchars($event['e_Image']) ?>" alt="Current Image" class="current-image">
                <?php endif; ?>
                <input type="file" name="e_Image" accept="image/*">

                <div class="form-actions">
                    <a href="organiser_list.php" class="secondary-link">&larr; Back to events</a>
                    <button type="submit">Save Changes</button>
                </div>
            </form>
        </section>
    </main>

    <script src="organiser.js"></script>
</body>
</html>
