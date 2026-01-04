<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Mis Resúmenes y Artículos</h1>
    <a href="<?php echo BASE_URL; ?>resumen/crear" class="btn btn-primary"><i class="bi bi-plus-circle-fill me-2"></i>Enviar Nuevo Resumen</a>
    
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($resumenes)): ?>
            <p class="text-center">Aún no has enviado ningún resumen.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Título / Área</th>
                            <th scope="col">Estatus Resumen</th>
                            <th scope="col">Estatus Artículo Extenso</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumenes as $resumen): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($resumen['titulo']); ?>
                                    <small class="d-block text-muted"><?php echo htmlspecialchars($resumen['nombre_area']); ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $estatus_resumen = htmlspecialchars($resumen['estatus']);
                                        $badge_class_resumen = 'bg-secondary';
                                        if ($estatus_resumen == 'Aceptado') $badge_class_resumen = 'bg-success';
                                        if ($estatus_resumen == 'Rechazado') $badge_class_resumen = 'bg-danger';
                                        if ($estatus_resumen == 'En Revision') $badge_class_resumen = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?php echo $badge_class_resumen; ?>"><?php echo $estatus_resumen; ?></span>
                                </td>
                                <td>
                                    <?php if (isset($resumen['estatus_extenso'])): ?>
                                        <?php 
                                            $estatus_extenso = htmlspecialchars($resumen['estatus_extenso']);
                                            $badge_class_extenso = 'bg-secondary';
                                            if ($estatus_extenso == 'Aceptado Final') $badge_class_extenso = 'bg-success';
                                            if ($estatus_extenso == 'Rechazado') $badge_class_extenso = 'bg-danger';
                                            if ($estatus_extenso == 'Aceptado con Correcciones') $badge_class_extenso = 'bg-warning text-dark';
                                            if ($estatus_extenso == 'En Revisión' || $estatus_extenso == 'Conflicto') $badge_class_extenso = 'bg-info';
                                        ?>
                                        <span class="badge <?php echo $badge_class_extenso; ?>"><?php echo $estatus_extenso; ?></span>
                                    <?php else: ?>
                                        <small class="text-muted">N/A</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($resumen['estatus'] == 'Rechazado' && $resumen['intento_envio'] == 1): ?>
                                        <a href="<?php echo BASE_URL; ?>resumen/vistaReenviar/<?php echo $resumen['id']; ?>" class="btn btn-sm btn-info">Corregir Resumen</a>
                                    <?php endif; ?>

                                    <?php if (isset($resumen['extenso_id'])): ?>
                                        <?php if ($resumen['estatus_extenso'] == 'No Enviado' && $resumen['estatus'] == 'Aceptado'): ?>
                                       <!--     <a href="<?php echo BASE_URL; ?>extenso/enviar/<?php echo $resumen['extenso_id']; ?>" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-arrow-up-fill"></i> Enviar Extenso</a> -->
                                                <a href"https://ccti2025.fasbit.edu.mx/backend/public/resumen/misExtensos" class="btn btn-sm btn-success"><i class="bi bi-journal-text"></i> Gestionar extenso</a>
                                        <?php elseif ($resumen['estatus_extenso'] == 'Aceptado con Correcciones' || ($resumen['estatus_extenso'] == 'Rechazado' && $resumen['extenso_intento'] < 2)): ?>
                                            <a href="<?php echo BASE_URL; ?>extenso/reenviar/<?php echo $resumen['extenso_id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-fill"></i> Subir Nueva Versión</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if ($resumen['estatus'] == 'Aceptado' && isset($resumen['pago_id']) && $resumen['estatus_pago'] == 'Pendiente'): ?>
                                         <a href="<?php echo BASE_URL; ?>pago" class="btn btn-sm btn-warning">Pagar Publicación</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (isset($comentariosPorExtenso[$resumen['extenso_id']])): ?>
                            <tr class="table-light">
                                <td colspan="4">
                                    <strong class="d-block mb-2"><i class="bi bi-chat-left-text-fill"></i> Comentarios de Revisores para tu Artículo Extenso:</strong>
                                    <ul class="mb-0">
                                        <?php foreach ($comentariosPorExtenso[$resumen['extenso_id']] as $comentario): ?>
                                            <li><?php echo nl2br(htmlspecialchars($comentario)); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>