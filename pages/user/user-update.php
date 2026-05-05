<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['user_ID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../../login.php");
    exit();
}

$user_ID = $_SESSION['user_ID'];
$message = "";
$error = "";

$stmt_user = $conn->prepare("SELECT f_Name, l_Name, dOb, email FROM user WHERE user_ID = ?");
$stmt_user->bind_param("i", $user_ID);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

if ($user_result->num_rows === 0) {
    die("Error: User not found on database.");
}

$user_data = $user_result->fetch_assoc();
$user_initials = strtoupper(substr($user_data['f_Name'], 0, 1) . substr($user_data['l_Name'] ?? '', 0, 1));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $f_name = trim($_POST['f_Name']);
    $l_name = trim($_POST['l_Name']);
    $dob = !empty($_POST['dOb']) ? $_POST['dOb'] : null;
    $email = trim($_POST['email']);
    $new_password = $_POST['password'];

    if ($f_name === "" || $l_name === "" || $email === "") {
        $error = "Please fill in first name, last name, and email.";
    } elseif (str_ends_with($email, '@pigment.com')) {
        $error = "This email domain is reserved for admin accounts.";
    } else {
        $stmt_check = $conn->prepare(
            "SELECT user_ID AS id FROM user WHERE email = ? AND user_ID != ?
             UNION
             SELECT organiser_ID AS id FROM organiser WHERE o_email = ?
             UNION
             SELECT admin_ID AS id FROM admin WHERE a_email = ?"
        );
        $stmt_check->bind_param("siss", $email, $user_ID, $email, $email);
        $stmt_check->execute();
        $email_check = $stmt_check->get_result();

        if ($email_check->num_rows > 0) {
            $error = "This email is already being used by another account.";
        } else {
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_update = $conn->prepare(
                    "UPDATE user SET f_Name = ?, l_Name = ?, dOb = ?, email = ?, pwd = ? WHERE user_ID = ?"
                );
                $stmt_update->bind_param("sssssi", $f_name, $l_name, $dob, $email, $hashed_password, $user_ID);
            } else {
                $stmt_update = $conn->prepare(
                    "UPDATE user SET f_Name = ?, l_Name = ?, dOb = ?, email = ? WHERE user_ID = ?"
                );
                $stmt_update->bind_param("ssssi", $f_name, $l_name, $dob, $email, $user_ID);
            }

            if ($stmt_update->execute()) {
                $_SESSION['email'] = $email;
                $message = "Profile updated successfully.";

                $user_data['f_Name'] = $f_name;
                $user_data['l_Name'] = $l_name;
                $user_data['dOb'] = $dob;
                $user_data['email'] = $email;
            } else {
                $error = "Error updating profile: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - Pigment Art Gallery</title>
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
            background: rgba(0, 0, 0, 0.55);
            z-index: -1;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .card {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 35px;
            width: 900px;
            max-width: 100%;
            padding: 35px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-sizing: border-box;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .profile-panel {
            padding: 28px;
            border-radius: 18px;
            background: rgba(0, 0, 0, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.16);
            text-align: center;
        }

        .avatar {
            width: 115px;
            height: 115px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 204, 0, 0.18);
            border: 2px solid #ffcc00;
            color: #ffcc00;
            font-size: 2.4rem;
            font-weight: bold;
            box-shadow: 0 0 30px rgba(255, 204, 0, 0.25);
        }

        .profile-panel h2,
        .form-panel h1 {
            font-family: 'Playfair Display', Georgia, serif;
        }

        .profile-panel h2 {
            margin: 0;
            font-size: 1.7rem;
        }

        .profile-panel p {
            opacity: 0.82;
            overflow-wrap: anywhere;
        }

        .profile-note {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.16);
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .form-panel h1 {
            margin: 0 0 8px;
            font-size: 2.2rem;
        }

        .form-panel > p {
            margin: 0 0 22px;
            opacity: 0.82;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 16px;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        label { font-weight: 600; }
        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: none;
            border-radius: 8px;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.92);
        }
        .password-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: -5px 0 15px;
            font-size: 0.9rem;
        }
        .password-toggle input {
            width: auto;
            margin: 0;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #ffcc00;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }
        .message,
        .error {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        .message { background: rgba(60, 118, 61, 0.3); border: 1px solid #3c763d; }
        .error { background: rgba(255, 85, 85, 0.3); border: 1px solid #ff5555; }
        .actions { margin-top: 20px; text-align: center; }
        a { color: #ffcc00; text-decoration: none; font-weight: bold; }

        @media (max-width: 820px) {
            .card,
            .form-grid {
                grid-template-columns: 1fr;
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

    <div class="card">
        <aside class="profile-panel">
            <div class="avatar"><?php echo htmlspecialchars($user_initials); ?></div>
            <h2><?php echo htmlspecialchars($user_data['f_Name'] . ' ' . $user_data['l_Name']); ?></h2>
            <p><?php echo htmlspecialchars($user_data['email']); ?></p>
            <div class="profile-note">
                Keep your attendee profile updated so Pigment can show the right booking details for your gallery visits.
            </div>
        </aside>

        <section class="form-panel">
            <h1>Update Profile</h1>
            <p>Edit your attendee details below.</p>

            <?php if ($message): ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form action="user-update.php" method="POST" data-update-form>
                <div class="form-grid">
                    <div>
                        <label>First Name:</label>
                        <input type="text" name="f_Name" value="<?php echo htmlspecialchars($user_data['f_Name']); ?>" required>
                    </div>

                    <div>
                        <label>Last Name:</label>
                        <input type="text" name="l_Name" value="<?php echo htmlspecialchars($user_data['l_Name']); ?>" required>
                    </div>

                    <div>
                        <label>Date of Birth:</label>
                        <input type="date" name="dOb" value="<?php echo htmlspecialchars($user_data['dOb'] ?? ''); ?>">
                    </div>

                    <div>
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>

                    <div class="field-full">
                        <label>New Password:</label>
                        <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">

                        <label class="password-toggle">
                            <input type="checkbox" id="showPassword">
                            Show password
                        </label>
                    </div>
                </div>

                <button type="submit">Save changes</button>
            </form>

            <div class="actions">
                <a href="user_dashboard.php">Back to dashboard</a> |
                <a href="../../logout.php" data-confirm-logout>Exit to homepage</a>
            </div>
        </section>
    </div>

    <script src="user.js"></script>
</body>
</html>
