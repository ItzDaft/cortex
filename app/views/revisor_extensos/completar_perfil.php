<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <h2 class="mb-3">Completar Perfil de Revisor de Extensos</h2>
        <p class="lead mb-4">Gracias por aceptar la invitación. Por favor, completa la siguiente información para activar tu cuenta.</p>
        <div id="mensaje-perfil"></div>

        <div class="card">
            <div class="card-body">
                <form id="perfilRevisorForm" enctype="multipart/form-data">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="grado_academico" class="form-label">Grado Académico (Dr., Mtro., etc.)</label>
                            <input type="text" class="form-control" id="grado_academico" name="grado_academico" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="afiliacion_institucional" class="form-label">Afiliación Institucional</label>
                            <input type="text" class="form-control" id="afiliacion_institucional" name="afiliacion_institucional" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="cargo_actual" class="form-label">Cargo o Puesto Actual</label>
                            <input type="text" class="form-control" id="cargo_actual" name="cargo_actual" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="area_id" class="form-label">Área Temática de Especialización</label>
                            <select class="form-select" id="area_id" name="area_id" required>
                                <option value="">-- Selecciona tu área principal de revisión --</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?php echo $area['id']; ?>" <?php echo (isset($usuario['area_id']) && $usuario['area_id'] == $area['id']) ? 'selected' : ''; ?> >
                                        <?php echo htmlspecialchars($area['nombre_area']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="area_especialidad" class="form-label">Área de Especialidad (Líneas de investigación)</label>
                            <textarea class="form-control" id="area_especialidad" name="area_especialidad" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="orcid" class="form-label">ORCID (Opcional)</label>
                            <input type="text" class="form-control" id="orcid" name="orcid">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="google_scholar_id" class="form-label" >Google Scholar / Scopus ID </label>
                            <input type="text" class="form-control" id="google_scholar_id" name="google_scholar_id">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="comprobante_sni" class="form-label">Comprobante SNI </label>
                            <input class="form-control" type="file" id="comprobante_sni" name="comprobante_sni" accept=".pdf" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="foto" class="form-label">Fotografía (JPG/PNG, opcional)</label>
                            <input class="form-control" type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png">
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="acepta_terminos" name="acepta_terminos" required>
                                <label class="form-check-label" for="acepta_terminos">
                                    Confirmo que acepto el código de ética y las políticas de revisión del congreso.
                                </label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Guardar Perfil y Activar Cuenta</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const perfilForm = document.getElementById('perfilRevisorForm');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    const mensajeDiv = document.getElementById('mensaje-perfil');
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';

    perfilForm.addEventListener('submit', function(event) {
        event.preventDefault();
        // Validación cliente: asegurar que se seleccionó Área Temática
        const areaSelect = document.getElementById('area_id');
        if (!areaSelect || !areaSelect.value) {
            mensajeDiv.innerHTML = `<div class="alert alert-danger">Selecciona tu Área Temática de Especialización.</div>`;
            return;
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Guardando...`;
        
        const formData = new FormData(this);
        formData.append('csrf_token', csrfToken);
        
        fetch(`${baseUrl}revisorExtensos/guardarPerfil`, {
            method: 'POST',
            body: formData 
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                mensajeDiv.innerHTML = `<div class="alert alert-success">${data.mensaje}</div>`;
                setTimeout(() => { window.location.href = `${baseUrl}revisorExtensos/dashboard`; }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mensajeDiv.innerHTML = `<div class="alert alert-danger">Ocurrió un error de conexión.</div>`;
        })
        .finally(() => {
            if (!document.querySelector('.alert-success')) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    });
</script>