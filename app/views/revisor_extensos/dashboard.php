<div class="container-fluid px-4">
    <h1 class="mt-4">Panel de Revisor de Artículos Extensos</h1>
    <div id="mensaje-dashboard"></div>

    <!-- SECCIÓN 1: PENDIENTES (Acción: Evaluar) -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-pencil-square me-1"></i> Artículos Asignados (Pendientes de Evaluar)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light"><tr><th>Título</th><th>Versión</th><th>Fecha Asignación</th><th class="text-center">Acción</th></tr></thead>
                    <tbody>
                        <?php if (empty($evaluacionesPorEvaluar)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No tienes evaluaciones pendientes.</td></tr>
                        <?php else: ?>
                            <?php foreach ($evaluacionesPorEvaluar as $eval): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($eval['titulo']); ?></td>
                                    <td><span class="badge bg-secondary">v<?php echo $eval['intento']; ?></span></td>
                                    <td><?php echo date('d/m/Y'); // O fecha real si existe ?></td>
                                    <td class="text-center">
                                        <!-- Botón para ver archivo -->
                                        <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $eval['archivo_ruta']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm" title="Ver Artículo">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                        <!-- Botón para evaluar -->
                                        <a href="<?php echo BASE_URL; ?>revisorExtensos/evaluar/<?php echo $eval['id']; ?>" class="btn btn-primary btn-sm ms-2">
                                            <i class="bi bi-play-fill"></i> Evaluar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: HISTORIAL Y GESTIÓN DE FIRMAS -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <i class="bi bi-clock-history me-1"></i> Historial de Evaluaciones Realizadas
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light"><tr><th>Título</th><th>Veredicto</th><th>Estatus</th><th class="text-center">Acciones</th></tr></thead>
                    <tbody>
                        <?php if (empty($evaluacionesRealizadas)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Aún no has realizado evaluaciones.</td></tr>
                        <?php else: ?>
                            <?php foreach ($evaluacionesRealizadas as $hist): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($hist['titulo']); ?></td>
                                    <td>
                                        <?php 
                                            $v = $hist['veredicto'];
                                            $colorV = 'secondary';
                                            if($v === 'Favorable y Publicable') $colorV = 'success';
                                            if($v === 'Favorable con Correcciones') $colorV = 'info text-dark';
                                            if($v === 'No Publicable') $colorV = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $colorV; ?>"><?php echo htmlspecialchars($v); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            $est = $hist['estatus_evaluacion'];
                                            $colorE = 'secondary';
                                            if($est === 'Pendiente de Firma') $colorE = 'warning text-dark';
                                            if($est === 'Pendiente de Validación') $colorE = 'info text-dark';
                                            if($est === 'Validada') $colorE = 'success';
                                            if($est === 'Rechazada por Coordinador') $colorE = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $colorE; ?>"><?php echo htmlspecialchars($est); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $hist['archivo_ruta']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver Artículo Original">
                                            <i class="bi bi-file-text"></i>
                                        </a>

                                        <button class="btn btn-sm btn-info text-white ms-1 btn-ver-eval" 
                                            data-veredicto="<?php echo htmlspecialchars($hist['veredicto']); ?>"
                                            data-obs="<?php echo htmlspecialchars($hist['observaciones_generales'] ?? ''); ?>"
                                            data-rechazo="<?php echo htmlspecialchars($hist['argumento_rechazo'] ?? ''); ?>"
                                            data-respuestas="<?php echo htmlspecialchars($hist['respuestas_formulario']); ?>"
                                            title="Ver lo que evalué">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <?php if ($hist['veredicto'] === 'Favorable y Publicable'): ?>
                                            
                                            <?php if ($hist['estatus_evaluacion'] === 'Pendiente de Firma' || $hist['estatus_evaluacion'] === 'Rechazada por Coordinador'): ?>
                                                <a href="<?php echo BASE_URL; ?>revisorExtensos/firmar/<?php echo $hist['id']; ?>" class="btn btn-sm btn-warning ms-1" title="Subir Firma">
                                                    <i class="bi bi-pen-fill"></i> Subir Firma
                                                </a>
                                            
                                            <?php elseif (!empty($hist['pdf_firmado_ruta'])): ?>
                                                <a href="<?php echo BASE_URL; ?>archivo/ver/evaluaciones_firmadas/<?php echo $hist['pdf_firmado_ruta']; ?>" target="_blank" class="btn btn-sm btn-success ms-1" title="Ver Documento Firmado">
                                                    <i class="bi bi-file-earmark-pdf"></i> Firma
                                                </a>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Evaluación (Read-Only) -->
<div class="modal fade" id="verEvaluacionModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Detalle de la Evaluación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
          <div class="mb-3">
              <strong>Veredicto:</strong> <span id="modal-veredicto" class="badge bg-secondary"></span>
          </div>
          <div class="mb-3">
              <strong>Observaciones:</strong>
              <div id="modal-obs" class="bg-light p-2 border rounded"></div>
          </div>
          <div id="modal-rechazo-container" class="mb-3 d-none">
              <strong class="text-danger">Motivo de Rechazo:</strong>
              <div id="modal-rechazo" class="alert alert-danger py-2"></div>
          </div>
          <hr>
          <h6>Cuestionario:</h6>
          <div id="modal-respuestas"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('verEvaluacionModal'));
    
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-ver-eval');
        if (!btn) return;

        document.getElementById('modal-veredicto').textContent = btn.dataset.veredicto;
        document.getElementById('modal-obs').textContent = btn.dataset.obs || 'Sin observaciones.';
        
        const rechazoDiv = document.getElementById('modal-rechazo-container');
        if (btn.dataset.rechazo) {
            rechazoDiv.classList.remove('d-none');
            document.getElementById('modal-rechazo').textContent = btn.dataset.rechazo;
        } else {
            rechazoDiv.classList.add('d-none');
        }

        const container = document.getElementById('modal-respuestas');
        container.innerHTML = '';
        try {
            const r = JSON.parse(btn.dataset.respuestas);
            const map = {
                'pregunta_1': '1. Claridad del tema',
                'pregunta_2': '2. Fundamentación teórica',
                'pregunta_3': '3. Contenido pertinente',
                'pregunta_4': '4. Suficiencia teórica',
                'pregunta_5': '5. Hallazgos relevantes',
                'pregunta_6': '6. Referencias correctas'
            };
            for (const [k, v] of Object.entries(r)) {
                const badge = v === 'si' ? '<span class="badge bg-success">SÍ</span>' : '<span class="badge bg-danger">NO</span>';
                container.innerHTML += `<div class="d-flex justify-content-between border-bottom py-1"><span>${map[k] || k}</span>${badge}</div>`;
            }
        } catch(e) { container.innerHTML = '<p class="text-muted">No hay detalles disponibles.</p>'; }

        modal.show();
    });
});
</script>