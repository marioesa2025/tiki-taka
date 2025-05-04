<?php
require __DIR__ . '/../includes/database.php';
//require __DIR__ . 'includes/auth_check.php';

// Verificar permisos de administrador
if (!$_SESSION['es_admin']) {
    header('Location: /dashboard/tiki-taka/index.php');
    exit();
}

// Procesar formulario de nuevo sorteo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        // Insertar sorteo principal
        $stmt = $conn->prepare("INSERT INTO sorteos 
            (nombre, descripcion, fecha_inicio, fecha_fin, estado, premio, creado_por) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssssi", 
            $_POST['nombre'],
            $_POST['descripcion'],
            $_POST['fecha_inicio'],
            $_POST['fecha_fin'],
            $_POST['estado'],
            $_POST['premio'],
            $_SESSION['user_id']
        );
        $stmt->execute();
        $sorteo_id = $conn->insert_id;

        // Insertar horarios
        $stmtHorarios = $conn->prepare("INSERT INTO horarios_sorteos 
            (sorteo_id, fecha_hora, repeticion) 
            VALUES (?, ?, ?)");
        
        foreach ($_POST['horarios'] as $horario) {
            $stmtHorarios->bind_param("iss", $sorteo_id, $horario['fecha_hora'], $horario['repeticion']);
            $stmtHorarios->execute();
        }

        $conn->commit();
        $_SESSION['mensaje'] = "Sorteo creado exitosamente";
        header('Location: gestion_sorteos.php');
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error al crear el sorteo: " . $e->getMessage();
    }
}

// Obtener lista de sorteos
$sorteos = $conn->query("
    SELECT s.*, COUNT(p.id) as participantes 
    FROM sorteos s
    LEFT JOIN participantes_sorteos p ON s.id = p.sorteo_id
    GROUP BY s.id
    ORDER BY s.fecha_inicio DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Sorteos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .sorteo-card {
            transition: transform 0.3s;
        }
        .sorteo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .badge-estado {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../includes/navbar_admin.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Gestión de Sorteos</h1>

        <!-- Mensajes de éxito/error -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Card de nuevo sorteo -->
        <div class="card mb-4">
            <div class="card-header">
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#nuevoSorteo">
                    <i class="fas fa-plus me-2"></i>Nuevo Sorteo
                </button>
            </div>
            <div class="collapse" id="nuevoSorteo">
                <div class="card-body">
                    <form method="POST" id="formSorteo">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre del Sorteo</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Premio</label>
                                <input type="text" name="premio" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="datetime-local" name="fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha Fin</label>
                                <input type="datetime-local" name="fecha_fin" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select" required>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="activo">Activo</option>
                                </select>
                            </div>
                        </div>

                        <!-- Horarios dinámicos -->
                        <div class="mb-3">
                            <label class="form-label">Horarios de Sorteo</label>
                            <div id="horarios-container">
                                <div class="horario-item row mb-2">
                                    <div class="col-md-8">
                                        <input type="datetime-local" name="horarios[0][fecha_hora]" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="horarios[0][repeticion]" class="form-select">
                                            <option value="unico">Único</option>
                                            <option value="diario">Diario</option>
                                            <option value="semanal">Semanal</option>
                                            <option value="mensual">Mensual</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-remove-horario" disabled>
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="btn-add-horario" class="btn btn-sm btn-secondary mt-2">
                                <i class="fas fa-plus me-1"></i>Añadir Horario
                            </button>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Guardar Sorteo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Listado de sorteos -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Sorteos Programados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Fechas</th>
                                <th>Horarios</th>
                                <th>Participantes</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sorteos as $sorteo): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($sorteo['nombre']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($sorteo['premio']) ?></small>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($sorteo['fecha_inicio'])) ?> - 
                                    <?= date('d/m/Y', strtotime($sorteo['fecha_fin'])) ?>
                                </td>
                                <td>
                                    <?php 
                                    $horarios = $conn->query("
                                        SELECT * FROM horarios_sorteos 
                                        WHERE sorteo_id = {$sorteo['id']}
                                        ORDER BY fecha_hora
                                    ")->fetch_all(MYSQLI_ASSOC);
                                    
                                    foreach ($horarios as $h) {
                                        echo date('H:i', strtotime($h['fecha_hora'])).' ('.$h['repeticion'].')<br>';
                                    }
                                    ?>
                                </td>
                                <td><?= $sorteo['participantes'] ?></td>
                                <td>
                                    <?php 
                                    $badgeClass = [
                                        'pendiente' => 'bg-secondary',
                                        'activo' => 'bg-success',
                                        'finalizado' => 'bg-primary',
                                        'cancelado' => 'bg-danger'
                                    ];
                                    ?>
                                    <span class="badge <?= $badgeClass[$sorteo['estado']] ?>">
                                        <?= ucfirst($sorteo['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editar_sorteo.php?id=<?= $sorteo['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="eliminar_sorteo.php?id=<?= $sorteo['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Eliminar este sorteo?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        // Configuración de Flatpickr para fechas
        flatpickr("input[type=datetime-local]", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            locale: "es"
        });

        // Manejo de horarios dinámicos
        let horarioCount = 1;
        document.getElementById('btn-add-horario').addEventListener('click', function() {
            const container = document.getElementById('horarios-container');
            const newItem = document.createElement('div');
            newItem.className = 'horario-item row mb-2';
            newItem.innerHTML = `
                <div class="col-md-8">
                    <input type="datetime-local" name="horarios[${horarioCount}][fecha_hora]" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <select name="horarios[${horarioCount}][repeticion]" class="form-select">
                        <option value="unico">Único</option>
                        <option value="diario">Diario</option>
                        <option value="semanal">Semanal</option>
                        <option value="mensual">Mensual</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-remove-horario">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(newItem);
            horarioCount++;
            
            // Habilitar botones de eliminar si hay más de un horario
            if (horarioCount > 1) {
                document.querySelectorAll('.btn-remove-horario').forEach(btn => {
                    btn.disabled = false;
                });
            }
        });

        // Eliminar horarios
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-horario')) {
                const item = e.target.closest('.horario-item');
                item.remove();
                horarioCount--;
                
                // Deshabilitar botones de eliminar si solo queda un horario
                if (horarioCount <= 1) {
                    document.querySelectorAll('.btn-remove-horario').forEach(btn => {
                        btn.disabled = true;
                    });
                }
            }
        });
    </script>
</body>
</html>