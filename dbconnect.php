<?php
//This page is necessary and unique for EVERY page that will connect to the database
//Don't forget to create a connection once you start a new page
$host = "Localhost";
$user = "root";
$pass = "";
$dbname = "pigment-art-gallery";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>