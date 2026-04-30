<?php
session_start();
require_once 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if (str_ends_with($email, '@pigment.com')) {
        $sql = "SELECT admin_ID as id, a_pwd as pwd, 'admin' as role FROM admin WHERE a_email = '$email'";
    } else {
        $sql = "SELECT user_ID as id, pwd, 'user' as role FROM user WHERE email = '$email' 
                UNION 
                SELECT organiser_ID as id, o_pwd as pwd, 'organiser' as role FROM organiser WHERE o_email = '$email'";
    }

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['pwd'])) {
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $email;

            if ($row['role'] === 'user') {
                $_SESSION['user_ID'] = $row['id'];
            } elseif ($row['role'] === 'organiser') {
                $_SESSION['organiser_ID'] = $row['id'];
            } elseif ($row['role'] === 'admin') {
                $_SESSION['admin_ID'] = $row['id'];
            }

            header("Location: index.php");
            exit();
        } else {
            $error = "Incorret Password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pigment - Login</title>
</head>
<body>
    <h1>Login</h1>

    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form action="login.php" method="POST">
        <label>E-mail:</label><br>
        <input type="email" name="email" required>
        <br><br>

        <label>Senha:</label><br>
        <input type="password" name="password" required>
        <br><br>

        <button type="submit">Enter</button>
    </form>

    <p>Not own an account? <a href="register.php">Register here</a></p>
    <p><a href="index.php">Back to homepage</a></p>
</body>
</html>
