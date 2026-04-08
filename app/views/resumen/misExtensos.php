<h1 class="mb-4">Mis Artículos Extensos</h1>
<p class="lead">Gestiona el envío y las correcciones de tus artículos aceptados.</p>

<div class="accordion mt-4" id="accordionExtensos">
    <?php if (empty($extensosConDetalles)): ?>
        <div class="alert alert-info">Aún no tienes artículos extensos para gestionar. Esta sección se activará cuando uno de tus resúmenes de extenso sea aceptado y su pago sea aprobado.</div>
    <?php else: ?>
        <?php foreach ($extensosConDetalles as $index => $extenso): 
            $isRechazadoPorPares = ($extenso['estatus_extenso'] == 'Rechazado' && !empty($extenso['evaluaciones']));
            $isRechazadoPorCoordinador = (($extenso['estatus_extenso'] == 'Rechazado' && empty($extenso['evaluaciones'])) || $extenso['estatus_extenso'] == 'Rechazado por Formato');
            $isEvaluacionFinalizada = in_array($extenso['estatus_extenso'], ['Aceptado Final', 'Aceptado con Correcciones', 'Rechazado']);
        ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-<?php echo $extenso['id']; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $extenso['id']; ?>">
                        <strong><?php echo htmlspecialchars($extenso['titulo']); ?></strong>
                        <span class="ms-auto badge bg-primary"><?php echo htmlspecialchars($extenso['estatus_extenso']); ?></span>
                    </button>
                </h2>
                <div id="collapse-<?php echo $extenso['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExtensos">
                    <div class="accordion-body">
                        
                        <?php if ($isRechazadoPorPares): ?>
                            <div class="alert alert-danger">
                                <p>Estimado/a autor/a:</p>
                                <p>Después de un proceso de evaluación por pares, le informamos que su manuscrito no ha sido aceptado para su publicación.</p>
                                <p>De acuerdo con los dictámenes emitidos por los revisores, el documento no cumple con algunas de las características académicas y/o criterios editoriales requeridos por esta publicación.</p>
                                <p>Agradecemos el interés en someter su trabajo a consideración y el tiempo dedicado al proceso. Le invitamos a considerar los comentarios de los revisores para una posible mejora del manuscrito.</p>
                                <p class="mb-2">Abajo las recomendaciones de los revisores</p>
                                
                                <ul class="list-unstyled">
                                <?php 
                                    $i = 1;
                                    foreach ($extenso['evaluaciones'] as $eval): 
                                        $comentarioCompleto = trim($eval['observaciones_generales'] . "\n" . $eval['argumento_rechazo']);
                                        if (!empty($comentarioCompleto)):
                                ?>
                                    <li class="mb-2"><strong>Revisor <?php echo $i++; ?>:</strong><br> <?php echo nl2br(htmlspecialchars($comentarioCompleto)); ?></li>
                                <?php else: $i++; endif; endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <?php 
                                $comentariosFormato = !empty($extenso['comentarios_formato']);
                                $comentariosRevisores = !empty($extenso['evaluaciones']) && $isEvaluacionFinalizada;
                                
                                if ($comentariosFormato || $comentariosRevisores): 
                            ?>
                                <div class="alert alert-warning">
                                    <h5 class="alert-heading">Observaciones</h5>
                                    <?php if ($comentariosFormato): ?>
                                        <p><strong>Comentarios del Coordinador de Área (Formato):</strong> <?php echo nl2br(htmlspecialchars($extenso['comentarios_formato'])); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($comentariosRevisores): ?>
                                        <p class="mt-2"><strong>Comentarios de los Revisores:</strong></p>
                                        <ul>
                                        <?php foreach ($extenso['evaluaciones'] as $eval): 
                                            $comentarioCompleto = trim($eval['observaciones_generales'] . "\n" . $eval['argumento_rechazo']);
                                            if (!empty($comentarioCompleto)):
                                        ?>
                                            <li><?php echo nl2br(htmlspecialchars($comentarioCompleto)); ?></li>
                                        <?php endif; endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="mb-3">
                            <?php if ($extenso['estatus_extenso'] == 'No Enviado'): ?>
                                <a href="<?php echo BASE_URL; ?>extenso/enviar/<?php echo $extenso['id']; ?>" class="btn btn-success"><i class="bi bi-file-earmark-arrow-up-fill"></i> Enviar 1ª Versión</a>
                            <?php elseif ($extenso['estatus_extenso'] == 'Aceptado con Correcciones' || $isRechazadoPorCoordinador): ?>
                                <?php if (count($extenso['versiones']) < 5): // Limita a 5 envíos totales ?>
                                    <a href="<?php echo BASE_URL; ?>extenso/reenviar/<?php echo $extenso['id']; ?>" class="btn btn-warning"><i class="bi bi-pencil-fill"></i> Subir Nueva Versión</a>
                                <?php else: ?>
                                    <p class="text-danger">Ya has alcanzado el número máximo de envíos para este artículo.</p>
                                <?php endif; ?>
                            <?php elseif ($extenso['estatus_extenso'] == 'Aceptado Final'): ?>
                                <a href="<?php echo BASE_URL; ?>extenso/subirFinal/<?php echo $extenso['id']; ?>" class="btn btn-primary">
                                    <i class="bi bi-journal-check"></i>
                                    <?php echo !empty($extenso['version_final']) ? 'Reemplazar versión final (PDF revista)' : 'Subir versión final (PDF revista)'; ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <h5>Historial de Envíos</h5>
                        <ul class="list-group">
                            <?php foreach ($extenso['versiones'] as $version): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Versión <?php echo $version['intento']; ?> (Enviada el <?php echo date('d/m/Y', strtotime($version['fecha_envio'])); ?>)
                                <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $version['archivo_ruta']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download"></i> Descargar
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if ($extenso['estatus_extenso'] == 'Aceptado Final'): ?>
                            <h5 class="mt-4">Versión final para la revista (PDF)</h5>
                            <?php if (!empty($extenso['version_final'])): ?>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <span>PDF enviado el <?php echo date('d/m/Y H:i', strtotime($extenso['version_final']['fecha_envio'])); ?></span>
                                        <a href="<?php echo BASE_URL; ?>archivo/ver/extensos_finales/<?php echo htmlspecialchars($extenso['version_final']['archivo_ruta']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-download"></i> Descargar
                                        </a>
                                    </li>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted mb-0">Aún no has subido el PDF final que se enviará a la revista. Usa el botón de arriba cuando esté listo.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>