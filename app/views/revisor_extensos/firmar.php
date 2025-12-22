<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <h2>Gestión de Firma de Dictamen</h2>
            <a href="<?php echo BASE_URL; ?>revisorExtensos/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <div class="alert alert-success">
            <h4 class="alert-heading"><i class="bi bi-check-circle-fill"></i> ¡Evaluación Favorable!</h4>
            <p>Has determinado que este artículo es <strong>Favorable y Publicable</strong>. Para formalizar esta decisión, es necesario que descargues el dictamen oficial, lo firmes y lo subas nuevamente a la plataforma.</p>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <strong>Paso 1: Descargar Documento</strong>
            </div>
            <div class="card-body text-center py-4">
                <p class="card-text text-muted mb-3">Descarga el PDF generado automáticamente con tus respuestas.</p>
                <a href="<?php echo BASE_URL; ?>reporte/generarEvaluacionPDF/<?php echo $evaluacion['id']; ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-file-earmark-arrow-down"></i> Descargar Dictamen Oficial
                </a>
            </div>
        </div>

        <div class="card border-warning">
            <div class="card-header bg-warning-subtle">
                <strong>Paso 2: Subir Documento Firmado</strong>
            </div>
            <div class="card-body">
                <p class="small text-muted">Una vez firmado (digital o autógrafa escaneada), sube el archivo PDF aquí.</p>
                
                <form id="formSubirFirma" enctype="multipart/form-data">
                    <input type="hidden" id="evaluacion_id" value="<?php echo $evaluacion['id']; ?>">
                    <?php CSRFHelper::getTokenInput(); ?>
                    
                    <div class="mb-3">
                        <label for="pdf_firmado" class="form-label">Archivo PDF Firmado:</label>
                        <input class="form-control" type="file" id="pdf_firmado" name="pdf_firmado" accept=".pdf" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" id="btnSubir" class="btn btn-success btn-lg">
                            <i class="bi bi-upload"></i> Subir y Finalizar Proceso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formSubirFirma').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubir');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Subiendo...';

    const formData = new FormData(this);
    const evalId = document.getElementById('evaluacion_id').value;
    // Token manual por seguridad extra
    formData.append('csrf_token', '<?php echo $_SESSION["csrf_token"] ?? ""; ?>');

    fetch('<?php echo BASE_URL; ?>revisorExtensos/subirPdfFirmado/' + evalId, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.error) {
            alert('Error: ' + data.error);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> Subir y Finalizar Proceso';
        } else {
            alert(data.mensaje);
            window.location.href = '<?php echo BASE_URL; ?>revisorExtensos/dashboard';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error de conexión.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-upload"></i> Subir y Finalizar Proceso';
    });
});
</script>