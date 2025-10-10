<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <h2 class="mb-4 text-center">Iniciar Sesión</h2>
        
        <div id="mensaje" class="mb-3"></div>

        <div class="card">
            <div class="card-body">
                <form id="loginForm">
                    <?php CSRFHelper::getTokenInput(); ?>

                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>

                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña:</label>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="submitButton" class="btn btn-primary">Ingresar</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <p>¿No tienes una cuenta? <a href="<?php echo BASE_URL; ?>usuario/registrar">Regístrate aquí</a></p>
            <a href="<?php echo BASE_URL; ?>passwordReset/vistaSolicitud">¿Olvidaste tu contraseña?</a>
        </div>
    </div>
</div>

<script>
    const loginForm = document.getElementById('loginForm');
    const mensajeDiv = document.getElementById('mensaje');
    const submitButton = document.getElementById('submitButton');
    const originalButtonText = submitButton.innerHTML;
    
    // La solución final: Se inyecta el token en una variable de JavaScript
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';

    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        submitButton.disabled = true;
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Ingresando...`;

        const datos = {
            correo: this.correo.value,
            contrasena: this.contrasena.value,
            // Se usa la variable, ya no se busca en el DOM
            csrf_token: csrfToken
        };

        fetch(`<?php echo BASE_URL; ?>usuario/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 403) { location.reload(); }
                return response.json().then(errorData => { throw new Error(errorData.error) });
            }
            return response.json();
        })
        .then(data => {
            window.location.href = '<?php echo BASE_URL; ?>';
        })
        .catch(error => {
            mensajeDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });
</script>