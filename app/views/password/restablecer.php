<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <h2 class="mb-4 text-center">Establecer Nueva Contraseña</h2>
        <div id="mensaje" class="mb-3"></div>
        <div class="card">
            <div class="card-body">
                <form id="resetForm">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="mb-3">
                        <label for="nueva_contrasena" class="form-label">Nueva Contraseña:</label>
                        <input type="password" class="form-control" id="nueva_contrasena" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña:</label>
                        <input type="password" class="form-control" id="confirmar_contrasena" required>
                        
                        <div id="password-match-message" class="form-text mt-1"></div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Guardar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resetForm = document.getElementById('resetForm');
    const mensajeDiv = document.getElementById('mensaje');
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"]; ?>';
    const nuevaContrasenaInput = document.getElementById('nueva_contrasena');
    const confirmarContrasenaInput = document.getElementById('confirmar_contrasena');
    const matchMessageDiv = document.getElementById('password-match-message');

    function validarContrasenas() {
        const pass1 = nuevaContrasenaInput.value;
        const pass2 = confirmarContrasenaInput.value;

        if (pass2.length > 0) {
            if (pass1 === pass2) {
                matchMessageDiv.textContent = '✅ Las contraseñas coinciden.';
                matchMessageDiv.classList.add('text-success');
                matchMessageDiv.classList.remove('text-danger');
                confirmarContrasenaInput.classList.add('is-valid');
                confirmarContrasenaInput.classList.remove('is-invalid');
            } else {
                matchMessageDiv.textContent = '❌ Las contraseñas no coinciden.';
                matchMessageDiv.classList.add('text-danger');
                matchMessageDiv.classList.remove('text-success');
                confirmarContrasenaInput.classList.add('is-invalid');
                confirmarContrasenaInput.classList.remove('is-valid');
            }
        } else {
            matchMessageDiv.textContent = '';
            confirmarContrasenaInput.classList.remove('is-valid', 'is-invalid');
        }
    }
    
    nuevaContrasenaInput.addEventListener('input', validarContrasenas);
    confirmarContrasenaInput.addEventListener('input', validarContrasenas);

    resetForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const nueva = nuevaContrasenaInput.value;
        const confirmar = confirmarContrasenaInput.value;

        if (nueva !== confirmar) {
            mensajeDiv.innerHTML = '<div class="alert alert-danger">Las contraseñas no coinciden.</div>';
            return;
        }
        fetch(`${baseUrl}passwordReset/restablecerContrasena`,  {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ token: this.token.value, nueva_contrasena: nueva, csrf_token: csrfToken })
        }).then(res => res.json()).then(data => {
            if(data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                mensajeDiv.innerHTML = `<div class="alert alert-success">${data.mensaje} Serás redirigido para iniciar sesión.</div>`;
                setTimeout(() => { window.location.href = `${baseUrl}usuario/login`; }, 3000);
            }
        });
    });
});
</script>