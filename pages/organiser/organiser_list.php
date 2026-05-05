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
    $stmt = mysqli_prepare($conn,"SELECT event_ID, e_Title, e_Location, e_Date, e_Time, e_Price, e_Description, e_Image FROM event WHERE organiser_ID = ? ORDER BY e_Date ASC");
    mysqli_stmt_bind_param($stmt, "i", $organiser_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    


  
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <meta charset="UTF-8">
    <title>My Events</title>
</head>
<body>
    <h2>My Events</h2>

    <nav>
        <a href="organiser_dashboard.php">Dashboard</a> 
        <a href="organiser_add.php">Add Event</a> 
        <a href="organiser_list.php">My Events</a> 
        <a href="../../logout.php">Logout</a>
    </nav>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <section class="events-grid">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <article class="event-card" data-aos="fade-up">
                    <?php if (!empty($row['e_Image'])): ?>
                        <a href="../../<?= htmlspecialchars($row['e_Image']) ?>" class="event-image-link glightbox" data-gallery="events" data-title="<?= htmlspecialchars($row['e_Title']) ?>">
                            <div class="event-image" style="background-image: url('../../<?= htmlspecialchars($row['e_Image']) ?>');"></div>
                        </a>
                    <?php else: ?>
                        <div class="event-image event-image-placeholder">
                            <span class="placeholder-text">No image</span>
                        </div>
                    <?php endif; ?>
                    <div class="event-content">
                        <h2 class="event-title"><?php echo htmlspecialchars($row['e_Title']); ?></h2>
                        <p class="event-meta">
                            <?php echo date('d M Y', strtotime($row['e_Date'])); ?>
                            <?php if (!empty($row['e_Location'])): ?>
                                &nbsp;·&nbsp; <?php echo htmlspecialchars($row['e_Location']); ?>
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($row['e_Description'])): ?>
                            <p class="event-description"><?php echo htmlspecialchars($row['e_Description']); ?></p>
                        <?php endif; ?>
                        <div class="event-footer">
                            <span class="event-price">€<?php echo number_format($row['e_Price'], 2); ?></span>
                            <a href="organiser_update.php?event_ID=<?php echo htmlspecialchars($row['event_ID']); ?>" class="event-edit">Edit</a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>
    <?php else: ?>
        <p class="empty-state">No events found. <a href="organiser_add.php">Create your first event</a>.</p>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        AOS.init({ duration: 800, once: true, offset: 50 });
        const lightbox = GLightbox({ selector: '.glightbox', loop: true });
    </script>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
