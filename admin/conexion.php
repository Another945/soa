<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "bd_barberia";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>