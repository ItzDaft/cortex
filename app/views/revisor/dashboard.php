<?php
$areaNombre = '';
if (isset($revisor['area_id'])) {
    $area = AreaTematica::buscarPorId($revisor['area_id']);
    if ($area && isset($area['nombre_area'])) {
        $areaNombre = $area['nombre_area'];
    }
}
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Panel de Coordinación: <?php echo htmlspecialchars($areaNombre); ?></h1>
    <p class="lead text-muted">Bienvenido. Selecciona el módulo que deseas gestionar.</p>
    
    <?php CSRFHelper::getTokenInput(); ?>
    <div id="mensaje-dashboard"></div>



    <!-- SECCIÓN: GESTIÓN DE RESÚMENES (Tareas propias) -->
    <div class="d-flex align-items-center mb-3">
        <h3 class="mb-0 me-2"><i class="bi bi-card-checklist"></i> Mis Revisiones de Resúmenes</h3>
        <span class="badge bg-secondary">Evaluación Directa</span>
    </div>
    
    <!-- Tabla: Pendientes -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Pendientes por Evaluar</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Título del Resumen</th><th>Tipo</th><th>Fecha Asignación</th><th>Acciones</th></tr></thead>
                    <tbody id="tabla-asignadas">
                        <?php if (empty($revisionesAsignadas)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No tienes revisiones de resúmenes pendientes.</td></tr>
                        <?php else: ?>
                            <?php foreach ($revisionesAsignadas as $revision): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($revision['titulo']); ?></td>
                                    <td>
                                        <?php if (str_contains($revision['autor_roles'] ?? '', 'Autor')): ?>
                                            <span class="badge bg-primary">Extenso</span>
                                        <?php elseif (str_contains($revision['autor_roles'] ?? '', 'Asistente con Cartel')): ?>
                                            <span class="badge bg-secondary">Cartel</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border">General</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($revision['fecha_asignacion'])); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>revisor/evaluar/<?php echo $revision['resumen_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Evaluar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabla: Historial -->
    <div class="card">
        <div class="card-header bg-light">
            <strong>Historial Completado</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle text-secondary">
                    <thead>
                        <tr>
                            <th>Título del Resumen</th>
                            <th>Tipo</th>
                            <th>Tu Veredicto</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($revisionesCompletadas)): ?>
                            <tr><td colspan="4" class="text-center py-3">Aún no has completado ninguna revisión.</td></tr>
                        <?php else: ?>
                            <?php foreach ($revisionesCompletadas as $revision): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($revision['titulo']); ?></td>
                                    <td>
                                        <?php if (str_contains($revision['autor_roles'] ?? '', 'Autor')): ?>
                                            <span class="badge bg-primary bg-opacity-75">Extenso</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-75">Cartel</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $veredicto = htmlspecialchars($revision['veredicto']);
                                            $badge_class = ($veredicto == 'Aceptado') ? 'bg-success' : 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $veredicto; ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($revision['fecha_revision'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>