<?php

class EvaluacionExtenso {

    /** Días naturales desde fecha_asignacion para completar la evaluación del extenso. */
    public const PLAZO_EVALUACION_EXTENSO_DIAS = 7;

    /** Inicio del cómputo del plazo (solo fecha). */
    public static function fechaInicioPlazoEvaluacion(?string $fecha_asignacion): ?DateTime {
        if (empty($fecha_asignacion)) {
            return null;
        }
        $d = new DateTime($fecha_asignacion);
        $d->setTime(0, 0, 0);
        return $d;
    }

    /** Último día del plazo (inclusive) según fecha_asignacion. */
    public static function fechaLimitePlazoEvaluacion(?string $fecha_asignacion): ?DateTime {
        $inicio = self::fechaInicioPlazoEvaluacion($fecha_asignacion);
        if ($inicio === null) {
            return null;
        }
        $limite = clone $inicio;
        $limite->modify('+' . self::PLAZO_EVALUACION_EXTENSO_DIAS . ' days');
        return $limite;
    }

    public static function plazoEvaluacionExtensoEstaVencido(?string $fecha_asignacion): bool {
        $limite = self::fechaLimitePlazoEvaluacion($fecha_asignacion);
        if ($limite === null) {
            return false;
        }
        $hoy = new DateTime('today');
        return $hoy > $limite;
    }

    /**
     * Crea los registros iniciales de evaluación cuando se asignan revisores.
     * MEJORA: Se registra la fecha_asignacion con NOW().
     */
    public static function asignarRevisores(int $extenso_version_id, array $revisores_ids): bool {
        $pdo = Database::conectar();
        $inTransaction = $pdo->inTransaction(); // Check if already in transaction
        try {
            if (!$inTransaction) {
                $pdo->beginTransaction();
            }
            $sql = "INSERT INTO evaluaciones_extensos (extenso_version_id, revisor_id, estatus_evaluacion, veredicto, fecha_asignacion) 
                    VALUES (:extenso_version_id, :revisor_id, 'Pendiente', 'Pendiente', NOW())";
            $stmt = $pdo->prepare($sql);
            foreach ($revisores_ids as $revisor_id) {
                $stmt->execute([
                    'extenso_version_id' => $extenso_version_id,
                    'revisor_id' => (int)$revisor_id
                ]);
            }
            if (!$inTransaction) {
                $pdo->commit();
            }
            return true;
        } catch (Exception $e) {
            if (!$inTransaction) {
                $pdo->rollBack();
            }
            error_log($e->getMessage());
            return false;
        }
    }



    public static function buscarPorId(int $id) {
        $pdo = Database::conectar();
        $sql = "SELECT ee.*, ev.archivo_ruta, r.titulo, u.correo as correo_revisor, u.nombre_completo as nombre_revisor
                FROM evaluaciones_extensos ee
                JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
                JOIN extensos e ON ev.extenso_id = e.id
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN usuarios u ON ee.revisor_id = u.id
                WHERE ee.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }   

    /**
     * Guarda las respuestas y el veredicto de una evaluación de extenso .
     */
public static function guardarEvaluacion(int $evaluacion_id, array $datos): bool {
    $pdo = Database::conectar();
    $sql = "UPDATE evaluaciones_extensos SET
                respuestas_formulario = :respuestas_formulario,
                observaciones_generales = :observaciones_generales,
                veredicto = :veredicto,
                argumento_rechazo = :argumento_rechazo,
                estatus_evaluacion = 'Pendiente de Firma',
                fecha_evaluacion = NOW() 
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
                estatus_evaluacion = 'Validada',
                fecha_evaluacion = NOW() 
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
        $sql = "INSERT INTO evaluaciones_extensos (extenso_version_id, revisor_id, estatus_evaluacion, veredicto, fecha_asignacion) 
                VALUES (:extenso_version_id, :revisor_id, 'Pendiente', 'Pendiente', NOW())";
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
        $inTransaction = $pdo->inTransaction();
        try {
            if (!$inTransaction) {
                $pdo->beginTransaction();
            }
            $stmt_delete = $pdo->prepare("DELETE FROM evaluaciones_extensos WHERE extenso_version_id = :id");
            $stmt_delete->execute(['id' => $extenso_version_id]);

            $stmt_insert = $pdo->prepare("INSERT INTO evaluaciones_extensos (extenso_version_id, revisor_id, estatus_evaluacion, veredicto, fecha_asignacion) VALUES (:version_id, :revisor_id, 'Pendiente', 'Pendiente', NOW())");
            foreach ($revisores_ids as $revisor_id) {
                $stmt_insert->execute(['version_id' => $extenso_version_id, 'revisor_id' => (int)$revisor_id]);
            }
            if (!$inTransaction) {
                $pdo->commit();
            }
            return true;
        } catch (Exception $e) {
            if (!$inTransaction) {
                $pdo->rollBack();
            }
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

        // Normalizamos los veredictos para soportar variantes históricas y acentos.
        $normalizarVeredicto = static function (string $veredicto): string {
            $v = trim(mb_strtolower($veredicto, 'UTF-8'));
            $v = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $v);

            if (
                strpos($v, 'no publicable') !== false ||
                strpos($v, 'no se recomienda su publicacion') !== false
            ) {
                return 'no_publicable';
            }

            if (
                strpos($v, 'favorable con correcciones') !== false ||
                strpos($v, 'favorable y publicable con correcciones') !== false
            ) {
                return 'favorable_con_correcciones';
            }

            if (
                strpos($v, 'favorable y publicable sin recomendaciones') !== false ||
                $v === 'favorable y publicable'
            ) {
                return 'favorable_sin_recomendaciones';
            }

            return 'otro';
        };

        $veredictos = array_map(function($ev) use ($normalizarVeredicto) {
            return $normalizarVeredicto((string)($ev['veredicto'] ?? ''));
        }, $evaluaciones);

        $conteos = array_count_values($veredictos);

        $aceptados = $conteos['favorable_sin_recomendaciones'] ?? 0;
        $conCorrecciones = $conteos['favorable_con_correcciones'] ?? 0;
        $rechazados = $conteos['no_publicable'] ?? 0;

        $estatus_final_extenso = '';

        if ($numEvaluaciones >= 2) {
            // Decisión por mayoría (2 a 5 revisores):
            // - Rechazado: mayoría "No Publicable".
            // - Aceptado Final: unanimidad "Favorable y Publicable" sin recomendaciones.
            // - Aceptado con Correcciones: mayoría publicable (con o sin correcciones), sin unanimidad limpia.
            // - Conflicto: empate o mezcla no concluyente.
            $publicables = $aceptados + $conCorrecciones;

            if ($aceptados === $numEvaluaciones) {
                $estatus_final_extenso = 'Aceptado Final';
            } elseif ($rechazados > $publicables) {
                $estatus_final_extenso = 'Rechazado';
            } elseif ($publicables > $rechazados) {
                $estatus_final_extenso = 'Aceptado con Correcciones';
            } else {
                $estatus_final_extenso = 'Conflicto';
            }
        }

        if (!empty($estatus_final_extenso)) {
            $extenso = Extenso::buscarPorVersionId($extenso_version_id);
            if (!$extenso) {
                return;
            }

            $estatusAnterior = (string)($extenso['estatus_extenso'] ?? '');
            $esPrimerCambioAFinal = ($estatus_final_extenso === 'Aceptado Final' && $estatusAnterior !== 'Aceptado Final');

            Extenso::actualizarEstatus((int)$extenso['id'], $estatus_final_extenso);

            if ($estatus_final_extenso !== 'Conflicto') {
                $autor = Usuario::buscarPorId($extenso['autor_id']);
                if ($autor) {
                    MailHelper::enviarCorreo(
                        $autor['correo'],
                        $autor['nombre_completo'],
                        'Actualización sobre tu Artículo Extenso',
                        "<h1>Hola {$autor['nombre_completo']}</h1><p>El estatus de tu artículo ha cambiado a: <strong>{$estatus_final_extenso}</strong>. Por favor ingresa a la plataforma para ver detalles.</p>"
                    );
                }
            }

            // Cuando ambos revisores dictaminan favorable/publicable y pasa a final por primera vez,
            // invitamos al autor a subir el documento editable con toda la información editorial.
            if ($esPrimerCambioAFinal) {
                $autor = Usuario::buscarPorId($extenso['autor_id']);
                if ($autor) {
                    $urlSubida = rtrim(BASE_URL, '/') . '/extenso/subirFinal/' . (int)$extenso['id'];
                    $cuerpoInvitacion = "
                        <h1>Hola {$autor['nombre_completo']}</h1>
                        <p>Tu extenso fue dictaminado como <strong>Favorable y Publicable</strong> por ambos revisores y ahora se encuentra en estatus <strong>Aceptado Final</strong>.</p>
                        <p>Te invitamos a ingresar a la plataforma para subir tu extenso en formato <strong>.doc</strong> o <strong>.docx</strong>, incluyendo:</p>
                        <ul>
                            <li>Información completa de autores y coautores.</li>
                            <li>Dependencias/adscripciones institucionales.</li>
                            <li>Imágenes y demás elementos requeridos para publicación.</li>
                        </ul>
                        <p>Puedes realizar la carga en el siguiente enlace:</p>
                        <p><a href=\"{$urlSubida}\">{$urlSubida}</a></p>
                    ";

                    MailHelper::enviarCorreo(
                        $autor['correo'],
                        $autor['nombre_completo'],
                        'Invitación para envío de extenso en formato editable',
                        $cuerpoInvitacion
                    );
                }
            }
        }
    }

    /**
     * Replica la asignación de revisores de una versión anterior a una nueva.
     */
    public static function replicarAsignacion(int $version_anterior_id, int $nueva_version_id): bool {
        $revisores_ids = self::obtenerIdsRevisoresAsignados($version_anterior_id);
        if (empty($revisores_ids)) return false;
        
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
                    ee.fecha_asignacion, 
                    ee.estatus_evaluacion,
                    ee.veredicto,
                    ee.respuestas_formulario,
                    ee.observaciones_generales,
                    ee.argumento_rechazo,
                    ee.pdf_firmado_ruta,
                    r.titulo as titulo_articulo,
                    u.nombre_completo as nombre_revisor,
                    u.correo as correo_revisor,
                    ev.archivo_ruta as archivo_extenso_ruta
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
     * Busca las evaluaciones PENDIENTES (Aún no evaluadas).
     * 
     */
    public static function buscarAsignadasPorRevisor(int $revisor_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT ee.*, ev.intento, ev.archivo_ruta, r.titulo 
                FROM evaluaciones_extensos ee
                JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
                JOIN extensos e ON ev.extenso_id = e.id
                JOIN resumenes r ON e.resumen_id = r.id
                JOIN usuarios u ON ee.revisor_id = u.id
                WHERE ee.revisor_id = :revisor_id 
                AND (ee.estatus_evaluacion = 'Pendiente' OR ee.veredicto = 'Pendiente')
                AND r.area_id = u.area_id"; 
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['revisor_id' => $revisor_id]);
        return $stmt->fetchAll();
    }

    /**
     * Busca TODAS las evaluaciones donde el revisor ya emitió un veredicto.
     * 
     */
    public static function buscarRealizadasPorRevisor(int $revisor_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT ee.*, ev.intento, ev.archivo_ruta, r.titulo, ee.pdf_firmado_ruta
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

    /**
     * Construye un historial completo de seguimiento por resumen para supervisión.
     * Incluye todas las versiones (Rev1, Rev2, ... ) y la versión final si existe.
     */
    public static function obtenerHistorialSeguimientoPorArea(int $area_id): array {
        $pdo = Database::conectar();

        $sqlVersiones = "SELECT
                            r.id AS resumen_id,
                            r.titulo AS titulo_articulo,
                            ev.intento AS version_intento,
                            ee.id AS evaluacion_id,
                            u.nombre_completo AS nombre_revisor,
                            u.correo AS correo_revisor,
                            ee.estatus_evaluacion,
                            ee.veredicto,
                            ee.observaciones_generales,
                            ee.argumento_rechazo,
                            ev.archivo_ruta AS archivo_ruta
                        FROM resumenes r
                        INNER JOIN extensos e ON e.resumen_id = r.id
                        INNER JOIN extenso_versiones ev ON ev.extenso_id = e.id
                        LEFT JOIN evaluaciones_extensos ee ON ee.extenso_version_id = ev.id
                        LEFT JOIN usuarios u ON u.id = ee.revisor_id
                        WHERE r.area_id = :area_id
                        ORDER BY r.id DESC, ev.intento ASC, ee.fecha_asignacion ASC, ee.id ASC";

        $stmtVersiones = $pdo->prepare($sqlVersiones);
        $stmtVersiones->execute(['area_id' => $area_id]);
        $rowsVersiones = $stmtVersiones->fetchAll(PDO::FETCH_ASSOC);

        $sqlFinal = "SELECT
                        r.id AS resumen_id,
                        vf.archivo_ruta AS archivo_ruta_final
                    FROM resumenes r
                    INNER JOIN extensos e ON e.resumen_id = r.id
                    INNER JOIN extenso_version_final vf ON vf.extenso_id = e.id
                    WHERE r.area_id = :area_id";
        $stmtFinal = $pdo->prepare($sqlFinal);
        $stmtFinal->execute(['area_id' => $area_id]);
        $rowsFinal = $stmtFinal->fetchAll(PDO::FETCH_ASSOC);

        $finalPorResumen = [];
        foreach ($rowsFinal as $rowFinal) {
            $finalPorResumen[(int)$rowFinal['resumen_id']] = $rowFinal['archivo_ruta_final'];
        }

        $agrupado = [];
        foreach ($rowsVersiones as $row) {
            $resumenId = (int)($row['resumen_id'] ?? 0);
            if ($resumenId <= 0) {
                continue;
            }

            if (!isset($agrupado[$resumenId])) {
                $agrupado[$resumenId] = [
                    'resumen_id' => $resumenId,
                    'titulo' => $row['titulo_articulo'] ?? 'Sin titulo',
                    'filas' => []
                ];
            }

            $comentario = '';
            if (!empty($row['argumento_rechazo'])) {
                $comentario = $row['argumento_rechazo'];
            } elseif (!empty($row['observaciones_generales'])) {
                $comentario = $row['observaciones_generales'];
            }

            $intento = (int)($row['version_intento'] ?? 0);
            $agrupado[$resumenId]['filas'][] = [
                'vuelta' => $intento > 0 ? ('Rev' . $intento) : 'Rev?',
                'revisor' => $row['nombre_revisor'] ?? '',
                'correo' => $row['correo_revisor'] ?? '',
                'estatus' => $row['estatus_evaluacion'] ?? 'Pendiente',
                'veredicto' => $row['veredicto'] ?? 'Pendiente',
                'comentario' => $comentario,
                'archivo_ruta' => $row['archivo_ruta'] ?? '',
                'archivo_tipo' => 'extensos'
            ];
        }

        foreach ($agrupado as $resumenId => &$item) {
            if (!empty($finalPorResumen[$resumenId])) {
                $item['filas'][] = [
                    'vuelta' => 'Version Final',
                    'revisor' => '-',
                    'correo' => '',
                    'estatus' => 'Entregada',
                    'veredicto' => '',
                    'comentario' => 'Documento final enviado por autor.',
                    'archivo_ruta' => $finalPorResumen[$resumenId],
                    'archivo_tipo' => 'extensos_finales'
                ];
            }
        }
        unset($item);

        return array_values($agrupado);
    }
}