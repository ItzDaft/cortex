<h1 class="mb-4">Panel de Revisor de Artículos Extensos</h1>
<p class="lead">Gestiona tus evaluaciones asignadas, pendientes de firma e historial.</p>

<!-- TABLA 1: POR EVALUAR -->
<h3 class="mt-4 text-primary">1. Por Evaluar</h3>
<p class="text-muted">Artículos pendientes de revisión. Llena el formulario y emite tu veredicto.</p>
<div class="card mb-5 border-primary">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Título del Artículo</th><th>Versión</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($evaluacionesPorEvaluar)): ?>
                        <tr><td colspan="3" class="text-center text-muted">No tienes artículos pendientes por evaluar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($evaluacionesPorEvaluar as $evaluacion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evaluacion['titulo']); ?></td>
                                <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($evaluacion['intento']); ?>ª Versión</span></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>revisorExtensos/evaluar/<?php echo $evaluacion['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil-square"></i> Realizar Evaluación
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

<!-- TABLA 2: POR FIRMAR -->
<h3 class="mt-4 text-warning">2. Por Firmar PDF</h3>
<p class="text-muted">Artículos aprobados ("Favorable y Publicable") que requieren tu firma autógrafa.</p>
<div class="card mb-5 border-warning">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Título del Artículo</th><th>Estatus</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($evaluacionesPorFirmar)): ?>
                        <tr><td colspan="3" class="text-center text-muted">No tienes evaluaciones pendientes de firma.</td></tr>
                    <?php else: ?>
                        <?php foreach ($evaluacionesPorFirmar as $evaluacion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evaluacion['titulo']); ?></td>
                                <td><span class="badge bg-warning text-dark">Pendiente de Firma</span></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>revisorExtensos/evaluar/<?php echo $evaluacion['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-file-earmark-pdf"></i> Subir PDF Firmado
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

<!-- TABLA 3: HISTORIAL -->
<h3 class="mt-4 text-secondary">3. Historial de Evaluaciones</h3>
<p class="text-muted">Registro de tus evaluaciones finalizadas.</p>
<div class="card mb-5">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
            <thead><tr><th>Título del Artículo</th><th>Tu Veredicto</th><th>Estatus</th></tr></thead>
                <tbody>
                    <?php if (empty($evaluacionesHistorial)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Aún no hay evaluaciones en el historial.</td></tr>
                    <?php else: ?>
                        <?php foreach ($evaluacionesHistorial as $evaluacion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evaluacion['titulo']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($evaluacion['veredicto']); ?></span></td>
                                <td><span class="badge bg-success">Completada</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
