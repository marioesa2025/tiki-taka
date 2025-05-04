<?php
/**
 * dashboard.php - Panel de usuario no administrador
 * Versión en producción - Validada el [Fecha]
 * 
 * Características confirmadas:
 * 1. Seguridad reforzada en manejo de sesiones
 * 2. Diseño responsive mejorado
 * 3. Integración con Font Awesome 6
 * 4. Manejo profesional de imágenes de perfil
 * 5. Estructura de código optimizada
 */

// 1. Configuración de sesión segura
session_start([
    'cookie_lifetime' => 86400,     // 24 horas de duración
    'cookie_httponly' => true,      // Solo accesible por HTTP
    'cookie_secure' => true,        // Solo en conexiones HTTPS
    'use_strict_mode' => true       // Mejor seguridad de sesión
]);

// 2. Verificación de autenticación con redirección inteligente
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect='.urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// 3. Conexión a base de datos con manejo profesional de errores
require $_SERVER['DOCUMENT_ROOT'] . '/dashboard/tiki-taka/includes/database.php';

// 4. Validación estricta de ID de usuario
$user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
if ($user_id <= 0) {
    header('Location: logout.php?error=invalid_id');
    exit();
}

// 5. Consulta segura de datos del usuario
try {
    $stmt = $conn->prepare("SELECT nombre, email, foto_perfil, fecha_registro FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        header('Location: logout.php?error=user_not_found');
        exit();
    }
} catch (Exception $e) {
    error_log("Database error in dashboard.php: " . $e->getMessage());
    die("Error al cargar los datos. Por favor intenta más tarde.");
}

// Función para manejo seguro de imágenes de perfil
function getProfileImage($filename) {
    $base_path = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/tiki-taka/assets/img/usuarios/';
    $default_image = 'default.jpg';
    
    if (!empty($filename) && file_exists($base_path . $filename)) {
        return 'assets/img/usuarios/' . $filename;
    }
    return 'assets/img/usuarios/' . $default_image;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - <?= htmlspecialchars($user['nombre']) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
        }
        
        .profile-img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .profile-img:hover {
            transform: scale(1.05);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        
        .list-group-item-action:hover {
            background-color: #f8f9fa;
        }
        
        .user-info-item {
            border-left: 3px solid var(--primary-color);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar_usuario.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Tarjeta principal del perfil -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Mi Perfil</h4>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-user-clock me-1"></i>
                            Miembro desde <?= date('d/m/Y', strtotime($user['fecha_registro'])) ?>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <!-- Sección de presentación -->
                        <div class="text-center mb-4">
                            <img src="<?= getProfileImage($user['foto_perfil'] ?? '') ?>" 
                                 class="profile-img mb-3"
                                 alt="Foto de perfil de <?= htmlspecialchars($user['nombre']) ?>"
                                 onerror="this.src='assets/img/usuarios/default.jpg'">
                            <h3 class="mb-1"><?= htmlspecialchars($user['nombre']) ?></h3>
                            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        
                        <!-- Menú de acciones -->
                        <div class="list-group">
                            <div class="list-group-item user-info-item">
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-envelope me-2 text-primary"></i> <strong>Email:</strong></span>
                                    <span><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                            </div>
                            
                            <a href="editar_perfil.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-user-edit me-2 text-primary"></i> Editar perfil
                            </a>
                            
                            <a href="cambiar_password.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-lock me-2 text-primary"></i> Cambiar contraseña
                            </a>
                            
                            <a href="mis_actividades.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-history me-2 text-primary"></i> Mi actividad reciente
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Botón de navegación -->
                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="fas fa-home me-1"></i> Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts esenciales -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Mejoras de accesibilidad -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('focus', function() {
                    this.style.outline = '2px solid var(--primary-color)';
                    this.style.outlineOffset = '2px';
                });
                link.addEventListener('blur', function() {
                    this.style.outline = 'none';
                });
            });
        });
    </script>
</body>
</html>