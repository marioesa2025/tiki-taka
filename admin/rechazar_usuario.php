<?php
session_start();
require __DIR__ . '../../includes/database.php';

// Verificar permisos de admin
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    die("Acceso no autorizado");
}

$user_id = intval($_GET['id'] ?? 0);

if ($user_id > 0) {
    // Rechazar usuario (marcar como rechazado)
    $stmt = $conn->prepare("UPDATE usuarios SET aprobado = 2 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje_exito'] = "Usuario #$user_id ha sido rechazado";
    } else {
        $_SESSION['mensaje_error'] = "Error al rechazar usuario";
    }
}

header("Location: ../admin/dashboard.php");
exit();
?>