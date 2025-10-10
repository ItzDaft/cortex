<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <h2 class="mb-3">Subir Nueva Versión del Artículo</h2>
        <p class="lead mb-4">Revisa los comentarios de los evaluadores y sube tu artículo corregido.</p>
        <div id="mensaje-envio"></div>

        <div class="card mb-4">
            <div class="card-header bg-warning-subtle">
                <h5 class="mb-0">Comentarios de la Versión Anterior</h5>
            </div>
            <div class="card-body">
                <?php if (empty($evaluaciones)): ?>
                    <p>No hay comentarios para mostrar.</p>
                <?php else: ?>
                    <ul class="list-unstyled">
                        <?php foreach ($evaluaciones as $index => $eval): ?>
                            <li class="mb-2">
                                <strong>Revisor <?php echo $index + 1; ?>:</strong>
                                <blockquote class="border-start border-4 ps-3 mt-1">
                                    <?php echo nl2br(htmlspecialchars(trim($eval['observaciones_generales'] . "\n" . $eval['argumento_rechazo']))); ?>
                                </blockquote>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="formReenviarExtenso" enctype="multipart/form-data">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="mb-3">
                        <label for="archivo_extenso" class="form-label">Selecciona tu nueva versión (PDF, DOC, DOCX):</label>
                        <input class="form-control" type="file" id="archivo_extenso" name="archivo_extenso" accept=".pdf,.doc,.docx" required>
                    </div>
                    <button type="submit" id="submitBtn" class="btn btn-warning">
                        <i class="bi bi-upload me-2"></i>Enviar Segunda Versión
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const formReenviarExtenso = document.getElementById('formReenviarExtenso');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    const mensajeDiv = document.getElementById('mensaje-envio');
    const baseUrl = '<?php echo BASE_URL; ?>';
    const extensoId = <?php echo $extenso['id']; ?>;
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';

    formReenviarExtenso.addEventListener('submit', function(event) {
        event.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Enviando...`;

        const formData = new FormData(this);
        formData.append('csrf_token', csrfToken);

        fetch(`${baseUrl}extenso/procesarReenvio/${extensoId}`, {
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