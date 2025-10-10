<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cortex-CCTI2025</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script>
        window.csrfToken = '<?php echo CSRFHelper::generateToken(); ?>';
    </script>
</head>
<body>
     <?php
    if (isset($_SESSION['usuario_id']) && in_array('Revisor de Extensos', $_SESSION['usuario_roles'])) {
        if (!Usuario::perfilRevisorEstaCompleto($_SESSION['usuario_id'])) {
            $current_url = $_GET['url'] ?? '';
            if ($current_url !== 'revisorExtensos/completarPerfil' && $current_url !== 'revisorExtensos/guardarPerfil') {
                redirect('revisorExtensos/completarPerfil');
            }
        }
    }
    ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>">
        <i class="bi bi-box-fill"></i> Cortex
    </a>
    
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <a href="<?php echo BASE_URL; ?>" class="nav-link text-light d-lg-none ms-auto me-3">
            <i class="bi bi-house-door-fill fs-4"></i>
        </a>
    <?php endif; ?>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>"><i class="bi bi-house-door me-1"></i>Inicio</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> 
                        Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                        <?php if (!in_array('Coordinador de Area', $_SESSION['usuario_roles'])): ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>usuario/perfil"><i class="bi bi-person-fill me-2"></i>Mi Perfil</a></li>
                        <?php endif; ?>
                        
                        <?php // Menú de paneles de gestión
                        if (in_array('Administrador', $_SESSION['usuario_roles'])) {
                            echo '<li><a class="dropdown-item" href="'.BASE_URL.'administrador/dashboard"><i class="bi bi-speedometer2 me-2"></i>Panel Admin</a></li>';
                        } elseif (in_array('Coordinador', $_SESSION['usuario_roles'])) {
                            echo '<li><a class="dropdown-item" href="'.BASE_URL.'coordinador/dashboard"><i class="bi bi-kanban me-2"></i>Panel Coordinador</a></li>';
                        } elseif (in_array('Coordinador de Area', $_SESSION['usuario_roles'])) {
                            echo '<li><a class="dropdown-item" href="'.BASE_URL.'revisor/dashboard"><i class="bi bi-search me-2"></i>Panel de Coordinador de Area</a></li>';
                        }
                        
                        if (in_array('Administrador', $_SESSION['usuario_roles']) || in_array('Coordinador', $_SESSION['usuario_roles']) || in_array('Revisor de Pagos', $_SESSION['usuario_roles'])) {
                            echo '<li><a class="dropdown-item" href="'.BASE_URL.'revisorPagos/dashboard"><i class="bi bi-credit-card me-2"></i>Revisar Pagos</a></li>';
                        }
                        ?>

                        <?php 
                        if (in_array('Autor', $_SESSION['usuario_roles']) || in_array('Asistente con Cartel', $_SESSION['usuario_roles'])): ?>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>resumen/misResumenes"><i class="bi bi-file-earmark-text me-2"></i>Mis Resúmenes</a></li>
                        <?php endif; ?>
                        
                        <?php
                        if (in_array('Autor', $_SESSION['usuario_roles']) || in_array('Asistente con Cartel', $_SESSION['usuario_roles']) || in_array('Asistente', $_SESSION['usuario_roles'])):

                            $usuarioTienePagos = Pago::usuarioTienePagos($_SESSION['usuario_id']);

                            if (in_array('Asistente', $_SESSION['usuario_roles']) || $usuarioTienePagos):
                        ?>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>pago"><i class="bi bi-wallet2 me-2"></i>Realizar Pago</a></li>
                        <?php else: ?>
                            <li><span class="dropdown-item disabled" style="cursor: help;" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="left" data-bs-title="Acción no disponible" data-bs-content="Esta opción se activará cuando uno de tus resúmenes sea aceptado.">
                                <i class="bi bi-wallet2 me-2"></i>Realizar Pago
                            </span></li>
                        <?php 
                            endif;
                        endif; 
                        ?>
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>usuario/logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>"><i class="bi bi-house-door me-1"></i>Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>usuario/registrar"><i class="bi bi-person-plus me-1"></i>Registrarse</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>usuario/login"><i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </div>
  </div>
</nav>

<main class="container mt-4">