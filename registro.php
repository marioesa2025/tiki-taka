<?php
session_start();
require __DIR__ . '/includes/database.php';

$errores = [];
$nombre = $email = '';
$foto_perfil = 'default.jpg'; // Imagen por defecto

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitizar datos básicos
    $nombre = trim($_POST['nombre'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 2. Validaciones
    if (empty($nombre)) $errores[] = "Nombre requerido";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "Email inválido";
    if (strlen($password) < 8) $errores[] = "La contraseña debe tener al menos 8 caracteres";
    if ($password !== $confirm_password) $errores[] = "Las contraseñas no coinciden";

    // 3. Procesar imagen
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['foto_perfil'];
        
        // Validar tipo y tamaño (máx 2MB)
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($foto['type'], $tiposPermitidos)) {
            $errores[] = "Solo se permiten imágenes JPG, PNG o WEBP";
        } elseif ($foto['size'] > $maxSize) {
            $errores[] = "La imagen no debe superar 2MB";
        } else {
            // Crear nombre único y mover archivo
            $extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $foto_perfil = 'perfil_' . uniqid() . '.' . $extension;
            $rutaDestino = __DIR__ . '/assets/img/usuarios/' . $foto_perfil;
            
            if (!move_uploaded_file($foto['tmp_name'], $rutaDestino)) {
                $errores[] = "Error al subir la imagen";
            }
        }
    }

    // 4. Verificar email único si no hay errores
    if (empty($errores)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $errores[] = "Email ya registrado";
            }
        } catch (Exception $e) {
            $errores[] = "Error al verificar usuario: " . $e->getMessage();
        }
    }

    // 5. Registrar usuario
    if (empty($errores)) {
        try {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, saldo, foto_perfil, aprobado, es_admin) 
                                   VALUES (?, ?, ?, 5000, ?, 0, 0)");
            $stmt->bind_param("ssss", $nombre, $email, $password_hash, $foto_perfil);
            $stmt->execute();

            $_SESSION['mensaje'] = "¡Registro exitoso! Espera aprobación del administrador";
            header("Location: login.php");
            exit();

        } catch (Exception $e) {
            $errores[] = "Error al registrar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro con Foto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .register-box { max-width: 600px; }
        .preview-img { 
            width: 120px; 
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="register-box mx-auto p-4 bg-white rounded shadow">
            <h2 class="text-center mb-4">Registro de Usuario</h2>
            
            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errores as $error): ?>
                        <p class="mb-1"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="preview" class="preview-img mb-3 border">
                        <div class="mb-3">
                            <label for="foto_perfil" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-camera"></i> Subir Foto
                            </label>
                            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" class="d-none">
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo*</label>
                            <input type="text" class="form-control" name="nombre" required value="<?= htmlspecialchars($nombre) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email*</label>
                            <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($email) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contraseña* (mínimo 8 caracteres)</label>
                            <input type="password" class="form-control" name="password" required minlength="8">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña*</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="8">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2">Registrarse</button>
                <div class="d-flex justify-content-between mt-3">
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-home"></i> Regresar al Inicio
    </a>
    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
</div>
            </form>
            
            <div class="mt-3 text-center">
                <a href="login.php" class="text-decoration-none">¿Ya tienes cuenta? Inicia sesión</a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <!-- Preview de imagen -->
    <script>
        document.getElementById('foto_perfil').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>