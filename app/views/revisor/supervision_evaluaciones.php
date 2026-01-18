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
                                    // --- LÓGICA ROBUSTA DE FECHAS Y ESTATUS ---
                                    
                                    // 1. Limpieza de datos
                                    $estatusRaw = $asig['estatus_evaluacion'] ?? 'Pendiente';
                                    $fechaRaw = $asig['fecha_asignacion'] ?? null;
                                    
                                    // Detectar si contiene la palabra "Pendiente" (ignora mayúsculas/espacios)
                                    $esPendiente = (stripos($estatusRaw, 'Pendiente') !== false);

                                    // Valores por defecto para visualización
                                    $htmlFechas = '<span class="text-muted small">-</span>';
                                    $htmlTiempo = '<span class="badge bg-secondary">Finalizado</span>';
                                    $claseTiempo = '';
                                    $mostrarCampana = true; // Siempre activo por defecto

                                    // 2. Cálculo SIEMPRE si hay fecha (no solo si está pendiente)
                                    if (!empty($fechaRaw)) {
                                        try {
                                            // Crear objetos de fecha
                                            $fInicio = new DateTime($fechaRaw);
                                            $fLimite = (clone $fInicio)->modify('+15 days');
                                            $hoy = new DateTime('today'); // Solo la fecha, sin hora
                                            
                                            // Renderizar columna Fechas
                                            $htmlFechas = '<div style="font-size: 0.8rem; line-height: 1.4;">
                                                            <div class="text-muted"><i class="bi bi-calendar-check me-1"></i>Asig: ' . $fInicio->format('d/m/Y') . '</div>
                                                            <div class="fw-bold text-dark"><i class="bi bi-flag-fill me-1"></i>Lim: ' . $fLimite->format('d/m/Y') . '</div>
                                                           </div>';

                                            // Calcular diferencia correcta: límite - hoy
                                            $diff = $fLimite->diff($hoy);
                                            $dias = (int)$diff->format('%R%a'); // %R da signo, %a da días

                                            // Si dias es negativo, está vencido
                                            if ($dias < 0) {
                                                // Vencido
                                                $htmlTiempo = "Vencido hace <strong>" . abs($dias) . " días</strong>";
                                                $claseTiempo = 'text-danger fw-bold';
                                                $mostrarCampana = true;
                                            } elseif ($dias == 0) {
                                                // Hoy es el último día
                                                $htmlTiempo = "Vence <strong>HOY</strong>";
                                                $claseTiempo = 'text-danger fw-bold';
                                                $mostrarCampana = true;
                                            } elseif ($dias <= 3) {
                                                // 1-3 días: rojo
                                                $htmlTiempo = "Quedan <strong>{$dias} días</strong>";
                                                $claseTiempo = 'text-danger fw-bold';
                                                $mostrarCampana = true;
                                            } elseif ($dias <= 7) {
                                                // 4-7 días: amarillo
                                                $htmlTiempo = "Quedan <strong>{$dias} días</strong>";
                                                $claseTiempo = 'text-warning text-dark fw-bold';
                                                $mostrarCampana = true;
                                            } else {
                                                // Más de 7 días: verde
                                                $htmlTiempo = "Quedan <strong>{$dias} días</strong>";
                                                $claseTiempo = 'text-success fw-bold';
                                                $mostrarCampana = true;
                                            }
                                        } catch (Exception $e) {
                                            $htmlTiempo = '<span class="text-danger small">Error Fecha</span>';
                                            error_log('Error en procesamiento de fecha: ' . $e->getMessage());
                                        }
                                    } else {
                                        $htmlFechas = '<span class="badge bg-warning text-dark">Sin fecha</span>';
                                        $htmlTiempo = '<span class="text-muted small">No calculable</span>';
                                        $mostrarCampana = false;
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';

    function apiCall(url, data, onSuccess) {
        data.csrf_token = csrfToken;
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
});
</script>