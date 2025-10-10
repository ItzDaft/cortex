<h1 class="mb-4">Mi Perfil</h1>
<div id="mensaje-general" class="mb-3"></div>

<div class="row">
    <div class="col-md-6 mb-4 mb-md-0">
        <div class="card h-100">
            <div class="card-header"><h4>Mis Datos</h4></div>
            <div class="card-body">
                <form id="perfilForm">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="mb-3"><label for="correo" class="form-label">Correo Electrónico (no editable)</label><input type="email" class="form-control" id="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled readonly></div>
                    <div class="mb-3"><label for="nombre_completo" class="form-label">Nombre Completo</label><input type="text" class="form-control" id="nombre_completo" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>"></div>
                    <div class="mb-3"><label for="institucion_procedencia" class="form-label">Institución</label><input type="text" class="form-control" id="institucion_procedencia" value="<?php echo htmlspecialchars($usuario['institucion_procedencia'] ?? ''); ?>"></div>
                    <button type="submit" id="guardarPerfilBtn" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><h4>Cambiar Contraseña</h4></div>
            <div class="card-body">
                <form id="contrasenaForm">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="mb-3"><label for="contrasena_actual" class="form-label">Contraseña Actual</label><input type="password" class="form-control" id="contrasena_actual" required></div>
                    <div class="mb-3"><label for="nueva_contrasena" class="form-label">Nueva Contraseña</label><input type="password" class="form-control" id="nueva_contrasena" required></div>
                    <div class="mb-3">
                        <label for="confirmar_contrasena" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirmar_contrasena" required>
                        <div id="password-match-message" class="form-text mt-1"></div>
                    </div>
                    <button type="submit" id="cambiarPassBtn" class="btn btn-danger">Cambiar Contraseña</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const perfilForm = document.getElementById('perfilForm');
    const baseUrl = '<?php echo BASE_URL; ?>';
    const contrasenaForm = document.getElementById('contrasenaForm');
    const mensajeDiv = document.getElementById('mensaje-general');
    const csrfToken = '<?php echo $_SESSION["csrf_token"]; ?>';
    const guardarPerfilBtn = document.getElementById('guardarPerfilBtn');
    const cambiarPassBtn = document.getElementById('cambiarPassBtn');
    const originalPerfilBtnText = guardarPerfilBtn.innerHTML;
    const originalPassBtnText = cambiarPassBtn.innerHTML;
    perfilForm.addEventListener('submit', function(event) {
        event.preventDefault();
        guardarPerfilBtn.disabled = true;
        guardarPerfilBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Guardando...`;

        const datos = {
            nombre_completo: document.getElementById('nombre_completo').value,
            institucion_procedencia: document.getElementById('institucion_procedencia').value,
            correo: document.getElementById('correo').value,
            area_id: <?php echo json_encode($usuario['area_id']); ?>,
            csrf_token: csrfToken
        };

        fetch(`${baseUrl}usuario/actualizarPerfil`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        }).then(res => res.json()).then(data => {
            mensajeDiv.innerHTML = `<div class="alert ${data.error ? 'alert-danger' : 'alert-success'}">${data.mensaje || data.error}</div>`;
            if (!data.error) { setTimeout(() => location.reload(), 1500); }
        }).finally(() => {
            guardarPerfilBtn.disabled = false;
            guardarPerfilBtn.innerHTML = originalPerfilBtnText;
        });
    });
    const nuevaContrasenaInput = document.getElementById('nueva_contrasena');
    const confirmarContrasenaInput = document.getElementById('confirmar_contrasena');
    const matchMessageDiv = document.getElementById('password-match-message');
    function validarContrasenas() { /* ... Lógica de validación sin cambios ... */ }
    nuevaContrasenaInput.addEventListener('input', validarContrasenas);
    confirmarContrasenaInput.addEventListener('input', validarContrasenas);
    contrasenaForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const nueva = nuevaContrasenaInput.value;
        const confirmar = confirmarContrasenaInput.value;

        if (nueva !== confirmar) {
            mensajeDiv.innerHTML = '<div class="alert alert-danger">Las contraseñas no coinciden.</div>';
            return;
        }

        cambiarPassBtn.disabled = true;
        cambiarPassBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Cambiando...`;

        const datos = {
            contrasena_actual: document.getElementById('contrasena_actual').value,
            nueva_contrasena: nueva,
            csrf_token: csrfToken
        };

        fetch(`${baseUrl}usuario/cambiarContrasena`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        }).then(res => res.json()).then(data => {
            mensajeDiv.innerHTML = `<div class="alert ${data.error ? 'alert-danger' : 'alert-success'}">${data.mensaje || data.error}</div>`;
            if (!data.error) { this.reset(); matchMessageDiv.textContent = ''; confirmarContrasenaInput.classList.remove('is-valid'); }
        }).finally(() => {
            cambiarPassBtn.disabled = false;
            cambiarPassBtn.innerHTML = originalPassBtnText;
        });
    });
});
</script>