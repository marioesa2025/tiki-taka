<?php
/**
 * Verificación de administrador
 */
session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'use_strict_mode' => true
]);

if (!isset($_SESSION['user_id']) || !isset($_SESSION['es_admin']) || !$_SESSION['es_admin']) {
    header('Location: /dashboard/tiki-taka/login.php?error=admin_required');
    exit();
}

$admin_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
if ($admin_id <= 0) {
    header('Location: logout.php?error=invalid_admin');
    exit();
}