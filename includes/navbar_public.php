<?php
// navbar_public.php - Barra de navegación para visitantes
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <!-- Logo/Brand -->
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-futbol me-2"></i>
            <span class="fw-bold">Tiki-Taka</span>
        </a>

        <!-- Botón móvil -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Elementos del menú -->
        <div class="collapse navbar-collapse" id="navbarPublic">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php">
                        <i class="fas fa-home me-1"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'sorteos.php') ? 'active' : '' ?>" href="sorteos.php">
                        <i class="fas fa-trophy me-1"></i> Sorteos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'como-jugar.php') ? 'active' : '' ?>" href="como-jugar.php">
                        <i class="fas fa-question-circle me-1"></i> ¿Cómo jugar?
                    </a>
                </li>
            </ul>

            <!-- Botones de acceso -->
            <div class="d-flex">
                <a href="login.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-sign-in-alt me-1"></i> Ingresar
                </a>
                <a href="registro.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-1"></i> Registrarse
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Estilos personalizados -->
<style>
.navbar {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
.navbar-brand {
    font-size: 1.5rem;
}
.nav-link.active {
    font-weight: 600;
    border-bottom: 2px solid #0d6efd;
}
</style>