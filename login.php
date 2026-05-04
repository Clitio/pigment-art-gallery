<?php
session_start();
require_once 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    //check if the "Remember Me" checkbox was ticked
    $remember = isset($_POST['remember_me']);

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

            //COOKIE
            if ($remember) {
                //store the email in a cookie for 30 days
                setcookie("remembered_email", $email, time() + (86400 * 30), "/");
            } else {
                //if not checked, delete the cookie by setting it to a past time
                setcookie("remembered_email", "", time() - 3600, "/");
            }
            //COOKIE

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
        <!-- the value attribute now checks if the cookie exists to pre-fill the field -->
        <input type="email" name="email" placeholder="E-mail" 
               value="<?php echo htmlspecialchars($_COOKIE['remembered_email'] ?? ''); ?>" 
               required><br><br>
               
        <input type="password" name="password" placeholder="Password" required><br><br>

        <!-- new checkbox for cookie opt-in -->
        <label>
            <input type="checkbox" name="remember_me" 
                   <?php echo isset($_COOKIE['remembered_email']) ? 'checked' : ''; ?>> 
            Remember Me
        </label><br><br>

        <button type="submit">Enter</button>
        <p><a href="index.php">Back to homepage</a></p>
    </form>
</body>
</html>