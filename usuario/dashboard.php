<?php
// 1. Control de sesión seguro
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// 2. Verificación de autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?error=no_autenticado');
    exit();
}

// 3. Conexión a la base de datos
require __DIR__ . '/../includes/database.php';

// 4. Obtener ID de usuario con validación
$user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
if ($user_id === false || $user_id <= 0) {
    die("ID de usuario inválido");
}

// 5. Consulta segura del usuario actual
try {
    $stmt = $conn->prepare("SELECT nombre, email, saldo, foto_perfil, aprobado, es_admin FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        header('Location: ../login.php?error=usuario_no_encontrado');
        exit();
    }

    // 6. Verificar aprobación (excepto para admins)
    if (!$user['aprobado'] && !($user['es_admin'] ?? false)) {
        header('Location: ../logout.php?error=cuenta_no_aprobada');
        exit();
    }

} catch (Exception $e) {
    die("Error de base de datos: " . $e->getMessage());
}

// 7. Obtener usuarios pendientes (solo para admin)
$pendientes = [];
if ($user['es_admin'] ?? false) {
    $pendientes = $conn->query("
        SELECT id, nombre, email, fecha_registro 
        FROM usuarios 
        WHERE aprobado = 0
        ORDER BY fecha_registro DESC
    ")->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <title>Panel - <?= htmlspecialchars($user['nombre']) ?></title>
    <style>
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 3px solid #0d6efd;
        }
        .admin-table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include ($user['es_admin'] ?? false) ? 
          __DIR__ . '/../includes/navbar_admin.php' : 
          __DIR__ . '/../includes/navbar_usuario.php'; ?>
    
    <div class="container py-4">
        <!-- Tarjeta de Bienvenida -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Bienvenido, <?= htmlspecialchars($user['nombre']) ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="resize_image.php?img=<?= urlencode($user['foto_perfil']) ?>&w=150&h=150" 
     class="rounded-circle">
                    </div>
                    <div class="col-md-8">
                        <h5>Información de Cuenta</h5>
                        <ul class="list-group mb-3">
                            <li class="list-group-item">
                                <strong>Email:</strong> <?= htmlspecialchars($user['email']) ?>
                            </li>
                            <li class="list-group-item">
                                <strong>Saldo:</strong> $<?= number_format($user['saldo'], 2) ?>
                            </li>
                            <li class="list-group-item">
                                <strong>Estado:</strong> 
                                <?= $user['aprobado'] ? '✅ Verificado' : '⏳ Pendiente' ?>
                                <?= ($user['es_admin'] ?? false) ? ' | 👑 Administrador' : '' ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Administración -->
        <?php if ($user['es_admin'] ?? false): ?>
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i> Usuarios Pendientes</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($pendientes)): ?>
                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendientes as $p): ?>
                                    <tr>
                                        <td><?= $p['id'] ?></td>
                                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                                        <td><?= htmlspecialchars($p['email']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($p['fecha_registro'])) ?></td>
                                        <td>
                                            <a href="../admin/aprobar_usuario.php?id=<?= $p['id'] ?>" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('¿Aprobar a <?= htmlspecialchars(addslashes($p['nombre'])) ?>?')">
                                               <i class="fas fa-check"></i> Aprobar
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-check-circle me-2"></i> No hay usuarios pendientes.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>