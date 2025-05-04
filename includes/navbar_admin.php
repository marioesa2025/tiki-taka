<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">
            <i class="fas fa-crown me-2"></i>Admin Panel
        </a>
        
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="panel.php">
                        <i class="fas fa-user-check"></i> Aprobar Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="sorteos.php">
                        <i class="fas fa-trophy"></i> Gestionar Sorteos
                    </a>
                </li>
            </ul>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    <i class="fas fa-user-shield"></i> <?= $_SESSION['user_nombre'] ?>
                </span>
                <!-- Agregar este elemento al menú de navegación -->
                <li class="nav-item">
                    <a class="nav-link logout-btn" href="logout.php" 
                       onclick="return confirm('¿Está seguro que desea cerrar sesión?')">
                       <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                    </a>
                </li>
            </div>
        </div>
    </div> 
</nav>