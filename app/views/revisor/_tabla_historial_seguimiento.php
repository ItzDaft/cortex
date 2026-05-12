<?php
/**
 * Tabla de historial de seguimiento por extenso (supervisión).
 * Variables esperadas: $historial (array), $tituloCard (string), $cardHeaderClass (string),
 * $mensajeVacioHistorial (string), $cardIconClass (string, clase Bootstrap Icons sin prefijo bi-).
 */
$historial = $historial ?? [];
$tituloCard = $tituloCard ?? 'Historial';
$cardHeaderClass = $cardHeaderClass ?? 'bg-dark text-white';
$mensajeVacioHistorial = $mensajeVacioHistorial ?? 'No hay historial de seguimiento para mostrar.';
$cardIconClass = $cardIconClass ?? 'bi-clock-history';
?>
<div class="card mb-4 shadow-sm">
    <div class="card-header <?php echo htmlspecialchars($cardHeaderClass, ENT_QUOTES, 'UTF-8'); ?>">
        <i class="bi <?php echo htmlspecialchars($cardIconClass, ENT_QUOTES, 'UTF-8'); ?> me-1"></i> <?php echo htmlspecialchars($tituloCard, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle tabla-historial-seguimiento">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" style="width: 26%;">Artículo</th>
                        <th scope="col" style="width: 20%;">Revisor</th>
                        <th scope="col" style="width: 9%;">Vuelta</th>
                        <th scope="col" style="width: 18%;">Estatus</th>
                        <th scope="col" style="width: 17%;">Comentarios</th>
                        <th scope="col" style="width: 10%;">Archivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historial)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <?php echo htmlspecialchars($mensajeVacioHistorial, ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historial as $itemHist): ?>
                            <?php
                                $filas = $itemHist['filas'] ?? [];
                                $rowspan = max(1, count($filas));
                            ?>
                            <?php foreach ($filas as $idxFila => $filaHist): ?>
                                <?php
                                    $estatusHist = (string)($filaHist['estatus'] ?? 'Pendiente');
                                    $veredictoHist = (string)($filaHist['veredicto'] ?? '');
                                    $comentarioHist = (string)($filaHist['comentario'] ?? '');

                                    $estatusClass = 'bg-secondary';
                                    if (
                                        stripos($estatusHist, 'Validada') !== false ||
                                        stripos($estatusHist, 'Entregada') !== false
                                    ) {
                                        $estatusClass = 'bg-success';
                                    } elseif (
                                        stripos($estatusHist, 'Rechazada') !== false ||
                                        stripos($estatusHist, 'No Publicable') !== false
                                    ) {
                                        $estatusClass = 'bg-danger';
                                    } elseif (
                                        stripos($estatusHist, 'Pendiente') !== false ||
                                        stripos($estatusHist, 'Proceso') !== false
                                    ) {
                                        $estatusClass = 'bg-warning text-dark';
                                    } elseif (
                                        stripos($estatusHist, 'Firma') !== false ||
                                        stripos($estatusHist, 'Validación') !== false
                                    ) {
                                        $estatusClass = 'bg-info text-dark';
                                    }

                                    $veredictoClass = 'bg-secondary';
                                    if (stripos($veredictoHist, 'Favorable y Publicable') !== false) {
                                        $veredictoClass = 'bg-success';
                                    } elseif (stripos($veredictoHist, 'Favorable con Correcciones') !== false) {
                                        $veredictoClass = 'bg-warning text-dark';
                                    } elseif (stripos($veredictoHist, 'No Publicable') !== false) {
                                        $veredictoClass = 'bg-danger';
                                    }

                                    $comentarioClass = 'text-muted';
                                    if (
                                        stripos($comentarioHist, 'rechaz') !== false ||
                                        stripos($comentarioHist, 'no publicable') !== false
                                    ) {
                                        $comentarioClass = 'text-danger fw-bold';
                                    } elseif (
                                        stripos($comentarioHist, 'retraso') !== false ||
                                        stripos($comentarioHist, 'dias') !== false
                                    ) {
                                        $comentarioClass = 'text-warning fw-bold';
                                    } elseif (!empty($comentarioHist)) {
                                        $comentarioClass = 'text-dark';
                                    }
                                ?>
                                <tr>
                                    <?php if ($idxFila === 0): ?>
                                        <td rowspan="<?php echo $rowspan; ?>" class="fw-bold text-primary col-articulo-fija">
                                            <span class="titulo-articulo-truncado" title="<?php echo htmlspecialchars($itemHist['titulo']); ?>">
                                                <?php echo htmlspecialchars($itemHist['titulo']); ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>

                                    <td>
                                        <?php if (!empty($filaHist['revisor']) && $filaHist['revisor'] !== '-'): ?>
                                            <div class="fw-semibold revisor-truncado" title="<?php echo htmlspecialchars($filaHist['revisor']); ?>">
                                                <?php echo htmlspecialchars($filaHist['revisor']); ?>
                                            </div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($filaHist['correo']); ?></div>
                                        <?php else: ?>
                                            <span class="text-muted"><?php echo htmlspecialchars($filaHist['revisor']); ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge <?php echo ($filaHist['archivo_tipo'] ?? 'extensos') === 'extensos_finales' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo htmlspecialchars($filaHist['vuelta'] ?? 'Rev?'); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div>
                                            <span class="badge <?php echo $estatusClass; ?>">
                                                <?php echo htmlspecialchars($estatusHist); ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($veredictoHist) && $veredictoHist !== 'Pendiente'): ?>
                                            <div class="small mt-1">
                                                <span class="badge <?php echo $veredictoClass; ?>">
                                                    <?php echo htmlspecialchars($veredictoHist); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($comentarioHist)): ?>
                                            <?php
                                                $preview = mb_substr($comentarioHist, 0, 55, 'UTF-8');
                                                if (mb_strlen($comentarioHist, 'UTF-8') > 55) {
                                                    $preview .= '...';
                                                }
                                            ?>
                                            <div class="small <?php echo $comentarioClass; ?>">
                                                <?php echo htmlspecialchars($preview); ?>
                                            </div>
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btn-sm mt-1 btn-ver-comentario-historial"
                                                data-comentario="<?php echo htmlspecialchars($comentarioHist, ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                                Ver mas
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin comentarios</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($filaHist['archivo_ruta'])): ?>
                                            <?php if (($filaHist['archivo_tipo'] ?? 'extensos') === 'extensos_finales'): ?>
                                                <a href="<?php echo BASE_URL; ?>archivo/ver/extensos_finales/<?php echo $filaHist['archivo_ruta']; ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-file-earmark-check"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $filaHist['archivo_ruta']; ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
