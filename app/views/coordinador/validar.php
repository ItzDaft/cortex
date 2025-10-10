<h2 class="mb-3">Detalles del Resumen</h2>
<a href="<?php echo BASE_URL; ?>coordinador/dashboard">&laquo; Volver al Panel</a>

<div class="card mb-4">
    <div class="card-header">
        <h5>Información del Resumen</h5>
    </div>
    <div class="card-body">
        <h4 class="card-title"><?php echo htmlspecialchars($view_resumen['titulo']); ?></h4>
        
        <div class="mb-3">
            <p class="mb-1"><strong>Autor Principal:</strong> <?php echo htmlspecialchars($view_resumen['autor_principal']); ?></p>
            <?php if (!empty($view_resumen['coautores'])): ?>
                <p class="mb-1"><strong>Coautores:</strong> <?php echo htmlspecialchars($view_resumen['coautores']); ?></p>
            <?php endif; ?>
            <p class="mb-1"><strong>Adscripción 1:</strong> <?php echo htmlspecialchars($view_resumen['adscripcion1']); ?></p>
            <?php if (!empty($view_resumen['adscripcion2'])): ?>
                <p class="mb-0"><strong>Adscripción 2:</strong> <?php echo htmlspecialchars($view_resumen['adscripcion2']); ?></p>
            <?php endif; ?>
        </div>
        
        <hr>
        <p class="card-text" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($view_resumen['resumen_texto'])); ?></p>
        <hr>

        <div class="mt-3">
            <p class="mb-2"><strong>Palabras Clave:</strong></p>
            <?php 
                $keywords = !empty($view_resumen['palabras_clave']) ? explode(',', $view_resumen['palabras_clave']) : [];
                if(empty($keywords)): echo '<small class="text-muted">No se especificaron palabras clave.</small>'; endif;
                foreach ($keywords as $keyword):
            ?>
                <span class="badge bg-secondary me-1"><?php echo htmlspecialchars(trim($keyword)); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Área Temática</h5>
    </div>
    <div class="card-body">
        <div id="mensaje"></div>
        <form id="validarAreaForm">
            <?php CSRFHelper::getTokenInput(); ?>
            <div class="mb-3">
                <label for="area_id" class="form-label">
                    <?php echo $esEditable ? 'Seleccione el área correcta para este resumen:' : 'Área Temática Asignada:'; ?>
                </label>
                <select class="form-select" id="area_id" name="area_id" <?php if (!$esEditable) echo 'disabled'; ?>>
                    <?php foreach ($view_areas as $area): ?>
                        <option value="<?php echo $area['id']; ?>" <?php echo ($area['id'] == $view_resumen['area_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($area['nombre_area']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if ($esEditable): ?>
                <button type="submit" class="btn btn-success">Guardar Cambios y Validar</button>
            <?php endif; ?>
            
            <a href="<?php echo BASE_URL; ?>coordinador/dashboard" class="btn btn-secondary">
                <?php echo $esEditable ? 'Cancelar' : 'Volver al Panel'; ?>
            </a>
        </form>
    </div>
</div>

<script>
    const validarAreaForm = document.getElementById('validarAreaForm');

    // El script solo se activa si el formulario es editable
    if (validarAreaForm) {
        validarAreaForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const baseUrl = '<?php echo BASE_URL; ?>';
            const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
            
            const resumenId = <?php echo $view_resumen['id']; ?>;
            const areaId = this.area_id.value;

            fetch(`${baseUrl}coordinador/validarArea`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ resumen_id: resumenId, area_id: areaId, csrf_token: csrfToken })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('mensaje').innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                } else {
                    window.location.href = `${baseUrl}coordinador/dashboard`;
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
</script>