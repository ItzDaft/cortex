<div class="container-fluid px-4 mt-4">
    <h2 class="mt-4">Supervisión de Evaluaciones</h2>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>revisor/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Evaluaciones</li>
    </ol>

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
                                    // 1. OBTENCIÓN DE DATOS
                                    $fechaAsig = $asig['fecha_asignacion'] ?? null;
                                    $estatus = $asig['estatus_evaluacion'] ?? 'Pendiente';
                                    
                                    // Normalizamos estatus para evitar errores por espacios
                                    $estatus = trim($estatus);
                                    
                                    // Definimos qué se considera "Pendiente" (el proceso está vivo)
                                    $esPendiente = in_array($estatus, ['Pendiente', 'Pendiente de Firma', 'Pendiente de Validación']);

                                    // Variables de visualización por defecto
                                    $textoFechas = '<span class="text-muted small">-</span>';
                                    $textoTiempo = '<span class="text-muted">-</span>';
                                    $claseTiempo = '';
                                    $mostrarCampana = false;

                                    // 2. LÓGICA DE TIEMPO
                                    if ($esPendiente) {
                                        if ($fechaAsig) {
                                            try {
                                                $fInicio = new DateTime($fechaAsig);
                                                $fLimite = (clone $fInicio)->modify('+15 days');
                                                $hoy = new DateTime();
                                                
                                                // Visualización de fechas
                                                $textoFechas = '<div style="font-size: 0.8rem;">
                                                                    <div class="text-muted"><i class="bi bi-calendar-check me-1"></i>' . $fInicio->format('d/m/Y') . '</div>
                                                                    <div class="fw-bold text-dark"><i class="bi bi-flag-fill me-1"></i>' . $fLimite->format('d/m/Y') . '</div>
                                                                </div>';

                                                // Cálculo de días
                                                $diff = $hoy->diff($fLimite);
                                                $dias = $diff->days;
                                                $vencido = ($diff->invert === 1); // 1 si hoy > limite

                                                if ($vencido) {
                                                    $textoTiempo = "Vencido hace <strong>{$dias} días</strong>";
                                                    $claseTiempo = 'text-danger';
                                                    $mostrarCampana = true;
                                                } else {
                                                    if ($dias == 0) {
                                                        $textoTiempo = "Vence <strong>HOY</strong>";
                                                        $claseTiempo = 'text-danger fw-bold';
                                                        $mostrarCampana = true;
                                                    } elseif ($dias <= 3) {
                                                        $textoTiempo = "Quedan <strong>{$dias} días</strong>";
                                                        $claseTiempo = 'text-danger fw-bold';
                                                        $mostrarCampana = true;
                                                    } elseif ($dias <= 7) {
                                                        $textoTiempo = "Quedan <strong>{$dias} días</strong>";
                                                        $claseTiempo = 'text-warning text-dark fw-bold';
                                                        $mostrarCampana = true;
                                                    } else {
                                                        $textoTiempo = "Quedan <strong>{$dias} días</strong>";
                                                        $claseTiempo = 'text-success fw-bold';
                                                        $mostrarCampana = false; 
                                                    }
                                                }
                                            } catch (Exception $e) {
                                                $textoTiempo = "Error formato fecha";
                                            }
                                        } else {
                                            $textoFechas = '<span class="badge bg-warning text-dark">Sin fecha</span>';
                                            $textoTiempo = '<span class="text-muted small">No se puede calcular</span>';
                                        }
                                    } else {
                                        // Si NO está pendiente (Validada, Rechazada, etc.)
                                        $textoTiempo = '<span class="badge bg-secondary">Finalizado</span>';
                                    }
                                ?>

                                <tr id="eval-row-<?php echo $asig['evaluacion_id']; ?>">
                                    
                                    <td>
                                        <div class="fw-bold text-primary text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($asig['titulo_articulo']); ?>">
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
                                        <?php echo $textoFechas; ?>
                                    </td>

                                    <td>
                                        <span class="<?php echo $claseTiempo; ?>">
                                            <?php echo $textoTiempo; ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php 
                                            $badgeClass = 'bg-secondary';
                                            $iconStatus = 'bi-hourglass-split';
                                            if ($estatus === 'Validada') { $badgeClass = 'bg-success'; $iconStatus = 'bi-check-circle-fill'; }
                                            elseif ($estatus === 'Pendiente de Firma') { $badgeClass = 'bg-info text-dark'; $iconStatus = 'bi-pen-fill'; }
                                            elseif ($estatus === 'Pendiente de Validación') { $badgeClass = 'bg-warning text-dark'; $iconStatus = 'bi-exclamation-circle-fill'; }
                                            elseif ($estatus === 'Rechazada por Coordinador') { $badgeClass = 'bg-danger'; $iconStatus = 'bi-x-circle-fill'; }
                                        ?>
                                        <div class="mb-2">
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <i class="bi <?php echo $iconStatus; ?> me-1"></i><?php echo $estatus; ?>
                                            </span>
                                        </div>

                                        <div class="btn-group w-100 shadow-sm" role="group">
                                            
                                            <?php if ($mostrarCampana): ?>
                                                <button type="button" class="btn btn-warning btn-sm text-dark btn-recordatorio"
                                                    data-id="<?php echo $asig['evaluacion_id']; ?>"
                                                    data-revisor="<?php echo htmlspecialchars($asig['nombre_revisor']); ?>"
                                                    title="Enviar recordatorio por correo">
                                                    <i class="bi bi-bell-fill"></i>
                                                </button>
                                            <?php else: ?>
                                                <button disabled class="btn btn-light btn-sm text-muted border"><i class="bi bi-bell"></i></button>
                                            <?php endif; ?>

                                            <?php if (!empty($asig['respuestas_formulario'])): ?>
                                                <button type="button" class="btn btn-outline-primary btn-sm btn-ver-dictamen" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDetalleEvaluacion"
                                                    data-titulo="<?php echo htmlspecialchars($asig['titulo_articulo']); ?>"
                                                    data-revisor="<?php echo htmlspecialchars($asig['nombre_revisor']); ?>"
                                                    data-veredicto="<?php echo htmlspecialchars($asig['veredicto']); ?>"
                                                    data-obs="<?php echo htmlspecialchars($asig['observaciones_generales'] ?? ''); ?>"
                                                    data-rechazo="<?php echo htmlspecialchars($asig['argumento_rechazo'] ?? ''); ?>"
                                                    data-respuestas='<?php echo $asig['respuestas_formulario']; ?>'
                                                    data-pdf="<?php echo htmlspecialchars($asig['pdf_firmado_ruta'] ?? ''); ?>"
                                                    title="Ver Detalle">
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
                                                <button disabled class="btn btn-light btn-sm text-muted border" title="Sin PDF firmado aún">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($estatus === 'Pendiente de Validación'): ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';

    function apiCall(url, data, onSuccess) {
        fetch(`${baseUrl}${url}`, {
            method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.error) { alert(resp.error); } 
            else { 
                alert(resp.mensaje); 
                if(onSuccess) onSuccess(); 
            }
        })
        .catch(err => { console.error(err); alert("Error de red."); });
    }

    // Botón Recordatorio
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

    // Lógica Modales
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

    // Validaciones
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
});
</script>