<?php

include 'includes/database.php';
include 'includes/auth.php'; // Verifica sesión y aprobación

// Validar si se envió un sorteo válido
$sorteo_id = $_GET['sorteo'] ?? null;
$sorteo = $conn->query("SELECT * FROM sorteos WHERE id = $sorteo_id AND estado = 'pendiente'")->fetch_assoc();

if (!$sorteo) {
    header("Location: usuario/dashboard.php?error=sorteo_no_valido");
    exit();
}

// Procesar apuesta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estrellas = $_POST['estrellas'];
    $usuario_id = $_SESSION['user_id'];
    $saldo = $conn->query("SELECT saldo FROM usuarios WHERE id = $usuario_id")->fetch_assoc()['saldo'];
    $monto_apuesta = $estrellas * 1000;

    // Validar saldo suficiente
    if ($saldo >= $monto_apuesta) {
        $conn->query("INSERT INTO apuestas (usuario_id, sorteo_id, estrellas) VALUES ($usuario_id, $sorteo_id, $estrellas)");
        $conn->query("UPDATE usuarios SET saldo = saldo - $monto_apuesta WHERE id = $usuario_id");
        $conn->query("UPDATE sorteos SET total_apostado = total_apostado + $monto_apuesta WHERE id = $sorteo_id");
        
        $_SESSION['mensaje'] = "¡Apuesta realizada! Se descontaron $".number_format($monto_apuesta, 2);
        header("Location: usuario/dashboard.php");
        exit();
    } else {
        $error = "Saldo insuficiente. Recarga tu cuenta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Apostar - Sorteo #<?= $sorteo_id ?></title>
</head>
<body>
    <?php include 'includes/navbar_usuario.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3>Apostar en Sorteo #<?= $sorteo_id ?></h3>
                        <p class="mb-0">Fecha: <?= date('d/m/Y H:i', strtotime($sorteo['fecha_hora'])) ?></p>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-warning"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Selecciona estrellas (cada una vale $1,000)</label>
                                <select name="estrellas" class="form-select" required>
                                    <option value="1">1 Estrella ($1,000)</option>
                                    <option value="2">2 Estrellas ($2,000)</option>
                                    <option value="3">3 Estrellas ($3,000)</option>
                                    <option value="4">4 Estrellas ($4,000)</option>
                                    <option value="5">5 Estrellas ($5,000)</option>
                                </select>
                            </div>
                            <div class="alert alert-info">
                                <strong>Premio:</strong> El ganador recibe el 75% del total apostado.
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Confirmar Apuesta</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>