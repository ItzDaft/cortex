<h1>Panel del Coordinador</h1>
<p class="lead">Resúmenes pendientes de validación de área temática.</p>

<div class="card">
    <div class="card-body">
        <?php if (empty($resumenesPendientes)): ?>
            <p class="text-center">No hay resúmenes pendientes por el momento.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Título del Resumen</th>
                            <th>Autor</th>
                            <th>Tipo</th>
                            <th>Área Enviada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumenesPendientes as $resumen): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($resumen['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($resumen['autor_nombre']); ?></td>
                                <td>
                                    <?php if (str_contains($resumen['roles'],'Autor')):?>
                                        <span class="badge bg-info text-dark">Extenso</span>
                                        <?php elseif (str_contains($resumen['roles'],'Asistente con Cartel')):?>
                                        <span class="badge bg-light text-dark">Póster</span>
                                        <?php endif;?>
                                </td>
                                <td><?php echo htmlspecialchars($resumen['nombre_area']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>coordinador/validar/<?php echo $resumen['id']; ?>" class="btn btn-sm btn-primary">
                                        Revisar y Validar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<h3 class="mt-5">Historial de Revisiones</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Título del Resumen</th>
                        <th>Autor</th>
                        <th>Tipo</th>
                        <th>Área Final</th>
                        <th>Estatus</th>
                        <th>Coordinador de Area Asignado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resumenesHistorial)): ?>
                        <tr><td colspan="5" class="text-center">Aún no hay resúmenes en el historial.</td></tr>
                    <?php else: ?>
                        <?php foreach ($resumenesHistorial as $resumen): ?>
                            <tr>
                                <td><a href="<?php echo BASE_URL; ?>coordinador/validar/<?php echo $resumen['id']; ?>"><?php echo htmlspecialchars($resumen['titulo']); ?></a></td>
                                <td><?php echo htmlspecialchars($resumen['autor_nombre']); ?></td>
                                <td>
                                    <?php if (str_contains($resumen['roles'],'Autor')):?>
                                        <span class="badge bg-info text-dark">Extenso</span>
                                        <?php elseif (str_contains($resumen['roles'],'Asistente con Cartel')):?>
                                        <span class="badge bg-light text-dark">Póster</span>
                                        <?php endif;?>
                                <td><?php echo htmlspecialchars($resumen['nombre_area']); ?></td>
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
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>