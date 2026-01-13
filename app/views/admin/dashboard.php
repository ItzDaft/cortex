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
        <hr class="my-5" style="border-top: 2px dashed #bbb;">

<div class="mt-4">
    <h5 class="fw-bold text-danger"><i class="bi bi-key-fill"></i> Reenvío Masivo de Credenciales</h5>
    
    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
        <div>
            <strong>¡Atención!</strong> Esta acción <b>cambiará la contraseña</b> de todos los usuarios del rol seleccionado y les enviará una nueva por correo electrónico. Esta acción no se puede deshacer.
        </div>
    </div>

    <div class="mb-3">
        <label for="rolDestinoCredenciales" class="form-label fw-bold">Seleccionar Grupo de Usuarios:</label>
        <select id="rolDestinoCredenciales" class="form-select form-select-lg">
            <option value="">-- Seleccione un Rol --</option>
            <option value="2">Autores</option>
            <option value="3">Revisores de Extenso</option>
            <option value="4">Revisores de Pagos</option>
            <option value="5">Coordinadores de Área</option>
            <option value="6">Coordinador General</option>
        </select>
    </div>

    <div class="d-grid gap-2">
        <button type="button" id="btnEnviarCredenciales" class="btn btn-danger btn-lg">
            <i class="bi bi-envelope-paper-fill"></i> Generar y Enviar Credenciales Nuevas
        </button>
    </div>

    <div id="loadingCredenciales" class="text-center mt-3 d-none">
        <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Procesando...</span>
        </div>
        <p class="mt-2 text-muted fw-bold">Procesando usuarios y enviando correos...<br><small>Por favor no cierre esta ventana.</small></p>
    </div>
</div>
        <form id="correoMasivoForm" enctype="multipart/form-data">
            <!-- CSRF token hidden input (used by JS) -->
            <input type="hidden" name="csrf_token" id="csrf_token_input" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

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
    const btnCredenciales = document.getElementById('btnEnviarCredenciales');
    // Initialize CSRF token from hidden input if present
    window.csrfToken = window.csrfToken || document.getElementById('csrf_token_input')?.value || '';

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
    if (btnCredenciales) {
        // Use a single event listener (no inline onclick). Pass the event to the handler.
        btnCredenciales.addEventListener('click', function(e) { enviarCredencialesMasivas(e); });
    }
    async function enviarCredencialesMasivas(event) {
        const rolSelect = document.getElementById('rolDestinoCredenciales');
        const rolId = rolSelect.value;
        const loadingDiv = document.getElementById('loadingCredenciales');
        const btnEnviar = event && event.target ? event.target : document.querySelector('#rolDestinoCredenciales + .d-grid .btn');

        // 1. Validaciones
        if (!rolId) {
            alert("Por favor selecciona un rol de la lista.");
            rolSelect.focus();
            return;
        }

        const confirmacion = confirm(`⚠️ ADVERTENCIA DE SEGURIDAD ⚠️\n\nEstás a punto de CAMBIAR LA CONTRASEÑA de TODOS los usuarios del rol seleccionado.\n\nSe les enviará un correo con su nueva clave temporal.\n\n¿Estás realmente seguro de continuar?`);
        
        if (!confirmacion) return;

        // 2. Interfaz de carga
        if (btnEnviar) btnEnviar.disabled = true;
        rolSelect.disabled = true;
        loadingDiv.classList.remove('d-none'); // Mostrar spinner

        try {
            // 3. Petición al Servidor
            const url = '<?php echo BASE_URL; ?>administrador/enviarCredencialesMasivas';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ rol_id: rolId, csrf_token: window.csrfToken })
            });

            const data = await response.json();

            // 4. Manejo de Respuesta
            if (response.ok) {
                let mensaje = `✅ Proceso Finalizado.\n\n`;
                mensaje += `Total procesados: ${data.total_usuarios}\n`;
                mensaje += `Correos enviados con éxito: ${data.enviados_exitosamente}\n`;
                
                if(data.fallidos > 0) {
                    mensaje += `⚠️ Fallos: ${data.fallidos} (Revisa los logs del servidor)`;
                }

                alert(mensaje);
            } else {
                throw new Error(data.error || "Error desconocido en el servidor");
            }

        } catch (error) {
            console.error("Error:", error);
            alert("❌ Ocurrió un error al procesar la solicitud:\n" + (error.message || error));
        } finally {
            // 5. Restaurar Interfaz
            if (btnEnviar) btnEnviar.disabled = false;
            rolSelect.disabled = false;
            rolSelect.value = ""; // Limpiar selección
            loadingDiv.classList.add('d-none'); // Ocultar spinner
        }
    }

});
</script>