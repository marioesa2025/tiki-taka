<?php
session_start();

// 1. Configuración y conexión a la BD
require __DIR__ . '/includes/database.php'; // Asegúrate de que esta ruta sea correcta

// 2. Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar campos
        if (empty($_POST['email']) || empty($_POST['password'])) {
            throw new Exception("Email y contraseña son requeridos");
        }

        // Sanitizar entrada
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // 3. Buscar usuario en la BD
        $stmt = $conn->prepare("SELECT id, nombre, email, password, es_admin, aprobado FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // 4. Verificar si el usuario existe
        if ($result->num_rows === 0) {
            throw new Exception("Usuario no encontrado");
        }

        $usuario = $result->fetch_assoc();

        // 5. Validar contraseña y estado
        if (!password_verify($password, $usuario['password'])) {
            throw new Exception("Contraseña incorrecta");
        }

        if ($usuario['aprobado'] != 1) {
            throw new Exception("Cuenta sin aprobar. Contacta al administrador");
        }

        // 6. Crear sesión segura
        $_SESSION = [
            'user_id'    => $usuario['id'],
            'user_nombre' => $usuario['nombre'],
            'user_email' => $usuario['email'],
            'es_admin'   => $usuario['es_admin'],
            'ip'         => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ];

        // 7. Redirección según rol
        $redirect = $usuario['es_admin'] ? 'admin/dashboard.php' : 'dashboard.php';
        header("Location: $redirect");
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-box { max-width: 400px; margin: 5% auto; padding: 20px; background: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h2 class="text-center mb-4">Iniciar Sesión</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="d-flex justify-content-between mt-3">
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-home"></i> Regresar al Inicio
    </a>
    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
</div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>