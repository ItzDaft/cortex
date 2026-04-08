-- Tabla para un único PDF final por extenso (versión para revista).
-- Ejecutar en producción después del despliegue del código que usa esta tabla.

CREATE TABLE IF NOT EXISTS `extenso_version_final` (
  `id` int NOT NULL AUTO_INCREMENT,
  `extenso_id` int NOT NULL,
  `archivo_ruta` varchar(255) NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_extenso_version_final_extenso` (`extenso_id`),
  CONSTRAINT `extenso_version_final_ibfk_1` FOREIGN KEY (`extenso_id`) REFERENCES `extensos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
