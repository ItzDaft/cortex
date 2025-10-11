<h1 class="mb-4">Gestión General de Pagos</h1>

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
<h3 class="mt-4 mb-3">Desglose de Ingresos Aprobados</h3>
<div class="row">
    <?php foreach($estadisticasPorTipo as $tipo): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card bg-light h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($tipo['tipo_pago']); ?></h5>
                    <p class="card-text">
                        <span class="fs-5 fw-bold">$<?php echo number_format($tipo['total'], 2); ?></span>
                        <small class="text-muted">en <?php echo $tipo['cantidad']; ?> pagos</small>
                    </p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$ordenDeEstatus = [
    'Pendiente' => 'Pagos Pendientes de Revisión',
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
                        <th>ID Pago</th><th>Usuario</th><th>Monto</th><th>Tipo</th><th>Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaPagos)): ?>
                        <tr><td colspan="5" class="text-center">No hay pagos en este estado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listaPagos as $pago): ?>
                            <tr>
                                <td><?php echo $pago['id']; ?></td>
                                <td><?php echo htmlspecialchars($pago['nombre_completo']); ?></td>
                                <td>$<?php echo number_format($pago['monto'], 2); ?></td>
                                <td><?php echo htmlspecialchars($pago['tipo_pago']); ?></td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador-pagos');
    if (buscador) {
        buscador.addEventListener('input', function() {
            const filtro = this.value.trim().toLowerCase();
            document.querySelectorAll('table tbody').forEach(function(tbody) {
                tbody.querySelectorAll('tr').forEach(function(row) {
                    if (row.children.length === 1 && row.textContent.includes('No hay pagos')) {
                        row.style.display = filtro ? 'none' : '';
                        return;
                    }
                    const usuario = row.children[1]?.textContent.toLowerCase() || '';
                    if (usuario.includes(filtro)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    }
});
</script>