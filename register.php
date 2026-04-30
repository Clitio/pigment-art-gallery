 <?php
require_once 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Criptografia
    $company = mysqli_real_escape_string($conn, $_POST['company']);

    $sql_check = "SELECT email FROM user WHERE email = '$email' 
                  UNION SELECT o_email FROM organiser WHERE o_email = '$email'
                  UNION SELECT a_email FROM admin WHERE a_email = '$email'";
    $res_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($res_check) > 0) {
        die("Error: e-mail already exist");
    }

    if ($role === 'admin') {
        if (!str_ends_with($email, '@pigment.com')) {
            die("Error: admin e-mail not authorized <br><br><a href='register.php'>Return to register page</a>" );
        }
        $query = "INSERT INTO admin (a_email, a_pwd) VALUES ('$email', '$password')";
    } 
    
    else if ($role === 'organiser') {
        if (empty(trim($company))) {
        die("Error: Please provide your company name. <br><br><a href='register.php'>Return to register page</a>");
    }
        $query = "INSERT INTO organiser (o_Name, o_Company, o_email, o_pwd) 
                  VALUES ('$full_name', '$company', '$email', '$password')";
    } 
    
    else { 
        $name_parts = explode(" ", $full_name);
        $f_name = $name_parts[0];
        $l_name = isset($name_parts[1]) ? $name_parts[1] : "";
        
        $query = "INSERT INTO user (f_Name, l_Name, dOb, email, pwd) 
                  VALUES ('$f_name', '$l_name', '$dob', '$email', '$password')";
    }

    if (mysqli_query($conn, $query)) {
        echo "<h1>Success!</h1>";
        echo "<p>Account created <strong>$role</strong>.</p>";
        echo "<a href='login.php'>Click here to login</a>";
    } else {
        echo "Error registering to the DB " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pigment</title>
</head>
<body>
    <h1>Registration</h1>
    
    <form action="register.php" method="POST">
        <label>Account Type:</label><br>
        <select name="role" required>
            <option value="" disabled selected>Choose an option: </option>
            <option value="user">User</option>
            <option value="organiser">Organiser</option>
            <option value="admin">Admin</option>
        </select>
        <br><br>

        <label>Full Name:</label><br>
        <input type="text" name="full_name" required>
        <br><br>

        <label>Date of Birth:</label><br>
        <input type="date" name="dob">
        <br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email" placeholder="ex: nome@pigment.com para admin" required>
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required>
        <br><br>

        <label>Company (Only for organiser):</label><br>
        <input type="text" name="company">
        <br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
    <p><a href="index.php">Back to homepage</a></p>
</body>
</html>