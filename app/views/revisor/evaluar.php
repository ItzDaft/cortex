<h2 class="mb-3">Evaluar Resumen</h2>
<a href="<?php echo BASE_URL;?>revisor/dashboard">&laquo; Volver al panel</a>

<div class="card my-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Título: <?php echo htmlspecialchars($resumen['titulo']); ?></span>
        <?php if (!empty($resumen['archivo_ruta'])): ?>
            <a href="<?php echo BASE_URL;?>uploads/resumenes/<?php echo $resumen['archivo_ruta']; ?>" target="_blank" class="btn btn-sm btn-secondary">
                <i class="bi bi-download"></i> Descargar Archivo
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <p class="card-text" style="white-space: pre-wrap;"><?php echo htmlspecialchars($resumen['resumen_texto']); ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Formulario de Evaluación
    </div>
    <div class="card-body">
        <div id="mensaje" class="mb-3"></div>
        <form id="evaluacionForm">
            <?php CSRFHelper::getTokenInput(); ?>
            <div class="mb-3">
                <label class="form-label">Veredicto:</label>
                <div class="form-check"><input class="form-check-input" type="radio" name="veredicto" id="veredictoAceptado" value="Aceptado" required><label class="form-check-label" for="veredictoAceptado">Aceptado</label></div>
                <div class="form-check"><input class="form-check-input" type="radio" name="veredicto" id="veredictoRechazado" value="Rechazado" required><label class="form-check-label" for="veredictoRechazado">Rechazado</label></div>
            </div>
            <div class="mb-3">
                <label for="comentarios" class="form-label">Comentarios para el autor:</label>
                <textarea class="form-control" name="comentarios" id="comentarios" rows="5"><?php echo htmlspecialchars($revision['comentarios'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" id="enviarEvaluacionBtn" class="btn btn-success">Enviar Evaluación Final</button>
            <button type="button" id="guardarBorradorBtn" class="btn btn-secondary">Guardar Borrador</button>
            <button type="button" id="devolverBtn" class="btn btn-outline-danger float-end">Devolver a Coordinador</button>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const evaluacionForm = document.getElementById('evaluacionForm');
    const mensajeDiv = document.getElementById('mensaje');
    const resumenId = <?php echo $resumen['id']; ?>;
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"]; ?>';
    const enviarBtn = document.getElementById('enviarEvaluacionBtn');
    const guardarBorradorBtn = document.getElementById('guardarBorradorBtn');
    const originalEnviarBtnText = enviarBtn.innerHTML;
    const originalBorradorBtnText = guardarBorradorBtn.innerHTML;
    const devolverBtn = document.getElementById('devolverBtn');
    devolverBtn.addEventListener('click', function() {
        if (!confirm('¿Estás seguro de que deseas devolver este resumen al coordinador? Esta acción no se puede deshacer.')) {
            return;
        }

        const originalBtnText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Devolviendo...`;

        const datos = {
            resumen_id: resumenId,
            csrf_token: csrfToken
        };

        fetch(`${baseUrl}revisor/devolverResumen`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                this.disabled = false;
                this.innerHTML = originalBtnText;
            } else {
                alert(data.mensaje); // Mostramos una alerta de éxito
                window.location.href = `${baseUrl}revisor/dashboard`; // Redirigimos
            }
        });
    });
    evaluacionForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const veredictoSeleccionado = document.querySelector('input[name="veredicto"]:checked');
        if (!veredictoSeleccionado) {
            mensajeDiv.innerHTML = `<div class="alert alert-danger">Por favor, seleccione un veredicto.</div>`;
            return; 
        }

        enviarBtn.disabled = true;
        guardarBorradorBtn.disabled = true; // Deshabilitamos ambos para evitar acciones conflictivas
        enviarBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Enviando...`;

        const datos = {
            resumen_id: resumenId,
            veredicto: veredictoSeleccionado.value, 
            comentarios: this.comentarios.value,
            csrf_token: csrfToken
        };

        fetch(`${baseUrl}revisor/enviarEvaluacion`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                window.location.href = `${baseUrl}revisor/dashboard`;
            }
        })
        .catch(error => {
            mensajeDiv.innerHTML = `<div class="alert alert-danger">Ocurrió un error de conexión.</div>`;
        })
        .finally(() => {
            // CAMBIO 4: Se restaura el estado de los botones
            enviarBtn.disabled = false;
            guardarBorradorBtn.disabled = false;
            enviarBtn.innerHTML = originalEnviarBtnText;
        });
    });

    guardarBorradorBtn.addEventListener('click', function() {
        guardarBorradorBtn.disabled = true;
        enviarBtn.disabled = true;
        guardarBorradorBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Guardando...`;

        const datos = {
            resumen_id: resumenId,
            comentarios: document.getElementById('comentarios').value,
            csrf_token: csrfToken
        };

        fetch(`${baseUrl}revisor/guardarBorrador`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                mensajeDiv.innerHTML = `<div class="alert alert-success">${data.mensaje}</div>`;
                setTimeout(() => { mensajeDiv.innerHTML = ''; }, 3000);
            }
        })
        .catch(error => {
             mensajeDiv.innerHTML = `<div class="alert alert-danger">Ocurrió un error de conexión.</div>`;
        })
        .finally(() => {
            guardarBorradorBtn.disabled = false;
            enviarBtn.disabled = false;
            guardarBorradorBtn.innerHTML = originalBorradorBtnText;
        });
    });
});
</script>