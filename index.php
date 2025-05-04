<?php
// Inicia sesión
session_start();

include 'includes/database.php';

// Redirigir al dashboard si ya está logueado
if (isset($_SESSION['user_id'])) {
    header("Location: usuario/dashboard.php");
    exit();
}

// Preparar consulta para obtener sorteos pendientes
$stmt = $conn->prepare("SELECT * FROM sorteos WHERE estado = ? AND fecha_inicio > NOW() ORDER BY fecha_fin ASC LIMIT 3");
$estado = 'pendiente';
$stmt->bind_param("s", $estado);
$stmt->execute();
$sorteos = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apuestas Online - Plataforma de Sorteos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (iconos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-star"></i> Tiki-Taka Fsa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#sorteos"><i class="fas fa-trophy"></i> Sorteos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Ingresar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary ms-2 text-white" href="registro.php"><i class="fas fa-user-plus"></i> Registrarse</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section (Banner principal) -->
    <header class="hero bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">¡Gana hasta el 75% del pozo!</h1>
            <p class="lead">Apuesta estrellas en nuestros sorteos y participa por premios increíbles</p>
            <a href="registro.php" class="btn btn-light btn-lg mt-3">
                <i class="fas fa-play"></i> Empezar ahora
            </a>
        </div>
    </header>

    <!-- Sección de Sorteos Destacados -->
    <section id="sorteos" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Próximos Sorteos</h2>
            <div class="row">
                <?php if ($sorteos->num_rows > 0): ?>
                    <?php while ($sorteo = $sorteos->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="card-title mb-0">Sorteo #<?= htmlspecialchars($sorteo['id']) ?></h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <i class="fas fa-calendar-alt"></i> 
                                        <strong><?= date('d/m/Y H:i', strtotime($sorteo['fecha_hora'])) ?></strong>
                                    </p>
                                    <p class="card-text">
                                        <i class="fas fa-coins"></i> 
                                        Pozo actual: <strong>$<?= number_format($sorteo['total_apostado'], 2) ?></strong>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="login.php" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-star"></i> Apostar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info">No hay sorteos programados. ¡Vuelve pronto!</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">© 2025 Abril Tiki-Taka - Plataforma de Apuestas Online</p>
            <small>Todos los derechos reservados</small>
        </div>
    </footer>

    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>