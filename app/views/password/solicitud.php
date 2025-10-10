<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <h2 class="mb-4 text-center">Recuperar Contraseña</h2>
        <p class="text-center text-muted">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
        <div id="mensaje" class="mb-3"></div>
        <div class="card">
            <div class="card-body">
                <form id="solicitudForm">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="correo" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" id="submitButton" class="btn btn-primary">Enviar Enlace</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const solicitudForm = document.getElementById('solicitudForm');
    const mensajeDiv = document.getElementById('mensaje');

    const submitButton = document.getElementById('submitButton');
    const originalButtonText = submitButton.innerHTML;
    const csrfToken = '<?php echo $_SESSION["csrf_token"]; ?>';    
    const baseUrl = '<?php echo BASE_URL; ?>';
    solicitudForm.addEventListener('submit', function(event) {
        event.preventDefault();

        submitButton.disabled = true;
        submitButton.innerHTML = `
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            Enviando...
        `;
        
        fetch(`${baseUrl}passwordReset/enviarEnlace`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ correo: this.correo.value, csrf_token: csrfToken })
        })
        .then(res => res.json())
        .then(data => {
            mensajeDiv.innerHTML = `<div class="alert alert-success">${data.mensaje}</div>`;
            this.reset();
        })
        .catch(error => {
            console.error('Error:', error);
            mensajeDiv.innerHTML = `<div class="alert alert-danger">Ocurrió un error de conexión.</div>`;
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });
});
</script>