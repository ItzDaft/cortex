<h1 class="mb-4">Panel de Revisor de Artículos Extensos</h1>
<p class="lead">Aquí se listan los artículos que te han sido asignados para evaluar.</p>

<h3 class="mt-5">Mis Evaluaciones Pendientes</h3>
<div class="card mb-5">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Título del Artículo</th><th>Versión</th><th>Estatus</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($evaluacionesAsignadas)): ?>
                        <tr><td colspan="4" class="text-center">No tienes evaluaciones pendientes.</td></tr>
                    <?php else: ?>
                        <?php foreach ($evaluacionesAsignadas as $evaluacion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evaluacion['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($evaluacion['intento']); ?>ª vuelta</td>
                                <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars($evaluacion['estatus_evaluacion']); ?></span></td>
                                <td><a href="<?php echo BASE_URL; ?>revisorExtensos/evaluar/<?php echo $evaluacion['id']; ?>" class="btn btn-sm btn-primary">Evaluar / Continuar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<h3 class="mt-5">Historial de Evaluaciones</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
            <thead><tr><th>Título del Artículo</th><th>Tu Veredicto</th><th>Estatus Final</th></tr></thead>
                <tbody>
                    <?php if (empty($evaluacionesCompletadas)): ?>
                        <tr><td colspan="3" class="text-center">No has completado ninguna evaluación.</td></tr>
                    <?php else: ?>
                        <?php foreach ($evaluacionesCompletadas as $evaluacion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evaluacion['titulo']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($evaluacion['veredicto']); ?></span></td>
                                <td><span class="badge <?php echo $evaluacion['estatus_evaluacion'] == 'Validada' ? 'bg-success' : 'bg-danger'; ?>"><?php echo htmlspecialchars($evaluacion['estatus_evaluacion']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>