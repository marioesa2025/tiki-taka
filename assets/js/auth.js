/**
 * auth.js - Manejo de autenticación
 * Contiene funciones para login, logout y manejo de sesión
 */

// Función para manejar el cierre de sesión
function setupLogout() {
    document.querySelectorAll('.logout-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            if (confirm('¿Está seguro que desea cerrar sesión?')) {
                try {
                    const response = await fetch('/dashboard/tiki-taka/logout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `csrf_token=${encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content)}`
                    });
                    
                    if (response.redirected) {
                        window.location.href = response.url;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.location.href = '/dashboard/tiki-taka/logout.php';
                }
            }
        });
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    setupLogout();
});