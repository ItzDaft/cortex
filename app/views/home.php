<style>
    .action-card { transition: all 0.2s ease-in-out; }
    .action-card:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important; }
    .action-card .card-title i { font-size: 2.5rem; }
</style>

<div class="container my-5">

    <?php if (!isset($_SESSION['usuario_id'])): ?>
        <div class="text-center bg-light p-5 rounded">
            <h1 class="display-4 fw-bold">Bienvenido al Sistema Cortex</h1>
            <p class="lead my-4">La plataforma para la gestión de resúmenes del CCTI 2025.</p>
            <a href="<?php echo BASE_URL; ?>usuario/login" class="btn btn-primary btn-lg me-2"><i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión</a>
            <a href="<?php echo BASE_URL; ?>usuario/registrar" class="btn btn-secondary btn-lg"><i class="bi bi-person-plus me-2"></i>Registrarse</a>
        </div>

    <?php else: ?>
        <div class="text-center mb-5">
            <h1 class="display-5">Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>.</h1>
            <p class="lead text-muted">Selecciona una acción para continuar.</p>
        </div>

        <div class="row g-4 justify-content-center">

            <?php if (!in_array('Coordinador de Area', $_SESSION['usuario_roles'])): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm action-card">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title"><i class="bi bi-person-circle text-info"></i><br>Mi Perfil</h5>
                        <p class="card-text flex-grow-1">Actualiza tu información personal y gestiona la seguridad de tu cuenta.</p>
                        <a href="<?php echo BASE_URL; ?>usuario/perfil" class="btn btn-info mt-auto">Editar mi Perfil</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (in_array('Autor', $_SESSION['usuario_roles']) || in_array('Asistente con Cartel', $_SESSION['usuario_roles'])): ?>

                <div class="card h-100 shadow-sm action-card">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title"><i class="bi bi-file-earmark-text text-primary"></i><br>Mis Resúmenes</h5>
                        <p class="card-text flex-grow-1">Gestiona tus envíos, revisa su estatus y realiza correcciones.</p>
                        <a href="<?php echo BASE_URL; ?>resumen/misResumenes" class="btn btn-outline-primary mt-auto">Ver mis Resúmenes</a>
                    <!--    <a href="<?php echo BASE_URL; ?>resumen/crear" class="btn btn-primary mt-2">Enviar Nuevo Resumen</a> -->
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (in_array('Autor', $_SESSION['usuario_roles'])): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm action-card">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title"><i class="bi bi-journal-check text-success"></i><br>Mis Artículos Extensos</h5>
                        <p class="card-text flex-grow-1">Sube y gestiona las versiones de tus artículos aceptados y pagados.</p>
                        <a href="<?php echo BASE_URL; ?>resumen/misExtensos" class="btn btn-success mt-auto">Gestionar Extensos</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (in_array('Coordinador de Area', $_SESSION['usuario_roles'])): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm action-card">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title"><i class="bi bi-person-workspace text-info"></i><br>Gestión de Extensos</h5>
                        <p class="card-text flex-grow-1">Administra los artículos extensos de tu área, asigna revisores y valida el formato de los envíos.</p>
                        <a href="<?php echo BASE_URL; ?>revisor/gestionExtensos" class="btn btn-info text-white mt-auto">Administrar Extensos</a>
                    </div>
                </div>
            </div>
                            <!-- MÓDULOS DE GESTIÓN (ACCESOS RÁPIDOS) -->
    <div class="row mt-4 mb-5">
        <!-- Módulo 1: Artículos Extensos -->
        <div class="col-md-6 mb-3">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3 text-primary"><i class="bi bi-journal-text fs-1"></i></div>
                    <h4 class="card-title">Gestión de Extensos</h4>
                    <p class="card-text text-muted">Filtra formatos, asigna revisores, valida firmas y resuelve conflictos.</p>
                    <a href="<?php echo BASE_URL; ?>revisor/gestionExtensos" class="btn btn-primary stretched-link">
                        Ir a Extensos <i class="bi bi-arrow-right-circle ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Módulo 2: Revisores de Extenso -->
        <div class="col-md-6 mb-3">
            <div class="card h-100 border-info shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3 text-info"><i class="bi bi-people-fill fs-1"></i></div>
                    <h4 class="card-title">Directorio de Revisores</h4>
                    <p class="card-text text-muted">Consulta la carga de trabajo, perfiles y disponibilidad de tu equipo.</p>
                    <a href="<?php echo BASE_URL; ?>revisor/gestionRevisores" class="btn btn-info text-white stretched-link">
                        Ver Revisores <i class="bi bi-arrow-right-circle ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">
            <?php endif; ?>
             <?php if (in_array('Revisor de Extensos', $_SESSION['usuario_roles'])): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm action-card">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title"><i class="bi bi-journal-check text-primary"></i><br>Panel de Evaluación</h5>
                        <p class="card-text flex-grow-1">Accede para ver los artículos extensos que te han sido asignados.</p>
                        <a href="<?php echo BASE_URL; ?>revisorExtensos/dashboard" class="btn btn-primary mt-auto">Ir a mi Panel</a>
                    </div>
                </div>
            </div>

            <?php endif; ?>

                <?php if (in_array('Autor', $_SESSION['usuario_roles']) || in_array('Asistente', $_SESSION['usuario_roles']) || in_array('Asistente con Cartel', $_SESSION['usuario_roles'])): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm action-card">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title"><i class="bi bi-credit-card text-success"></i><br>Pagos</h4>
                        <p class="card-text flex-grow-1">Consulta tu historial de transacciones y sube tus comprobantes.</p>
                        <?php
+                        $usuarioTienePagos = Pago::usuarioTienePagos($_SESSION['usuario_id']);
                        if (in_array('Asistente', $_SESSION['usuario_roles']) || $usuarioTienePagos):
                        ?>
                            <a href="<?php echo BASE_URL; ?>pago" class="btn btn-success mt-auto">Realizar / Ver Pagos</a>
                        <?php else: ?>
                            <span class="btn btn-success mt-auto disabled" style="cursor: help;" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="top" data-bs-content="Esta opción se activará cuando uno de tus resúmenes sea aceptado.">
                                Realizar / Ver Pagos
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php // Menú para roles de gestión ?>
        <?php if (in_array('Administrador', $_SESSION['usuario_roles']) || in_array('Coordinador', $_SESSION['usuario_roles']) || in_array('Coordinador de Area', $_SESSION['usuario_roles']) || in_array('Revisor de Pagos', $_SESSION['usuario_roles'])): ?>
            <div class="col-12"><hr class="my-4"></div>
            <div class="col-12 text-center"><h2 class="h4">Panel de Gestión</h2></div>

            <div class="row g-3 justify-content-center">
                <?php if (in_array('Administrador', $_SESSION['usuario_roles'])): ?>
                    <div class="col-md-6 col-lg-4"><a href="<?php echo BASE_URL; ?>administrador/dashboard" class="btn btn-dark btn-lg w-100 p-3"><i class="bi bi-speedometer2 me-2"></i>Panel Admin</a></div>
                <?php endif; ?>
                <?php if (in_array('Administrador', $_SESSION['usuario_roles'])): ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo BASE_URL; ?>administrador/reportes" class="btn btn-warning btn-lg w-100 p-3">
                        <i class="bi bi-graph-up me-2"></i>Reportes y Estadísticas
                    </a>
                </div>
                <?php endif; ?>
                <?php if (in_array('Coordinador', $_SESSION['usuario_roles'])): ?>
                    <div class="col-md-6 col-lg-4"><a href="<?php echo BASE_URL; ?>coordinador/dashboard" class="btn btn-dark btn-lg w-100 p-3"><i class="bi bi-kanban me-2"></i>Panel Coordinador</a></div>
                <?php endif; ?>
                <?php if (in_array('Coordinador de Area', $_SESSION['usuario_roles'])): ?>
                    <div class="col-md-6 col-lg-4"><a href="<?php echo BASE_URL; ?>revisor/dashboard" class="btn btn-dark btn-lg w-100 p-3"><i class="bi bi-search me-2"></i>Panel de Coordinacion de Area</a></div>
                <?php endif; ?>
                <?php if (in_array('Revisor de Pagos', $_SESSION['usuario_roles']) || in_array('Administrador', $_SESSION['usuario_roles']) || in_array('Coordinador', $_SESSION['usuario_roles'])): ?>
                    <div class="col-md-6 col-lg-4"><a href="<?php echo BASE_URL; ?>revisorPagos/dashboard" class="btn btn-dark btn-lg w-100 p-3"><i class="bi bi-credit-card me-2"></i>Revisar Pagos</a></div>
                <?php endif; ?>
                <?php if (in_array('Administrador', $_SESSION['usuario_roles']) || in_array('Revisor de Pagos', $_SESSION['usuario_roles'])): ?>
                    <div class="col-md-6 col-lg-4"><a href="<?php echo BASE_URL; ?>administrador/pagos" class="btn btn-dark btn-lg w-100 p-3"><i class="bi bi-journal-album me-2"></i>Gestión General de Pagos</a></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>