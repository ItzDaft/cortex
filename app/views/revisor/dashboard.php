<?php
$areaNombre = '';
if (isset($revisor['area_id'])) {
    $area = AreaTematica::buscarPorId($revisor['area_id']);
    if ($area && isset($area['nombre_area'])) {
        $areaNombre = $area['nombre_area'];
    }
}
?>
<h1>Panel de Coordinador del area <?php echo htmlspecialchars($areaNombre); ?></h1>
<p class="lead">Bienvenido a tu panel de evaluación.</p>
<?php CSRFHelper::getTokenInput(); ?>
<div id="mensaje-dashboard"></div>
<?php /* --- INICIA CÓDIGO COMENTADO ---

<h3 class="mt-5">Resúmenes Disponibles en tu Área</h3>
<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Título del Resumen</th><th>Acciones</th></tr></thead>
                <tbody id="tabla-disponibles">
                    <?php if (empty($resumenesDisponibles)): ?>
                        <tr><td colspan="2" class="text-center">No hay nuevos resúmenes disponibles en tu área.</td></tr>
                    <?php else: ?>
                        <?php foreach ($resumenesDisponibles as $resumen): ?>
                            <tr id="resumen-<?php echo $resumen['id']; ?>">
                                <td><?php echo htmlspecialchars($resumen['titulo']); ?></td>
                                <td><button class="btn btn-sm btn-success btn-reclamar" data-id="<?php echo $resumen['id']; ?>">Reclamar para Revisar</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
--- FIN CÓDIGO COMENTADO --- */ ?>

<h3 class="mt-5">Mis Revisiones Pendientes</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Título del Resumen</th><th>Tipo</th><th>Fecha de Asignación</th><th>Acciones</th></tr></thead>
                <tbody id="tabla-asignadas">
                    <?php if (empty($revisionesAsignadas)): ?>
                        <tr><td colspan="4" class="text-center">No tienes revisiones pendientes.</td></tr>
                    <?php else: ?>
                        <?php foreach ($revisionesAsignadas as $revision): ?>
                            <tr>

                                <td><?php echo htmlspecialchars($revision['titulo']); ?></td>
                                <td>
                                <?php if (str_contains($revision['autor_roles'], 'Autor')): ?>
                                    <span class="badge bg-info">Extenso</span>
                                <?php elseif (str_contains($revision['autor_roles'], 'Asistente con Cartel')): ?>
                                    <span class="badge bg-light text-dark border">Póster</span>
                                <?php endif; ?>

                                </td>
                                <td><?php echo date('d/m/Y', strtotime($revision['fecha_asignacion'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>revisor/evaluar/<?php echo $revision['resumen_id']; ?>" class="btn btn-sm btn-primary">Evaluar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<h3 class="mt-5">Historial de Revisiones Completadas</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Título del Resumen</th>
                        <th>Tipo</th>
                        <th>Tu Veredicto</th>
                        <th>Fecha de Revisión</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($revisionesCompletadas)): ?>
                        <tr><td colspan="4" class="text-center">Aún no has completado ninguna revisión.</td></tr>
                    <?php else: ?>
                        <?php foreach ($revisionesCompletadas as $revision): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($revision['titulo']); ?></td>
                                <td>
                                <?php if (str_contains($revision['autor_roles'], 'Autor')): ?>
                                    <span class="badge bg-info">Extenso</span>
                                <?php elseif (str_contains($revision['autor_roles'], 'Asistente con Cartel')): ?>
                                    <span class="badge bg-light text-dark border">Póster</span>
                                <?php endif; ?>  
                                </td>
                                <td>
                                    <?php 
                                        $veredicto = htmlspecialchars($revision['veredicto']);
                                        $badge_class = ($veredicto == 'Aceptado') ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $veredicto; ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($revision['fecha_revision'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<h3 class="mt-5">Artículos Extensos Pendientes de Filtro</h3>
<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead><tr><th>ID</th><th>Título del Artículo</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php if (empty($extensosParaMiArea)): // Asegúrate de que la variable sea la correcta ?>
                    <tr><td colspan="3" class="text-center">No hay artículos por filtrar en tu área.</td></tr>
                <?php else: ?>
                    <?php foreach ($extensosParaMiArea as $extenso): ?>
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
<h3 class="mt-5">Evaluaciones Firmadas Pendientes de Validación</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Artículo</th><th>Revisor del Extenso</th><th>PDF Firmado</th><th>Acciones</th></tr></thead>
                <tbody id="tabla-validar-evaluaciones">
                    <?php if (empty($evaluacionesPorValidar)): ?>
                        <tr><td colspan="4" class="text-center">No hay evaluaciones por validar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($evaluacionesPorValidar as $eval): ?>
                        <tr id="eval-row-<?php echo $eval['id']; ?>">
                            <td><?php echo htmlspecialchars($eval['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($eval['revisor_nombre']); ?></td>
                            <td><a href="<?php echo BASE_URL; ?>archivo/ver/evaluaciones_firmadas/<?php echo $eval['pdf_firmado_ruta']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Ver PDF</a></td>
                            <td>
                                <button class="btn btn-sm btn-success btn-aprobar-eval" data-id="<?php echo $eval['id']; ?>">Aprobar</button>
                                <button class="btn btn-sm btn-danger btn-rechazar-eval" data-id="<?php echo $eval['id']; ?>" data-bs-toggle="modal" data-bs-target="#rechazoCoordModal">Rechazar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="rechazoCoordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Motivo del Rechazo de Evaluación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Escribe un comentario para el Revisor del Extenso explicando por qué su evaluación fue rechazada (ej. "Falta firma").</p>
        <form id="rechazoCoordForm">
            <input type="hidden" id="eval_id_rechazo">
            <textarea class="form-control" id="comentarios_coord_rechazo" rows="3" required></textarea>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="rechazoCoordForm" class="btn btn-danger">Confirmar Rechazo</button></div>
    </div>
  </div>
</div>
<h3 class="mt-5 text-danger">Artículos en Conflicto (Requieren 3er Revisor)</h3>
<div class="card border-danger">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>ID</th><th>Título del Artículo</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($extensosEnConflicto)): ?>
                        <tr><td colspan="3" class="text-center">No hay artículos en conflicto.</td></tr>
                    <?php else: ?>
                        <?php foreach ($extensosEnConflicto as $extenso): ?>
                            <tr>
                                <td><?php echo $extenso['id']; ?></td>
                                <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger btn-asignar-tercero" data-extenso-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#tercerRevisorModal">
                                        Asignar 3er Revisor
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
<!-- 
<script>
    <?php /* --- INICIA CÓDIGO COMENTADO ---

document.getElementById('tabla-disponibles').addEventListener('click', function(event) {
    if (event.target.classList.contains('btn-reclamar')) {
        const resumenId = event.target.getAttribute('data-id');
        
        const baseUrl = '<?php echo BASE_URL; ?>';
        const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
        
        fetch(`${baseUrl}revisor/reclamarResumen`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ resumen_id: resumenId, csrf_token: csrfToken })
        })
        .then(response => response.json())
        .then(data => {
            const mensajeDiv = document.getElementById('mensaje-dashboard');
            if (data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                mensajeDiv.innerHTML = `<div class="alert alert-success">${data.mensaje}</div>`;
                location.reload(); 
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
--- FIN CÓDIGO COMENTADO --- */ ?>

</script>
-->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
    const mensajeDiv = document.getElementById('mensaje-dashboard');
    
    const asignarModal = new bootstrap.Modal(document.getElementById('asignarModal'));
    const asignarForm = document.getElementById('asignarForm');
    const rechazoCoordModal = new bootstrap.Modal(document.getElementById('rechazoCoordModal'));
    const tercerRevisorModal = new bootstrap.Modal(document.getElementById('tercerRevisorModal'));
    const tercerRevisorForm = document.getElementById('tercerRevisorForm');
    document.body.addEventListener('click', function(event) {
        const target = event.target;
        const extensoId = target.getAttribute('data-extenso-id');
        const evalId = target.getAttribute('data-id');

        if (target.classList.contains('btn-asignar')) {
            document.getElementById('extenso_id_asignar').value = extensoId;
        }

        if (target.classList.contains('btn-aprobar-eval')) {
            if (confirm('¿Seguro que deseas APROBAR esta evaluación?')) {
                procesarValidacion(evalId, 'Validada');
            }
        }
        if (target.classList.contains('btn-rechazar-eval')) {
            document.getElementById('eval_id_rechazo').value = evalId;
        }
        if (event.target.classList.contains('btn-asignar-tercero')) {
            const extensoId = event.target.getAttribute('data-extenso-id');
            document.getElementById('extenso_id_tercero').value = extensoId;
        }
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
    asignarForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const revisoresSeleccionados = document.querySelectorAll('input[name="revisores_ids[]"]:checked');
        if (revisoresSeleccionados.length !== 2) {
            alert('Debe seleccionar exactamente dos revisores.');
            return;
        }
        const revisoresIds = Array.from(revisoresSeleccionados).map(cb => cb.value);
        const extensoId = document.getElementById('extenso_id_asignar').value;
        const datos = {
            extenso_version_id: extensoId, // Simulación, ajustar si hay múltiples versiones
            revisores_ids: revisoresIds,
            csrf_token: csrfToken
        };
        fetch(`${baseUrl}revisor/asignarRevisoresExtenso`, {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(datos)
        })
        .then(res => res.json()).then(data => {
            alert(data.mensaje || data.error);
            if (!data.error) location.reload();
        });
    });

    document.getElementById('rechazoCoordForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const evalId = document.getElementById('eval_id_rechazo').value;
        const comentarios = document.getElementById('comentarios_coord_rechazo').value;
        procesarValidacion(evalId, 'Rechazada por Coordinador', comentarios);
        rechazoCoordModal.hide();
        this.reset();
    });

    function procesarValidacion(evalId, accion, comentarios = null) {
        fetch(`${baseUrl}revisor/validarEvaluacionFirmada`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ evaluacion_id: evalId, accion: accion, comentarios: comentarios, csrf_token: csrfToken })
        })
        .then(res => res.json()).then(data => {
            if (data.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                document.getElementById(`eval-row-${evalId}`).remove();
                mensajeDiv.innerHTML = `<div class="alert alert-success">${data.mensaje}</div>`;
            }
        });
    }
});
</script>