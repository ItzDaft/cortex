<h1 class="mb-4">Mis Artículos Extensos</h1>
<p class="lead">Desde aquí puedes gestionar el envío de tus artículos en extenso para los resúmenes que han sido aceptados y cuyo pago ha sido aprobado.</p>

<div class="card mt-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Título del Trabajo Aceptado</th>
                        <th>Estatus del Extenso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resumenesParaExtenso)): ?>
                        <tr><td colspan="3" class="text-center">Aún no tienes resúmenes con pago aprobado para enviar el artículo extenso.</td></tr>
                    <?php else: ?>
                        <?php foreach ($resumenesParaExtenso as $resumen): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($resumen['titulo']); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($resumen['estatus_extenso']); ?></span>
                                </td>
                                <td>
                                    <?php if ($resumen['estatus_extenso'] == 'No Enviado'): ?>
                                        <a href="<?php echo BASE_URL; ?>extenso/enviar/<?php echo $resumen['extenso_id']; ?>" class="btn btn-sm btn-success">
                                            <i class="bi bi-file-earmark-arrow-up-fill"></i> Enviar Extenso
                                        </a>
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