<?php
$host = 'localhost';
$user = 'marioesa';
$password = 'esaondax1';
$database = 'apuestas_online';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>