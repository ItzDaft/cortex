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

            // Nota: La ruta 'administrador/obtenerEvaluacionesExtenso' es hipotética.
            // Necesitaríamos crearla en AdministradorController si se requiere.
            // Por ahora, mostraremos un mensaje de ejemplo.

            // Simulación de datos (reemplazar con un fetch real si es necesario)
            evaluacionesBody.innerHTML = `
                <h5>Evaluaciones para el Extenso #${extensoId}</h5>
                <p>Funcionalidad para ver los detalles de cada evaluación (PDF firmado, comentarios, etc.) se puede implementar aquí.</p>
                <hr>
                <div>
                    <h6>Evaluación 1 (Revisor A)</h6>
                    <p><strong>Veredicto:</strong> Aceptado con Correcciones</p>
                    <p><strong>Comentarios:</strong> El marco teórico necesita más profundidad.</p>
                    <p><strong>PDF Firmado:</strong> <a href="#">ver_archivo.pdf</a></p>
                </div>
            `;
        }
    });
});
</script>