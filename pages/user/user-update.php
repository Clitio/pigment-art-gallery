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
    <title>Update Profile - Pigment Art Gallery</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: sans-serif;
        }
        .card {
            width: 420px;
            max-width: calc(100% - 40px);
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-sizing: border-box;
        }
        h1 { margin-top: 0; text-align: center; }
        label { font-weight: bold; }
        input { width: 100%; padding: 8px; margin: 6px 0 15px; box-sizing: border-box; }
        button { width: 100%; padding: 10px 16px; cursor: pointer; }
        .message { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .actions { margin-top: 20px; text-align: center; }
    </style>
</head>
<body>

    <div class="card">
        <h1>Update Profile</h1>

        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="user-update.php" method="POST">
            <label>First Name:</label>
            <input type="text" name="f_Name" value="<?php echo htmlspecialchars($user_data['f_Name']); ?>" required>

            <label>Last Name:</label>
            <input type="text" name="l_Name" value="<?php echo htmlspecialchars($user_data['l_Name']); ?>" required>

            <label>Date of Birth:</label>
            <input type="date" name="dOb" value="<?php echo htmlspecialchars($user_data['dOb'] ?? ''); ?>">

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>

            <label>New Password:</label>
            <input type="password" name="password" placeholder="Leave blank to keep current password">

            <button type="submit">Save changes</button>
        </form>

        <div class="actions">
            <a href="user_dashboard.php">Back to dashboard</a> |
            <a href="../../logout.php">Logout</a>
        </div>
    </div>

</body>
</html>
