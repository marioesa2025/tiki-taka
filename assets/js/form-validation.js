// Validación de formularios al enviar
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form[id$="-form"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        // Validar confirmación de contraseña
        if (form.id === 'register-form') {
            const password = document.getElementById('password');
            const confirm_password = document.getElementById('confirm_password');

            confirm_password.addEventListener('input', () => {
                if (confirm_password.value !== password.value) {
                    confirm_password.setCustomValidity("Las contraseñas no coinciden");
                } else {
                    confirm_password.setCustomValidity("");
                }
            });
        }
    });
});