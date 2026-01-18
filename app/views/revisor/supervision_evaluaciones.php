<div class="container-fluid px-4 mt-4">
    <h2 class="mt-4">Evaluacion de extensosgit</h2>
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
                // --- LÓGICA DE SEMÁFORO (Tu lógica actual, intacta) ---
                $fechaAsig = $asig['fecha_asignacion'] ?? null;
                $textoFechas = '<span class="text-muted small">No asignado</span>';
                $textoTiempo = '-';
                $claseTiempo = '';
                $mostrarCampana = false;

                $estatus = $asig['estatus_evaluacion'] ?? 'Pendiente';
                $esPendiente = in_array($estatus, ['Pendiente', 'Pendiente de Firma', 'Pendiente de Validación']);

                if ($fechaAsig && $esPendiente) {
                    try {
                        $fInicio = new DateTime($fechaAsig);
                        $fLimite = (clone $fInicio)->modify('+15 days');
                        $hoy = new DateTime();
                        
                        $textoFechas = '<div style="font-size: 0.85rem;">
                                            <span class="text-muted"><i class="bi bi-calendar-event"></i> ' . $fInicio->format('d/m/Y') . '</span><br>
                                            <strong><i class="bi bi-flag-fill"></i> ' . $fLimite->format('d/m/Y') . '</strong>
                                        </div>';

                        $diff = $hoy->diff($fLimite);
                        $dias = $diff->days;
                        $vencido = ($diff->invert === 1);

                        if ($vencido) {
                            $textoTiempo = "Vencido hace {$dias} días";
                            $claseTiempo = 'text-danger fw-bold';
                            $mostrarCampana = true;
                        } else {
                            if ($dias == 0) {
                                $textoTiempo = "Vence HOY";
                                $claseTiempo = 'text-danger fw-bold';
                                $mostrarCampana = true;
                            } elseif ($dias <= 3) {
                                $textoTiempo = "Quedan {$dias} días";
                                $claseTiempo = 'text-danger fw-bold';
                                $mostrarCampana = true;
                            } elseif ($dias <= 7) {
                                $textoTiempo = "Quedan {$dias} días";
                                $claseTiempo = 'text-warning text-dark fw-bold';
                                $mostrarCampana = true;
                            } else {
                                $textoTiempo = "Quedan {$dias} días";
                                $claseTiempo = 'text-success';
                                $mostrarCampana = false;
                            }
                        }
                    } catch (Exception $e) { $textoTiempo = "Error fecha"; }
                } elseif (!$esPendiente) {
                    $textoTiempo = '<span class="text-muted"><i class="bi bi-check-all"></i> Completado</span>';
                }
            ?>

            <tr id="eval-row-<?php echo $asig['evaluacion_id']; ?>">
                
                <td>
                    <div class="fw-bold text-primary mb-1">
                        <?php echo htmlspecialchars($asig['titulo_articulo']); ?>
                    </div>
                    <?php if (!empty($asig['archivo_extenso_ruta'])): ?>
                        <a href="<?php echo BASE_URL; ?>archivo/ver/extensos/<?php echo $asig['archivo_extenso_ruta']; ?>" target="_blank" class="text-decoration-none text-secondary" style="font-size: 0.85rem;">
                            <i class="bi bi-file-earmark-text me-1"></i>Ver Extenso Original
                        </a>
                    <?php endif; ?>
                </td>

                <td>
                    <i class="bi bi-person-circle text-muted me-1"></i>
                    <?php echo htmlspecialchars($asig['nombre_revisor']); ?>
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
                        elseif ($estatus === 'Pendiente de Firma' || $estatus === 'Pendiente de Validación') { $badgeClass = 'bg-warning text-dark'; $iconStatus = 'bi-exclamation-circle-fill'; }
                        elseif ($estatus === 'Rechazada por Coordinador') { $badgeClass = 'bg-danger'; $iconStatus = 'bi-x-circle-fill'; }
                    ?>
                    <div class="mb-2">
                        <span class="badge <?php echo $badgeClass; ?>">
                            <i class="bi <?php echo $iconStatus; ?> me-1"></i><?php echo $estatus; ?>
                        </span>
                    </div>

                    <div class="btn-group w-100" role="group">
                        <?php if ($mostrarCampana): ?>
                            <button type="button" class="btn btn-warning btn-sm text-dark btn-recordatorio"
                                data-id="<?php echo $asig['evaluacion_id']; ?>"
                                data-revisor="<?php echo htmlspecialchars($asig['nombre_revisor']); ?>"
                                title="Enviar recordatorio">
                                <i class="bi bi-bell-fill"></i>
                            </button>
                        <?php endif; ?>

                        <?php if (!empty($asig['respuestas_formulario'])): ?>
                            <button type="button" class="btn btn-outline-info btn-sm btn-ver-dictamen" 
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

<!-- Modal para Ver Detalle de la Evaluación (Formulario) -->
<div class="modal fade" id="modalDetalleEvaluacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-clipboard-data me-2"></i>Detalle de Evaluación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="text-primary fw-bold" id="modalEvalTitulo"></h6>
                <p class="text-muted mb-4">Revisado por: <span id="modalEvalRevisor" class="fw-bold text-dark"></span></p>

                <div class="card mb-3">
                    <div class="card-header bg-light fw-bold">Respuestas del Cuestionario</div>
                    <ul class="list-group list-group-flush" id="listaRespuestasModal">
                        <!-- Se llena con JS -->
                    </ul>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Observaciones Generales:</label>
                    <div class="p-2 bg-light border rounded" id="modalEvalObs"></div>
                </div>
                
                <div id="dictamen-rechazo-container" class="d-none mb-3">
                    <p class="text-danger fw-bold">Motivo de Rechazo:</p>
                    <div id="modalEvalRechazo" class="alert alert-danger"></div>
                </div>

                <div class="alert alert-secondary text-center fw-bold" id="modalEvalVeredicto"></div>
                
                <div id="modalEvalPdfContainer" class="text-center mt-3 d-none">
                     <!-- CORRECCIÓN 3: Enlace dinámico en el modal -->
                     <a id="modalEvalPdfLink" href="#" target="_blank" class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Ver Documento Firmado
                     </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Rechazo -->
<div class="modal fade" id="rechazoCoordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Rechazar Evaluación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRechazoCoord">
                    <input type="hidden" id="idEvaluacionRechazo" name="evaluacion_id">
                    <div class="mb-3">
                        <label class="form-label">Motivo del rechazo (se enviará al revisor):</label>
                        <textarea class="form-control" id="comentarios_rechazo" name="argumento" rows="4" required placeholder="Ej: El documento no cuenta con firma autógrafa..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Confirmar Rechazo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo BASE_URL; ?>';
    const csrfToken = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';

    // Helper API Call
    function apiCall(url, data, onSuccess) {
        // data.csrf_token = csrfToken; // Descomentar si usas CSRF
        fetch(`${baseUrl}${url}`, {
            method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.error) {
                alert(resp.error);
            } else {
                alert(resp.mensaje);
                if(onSuccess) onSuccess();
            }
        })
        .catch(err => console.error(err));
    }
    document.querySelectorAll('.btn-recordatorio').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const revisor = this.dataset.revisor;
            
            // Confirmación UX
            if(confirm(`¿Desea enviar un correo de recordatorio a ${revisor}?\n\nEl sistema calculará los días restantes y enviará el aviso automáticamente.`)) {
                // Deshabilitar botón temporalmente para evitar doble click
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                
                apiCall('revisor/enviarRecordatorio', { evaluacion_id: id }, () => {
                    // Restaurar botón tras éxito
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-bell-fill"></i>';
                });
            }
        });
    });

    // 1. Lógica Modal Detalle
    const modalDetalle = document.getElementById('modalDetalleEvaluacion');
    modalDetalle.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        const titulo = button.getAttribute('data-titulo');
        const revisor = button.getAttribute('data-revisor');
        const veredicto = button.getAttribute('data-veredicto');
        const obs = button.getAttribute('data-obs') || 'Sin observaciones.';
        const rechazo = button.getAttribute('data-rechazo');
        const pdf = button.getAttribute('data-pdf');
        const respuestasJson = button.getAttribute('data-respuestas');

        document.getElementById('modalEvalTitulo').textContent = titulo;
        document.getElementById('modalEvalRevisor').textContent = revisor;
        document.getElementById('modalEvalObs').textContent = obs;
        document.getElementById('modalEvalVeredicto').textContent = 'Veredicto: ' + veredicto;

        // Rechazo
        const divRechazo = document.getElementById('dictamen-rechazo-container');
        if(rechazo) {
            divRechazo.classList.remove('d-none');
            document.getElementById('modalEvalRechazo').textContent = rechazo;
        } else {
            divRechazo.classList.add('d-none');
        }

        // PDF en Modal - CORRECCIÓN JS
        const divPdf = document.getElementById('modalEvalPdfContainer');
        if(pdf) {
            divPdf.classList.remove('d-none');
            // Usar la ruta 'archivo/ver/evaluaciones_firmadas/'
            document.getElementById('modalEvalPdfLink').href = `${baseUrl}archivo/ver/evaluaciones_firmadas/${pdf}`;
        } else {
            divPdf.classList.add('d-none');
        }

        // Respuestas
        const lista = document.getElementById('listaRespuestasModal');
        lista.innerHTML = ''; 
        try {
            const respuestas = JSON.parse(respuestasJson);
            const preguntasTexto = {
                'pregunta_1': '1. Claridad del tema',
                'pregunta_2': '2. Fundamentación teórica',
                'pregunta_3': '3. Contenido pertinente',
                'pregunta_4': '4. Aspectos teóricos suficientes',
                'pregunta_5': '5. Aspectos metodológicos suficientes',
                'pregunta_6': '6. Hallazgos contribuyen a reflexión'
            };

            for (const [key, val] of Object.entries(respuestas)) {
                if(preguntasTexto[key]) {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center';
                    const textoRespuesta = (val === 'si') ? '<span class="badge bg-success">SÍ</span>' : 
                                           (val === 'no' ? '<span class="badge bg-danger">NO</span>' : '<span class="badge bg-secondary">-</span>');
                    li.innerHTML = `<span>${preguntasTexto[key]}</span> ${textoRespuesta}`;
                    lista.appendChild(li);
                }
            }
        } catch (e) {
            lista.innerHTML = '<li class="list-group-item text-danger">Error al cargar respuestas.</li>';
        }
    });

    // 2. Lógica Modal Rechazo
    const modalRechazoElement = document.getElementById('rechazoCoordModal');
    const modalRechazo = new bootstrap.Modal(modalRechazoElement);
    modalRechazoElement.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        document.getElementById('idEvaluacionRechazo').value = id;
    });

    // 3. Submit Rechazo
    document.getElementById('formRechazoCoord').addEventListener('submit', function(e) {
        e.preventDefault();
        apiCall('revisor/validarEvaluacionFirmada', { 
            evaluacion_id: document.getElementById('idEvaluacionRechazo').value, 
            accion: 'Rechazada por Coordinador', 
            comentarios: document.getElementById('comentarios_rechazo').value 
        }, () => {
            modalRechazo.hide();
            location.reload();
        });
    });

    // 4. Acción Aprobar Directa
    document.querySelectorAll('.btn-aprobar-eval').forEach(btn => {
        btn.addEventListener('click', function() {
            if(confirm('¿Validar esta evaluación y firma? Esto finalizará el proceso para este revisor.')) {
                apiCall('revisor/validarEvaluacionFirmada', { 
                    evaluacion_id: this.dataset.id, 
                    accion: 'Validada' 
                }, () => {
                    location.reload(); 
                });
            }
        });
    });
});
</script>