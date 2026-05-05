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

    // Recent 3 events for activity feed
    $stmt6 = mysqli_prepare($conn, 'SELECT e_Title, e_Date, event_ID FROM event WHERE organiser_ID = ? ORDER BY event_ID DESC LIMIT 3');
    mysqli_stmt_bind_param($stmt6, 'i', $organiser_ID);
    mysqli_stmt_execute($stmt6);
    $result6 = mysqli_stmt_get_result($stmt6);
    $recent_events = mysqli_fetch_all($result6, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt6);




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="../../css/style.css">
    <meta charset="UTF-8">
    <title>Organiser Dashboard</title>
</head>
<body> 
    
    <nav>
        <a href="organiser_dashboard.php">Dashboard</a>
        <a href="organiser_add.php">Add Event</a>
        <a href="organiser_list.php">My Events</a>
        <a href="../../logout.php">Logout</a>
    </nav>

    <header class="dashboard-header" data-aos="fade-up">
        <div class="dashboard-header-text">
            <h1>Welcome, <?php echo htmlspecialchars($o_Name); ?></h1>
            <p class="subtitle"><?php echo htmlspecialchars($o_Company); ?></p>
        </div>
        <a href="organiser_add.php" class="cta">+ Create New Event</a>
    </header>

    <section class="stats" data-aos="fade-up">
        <div class="stat">
            <span class="stat-label">Total Events</span>
            <span class="stat-value"><?= $total_events; ?></span>
        </div>
        <div class="stat">
            <span class="stat-label">Upcoming</span>
            <span class="stat-value"><?= $upcoming_events; ?></span>
        </div>
        <div class="stat">
            <span class="stat-label">Tickets Sold</span>
            <span class="stat-value"><?= $tickets_sold; ?></span>
        </div>
    </section>

    <?php if ($next_event): ?>
        <section class="next-event" data-aos="fade-up">
            <h2>Next Up</h2>
            <article class="next-event-card">
                <?php if (!empty($next_event['e_Image'])): ?>
                    <div class="next-event-image" style="background-image: url('../../<?= htmlspecialchars($next_event['e_Image']) ?>');"></div>
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
        <section class="recent-feed" data-aos="fade-up">
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

    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true, offset: 50 });</script>
</body>
</html>