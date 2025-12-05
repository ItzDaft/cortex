<h2 class="mb-3">Evaluación de Artículo Extenso</h2>
<a href="<?php echo BASE_URL; ?>revisorExtensos/dashboard">&laquo; Volver al panel</a>

<div class="card my-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Artículo: "<?php echo htmlspecialchars($evaluacion['titulo']); ?>"</h5>
        <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $evaluacion['archivo_ruta']; ?>" target="_blank" class="btn btn-secondary">
            <i class="bi bi-download me-2"></i> Descargar Artículo para Evaluar
        </a>
    </div>
</div>

<div id="mensaje-gestion"></div>

<div class="card">
    <div class="card-header"><h5>Paso 1: Formulario de Evaluación</h5></div>
    <div class="card-body">
        <form id="formEvaluacionExtenso">
            <?php CSRFHelper::getTokenInput(); ?>

            <h6>Criterios de Evaluación</h6>
            <hr>
            <div class="mb-3"><p>1. ¿Se plantea con claridad el tema abordado en el artículo?</p>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_1" value="si" required><label class="form-check-label">Sí</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_1" value="no"><label class="form-check-label">No</label></div>
            </div>
            <div class="mb-3"><p>2. ¿Se presenta una fundamentación teórica pertinente de acuerdo con el área de conocimiento en la cual se inscribe el tema?</p>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_2" value="si" required><label class="form-check-label">Sí</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_2" value="no"><label class="form-check-label">No</label></div>
            </div>
            <div class="mb-3"><p>3. ¿Se integra contenido pertinente y relevante para el desarrollo del área de conocimiento?</p>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_3" value="si" required><label class="form-check-label">Sí</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_3" value="no"><label class="form-check-label">No</label></div>
            </div>
            <div class="mb-3"><p>4. ¿Los aspectos teóricos que presenta el texto son suficientes para el análisis que presenta?</p>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_4" value="si" required><label class="form-check-label">Sí</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_4" value="no"><label class="form-check-label">No</label></div>
            </div>
            <div class="mb-3"><p>5. ¿Los hallazgos de la investigación contribuyen a la reflexión y/o explicación del tema tratado?</p>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_5" value="si" required><label class="form-check-label">Sí</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_5" value="no"><label class="form-check-label">No</label></div>
            </div>
            <div class="mb-3"><p>6. ¿Se presentan las referencias bibliográficas apropiadas y se citan correctamente?</p>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_6" value="si" required><label class="form-check-label">Sí</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="pregunta_6" value="no"><label class="form-check-label">No</label></div>
            </div>
            <h6 class="mt-4">Observaciones y Veredicto</h6>
            <hr>
            <div class="mb-3">
                <label for="observaciones_generales" class="form-label">Observaciones generales:</label>
                <textarea class="form-control" id="observaciones_generales" name="observaciones_generales" rows="5"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Valoración global (Veredicto):</label>
                <div class="form-check"><input class="form-check-input" type="radio" name="veredicto" value="Favorable y Publicable" required><label class="form-check-label">Favorable y Publicable sin recomendaciones</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" name="veredicto" value="Favorable con Correcciones"><label class="form-check-label">Favorable y Publicable con correcciones y/o modificaciones</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" name="veredicto" value="No Publicable"><label class="form-check-label">No se recomienda su publicación</label></div>
            </div>

            <div class="mb-3 d-none" id="argumento-rechazo-container">
                <label for="argumento_rechazo" class="form-label">Argumente los motivos por los cuales no recomienda su publicación:</label>
                <textarea class="form-control" id="argumento_rechazo" name="argumento_rechazo" rows="4"></textarea>
            </div>

            <button type="submit" class="btn btn-success" id="submitBtn">Guardar Evaluación y Generar PDF</button>
            <button type="button" id="guardarBorradorBtn" class="btn btn-secondary">Guardar Borrador</button>

        </form>
    </div>
</div>

<div id="seccion-subir-firmado" class="card mt-4 d-none">
    <div class="card-header">
        <h5>Paso 2: Subir Dictamen Firmado</h5>
    </div>
    <div class="card-body">
        <p>Por favor, sube el archivo PDF después de haberlo firmado.</p>
        <form id="formPdfFirmado" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="pdf_firmado" class="form-label">Selecciona tu dictamen firmado (PDF):</label>
                <input class="form-control" type="file" id="pdf_firmado" name="pdf_firmado" accept=".pdf" required>
            </div>
            <button type="submit" id="btnSubirFirmado" class="btn btn-info">
                <i class="bi bi-check-circle-fill me-2"></i>Finalizar y Enviar a Coordinador
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
    const evaluacionId = <?php echo $evaluacion['id']; ?>;
    const mensajeDiv = document.getElementById('mensaje');

    const formEvaluacion = document.getElementById('formEvaluacionExtenso');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
    const guardarBorradorBtn = document.getElementById('guardarBorradorBtn');
    const originalBorradorBtnText = guardarBorradorBtn ? guardarBorradorBtn.innerHTML : '';

    const seccionSubirFirmado = document.getElementById('seccion-subir-firmado');
    const formPdfFirmado = document.getElementById('formPdfFirmado');
    const btnSubirFirmado = document.getElementById('btnSubirFirmado');
    const originalBtnFirmadoText = btnSubirFirmado ? btnSubirFirmado.innerHTML : '';

    document.querySelectorAll('input[name="veredicto"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const container = document.getElementById('argumento-rechazo-container');
            const textarea = document.getElementById('argumento_rechazo');
            if (this.value === 'No Publicable') {
                container.classList.remove('d-none');
                textarea.required = true;
            } else {
                container.classList.add('d-none');
                textarea.required = false;
            }
        });
    });

    if (guardarBorradorBtn) {
        guardarBorradorBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Guardando...`;

            const formData = new FormData(formEvaluacion);
            formData.append('csrf_token', csrfToken);

            fetch(`${baseUrl}revisorExtensos/guardarBorradorEvaluacion/${evaluacionId}`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                const alertClass = data.error ? 'alert-danger' : 'alert-success';
                mensajeDiv.innerHTML = `<div class="alert ${alertClass}">${data.mensaje || data.error}</div>`;
                setTimeout(() => { mensajeDiv.innerHTML = ''; }, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                mensajeDiv.innerHTML = `<div class="alert alert-danger">Error de conexión.</div>`;
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = originalBorradorBtnText;
            });
        });
    }

    if (formEvaluacion && submitBtn) {
        formEvaluacion.addEventListener('submit', function(event) {
            event.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Procesando...`;

            const formData = new FormData(this);
            formData.append('csrf_token', csrfToken);

            fetch(`${baseUrl}revisorExtensos/procesarEvaluacion/${evaluacionId}`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                } else {
                    if (data.requiere_firma) {
                        mensajeDiv.innerHTML = `
                            <div class="alert alert-success">
                                <p class="mb-2">${data.mensaje}</p>
                                <a href="${data.pdf_url}" class="btn btn-primary" target="_blank">
                                    <i class="bi bi-download"></i> Descargar PDF para Firmar
                                </a>
                            </div>`;
                        formEvaluacion.querySelectorAll('input, textarea, button').forEach(el => el.disabled = true);
                        seccionSubirFirmado.classList.remove('d-none');
                    } else {
                        alert(data.mensaje);
                        window.location.href = `${baseUrl}revisorExtensos/dashboard`;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mensajeDiv.innerHTML = `<div class="alert alert-danger">Ocurrió un error de conexión.</div>`;
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }
    if (formPdfFirmado) {
        formPdfFirmado.addEventListener('submit', function(event) {
            event.preventDefault();
            btnSubirFirmado.disabled = true;
            btnSubirFirmado.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Subiendo...`;

            const formData = new FormData(this);
            formData.append('csrf_token', csrfToken);

            fetch(`${baseUrl}revisorExtensos/subirPdfFirmado/${evaluacionId}`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    btnSubirFirmado.disabled = false;
                    btnSubirFirmado.innerHTML = originalBtnFirmadoText;
                } else {
                    alert(data.mensaje);
                    window.location.href = `${baseUrl}revisorExtensos/dashboard`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al subir el archivo.');
                btnSubirFirmado.disabled = false;
                btnSubirFirmado.innerHTML = originalBtnFirmadoText;
            });
        });
    }
});
</script>