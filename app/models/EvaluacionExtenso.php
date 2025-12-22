<?php

class EvaluacionExtenso {
    
    /**
     * Crea los registros iniciales de evaluación cuando se asignan revisores.
     * MEJORA: Forzamos estatus y veredicto a 'Pendiente' para evitar valores por defecto incorrectos.
     */
    public static function asignarRevisores(int $extenso_version_id, array $revisores_ids): bool {
        $pdo = Database::conectar();
        try {
            $pdo->beginTransaction();
            // AJUSTE CRÍTICO: Especificamos 'Pendiente' explícitamente
            $sql = "INSERT INTO evaluaciones_extensos (extenso_version_id, revisor_id, estatus_evaluacion, veredicto) 
                    VALUES (:extenso_version_id, :revisor_id, 'Pendiente', 'Pendiente')";
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
                AND (ee.estatus_evaluacion IS NULL OR ee.estatus_evaluacion = 'Pendiente')
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
     * Guarda las respuestas y el veredicto de una evaluación de extenso (FINAL).
     */
    public static function guardarEvaluacion(int $evaluacion_id, array $datos): bool {
        $pdo = Database::conectar();
        $sql = "UPDATE evaluaciones_extensos SET
                    respuestas_formulario = :respuestas_formulario,
                    observaciones_generales = :observaciones_generales,
                    veredicto = :veredicto,
                    argumento_rechazo = :argumento_rechazo,
                    estatus_evaluacion = 'Pendiente de Firma' -- Al guardar final, pasa a firma
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
        $sql = "SELECT ee.*, r.titulo, u.nombre_completo as revisor_nombre
                FROM evaluaciones_extensos ee
                JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
                JOIN extensos e ON ev.extenso_id = e.id
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN usuarios u ON ee.revisor_id = u.id
                WHERE r.area_id = (SELECT area_id FROM usuarios WHERE id = :coordinador_area_id)
                AND ee.estatus_evaluacion = 'Pendiente de Validación'"; // Ojo: Verifica si usas 'Validada' o 'Pendiente de Validación' en guardarPdfFirmado
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
        // AJUSTE: Forzamos 'Pendiente'
        $sql = "INSERT INTO evaluaciones_extensos (extenso_version_id, revisor_id, estatus_evaluacion, veredicto) 
                VALUES (:extenso_version_id, :revisor_id, 'Pendiente', 'Pendiente')";
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
                WHERE ee.revisor_id = :revisor_id AND ee.estatus_evaluacion NOT IN ('Pendiente de Firma', 'Pendiente de Validación', 'Pendiente')
                ORDER BY ee.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['revisor_id' => $revisor_id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todas las evaluaciones de un extenso, incluyendo los detalles del revisor.
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
                    -- NO cambiamos el estatus_evaluacion, sigue siendo 'Pendiente'
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

            // AJUSTE: Forzamos 'Pendiente' en los nuevos
            $stmt_insert = $pdo->prepare("INSERT INTO evaluaciones_extensos (extenso_version_id, revisor_id, estatus_evaluacion, veredicto) VALUES (:version_id, :revisor_id, 'Pendiente', 'Pendiente')");
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

    /**
     * Verifica si hay consenso en las evaluaciones y actualiza el estatus del extenso si corresponde.
     */
    public static function verificarConsenso(int $extenso_version_id): void {
        $evaluaciones = self::obtenerEvaluacionesValidadas($extenso_version_id);
        $numEvaluaciones = count($evaluaciones);

        if ($numEvaluaciones < 2) return;

        $veredictos = array_column($evaluaciones, 'veredicto');
        $conteos = array_count_values($veredictos);
        
        $aceptados = $conteos['Favorable y Publicable'] ?? 0;
        $conCorrecciones = $conteos['Favorable con Correcciones'] ?? 0;
        $rechazados = $conteos['No Publicable'] ?? 0;
        // $pendientes= $conteos['Pendiente'] ?? 0; // No lo usamos para decidir, pero existe
        
        $estatus_final_extenso = '';

        if ($numEvaluaciones >= 3) {
            if ($rechazados >= 2) $estatus_final_extenso = 'Rechazado';
            elseif ($aceptados >= 2) $estatus_final_extenso = 'Aceptado Final';
            elseif ($conCorrecciones >= 2) $estatus_final_extenso = 'Aceptado con Correcciones';
            else $estatus_final_extenso = 'Aceptado con Correcciones';
        } 
        elseif ($numEvaluaciones == 2) {
            if ($rechazados == 2) {
                $estatus_final_extenso = 'Rechazado';
            } elseif ($aceptados == 2) {
                $estatus_final_extenso = 'Aceptado Final';
            } elseif ($aceptados == 1 && $rechazados == 1) {
                $estatus_final_extenso = 'Conflicto';
            } else {
                $estatus_final_extenso = 'Aceptado con Correcciones';
            }
        }

        if (!empty($estatus_final_extenso)) {
            $extenso = Extenso::buscarPorVersionId($extenso_version_id);
            
            Extenso::actualizarEstatus($extenso['id'], $estatus_final_extenso);
            
            if ($estatus_final_extenso !== 'Conflicto') {
                $autor = Usuario::buscarPorId($extenso['autor_id']);
                MailHelper::enviarCorreo(
                    $autor['correo'], 
                    $autor['nombre_completo'], 
                    'Actualización sobre tu Artículo Extenso', 
                    "<h1>Hola {$autor['nombre_completo']}</h1><p>El estatus de tu artículo ha cambiado a: <strong>{$estatus_final_extenso}</strong>. Por favor ingresa a la plataforma para ver detalles.</p>"
                );
            }
        }
    }

    /**
     * Replica la asignación de revisores de una versión anterior a una nueva.
     */
    public static function replicarAsignacion(int $version_anterior_id, int $nueva_version_id): bool {
        $revisores_ids = self::obtenerIdsRevisoresAsignados($version_anterior_id);
        if (empty($revisores_ids)) return false;
        
        // Al llamar a asignarRevisores aquí, se usarán los valores 'Pendiente' definidos arriba.
        // Así los revisores empiezan de cero con la nueva versión.
        return self::asignarRevisores($nueva_version_id, $revisores_ids);
    }

    /**
     * Busca las evaluaciones que están pendientes de firma (Favorable y Publicable) para un revisor.
     */
    public static function buscarPorFirmarPorRevisor(int $revisor_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT ee.*, ev.intento, r.titulo 
                FROM evaluaciones_extensos ee
                JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
                JOIN extensos e ON ev.extenso_id = e.id
                JOIN resumenes r ON e.resumen_id = r.id
                WHERE ee.revisor_id = :revisor_id 
                AND ee.estatus_evaluacion = 'Pendiente de Firma'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['revisor_id' => $revisor_id]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los detalles de todas las asignaciones de extensos en un área para supervisión.
     */
    public static function obtenerDetallesDeAsignacionesPorArea(int $area_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT 
                    ee.id as evaluacion_id,
                    r.titulo as titulo_articulo,
                    u.nombre_completo as nombre_revisor,
                    ee.estatus_evaluacion,
                    ee.veredicto,
                    ee.respuestas_formulario,
                    ee.observaciones_generales,
                    ee.pdf_firmado_ruta,
                    ev.archivo_ruta as archivo_articulo
                FROM evaluaciones_extensos ee
                JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
                JOIN extensos e ON ev.extenso_id = e.id
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN usuarios u ON ee.revisor_id = u.id
                WHERE r.area_id = :area_id
                ORDER BY e.id DESC, u.nombre_completo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['area_id' => $area_id]);
        return $stmt->fetchAll();
    }
        /**
     * (NUEVO) Busca TODAS las evaluaciones donde el revisor ya emitió un veredicto.
     * Incluye: Pendientes de Firma, Validada, Rechazada, Favorable, No Publicable.
     */
    public static function buscarRealizadasPorRevisor(int $revisor_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT ee.*, ev.intento, r.titulo 
                FROM evaluaciones_extensos ee
                JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
                JOIN extensos e ON ev.extenso_id = e.id
                JOIN resumenes r ON e.resumen_id = r.id
                WHERE ee.revisor_id = :revisor_id 
                AND ee.veredicto != 'Pendiente' 
                AND ee.veredicto IS NOT NULL
                ORDER BY ee.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['revisor_id' => $revisor_id]);
        return $stmt->fetchAll();
    }
}