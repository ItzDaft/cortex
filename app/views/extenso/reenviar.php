<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <h2 class="mb-3">Subir Nueva Versión del Artículo</h2>
        <p class="lead mb-4">Revisa los comentarios de los evaluadores y sube tu artículo corregido.</p>
        
        <div id="mensaje-envio"></div>

        <div class="alert alert-danger text-center" style="font-size:1.1rem; font-weight:bold;">
            <span style="font-size:1.3rem; color:#b30000; display:block; margin-bottom:10px;">¡RECORDATORIO! Para evitar otro rechazo:</span>
            <ul class="text-start" style="color:#b30000; font-size:1rem;">
                <li><b>Formato PDF:</b> Solo se aceptan archivos PDF.</li>
                <li><b>Anonimato:</b> Asegúrate de que esta nueva versión <u>siga sin contener</u> nombres de autores ni afiliaciones.</li>
                <li><b>Plantilla:</b> Verifica que estás usando la plantilla oficial a una columna.</li>
            </ul>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-warning-subtle">
                <h5 class="mb-0">Observaciones a Atender</h5>
            </div>
            <div class="card-body">
                <?php 
                    $comentariosFormato = !empty($extenso['comentarios_formato']);
                    $comentariosRevisores = !empty($extenso['evaluaciones']); // Ajustado para usar el array dentro de $extenso si viene de ahí
                    
                    if (!$comentariosFormato && !$comentariosRevisores): 
                ?>
                    <p>No hay comentarios específicos registrados. Por favor, revisa la guía de formato general.</p>
                <?php else: ?>
                    
                    <?php if ($comentariosFormato): ?>
                        <p><strong>Del Coordinador de Área (Formato):</strong></p>
                        <blockquote class="border-start border-4 border-danger ps-3 mb-3">
                            <?php echo nl2br(htmlspecialchars($extenso['comentarios_formato'])); ?>
                        </blockquote>
                    <?php endif; ?>

                    <?php if ($comentariosRevisores): ?>
                        <p><strong>De los Revisores de Extenso:</strong></p>
                        <ul class="list-unstyled">
                        <?php foreach ($extenso['evaluaciones'] as $index => $eval):
                            $comentarioCompleto = trim(($eval['observaciones_generales'] ?? '') . "\n" . ($eval['argumento_rechazo'] ?? ''));
                            if (!empty($comentarioCompleto)):
                        ?>
                            <li class="mb-2">
                                <strong>Revisor <?php echo $index + 1; ?>:</strong>
                                <blockquote class="border-start border-4 ps-3 mt-1 bg-light p-2">
                                    <?php echo nl2br(htmlspecialchars($comentarioCompleto)); ?>
                                </blockquote>
                            </li>
                        <?php endif; endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="formReenviarExtenso" enctype="multipart/form-data">
                    <?php CSRFHelper::getTokenInput(); ?>
                    
                    <div class="mb-3">
                        <label for="archivo_extenso" class="form-label">Selecciona tu nueva versión (PDF unicamente):</label>
                        <input class="form-control" type="file" id="archivo_extenso" name="archivo_extenso" accept=".pdf" required>
                    </div>
                    
                    <button type="submit" id="submitBtn" class="btn btn-warning">
                        <i class="bi bi-upload me-2"></i>Enviar Nueva Versión
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
                mensajeDiv.scrollIntoView({ behavior: 'smooth' });
            } else {
                alert(data.mensaje);
                window.location.href = `${baseUrl}resumen/misResumenes`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mensajeDiv.innerHTML = `<div class="alert alert-danger">Error de conexión al enviar el archivo.</div>`;
        })
        .finally(() => {
            if (mensajeDiv.innerHTML.includes('alert-danger')) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    });
</script>