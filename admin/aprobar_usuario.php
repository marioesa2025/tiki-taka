<?php
session_start();

// 1. Validar permisos de administrador
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    die("Acceso denegado: Se requieren permisos de administrador");
}

// 2. Conexión a la base de datos
require __DIR__ . '../../includes/database.php'; // Ajusta la ruta según tu estructura

// 3. Validar y sanitizar el ID del usuario a aprobar
$user_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if ($user_id <= 0) {
    die("ID de usuario inválido");
}

try {
    // 4. Actualizar el usuario en la BD (consulta preparada para seguridad)
    $stmt = $conn->prepare("UPDATE usuarios SET aprobado = 1 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // 5. Registrar quién aprobó al usuario (opcional pero recomendado)
    //$admin_id = $_SESSION['user_id'];
   // $stmt_log = $conn->prepare("INSERT INTO aprobaciones_log (usuario_id, admin_id, fecha) VALUES (?, ?, NOW())");
   // $stmt_log->bind_param("ii", $user_id, $admin_id);
   // $stmt_log->execute();

    // 6. Mensaje de éxito y redirección
    $_SESSION['mensaje'] = "Usuario #$user_id aprobado correctamente";
    header("Location: ../admin/dashboard.php"); // Ajusta la ruta
    exit();

} catch (Exception $e) {
    die("Error al aprobar usuario: " . $e->getMessage());
}
?>