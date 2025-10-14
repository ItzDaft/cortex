<?php

class EvaluacionExtenso {
    /**
     * Crea los registros iniciales de evaluación cuando se asignan revisores.
     */
    public static function asignarRevisores(int $extenso_version_id, array $revisores_ids): bool {
        $pdo = Database::conectar();
        try {
            $pdo->beginTransaction();
            $sql = "INSERT INTO evaluaciones_extensos (extenso_version_id, revisor_id) VALUES (:extenso_version_id, :revisor_id)";
            $stmt = $pdo->prepare($sql);
            foreach ($revisores_ids as $revisor_id) {
                $stmt->execute([
                    'extenso_version_id' => $extenso_version_id,
                    'revisor_id' => (int)$revisor_id
                ]);
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
    /**
 * Busca las evaluaciones pendientes asignadas a un revisor específico.
 */
public static function buscarAsignadasPorRevisor(int $revisor_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT ee.*, ev.intento, r.titulo 
            FROM evaluaciones_extensos ee
            JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
            JOIN extensos e ON ev.extenso_id = e.id
            JOIN resumenes r ON e.resumen_id = r.id
            JOIN usuarios u ON ee.revisor_id = u.id
            WHERE ee.revisor_id = :revisor_id 
            AND ee.estatus_evaluacion IN ('Pendiente de Firma', 'Pendiente de Validación')
            AND r.area_id = u.area_id"; 
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['revisor_id' => $revisor_id]);
    return $stmt->fetchAll();
}
  public static function buscarPorId(int $id) {
    $pdo = Database::conectar();
    $sql = "SELECT ee.*, ev.archivo_ruta, r.titulo 
            FROM evaluaciones_extensos ee
            JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
            JOIN extensos e ON ev.extenso_id = e.id
            JOIN resumenes r ON e.resumen_id = r.id
            WHERE ee.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}  
/**
 * Guarda las respuestas y el veredicto de una evaluación de extenso.
 */
public static function guardarEvaluacion(int $evaluacion_id, array $datos): bool {
    $pdo = Database::conectar();
    $sql = "UPDATE evaluaciones_extensos SET
                respuestas_formulario = :respuestas_formulario,
                observaciones_generales = :observaciones_generales,
                veredicto = :veredicto,
                argumento_rechazo = :argumento_rechazo,
                estatus_evaluacion = 'Pendiente de Validación' -- Cambia el estado
            WHERE id = :evaluacion_id";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'respuestas_formulario'   => $datos['respuestas_formulario'],
        'observaciones_generales' => $datos['observaciones_generales'],
        'veredicto'               => $datos['veredicto'],
        'argumento_rechazo'       => $datos['argumento_rechazo'],
        'evaluacion_id'           => $evaluacion_id
    ]);
}
/**
 * Guarda la ruta del PDF firmado y actualiza el estatus de la evaluación.
 */
public static function guardarPdfFirmado(int $evaluacion_id, string $nombreArchivo): bool {
    $pdo = Database::conectar();
    $sql = "UPDATE evaluaciones_extensos SET 
                pdf_firmado_ruta = :pdf_firmado_ruta,
                estatus_evaluacion = 'Validada' -- Cambia a 'Validada' para que el Coordinador la vea
            WHERE id = :evaluacion_id";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'pdf_firmado_ruta' => $nombreArchivo,
        'evaluacion_id'    => $evaluacion_id
    ]);
}
/**
 * Busca las evaluaciones que ya fueron firmadas y están pendientes de validación.
 */
public static function buscarPendientesDeValidacion(int $coordinador_area_id): array {
    $pdo = Database::conectar();
    // Esta consulta busca evaluaciones de artículos que pertenecen al área del coordinador.
    $sql = "SELECT ee.*, r.titulo, u.nombre_completo as revisor_nombre
            FROM evaluaciones_extensos ee
            JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
            JOIN extensos e ON ev.extenso_id = e.id
            JOIN resumenes r ON e.resumen_id = r.id
            JOIN usuarios u ON ee.revisor_id = u.id
            WHERE r.area_id = (SELECT area_id FROM usuarios WHERE id = :coordinador_area_id)
            AND ee.estatus_evaluacion = 'Pendiente de Validación'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['coordinador_area_id' => $coordinador_area_id]);
    return $stmt->fetchAll();
}

/**
 * Actualiza el estatus de una evaluación después de ser validada por un coordinador.
 */
public static function validarEvaluacion(int $evaluacion_id, string $nuevo_estatus, ?string $comentarios): bool {
    $pdo = Database::conectar();
    $sql = "UPDATE evaluaciones_extensos SET 
                estatus_evaluacion = :estatus,
                comentarios_coordinador = :comentarios
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'estatus' => $nuevo_estatus,
        'comentarios' => $comentarios,
        'id' => $evaluacion_id
    ]);
}
/**
 * Obtiene todas las evaluaciones validadas para una versión de extenso específica.
 */
public static function obtenerEvaluacionesValidadas(int $extenso_version_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT veredicto, observaciones_generales, argumento_rechazo 
            FROM evaluaciones_extensos 
            WHERE extenso_version_id = :extenso_version_id AND estatus_evaluacion = 'Validada'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['extenso_version_id' => $extenso_version_id]);
    return $stmt->fetchAll();
}
/**
 * Asigna un único tercer revisor para desempate.
 */
public static function asignarTercerRevisor(int $extenso_version_id, int $revisor_id): bool {
    $pdo = Database::conectar();
    $sql = "INSERT INTO evaluaciones_extensos (extenso_version_id, revisor_id) VALUES (:extenso_version_id, :revisor_id)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'extenso_version_id' => $extenso_version_id,
        'revisor_id' => $revisor_id
    ]);
}
/**
 * Obtiene todas las evaluaciones validadas para los extensos de un autor.
 */
public static function obtenerEvaluacionesParaAutor(int $autor_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT ee.observaciones_generales, ee.argumento_rechazo, e.id as extenso_id
            FROM evaluaciones_extensos ee
            JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
            JOIN extensos e ON ev.extenso_id = e.id
            JOIN resumenes r ON e.resumen_id = r.id
            WHERE r.autor_id = :autor_id AND ee.estatus_evaluacion = 'Validada'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['autor_id' => $autor_id]);
    return $stmt->fetchAll();
}
/**
 * Elimina todas las evaluaciones asociadas a las versiones de un extenso.
 */
public static function eliminarEvaluacionesAnteriores(int $extenso_id): bool {
    $pdo = Database::conectar();
    $sql = "DELETE FROM evaluaciones_extensos WHERE extenso_version_id IN (SELECT id FROM extenso_versiones WHERE extenso_id = :extenso_id)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['extenso_id' => $extenso_id]);
}
/**
 * Busca las evaluaciones que un Revisor de Extensos ya ha completado.
 */
public static function buscarCompletadasPorRevisor(int $revisor_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT ee.*, r.titulo 
            FROM evaluaciones_extensos ee
            JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
            JOIN extensos e ON ev.extenso_id = e.id
            JOIN resumenes r ON e.resumen_id = r.id
            WHERE ee.revisor_id = :revisor_id AND ee.estatus_evaluacion NOT IN ('Pendiente de Firma', 'Pendiente de Validación')
            ORDER BY ee.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['revisor_id' => $revisor_id]);
    return $stmt->fetchAll();
}
/**
 * Obtiene todas las evaluaciones de un extenso, incluyendo los detalles del revisor.
 * @param int $extenso_id El ID del extenso.
 * @return array Una lista de las evaluaciones.
 */
public static function obtenerPorExtensoId(int $extenso_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT ee.*, u.nombre_completo as revisor_nombre
            FROM evaluaciones_extensos ee
            JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
            JOIN usuarios u ON ee.revisor_id = u.id
            WHERE ev.extenso_id = :extenso_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['extenso_id' => $extenso_id]);
    return $stmt->fetchAll();
}
/**
 * Guarda el estado completo de un formulario de evaluación como borrador.
 */
public static function guardarBorrador(int $evaluacion_id, array $datos): bool {
    $pdo = Database::conectar();
    $sql = "UPDATE evaluaciones_extensos SET 
                respuestas_formulario = :respuestas_formulario,
                observaciones_generales = :observaciones_generales,
                veredicto = :veredicto,
                argumento_rechazo = :argumento_rechazo
            WHERE id = :evaluacion_id";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'respuestas_formulario'   => $datos['respuestas_formulario'],
        'observaciones_generales' => $datos['observaciones_generales'],
        'veredicto'               => $datos['veredicto'],
        'argumento_rechazo'       => $datos['argumento_rechazo'],
        'evaluacion_id'           => $evaluacion_id
    ]);
}
/**
 * Obtiene los IDs de los revisores actualmente asignados a una versión de extenso.
 */
public static function obtenerIdsRevisoresAsignados(int $extenso_version_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT revisor_id FROM evaluaciones_extensos WHERE extenso_version_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $extenso_version_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Actualiza los revisores de una versión de extenso (borra los anteriores e inserta los nuevos).
 */
public static function actualizarRevisores(int $extenso_version_id, array $revisores_ids): bool {
    $pdo = Database::conectar();
    try {
        $pdo->beginTransaction();
        $stmt_delete = $pdo->prepare("DELETE FROM evaluaciones_extensos WHERE extenso_version_id = :id");
        $stmt_delete->execute(['id' => $extenso_version_id]);

        $stmt_insert = $pdo->prepare("INSERT INTO evaluaciones_extensos (extenso_version_id, revisor_id) VALUES (:version_id, :revisor_id)");
        foreach ($revisores_ids as $revisor_id) {
            $stmt_insert->execute(['version_id' => $extenso_version_id, 'revisor_id' => (int)$revisor_id]);
        }
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        return false;
    }
}
}