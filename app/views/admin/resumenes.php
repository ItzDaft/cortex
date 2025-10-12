<h1 class="mb-4">Gestión General de Resúmenes</h1>
<?php CSRFHelper::getTokenInput(); ?>
<div id="mensaje-resumenes"></div>

<div class="mb-4">
    <input type="text" id="buscador-resumenes" class="form-control" placeholder="Buscar por título, autor o área...">
</div>

<?php
$conteoEstatus = [
    'Aceptado' => 0,
    'Rechazado' => 0,
    'En Revision' => 0,
    'Pendiente de Asignacion' => 0
];
foreach ($resumenesPorArea as $resumenesArea) {
    foreach ($resumenesArea as $res) {
        if (isset($conteoEstatus[$res['estatus']])) {
            $conteoEstatus[$res['estatus']]++;
        }
    }
}
?>
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Total de Resúmenes</h5>
                <p class="card-text fs-4"><?php echo $estadisticasResumenes['Total'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-6 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Aceptados</h5>
                <p class="card-text fs-4"><?php echo $conteoEstatus['Aceptado']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-6 mb-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Rechazados</h5>
                <p class="card-text fs-4"><?php echo $conteoEstatus['Rechazado']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-6 mb-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <h5 class="card-title">En Revisión</h5>
                <p class="card-text fs-4"><?php echo $conteoEstatus['En Revision']; ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Pendientes de Asignación</h5>
                <p class="card-text fs-4"><?php echo $conteoEstatus['Pendiente de Asignacion']; ?></p>
            </div>
        </div>
    </div>

    <!-- Contadores por área -->
    <?php foreach($estadisticasResumenes as $area => $total): ?>
        <?php if ($area !== 'Total'): ?>
        <div class="col-lg-2 col-md-4 mb-4">
            <div class="card bg-light h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($area); ?></h5>
                    <p class="card-text fs-5"><?php echo $total; ?> resumenes</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<?php
foreach ($areas as $area):
    $areaNombre = $area['nombre_area'];
    $listaResumenes = $resumenesPorArea[$areaNombre] ?? [];
?>

<h3 class="mt-5"><?php echo htmlspecialchars($areaNombre); ?></h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>#</th><th>ID</th><th>Título</th><th>Autor</th><th>Estatus</th><th>Revisores</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($listaResumenes)): ?>
                        <tr><td colspan="7" class="text-center">No hay resúmenes en esta área.</td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($listaResumenes as $resumen): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo $resumen['id']; ?></td>
                                <td>
                                    <a href="#" class="btn-ver-detalles" data-bs-toggle="modal" data-bs-target="#detalleResumenModal" data-id="<?php echo $resumen['id']; ?>">
                                        <?php echo htmlspecialchars($resumen['titulo']); ?>
                                    </a>
                                </td>                                <td><?php echo htmlspecialchars($resumen['autor_nombre']); ?></td>
                                <td>
                                    <?php 
                                        $estatus = htmlspecialchars($resumen['estatus']);
                                        $badge_class = 'bg-secondary';
                                        if ($estatus == 'Aceptado') $badge_class = 'bg-success';
                                        if ($estatus == 'Rechazado') $badge_class = 'bg-danger';
                                        if ($estatus == 'En Revision') $badge_class = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $estatus; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($resumen['revisores_asignados'] ?? 'Ninguno'); ?></td>
                                <td>
                                    <?php if ($resumen['estatus'] != 'Aceptado'): ?>
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
<div class="modal fade" id="detalleResumenModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Details of the Summary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detalleResumenBody">
        <div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

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
                const titulo = row.children[2]?.textContent.toLowerCase() || '';
                const autor = row.children[3]?.textContent.toLowerCase() || '';
                const area = '';
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
    const cuerpoDocumento = document.body;
    const modificarAreaModal = new bootstrap.Modal(document.getElementById('modificarAreaModal'));
    const verEvaluacionesModal = new bootstrap.Modal(document.getElementById('verEvaluacionesModal'));
    const modificarAreaForm = document.getElementById('modificarAreaForm');
    const detalleResumenModal = new bootstrap.Modal(document.getElementById('detalleResumenModal'));

    cuerpoDocumento.addEventListener('click', function(event) {
        const target = event.target;
        if (target.classList.contains('btn-ver-detalles')) {
            event.preventDefault();
            const resumenId = target.getAttribute('data-id');
            const modalBody = document.getElementById('detalleResumenBody');
            modalBody.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';
            detalleResumenModal.show();
            fetch(`${baseUrl}administrador/obtenerResumenDetalles/${resumenId}`)
                .then(res => res.json())
                .then(data => {
                    const tipoResumen = data.autor_roles && data.autor_roles.includes('Autor') ? 'Extenso' : 'Póster';
                    let keywordsHTML = data.palabras_clave ? data.palabras_clave.split(',').map(k => `<span class="badge bg-secondary me-1">${k.trim()}</span>`).join(' ') : 'Not specified';
                    modalBody.innerHTML = `
                        <h4>${data.titulo}</h4>
                        <p><strong>Type:</strong> ${tipoResumen}</p>
                        <hr>
                        <p><strong>Main Author:</strong> ${data.autor_principal}</p>
                        <p><strong>Co-authors:</strong> ${data.coautores || 'N/A'}</p>
                        <p><strong>Affiliation 1:</strong> ${data.adscripcion1}</p>
                        <p><strong>Affiliation 2:</strong> ${data.adscripcion2 || 'N/A'}</p>
                        <hr>
                        <p style="white-space: pre-wrap;">${data.resumen_texto}</p>
                        <hr>
                        <p><strong>Keywords:</strong> ${keywordsHTML}</p>
                    `;
                });
            return;
        }
        // Modificar área
        if (target.classList.contains('btn-editar-area')) {
            const resumenId = target.getAttribute('data-id');
            const areaIdActual = target.getAttribute('data-area-id');
            document.getElementById('edit_resumen_id').value = resumenId;
            document.getElementById('area_id_select').value = areaIdActual;
            modificarAreaModal.show();
            return;
        }
        // Ver evaluaciones
        if (target.classList.contains('btn-ver-evaluaciones')) {
            const resumenId = target.getAttribute('data-id');
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
            return;
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