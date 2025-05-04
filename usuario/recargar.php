<?php

include '../includes/database.php';
include '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto = $_POST['monto'];
    $user_id = $_SESSION['user_id'];

    // Simular pasarela de pago (en un proyecto real, usarías PayPal, Stripe, etc.)
    $conn->query("INSERT INTO recargas (usuario_id, monto, estado) VALUES ($user_id, $monto, 'completada')");
    $conn->query("UPDATE usuarios SET saldo = saldo + $monto WHERE id = $user_id");

    $_SESSION['mensaje'] = "¡Recarga exitosa! Saldo actualizado.";
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
</head>
<body>
    <?php include '../includes/navbar_usuario.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3>Recargar Saldo</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Monto a Recargar (Mínimo $10,000)</label>
                                <select name="monto" class="form-select" required>
                                    <option value="10000">$10,000</option>
                                    <option value="20000">$20,000</option>
                                    <option value="50000">$50,000</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Confirmar Recarga</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>