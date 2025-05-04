<?php
/**
 * admin/configurar_sorteos.php - Gestión de Sorteos
 * 
 * Funcionalidades:
 * 1. Crear nuevos sorteos
 * 2. Editar sorteos existentes
 * 3. Ver sorteos vigentes
 * 4. Gestionar participantes
 */

// 1. Configuración inicial y seguridad
require __DIR__ . '../../includes/verificar_admin.php'; // Script que verifica sesión admin

// 2. Conexión a base de datos
require __DIR__ . '../../includes/database.php';

// 3. Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['crear_sorteo'])) {
            // Validar y crear nuevo sorteo
            $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];
            $premio = filter_var($_POST['premio'], FILTER_SANITIZE_STRING);
            $max_participantes = filter_var($_POST['max_participantes'], FILTER_VALIDATE_INT);
            
            $stmt = $conn->prepare("INSERT INTO sorteos (nombre, fecha_inicio, fecha_fin, premio, max_participantes, estado) 
                                   VALUES (?, ?, ?, ?, ?, 'pendiente')");
            $stmt->bind_param("ssssi", $nombre, $fecha_inicio, $fecha_fin, $premio, $max_participantes);
            $stmt->execute();
            
            $_SESSION['mensaje_exito'] = "Sorteo creado exitosamente!";
        }
        
        if (isset($_POST['actualizar_sorteo'])) {
            // Actualizar sorteo existente
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $estado = $_POST['estado'];
            
            $stmt = $conn->prepare("UPDATE sorteos SET estado = ? WHERE id = ?");
            $stmt->bind_param("si", $estado, $id);
            $stmt->execute();
            
            $_SESSION['mensaje_exito'] = "Sorteo actualizado!";
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: configurar_sorteos.php");
    exit();
}

// 4. Obtener datos para mostrar
try {
    // Sorteos vigentes
    $sorteos_vigentes = $conn->query("
        SELECT s.*, COUNT(p.id) as participantes
        FROM sorteos s
        LEFT JOIN participantes_sorteos p ON s.id = p.sorteo_id
        WHERE s.fecha_fin >= CURDATE()
        GROUP BY s.id
        ORDER BY s.fecha_inicio DESC
    ")->fetch_all(MYSQLI_ASSOC);
    
    // Sorteos pasados
    $sorteos_pasados = $conn->query("
        SELECT s.*, COUNT(p.id) as participantes, u.nombre as ganador
        FROM sorteos s
        LEFT JOIN participantes_sorteos p ON s.id = p.sorteo_id
        LEFT JOIN usuarios u ON s.ganador_id = u.id
        WHERE s.fecha_fin < CURDATE()
        GROUP BY s.id
        ORDER BY s.fecha_fin DESC
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);
    
    // Total de sorteos
    $total_sorteos = $conn->query("SELECT COUNT(*) FROM sorteos")->fetch_row()[0];
    
} catch (Exception $e) {
    die("Error al cargar sorteos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Sorteos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-sorteo {
            transition: transform 0.3s;
            border-left: 4px solid #6f42c1;
        }
        .card-sorteo:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge-estado {
            font-size: 0.9em;
        }
        .progress {
            height: 10px;
        }
        .tabla-sorteos th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar_admin.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <?php include __DIR__ . '../sidebar_admin.php'; ?>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-md-4">
                <h2 class="mb-4"><i class="fas fa-gift me-2"></i> Configuración de Sorteos</h2>
                
                <!-- Mensajes de feedback -->
                <?php if (isset($_SESSION['mensaje_exito'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['mensaje_exito'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_exito']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['mensaje_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['mensaje_error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_error']); ?>
                <?php endif; ?>
                
                <!-- Pestañas -->
                <ul class="nav nav-tabs mb-4" id="sorteosTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="vigentes-tab" data-bs-toggle="tab" data-bs-target="#vigentes" type="button">
                            <i class="fas fa-calendar-check me-1"></i> Sorteos Vigentes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo" type="button">
                            <i class="fas fa-plus-circle me-1"></i> Crear Nuevo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button">
                            <i class="fas fa-history me-1"></i> Historial
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="sorteosTabsContent">
                    <!-- Pestaña Sorteos Vigentes -->
                    <div class="tab-pane fade show active" id="vigentes" role="tabpanel">
                        <div class="row">
                            <?php foreach ($sorteos_vigentes as $sorteo): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card card-sorteo h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><?= htmlspecialchars($sorteo['nombre']) ?></h5>
                                        <span class="badge bg-<?= 
                                            $sorteo['estado'] == 'activo' ? 'success' : 
                                            ($sorteo['estado'] == 'pendiente' ? 'warning' : 'secondary') 
                                        ?> badge-estado">
                                            <?= ucfirst($sorteo['estado']) ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?= htmlspecialchars($sorteo['premio']) ?></p>
                                        <p><i class="fas fa-calendar-day me-2"></i> 
                                            <?= date('d/m/Y', strtotime($sorteo['fecha_inicio'])) ?> - 
                                            <?= date('d/m/Y', strtotime($sorteo['fecha_fin'])) ?>
                                        </p>
                                        
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span>Participantes:</span>
                                                <span><?= $sorteo['participantes'] ?> / <?= $sorteo['max_participantes'] ?></span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-striped" 
                                                     role="progressbar" 
                                                     style="width: <?= ($sorteo['participantes']/$sorteo['max_participantes'])*100 ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <div class="d-flex justify-content-between">
                                            <a href="ver_sorteo.php?id=<?= $sorteo['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <a href="editar_sorteo.php?id=<?= $sorteo['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($sorteos_vigentes)): ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    No hay sorteos vigentes en este momento.
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Pestaña Nuevo Sorteo -->
                    <div class="tab-pane fade" id="nuevo" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Crear Nuevo Sorteo</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="configurar_sorteos.php">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="nombre" class="form-label">Nombre del Sorteo*</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="premio" class="form-label">Premio*</label>
                                            <input type="text" class="form-control" id="premio" name="premio" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="fecha_inicio" class="form-label">Fecha de Inicio*</label>
                                            <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="fecha_fin" class="form-label">Fecha de Cierre*</label>
                                            <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="max_participantes" class="form-label">Máx. Participantes*</label>
                                            <input type="number" class="form-control" id="max_participantes" name="max_participantes" min="1" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="1"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" name="crear_sorteo" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Guardar Sorteo
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pestaña Historial -->
                    <div class="tab-pane fade" id="historial" role="tabpanel">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i> Historial de Sorteos</h5>
                                <span class="badge bg-primary">Total: <?= $total_sorteos ?></span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover tabla-sorteos mb-0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Fecha</th>
                                                <th>Premio</th>
                                                <th>Participantes</th>
                                                <th>Ganador</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sorteos_pasados as $sorteo): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($sorteo['nombre']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($sorteo['fecha_fin'])) ?></td>
                                                <td><?= htmlspecialchars($sorteo['premio']) ?></td>
                                                <td><?= $sorteo['participantes'] ?></td>
                                                <td><?= $sorteo['ganador'] ?? 'N/A' ?></td>
                                                <td>
                                                    <span class="badge bg-<?= 
                                                        $sorteo['estado'] == 'completado' ? 'success' : 
                                                        ($sorteo['estado'] == 'cancelado' ? 'danger' : 'secondary') 
                                                    ?>">
                                                        <?= ucfirst($sorteo['estado']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <?php if (empty($sorteos_pasados)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    No hay sorteos en el historial
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de fechas en el formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const inicio = new Date(document.getElementById('fecha_inicio').value);
                    const fin = new Date(document.getElementById('fecha_fin').value);
                    
                    if (inicio >= fin) {
                        e.preventDefault();
                        alert('La fecha de fin debe ser posterior a la fecha de inicio');
                        return false;
                    }
                    
                    const max = parseInt(document.getElementById('max_participantes').value);
                    if (max < 1) {
                        e.preventDefault();
                        alert('El número de participantes debe ser al menos 1');
                        return false;
                    }
                });
            }
            
            // Activar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function (element) {
                return new bootstrap.Tooltip(element);
            });
        });
    </script>
</body>
</html>