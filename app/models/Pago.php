<?php

class Pago {
    public ?int $id;
    public int $usuario_id;
    public float $monto;
    public string $tipo_pago;
    public string $comprobante_ruta;
    public string $estatus_pago;
    public ?int $revisor_pago_id;
    public ?string $fecha_revision_pago;

    private $pdo;

    public function __construct() {
        $this->pdo = Database::conectar();
    }

    /**
     * Guarda o actualiza un registro de pago.
     * Útil para cuando un usuario sube o reemplaza su comprobante.
     * @return bool True si la operación es exitosa, false si no.
     */
    public function guardar(): bool {
        try {
            $existente = self::buscarPorUsuarioId($this->usuario_id);

            if ($existente) {
                $sql = "UPDATE pagos SET 
                            monto = :monto,
                            tipo_pago = :tipo_pago,
                            comprobante_ruta = :comprobante_ruta,
                            estatus_pago = 'Pendiente'
                        WHERE usuario_id = :usuario_id";
            } else {
                $sql = "INSERT INTO pagos (usuario_id, monto, tipo_pago, comprobante_ruta, estatus_pago) 
                        VALUES (:usuario_id, :monto, :tipo_pago, :comprobante_ruta, :estatus_pago)";
            }

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':usuario_id', $this->usuario_id);
            $stmt->bindParam(':monto', $this->monto);
            $stmt->bindParam(':tipo_pago', $this->tipo_pago);
            $stmt->bindParam(':comprobante_ruta', $this->comprobante_ruta);

            if (!$existente) {
                $stmt->bindParam(':estatus_pago', $this->estatus_pago);
            }
            
            return $stmt->execute();

        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Busca el registro de pago de un usuario específico.
     * @param int $usuario_id El ID del usuario.
     * @return mixed Los datos del pago o false si no se encuentra.
     */
    public static function buscarPorUsuarioId(int $usuario_id) {
        $pdo = Database::conectar();
        $sql = "SELECT * FROM pagos WHERE usuario_id = :usuario_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Actualiza el estado de un pago (Aprobado/Rechazado) por un revisor.
     * @param int $pago_id El ID del pago a actualizar.
     * @param string $nuevo_estatus El nuevo estatus ('Aprobado' o 'Rechazado').
     * @param int $revisor_id El ID del usuario que está revisando el pago.
     * @return bool True si la actualización fue exitosa.
     */
    public static function actualizarEstatus(int $pago_id, string $nuevo_estatus, int $revisor_id, ?string $comentarios=null): bool {
        $pdo = Database::conectar();
        $sql = "UPDATE pagos SET 
                    estatus_pago = :estatus, 
                    revisor_pago_id = :revisor_id, 
                    fecha_revision_pago = CURRENT_TIMESTAMP,
                    comentarios_rechazo= :comentarios 
                WHERE id = :pago_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':estatus', $nuevo_estatus);
        $stmt->bindParam(':revisor_id', $revisor_id);
        $stmt->bindParam(':comentarios', $comentarios);
        $stmt->bindParam(':pago_id', $pago_id);
        
        return $stmt->execute();
    }
    

/**
 * Obtiene todos los pagos que están pendientes Y listos para revisión (ya tienen comprobante).
 * @return array Una lista de pagos pendientes.
 */
public static function obtenerPendientes(): array {
    $pdo = Database::conectar();
    $sql = "SELECT p.*, u.nombre_completo,
                (SELECT GROUP_CONCAT(rl.nombre_rol) FROM usuario_roles ur
                 JOIN roles rl ON ur.rol_id = rl.id WHERE ur.usuario_id = u.id) as roles
            FROM pagos p
            JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.estatus_pago = 'Pendiente' AND p.comprobante_ruta IS NOT NULL";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}
    /**
 * Crea un nuevo registro de pago para un resumen aceptado.
 */
public static function crearPagoParaResumen(int $resumen_id, int $autor_id,float $monto): bool {
    $pdo = Database::conectar();
    $stmt_check = $pdo->prepare("SELECT id FROM pagos WHERE resumen_id = :resumen_id");
    $stmt_check->execute(['resumen_id' => $resumen_id]);
    if ($stmt_check->fetch()) {
        return true; 
    }

    try {
        $sql = "INSERT INTO pagos (usuario_id, resumen_id, monto, tipo_pago, estatus_pago)
                VALUES (:usuario_id, :resumen_id, :monto, 'Publicacion de Resumen', 'Pendiente')";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $autor_id,
            'resumen_id' => $resumen_id,
            'monto' => $monto
        ]);
    } catch (PDOException $e) {
        error_log("Error al crear pago para resumen: " . $e->getMessage());
        return false;
    }
}
/**
 * Obtiene todos los pagos registrados en el sistema con detalles del usuario.
 * @return array Una lista de todos los pagos.
 */
public static function obtenerTodosConDetalles(): array {
    $pdo = Database::conectar();
    $sql = "SELECT p.*, u.nombre_completo 
            FROM pagos p
            JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.id DESC";
            
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Calcula las estadísticas de los pagos: conteos por estatus y monto total aprobado.
 * @return array Un array asociativo con las estadísticas.
 */
public static function obtenerEstadisticas(): array {
    $pdo = Database::conectar();
    $sql = "SELECT
                (SELECT COUNT(*) FROM pagos WHERE estatus_pago = 'Aprobado') as aprobados,
                (SELECT COUNT(*) FROM pagos WHERE estatus_pago = 'Rechazado') as rechazados,
                (SELECT COUNT(*) FROM pagos WHERE estatus_pago = 'Pendiente' AND comprobante_ruta IS NOT NULL) as en_revision,
                (SELECT COUNT(*) FROM pagos WHERE estatus_pago = 'Pendiente' AND comprobante_ruta IS NULL) as pendientes_sin_comprobante,
                (SELECT SUM(monto) FROM pagos WHERE estatus_pago = 'Aprobado') as total_recaudado";
    $stmt = $pdo->query($sql);
    return $stmt->fetch();
}
/**
 * Busca todos los pagos pendientes de un usuario específico.
 * @param int $usuario_id El ID del usuario.
 * @return array Una lista de los pagos pendientes.
 */
public static function obtenerPendientesPorUsuario(int $usuario_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT p.*, r.titulo as resumen_titulo 
            FROM pagos p
            LEFT JOIN resumenes r ON p.resumen_id = r.id
            WHERE p.usuario_id = :usuario_id AND p.estatus_pago = 'Pendiente' AND p.comprobante_ruta IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Actualiza un registro de pago existente con la ruta del comprobante.
 * También resetea el estatus a 'Pendiente' para una nueva revisión.
 * @param int $pago_id El ID del pago a actualizar.
 * @param string $nombreArchivo La ruta del archivo del comprobante.
 * @return bool True si la actualización fue exitosa.
 */
public static function registrarComprobante(int $pago_id, string $nombreArchivo): bool {
    $pdo = Database::conectar();
    
    $sql = "UPDATE pagos SET 
                comprobante_ruta = :ruta, 
                fecha_carga = CURRENT_TIMESTAMP,
                estatus_pago = 'Pendiente',
                revisor_pago_id = NULL,
                fecha_revision_pago = NULL,
                comentarios_rechazo = NULL
            WHERE id = :pago_id";
            
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['ruta' => $nombreArchivo, 'pago_id' => $pago_id]);
}

/**
 * Crea un nuevo registro de pago de inscripción para un asistente.
 */
public static function crearPagoInscripcion(int $usuario_id, string $tipo_pago, float $monto): bool {
    try {
        $pdo = Database::conectar();
        $sql = "INSERT INTO pagos (usuario_id, monto, tipo_pago, estatus_pago)
                VALUES (:usuario_id, :monto, :tipo_pago, 'Pendiente')";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $usuario_id,
            'monto' => $monto,
            'tipo_pago' => $tipo_pago
        ]);
    } catch (PDOException $e) {
        error_log("Error al crear pago de inscripción: " . $e->getMessage());
        return false;
    }
}
/**
 * Busca TODOS los pagos de un usuario específico.
 * @param int $usuario_id El ID del usuario.
 * @return array Una lista de todos sus pagos.
 */
public static function obtenerPorUsuario(int $usuario_id): array {
    $pdo = Database::conectar();
    // La consulta obtiene todos los pagos, sin importar el estatus
    $sql = "SELECT p.*, r.titulo as resumen_titulo 
            FROM pagos p
            LEFT JOIN resumenes r ON p.resumen_id = r.id
            WHERE p.usuario_id = :usuario_id 
            ORDER BY p.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    return $stmt->fetchAll();
}
/**
 * Busca un pago específico por su ID.
 * @param int $id El ID del pago.
 * @return mixed Devuelve los datos del pago si se encuentra, o false si no.
 */
public static function buscarPorId(int $id) {
    $pdo = Database::conectar();
    $sql = "SELECT * FROM pagos WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch();
}
/**
 * Obtiene el historial de pagos ya procesados (Aprobados o Rechazados).
 * @return array Una lista de pagos procesados.
 */
/**
 * Obtiene el historial de pagos ya procesados (Aprobados o Rechazados).
 * @return array Una lista de pagos procesados con detalles del revisor.
 */
public static function obtenerHistorial(): array {
    $pdo = Database::conectar();
    $sql = "SELECT p.*, u.nombre_completo, rev.nombre_completo as revisor_nombre,
                (SELECT GROUP_CONCAT(rl.nombre_rol) FROM usuario_roles ur
                 JOIN roles rl ON ur.rol_id = rl.id WHERE ur.usuario_id = u.id) as roles
            FROM pagos p
            JOIN usuarios u ON p.usuario_id = u.id
            LEFT JOIN usuarios rev ON p.revisor_pago_id = rev.id
            WHERE p.estatus_pago IN ('Aprobado', 'Rechazado')
            ORDER BY p.fecha_revision_pago DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}
/**
 * Verifica si un usuario tiene al menos un pago pendiente que requiera acción.
 * @param int $usuario_id El ID del usuario.
 * @return bool True si tiene pagos pendientes sin comprobante, false si no.
 */
public static function tienePagosPendientes(int $usuario_id): bool {
    $pdo = Database::conectar();
    $sql = "SELECT COUNT(*) FROM pagos WHERE usuario_id = :usuario_id AND estatus_pago = 'Pendiente' AND comprobante_ruta IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $usuario_id]);
    return $stmt->fetchColumn() > 0;
}
/**
 * Verifica si un usuario tiene CUALQUIER registro de pago asociado.
 * @param int $usuario_id El ID del usuario.
 * @return bool True si tiene al menos un pago, false si no.
 */
public static function usuarioTienePagos(int $usuario_id): bool {
    $pdo = Database::conectar();
    $sql = "SELECT COUNT(*) FROM pagos WHERE usuario_id = :usuario_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $usuario_id]);
    return $stmt->fetchColumn() > 0;
}
/**
 * Obtiene estadísticas de pagos aprobados, agrupados por tipo de pago.
 * @return array
 */
public static function obtenerEstadisticasPorTipo(): array {
    $pdo = Database::conectar();
    $sql = "SELECT tipo_pago, COUNT(*) as cantidad, SUM(monto) as total
            FROM pagos 
            WHERE estatus_pago = 'Aprobado' 
            GROUP BY tipo_pago";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}
}