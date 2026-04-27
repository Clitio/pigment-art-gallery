<?php
session_start();
require_once 'dbconnect.php';

if (isset($_SESSION['role'])) {
    header("Location: " . $_SESSION['role'] . "_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pigment</title>
    <style>
        body { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; }
        .card-container { display: flex; gap: 20px; }
        .card { padding: 30px; border: 1px solid #ccc; text-align: center; border-radius: 10px; width: 150px; }
    </style>
</head>
<body>

    <h1>Pigment</h1>
    <p>Choose your access:</p>

    <div class="card-container">
        <a href="register.php" class="card">Register</a>
        <a href="login.php" class="card">Login</a>
        <a href="catalog.php" class="card">Catalog</a>
    </div>

</body>
</html>