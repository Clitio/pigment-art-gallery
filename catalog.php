<?php
session_start();
// Adjusted path: assumes dbconnect.php is in the root of pigment-art-gallery
require_once 'dbconnect.php'; 

/**
 * Function to render each event section
 * Ensures a consistent layout and clean main loop
 */
function renderEventSection($event) {
    // Path logic: 'e_Image' comes from your database
    $imagePath = !empty($event['e_Image']) ? $event['e_Image'] : "assets/default_art.jpg";
    $title = htmlspecialchars($event['e_Title']);
    $loc = htmlspecialchars($event['e_Location']);
    $date = date('d M Y', strtotime($event['e_Date']));
    $desc = nl2br(htmlspecialchars($event['e_Description']));
    $price = number_format($event['e_Price'], 2, ',', '.');
    $id = $event['event_ID'];
    $org = htmlspecialchars($event['o_Name'] ?? 'Pigment Gallery');

    echo "
    <section class='event-section'>
        <div class='event-bg' style=\"background-image: url('$imagePath');\"></div>
        <div class='content-box'>
            <h1 class='event-title'>$title</h1>
            <div class='details'>📍 $loc &nbsp; | &nbsp; 📅 $date</div>
            <div class='description'>$desc</div>
            <div class='action-row'>
                <span class='price-display'>€$price</span>
                <a href='event_details.php?id=$id' class='btn-reserve'>Explore Piece</a>
            </div>
            <p class='organiser-credit'>Curated by: $org</p>
        </div>
    </section>
    ";
}

// Fetch Logic
$sql = "SELECT e.*, o.o_Name FROM event e 
        LEFT JOIN organiser o ON e.organiser_ID = o.organiser_ID 
        ORDER BY e.e_Date ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pigment - Experience Catalog</title>
    <style>
        :root { --accent: #ffcc00; }

        /* SCROLL ENGINE: Applied to HTML to prevent locking */
        html {
            scroll-snap-type: y mandatory;
            scroll-behavior: smooth;
            overflow-y: scroll;
            height: 100%;
            scroll-padding-top: 0;
        }

        /* HIDE SCROLLBARS while keeping scroll active */
        html::-webkit-scrollbar { display: none; }
        html { -ms-overflow-style: none; scrollbar-width: none; }

        body {
            margin: 0;
            padding: 0;
            background: #000;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            overscroll-behavior-y: none;
        }

        /* SECTION LAYOUT */
        .event-section {
            height: 100vh;
            width: 100vw;
            display: flex;
            align-items: center;
            position: relative;
            scroll-snap-align: start;
            scroll-snap-stop: always;
            overflow: hidden;
        }

        /* DYNAMIC BACKGROUND */
        .event-bg {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-size: cover;
            background-position: center;
            z-index: 1;
            filter: brightness(0.35) saturate(1.2);
            transition: transform 2s ease-out;
        }

        .event-section:hover .event-bg { transform: scale(1.08); }

        /* GLASSMORPHISM BOX */
        .content-box {
            position: relative;
            z-index: 2;
            margin-left: 8%;
            max-width: 650px;
            padding: 50px;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(15px);
            border-left: 5px solid var(--accent);
            border-radius: 0 25px 25px 0;
            box-shadow: 20px 0 50px rgba(0,0,0,0.4);
            
            /* Initial state for Fade Animation */
            opacity: 0;
            transform: translateY(40px);
            transition: all 1.5s cubic-bezier(0.19, 1, 0.22, 1);
        }

        /* Triggered by JS */
        .content-box.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .event-title { font-size: 4.5rem; color: var(--accent); margin: 0; line-height: 1; text-transform: uppercase; }
        .details { margin: 20px 0; font-size: 1.2rem; letter-spacing: 1.5px; color: #ccc; }
        .description { line-height: 1.8; margin-bottom: 30px; color: #eee; font-size: 1.1rem; }
        
        .action-row { display: flex; align-items: center; gap: 30px; }
        .price-display { font-size: 2.5rem; font-weight: bold; }
        
        .btn-reserve {
            padding: 15px 40px;
            background: var(--accent);
            color: #000;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 50px;
            transition: 0.3s;
        }

        .btn-reserve:hover { background: #fff; box-shadow: 0 0 20px var(--accent); transform: scale(1.05); }
        .organiser-credit { margin-top: 25px; font-size: 0.85rem; opacity: 0.4; font-style: italic; }

        /* FIXED NAV */
        .top-nav {
            position: fixed;
            top: 30px; right: 40px;
            z-index: 100;
        }
        .top-nav a {
            color: white; text-decoration: none; padding: 10px 25px;
            background: rgba(255,255,255,0.1); border-radius: 30px;
            backdrop-filter: blur(5px); transition: 0.3s; border: 1px solid rgba(255,255,255,0.1);
        }
        .top-nav a:hover { background: var(--accent); color: black; }

        .event-bg {
            transition: transform 3s cubic-bezier(0.1, 0, 0.1, 1);
        }
    </style>
</head>
<body>

    <nav class="top-nav">
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
    </nav>

    <?php 
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            renderEventSection($row);
        }
    } else {
        echo "<div style='height:100vh; display:flex; align-items:center; justify-content:center;'>
                <h2>No pigments found in the gallery yet.</h2>
              </div>";
    }
    ?>

    <script>
        /**
         * Intersection Observer for the Fade-In effect
         * Detects when a section is 50% visible to trigger animation
         */
        const observerOptions = { threshold: 0.5 };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const box = entry.target.querySelector('.content-box');
                if (entry.isIntersecting) {
                    box.classList.add('is-visible');
                } else {
                    box.classList.remove('is-visible'); // Reset to fade out when leaving
                }
            });
        }, observerOptions);

        document.querySelectorAll('.event-section').forEach(section => {
            observer.observe(section);
        });
    </script>
</body>
</html>