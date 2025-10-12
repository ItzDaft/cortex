<h1 class="mb-4">Gestión de Artículos Extensos</h1>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID Extenso</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Área</th>
                        <th>Estatus</th>
                        <th>Revisores Asignados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($extensos)): ?>
                        <tr><td colspan="8" class="text-center">No se han enviado artículos extensos todavía.</td></tr>
                    <?php else: ?>
                        <?php foreach($extensos as $index => $extenso): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $extenso['id']; ?></td>
                                <td><?php echo htmlspecialchars($extenso['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($extenso['autor_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($extenso['nombre_area']); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($extenso['estatus_extenso']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($extenso['revisores_asignados'] ?? 'Pendiente'); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-ver-evaluaciones" data-id="<?php echo $extenso['id']; ?>" data-bs-toggle="modal" data-bs-target="#evaluacionesModal">
                                        Ver Evaluaciones
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

<div class="modal fade" id="evaluacionesModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de Evaluaciones del Extenso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="evaluacionesBody">
        <div class="text-center"><div class="spinner-border"></div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';
    const evaluacionesModal = new bootstrap.Modal(document.getElementById('evaluacionesModal'));
    const evaluacionesBody = document.getElementById('evaluacionesBody');

    document.body.addEventListener('click', function(event) {
        if (event.target.classList.contains('btn-ver-evaluaciones')) {
            const extensoId = event.target.getAttribute('data-id');
            evaluacionesBody.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';

            fetch(`${baseUrl}administrador/obtenerEvaluacionesExtenso/${extensoId}`)
                .then(res => res.json())
                .then(evaluaciones => {
                    evaluacionesBody.innerHTML = `<h5>Evaluaciones para el Extenso #${extensoId}</h5>`;

                    if (evaluaciones.length === 0) {
                        evaluacionesBody.innerHTML += '<p class="text-muted">Este extenso aún no tiene evaluaciones registradas.</p>';
                        return;
                    }

                    let html = '';
                    evaluaciones.forEach(eval => {
                        const veredictoClass = eval.veredicto === 'Favorable y Publicable' ? 'text-success' : (eval.veredicto === 'No Publicable' ? 'text-danger' : 'text-warning');
                        const pdfLink = eval.pdf_firmado_ruta 
                            ? `<a href="${baseUrl}archivo/ver/evaluaciones_firmadas/${eval.pdf_firmado_ruta}" target="_blank">Ver PDF Firmado</a>`
                            : '<span class="text-muted">Pendiente de firma</span>';

                        html += `
                            <div class="border-top pt-3 mt-3">
                                <h6>Evaluación de: ${eval.revisor_nombre}</h6>
                                <p class="mb-1"><strong>Veredicto:</strong> <span class="${veredictoClass}">${eval.veredicto}</span></p>
                                <p class="mb-1"><strong>Observaciones:</strong> ${eval.observaciones_generales || '<em>Sin observaciones.</em>'}</p>
                                <p class="mb-1"><strong>PDF Firmado:</strong> ${pdfLink}</p>
                            </div>
                        `;
                    });
                    evaluacionesBody.innerHTML += html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    evaluacionesBody.innerHTML = '<p class="text-danger">Ocurrió un error al cargar las evaluaciones.</p>';
                });
        }
    });
});
</script>