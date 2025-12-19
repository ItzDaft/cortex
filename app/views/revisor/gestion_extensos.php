<h1 class="mb-4">Panel de Gestión de Artículos Extensos</h1>
<p class="lead">Desde aquí podrás filtrar los artículos enviados, asignarlos a revisores y validar las evaluaciones firmadas.</p>

<?php CSRFHelper::getTokenInput(); ?>
<div id="mensaje-gestion"></div>

<!-- STAGE A: Validacion de Formato -->
<h3 class="mt-4 text-primary">Etapa A: Pendientes de Validación de Formato</h3>
<p class="text-muted">Revisa que el archivo sea anónimo y cumpla con la estructura.</p>
<div class="card mb-4">
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
                    <?php if (empty($extensosPendientesFiltro)): ?>
                        <tr><td colspan="3" class="text-center">No hay artículos pendientes de validación de formato.</td></tr>
                    <?php else: ?>
                        <?php foreach ($extensosPendientesFiltro as $extenso): ?>
                            <tr>
                                <td><?php echo $extenso['id']; ?></td>
                                <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $extenso['archivo_ruta']; ?>" target="_blank" class="btn btn-sm btn-secondary">Ver Archivo</a>
                                    <button class="btn btn-sm btn-success btn-aprobar-formato" data-extenso-id="<?php echo $extenso['id']; ?>">Aprobar Formato</button>
                                    <button class="btn btn-sm btn-warning btn-devolver" data-extenso-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#devolverModal">Devolver</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- STAGE B: Asignacion de Revisores -->
<h3 class="mt-4 text-primary">Etapa B: Extensos Listos para Asignación</h3>
<p class="text-muted">Artículos con formato validado. Asigna 2 revisores pares ciegos.</p>
<div class="card mb-4">
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
                    <?php if (empty($extensosPorAsignar)): ?>
                        <tr><td colspan="3" class="text-center">No hay artículos esperando asignación.</td></tr>
                    <?php else: ?>
                        <?php foreach ($extensosPorAsignar as $extenso): ?>
                            <tr>
                                <td><?php echo $extenso['id']; ?></td>
                                <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $extenso['archivo_ruta']; ?>" target="_blank" class="btn btn-sm btn-secondary">Ver Archivo</a>
                                    <button class="btn btn-sm btn-primary btn-asignar" data-extenso-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#asignarModal">Asignar Revisores</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- STAGE C: En Revision -->
<h3 class="mt-4 text-info">Etapa C: Artículos en Revisión</h3>
<div class="card mb-4">
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
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- STAGE D: Conflictos -->
<?php if (!empty($extensosEnConflicto)): ?>
<h3 class="mt-4 text-danger">Conflictos (Requiere Desempate)</h3>
<div class="card mb-4 border-danger">
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
                    <?php foreach ($extensosEnConflicto as $extenso): ?>
                        <tr>
                            <td><?php echo $extenso['id']; ?></td>
                            <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-danger btn-asignar-tercero" data-extenso-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#tercerRevisorModal">Asignar 3er Revisor</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- TEAM: Revisores -->
<h3 class="mt-5">Equipo de Revisores de Extensos en tu Área</h3>
<div class="row">
    <?php if (empty($revisoresDisponibles)): ?>
        <div class="col-12"><p class="text-center text-muted">No hay Revisores de Extensos asignados a esta área.</p></div>
    <?php else: ?>
        <?php foreach ($revisoresDisponibles as $revisor): ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <?php if (!empty($revisor['foto_ruta'])): ?>
                            <img src="<?php echo BASE_URL . 'uploads/revisores_perfil/' . $revisor['foto_ruta']; ?>" alt="Foto Perfil" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px; color: white; font-size: 2rem;">
                                <?php echo strtoupper(substr($revisor['nombre_completo'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>

                        <h5 class="card-title"><?php echo htmlspecialchars($revisor['nombre_completo']); ?></h5>
                        <p class="card-text small text-muted"><?php echo htmlspecialchars($revisor['grado_academico']); ?></p>
                        <p class="card-text small"><?php echo htmlspecialchars($revisor['area_especialidad']); ?></p>
                        <p class="card-text small text-muted"><?php echo htmlspecialchars($revisor['correo']); ?></p>

                        <div class="mt-2">
                             <span class="badge bg-info mb-2">Carga: <?php echo $revisor['carga_actual']; ?> / 4</span>
                             <?php if (!empty($revisor['comprobante_sni_ruta'])): ?>
                                <br>
                                <a href="<?php echo BASE_URL . 'uploads/revisores_perfil/' . $revisor['comprobante_sni_ruta']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Ver CV/SNI</a>
                             <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<!-- MODALES -->
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
                    <?php echo htmlspecialchars($rev['nombre_completo']); ?> (<?php echo $rev['carga_actual']; ?>/4)
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
        <p>Escribe las observaciones para que el autor corrija el formato.</p>
        <form id="devolverForm">
            <input type="hidden" id="extenso_id_devolver">
            <textarea class="form-control" id="comentarios_formato" rows="4" required></textarea>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="devolverForm" class="btn btn-warning">Enviar Observaciones al Autor</button></div>
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
    // --- VARIABLES GLOBALES ---
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';

    // --- LISTENER UNIFICADO DE CLICS (Abrir modales) ---
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
            const form = document.getElementById('cambiarRevisoresForm');
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
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
        // NUEVO: Aprobar Formato
        if (target.classList.contains('btn-aprobar-formato')) {
            if (confirm('¿Estás seguro de que el formato es correcto? El artículo pasará a la lista de asignación.')) {
                const datos = { extenso_id: extensoId, csrf_token: csrfToken };
                fetch(`${baseUrl}revisor/aprobarFormatoExtenso`, {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
                })
                .then(res => res.json()).then(data => {
                    alert(data.mensaje || data.error);
                    if (!data.error) location.reload();
                });
            }
        }
    });

    // --- LISTENER UNIFICADO DE ENVÍOS ---
    document.body.addEventListener('submit', function(event) {
        const form = event.target;

        // 1. Devolver por Formato
        if (form.id === 'devolverForm') {
            event.preventDefault();
            const extensoId = document.getElementById('extenso_id_devolver').value;
            const comentarios = document.getElementById('comentarios_formato').value;
            
            const datos = { extenso_id: extensoId, comentarios: comentarios, csrf_token: csrfToken };
            fetch(`${baseUrl}revisor/devolverExtensoPorFormato`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
            })
            .then(res => res.json()).then(data => {
                alert(data.mensaje || data.error);
                if (!data.error) location.reload();
            });
        }

        // 2. Asignar Revisores
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
        
        // 3. Cambiar Revisores
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

        // 4. Asignar Tercer Revisor
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