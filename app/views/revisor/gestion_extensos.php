<h1 class="mb-4">Panel de Gestión de Artículos Extensos</h1>
<p class="lead">Desde aquí podrás filtrar los artículos enviados, asignarlos a revisores y validar las evaluaciones firmadas.</p>

<?php CSRFHelper::getTokenInput(); ?>
<div id="mensaje-gestion"></div>

<h3 class="mt-4">Artículos Extensos Pendientes de Filtro</h3>
<p class="text-muted">Revisa que cada artículo cumpla con el formato anónimo antes de asignarlo.</p>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título del Artículo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($extensosParaFiltro)): ?>
                        <tr><td colspan="3" class="text-center">No hay artículos por filtrar en tu área.</td></tr>
                    <?php else: ?>
                        <?php foreach ($extensosParaFiltro as $extenso): ?>
                            <tr>
                                <td><?php echo $extenso['id']; ?></td>
                                <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $extenso['archivo_ruta']; ?>" target="_blank" class="btn btn-sm btn-secondary">Ver Archivo</a>
                                    <button class="btn btn-sm btn-primary btn-asignar" data-extenso-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#asignarModal">Asignar</button>
                                    <button class="btn btn-sm btn-warning btn-devolver" data-extenso-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#devolverModal">Devolver por Formato</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="asignarModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Asignar Revisores de Extensos</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Selecciona **dos** revisores para el artículo.</p>
        <form id="asignarForm">
            <input type="hidden" id="extenso_id_asignar">
            <div id="revisores-container">
                <?php foreach ($revisoresDisponibles as $rev): ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="revisores_ids[]" value="<?php echo $rev['id']; ?>" id="rev-<?php echo $rev['id']; ?>">
                  <label class="form-check-label" for="rev-<?php echo $rev['id']; ?>">
                    <?php echo htmlspecialchars($rev['nombre_completo']); ?> (<?php echo $rev['carga_actual']; ?>/4 asignados)
                  </label>
                </div>
                <?php endforeach; ?>
            </div>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="asignarForm" class="btn btn-primary">Confirmar Asignación</button></div>
    </div>
  </div>
</div>

<div class="modal fade" id="devolverModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Devolver Artículo por Formato</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Escribe las observaciones para que el autor corrija el formato (ej. "El archivo debe ser anónimo, por favor, elimina tus datos personales").</p>
        <form id="devolverForm">
            <input type="hidden" id="extenso_id_devolver">
            <textarea class="form-control" id="comentarios_formato" rows="4" required></textarea>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="devolverForm" class="btn btn-warning">Enviar Observaciones al Autor</button></div>
    </div>
  </div>
</div>
<h3 class="mt-5">Artículos Actualmente en Revisión</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título del Artículo</th>
                        <th>Revisores Asignados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($extensosEnRevision)): ?>
                        <tr><td colspan="4" class="text-center">No hay artículos en revisión en tu área.</td></tr>
                    <?php else: ?>
                        <?php foreach ($extensosEnRevision as $extenso): ?>
                            <tr>
                                <td><?php echo $extenso['id']; ?></td>
                                <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($extenso['revisores_asignados']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-secondary btn-cambiar-revisores" data-extenso-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#cambiarRevisoresModal">
                                        Cambiar Revisores
                                    </button>
                                </td>
                                <td>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="cambiarRevisoresModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Cambiar Revisores</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Selecciona los **DOS** nuevos revisores para este artículo.</p>
        <form id="cambiarRevisoresForm">
            <input type="hidden" id="extenso_id_cambiar">
            <div id="revisores-change-container">
                <?php foreach ($revisoresDisponibles as $rev): ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="revisores_ids_change[]" value="<?php echo $rev['id']; ?>" id="rev-change-<?php echo $rev['id']; ?>">
                  <label class="form-check-label" for="rev-change-<?php echo $rev['id']; ?>">
                    <?php echo htmlspecialchars($rev['nombre_completo']); ?> (<?php echo $rev['carga_actual']; ?>/4)
                  </label>
                </div>
                <?php endforeach; ?>
            </div>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="cambiarRevisoresForm" class="btn btn-primary">Guardar Cambios</button></div>
    </div>
  </div>
</div>
<h3 class="mt-5">Equipo de Revisores de Extensos en tu Área</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Grado Académico</th>
                        <th>Correo</th>
                        <th>Área de Especialidad</th>
                        <th>Carga Actual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($revisoresDisponibles)): ?>
                        <tr><td colspan="5" class="text-center">No hay Revisores de Extensos asignados a esta área.</td></tr>
                    <?php else: ?>
                        <?php foreach ($revisoresDisponibles as $revisor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($revisor['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($revisor['grado_academico']); ?></td>
                                <td><?php echo htmlspecialchars($revisor['correo']); ?></td>
                                <td><small><?php echo htmlspecialchars($revisor['area_especialidad']); ?></small></td>
                                <td>
                                    <span class="badge bg-info"><?php echo $revisor['carga_actual']; ?> / 4</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="tercerRevisorModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Asignar Tercer Revisor (Desempate)</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Selecciona **un** revisor para desempatar la evaluación.</p>
        <form id="tercerRevisorForm">
            <input type="hidden" id="extenso_id_tercero">
            <div id="revisores-container-tercero">
                <?php foreach ($revisoresDisponibles as $rev): ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="revisor_id" value="<?php echo $rev['id']; ?>" id="rev-tercero-<?php echo $rev['id']; ?>" required>
                  <label class="form-check-label" for="rev-tercero-<?php echo $rev['id']; ?>">
                    <?php echo htmlspecialchars($rev['nombre_completo']); ?> (<?php echo $rev['carga_actual']; ?>/4)
                  </label>
                </div>
                <?php endforeach; ?>
            </div>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="tercerRevisorForm" class="btn btn-danger">Confirmar Asignación</button></div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- VARIABLES GLOBALES Y DE CONFIGURACIÓN ---
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
    const mensajeDiv = document.getElementById('mensaje-gestion');
        const asignarModal = new bootstrap.Modal(document.getElementById('asignarModal'));
    const asignarForm = document.getElementById('asignarForm');
    const devolverModal = new bootstrap.Modal(document.getElementById('devolverModal'));
    const devolverForm = document.getElementById('devolverForm');
    const cambiarRevisoresModal = new bootstrap.Modal(document.getElementById('cambiarRevisoresModal'));
    const cambiarRevisoresForm = document.getElementById('cambiarRevisoresForm');
    const tercerRevisorModal = new bootstrap.Modal(document.getElementById('tercerRevisorModal'));
    const tercerRevisorForm = document.getElementById('tercerRevisorForm');


    // --- LISTENER DE CLICS PARA ABRIR MODALES ---
    document.body.addEventListener('click', function(event) {
        const target = event.target;
        const extensoId = target.getAttribute('data-extenso-id');

        if (target.classList.contains('btn-asignar')) {
            document.getElementById('extenso_id_asignar').value = extensoId;
        }
        if (target.classList.contains('btn-devolver')) {
            document.getElementById('extenso_id_devolver').value = extensoId;
        }
        if (target.classList.contains('btn-cambiar-revisores')) {
            document.getElementById('extenso_id_cambiar').value = extensoId;
            cambiarRevisoresForm.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            fetch(`${baseUrl}revisor/obtenerRevisoresAsignados/${extensoId}`)
                .then(res => res.json())
                .then(revisoresIds => {
                    revisoresIds.forEach(id => {
                        const checkbox = document.getElementById(`rev-change-${id}`);
                        if (checkbox) checkbox.checked = true;
                    });
                });
        }
        if (target.classList.contains('btn-asignar-tercero')) {
            document.getElementById('extenso_id_tercero').value = extensoId;
        }
    });

    asignarForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const revisoresSeleccionados = this.querySelectorAll('input[name="revisores_ids[]"]:checked');
        if (revisoresSeleccionados.length !== 2) {
            alert('Debe seleccionar exactamente dos revisores.');
            return;
        }
        const revisoresIds = Array.from(revisoresSeleccionados).map(cb => cb.value);
        const extensoId = document.getElementById('extenso_id_asignar').value;
        const datos = { extenso_id: extensoId, revisores_ids: revisoresIds, csrf_token: csrfToken };
        fetch(`${baseUrl}revisor/asignarRevisoresExtenso`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) })
            .then(res => res.json()).then(data => {
                alert(data.mensaje || data.error);
                if (!data.error) location.reload();
            });
    });

    cambiarRevisoresForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const revisoresSeleccionados = this.querySelectorAll('input[name="revisores_ids_change[]"]:checked');
        if (revisoresSeleccionados.length !== 2) {
            alert('Debe seleccionar exactamente dos revisores.');
            return;
        }
        const revisoresIds = Array.from(revisoresSeleccionados).map(cb => cb.value);
        const extensoId = document.getElementById('extenso_id_cambiar').value;
        const datos = { extenso_id: extensoId, revisores_ids: revisoresIds, csrf_token: csrfToken };
        fetch(`${baseUrl}revisor/actualizarRevisoresExtenso`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) })
            .then(res => res.json()).then(data => {
                alert(data.mensaje || data.error);
                if (!data.error) location.reload();
            });
    });

    devolverForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const extensoId = document.getElementById('extenso_id_devolver').value;
        const comentarios = document.getElementById('comentarios_formato').value;
        const datos = { extenso_id: extensoId, comentarios: comentarios, csrf_token: csrfToken };
        fetch(`${baseUrl}revisor/devolverExtensoPorFormato`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos) })
            .then(res => res.json()).then(data => {
                alert(data.mensaje || data.error);
                if (!data.error) location.reload();
            });
    });
        tercerRevisorForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const revisorSeleccionado = document.querySelector('input[name="revisor_id"]:checked');
            if (!revisorSeleccionado) {
                alert('Debe seleccionar un revisor.');
                return;
            }
            const datos = {
                extenso_version_id: document.getElementById('extenso_id_tercero').value, // Simulación
                revisor_id: revisorSeleccionado.value,
                csrf_token: csrfToken
            };
            fetch(`${baseUrl}revisor/asignarTercerRevisor`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
            })
            .then(res => res.json()).then(data => {
                alert(data.mensaje || data.error);
                if (!data.error) location.reload();
            });
        });
document.body.addEventListener('submit', function(event) {
    const form = event.target; 
    console.log("Paso 3: Se detectó un envío de formulario. ID del formulario:", form.id);

       if (form.id === 'devolverForm') {
            console.log("Paso 4: El formulario 'devolverForm' fue identificado.");
            event.preventDefault(); // Detenemos el envío para depurar

            const extensoId = document.getElementById('extenso_id_devolver').value;
            const comentarios = document.getElementById('comentarios_formato').value;

            if (!extensoId || !comentarios) {
                alert("ERROR DE DEPURACIÓN: El ID del extenso o los comentarios están vacíos.");
                return;
            }

            alert("¡ÉXITO! El formulario 'devolverForm' está listo para enviar la petición fetch. Revisa la consola para ver los datos.");
            console.log("Datos que se enviarían:", {
                extenso_id: extensoId,
                comentarios: comentarios,
                csrf_token: csrfToken
            });
        }

    if (form.id === 'asignarForm') {
        event.preventDefault();
        const revisoresSeleccionados = form.querySelectorAll('input[name="revisores_ids[]"]:checked');
        if (revisoresSeleccionados.length !== 2) {
            alert('Debe seleccionar exactamente dos revisores.');
            return;
        }
        const revisoresIds = Array.from(revisoresSeleccionados).map(cb => cb.value);
        const extensoId = document.getElementById('extenso_id_asignar').value;
        const datos = { extenso_id: extensoId, revisores_ids: revisoresIds, csrf_token: csrfToken };

        fetch(`${baseUrl}revisor/asignarRevisoresExtenso`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        })
        .then(res => res.json()).then(data => {
            alert(data.mensaje || data.error);
            if (!data.error) location.reload();
        });
    }
    
    if (form.id === 'cambiarRevisoresForm') {
        event.preventDefault();
        const revisoresSeleccionados = form.querySelectorAll('input[name="revisores_ids_change[]"]:checked');
        if (revisoresSeleccionados.length !== 2) {
            alert('Debe seleccionar exactamente dos revisores.');
            return;
        }
        const revisoresIds = Array.from(revisoresSeleccionados).map(cb => cb.value);
        const extensoId = document.getElementById('extenso_id_cambiar').value;
        const datos = { extenso_id: extensoId, revisores_ids: revisoresIds, csrf_token: csrfToken };

        fetch(`${baseUrl}revisor/actualizarRevisoresExtenso`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        })
        .then(res => res.json()).then(data => {
            alert(data.mensaje || data.error);
            if (!data.error) location.reload();
        });
    }

    if (form.id === 'tercerRevisorForm') {
        event.preventDefault();
        const revisorSeleccionado = form.querySelector('input[name="revisor_id"]:checked');
        if (!revisorSeleccionado) {
            alert('Debe seleccionar un revisor.');
            return;
        }
        const extensoId = document.getElementById('extenso_id_tercero').value;
        const datos = { extenso_id: extensoId, revisor_id: revisorSeleccionado.value, csrf_token: csrfToken };
        
        fetch(`${baseUrl}revisor/asignarTercerRevisor`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        })
        .then(res => res.json()).then(data => {
            alert(data.mensaje || data.error);
            if (!data.error) location.reload();
        });
    }
});
});
</script>