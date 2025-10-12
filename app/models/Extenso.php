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
            'extenso_id' => $extenso_id,
            'intento' => $intento,
            'archivo_ruta' => $archivo_ruta
        ]);
    }
    
    /**
     * Actualiza el estatus general de un artículo extenso.
     */
    public static function actualizarEstatus(int $extenso_id, string $nuevo_estatus): bool {
        $pdo = Database::conectar();
        $sql = "UPDATE extensos SET estatus_extenso = :estatus WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['estatus' => $nuevo_estatus, 'id' => $extenso_id]);
    }
    /**
 * Obtiene los extensos que han sido enviados y están esperando ser asignados a revisores.
 */
public static function obtenerPendientesDeAsignacion(): array {
    $pdo = Database::conectar();
    // CAMBIO: Se añade r.area_id a la consulta SELECT
    $sql = "SELECT e.*, r.titulo, r.area_id
            FROM extensos e
            JOIN resumenes r ON e.resumen_id = r.id
            LEFT JOIN extenso_versiones ev ON e.id = ev.extenso_id
            LEFT JOIN evaluaciones_extensos ee ON ev.id = ee.extenso_version_id
            WHERE e.estatus_extenso = 'Pendiente de Filtro' AND ee.id IS NULL
            GROUP BY e.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
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
    $sql = "SELECT e.*, r.titulo 
            FROM extensos e
            JOIN resumenes r ON e.resumen_id = r.id
            WHERE e.estatus_extenso = 'Conflicto' AND r.area_id = :area_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['area_id' => $area_id]);
    return $stmt->fetchAll();
}
/**
 * Busca un extenso por su ID.
 */
public static function buscarPorId(int $id) {
    $pdo = Database::conectar();
    $sql = "SELECT * FROM extensos WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

/**
 * Obtiene el número del último intento de envío para un extenso.
 */
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
    $sql = "SELECT e.*, r.titulo, ev.archivo_ruta 
            FROM extensos e 
            JOIN resumenes r ON e.resumen_id = r.id
            JOIN extenso_versiones ev ON e.id = ev.extenso_id 
                AND ev.intento = (SELECT MAX(v.intento) FROM extenso_versiones v WHERE v.extenso_id = e.id)
            WHERE e.estatus_extenso = 'Pendiente de Filtro'"; //"AND r.area_id = :area_id";
    $stmt = $pdo->prepare($sql);
    //$stmt->execute(['area_id' => $area_id]);
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
}