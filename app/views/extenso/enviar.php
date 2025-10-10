<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <h2 class="mb-3">Envío de Artículo Extenso</h2>
        <p class="lead mb-4">Desde aquí puedes subir la primera versión de tu artículo completo para su evaluación.</p>
        <div id="mensaje-envio"></div>
        <div class="alert alert-info">
            <h5 class="alert-heading">Guía de Formato</h5>
            <p>Antes de subir tu archivo, asegúrate de que cumpla con todos los lineamientos de formato.</p>
            <a href="<?php echo BASE_URL; ?>assets/docs/formato_extenso.pdf" class="btn btn-primary" target="_blank">
                <i class="bi bi-download me-2"></i>Descargar Guía de Formato
            </a>
        </div>
        <div class="card">
            <div class="card-body">
                <form id="formEnviarExtenso" enctype="multipart/form-data">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="mb-3">
                        <label for="archivo_extenso" class="form-label">Selecciona tu artículo (PDF, DOC, DOCX):</label>
                        <input class="form-control" type="file" id="archivo_extenso" name="archivo_extenso" accept=".pdf,.doc,.docx" required>
                    </div>
                    <button type="submit" id="submitBtn" class="btn btn-success">Enviar Artículo para Revisión</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const formEnviarExtenso = document.getElementById('formEnviarExtenso');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    const mensajeDiv = document.getElementById('mensaje-envio');
    const baseUrl = '<?php echo BASE_URL; ?>';
    const extensoId = <?php echo $extenso_id; ?>;
    
    // CAMBIO 1: Se inyecta el token en una variable de JavaScript
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';

    formEnviarExtenso.addEventListener('submit', function(event) {
        event.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Enviando...`;
        
        const formData = new FormData(this);
        
        // CAMBIO 2: Se añade manualmente el token al FormData
        formData.append('csrf_token', csrfToken);

        fetch(`${baseUrl}extenso/procesarEnvio/${extensoId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                alert(data.mensaje);
                window.location.href = `${baseUrl}resumen/misResumenes`;
            }
        })
        .finally(() => {
            if (!document.querySelector('.alert-success')) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    });
</script>