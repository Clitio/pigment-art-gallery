<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: catalog.php");
    exit();
}

$event_id = $_GET['id'];

// Fetch event details with Organiser name
$stmt = mysqli_prepare($conn, 
    "SELECT e.*, o.o_Name 
     FROM event e 
     LEFT JOIN organiser o ON e.organiser_ID = o.organiser_ID 
     WHERE e.event_ID = ?"
);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($result);

if (!$event) {
    header("Location: catalog.php");
    exit();
}

$imagePath = !empty($event['e_Image']) ? $event['e_Image'] : "assets/default_art.jpg";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($event['e_Title']); ?> - Pigment</title>
    <style>
        :root { --accent: #00f2ff; }
        body, html { margin: 0; padding: 0; height: 100%; background: #0a0a0a; color: white; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .details-container { display: flex; min-height: 100vh; width: 100%; }
        .visual-side { flex: 1; position: sticky; top: 0; height: 100vh; overflow: hidden; }
        .main-image { width: 100%; height: 100%; object-fit: cover; animation: slowZoom 15s infinite alternate; }
        @keyframes slowZoom { from { transform: scale(1); } to { transform: scale(1.15); } }
        .info-side { flex: 1; padding: 80px; background: linear-gradient(to right, #0a0a0a, #141414); display: flex; flex-direction: column; justify-content: center; }
        
        .fade-in { opacity: 0; transform: translateY(40px); transition: all 2.5s cubic-bezier(0.16, 1, 0.3, 1); }
        .fade-in.is-visible { opacity: 1; transform: translateY(0); }

        h1 { font-size: 4rem; margin: 10px 0; color: var(--accent); }
        .meta-data { font-size: 1.2rem; color: #888; margin-bottom: 30px; line-height: 1.6; }
        .description { font-size: 1.1rem; line-height: 1.8; color: #eee; margin-bottom: 40px; }

        .booking-card {
            background: rgba(255, 255, 255, 0.03);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-book {
            background: var(--accent);
            color: black;
            padding: 18px 45px;
            border: none;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            transition: 0.4s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-book:hover { transform: scale(1.05); background: white; box-shadow: 0 0 30px var(--accent); }

        /* Notification Styles */
        .status-msg { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; }
        .error { background: rgba(255, 0, 0, 0.2); border: 1px solid red; color: #ff8888; }
        
        @media (max-width: 1000px) { .details-container { flex-direction: column; } .visual-side { height: 40vh; } .info-side { padding: 40px; } }
    </style>
</head>
<body>

    <div class="details-container">
        <div class="visual-side">
            <img src="<?php echo $imagePath; ?>" class="main-image">
        </div>

        <div class="info-side">
            <?php if(isset($_GET['error'])): ?>
                <div class="status-msg error fade-in">
                    <?php 
                        if($_GET['error'] == 'already_reserved') echo "You already have a spot for this event.";
                        if($_GET['error'] == 'login_required') echo "Please login to reserve your spot.";
                    ?>
                </div>
            <?php endif; ?>

            <a href="catalog.php" style="color:var(--accent); text-decoration:none;" class="fade-in">← BACK TO GALLERY</a>
            
            <h1 class="fade-in" style="transition-delay: 0.3s;"><?php echo htmlspecialchars($event['e_Title']); ?></h1>
            
            <div class="meta-data fade-in" style="transition-delay: 0.6s;">
                📍 <?php echo htmlspecialchars($event['e_Location']); ?> <br>
                📅 <?php echo date('l, F jS', strtotime($event['e_Date'])); ?> @ <?php echo $event['e_Time']; ?>
            </div>

            <div class="description fade-in" style="transition-delay: 0.9s;">
                <?php echo nl2br(htmlspecialchars($event['e_Description'])); ?>
                <p style="opacity: 0.5; font-style: italic;">Host: <?php echo htmlspecialchars($event['o_Name']); ?></p>
            </div>

            <div class="booking-card fade-in" style="transition-delay: 1.2s;">
                <div>
                    <span style="color: var(--accent); text-transform: uppercase; font-size: 0.8rem;">Price</span>
                    <div style="font-size: 2rem; font-weight: bold;">€<?php echo number_format($event['e_Price'], 2); ?></div>
                </div>

                <form action="process_reservation.php" method="POST">
                    <input type="hidden" name="event_id" value="<?php echo $event['event_ID']; ?>">
                    <button type="submit" class="btn-book">Reserve Spot</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const elements = document.querySelectorAll('.fade-in');
            setTimeout(() => {
                elements.forEach(el => el.classList.add('is-visible'));
            }, 100);
        });
    </script>
</body>
</html>