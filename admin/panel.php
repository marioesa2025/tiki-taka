<?php
// admin/panel.php - Panel de Administración
session_start();

// Verificar permisos de admin
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header('Location: ../login.php?error=no_admin');
    exit();
}

require __DIR__ . '/../includes/database.php';

// Aprobar usuarios
if (isset($_GET['aprobar'])) {
    $id = intval($_GET['aprobar']);
    $conn->query("UPDATE usuarios SET aprobado = 1 WHERE id = $id");
    $_SESSION['mensaje'] = "Usuario #$id aprobado";
    header("Location: panel.php");
    exit();
}

// Obtener usuarios pendientes
$pendientes = $conn->query("
    SELECT id, nombre, email, fecha_registro 
    FROM usuarios 
    WHERE aprobado = 0
    ORDER BY fecha_registro DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <title>Panel Admin - Tiki Taka</title>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar_admin.php'; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-user-cog"></i> Panel de Administración</h2>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-users"></i> Usuarios Pendientes</h5>
            </div>
            <div class="card-body">
                <?php if ($pendientes->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Fecha Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($usuario = $pendientes->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $usuario['id'] ?></td>
                                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                    <td>
                                        <a href="panel.php?aprobar=<?= $usuario['id'] ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('¿Aprobar este usuario?')">
                                            <i class="fas fa-check"></i> Aprobar
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No hay usuarios pendientes de aprobación</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>