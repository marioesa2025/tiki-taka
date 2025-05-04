<?php
/**
 * logout.php - Manejo seguro de cierre de sesión
 * Versión: 1.0.0
 */

// 1. Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 0,
        'cookie_httponly' => true,
        'cookie_secure' => true,
        'use_strict_mode' => true
    ]);
}

// 2. Registrar el cierre de sesión (opcional pero recomendado)
if (isset($_SESSION['user_id'])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/tiki-taka/includes/database.php';
    
    try {
        $conn->query("UPDATE usuarios SET ultimo_login = NOW() WHERE id = {$_SESSION['user_id']}");
        $conn->query("INSERT INTO logs (usuario_id, accion) VALUES ({$_SESSION['user_id']}, 'Cierre de sesión')");
    } catch (Exception $e) {
        error_log("Error al registrar cierre de sesión: " . $e->getMessage());
    }
}

// 3. Destruir completamente la sesión
$_SESSION = array();

// 4. Borrar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 5. Destruir la sesión
session_destroy();

// 6. Redirigir al login con mensaje
header('Location: /dashboard/tiki-taka/login.php?msg=logout_success');
exit();