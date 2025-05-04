<?php
/**
 * sidebar_admin.php - Menú lateral para administradores
 */
?>
<div class="position-sticky pt-3">
    <div class="text-center mb-4">
        <img src="<?= getAdminProfileImage($_SESSION['foto_perfil'] ?? '') ?>" 
             class="rounded-circle admin-profile mb-2"
             alt="Admin"
             onerror="this.src='../assets/img/usuarios/admin_default.png'">
        <h6><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Administrador') ?></h6>
        <span class="badge bg-primary">Administrador</span>
    </div>
    
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="configurar_sorteos.php">
                <i class="fas fa-gift me-2"></i> Sorteos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="gestion_usuarios.php">
                <i class="fas fa-users me-2"></i> Usuarios
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="configuracion.php">
                <i class="fas fa-cog me-2"></i> Configuración
            </a>
        </li>
        <li class="nav-item mt-3">
            <a class="nav-link text-danger" href="../logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
            </a>
        </li>
    </ul>
</div>

<?php
// Función auxiliar para imágenes de perfil
function getAdminProfileImage($filename) {
    $base_path = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/tiki-taka/assets/img/usuarios/';
    $default_image = 'default.jpg';
    
    if (!empty($filename) && file_exists($base_path . $filename)) {
        return '../assets/img/usuarios/' . $filename;
    }
    return '../assets/img/usuarios/' . $default_image;
}
?>