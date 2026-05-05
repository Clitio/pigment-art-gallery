<?php
session_start();
require_once 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember_me']);

    if (str_ends_with($email, '@pigment.com')) {
        $stmt = $conn->prepare("SELECT admin_ID as id, a_pwd as pwd, 'admin' as role FROM admin WHERE a_email = ?");
        $stmt->bind_param("s", $email);
    } else {
        $stmt = $conn->prepare("SELECT user_ID as id, pwd, 'user' as role FROM user WHERE email = ? 
                                UNION 
                                SELECT organiser_ID as id, o_pwd as pwd, 'organiser' as role FROM organiser WHERE o_email = ?");
        $stmt->bind_param("ss", $email, $email);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['pwd'])) {
            session_regenerate_id(true); 
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $email;

            if ($row['role'] === 'user') {
                $_SESSION['user_ID'] = $row['id'];
            } elseif ($row['role'] === 'organiser') {
                $_SESSION['organiser_ID'] = $row['id'];
            } elseif ($row['role'] === 'admin') {
                $_SESSION['admin_ID'] = $row['id'];
            }

            if ($remember) {
                setcookie("remembered_email", $email, time() + (86400 * 30), "/");
            } else {
                setcookie("remembered_email", "", time() - 3600, "/");
            }

            header("Location: index.php");
            exit();
        } else {
            $error = "Password Incorrect.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pigment - Login</title>
    <style>
        body, html { margin: 0; padding: 0; height: 100%; font-family: 'Segoe UI', sans-serif; color: white; overflow: hidden; }
        #bg-video { position: fixed; top: 0; left: 0; min-width: 100%; min-height: 100%; z-index: -2; object-fit: cover; }
        .video-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.55); z-index: -1; }
        
        .container { display: flex; justify-content: center; align-items: center; height: 100vh; }
        .form-card { 
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(25px); /* Higher blur for focus */
            padding: 40px; 
            border-radius: 20px; 
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 350px;
            text-align: center;
        }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: none; background: rgba(255,255,255,0.9);
        }
        button { 
            width: 100%; padding: 12px; border-radius: 8px; border: none; background: #ffcc00; 
            color: #333; font-weight: bold; cursor: pointer; margin-top: 10px;
        }
        a { color: #ffcc00; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>
    <video autoplay muted loop id="bg-video"><source src="assets/aquarela_bg.mp4" type="video/mp4"></video>
    <div class="video-overlay"></div>

    <div class="container">
        <div class="form-card">
            <h1>Login</h1>
            <?php if(isset($error)) echo "<p style='color:#ff5555;'>" . htmlspecialchars($error) . "</p>"; ?>
            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="E-mail" value="<?php echo htmlspecialchars($_COOKIE['remembered_email'] ?? ''); ?>" required>
                <input type="password" name="password" placeholder="Password" required>
                <label style="font-size: 0.8rem;">
                    <input type="checkbox" name="remember_me" <?php echo isset($_COOKIE['remembered_email']) ? 'checked' : ''; ?>> Remember Me
                </label>
                <button type="submit">Enter</button>
            </form>
            <p style="font-size: 0.9rem; margin-top: 20px;">
                New here? <a href="register.php">Register</a><br>
                <a href="index.php">Back to homepage</a>
            </p>
        </div>
    </div>
</body>
</html>
