<?php
require 'includes/database.php';

$datos_admin = [
    'nombre'      => 'Admin',
    'email'       => 'admin@tiki-taka.com',
    'password'    => password_hash('123', PASSWORD_BCRYPT),
    'aprobado'    => 1,
    'saldo'       => 10000,
    'foto_perfil' => 'admin.png',
    'es_admin'    => 1
];

$stmt = $conn->prepare("
    INSERT INTO usuarios (nombre, email, password, aprobado, saldo, foto_perfil, es_admin)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssdssi",
    $datos_admin['nombre'],
    $datos_admin['email'],
    $datos_admin['password'],
    $datos_admin['aprobado'],
    $datos_admin['saldo'],
    $datos_admin['foto_perfil'],
    $datos_admin['es_admin']
);

if ($stmt->execute()) {
    echo "✅ Admin creado exitosamente. Elimina este archivo ahora.";
} else {
    echo "❌ Error: " . $conn->error;
}