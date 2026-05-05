<?php
session_start();
require_once 'dbconnect.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organiser') {
    header("Location: ../../login.php");
    exit();
}
$organiser_ID = $_SESSION['organiser_ID'];

// Organiser profile (for sidebar)
$stmt_org = mysqli_prepare($conn, 'SELECT o_Name, o_Company FROM organiser WHERE organiser_ID = ?');
mysqli_stmt_bind_param($stmt_org, 'i', $organiser_ID);
mysqli_stmt_execute($stmt_org);
mysqli_stmt_bind_result($stmt_org, $o_Name, $o_Company);
mysqli_stmt_fetch($stmt_org);
mysqli_stmt_close($stmt_org);
$initials = strtoupper(substr($o_Name ?? 'O', 0, 1));

// Fetch events
$stmt = mysqli_prepare($conn, "SELECT event_ID, e_Title, e_Location, e_Date, e_Time, e_Price, e_Description, e_Image FROM event WHERE organiser_ID = ? ORDER BY e_Date ASC");
mysqli_stmt_bind_param($stmt, "i", $organiser_ID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - Pigment</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
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
            <div class="panel-header">
                <div>
                    <h1>My Events</h1>
                    <p>All of your exhibitions and experiences.</p>
                </div>
                <a href="organiser_add.php" class="cta">+ New Event</a>
            </div>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <section class="events-grid">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <article class="event-card">
                            <?php if (!empty($row['e_Image'])): ?>
                                <a href="../../<?= htmlspecialchars($row['e_Image']) ?>" class="glightbox" data-gallery="events" data-title="<?= htmlspecialchars($row['e_Title']) ?>">
                                    <div class="event-image" style="background-image: url('../../<?= htmlspecialchars($row['e_Image']) ?>');"></div>
                                </a>
                            <?php else: ?>
                                <div class="event-image event-image-placeholder">
                                    <span class="placeholder-text">No image</span>
                                </div>
                            <?php endif; ?>
                            <div class="event-content">
                                <h2 class="event-title"><?= htmlspecialchars($row['e_Title']) ?></h2>
                                <p class="event-meta">
                                    <?= date('d M Y', strtotime($row['e_Date'])) ?>
                                    <?php if (!empty($row['e_Location'])): ?>
                                        &nbsp;·&nbsp; <?= htmlspecialchars($row['e_Location']) ?>
                                    <?php endif; ?>
                                </p>
                                <?php if (!empty($row['e_Description'])): ?>
                                    <p class="event-description"><?= htmlspecialchars($row['e_Description']) ?></p>
                                <?php endif; ?>
                                <div class="event-footer">
                                    <span class="event-price">€<?= number_format($row['e_Price'], 2) ?></span>
                                    <a href="organiser_update.php?event_ID=<?= htmlspecialchars($row['event_ID']) ?>" class="event-edit">Edit</a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </section>
            <?php else: ?>
                <p class="empty-state">No events yet. <a href="organiser_add.php">Create your first event</a>.</p>
            <?php endif; ?>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script src="organiser.js"></script>
    <script>
        const lightbox = GLightbox({ selector: '.glightbox', loop: true });
    </script>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
