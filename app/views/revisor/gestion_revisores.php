<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1>Directorio de Revisores</h1>
            <p class="text-muted">Área: <?php echo htmlspecialchars($areaNombre); ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>revisor/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Panel
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 text-primary"><i class="bi bi-people"></i> Equipo de Revisión</h5>
                </div>
                <div class="col-auto">
                    <span class="badge bg-light text-dark border">Total: <?php echo count($revisores); ?></span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Revisor</th>
                            <th>Formación / Especialidad</th>
                            <th class="text-center">Carga Actual</th>
                            <th>Contacto</th>
                            <th class="text-end pe-4">Documentos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($revisores)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                    No hay revisores de extenso registrados en esta área.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($revisores as $rev): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($rev['foto_ruta'])): ?>
                                                <img src="<?php echo BASE_URL . 'archivo/ver/revisores_perfil/' . $rev['foto_ruta']; ?>" 
                                                     class="rounded-circle me-3 border" 
                                                     style="width: 40px; height: 40px; object-fit: cover;"
                                                     onerror="this.src='https://via.placeholder.com/40?text=<?php echo strtoupper(substr($rev['nombre_completo'], 0, 1)); ?>'">
                                            <?php else: ?>
                                                <div class="rounded-circle me-3 bg-secondary text-white d-flex align-items-center justify-content-center border" 
                                                     style="width: 40px; height: 40px; font-size: 1.2rem; font-weight: bold;">
                                                    <?php echo strtoupper(substr($rev['nombre_completo'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($rev['nombre_completo']); ?></div>
                                                <small class="text-muted">ID: <?php echo $rev['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold"><?php echo htmlspecialchars($rev['grado_academico'] ?? 'N/D'); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($rev['area_especialidad'] ?? 'Sin especialidad'); ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $carga = $rev['carga_actual'];
                                            $limite = 2; // Límite establecido por reglas de negocio
                                            $color = 'success';
                                            if ($carga == 1) $color = 'info text-dark';
                                            if ($carga >= $limite) $color = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?> rounded-pill px-3">
                                            <?php echo $carga; ?> / <?php echo $limite; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo $rev['correo']; ?>" class="text-decoration-none">
                                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($rev['correo']); ?>
                                        </a>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if (!empty($rev['comprobante_sni_ruta'])): ?>
                                            <a href="<?php echo BASE_URL . 'archivo/ver/revisores_perfil/' . $rev['comprobante_sni_ruta']; ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-info" title="Ver Comprobante SNI">
                                                <i class="bi bi-file-earmark-person"></i> SNI
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin SNI</span>
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
</div>