<?php
// resize_image.php
require __DIR__ . '../../includes/database.php';

if (isset($_GET['img']) && isset($_GET['w']) && isset($_GET['h'])) {
    $image = $_GET['img'];
    $width = (int)$_GET['w'];
    $height = (int)$_GET['h'];
    
    // Validar ruta segura
    $imagePath = realpath(__DIR__ . '../../essets/img/usuarios/' . basename($image));
    
    if ($imagePath && file_exists($imagePath)) {
        header('Content-Type: image/jpeg');
        // ... (código de redimensionado similar al de arriba)
        exit;
    }
}
header("HTTP/1.0 404 Not Found");