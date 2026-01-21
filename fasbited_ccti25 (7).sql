-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 200.52.83.42:3306
-- Tiempo de generación: 13-01-2026 a las 20:01:45
-- Versión del servidor: 8.0.27
-- Versión de PHP: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fasbited_ccti25`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas_tematicas`
--

CREATE TABLE `areas_tematicas` (
  `id` int NOT NULL,
  `nombre_area` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `areas_tematicas`
--

INSERT INTO `areas_tematicas` (`id`, `nombre_area`, `descripcion`) VALUES
(1, 'Fibras ópticas y sus aplicaciones', 'Área dedicada a la transmisión de información y señales mediante luz, con aplicaciones en telecomunicaciones, medicina y sensores avanzados.'),
(2, 'Materiales y energía', 'Investigación orientada al desarrollo de nuevos materiales y tecnologías que mejoren la generación, almacenamiento y uso eficiente de la energía.'),
(3, 'Matemáticas', 'Espacio para el estudio de modelos, teorías y métodos que permiten analizar, explicar y resolver problemas en distintas disciplinas científicas y tecnológicas.'),
(4, 'Tecnologías inteligentes', 'Área enfocada en sistemas innovadores que integran inteligencia artificial, robótica y automatización para optimizar procesos y mejorar la vida cotidiana.'),
(5, 'Ciencias biológicas y salud', 'Investigación sobre los procesos de la vida y su aplicación en la prevención, diagnóstico y tratamiento de enfermedades para mejorar la salud y el bienestar.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluaciones_extensos`
--

CREATE TABLE `evaluaciones_extensos` (
  `id` int NOT NULL,
  `extenso_version_id` int NOT NULL,
  `revisor_id` int NOT NULL,
  `respuestas_formulario` json DEFAULT NULL,
  `observaciones_generales` text,
  `veredicto` enum('Pendiente','Favorable y Publicable','Favorable con Correcciones','No Publicable') DEFAULT 'Pendiente',
  `argumento_rechazo` text,
  `pdf_firmado_ruta` varchar(255) DEFAULT NULL,
  `estatus_evaluacion` enum('Pendiente','Pendiente de Firma','Pendiente de Validación','Validada','Rechazada por Coordinador') DEFAULT 'Pendiente',
  `comentarios_coordinador` text,
  `fecha_asignacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extensos`
--

CREATE TABLE `extensos` (
  `id` int NOT NULL,
  `resumen_id` int NOT NULL,
  `estatus_extenso` enum('No Enviado','Pendiente de Filtro','Rechazado por Formato','Pendiente de Asignación','En Revisión','Aceptado con Correcciones','Aceptado Final','Rechazado','Conflicto') DEFAULT 'No Enviado',
  `comentarios_formato` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extenso_versiones`
--

CREATE TABLE `extenso_versiones` (
  `id` int NOT NULL,
  `extenso_id` int NOT NULL,
  `intento` int NOT NULL,
  `archivo_ruta` varchar(255) NOT NULL,
  `fecha_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `resumen_id` int DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `tipo_pago` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `comprobante_ruta` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estatus_pago` enum('Pendiente','Aprobado','Rechazado') COLLATE utf8mb4_general_ci DEFAULT 'Pendiente',
  `comentarios_rechazo` text COLLATE utf8mb4_general_ci,
  `revisor_pago_id` int DEFAULT NULL,
  `fecha_carga` timestamp NULL DEFAULT NULL,
  `fecha_revision_pago` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resumenes`
--

CREATE TABLE `resumenes` (
  `id` int NOT NULL,
  `autor_id` int NOT NULL,
  `autor_principal` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `coautores` text COLLATE utf8mb4_general_ci,
  `adscripcion1` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `adscripcion2` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `resumen_texto` text COLLATE utf8mb4_general_ci NOT NULL,
  `area_id` int NOT NULL,
  `estatus` enum('Borrador','Pendiente de Asignacion','En Revision','Aceptado','Rechazado') COLLATE utf8mb4_general_ci DEFAULT 'Borrador',
  `fecha_envio` timestamp NULL DEFAULT NULL,
  `fecha_ultima_modificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `intento_envio` int DEFAULT '1',
  `palabras_clave` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `revisiones`
--

CREATE TABLE `revisiones` (
  `id` int NOT NULL,
  `resumen_id` int NOT NULL,
  `revisor_id` int NOT NULL,
  `veredicto` enum('Pendiente','Aceptado','Rechazado') COLLATE utf8mb4_general_ci DEFAULT 'Pendiente',
  `comentarios` text COLLATE utf8mb4_general_ci,
  `fecha_asignacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_revision` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `revisores_extensos_perfil`
--

CREATE TABLE `revisores_extensos_perfil` (
  `usuario_id` int NOT NULL,
  `grado_academico` varchar(100) DEFAULT NULL,
  `afiliacion_institucional` varchar(255) DEFAULT NULL,
  `cargo_actual` varchar(255) DEFAULT NULL,
  `area_especialidad` text,
  `orcid` varchar(255) DEFAULT NULL,
  `google_scholar_id` varchar(255) DEFAULT NULL,
  `comprobante_sni_ruta` varchar(255) DEFAULT NULL,
  `foto_ruta` varchar(255) DEFAULT NULL,
  `acepta_terminos` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `nombre_rol` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre_rol`) VALUES
(1, 'Administrador'),
(5, 'Asistente'),
(7, 'Asistente con Cartel'),
(4, 'Autor'),
(2, 'Coordinador'),
(3, 'Coordinador de Area'),
(8, 'Revisor de Extensos'),
(6, 'Revisor de Pagos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nombre_completo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `correo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `institucion_procedencia` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1',
  `area_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_roles`
--

CREATE TABLE `usuario_roles` (
  `usuario_id` int NOT NULL,
  `rol_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `areas_tematicas`
--
ALTER TABLE `areas_tematicas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_area` (`nombre_area`);

--
-- Indices de la tabla `evaluaciones_extensos`
--
ALTER TABLE `evaluaciones_extensos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `extenso_version_id` (`extenso_version_id`),
  ADD KEY `revisor_id` (`revisor_id`);

--
-- Indices de la tabla `extensos`
--
ALTER TABLE `extensos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resumen_id` (`resumen_id`);

--
-- Indices de la tabla `extenso_versiones`
--
ALTER TABLE `extenso_versiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `extenso_id` (`extenso_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `resumen_id` (`resumen_id`),
  ADD KEY `revisor_pago_id` (`revisor_pago_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indices de la tabla `resumenes`
--
ALTER TABLE `resumenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autor_id` (`autor_id`),
  ADD KEY `area_id` (`area_id`);

--
-- Indices de la tabla `revisiones`
--
ALTER TABLE `revisiones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resumen_id` (`resumen_id`,`revisor_id`),
  ADD KEY `revisor_id` (`revisor_id`);

--
-- Indices de la tabla `revisores_extensos_perfil`
--
ALTER TABLE `revisores_extensos_perfil`
  ADD PRIMARY KEY (`usuario_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `area_id` (`area_id`);

--
-- Indices de la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  ADD PRIMARY KEY (`usuario_id`,`rol_id`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `areas_tematicas`
--
ALTER TABLE `areas_tematicas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `evaluaciones_extensos`
--
ALTER TABLE `evaluaciones_extensos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `extensos`
--
ALTER TABLE `extensos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `extenso_versiones`
--
ALTER TABLE `extenso_versiones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resumenes`
--
ALTER TABLE `resumenes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `revisiones`
--
ALTER TABLE `revisiones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `evaluaciones_extensos`
--
ALTER TABLE `evaluaciones_extensos`
  ADD CONSTRAINT `evaluaciones_extensos_ibfk_1` FOREIGN KEY (`extenso_version_id`) REFERENCES `extenso_versiones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluaciones_extensos_ibfk_2` FOREIGN KEY (`revisor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `extensos`
--
ALTER TABLE `extensos`
  ADD CONSTRAINT `extensos_ibfk_1` FOREIGN KEY (`resumen_id`) REFERENCES `resumenes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `extenso_versiones`
--
ALTER TABLE `extenso_versiones`
  ADD CONSTRAINT `extenso_versiones_ibfk_1` FOREIGN KEY (`extenso_id`) REFERENCES `extensos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`resumen_id`) REFERENCES `resumenes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`revisor_pago_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `resumenes`
--
ALTER TABLE `resumenes`
  ADD CONSTRAINT `resumenes_ibfk_1` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resumenes_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `areas_tematicas` (`id`);

--
-- Filtros para la tabla `revisiones`
--
ALTER TABLE `revisiones`
  ADD CONSTRAINT `revisiones_ibfk_1` FOREIGN KEY (`resumen_id`) REFERENCES `resumenes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revisiones_ibfk_2` FOREIGN KEY (`revisor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `revisores_extensos_perfil`
--
ALTER TABLE `revisores_extensos_perfil`
  ADD CONSTRAINT `revisores_extensos_perfil_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas_tematicas` (`id`);

--
-- Filtros para la tabla `usuario_roles`
--
ALTER TABLE `usuario_roles`
  ADD CONSTRAINT `usuario_roles_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_roles_ibfk_2` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
