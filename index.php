<?php
session_start(); 
require_once 'dbconnect.php'; 

$dashboard_link = null;
if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    $dashboard_link = "pages/$role/{$role}_dashboard.php";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pigment</title>

    <style>
        :root {
            --accent-color: #00f2ff;
            --card-bg: rgba(255, 255, 255, 0.15);
            --card-hover: rgba(255, 255, 255, 0.25);
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            overflow: hidden; 
        }

        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: -2;
            object-fit: cover; 
        }

        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            z-index: -1;
        }

        .content-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
            z-index: 1;
        }

        /* --- FADE EFFECTS CSS --- */
        .fade-element {
            opacity: 0;
            transform: translateY(40px);
            transition: all 2.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .fade-element.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Staggered Delays */
        .title { transition-delay: 0.4s; }
        .subtitle { transition-delay: 1.2s; }
        .card-container { transition-delay: 2.0s; }

        h1 {
            font-size: 5rem;
            margin: 0;
            letter-spacing: 5px;
            text-shadow: 0 0 20px rgba(0, 242, 255, 0.4);
        }

        h2 {
            font-weight: 300;
            font-style: italic;
            margin-bottom: 40px;
            opacity: 0.8;
        }

        .card-container {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .card {
            padding: 35px 25px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            color: white;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            width: 140px;
            transition: all 0.4s ease;
            font-weight: bold;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .card:hover {
            transform: translateY(-10px);
            background: var(--card-hover);
            border-color: var(--accent-color);
            box-shadow: 0 12px 40px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>

    <video autoplay muted loop id="bg-video">
        <source src="assets/aquarela_bg.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <div class="video-overlay"></div>

    <div class="content-wrapper">
        <!-- Elements now have the fade-element class and their specific identifiers -->
        <h1 class="fade-element title">PIGMENT</h1>
        <h2 class="fade-element subtitle">Where colours turn into dreams</h2>

        <div class="card-container fade-element">
            <?php if ($dashboard_link): ?>
                <a href="<?php echo htmlspecialchars($dashboard_link); ?>" class="card">Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="card">Register</a>
                <a href="login.php" class="card">Login</a>
            <?php endif; ?>
            <a href="catalog.php" class="card">Catalog</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Options for the observer
            const observerOptions = {
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Find all items with the fade-element class inside the content-wrapper
                        const elements = entry.target.querySelectorAll('.fade-element');
                        elements.forEach(el => el.classList.add('is-visible'));
                    }
                });
            }, observerOptions);

            // Observe the main content wrapper
            const content = document.querySelector('.content-wrapper');
            if (content) {
                observer.observe(content);
            }
        });
    </script>
</body>
</html>
