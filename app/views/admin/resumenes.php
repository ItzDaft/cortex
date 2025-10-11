<h1 class="mb-4">Gestión General de Resúmenes</h1>
<?php CSRFHelper::getTokenInput(); ?>
<div id="mensaje-resumenes"></div>

<div class="mb-4">
    <input type="text" id="buscador-resumenes" class="form-control" placeholder="Buscar por título, autor o área...">
</div>
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Total de Resúmenes</h5>
                <p class="card-text fs-4"><?php echo $estadisticasResumenes['Total'] ?? 0; ?></p>
            </div>
        </div>
    </div>

    <?php 
    unset($estadisticasResumenes['Total']); 
    foreach ($estadisticasResumenes as $area => $cantidad):
    ?>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-light h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($area); ?></h5>
                <p class="card-text fs-4"><?php echo $cantidad; ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php
$ordenDeEstatus = [
    'Pendiente de Asignacion' => 'Pendientes de Validación (Coordinador)',
    'En Revision' => 'En Revisión (Coordinador de Area)',
    'Aceptado' => 'Resúmenes Aceptados',
    'Rechazado' => 'Resúmenes Rechazados'
];

foreach ($ordenDeEstatus as $estatus => $titulo):
    $listaResumenes = $resumenesPorEstatus[$estatus] ?? [];
?>

<h3 class="mt-5"><?php echo $titulo; ?></h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>ID</th><th>Título</th><th>Autor</th><th>Área</th><th>Revisores</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($listaResumenes)): ?>
                        <tr><td colspan="6" class="text-center">No hay resúmenes en este estado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listaResumenes as $resumen): ?>
                            <tr>
                                <td><?php echo $resumen['id']; ?></td>
                                <td><?php echo htmlspecialchars($resumen['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($resumen['autor_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($resumen['nombre_area']); ?></td>
                                <td><?php echo htmlspecialchars($resumen['revisores_asignados'] ?? 'Ninguno'); ?></td>
                                <td>
                                    <?php if ($resumen['estatus'] != 'Aceptado' && $resumen['estatus'] != 'En Revision'): ?>
                                        <button class="btn btn-sm btn-info btn-editar-area" data-id="<?php echo $resumen['id']; ?>" data-area-id="<?php echo $resumen['area_id']; ?>">Modificar Área</button>
                                    <?php endif; ?>
                                    <?php if ($resumen['estatus'] == 'Aceptado' || $resumen['estatus'] == 'Rechazado'): ?>
                                        <button class="btn btn-sm btn-primary btn-ver-evaluaciones" data-id="<?php echo $resumen['id']; ?>">Ver Evaluaciones</button>
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

<?php endforeach; ?>

<div class="modal fade" id="modificarAreaModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Modificar Área Temática</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <form id="modificarAreaForm">
        <input type="hidden" id="edit_resumen_id">
        <div class="mb-3"><label for="area_id_select" class="form-label">Nueva Área:</label>
          <select id="area_id_select" class="form-select">
            <?php foreach($areas as $area): ?><option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre_area']); ?></option><?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="modificarAreaForm" class="btn btn-primary">Guardar</button></div>
  </div></div>
</div>

<div class="modal fade" id="verEvaluacionesModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Detalle de Evaluaciones</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="evaluaciones-body"></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
  </div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador-resumenes');
    buscador.addEventListener('input', function() {
        const filtro = this.value.trim().toLowerCase();
        document.querySelectorAll('table tbody').forEach(function(tbody) {
            tbody.querySelectorAll('tr').forEach(function(row) {
                if (row.children.length === 1 && row.textContent.includes('No hay resúmenes')) {
                    row.style.display = filtro ? 'none' : '';
                    return;
                }
                const titulo = row.children[1]?.textContent.toLowerCase() || '';
                const autor = row.children[2]?.textContent.toLowerCase() || '';
                const area = row.children[3]?.textContent.toLowerCase() || '';
                if (titulo.includes(filtro) || autor.includes(filtro) || area.includes(filtro)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
    const cuerpoDocumento = document.body;    const modificarAreaModal = new bootstrap.Modal(document.getElementById('modificarAreaModal'));
    const verEvaluacionesModal = new bootstrap.Modal(document.getElementById('verEvaluacionesModal'));
    const modificarAreaForm = document.getElementById('modificarAreaForm');

    cuerpoDocumento.addEventListener('click', function(event) {
        const target = event.target.closest('.btn-editar-area, .btn-ver-evaluaciones');
        if (!target) return;
        const resumenId = target.getAttribute('data-id');

        if (target.classList.contains('btn-editar-area')) {
            const areaIdActual = target.getAttribute('data-area-id');
            document.getElementById('edit_resumen_id').value = resumenId;
            document.getElementById('area_id_select').value = areaIdActual;
            modificarAreaModal.show();
        }

        if (target.classList.contains('btn-ver-evaluaciones')) {
            fetch(`${baseUrl}administrador/obtenerRevisionesResumen/${resumenId}`)
                .then(res => res.json())
                .then(revisiones => {
                    const container = document.getElementById('evaluaciones-body');
                    container.innerHTML = '';
                    if(revisiones.length === 0) {
                        container.innerHTML = '<p>No se encontraron evaluaciones para este resumen.</p>';
                    } else {
                        revisiones.forEach(rev => {
                            const veredictoClass = rev.veredicto === 'Aceptado' ? 'text-success' : 'text-danger';
                            container.innerHTML += `<div><h5>Coordinador: ${rev.revisor_nombre}</h5><p><strong>Veredicto: <span class="${veredictoClass}">${rev.veredicto}</span></strong></p><p><strong>Comentarios:</strong> ${rev.comentarios || '<em>Sin comentarios.</em>'}</p><hr></div>`;
                        });
                    }
                    verEvaluacionesModal.show();
                });
        }
    });

    modificarAreaForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const resumenId = document.getElementById('edit_resumen_id').value;
        const areaId = document.getElementById('area_id_select').value;
        
        fetch(`${baseUrl}administrador/actualizarAreaResumen/${resumenId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ area_id: areaId, csrf_token: csrfToken })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.mensaje || data.error);
            modificarAreaModal.hide();
            location.reload();
        });
    });
});
</script>