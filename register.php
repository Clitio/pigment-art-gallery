<?php
require_once 'dbconnect.php';

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //collect form data using null coalescing operator to avoid "undefined index" errors
    $role = $_POST['role'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $company = $_POST['company'] ?? '';
    $password_plain = $_POST['password'] ?? '';

    //password Validation: 8+ chars, letters, and numbers
    if (strlen($password_plain) < 8 || !preg_match("/[a-z]/i", $password_plain) || !preg_match("/[0-9]/", $password_plain)) {
        $error_msg = "Password must contain at least 8 characteres, including letters and numbers.";
    } 
    else {
        //check if email already exists in ANY of the three tables
        $sql_check = "SELECT email FROM user WHERE email = ? 
                      UNION SELECT o_email FROM organiser WHERE o_email = ? 
                      UNION SELECT a_email FROM admin WHERE a_email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("sss", $email, $email, $email);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows > 0) {
            $error_msg = "E-mail unavailable.";
        } else {
            //securely hash the password before saving
            $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

            //logic for ADMIN registration
            if ($role === 'admin') {
                if (!str_ends_with($email, '@pigment.com')) {
                    $error_msg = "Admin Account not authorized.";
                } else {
                    $stmt = $conn->prepare("INSERT INTO admin (a_email, a_pwd) VALUES (?, ?)");
                    $stmt->bind_param("ss", $email, $password_hash);
                }
            } 

            //logic for ORGANISER registration
            elseif ($role === 'organiser') {
                if (empty(trim($company))) {
                    $error_msg = "Company's name is a must for Organisers.";
                } else {
                    $stmt = $conn->prepare("INSERT INTO organiser (o_Name, o_Company, o_email, o_pwd) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $full_name, $company, $email, $password_hash);
                }
            } 
            //logic for standard USER registration
            else { 
                $name_parts = explode(" ", $full_name); //split full name into first and last
                $f_name = $name_parts[0];
                $l_name = $name_parts[1] ?? "";
                $stmt = $conn->prepare("INSERT INTO user (f_Name, l_Name, dOb, email, pwd) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $f_name, $l_name, $dob, $email, $password_hash);
            }

            //execute the insertion if no errors occurred during validation
            if (empty($error_msg)) {
                if ($stmt->execute()) {
                    $success_msg = "Account created successfuly!";
                } else {
                    $error_msg = "ERROR: " . $stmt->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pigment - Registration</title>
    <style>
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-danger { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
    </style>
</head>
<body>
    <h1>Registration</h1>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><strong>Error:</strong> <?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    
    <form action="register.php" method="POST">
        <label>Account Type:</label><br>
        <select name="role" required>
            <option value="user" <?php if(isset($_POST['role']) && $_POST['role'] == 'user') echo 'selected'; ?>>User</option>
            <option value="organiser" <?php if(isset($_POST['role']) && $_POST['role'] == 'organiser') echo 'selected'; ?>>Organiser</option>
            <option value="admin" <?php if(isset($_POST['role']) && $_POST['role'] == 'admin') echo 'selected'; ?>>Admin</option>
        </select>
        <br><br>

        <label>Full Name:</label><br>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
        <br><br>

        <label>Date of Birth:</label><br>
        <input type="date" name="dob" value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
        <br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="ex: nome@pigment.com para admin" required>
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required>
        <small>(Minimum 8 characters, letters and numbers)</small>
        <br><br>

        <label>Company (Only for organiser):</label><br>
        <input type="text" name="company" value="<?php echo htmlspecialchars($_POST['company'] ?? ''); ?>">
        <br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
    <p><a href="index.php">Back to homepage</a></p>
</body>
</html>