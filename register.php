<?php
require_once 'dbconnect.php';
$error_msg = ""; $success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $company = $_POST['company'] ?? '';
    $password_plain = $_POST['password'] ?? '';

    if (strlen($password_plain) < 8 || !preg_match("/[a-z]/i", $password_plain) || !preg_match("/[0-9]/", $password_plain)) {
        $error_msg = "Password must contain at least 8 characters, including letters and numbers.";
    } else {
        $sql_check = "SELECT email FROM user WHERE email = ? UNION SELECT o_email FROM organiser WHERE o_email = ? UNION SELECT a_email FROM admin WHERE a_email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("sss", $email, $email, $email);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows > 0) {
            $error_msg = "E-mail unavailable.";
        } else {
            $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);
            if ($role === 'admin') {
                if (!str_ends_with($email, '@pigment.com')) { $error_msg = "Admin Account not authorized."; }
                else { $stmt = $conn->prepare("INSERT INTO admin (a_email, a_pwd) VALUES (?, ?)"); $stmt->bind_param("ss", $email, $password_hash); }
            } elseif ($role === 'organiser') {
                if (empty(trim($company))) { $error_msg = "Company's name is required."; }
                else { $stmt = $conn->prepare("INSERT INTO organiser (o_Name, o_Company, o_email, o_pwd) VALUES (?, ?, ?, ?)"); $stmt->bind_param("ssss", $full_name, $company, $email, $password_hash); }
            } else { 
                $parts = explode(" ", $full_name);
                $stmt = $conn->prepare("INSERT INTO user (f_Name, l_Name, dOb, email, pwd) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $parts[0], $parts[1], $dob, $email, $password_hash);
            }
            if (empty($error_msg) && $stmt->execute()) { $success_msg = "Account created!"; }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pigment - Registration</title>
    <style>
        body, html { margin: 0; padding: 0; min-height: 100%; font-family: 'Segoe UI', sans-serif; color: white; }
        #bg-video { position: fixed; top: 0; left: 0; min-width: 100%; min-height: 100%; z-index: -2; object-fit: cover; }
        .video-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.55); z-index: -1; }
        .container { display: flex; justify-content: center; align-items: center; padding: 50px 0; }
        .form-card { 
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(25px); 
            padding: 40px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.2);
            width: 450px; text-align: left;
        }
        input, select { width: 100%; padding: 10px; margin: 8px 0; border-radius: 5px; border: none; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #ffcc00; border: none; font-weight: bold; border-radius: 8px; cursor: pointer; margin-top: 15px; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 10px; font-size: 0.9rem; }
        .alert-danger { background: rgba(255, 85, 85, 0.3); border: 1px solid #ff5555; }
        .alert-success { background: rgba(60, 118, 61, 0.3); border: 1px solid #3c763d; }
        a { color: #ffcc00; text-decoration: none; }
    </style>
</head>
<body>
    <video autoplay muted loop id="bg-video"><source src="assets/aquarela_bg.mp4" type="video/mp4"></video>
    <div class="video-overlay"></div>

    <div class="container">
        <div class="form-card">
            <h1 style="text-align: center;">Join Pigment</h1>
            <?php if ($error_msg): ?><div class="alert alert-danger"><?php echo $error_msg; ?></div><?php endif; ?>
            <?php if ($success_msg): ?><div class="alert alert-success"><?php echo $success_msg; ?></div><?php endif; ?>

            <form action="register.php" method="POST">
                <label>Account Type:</label>
                <select name="role" required>
                    <option value="user">User/Attendee</option>
                    <option value="organiser">Organiser</option>
                    <option value="admin">Admin</option>
                </select>
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="date" name="dob" title="Date of Birth">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password (8+ chars, letters & numbers)" required>
                <input type="text" name="company" placeholder="Company Name (Organisers only)">
                <button type="submit">Create Account</button>
            </form>
            <p style="text-align: center; font-size: 0.9rem;">Already registered? <a href="login.php">Login</a><br>
            <a href="index.php">Back to homepage</a></p>
            
        </div>
    </div>
</body>
</html>