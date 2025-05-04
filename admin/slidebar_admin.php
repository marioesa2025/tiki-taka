<!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="<?= getAdminProfileImage($admin['foto_perfil'] ?? '') ?>" 
                             class="rounded-circle admin-profile mb-2"
                             alt="Admin"
                             onerror="this.src='../assets/img/usuarios/admin_default.png'">
                        <h6><?= htmlspecialchars($admin['nombre']) ?></h6>
                        <span class="badge rounded-pill text-white" style="background-color: var(--admin-color);">
                            Administrador
                        </span>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="configurar_sorteos.php" data-bs-toggle="tab">
                                <i class="fas fa-calendar-alt me-2"></i> Configurar Sorteos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#usuarios" data-bs-toggle="tab">
                                <i class="fas fa-users me-2"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#logs" data-bs-toggle="tab">
                                <i class="fas fa-clipboard-list me-2"></i> Actividades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#config" data-bs-toggle="tab">
                                <i class="fas fa-cog me-2"></i> Configuración
                            </a>
                        </li>
                    </ul>
                </div>
            </div>