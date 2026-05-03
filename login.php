<?php
session_start();
require_once 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (str_ends_with($email, '@pigment.com')) {
        $stmt = $conn->prepare("SELECT admin_ID as id, a_pwd as pwd, 'admin' as role FROM admin WHERE a_email = ?");
        $stmt->bind_param("s", $email);
    } else {
        $stmt = $conn->prepare("SELECT user_id as id, pwd, 'user' as role FROM user WHERE email = ? 
                                UNION 
                                SELECT organiser_id as id, o_pwd as pwd, 'organiser' as role FROM organiser WHERE o_email = ?");
        $stmt->bind_param("ss", $email, $email);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['pwd'])) {
            session_regenerate_id(true); 

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $email;

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
<head><meta charset="UTF-8"><title>Pigment - Login</title></head>
<body>
    <h1>Login</h1>
    <?php if(isset($error)) echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>"; ?>
    <form action="login.php" method="POST">
        <input type="email" name="email" placeholder="E-mail" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Enter</button>
    </form>
</body>
</html>