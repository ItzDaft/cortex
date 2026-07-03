<h1 class="mb-4">Reportes y Estadísticas</h1>

<ul class="nav nav-tabs" id="reportesTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="graficas-tab" data-bs-toggle="tab" data-bs-target="#graficas" type="button" role="tab">Estadísticas Visuales (Gráficas)</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="csv-tab" data-bs-toggle="tab" data-bs-target="#csv" type="button" role="tab">Exportar Reportes (CSV)</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="memorias-tab" data-bs-toggle="tab" data-bs-target="#memorias" type="button" role="tab">Reportes Predefinidos (Memorias)</button>
    </li>
</ul>

<div class="tab-content" id="reportesTabContent">

    <div class="tab-pane fade show active p-3" id="graficas" role="tabpanel">
        <h3 class="mt-3">Dashboard de Estadísticas</h3>
        <p class="lead text-muted">Visualiza KPIs rápidos y tendencias de ingresos. Ajusta el rango y aplica filtros.</p>

        <div class="row mb-3">
            <div class="col-md-3 mb-3">
                <label class="form-label">Desde</label>
                <input type="date" id="filtro-from" class="form-control" />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Hasta</label>
                <input type="date" id="filtro-to" class="form-control" />
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Área temática</label>
                <select id="filtro-area" class="form-select">
                    <option value="">(Todas)</option>
                    <?php foreach($areas as $area): ?>
                        <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre_area']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Tipo de Participante</label>
                <select id="filtro-participante" class="form-select">
                    <option value="">(Todos)</option>
                    <option value="Autor">Autor</option>
                    <option value="Asistente con Cartel">Asistente con Cartel</option>
                    <option value="Asistente Estudiante">Asistente Estudiante</option>
                    <option value="Asistente Profesionista">Asistente Profesionista</option>
                    <option value="Revisor">Revisor</option>
                    <option value="Revisor de Pagos">Revisor de Pagos</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end mb-3">
                <button id="btn-aplicar-filtros" class="btn btn-primary">Aplicar filtros</button>
            </div>
        </div>

        <div class="row mb-4" id="kpi-row">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body">
                        <h6 class="card-title">Total Recaudado</h6>
                        <p id="report-total-recaudado" class="card-text fs-5">$0.00 MXN</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <h6 class="card-title">En Revisión</h6>
                        <p id="report-en-revision" class="card-text fs-5">0</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title">Aprobados</h6>
                        <p id="report-aprobados" class="card-text fs-5">0</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title">Rechazados</h6>
                        <p id="report-rechazados" class="card-text fs-5">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Ingresos por mes</h5>
                    <div>
                        <button id="btn-download-chart" class="btn btn-sm btn-outline-secondary">Descargar PNG</button>
                    </div>
                </div>
                <canvas id="chart-ingresos" height="120"></canvas>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="mb-0">Detalle de Pagos</h5>
                    <div>
                        <button id="btn-export-csv" class="btn btn-sm btn-success">Exportar CSV</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="pagos-table" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID Pago</th>
                                <th>Resumen ID</th>
                                <th>ID Usuario</th>
                                <th>Usuario</th>
                                <th>Institución</th>
                                <th>Tipo Pago</th>
                                <th>Tipo Participante</th>
                                <th>Monto</th>
                                <th>Estatus</th>
                                <th>Comprobante</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <!-- DataTables -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>


        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = '<?php echo BASE_URL; ?>';
            const ctx = document.getElementById('chart-ingresos').getContext('2d');
            let ingresosChart = new Chart(ctx, {
                type: 'line',
                data: { labels: [], datasets: [{ label: 'Ingresos', data: [], backgroundColor: 'rgba(54,162,235,0.2)', borderColor: 'rgba(54,162,235,1)', fill: true }] },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });

            function renderKPIs(stats) {
                document.getElementById('report-total-recaudado').textContent = '$' + (Number(stats.total_recaudado || 0)).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' MXN';
                document.getElementById('report-en-revision').textContent = stats.en_revision ?? 0;
                document.getElementById('report-aprobados').textContent = stats.aprobados ?? 0;
                document.getElementById('report-rechazados').textContent = stats.rechazados ?? 0;
            }

            function fetchAndRender() {
                const from = document.getElementById('filtro-from').value;
                const to = document.getElementById('filtro-to').value;
                const params = new URLSearchParams();
                if (from) params.append('from', from);
                if (to) params.append('to', to);

                const area = document.getElementById('filtro-area').value;
                const participante = document.getElementById('filtro-participante').value;
                if (area) params.append('area_id', area);
                if (participante) params.append('participant_type', participante);

                fetch(baseUrl + 'administrador/estadisticasReportes?' + params.toString())
                    .then(res => res.json())
                    .then(json => {
                        if (json.error) throw new Error(json.error);
                        renderKPIs(json.stats || {});
                        const ingresos = json.ingresos_por_mes || [];
                        const labels = ingresos.map(i => i.mes);
                        const data = ingresos.map(i => Number(i.total));
                        ingresosChart.data.labels = labels;
                        ingresosChart.data.datasets[0].data = data;
                        ingresosChart.update();
                    })
                    .catch(err => console.error('Error cargando estadísticas:', err));
            }

            document.getElementById('btn-aplicar-filtros').addEventListener('click', function(){ fetchAndRender(); });

            const today = new Date();
            const prior = new Date(); prior.setMonth(prior.getMonth() - 5);
            function toISODate(d){ return d.toISOString().slice(0,10); }
            document.getElementById('filtro-to').value = toISODate(today);
            document.getElementById('filtro-from').value = toISODate(prior);
            fetchAndRender();
        });
        </script>
        </div>

        <script>
        $(document).ready(function() {
        const baseUrl = '<?php echo BASE_URL; ?>';
        const table = $('#pagos-table').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
            url: '<?php echo rtrim(BASE_URL, "/"); ?>/administrador/listarPagosReportes',
                    type: 'GET',
                    data: function(d) {
                            d.from = $('#filtro-from').val();
                            d.to = $('#filtro-to').val();
                            d.area_id = $('#filtro-area').val();
                            d.participant_type = $('#filtro-participante').val();
                        }
                },
                columns: [
                    { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 }, { data: 4 }, { data: 5 }, { data: 6 }, { data: 7 }, { data: 8 }, { data: 9 }
                ],
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'desc']]
            });

            $('#btn-aplicar-filtros').on('click', function() {
                table.ajax.reload();
            });

                $('#btn-export-csv').on('click', function() {
                const from = $('#filtro-from').val();
                const to = $('#filtro-to').val();
                const search = table.search();
                const area = $('#filtro-area').val();
                const participant = $('#filtro-participante').val();

                const formData = new FormData();
                if (from) formData.append('from', from);
                if (to) formData.append('to', to);
                if (search) formData.append('search', search);
                if (area) formData.append('area_id', area);
                if (participant) formData.append('participant_type', participant);

                fetch('<?php echo rtrim(BASE_URL, "/"); ?>/administrador/exportarPagosReportes', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) throw new Error('Error generando CSV');
                    return res.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'reporte_pagos_detalle_<?php echo date('Y-m-d'); ?>.csv';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                })
                .catch(err => alert('No se pudo generar el CSV: ' + err.message));
            });

            $('#btn-download-chart').on('click', function() {
                // ingresosChart is attached to window in the DOMContentLoaded scope
                const chart = window.ingresosChart;
                if (!chart) {
                    alert('El gráfico aún no está listo. Intente de nuevo en un momento.');
                    return;
                }
                const url = chart.toBase64Image();
                const a = document.createElement('a');
                a.href = url;
                const date = new Date().toISOString().slice(0,10);
                a.download = 'ingresos_por_mes_' + date + '.png';
                document.body.appendChild(a);
                a.click();
                a.remove();
            });
        });
        </script>


    <div class="tab-pane fade p-3" id="csv" role="tabpanel">
        <h3 class="mt-3">Generador de Reportes CSV</h3>
        <p class="lead text-muted">Próximamente: Filtros dinámicos para exportar datos de usuarios, pagos y resúmenes.</p>
        </div>

    <div class="tab-pane fade p-3" id="memorias" role="tabpanel">
        <h3 class="mt-3">Reportes Predefinidos</h3>
        <p class="lead text-muted">Descarga de memorias para extensos con estatus "Aceptado Final".</p>
        <div class="card mb-4">
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>administrador/exportarMemoriasExtensos" method="GET" class="row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Área temática</label>
                        <select name="area_id" class="form-select">
                            <option value="">(Todas las áreas)</option>
                            <?php foreach($areas as $area): ?>
                                <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre_area']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <button type="submit" class="btn btn-primary">Descargar Memorias CSV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>