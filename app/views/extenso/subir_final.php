<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <h1 class="mb-3"><?php echo htmlspecialchars($subir_final['titulo']); ?></h1>
        <p class="lead text-muted mb-4">Versión final para envío a la revista</p>

        <div class="alert alert-primary">
            <p class="mb-2"><strong>Este documento es la versión definitiva</strong> que se remitirá a la revista. Debe entregarse <strong>en un solo archivo PDF</strong>.</p>
            <p class="mb-2">El PDF debe incluir de forma completa y visible:</p>
            <ul class="mb-2">
                <li><strong>Autor(es):</strong> <?php echo htmlspecialchars((string) ($subir_final['autor_principal'] ?? '')); ?></li>
                <?php if (!empty(trim((string) ($subir_final['coautores'] ?? '')))): ?>
                    <li><strong>Coautor(es):</strong> <?php echo nl2br(htmlspecialchars((string) $subir_final['coautores'])); ?></li>
                <?php endif; ?>
                <?php
                $ads = array_filter([
                    trim((string) ($subir_final['adscripcion1'] ?? '')),
                    trim((string) ($subir_final['adscripcion2'] ?? '')),
                ]);
                ?>
                <?php if (!empty($ads)): ?>
                    <li><strong>Instituciones / adscripciones:</strong> <?php echo htmlspecialchars(implode(' · ', $ads)); ?></li>
                <?php endif; ?>
                <li><strong>Bibliografía o referencias</strong> según el formato requerido por la revista.</li>
                <li><strong>Figuras e imágenes al final</strong> del documento (después del cuerpo y referencias), no intercaladas en el texto si así lo solicita la convocatoria.</li>
            </ul>
            <p class="mb-0">Revise que el archivo esté completo antes de enviarlo; esta es la copia que se considerará oficial para publicación.</p>
        </div>

        <?php if (!empty($subir_final['vf_archivo_ruta'])): ?>
            <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span>
                    Ya envió un PDF final el <?php echo date('d/m/Y H:i', strtotime($subir_final['vf_fecha_envio'])); ?>.
                    Puede sustituirlo subiendo un nuevo archivo.
                </span>
                <a href="<?php echo BASE_URL; ?>archivo/ver/extensos_finales/<?php echo htmlspecialchars($subir_final['vf_archivo_ruta']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-download"></i> Descargar envío actual
                </a>
            </div>
        <?php endif; ?>

        <div id="mensaje-subida-final"></div>

        <div class="card">
            <div class="card-body">
                <form id="formSubirFinal" enctype="multipart/form-data">
                    <?php CSRFHelper::getTokenInput(); ?>
                    <div class="mb-3">
                        <label for="archivo_extenso" class="form-label">Archivo PDF de la versión final</label>
                        <input class="form-control" type="file" id="archivo_extenso" name="archivo_extenso" accept=".pdf,application/pdf" required>
                    </div>
                    <button type="submit" id="submitBtnFinal" class="btn btn-success">
                        <?php echo !empty($subir_final['vf_archivo_ruta']) ? 'Reemplazar versión final' : 'Enviar versión final'; ?>
                    </button>
                    <a href="<?php echo BASE_URL; ?>resumen/misExtensos" class="btn btn-outline-secondary ms-2">Volver a mis extensos</a>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const formSubirFinal = document.getElementById('formSubirFinal');
    const submitBtnFinal = document.getElementById('submitBtnFinal');
    const originalBtnTextFinal = submitBtnFinal.innerHTML;
    const mensajeDivFinal = document.getElementById('mensaje-subida-final');
    const baseUrl = '<?php echo BASE_URL; ?>';
    const extensoIdFinal = <?php echo (int) $extenso_id; ?>;
    const csrfTokenFinal = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

    formSubirFinal.addEventListener('submit', function(event) {
        event.preventDefault();
        submitBtnFinal.disabled = true;
        submitBtnFinal.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Subiendo...';

        const formData = new FormData(this);
        formData.append('csrf_token', csrfTokenFinal);

        fetch(`${baseUrl}extenso/procesarSubidaFinal/${extensoIdFinal}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mensajeDivFinal.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                alert(data.mensaje);
                window.location.href = `${baseUrl}resumen/misExtensos`;
            }
        })
        .catch(() => {
            mensajeDivFinal.innerHTML = '<div class="alert alert-danger">Error de red. Intente de nuevo.</div>';
        })
        .finally(() => {
            if (!document.querySelector('#mensaje-subida-final .alert-success')) {
                submitBtnFinal.disabled = false;
                submitBtnFinal.innerHTML = originalBtnTextFinal;
            }
        });
    });
</script>
