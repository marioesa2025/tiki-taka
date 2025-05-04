<?php

include '../includes/database.php';

// Verificar si es admin (aquí debes implementar tu lógica de roles)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Aprobar usuario
if (isset($_GET['aprobar'])) {
    $id = $_GET['aprobar'];
    $conn->query("UPDATE usuarios SET aprobado = TRUE WHERE id = $id");
}

// Obtener usuarios pendientes
$pendientes = $conn->query("SELECT * FROM usuarios WHERE aprobado = FALSE");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../includes/header.php'; ?>
    <title>Panel Admin - Usuarios</title>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Usuarios Pendientes de Aprobación</h2>
        <div class="table-responsive">
            <table class="table table-striped">
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
                        <td><?= $usuario['nombre'] ?></td>
                        <td><?= $usuario['email'] ?></td>
                        <td><?= $usuario['fecha_registro'] ?></td>
                        <td>
                            <a href="?aprobar=<?= $usuario['id'] ?>" class="btn btn-sm btn-success">Aprobar</a>
                            <a href="#" class="btn btn-sm btn-danger">Rechazar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>