<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Artículos Extensos</h1>
    <p class="lead text-muted">Panel de control para filtrar, asignar y validar evaluaciones de extensos.</p>
    
    <?php CSRFHelper::getTokenInput(); ?>
    <div id="mensaje-gestion"></div>

    <!-- STAGE A: Validación de Formato -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-file-earmark-check me-1"></i> Etapa A: Pendientes de Validación de Formato
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Título del Artículo</th>
                            <th>Archivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($extensosPendientesFiltro)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No hay artículos pendientes de validación de formato.</td></tr>
                        <?php else: ?>
                            <?php foreach ($extensosPendientesFiltro as $extenso): ?>
                                <tr id="row-filtro-<?php echo $extenso['id']; ?>">
                                    <td><?php echo $extenso['id']; ?></td>
                                    <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $extenso['archivo_ruta']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-file-pdf"></i> Ver PDF
                                        </a>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success btn-aprobar-formato" data-extenso-id="<?php echo $extenso['id']; ?>">
                                            <i class="bi bi-check-lg"></i> Aprobar
                                        </button>
                                        <button class="btn btn-sm btn-warning btn-devolver" data-extenso-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#devolverModal">
                                            <i class="bi bi-arrow-return-left"></i> Devolver
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

    <!-- STAGE B: Asignación de Revisores -->
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <i class="bi bi-people me-1"></i> Etapa B: Listos para Asignación (Formato Aprobado)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Título del Artículo</th>
                            <th>Archivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($extensosPorAsignar)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No hay artículos esperando asignación.</td></tr>
                        <?php else: ?>
                            <?php foreach ($extensosPorAsignar as $extenso): ?>
                                <tr id="row-asignar-<?php echo $extenso['id']; ?>">
                                    <td><?php echo $extenso['id']; ?></td>
                                    <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $extenso['archivo_ruta']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-file-pdf"></i> Ver PDF
                                        </a>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-asignar" data-extenso-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#asignarModal" data-id="<?php echo $extenso['id']; ?>" data-titulo="<?php echo htmlspecialchars($extenso['titulo']); ?>" onclick="prepararModal(this)">
                                            <i class="bi bi-person-plus-fill"></i> Asignar Revisores
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

    <!-- STAGE C: En Revisión -->
    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-dark">
            <i class="bi bi-hourglass-split me-1"></i> Etapa C: En Proceso de Revisión
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Título del Artículo</th>
                            <th>Revisores Asignados</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($extensosEnRevision)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No hay artículos en revisión.</td></tr>
                        <?php else: ?>
                            <?php foreach ($extensosEnRevision as $extenso): ?>
                                <tr>
                                    <td><?php echo $extenso['id']; ?></td>
                                    <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                    <td><small><?php echo htmlspecialchars($extenso['revisores_asignados']); ?></small></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary btn-cambiar-revisores" data-extenso-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#cambiarRevisoresModal">
                                            <i class="bi bi-arrow-repeat"></i> Cambiar Revisores
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
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Conflictos (Requieren Desempate)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Título del Artículo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($extensosEnConflicto as $extenso): ?>
                            <tr id="row-conflicto-<?php echo $extenso['id']; ?>">
                                <td><?php echo $extenso['id']; ?></td>
                                <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger btn-asignar-tercero" data-extenso-id="<?php echo $extenso['version_id']; ?>" data-bs-toggle="modal" data-bs-target="#tercerRevisorModal">
                                        <i class="bi bi-person-plus"></i> Asignar 3er Revisor
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- --- MODALES --- -->

<!-- Modal Devolver Formato -->
<div class="modal fade" id="devolverModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning"><h5 class="modal-title">Devolver por Formato</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Indica las correcciones necesarias:</p>
        <form id="devolverForm">
            <input type="hidden" id="extenso_id_devolver">
            <textarea class="form-control" id="comentarios_formato" rows="4" required placeholder="Ej: Contiene datos personales..."></textarea>
        </form>
      </div>
      <div class="modal-footer"><button type="submit" form="devolverForm" class="btn btn-warning">Enviar</button></div>
    </div>
  </div>
</div>

<!-- Modal Asignar -->
<div class="modal fade" id="asignarModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h5 class="modal-title">Asignar Revisores</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p class="text-muted">Selecciona <strong>dos</strong> revisores (Límite: 2 extensos por revisor):</p>
        <form id="asignarForm">
            <input type="hidden" id="extenso_id_asignar">
            <div id="revisores-container" style="max-height: 300px; overflow-y: auto;">
                <?php foreach ($revisoresDisponibles as $rev): ?>
                      <?php 
                            $carga = $rev['carga_actual'];
                            $full = ($carga >= 2); 
                            $color = $full ? 'text-danger' : 'text-success';
                            
                            $disabledAttr = $full ? 'disabled' : '';
                            $opacityStyle = $full ? 'opacity: 0.6;' : '';
                    ?>
                <div class="form-check py-1 border-bottom" style="<?php echo $opacityStyle; ?>">
                <input class="form-check-input" type="checkbox" name="revisores_ids[]" value="<?php echo $rev['id']; ?>" id="rev-<?php echo $rev['id']; ?>" <?php echo $disabledAttr; ?>>
                  <label class="form-check-label w-100" for="rev-<?php echo $rev['id']; ?>">
                    <strong><?php echo htmlspecialchars($rev['nombre_completo']); ?></strong>
                    <br><small class="<?php echo $color; ?>">Carga: <?php echo $carga; ?> / 2 <?php echo $full ? '(Lleno)' : ''; ?></small>
                  </label>
                </div>
                <?php endforeach; ?>
            </div>
        </form>
      </div>
      <div class="modal-footer"><button type="submit" form="asignarForm" class="btn btn-primary">Confirmar</button></div>
    </div>
  </div>
</div>

<!-- Modal Cambiar Revisores -->
<div class="modal fade" id="cambiarRevisoresModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Modificar Asignación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Selecciona los nuevos revisores (esto borrará las evaluaciones previas de esta versión):</p>
        <form id="cambiarRevisoresForm">
            <input type="hidden" id="extenso_id_cambiar">
            <div id="revisores-change-container" style="max-height: 300px; overflow-y: auto;">
                <?php foreach ($revisoresDisponibles as $rev): ?>
                <div class="form-check py-1 border-bottom">
                  <input class="form-check-input" type="checkbox" name="revisores_ids_change[]" value="<?php echo $rev['id']; ?>" id="rev-ch-<?php echo $rev['id']; ?>">
                  <label class="form-check-label w-100" for="rev-ch-<?php echo $rev['id']; ?>">
                    <strong><?php echo htmlspecialchars($rev['nombre_completo']); ?></strong>
                    <br><small class="text-muted">Carga: <?php echo $rev['carga_actual']; ?></small>
                  </label>
                </div>
                <?php endforeach; ?>
            </div>
        </form>
      </div>
      <div class="modal-footer"><button type="submit" form="cambiarRevisoresForm" class="btn btn-primary">Guardar Cambios</button></div>
    </div>
  </div>
</div>

<!-- Modal Tercer Revisor -->
<div class="modal fade" id="tercerRevisorModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Desempate</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Selecciona <strong>un</strong> revisor para desempatar:</p>
        <form id="tercerRevisorForm">
            <input type="hidden" id="extenso_version_id_tercero">
            <div style="max-height: 300px; overflow-y: auto;">
                <?php foreach ($revisoresDisponibles as $rev): ?>
                <div class="form-check py-1 border-bottom">
                  <input class="form-check-input" type="radio" name="revisor_id" value="<?php echo $rev['id']; ?>" id="rev-t-<?php echo $rev['id']; ?>" required>
                  <label class="form-check-label w-100" for="rev-t-<?php echo $rev['id']; ?>">
                    <strong><?php echo htmlspecialchars($rev['nombre_completo']); ?></strong>
                    <br><small class="text-muted">Carga: <?php echo $rev['carga_actual']; ?></small>
                  </label>
                </div>
                <?php endforeach; ?>
            </div>
        </form>
      </div>
      <div class="modal-footer"><button type="submit" form="tercerRevisorForm" class="btn btn-danger">Asignar</button></div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
    const mensajeDiv = document.getElementById('mensaje-gestion');
    
    // Instancias de Modales
    const modalObjects = {
        asignar: new bootstrap.Modal(document.getElementById('asignarModal')),
        devolver: new bootstrap.Modal(document.getElementById('devolverModal')),
        cambiar: new bootstrap.Modal(document.getElementById('cambiarRevisoresModal')),
        tercero: new bootstrap.Modal(document.getElementById('tercerRevisorModal'))
    };

    // --- DELEGACIÓN DE EVENTOS (CLICK) ---
    document.body.addEventListener('click', function(event) {
        const t = event.target.closest('button');
        if (!t) return;

        // 1. Modales Simples
        if (t.classList.contains('btn-devolver')) {
            document.getElementById('extenso_id_devolver').value = t.dataset.extensoId;
        }
        if (t.classList.contains('btn-asignar')) {
            document.getElementById('extenso_id_asignar').value = t.dataset.extensoId;
        }
        if (t.classList.contains('btn-cambiar-revisores')) {
            document.getElementById('extenso_id_cambiar').value = t.dataset.extensoId;
            document.querySelectorAll('#revisores-change-container input').forEach(c => c.checked = false);
            fetch(`${baseUrl}revisor/obtenerRevisoresAsignados/${t.dataset.extensoId}`)
                .then(r => r.json())
                .then(ids => {
                    ids.forEach(id => {
                        const cb = document.getElementById(`rev-ch-${id}`);
                        if(cb) cb.checked = true;
                    });
                });
        }
        if (t.classList.contains('btn-asignar-tercero')) {
            document.getElementById('extenso_version_id_tercero').value = t.dataset.extensoId;
        }

        // 2. Acciones Directas (Confirmación)
        if (t.classList.contains('btn-aprobar-formato')) {
            if(confirm('¿Aprobar formato y pasar a asignación?')) {
                apiCall('revisor/aprobarFormatoExtenso', { extenso_id: t.dataset.extensoId }, () => {
                    document.getElementById(`row-filtro-${t.dataset.extensoId}`).remove();
                });
            }
        }
    });

    // --- MANEJO DE FORMULARIOS ---
    
    // Devolver
    document.getElementById('devolverForm').addEventListener('submit', e => {
        e.preventDefault();
        const id = document.getElementById('extenso_id_devolver').value;
        const comm = document.getElementById('comentarios_formato').value;
        apiCall('revisor/devolverExtensoPorFormato', { extenso_id: id, comentarios: comm }, () => {
            modalObjects.devolver.hide();
            document.getElementById(`row-filtro-${id}`).remove();
        });
    });

    // Asignar (Validar 2)
    document.getElementById('asignarForm').addEventListener('submit', e => {
        e.preventDefault();
        const checked = document.querySelectorAll('input[name="revisores_ids[]"]:checked');
        if (checked.length !== 2) { alert('Debes seleccionar exactamente 2 revisores.'); return; }
        const ids = Array.from(checked).map(c => c.value);
        const id = document.getElementById('extenso_id_asignar').value;
        
        apiCall('revisor/asignarRevisoresExtenso', { extenso_id: id, revisores_ids: ids }, () => {
            modalObjects.asignar.hide();
            document.getElementById(`row-asignar-${id}`).remove();
        });
    });

    // Cambiar Revisores (Validar 2)
    document.getElementById('cambiarRevisoresForm').addEventListener('submit', e => {
        e.preventDefault();
        const checked = document.querySelectorAll('input[name="revisores_ids_change[]"]:checked');
        if (checked.length !== 2) { alert('Debes seleccionar exactamente 2 revisores.'); return; }
        const ids = Array.from(checked).map(c => c.value);
        const id = document.getElementById('extenso_id_cambiar').value;
        
        apiCall('revisor/actualizarRevisoresExtenso', { extenso_id: id, revisores_ids: ids }, () => {
            modalObjects.cambiar.hide();
            location.reload();
        });
    });

    // Tercer Revisor (Validar 1)
    document.getElementById('tercerRevisorForm').addEventListener('submit', e => {
        e.preventDefault();
        const radio = document.querySelector('input[name="revisor_id"]:checked');
        if (!radio) { alert('Selecciona un revisor.'); return; }
        
        apiCall('revisor/asignarTercerRevisor', { 
            extenso_version_id: document.getElementById('extenso_version_id_tercero').value, 
            revisor_id: radio.value 
        }, () => {
            modalObjects.tercero.hide();
            location.reload();
        });
    });

    // API Helper
    function apiCall(url, data, onSuccess) {
        data.csrf_token = csrfToken;
        fetch(`${baseUrl}${url}`, {
            method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.error) {
                mensajeDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show">${resp.error}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
            } else {
                mensajeDiv.innerHTML = `<div class="alert alert-success alert-dismissible fade show">${resp.mensaje}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                if(onSuccess) onSuccess();
            }
        })
        .catch(err => console.error(err));
    }

});
    function prepararModal(boton) {
    var form = document.getElementById('asignarForm');
    
    form.reset();

    var idExtenso = boton.getAttribute('data-id');
    var tituloExtenso = boton.getAttribute('data-titulo');

    document.getElementById('extenso_id_asignar').value = idExtenso;

    var modalTitle = document.querySelector('#asignarModal .modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Asignar Revisores a: ' + tituloExtenso;
    }
}
</script>