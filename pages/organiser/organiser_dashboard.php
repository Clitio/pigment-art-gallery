<?php
session_start();
require_once 'dbconnect.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organiser') {
    header("Location: ../../login.php");
    exit();
}
$organiser_ID = $_SESSION['organiser_ID'];
$o_Name = '';
$o_Company = '';

// Organiser profile
$stmt = mysqli_prepare($conn, 'SELECT o_Name, o_Company FROM organiser WHERE organiser_ID = ?');
mysqli_stmt_bind_param($stmt, 'i', $organiser_ID);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $o_Name, $o_Company);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Total events
$stmt2 = mysqli_prepare($conn, 'SELECT COUNT(*) AS total_events FROM event WHERE organiser_ID = ?');
mysqli_stmt_bind_param($stmt2, 'i', $organiser_ID);
mysqli_stmt_execute($stmt2);
mysqli_stmt_bind_result($stmt2, $total_events);
mysqli_stmt_fetch($stmt2);
mysqli_stmt_close($stmt2);

// Upcoming events count
$stmt3 = mysqli_prepare($conn, 'SELECT COUNT(*) FROM event WHERE organiser_ID = ? AND e_Date >= CURDATE()');
mysqli_stmt_bind_param($stmt3, 'i', $organiser_ID);
mysqli_stmt_execute($stmt3);
mysqli_stmt_bind_result($stmt3, $upcoming_events);
mysqli_stmt_fetch($stmt3);
mysqli_stmt_close($stmt3);

// Tickets sold across all my events
$stmt4 = mysqli_prepare($conn, 'SELECT COUNT(*) FROM tickets t JOIN event e ON t.event_ID = e.event_ID WHERE e.organiser_ID = ?');
mysqli_stmt_bind_param($stmt4, 'i', $organiser_ID);
mysqli_stmt_execute($stmt4);
mysqli_stmt_bind_result($stmt4, $tickets_sold);
mysqli_stmt_fetch($stmt4);
mysqli_stmt_close($stmt4);

// Next upcoming event (one row)
$stmt5 = mysqli_prepare($conn, 'SELECT e_Title, e_Date, e_Location, e_Image, event_ID FROM event WHERE organiser_ID = ? AND e_Date >= CURDATE() ORDER BY e_Date ASC LIMIT 1');
mysqli_stmt_bind_param($stmt5, 'i', $organiser_ID);
mysqli_stmt_execute($stmt5);
$result5 = mysqli_stmt_get_result($stmt5);
$next_event = mysqli_fetch_assoc($result5);
mysqli_stmt_close($stmt5);

// Recent 3 events
$stmt6 = mysqli_prepare($conn, 'SELECT e_Title, e_Date, event_ID FROM event WHERE organiser_ID = ? ORDER BY event_ID DESC LIMIT 3');
mysqli_stmt_bind_param($stmt6, 'i', $organiser_ID);
mysqli_stmt_execute($stmt6);
$result6 = mysqli_stmt_get_result($stmt6);
$recent_events = mysqli_fetch_all($result6, MYSQLI_ASSOC);
mysqli_stmt_close($stmt6);

$initials = strtoupper(substr($o_Name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organiser Dashboard - Pigment</title>
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
                <a href="organiser_dashboard.php" class="active">Dashboard</a>
                <a href="organiser_list.php">My events</a>
                <a href="organiser_add.php">Add new event</a>
                <a href="../../logout.php" data-confirm-logout>Logout</a>
            </div>
        </aside>

        <section class="content-panel">
            <div class="panel-header">
                <div>
                    <h1>Welcome, <?= htmlspecialchars($o_Name) ?></h1>
                    <p>Manage your gallery experiences in one place.</p>
                </div>
                <a href="organiser_add.php" class="cta">+ New Event</a>
            </div>

            <section class="stats">
                <div class="stat">
                    <span class="stat-label">Total Events</span>
                    <span class="stat-value"><?= $total_events ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Upcoming</span>
                    <span class="stat-value"><?= $upcoming_events ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Tickets Sold</span>
                    <span class="stat-value"><?= $tickets_sold ?></span>
                </div>
            </section>

            <?php if ($next_event): ?>
                <section class="next-event">
                    <h2>Next Up</h2>
                    <article class="next-event-card">
                        <?php if (!empty($next_event['e_Image'])): ?>
                            <div class="next-event-image" style="background-image: url('../../<?= htmlspecialchars($next_event['e_Image']) ?>');"></div>
                        <?php else: ?>
                            <div class="next-event-image"></div>
                        <?php endif; ?>
                        <div class="next-event-content">
                            <p class="next-event-meta">
                                <?= date('l, d F Y', strtotime($next_event['e_Date'])) ?>
                                <?php if (!empty($next_event['e_Location'])): ?>
                                    &nbsp;·&nbsp; <?= htmlspecialchars($next_event['e_Location']) ?>
                                <?php endif; ?>
                            </p>
                            <h3 class="next-event-title"><?= htmlspecialchars($next_event['e_Title']) ?></h3>
                            <a href="organiser_update.php?event_ID=<?= $next_event['event_ID'] ?>" class="next-event-link">Manage event &rarr;</a>
                        </div>
                    </article>
                </section>
            <?php endif; ?>

            <?php if (!empty($recent_events)): ?>
                <section class="recent-feed">
                    <h2>Recent Activity</h2>
                    <ul class="recent-list">
                        <?php foreach ($recent_events as $ev): ?>
                            <li class="recent-item">
                                <span class="recent-date"><?= date('d M', strtotime($ev['e_Date'])) ?></span>
                                <span class="recent-title"><?= htmlspecialchars($ev['e_Title']) ?></span>
                                <a href="organiser_update.php?event_ID=<?= $ev['event_ID'] ?>" class="recent-link">Edit</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        </section>
    </main>

    <script src="organiser.js"></script>
</body>
</html>
