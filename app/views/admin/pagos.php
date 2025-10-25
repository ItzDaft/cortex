<h1 class="mb-4">Gestión General de Pagos</h1>
<button type="button" class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#exportarPagosModal">
    <i class="bi bi-download me-2"></i>Exportar Datos de Pagos...
</button>
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Total Recaudado</h5>
                <p class="card-text fs-4">$<?php echo number_format($estadisticas['total_recaudado'] ?? 0, 2); ?> MXN</p>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6 mb-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <h5 class="card-title">Pagos en Revisión</h5>
                <p class="card-text fs-4"><?php echo $estadisticas['en_revision'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Pendientes (sin comprobante)</h5>
                <p class="card-text fs-4"><?php echo $estadisticas['pendientes_sin_comprobante'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Aprobados</h5>
                <p class="card-text fs-4"><?php echo $estadisticas['aprobados'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    <div class="col-xl col-md-6 mb-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <h5 class="card-title">Rechazados</h5>
                <p class="card-text fs-4"><?php echo $estadisticas['rechazados'] ?? 0; ?></p>
            </div>
        </div>
    </div>
</div>

<hr>
<div class="mb-4">
    <input type="text" id="buscador-pagos" class="form-control" placeholder="Buscar por nombre de usuario...">
</div>
<h3 class="mt-4 mb-3">Desglose de Ingresos por Tipo de Participante</h3>
<div class="row">
    <?php foreach($estadisticasPorRol as $rolStat): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card bg-light h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($rolStat['categoria']); ?></h5>
                    <p class="card-text">
                        <span class="fs-5 fw-bold">$<?php echo number_format($rolStat['total'], 2); ?></span>
                        <small class="text-muted">en <?php echo $rolStat['cantidad']; ?> pagos</small>
                    </p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$pendientesRevision = [];
$pendientesSinComprobante = [];
foreach (($pagosPorEstatus['Pendiente'] ?? []) as $pago) {
    if (!empty($pago['comprobante_ruta'])) {
        $pendientesRevision[] = $pago;
    } else {
        $pendientesSinComprobante[] = $pago;
    }
}
?>

<?php
function tipoParticipanteFromPago($pago) {
    $roles = $pago['roles'] ?? '';
    $monto = $pago['monto'] ?? null;

    if (is_string($roles)) {
        if (strpos($roles, 'Autor') !== false) return htmlspecialchars('Autor');
        if (strpos($roles, 'Asistente con Cartel') !== false) return htmlspecialchars('Asistente con Cartel');
        if (strpos($roles, 'Revisor') !== false) return htmlspecialchars('Revisor');
        if (strpos($roles, 'Revisor de Pagos') !== false) return htmlspecialchars('Revisor de Pagos');
    }

    if (is_numeric($monto)) {
        if ($monto == 300) return htmlspecialchars('Asistente Estudiante');
        if ($monto == 1000) return htmlspecialchars('Asistente Profesionista');
    }

    if (!empty($roles)) return htmlspecialchars($roles);
    return '-';
}
?>

<h3 class="mt-5">Pagos Pendientes de Revisión (con comprobante)</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                            <th>#</th><th>ID Pago</th><th>Resumen ID</th><th>ID Usuario</th><th>Usuario</th><th>Institución</th><th>Monto</th><th>Tipo de Participante</th><th>Comprobante</th>
                        </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendientesRevision)): ?>
                        <tr><td colspan="9" class="text-center">No hay pagos pendientes en revisión.</td></tr>
                    <?php else: ?>
                        <?php $i=1; foreach ($pendientesRevision as $pago): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo $pago['id']; ?></td>
                                <td>
                                    <?php if (!empty($pago['resumen_id'])): ?>
                                        <a href="#" class="btn-ver-detalles" data-id="<?php echo $pago['resumen_id']; ?>">
                                            <?php echo $pago['resumen_id']; ?>
                                        </a>
                                    <?php else: ?>
                                        &ndash;
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $pago['usuario_id'] ?? ''; ?></td>
                                <td><?php echo htmlspecialchars($pago['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($pago['institucion_procedencia'] ?? ''); ?></td>
                                <td>$<?php echo number_format($pago['monto'], 2); ?></td>
                                <td><?php echo tipoParticipanteFromPago($pago); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>archivo/ver/pagos/<?php echo $pago['comprobante_ruta']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        Ver Archivo
                                    </a>
                                    <button type="button" class="btn btn-sm btn-warning ms-2 btn-condonar" data-id="<?php echo $pago['id']; ?>">Condonar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<h3 class="mt-5">Pagos Pendientes (sin comprobante)</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                        <tr>
                        <th>#</th><th>ID Pago</th><th>Resumen ID</th><th>ID Usuario</th><th>Usuario</th><th>Institución</th><th>Monto</th><th>Tipo de Participante</th><th>Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendientesSinComprobante)): ?>
                        <tr><td colspan="9" class="text-center">No hay pagos pendientes sin comprobante.</td></tr>
                    <?php else: ?>
                        <?php $i=1; foreach ($pendientesSinComprobante as $pago): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo $pago['id']; ?></td>
                                <td>
                                    <?php if (!empty($pago['resumen_id'])): ?>
                                        <a href="#" class="btn-ver-detalles" data-id="<?php echo $pago['resumen_id']; ?>">
                                            <?php echo $pago['resumen_id']; ?>
                                        </a>
                                    <?php else: ?>
                                        &ndash;
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $pago['usuario_id'] ?? ''; ?></td>
                                <td><?php echo htmlspecialchars($pago['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($pago['institucion_procedencia'] ?? ''); ?></td>
                                <td>$<?php echo number_format($pago['monto'], 2); ?></td>
                                <td><?php echo tipoParticipanteFromPago($pago); ?></td>
                                <td>
                                    <small class="text-muted">N/A</small>
                                    <button type="button" class="btn btn-sm btn-warning ms-2 btn-condonar" data-id="<?php echo $pago['id']; ?>">Condonar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$ordenDeEstatus = [
    'Aprobado'  => 'Historial de Pagos Aprobados',
    'Rechazado' => 'Historial de Pagos Rechazados'
];
foreach ($ordenDeEstatus as $estatus => $titulo):
    $listaPagos = $pagosPorEstatus[$estatus] ?? [];
?>

<h3 class="mt-5"><?php echo $titulo; ?></h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th><th>ID Pago</th><th>Resumen ID</th><th>ID Usuario</th><th>Usuario</th><th>Institución</th><th>Monto</th><th>Tipo de Participante</th><th>Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaPagos)): ?>
                        <tr><td colspan="9" class="text-center">No hay pagos en este estado.</td></tr>
                    <?php else: ?>
                        <?php $i=1; foreach ($listaPagos as $pago): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo $pago['id']; ?></td>
                                <td>
                                    <?php if (!empty($pago['resumen_id'])): ?>
                                        <a href="#" class="btn-ver-detalles" data-id="<?php echo $pago['resumen_id']; ?>">
                                            <?php echo $pago['resumen_id']; ?>
                                        </a>
                                    <?php else: ?>
                                        &ndash;
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $pago['usuario_id'] ?? ''; ?></td>
                                <td><?php echo htmlspecialchars($pago['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($pago['institucion_procedencia'] ?? ''); ?></td>
                                <td>$<?php echo number_format($pago['monto'], 2); ?></td>
                                <td><?php echo tipoParticipanteFromPago($pago); ?></td>
                                <td>
                                    <?php if (!empty($pago['comprobante_ruta'])): ?>
                                        <a href="<?php echo BASE_URL; ?>archivo/ver/pagos/<?php echo $pago['comprobante_ruta']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            Ver Archivo
                                        </a>
                                    <?php else: ?>
                                        <small class="text-muted">N/A</small>
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
<div class="modal fade" id="exportarPagosModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Exportar Reporte de Pagos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Selecciona los roles que deseas incluir en el reporte CSV.</p>
        <form id="exportarPagosForm" action="<?php echo BASE_URL; ?>administrador/exportarPagos" method="POST">
            <?php echo CSRFHelper::getTokenInput(); ?>
            <input type="hidden" name="query" id="export-query" value="">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="roles[]" value="Autor" id="rol-export-autor" checked>
                <label class="form-check-label" for="rol-export-autor">Autores (Extenso)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="roles[]" value="Asistente con Cartel" id="rol-export-cartel" checked>
                <label class="form-check-label" for="rol-export-cartel">Asistentes con Cartel (Póster)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="roles[]" value="Asistente" id="rol-export-asistente" checked>
                <label class="form-check-label" for="rol-export-asistente">Asistentes (Estudiantes y Profesionistas)</label>
            </div>
            <hr>
            <label class="form-label fw-bold">Filtrar por Estatus de Pago:</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="estatus[]" value="Aprobado" id="estatus-export-aprobado" checked>
                <label class="form-check-label" for="estatus-export-aprobado">Aprobados</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="estatus[]" value="Pendiente" id="estatus-export-pendiente" checked>
                <label class="form-check-label" for="estatus-export-pendiente">Pendientes</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="estatus[]" value="Rechazado" id="estatus-export-rechazado" checked>
                <label class="form-check-label" for="estatus-export-rechazado">Rechazados</label>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="exportarPagosForm" class="btn btn-primary">Generar y Descargar CSV</button>
      </div>
    </div>
  </div>
</div>
 
    <div class="modal fade" id="detalleResumenModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle del Resumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalleResumenBody">
                    <div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador-pagos');
    if (buscador) {
        buscador.addEventListener('input', function() {
            const filtro = this.value.trim().toLowerCase();
            const mostrarTodo = filtro === '';
            document.querySelectorAll('table tbody').forEach(function(tbody) {
                tbody.querySelectorAll('tr').forEach(function(row) {
                    if (row.children.length === 1 && row.textContent.match(/no hay pagos/i)) {
                        row.style.display = mostrarTodo ? '' : 'none';
                        return;
                    }

                    const idCell = row.children[1];
                    const resumenIdCell = row.children[2];
                    const usuarioIdCell = row.children[3];
                    const nombreCell = row.children[4];
                    const institucionCell = row.children[5];
                    const tipoCell = row.children[7];

                    const idText = idCell ? idCell.textContent.trim().toLowerCase() : '';
                    const resumenIdText = resumenIdCell ? resumenIdCell.textContent.trim().toLowerCase() : '';
                    const usuarioIdText = usuarioIdCell ? usuarioIdCell.textContent.trim().toLowerCase() : '';
                    const nombreText = nombreCell ? nombreCell.textContent.trim().toLowerCase() : '';
                    const institucionText = institucionCell ? institucionCell.textContent.trim().toLowerCase() : '';
                    const tipoText = tipoCell ? tipoCell.textContent.trim().toLowerCase() : '';

                    if (mostrarTodo || idText.includes(filtro) || resumenIdText.includes(filtro) || usuarioIdText.includes(filtro) || nombreText.includes(filtro) || institucionText.includes(filtro) || tipoText.includes(filtro)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    }

    const exportForm = document.getElementById('exportarPagosForm');
    if (exportForm) {
        const exportModal = new bootstrap.Modal(document.getElementById('exportarPagosModal'));

        exportForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const currentQuery = (document.getElementById('buscador-pagos') || { value: '' }).value || '';
            document.getElementById('export-query').value = currentQuery;

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    return response.blob(); 
                }
                return response.json().then(err => { throw new Error(err.error); });
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = 'reporte_pagos_filtrado_<?php echo date('Y-m-d'); ?>.csv';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                a.remove();
                exportModal.hide(); 
            })
            .catch(error => {
                alert('Error al generar el reporte: ' + error.message);
            });
        });
    }

            const detalleModalEl = document.getElementById('detalleResumenModal');
            const detalleModal = detalleModalEl ? new bootstrap.Modal(detalleModalEl) : null;
            const detalleBody = document.getElementById('detalleResumenBody');
            const baseUrl = '<?php echo BASE_URL; ?>';

            document.body.addEventListener('click', function(event) {
                const btn = event.target.closest('.btn-ver-detalles');
                if (!btn) return;
                event.preventDefault();
                const resumenId = btn.getAttribute('data-id');
                if (!resumenId) return;

                if (detalleBody) {
                    detalleBody.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
                }
                if (detalleModal) detalleModal.show();

                fetch(`${baseUrl}administrador/obtenerResumenDetalles/${resumenId}`)
                    .then(res => res.json())
                    .then(data => {
                        const tipoResumen = data.autor_roles && data.autor_roles.includes('Autor') ? 'Extenso' : 'Póster';
                        let keywordsHTML = data.palabras_clave ? data.palabras_clave.split(',').map(k => `<span class="badge bg-secondary me-1">${k.trim()}</span>`).join(' ') : 'No especificado';
                        if (detalleBody) {
                            detalleBody.innerHTML = `
                                <h4>${data.titulo}</h4>
                                <p><strong>Tipo:</strong> ${tipoResumen}</p>
                                <hr>
                                <p><strong>Autor Principal:</strong> ${data.autor_principal}</p>
                                <p><strong>Co-autores:</strong> ${data.coautores || 'N/A'}</p>
                                <p><strong>Adscripción 1:</strong> ${data.adscripcion1 || 'N/A'}</p>
                                <p><strong>Adscripción 2:</strong> ${data.adscripcion2 || 'N/A'}</p>
                                <hr>
                                <p style="white-space: pre-wrap;">${data.resumen_texto || ''}</p>
                                <hr>
                                <p><strong>Palabras clave:</strong> ${keywordsHTML}</p>
                            `;
                        }
                    })
                    .catch(err => {
                        if (detalleBody) detalleBody.innerHTML = '<p class="text-danger">Error al cargar el detalle del resumen.</p>';
                    });
            });

                // Manejador para condonar un pago desde la tabla
                document.body.addEventListener('click', function(event) {
                    const btn = event.target.closest('.btn-condonar');
                    if (!btn) return;
                    event.preventDefault();
                    if (!confirm('¿Confirma que desea condonar este pago? Esta acción pondrá el monto a $0.00 y lo marcará como Aprobado.')) return;

                    const pagoId = btn.getAttribute('data-id');
                    if (!pagoId) return;

                    const csrfInput = document.querySelector('input[name="csrf_token"]');
                    const csrf = csrfInput ? csrfInput.value : '';

                    const fd = new FormData();
                    fd.append('pago_id', pagoId);
                    fd.append('csrf_token', csrf);

                    fetch(baseUrl + 'administrador/condonarPago', {
                        method: 'POST',
                        body: fd
                    })
                    .then(res => res.json())
                    .then(json => {
                        if (json.error) throw new Error(json.error);
                        // Actualizar la fila visualmente
                        const row = btn.closest('tr');
                        if (row) {
                            const montoCell = row.children[6];
                            const comprobanteCell = row.children[8];
                            if (montoCell) montoCell.textContent = '$0.00';
                            if (comprobanteCell) comprobanteCell.innerHTML = '<small class="text-muted">Condonado</small>';
                            btn.remove();
                        }
                        alert(json.mensaje || 'Pago condonado correctamente.');
                    })
                    .catch(err => {
                        alert('Error al condonar el pago: ' + (err.message || 'No se pudo completar la operación.'));
                    });
                });
});
</script>