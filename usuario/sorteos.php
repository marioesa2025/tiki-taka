<?php
require __DIR__ . '/../includes/database.php';
require __DIR__ . '/../includes/auth_check.php';

// Obtener sorteos activos
$sorteos = $conn->query("
    SELECT s.*, 
           (SELECT COUNT(*) FROM participantes_sorteos WHERE usuario_id = {$_SESSION['user_id']} AND sorteo_id = s.id) as participando
    FROM sorteos s
    WHERE s.estado = 'activo' 
    AND s.fecha_inicio <= NOW() 
    AND s.fecha_fin >= NOW()
    ORDER BY s.fecha_inicio
")->fetch_all(MYSQLI_ASSOC);

// Obtener horarios de sorteos
foreach ($sorteos as &$sorteo) {
    $sorteo['horarios'] = $conn->query("
        SELECT * FROM horarios_sorteos 
        WHERE sorteo_id = {$sorteo['id']}
        AND fecha_hora >= NOW()
        ORDER BY fecha_hora
    ")->fetch_all(MYSQLI_ASSOC);
}
unset($sorteo); // Romper la referencia
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorteos Disponibles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sorteo-card {
            transition: all 0.3s;
            border-left: 4px solid #6f42c1;
        }
        .sorteo-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .horario-item {
            border-left: 3px solid #6f42c1;
            padding-left: 10px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar_usuario.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4"><i class="fas fa-ticket-alt me-2"></i>Sorteos Disponibles</h1>

        <?php if (empty($sorteos)): ?>
            <div class="alert alert-info">
                Actualmente no hay sorteos disponibles. Vuelve a revisar más tarde.
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($sorteos as $sorteo): ?>
            <div class="col-md-6 mb-4">
                <div class="card sorteo-card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($sorteo['nombre']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            Premio: <?= htmlspecialchars($sorteo['premio']) ?>
                        </h6>
                        <p class="card-text"><?= htmlspecialchars($sorteo['descripcion']) ?></p>
                        
                        <div class="mb-3">
                            <h6><i class="fas fa-calendar-day me-2"></i>Próximos Horarios:</h6>
                            <?php foreach ($sorteo['horarios'] as $horario): ?>
                                <div class="horario-item">
                                    <strong><?= date('d/m/Y H:i', strtotime($horario['fecha_hora'])) ?></strong>
                                    <small class="text-muted">(<?= ucfirst($horario['repeticion']) ?>)</small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <?php if ($sorteo['participando']): ?>
                            <button class="btn btn-success" disabled>
                                <i class="fas fa-check-circle me-2"></i>Ya participas
                            </button>
                            <a href="ver_sorteo.php?id=<?= $sorteo['id'] ?>" class="btn btn-outline-primary">
                                <i class="fas fa-info-circle me-2"></i>Detalles
                            </a>
                        <?php else: ?>
                            <a href="participar_sorteo.php?id=<?= $sorteo['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Participar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>