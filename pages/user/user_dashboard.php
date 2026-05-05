<?php
session_start();
require_once '../../dbconnect.php';

if (!isset($_SESSION['user_ID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../../login.php");
    exit();
}

$user_ID = $_SESSION['user_ID'];

$sql_user = "SELECT f_Name, l_Name, email FROM user WHERE user_ID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_ID);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result->num_rows === 0) {
    die("Error: User not found on database.");
}

$user_data = $user_result->fetch_assoc();
$user_initials = strtoupper(substr($user_data['f_Name'], 0, 1) . substr($user_data['l_Name'] ?? '', 0, 1));

$sql_bookings = "SELECT e.event_ID, e.e_Title, e.e_Date, e.e_Location
                 FROM tickets t
                 JOIN event e ON t.event_ID = e.event_ID
                 WHERE t.user_ID = ?";
$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param("i", $user_ID);
$stmt_bookings->execute();
$bookings = $stmt_bookings->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Panel - Pigment Art Gallery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
        }

        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -2;
            object-fit: cover;
        }

        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .page {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 25px;
            width: 1050px;
            max-width: 100%;
        }

        .sidebar,
        .content-panel,
        .card {
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .sidebar {
            min-height: 520px;
            display: flex;
            flex-direction: column;
            text-align: center;
        }

        .brand,
        .profile-name,
        .panel-header h1,
        .event-details h3 {
            font-family: 'Playfair Display', Georgia, serif;
        }

        .brand {
            margin: 0 0 25px;
            letter-spacing: 3px;
            font-size: 1.4rem;
        }

        .avatar {
            width: 105px;
            height: 105px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 204, 0, 0.18);
            border: 2px solid #ffcc00;
            color: #ffcc00;
            font-size: 2.3rem;
            font-weight: bold;
            box-shadow: 0 0 30px rgba(255, 204, 0, 0.25);
        }

        .profile-name {
            margin: 0;
            font-size: 1.5rem;
        }

        .profile-email {
            margin: 8px 0 25px;
            opacity: 0.82;
            overflow-wrap: anywhere;
        }

        .side-menu {
            margin-top: 24px;
            text-align: left;
        }

        .menu-toggle {
            width: 100%;
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.08);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 10px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 700;
            text-align: left;
        }

        .menu-toggle::after {
            content: "+";
            float: right;
            color: #ffcc00;
            font-size: 1.1rem;
        }

        .side-menu.is-open .menu-toggle::after {
            content: "-";
        }

        .menu-options {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.3s ease;
        }

        .side-menu.is-open .menu-options {
            grid-template-rows: 1fr;
        }

        .menu-options-inner {
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding-top: 0;
            transition: padding-top 0.3s ease;
        }

        .side-menu.is-open .menu-options-inner {
            padding-top: 12px;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: auto;
        }

        a {
            color: #ffcc00;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .nav-links a,
        .menu-options a,
        .empty-state a {
            display: block;
            padding: 12px 14px;
            border: 1px solid rgba(255, 204, 0, 0.45);
            border-radius: 10px;
            background: rgba(255, 204, 0, 0.08);
        }

        .nav-links a:hover,
        .menu-options a:hover,
        .empty-state a:hover {
            background: #ffcc00;
            color: #222;
        }

        .content-panel {
            min-height: 520px;
        }

        .status-msg {
            margin: 0 0 18px;
            padding: 12px 14px;
            border-radius: 10px;
            background: rgba(0, 242, 255, 0.14);
            border: 1px solid rgba(0, 242, 255, 0.45);
            color: #00f2ff;
            font-weight: bold;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }

        .panel-header h1 {
            margin: 0;
            font-size: 2.2rem;
        }

        .panel-header p {
            margin: 8px 0 0;
            opacity: 0.82;
        }

        .booking-count {
            min-width: 110px;
            padding: 14px;
            border-radius: 15px;
            background: rgba(255, 204, 0, 0.16);
            border: 1px solid rgba(255, 204, 0, 0.45);
            text-align: center;
        }

        .booking-count strong {
            display: block;
            font-size: 2rem;
            color: #ffcc00;
        }

        .booking-count span {
            font-size: 0.85rem;
            opacity: 0.85;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 15px;
        }

        .card {
            position: relative;
            overflow: hidden;
            padding: 0;
        }

        .card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 242, 255, 0.16), rgba(255, 204, 0, 0.12));
            pointer-events: none;
        }

        .event-date {
            position: relative;
            padding: 18px;
            background: rgba(0, 0, 0, 0.28);
            border-bottom: 1px solid rgba(255, 255, 255, 0.16);
        }

        .event-date strong {
            display: block;
            font-size: 2.2rem;
            line-height: 1;
            color: #ffcc00;
        }

        .event-date span {
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.85rem;
        }

        .event-details {
            position: relative;
            padding: 20px;
        }

        .event-details h3 {
            margin: 0 0 14px;
            font-size: 1.25rem;
        }

        .event-details p {
            margin: 8px 0 0;
            opacity: 0.86;
        }

        .btn-cancel {
            display: inline-block;
            margin-top: 16px;
            padding: 9px 12px;
            color: #ff7777;
            border: 1px solid rgba(255, 119, 119, 0.65);
            border-radius: 10px;
            background: rgba(255, 68, 68, 0.08);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .btn-cancel:hover {
            background: #ff4444;
            color: white;
            text-shadow: none;
        }

        .empty-state {
            grid-column: 1 / -1;
            padding: 35px;
            text-align: center;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        @media (max-width: 820px) {
            body {
                justify-content: flex-start;
            }

            .page {
                grid-template-columns: 1fr;
            }

            .sidebar,
            .content-panel {
                min-height: auto;
            }

            .panel-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <video autoplay muted loop id="bg-video">
        <source src="../../assets/aquarela_bg.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <div class="video-overlay"></div>

    <main class="page">
        <aside class="sidebar">
            <h1 class="brand">PIGMENT</h1>
            <div class="avatar"><?php echo htmlspecialchars($user_initials); ?></div>
            <h2 class="profile-name"><?php echo htmlspecialchars($user_data['f_Name'] . ' ' . $user_data['l_Name']); ?></h2>
            <p class="profile-email"><?php echo htmlspecialchars($user_data['email']); ?></p>

            <div class="side-menu" data-expandable-menu>
                <button type="button" class="menu-toggle" data-menu-toggle>Explore</button>
                <div class="menu-options">
                    <div class="menu-options-inner">
                        <a href="../../index.php">Home</a>
                        <a href="../../catalog.php">Collections</a>
                    </div>
                </div>
            </div>

            <div class="nav-links">
                <a href="../../catalog.php">Book new events</a>
                <a href="user-update.php">Update profile</a>
                <a href="../../logout.php" data-confirm-logout>Logout</a>
            </div>
        </aside>

        <section class="content-panel">
            <?php if (isset($_GET['status']) && $_GET['status'] === 'cancelled'): ?>
                <p class="status-msg">Reservation successfully removed.</p>
            <?php endif; ?>

            <div class="panel-header">
                <div>
                    <h1>My bookings</h1>
                    <p>Your upcoming gallery experiences in one place.</p>
                </div>
                <div class="booking-count">
                    <strong><?php echo $bookings->num_rows; ?></strong>
                    <span>booked events</span>
                </div>
            </div>

            <div class="card-container">
                <?php if ($bookings->num_rows > 0): ?>
                    <?php while ($ticket = $bookings->fetch_assoc()): ?>
                        <article class="card">
                            <div class="event-date">
                                <strong><?php echo date('d', strtotime($ticket['e_Date'])); ?></strong>
                                <span><?php echo date('M Y', strtotime($ticket['e_Date'])); ?></span>
                            </div>
                            <div class="event-details">
                                <h3><?php echo htmlspecialchars($ticket['e_Title']); ?></h3>
                                <p><strong>Full date:</strong> <?php echo date('d/m/Y', strtotime($ticket['e_Date'])); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($ticket['e_Location']); ?></p>
                                <a href="../../cancel_reservation.php?id=<?php echo $ticket['event_ID']; ?>"
                                   class="btn-cancel"
                                   onclick="return confirm('Are you sure you want to cancel this booking?');">
                                    Cancel booking
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="card empty-state">
                        <h3>No bookings yet</h3>
                        <p>Start exploring the gallery calendar and reserve your first event.</p>
                        <a href="../../catalog.php">Browse events</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="user.js"></script>
</body>
</html>
