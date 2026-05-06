<?php

class Extenso {
    
    /**
     * Crea un registro de extenso para un resumen que ha sido aceptado.
     */
    public static function crearParaResumen(int $resumen_id): bool {
        $pdo = Database::conectar();
        $sql = "INSERT INTO extensos (resumen_id) VALUES (:resumen_id)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['resumen_id' => $resumen_id]);
    }

    /**
     * Añade una nueva versión (archivo) a un artículo extenso.
     */
    public static function agregarVersion(int $extenso_id, int $intento, string $archivo_ruta): bool {
        $pdo = Database::conectar();
        $sql = "INSERT INTO extenso_versiones (extenso_id, intento, archivo_ruta) VALUES (:extenso_id, :intento, :archivo_ruta)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'extenso_id'   => $extenso_id,
            'intento'      => $intento,
            'archivo_ruta' => $archivo_ruta
        ]);
    }
    
    /**
     * Actualiza el estatus general de un artículo extenso usando su ID principal.
     */
    public static function actualizarEstatus(int $extenso_id, string $nuevo_estatus): bool {
        $pdo = Database::conectar();
        $sql = "UPDATE extensos SET estatus_extenso = :estatus WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['estatus' => $nuevo_estatus, 'id' => $extenso_id]);
    }

    /**
     * Actualiza el estatus usando el ID de la Versión.
     */
    public static function actualizarEstatusPorVersionId(int $extenso_version_id, string $nuevo_estatus): bool {
        $pdo = Database::conectar();
        $sql = "UPDATE extensos e 
                JOIN extenso_versiones ev ON e.id = ev.extenso_id 
                SET e.estatus_extenso = :estatus 
                WHERE ev.id = :version_id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['estatus' => $nuevo_estatus, 'version_id' => $extenso_version_id]);
    }

    /**
     * Obtiene los extensos que han sido validados y están esperando ser asignados a revisores.
     */
    public static function obtenerPendientesDeAsignacionPorArea(int $area_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT e.*, r.titulo, r.area_id, ev.archivo_ruta
                FROM extensos e
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN extenso_versiones ev ON e.id = ev.extenso_id 
                    AND ev.intento = (SELECT MAX(v.intento) FROM extenso_versiones v WHERE v.extenso_id = e.id)
                WHERE e.estatus_extenso = 'Pendiente de Asignacion' AND r.area_id = :area_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['area_id' => $area_id]);
        return $stmt->fetchAll();
    }

    /**
     * Busca los datos de un extenso a partir del ID de una de sus versiones.
     */
    public static function buscarPorVersionId(int $extenso_version_id) {
        $pdo = Database::conectar();
        $sql = "SELECT e.*, r.autor_id FROM extensos e
                JOIN extenso_versiones ev ON e.id = ev.extenso_id
                JOIN resumenes r ON e.resumen_id = r.id
                WHERE ev.id = :extenso_version_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['extenso_version_id' => $extenso_version_id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene los extensos que están en estado de Conflicto en un área específica.
     */
    public static function obtenerEnConflictoPorArea(int $area_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT e.*, r.titulo, ev.id as version_id 
                FROM extensos e
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN extenso_versiones ev ON e.id = ev.extenso_id 
                    AND ev.intento = (SELECT MAX(v.intento) FROM extenso_versiones v WHERE v.extenso_id = e.id)
                WHERE e.estatus_extenso = 'Conflicto' AND r.area_id = :area_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['area_id' => $area_id]);
        return $stmt->fetchAll();
    }

    public static function buscarPorId(int $id) {
        $pdo = Database::conectar();
        $sql = "SELECT * FROM extensos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function obtenerUltimoIntento(int $extenso_id): int {
        $pdo = Database::conectar();
        $sql = "SELECT MAX(intento) FROM extenso_versiones WHERE extenso_id = :extenso_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['extenso_id' => $extenso_id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Obtiene todos los extensos con detalles completos para el panel de admin.
     */
    public static function obtenerTodosConDetalles(): array {
        $pdo = Database::conectar();
        $sql = "SELECT 
                    e.id,
                    e.estatus_extenso,
                    r.titulo,
                    u.nombre_completo as autor_nombre,
                    a.nombre_area,
                    GROUP_CONCAT(DISTINCT rev_user.nombre_completo SEPARATOR ', ') as revisores_asignados
                FROM extensos e
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN usuarios u ON r.autor_id = u.id
                JOIN areas_tematicas a ON r.area_id = a.id
                LEFT JOIN extenso_versiones ev ON e.id = ev.extenso_id
                LEFT JOIN evaluaciones_extensos ee ON ev.id = ee.extenso_version_id
                LEFT JOIN usuarios rev_user ON ee.revisor_id = rev_user.id
                GROUP BY e.id
                ORDER BY e.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene extensos pendientes de filtro por área.
     */
    public static function obtenerPendientesDeFiltroPorArea(int $area_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT e.*, r.titulo, r.area_id, ev.archivo_ruta 
                FROM extensos e 
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN extenso_versiones ev ON e.id = ev.extenso_id 
                    AND ev.intento = (SELECT MAX(v.intento) FROM extenso_versiones v WHERE v.extenso_id = e.id)
                WHERE e.estatus_extenso = 'Pendiente de Filtro' AND r.area_id = :area_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['area_id' => $area_id]);
        return $stmt->fetchAll();
    }

    /**
     * Actualiza el estatus y los comentarios de formato de un extenso.
     */
    public static function actualizarEstatusYComentarios(int $extenso_id, string $estatus, string $comentarios): bool {
        $pdo = Database::conectar();
        $sql = "UPDATE extensos SET estatus_extenso = :estatus, comentarios_formato = :comentarios WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['estatus' => $estatus, 'comentarios' => $comentarios, 'id' => $extenso_id]);
    }

    public static function obtenerIdUltimaVersion(int $extenso_id) {
        $pdo = Database::conectar();
        $sql = "SELECT id FROM extenso_versiones 
                WHERE extenso_id = :extenso_id 
                ORDER BY intento DESC 
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['extenso_id' => $extenso_id]);
        return $stmt->fetchColumn();
    }

    /**
     * Obtiene los detalles completos de un extenso para el panel del autor.
     */
    public static function obtenerDetallesParaAutor(int $extenso_id) {
        $pdo = Database::conectar();
        $extenso = self::buscarPorId($extenso_id);
        if (!$extenso) return false;

        $stmt_versiones = $pdo->prepare("SELECT * FROM extenso_versiones WHERE extenso_id = :extenso_id ORDER BY intento DESC");
        $stmt_versiones->execute(['extenso_id' => $extenso_id]);
        $extenso['versiones'] = $stmt_versiones->fetchAll();

        $stmt_evaluaciones = $pdo->prepare(
            "SELECT ee.observaciones_generales, ee.argumento_rechazo 
             FROM evaluaciones_extensos ee
             JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
             WHERE ev.extenso_id = :extenso_id AND ee.estatus_evaluacion = 'Validada'"
        );
        $stmt_evaluaciones->execute(['extenso_id' => $extenso_id]);
        $extenso['evaluaciones'] = $stmt_evaluaciones->fetchAll();

        $stmt_final = $pdo->prepare('SELECT archivo_ruta, fecha_envio FROM extenso_version_final WHERE extenso_id = :extenso_id');
        $stmt_final->execute(['extenso_id' => $extenso_id]);
        $vf = $stmt_final->fetch();
        $extenso['version_final'] = $vf ?: null;

        return $extenso;
    }

    /**
     * Registro de versión final (PDF para revista), si existe.
     */
    public static function obtenerVersionFinalPorExtenso(int $extenso_id): ?array {
        $pdo = Database::conectar();
        $stmt = $pdo->prepare('SELECT id, extenso_id, archivo_ruta, fecha_envio FROM extenso_version_final WHERE extenso_id = :extenso_id');
        $stmt->execute(['extenso_id' => $extenso_id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Extenso en estado Aceptado Final y perteneciente al autor; incluye datos del resumen para la vista de subida.
     */
    public static function obtenerParaSubidaVersionFinal(int $extenso_id, int $autor_id): ?array {
        $pdo = Database::conectar();
        $sql = 'SELECT e.id AS extenso_id, e.estatus_extenso, e.comentarios_formato, e.resumen_id,
                       r.titulo, r.autor_principal, r.coautores, r.adscripcion1, r.adscripcion2,
                       evf.archivo_ruta AS vf_archivo_ruta, evf.fecha_envio AS vf_fecha_envio
                FROM extensos e
                INNER JOIN resumenes r ON e.resumen_id = r.id
                LEFT JOIN extenso_version_final evf ON evf.extenso_id = e.id
                WHERE e.id = :extenso_id AND r.autor_id = :autor_id
                AND e.estatus_extenso IN ("Aceptado Final", "Corregir extenso final")';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'extenso_id' => $extenso_id,
            'autor_id'   => $autor_id
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Inserta o actualiza el PDF final y elimina el archivo anterior del disco si se reemplaza.
     */
    public static function guardarOReemplazarVersionFinal(int $extenso_id, string $archivo_ruta): bool {
        $pdo = Database::conectar();
        $anterior = self::obtenerVersionFinalPorExtenso($extenso_id);
        $dir = BACKEND_ROOT . '/uploads/extensos_finales/';

        $pdo->beginTransaction();
        if ($anterior) {
            $stmt = $pdo->prepare('UPDATE extenso_version_final SET archivo_ruta = :archivo_ruta, fecha_envio = CURRENT_TIMESTAMP WHERE extenso_id = :extenso_id');
            $ok = $stmt->execute(['archivo_ruta' => $archivo_ruta, 'extenso_id' => $extenso_id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO extenso_version_final (extenso_id, archivo_ruta) VALUES (:extenso_id, :archivo_ruta)');
            $ok = $stmt->execute(['extenso_id' => $extenso_id, 'archivo_ruta' => $archivo_ruta]);
        }
        if (!$ok) {
            $pdo->rollBack();
            return false;
        }
        $pdo->commit();

        if ($anterior && !empty($anterior['archivo_ruta']) && $anterior['archivo_ruta'] !== $archivo_ruta) {
            $rutaVieja = $dir . basename($anterior['archivo_ruta']);
            if (is_file($rutaVieja)) {
                @unlink($rutaVieja);
            }
        }

        return true;
    }

    /**
     * Obtiene los extensos que ya fueron asignados y están 'En Revisión'.
     * Muestra revisores de TODAS las versiones (histórico), indicando la versión.
     */
    public static function obtenerEnRevisionPorArea(int $area_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT
                    e.id,
                    r.titulo,
                    GROUP_CONCAT(CONCAT(u.nombre_completo, ' [v', ev.intento, ']') SEPARATOR ', ') as revisores_asignados
                FROM extensos e
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN extenso_versiones ev ON e.id = ev.extenso_id 
                LEFT JOIN evaluaciones_extensos ee ON ev.id = ee.extenso_version_id
                LEFT JOIN usuarios u ON ee.revisor_id = u.id
                WHERE e.estatus_extenso = 'En Revisión' AND r.area_id = :area_id
                GROUP BY e.id
                ORDER BY e.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['area_id' => $area_id]);
        return $stmt->fetchAll();
    }

    public static function obtenerDetallesParaNotificacion(int $extenso_id) {
        $pdo = Database::conectar();
        $sql = "SELECT 
                    r.titulo, 
                    u.nombre_completo as autor_nombre, 
                    u.correo as autor_correo
                FROM extensos e
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN usuarios u ON r.autor_id = u.id
                WHERE e.id = :extenso_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['extenso_id' => $extenso_id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene extensos en etapa final por área para gestión del coordinador.
     */
    public static function obtenerFinalesPorArea(int $area_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT
                    e.id,
                    e.estatus_extenso,
                    e.comentarios_formato,
                    r.titulo,
                    r.autor_principal,
                    u.nombre_completo AS autor_nombre,
                    u.correo AS autor_correo,
                    evf.archivo_ruta AS archivo_final_ruta,
                    evf.fecha_envio AS fecha_envio_final
                FROM extensos e
                INNER JOIN resumenes r ON e.resumen_id = r.id
                INNER JOIN usuarios u ON r.autor_id = u.id
                LEFT JOIN extenso_version_final evf ON evf.extenso_id = e.id
                WHERE r.area_id = :area_id
                  AND e.estatus_extenso IN ('Aceptado Final', 'Corregir extenso final')
                ORDER BY
                    CASE WHEN evf.archivo_ruta IS NULL THEN 0 ELSE 1 END DESC,
                    evf.fecha_envio DESC,
                    e.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['area_id' => $area_id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los extensos que ya han sido evaluados (Etapa D) para un área.
     * Incluye Aceptados, Rechazados y en Conflicto.
     */
    public static function obtenerEvaluadosPorArea(int $area_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT 
                    e.id, 
                    r.titulo, 
                    e.estatus_extenso,
                    ev.id as version_id,
                    GROUP_CONCAT(CONCAT(u.nombre_completo, ':::', IFNULL(ee.veredicto, 'Pendiente'), ':::', u.id) SEPARATOR '|||') as detalles_evaluacion
                FROM extensos e
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN extenso_versiones ev ON e.id = ev.extenso_id 
                    AND ev.intento = (SELECT MAX(v.intento) FROM extenso_versiones v WHERE v.extenso_id = e.id)
                LEFT JOIN evaluaciones_extensos ee ON ev.id = ee.extenso_version_id
                LEFT JOIN usuarios u ON ee.revisor_id = u.id
                WHERE r.area_id = :area_id
                AND e.estatus_extenso IN ('Aceptado Final', 'Aceptado con Correcciones', 'Rechazado', 'Conflicto')
                GROUP BY e.id, ev.id
                ORDER BY e.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['area_id' => $area_id]);
        return $stmt->fetchAll();
    }

/**
     * Calcula la fecha límite (15 días después de la última evaluación de la versión actual).
     */
    public static function calcularFechaLimite(int $extenso_id): ?string {
        $pdo = Database::conectar();
        // Obtener ID de la última versión
        $sql = "SELECT id FROM extenso_versiones WHERE extenso_id = :extenso_id ORDER BY intento DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['extenso_id' => $extenso_id]);
        $version_id = $stmt->fetchColumn();

        if (!$version_id) return null;

        // Obtener la fecha de evaluación más reciente de esa versión
        $sql = "SELECT MAX(fecha_evaluacion) FROM evaluaciones_extensos 
                WHERE extenso_version_id = :version_id AND estatus_evaluacion = 'Validada'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['version_id' => $version_id]);
        $fecha_eval = $stmt->fetchColumn();

        if (!$fecha_eval) return null;

        // MODIFICACIÓN: Lógica original comentada para desactivar los 15 días
        // return date('Y-m-d H:i:s', strtotime($fecha_eval . ' + 15 days'));
        
        // Retornamos null para que el sistema asuma que NO hay fecha límite
        return null; 
    }
}