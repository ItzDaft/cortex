<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <h2 class="mb-4 text-center">Registro de Nuevo Usuario</h2>
        
        <div id="mensaje" class="mb-3"></div>

        <div class="card">
            <div class="card-body">
                <form id="registroForm">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>

                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>

                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña:</label>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña:</label>
                        <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                        <div id="mensaje_contrasena" class="form-text text-danger"></div>
                    </div>

                    <div class="mb-3">
                        <label for="institucion" class="form-label">Institución de Procedencia:</label>
                        <input type="text" class="form-control" id="institucion" name="institucion">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deseo registrarme como:</label>
                       <!-- <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_registro" id="tipoAutor" value="autor" checked>
                            <label class="form-check-label" for="tipoAutor">
                                **Autor** (Para resumen para el extenso - $1000.00 MXN)
                            </label> 
                        </div> -->
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_registro" id="tipoAsistente" value="asistente_estudiante">
                            <label class="form-check-label" for="tipoAsistente">
                                **Asistente Estudiante** (Solo para asistir al evento siendo estudiante de cualquier institucion educativa - $300.00 MXN)
                            </label>
                        </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_registro" id="tipoAsistenteProfesionista" value="asistente_profesionista">
                                <label class="form-check-label" for="tipoAsistenteProfesionista">**Asistente general** (Solo para asistir al evento - $1000.00 MXN)</label>
                            </div>
                      <!--  <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_registro" id="tipoAsistenteCartel" value="asistente_cartel">
                            <label class="form-check-label" for="tipoAsistenteCartel">
                                **Asistente con Cartel** (Asistir, enviar tu resumen y presentar un póster - $500.00 MXN)
                            </label>
                        </div> -->
                    </div>

                    <div class="d-grid">
                                        <button type="submit" id="submitBtn" class="btn btn-primary">Registrarme</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const registroForm = document.getElementById('registroForm');
    const mensajeDiv = document.getElementById('mensaje');
    const csrfToken = '<?php echo $_SESSION["csrf_token"]; ?>';
    const baseUrl = '<?php echo BASE_URL; ?>';
    const submitBtn = document.getElementById('submitBtn');
    const originalButtonText = submitBtn.innerHTML;
    const contrasena = document.getElementById('contrasena');
const confirmarContrasena = document.getElementById('confirmar_contrasena');
const mensajeContrasena = document.getElementById('mensaje_contrasena');

function validarContrasenas() {
    if (confirmarContrasena.value === "") {
        mensajeContrasena.textContent = "";
        return false;
    }
    if (contrasena.value !== confirmarContrasena.value) {
        mensajeContrasena.textContent = "Las contraseñas no coinciden";
        return false;
    } else {
        mensajeContrasena.textContent = "";
        return true;
    }
}
contrasena.addEventListener('input', validarContrasenas);
confirmarContrasena.addEventListener('input', validarContrasenas);
    
registroForm.addEventListener('submit', function(event) {
        if(!validarContrasenas()) {
            event.preventDefault();
            confirmarContrasena.focus()
            return;
        }
        event.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Registrando...`;
        const formData = new FormData(this);
        const tipoRegistro = document.querySelector('input[name="tipo_registro"]:checked').value;

        const datos = {
            nombre: formData.get('nombre'),
            correo: formData.get('correo'),
            contrasena: formData.get('contrasena'),
            institucion: formData.get('institucion'),
            tipo_registro: tipoRegistro, 
            csrf_token: csrfToken

        };

        fetch(`${baseUrl}usuario/registrar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.error);
                });
            }
            return response.json();
        })
        .then(data => {
            window.location.href = `${baseUrl}usuario/login`;
        })
        .catch(error => {
            mensajeDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;        });
    });
</script>