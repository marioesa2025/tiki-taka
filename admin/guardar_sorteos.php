<?php
/**
 * guardar_sorteos.php - Maneja la configuración de sorteos
 */

// 1. Validar sesión y permisos
session_start();
if (!isset($_SESSION['es_admin']) || !$_SESSION['es_admin']) {
    die(json_encode(['error' => 'Acceso no autorizado']));
}

// 2. Conexión a la base de datos
require $_SERVER['DOCUMENT_ROOT'] . '/dashboard/tiki-taka/includes/database.php';

// 3. Validar y sanitizar datos
$dia_semana = filter_input(INPUT_POST, 'dia_semana', FILTER_VALIDATE_INT);
$hora_sorteo = filter_input(INPUT_POST, 'hora_sorteo', FILTER_SANITIZE_STRING);
$frecuencia = filter_input(INPUT_POST, 'frecuencia', FILTER_SANITIZE_STRING);
$activo = isset($_POST['activo']) ? 1 : 0;

// 4. Validaciones adicionales
if ($dia_semana < 1 || $dia_semana > 7) {
    die(json_encode(['error' => 'Día de la semana inválido']));
}

if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora_sorteo)) {
    die(json_encode(['error' => 'Hora inválida']));
}

// 5. Guardar en la base de datos
try {
    $stmt = $conn->prepare("INSERT INTO config_sorteos 
                          (dia_semana, hora_sorteo, frecuencia, activo, creado_por) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issii", $dia_semana, $hora_sorteo, $frecuencia, $activo, $_SESSION['user_id']);
    $stmt->execute();
    
    // Registrar en logs
    $conn->query("INSERT INTO logs (usuario_id, accion) 
                 VALUES ({$_SESSION['user_id']}, 'Configuró sorteo: $frecuencia')");
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Error al guardar sorteo: " . $e->getMessage());
    die(json_encode(['error' => 'Error al guardar configuración']));
}