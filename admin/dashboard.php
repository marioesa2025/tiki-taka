<?php
/**
 * admin/dashboard.php - Panel de Administración
 * Versión final confirmada - [Fecha Actual]
 * 
 * Funcionalidades clave:
 * 1. Gestión completa de usuarios (aprobación/rechazo)
 * 2. Visualización de estadísticas del sistema
 * 3. Registro de actividades administrativas
 * 4. Configuración del sistema
 */

// ================= CONFIGURACIÓN INICIAL ================= //
session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'use_strict_mode' => true
]);

// ================= VERIFICACIÓN DE PERMISOS ================= //
if (!isset($_SESSION['user_id']) || !isset($_SESSION['es_admin']) || !$_SESSION['es_admin']) {
    header('Location: /dashboard/tiki-taka/login.php?error=admin_required');
    exit();
}

// ================= CONEXIÓN Y VERIFICACIÓN DE BD ================= //
try {
    require $_SERVER['DOCUMENT_ROOT'] . '/dashboard/tiki-taka/includes/database.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    // Verificación de tablas esenciales
    $conn->query("SELECT 1 FROM usuarios LIMIT 1");
    $conn->query("SELECT 1 FROM logs LIMIT 1");
    
    // Verificación de columnas requeridas
    $result = $conn->query("SHOW COLUMNS FROM usuarios");
    $columns = array_column($result->fetch_all(), 0);
    foreach (['aprobado', 'es_admin', 'ultimo_login'] as $col) {
        if (!in_array($col, $columns)) {
            throw new Exception("Columna requerida faltante: $col");
        }
    }

} catch (Exception $e) {
    error_log("Error en dashboard admin: " . $e->getMessage());
    die("Error en la configuración del sistema. Contacte al desarrollador.");
}

// ================= CARGA DE DATOS ================= //
$admin_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
if ($admin_id <= 0) {
    header('Location: logout.php?error=invalid_admin');
    exit();
}

try {
    // Datos del admin
    $stmt = $conn->prepare("SELECT id, nombre, email, foto_perfil FROM usuarios WHERE id = ? AND es_admin = 1");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    
    if (!$admin) {
        throw new Exception("Credenciales de administrador no válidas");
    }

    // Estadísticas
    $stats = [
        'total_usuarios' => $conn->query("SELECT COUNT(*) FROM usuarios")->fetch_row()[0],
        'usuarios_activos' => $conn->query("SELECT COUNT(*) FROM usuarios WHERE ultimo_login > DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_row()[0],
        'usuarios_pendientes' => $conn->query("SELECT COUNT(*) FROM usuarios WHERE aprobado = 0")->fetch_row()[0]
    ];
    
    // Usuarios pendientes
    $pendientes = $conn->query("
        SELECT id, nombre, email, fecha_registro 
        FROM usuarios 
        WHERE aprobado = 0 
        ORDER BY fecha_registro DESC 
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);
    
    // Actividades recientes
    $actividades = $conn->query("
        SELECT l.usuario_id, u.nombre, l.accion, l.fecha 
        FROM logs l
        JOIN usuarios u ON l.usuario_id = u.id
        ORDER BY l.fecha DESC 
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Error al cargar datos admin: " . $e->getMessage());
    die("Error al cargar datos del sistema. Intente nuevamente.");
}

// ================= FUNCIÓN AUXILIAR ================= //
function getAdminProfileImage($filename) {
    $base_path = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/tiki-taka/assets/img/usuarios/';
    if (!empty($filename) && file_exists($base_path . $filename)) {
        return '../assets/img/usuarios/' . $filename;
    }
    return '../assets/img/usuarios/admin_default.png';
}
?>

<!-- ================= ESTRUCTURA HTML ================= -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - <?= htmlspecialchars($admin['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --admin-color: #6f42c1;
            --danger-color: #dc3545;
            --warning-color: #fd7e14;
        }
        .admin-nav { background-color: var(--admin-color); }
        .admin-profile {
            width: 150px;
            height: 150px;
            border: 4px solid var(--admin-color);
        }
        .stat-card {
            border-left: 4px solid var(--admin-color);
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .nav-pills .nav-link.active { background-color: var(--admin-color); }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark admin-nav mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt me-2"></i>Panel de Administración
            </a>
            <div class="d-flex align-items-center text-white">
                <span class="me-3 d-none d-sm-inline">
                    <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($admin['nombre']) ?>
                </span>
                <a href="../logout.php" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="<?= getAdminProfileImage($admin['foto_perfil'] ?? '') ?>" 
                             class="rounded-circle admin-profile mb-2"
                             alt="Admin"
                             onerror="this.src='../assets/img/usuarios/admin_default.png'">
                        <h6><?= htmlspecialchars($admin['nombre']) ?></h6>
                        <span class="badge rounded-pill text-white" style="background-color: var(--admin-color);">
                            Administrador
                        </span>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="configurar_sorteos.php" data-bs-toggle="tab">
                                <i class="fas fa-calendar-alt me-2"></i> Configurar Sorteos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#usuarios" data-bs-toggle="tab">
                                <i class="fas fa-users me-2"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#logs" data-bs-toggle="tab">
                                <i class="fas fa-clipboard-list me-2"></i> Actividades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#config" data-bs-toggle="tab">
                                <i class="fas fa-cog me-2"></i> Configuración
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Contenido Principal -->
            <div class="col-md-9 col-lg-10 px-md-4">
                <div class="tab-content">
                    <!-- Pestaña Dashboard -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <!-- Sección de Estadísticas -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-muted">Usuarios Totales</h5>
                                        <h2 class="text-primary"><?= number_format($stats['total_usuarios']) ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-muted">Usuarios Activos</h5>
                                        <h2 class="text-success"><?= number_format($stats['usuarios_activos']) ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card stat-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-muted">Pendientes</h5>
                                        <h2 class="text-warning"><?= number_format($stats['usuarios_pendientes']) ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección de Aprobaciones -->
                        <div class="card mb-4">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Aprobaciones Pendientes</h5>
                                <span class="badge bg-warning text-dark"><?= count($pendientes) ?> pendientes</span>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($pendientes)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nombre</th>
                                                    <th>Email</th>
                                                    <th>Registro</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pendientes as $user): ?>
                                                <tr>
                                                    <td><?= $user['id'] ?></td>
                                                    <td><?= htmlspecialchars($user['nombre']) ?></td>
                                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($user['fecha_registro'])) ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="aprobar_usuario.php?id=<?= $user['id'] ?>" 
                                                               class="btn btn-success"
                                                               title="Aprobar">
                                                               <i class="fas fa-check"></i>
                                                            </a>
                                                            <a href="rechazar_usuario.php?id=<?= $user['id'] ?>" 
                                                               class="btn btn-danger"
                                                               title="Rechazar">
                                                               <i class="fas fa-times"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success mb-0">
                                        <i class="fas fa-check-circle me-2"></i> No hay usuarios pendientes.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Sección de Actividades -->
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Actividad Reciente</h5>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($actividades as $log): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?= htmlspecialchars($log['nombre']) ?></strong>
                                                <div class="text-muted small"><?= htmlspecialchars($log['accion']) ?></div>
                                            </div>
                                            <span class="text-muted small">
                                                <?= date('d/m/Y H:i', strtotime($log['fecha'])) ?>
                                            </span>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Otras pestañas -->
                    <div class="tab-pane fade" id="usuarios">...</div>
                    <div class="tab-pane fade" id="logs">...</div>
                    <div class="tab-pane fade" id="config">...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tooltips y mejoras de accesibilidad
        document.addEventListener('DOMContentLoaded', function() {
            // Activar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function (element) {
                return new bootstrap.Tooltip(element);
            });
            
            // Mejorar navegación por teclado
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('keydown', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        new bootstrap.Tab(link).show();
                    }
                });
            });
        });
    </script>
</body>
</html>