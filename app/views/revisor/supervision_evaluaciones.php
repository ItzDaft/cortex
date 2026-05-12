<div class="container-fluid px-4 mt-4">
    <h2 class="mt-4">Evaluaciones de extenso</h2>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>revisor/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Evaluaciones</li>
    </ol>


    <?php $historial = $historialSeguimiento ?? []; ?>
    <style>
        .tabla-historial-seguimiento {
            table-layout: fixed;
        }
        .tabla-historial-seguimiento th,
        .tabla-historial-seguimiento td {
            vertical-align: middle;
            font-size: 0.84rem;
            padding: 0.45rem 0.5rem;
        }
        .col-articulo-fija {
            max-width: 280px;
        }
        .titulo-articulo-truncado {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.2rem;
            max-height: 2.4rem;
        }
        .revisor-truncado {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
            display: block;
        }
    </style>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-clock-history me-1"></i> Historial de Seguimiento por Extenso
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
                                    No hay historial de seguimiento para mostrar.
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

    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-eye me-1"></i> Supervisión Detallada de Evaluaciones
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 30%;">Artículo</th>
                            <th scope="col" style="width: 20%;">Revisor</th>
                            <th scope="col" style="width: 15%;">Fechas (Asig. / Límite)</th>
                            <th scope="col" style="width: 15%;">Tiempo Restante</th>
                            <th scope="col" style="width: 20%;">Estatus / Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-validar-evaluaciones">
                        <?php if (empty($asignacionesExtensos)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No hay evaluaciones registradas en su área.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($asignacionesExtensos as $asig): ?>
                                <?php 
                                    $estatusRaw = $asig['estatus_evaluacion'] ?? 'Pendiente';
                                    $fechaRaw = $asig['fecha_asignacion'] ?? null;
                                    $enProceso = (stripos($estatusRaw, 'En Proceso') !== false);

                                    $htmlFechas = '<span class="text-muted small">-</span>';
                                    $htmlTiempo = '<span class="badge bg-secondary">Evaluado</span>';
                                    $claseTiempo = '';

                                    $fInicio = EvaluacionExtenso::fechaInicioPlazoEvaluacion($fechaRaw);
                                    $fLimite = EvaluacionExtenso::fechaLimitePlazoEvaluacion($fechaRaw);

                                    if ($fInicio !== null && $fLimite !== null) {
                                        try {
                                            $htmlFechas = '<div style="font-size: 0.8rem; line-height: 1.4;">
                                                            <div class="text-muted"><i class="bi bi-calendar-check me-1"></i>Asig: ' . $fInicio->format('d/m/Y') . '</div>
                                                            <div class="fw-bold text-dark"><i class="bi bi-flag-fill me-1"></i>Lim: ' . $fLimite->format('d/m/Y') . '</div>
                                                           </div>';

                                            if ($enProceso) {
                                                $hoy = new DateTime('today');
                                                $diff = $hoy->diff($fLimite);
                                                $dias = (int)$diff->days;
                                                $esVencido = ($diff->invert === 1);

                                                if ($esVencido) {
                                                    $htmlTiempo = "Vencido hace <strong>{$dias} días</strong>";
                                                    $claseTiempo = 'text-danger fw-bold';
                                                } elseif ($dias == 0) {
                                                    $htmlTiempo = "Vence <strong>HOY</strong>";
                                                    $claseTiempo = 'text-danger fw-bold';
                                                } elseif ($dias <= 3) {
                                                    $htmlTiempo = "Quedan <strong>{$dias} días</strong>";
                                                    $claseTiempo = 'text-danger fw-bold';
                                                } elseif ($dias <= 5) {
                                                    $htmlTiempo = "Quedan <strong>{$dias} días</strong>";
                                                    $claseTiempo = 'text-warning text-dark fw-bold';
                                                } else {
                                                    $htmlTiempo = "Quedan <strong>{$dias} días</strong>";
                                                    $claseTiempo = 'text-success fw-bold';
                                                }
                                            }
                                        } catch (Exception $e) {
                                            $htmlTiempo = '<span class="text-danger small">Error Fecha</span>';
                                            error_log('Error en procesamiento de fecha: ' . $e->getMessage());
                                        }
                                    } elseif (empty($fechaRaw)) {
                                        $htmlFechas = '<span class="badge bg-warning text-dark">Sin fecha</span>';
                                        $htmlTiempo = $enProceso
                                            ? '<span class="text-muted small">No calculable</span>'
                                            : '<span class="badge bg-secondary">Evaluado</span>';
                                    }
                                ?>

                                <tr id="eval-row-<?php echo $asig['evaluacion_id']; ?>">
                                    
                                    <td>
                                        <div class="fw-bold text-primary mb-1 text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($asig['titulo_articulo']); ?>">
                                            <?php echo htmlspecialchars($asig['titulo_articulo']); ?>
                                        </div>
                                        <?php if (!empty($asig['archivo_extenso_ruta'])): ?>
                                            <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $asig['archivo_extenso_ruta']; ?>" target="_blank" class="text-decoration-none text-secondary small">
                                                <i class="bi bi-file-earmark-text me-1"></i>Ver Extenso Original
                                            </a>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-2 text-primary">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold small"><?php echo htmlspecialchars($asig['nombre_revisor']); ?></div>
                                                <div class="text-muted small" style="font-size: 0.75rem;"><?php echo htmlspecialchars($asig['correo_revisor'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <?php echo $htmlFechas; ?>
                                    </td>

                                    <td>
                                        <span class="<?php echo $claseTiempo; ?>">
                                            <?php echo $htmlTiempo; ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="mb-2">
                                            <?php 
                                                $bgBadge = 'bg-secondary';
                                                if (stripos($estatusRaw, 'Validada') !== false) $bgBadge = 'bg-success';
                                                elseif (stripos($estatusRaw, 'Firma') !== false) $bgBadge = 'bg-info text-dark';
                                                elseif (stripos($estatusRaw, 'Validación') !== false) $bgBadge = 'bg-warning text-dark';
                                                elseif (stripos($estatusRaw, 'Rechazada') !== false) $bgBadge = 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $bgBadge; ?>">
                                                <?php echo htmlspecialchars($estatusRaw); ?>
                                            </span>
                                            <?php if (!empty($asig['veredicto']) && $asig['veredicto'] !== 'Pendiente'): ?>
                                                <?php
                                                    $bgVeredicto = 'bg-secondary';
                                                    if ($asig['veredicto'] === 'Favorable y Publicable') $bgVeredicto = 'bg-success';
                                                    elseif ($asig['veredicto'] === 'Favorable con Correcciones') $bgVeredicto = 'bg-info text-dark';
                                                    elseif ($asig['veredicto'] === 'No Publicable') $bgVeredicto = 'bg-danger';
                                                ?>
                                                <div class="mt-1">
                                                    <span class="badge <?php echo $bgVeredicto; ?>">
                                                        <?php echo htmlspecialchars($asig['veredicto']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="btn-group w-100 shadow-sm" role="group">
                                            
                                            <button type="button" class="btn btn-warning btn-sm text-dark btn-recordatorio"
                                                data-id="<?php echo $asig['evaluacion_id']; ?>"
                                                data-revisor="<?php echo htmlspecialchars($asig['nombre_revisor']); ?>"
                                                title="Enviar recordatorio al revisor">
                                                <i class="bi bi-bell-fill"></i>
                                            </button>

                                            <?php if (!empty($asig['respuestas_formulario'])): ?>
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDetalleEvaluacion"
                                                    data-titulo="<?php echo htmlspecialchars($asig['titulo_articulo']); ?>"
                                                    data-revisor="<?php echo htmlspecialchars($asig['nombre_revisor']); ?>"
                                                    data-veredicto="<?php echo htmlspecialchars($asig['veredicto']); ?>"
                                                    data-obs="<?php echo htmlspecialchars($asig['observaciones_generales'] ?? ''); ?>"
                                                    data-rechazo="<?php echo htmlspecialchars($asig['argumento_rechazo'] ?? ''); ?>"
                                                    data-respuestas='<?php echo $asig['respuestas_formulario']; ?>'
                                                    data-pdf="<?php echo htmlspecialchars($asig['pdf_firmado_ruta'] ?? ''); ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            <?php else: ?>
                                                <button disabled class="btn btn-light btn-sm text-muted border"><i class="bi bi-eye-slash"></i></button>
                                            <?php endif; ?>

                                            <?php if (!empty($asig['pdf_firmado_ruta'])): ?>
                                                <a href="<?php echo BASE_URL; ?>archivo/ver/evaluaciones_firmadas/<?php echo $asig['pdf_firmado_ruta']; ?>" 
                                                   target="_blank" 
                                                   class="btn btn-outline-danger btn-sm"
                                                   title="Ver PDF Firmado">
                                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                                </a>
                                            <?php else: ?>
                                                <button disabled class="btn btn-light btn-sm text-muted border" title="Sin archivo firmado">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if (stripos($estatusRaw, 'Pendiente de Validación') !== false): ?>
                                                <button class="btn btn-outline-success btn-sm btn-aprobar-eval" 
                                                        data-id="<?php echo $asig['evaluacion_id']; ?>" 
                                                        title="Validar">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm btn-rechazar-eval" 
                                                        data-id="<?php echo $asig['evaluacion_id']; ?>" 
                                                        data-bs-toggle="modal" data-bs-target="#rechazoCoordModal" 
                                                        title="Rechazar">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleEvaluacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalle de Evaluación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="text-primary fw-bold" id="modalEvalTitulo"></h6>
                <p class="text-muted mb-4">Revisado por: <span id="modalEvalRevisor" class="fw-bold text-dark"></span></p>
                <div class="card mb-3">
                    <ul class="list-group list-group-flush" id="listaRespuestasModal"></ul>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Observaciones:</label>
                    <div class="p-2 bg-light border rounded" id="modalEvalObs"></div>
                </div>
                <div id="dictamen-rechazo-container" class="d-none mb-3">
                    <p class="text-danger fw-bold">Motivo Rechazo:</p>
                    <div id="modalEvalRechazo" class="alert alert-danger"></div>
                </div>
                <div class="alert alert-secondary text-center fw-bold" id="modalEvalVeredicto"></div>
                <div id="modalEvalPdfContainer" class="text-center mt-3 d-none">
                     <a id="modalEvalPdfLink" href="#" target="_blank" class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Ver Documento Firmado
                     </a>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="rechazoCoordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title">Rechazar Evaluación</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="formRechazoCoord">
                    <input type="hidden" id="idEvaluacionRechazo" name="evaluacion_id">
                    <textarea class="form-control" id="comentarios_rechazo" name="argumento" rows="4" required placeholder="Motivo..."></textarea>
                    <button type="submit" class="btn btn-danger w-100 mt-3">Confirmar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalComentarioHistorial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Comentario completo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contenidoComentarioHistorial" class="small" style="white-space: pre-wrap;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

    function apiCall(url, data, onSuccess) {
        data.csrf_token = csrfToken;

        fetch(`${baseUrl}${url}`, {
            method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data)
        })
        .then(r => r.text())
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Server response not JSON:", text);
                throw new Error("Respuesta del servidor inválida.");
            }
        })
        .then(resp => {
            if (resp.error) { alert(resp.error); } 
            else { 
                alert(resp.mensaje); 
                if(onSuccess) onSuccess(); 
            }
        })
        .catch(err => { console.error(err); alert("Error de red o servidor."); });
    }

    document.querySelectorAll('.btn-recordatorio').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const revisor = this.dataset.revisor;
            if(confirm(`¿Desea enviar un correo de recordatorio a ${revisor}?`)) {
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                apiCall('revisor/enviarRecordatorio', { evaluacion_id: id }, () => {
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-bell-fill"></i>';
                });
            }
        });
    });

    const modalDetalle = document.getElementById('modalDetalleEvaluacion');
    modalDetalle.addEventListener('show.bs.modal', function (event) {
        const b = event.relatedTarget;
        document.getElementById('modalEvalTitulo').textContent = b.dataset.titulo;
        document.getElementById('modalEvalRevisor').textContent = b.dataset.revisor;
        document.getElementById('modalEvalObs').textContent = b.dataset.obs || 'Sin observaciones';
        document.getElementById('modalEvalVeredicto').textContent = 'Veredicto: ' + b.dataset.veredicto;
        
        const divRechazo = document.getElementById('dictamen-rechazo-container');
        if(b.dataset.rechazo) { divRechazo.classList.remove('d-none'); document.getElementById('modalEvalRechazo').textContent = b.dataset.rechazo; } 
        else { divRechazo.classList.add('d-none'); }

        const divPdf = document.getElementById('modalEvalPdfContainer');
        if(b.dataset.pdf) { divPdf.classList.remove('d-none'); document.getElementById('modalEvalPdfLink').href = `${baseUrl}archivo/ver/evaluaciones_firmadas/${b.dataset.pdf}`; } 
        else { divPdf.classList.add('d-none'); }

        const lista = document.getElementById('listaRespuestasModal');
        lista.innerHTML = '';
        try {
            const resp = JSON.parse(b.dataset.respuestas);
            const titulos = {'pregunta_1':'Claridad', 'pregunta_2':'Fundamentación', 'pregunta_3':'Pertinencia', 'pregunta_4':'Teoría', 'pregunta_5':'Metodología', 'pregunta_6':'Hallazgos'};
            for (const [k, v] of Object.entries(resp)) {
                if(titulos[k]) {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between';
                    li.innerHTML = `<span>${titulos[k]}</span> <span class="badge ${v==='si'?'bg-success':'bg-danger'}">${v.toUpperCase()}</span>`;
                    lista.appendChild(li);
                }
            }
        } catch(e){}
    });

    const modalRechazoEl = document.getElementById('rechazoCoordModal');
    const modalRechazo = new bootstrap.Modal(modalRechazoEl);
    modalRechazoEl.addEventListener('show.bs.modal', function(e) { document.getElementById('idEvaluacionRechazo').value = e.relatedTarget.dataset.id; });
    
    document.getElementById('formRechazoCoord').addEventListener('submit', function(e) {
        e.preventDefault();
        apiCall('revisor/validarEvaluacionFirmada', { 
            evaluacion_id: document.getElementById('idEvaluacionRechazo').value, 
            accion: 'Rechazada por Coordinador', 
            comentarios: document.getElementById('comentarios_rechazo').value 
        }, () => { modalRechazo.hide(); location.reload(); });
    });

    document.querySelectorAll('.btn-aprobar-eval').forEach(btn => {
        btn.addEventListener('click', function() {
            if(confirm('¿Validar esta evaluación?')) {
                apiCall('revisor/validarEvaluacionFirmada', { evaluacion_id: this.dataset.id, accion: 'Validada' }, () => location.reload());
            }
        });
    });

    const modalComentarioEl = document.getElementById('modalComentarioHistorial');
    const modalComentario = new bootstrap.Modal(modalComentarioEl);
    const contenidoComentario = document.getElementById('contenidoComentarioHistorial');

    document.querySelectorAll('.btn-ver-comentario-historial').forEach(btn => {
        btn.addEventListener('click', function() {
            contenidoComentario.textContent = this.dataset.comentario || 'Sin comentario';
            modalComentario.show();
        });
    });
});
</script>