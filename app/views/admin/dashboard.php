<h1 class="mb-4">Dashboard de Administración</h1>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Aceptados</h5>
                <p class="card-text fs-4"><?php echo $stats['Aceptado'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-danger h-100">
            <div class="card-body">
                <h5 class="card-title">Rechazados</h5>
                <p class="card-text fs-4"><?php echo $stats['Rechazado'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-dark bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title">En Revisión</h5>
                <p class="card-text fs-4"><?php echo $stats['En Revision'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h5 class="card-title">Pendientes de Validación</h5>
                <p class="card-text fs-4"><?php echo $stats['Pendiente de Asignacion'] ?? 0; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <h2>Acciones Rápidas</h2>
    <a href="<?php echo BASE_URL; ?>administrador/usuarios" class="btn btn-primary btn-lg">Gestionar Usuarios</a>
    <a href="<?php echo BASE_URL; ?>administrador/resumenes" class="btn btn-secondary btn-lg">Gestionar Resúmenes</a>
    <a href="<?php echo BASE_URL; ?>administrador/pagos" class="btn btn-success btn-lg">Ver Pagos</a>
        <a href="<?php echo BASE_URL; ?>administrador/extensos" class="btn btn-warning btn-lg">Gestionar Extensos</a>
    <button type="button" class="btn btn-info btn-lg" data-bs-toggle="modal" data-bs-target="#correoMasivoModal">
        <i class="bi bi-envelope-fill"></i> Enviar Correo Masivo
    </button>
</div>
<div class="modal fade" id="correoMasivoModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Enviar Correo Masivo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="correoMasivoForm" enctype="multipart/form-data">

            <div class="mb-3 p-3 bg-light rounded">
                <label class="form-label fw-bold">Paso 1: Seleccionar Destinatarios</label>
                <div class="d-flex flex-wrap">
                    <div class="form-check me-3"><input class="form-check-input" type="checkbox" name="roles[]" value="Autor" id="rol-autor"><label class="form-check-label" for="rol-autor">Autores</label></div>
                    <div class="form-check me-3"><input class="form-check-input" type="checkbox" name="roles[]" value="Asistente con Cartel" id="rol-cartel"><label class="form-check-label" for="rol-cartel">Asistentes con Cartel</label></div>
                    <div class="form-check me-3"><input class="form-check-input" type="checkbox" name="roles[]" value="Asistente" id="rol-asistente"><label class="form-check-label" for="rol-asistente">Asistentes</label></div>
                    </div>
            </div>

            <div class="mb-3">
                <label for="plantilla-select" class="form-label fw-bold">Paso 2: Cargar Plantilla (Opcional)</label>
                <select class="form-select" id="plantilla-select">
                    <option value="">-- Escribir correo desde cero --</option>
                    <option value="recordatorio_autores">Recordatorio para Autores (Extensos)</option>
                    <option value="recordatorio_carteles">Recordatorio para Asistentes con Cartel</option>
                </select>
            </div>

            <hr>
            <p class="fw-bold">Paso 3: Revisar y Enviar Mensaje</p>
            <div class="mb-3"><label for="asunto" class="form-label">Asunto:</label><input type="text" class="form-control" id="asunto" name="asunto" required></div>
            <div class="mb-3"><label for="cuerpo" class="form-label">Cuerpo del Mensaje:</label><textarea class="form-control" id="cuerpo" name="cuerpo" rows="10" required></textarea></div>
            <div class="mb-3"><label for="adjunto" class="form-label">Adjuntar archivo (opcional):</label><input class="form-control" type="file" id="adjunto" name="adjunto"></div>
        </form>
      </div>
      <div class="modal-footer">
    <div id="mensaje-correo" class="me-auto text-muted"></div>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="submit" form="correoMasivoForm" id="enviarCorreoBtn" class="btn btn-primary">Enviar a Roles Seleccionados</button>
    </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const correoForm = document.getElementById('correoMasivoForm');
    const enviarBtn = document.getElementById('enviarCorreoBtn');
    const originalBtnText = enviarBtn.innerHTML;
    const mensajeCorreoDiv = document.getElementById('mensaje-correo');
    const correoModal = new bootstrap.Modal(document.getElementById('correoMasivoModal'));
    const plantillaSelect = document.getElementById('plantilla-select');
    const asuntoInput = document.getElementById('asunto');
    const cuerpoTextarea = document.getElementById('cuerpo');

    const plantillas = {
        recordatorio_autores: {
            asunto: 'Recordatorio de asistencia, pago y disponibilidad de plantilla de extensos – CCTi 2025',
            cuerpo: `Estimados autores:\n\n¡Los esperamos del 28 al 30 de octubre de 2025 en el CCTi 2025!\n\nLes recordamos subir su comprobante de pago en la plataforma Cortex a más tardar el 10 de octubre de 2025.\n\nPor otro lado, la plantilla para los artículos en extenso ya está disponible en nuestra página web: https://ccti2025.fasbit.edu.mx/assets/formatos.html\n\nPodrán subir sus trabajos a partir del 15 de octubre. El archivo que suban no debe incluir los nombres de los autores (revisión a ciegas) y debe ser en formato PDF.\n\nAgradecemos su participación.\n\nAtentamente,\nComité Organizador`
        },
        recordatorio_carteles: {
            asunto: 'Recordatorio de asistencia y pago para presentación de póster – CCTi 2025',
            cuerpo: `Estimados presentadores de cartel:\n\n¡Los esperamos del 28 al 30 de octubre de 2025 en el CCTi 2025!\n\nDurante el evento, podrán presentar su póster. Las dimensiones y demás detalles para la elaboración del mismo están disponibles en nuestra página web.\n\nPara asegurar su participación, les recordamos subir su comprobante de pago en la plataforma Cortex a más tardar el 10 de octubre de 2025.\n\nAgradecemos su valiosa contribución.\n\nAtentamente,\nComité Organizador`
        }
    };
    plantillaSelect.addEventListener('change', function() {
        const plantillaSeleccionada = this.value;
        if (plantillaSeleccionada && plantillas[plantillaSeleccionada]) {
            asuntoInput.value = plantillas[plantillaSeleccionada].asunto;
            cuerpoTextarea.value = plantillas[plantillaSeleccionada].cuerpo;
        } else {
            asuntoInput.value = '';
            cuerpoTextarea.value = '';
        }
    });
    correoForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const rolesSeleccionados = document.querySelectorAll('input[name="roles[]"]:checked');
        if (rolesSeleccionados.length === 0) {
            alert('Por favor, selecciona al menos un rol de destinatario.');
            return;
        }
        if (!confirm('¿Estás seguro de que deseas enviar este correo a los roles seleccionados?')) {
            return;
        }

        enviarBtn.disabled = true;
        enviarBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Enviando...`;
        
        const formData = new FormData(this);
        formData.append('csrf_token', window.csrfToken);

        fetch('<?php echo BASE_URL; ?>administrador/enviarCorreoMasivo', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.mensaje || data.error);
            if (!data.error) {
                correoModal.hide();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error de conexión.');
        })
        .finally(() => {
            enviarBtn.disabled = false;
            enviarBtn.innerHTML = originalBtnText;
        });
    });
});
</script>