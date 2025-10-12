<?php

class Resumen {
    public ?int $id;
    public int $autor_id;
    public string $titulo;
    public ?string $autor_principal;
    public ?string $coautores;
    public string $resumen_texto;
    public int $area_id;
    public string $estatus;
    public ?string $fecha_envio;
    public int $intento_envio;
    public ?string $adscripcion1;
    public ?string $adscripcion2;
    public ?string $palabras_clave;

    private $pdo;

    public function __construct() {
        $this->pdo = Database::conectar();
    }

    /**
     * Guarda un nuevo resumen o actualiza uno existente (borrador).
     * @return bool Devuelve true si la operación fue exitosa, false en caso contrario.
     */
public function guardar(): bool {
    try {
        if (empty($this->id)) {
            $sql = "INSERT INTO resumenes (autor_id, autor_principal, titulo, coautores, resumen_texto, area_id, estatus, intento_envio, fecha_envio, palabras_clave, adscripcion1, adscripcion2) 
                    VALUES (:autor_id, :autor_principal, :titulo, :coautores, :resumen_texto, :area_id, :estatus, :intento_envio, :fecha_envio, :palabras_clave, :adscripcion1, :adscripcion2)";
            
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':autor_id', $this->autor_id);

        } else {
            $sql = "UPDATE resumenes SET 
                        autor_principal = :autor_principal, 
                        titulo = :titulo, 
                        coautores = :coautores, 
                        resumen_texto = :resumen_texto, 
                        area_id = :area_id, 
                        estatus = :estatus,
                        intento_envio = :intento_envio, 
                        fecha_envio = :fecha_envio,
                        palabras_clave = :palabras_clave,
                        adscripcion1 = :adscripcion1,
                        adscripcion2 = :adscripcion2
                
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':id', $this->id);
        }

        $stmt->bindParam(':autor_principal', $this->autor_principal);
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':coautores', $this->coautores);
        $stmt->bindParam(':resumen_texto', $this->resumen_texto);
        $stmt->bindParam(':area_id', $this->area_id);
        $stmt->bindParam(':estatus', $this->estatus);
        $stmt->bindParam(':intento_envio', $this->intento_envio);
        $stmt->bindParam(':fecha_envio', $this->fecha_envio);
        $stmt->bindParam(':palabras_clave', $this->palabras_clave);
        $stmt->bindParam(':adscripcion1', $this->adscripcion1);
        $stmt->bindParam(':adscripcion2', $this->adscripcion2);

        if (empty($this->id)) {
            $stmt->bindParam(':autor_id', $this->autor_id);
        } else {
            $stmt->bindParam(':id', $this->id);
        }
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

    /**
     * Busca todos los resúmenes de un autor específico.
     * @param int $autor_id El ID del autor.
     * @return array Un array con los resúmenes del autor.
     */
    public static function buscarPorAutor(int $autor_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT * FROM resumenes WHERE autor_id = :autor_id ORDER BY fecha_ultima_modificacion DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':autor_id', $autor_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca un resumen específico por su ID.
     * @param int $id El ID del resumen.
     * @return mixed Los datos del resumen o false si no se encuentra.
     */
    public static function buscarPorId(int $id) {
        $pdo = Database::conectar();
        $sql = "SELECT * FROM resumenes WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Busca resúmenes disponibles para ser revisados en un área específica.
     * Útil para la vista de coordinadores y revisores.
     * @param int $area_id El ID del área temática.
     * @return array Un array con los resúmenes pendientes.
     */
    public static function buscarPendientesPorArea(int $area_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT * FROM resumenes WHERE area_id = :area_id AND estatus = 'Pendiente de Asignacion'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':area_id', $area_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
/**
 * Busca resúmenes disponibles para ser revisados en un área específica,
 * que tengan menos de 3 revisores asignados.
 * @param int $area_id El ID del área temática.
 * @param int $revisor_id El ID del revisor para excluir resúmenes que ya ha reclamado.
 * @return array Una lista de resúmenes disponibles.
 */
public static function buscarDisponiblesPorArea(int $area_id, int $revisor_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT r.* FROM resumenes r
            LEFT JOIN (SELECT resumen_id, COUNT(*) as num_revisiones FROM revisiones GROUP BY resumen_id) as rev_counts
            ON r.id = rev_counts.resumen_id
            WHERE r.area_id = :area_id
            AND r.estatus = 'En Revision'
            AND (rev_counts.num_revisiones IS NULL OR rev_counts.num_revisiones < 1)
            AND r.id NOT IN (SELECT resumen_id FROM revisiones WHERE revisor_id = :revisor_id)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':area_id', $area_id);
    $stmt->bindParam(':revisor_id', $revisor_id);
    $stmt->execute();
    return $stmt->fetchAll();
}
/**
 * Actualiza directamente el estatus de un resumen.
 * @param int $resumen_id El ID del resumen.
 * @param string $nuevo_estatus El nuevo estatus ('Aceptado' o 'Rechazado').
 * @return bool
 */
public static function actualizarEstatus(int $resumen_id, string $nuevo_estatus): bool {
    $pdo = Database::conectar();
    $sql_update = "UPDATE resumenes SET estatus = :estatus WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    return $stmt_update->execute(['estatus' => $nuevo_estatus, 'id' => $resumen_id]);
}
/**
 * Cuenta los resúmenes agrupados por su estatus.
 * @return array Un array con el conteo por cada estatus.
 */
public static function contarPorEstatus(): array {
    $pdo = Database::conectar();
    $sql = "SELECT estatus, COUNT(*) as total FROM resumenes GROUP BY estatus";
    $stmt = $pdo->query($sql);
    // PDO::FETCH_KEY_PAIR crea un array asociativo ['Estatus' => total]
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

/**
 * Obtiene todos los resúmenes con detalles del autor, área y revisores asignados.
 * @return array Una lista de todos los resúmenes.
 */
public static function obtenerTodosConDetalles(): array {
    $pdo = Database::conectar();
    $sql = "SELECT 
                r.*, 
                u.nombre_completo as autor_nombre, 
                a.nombre_area,
                (SELECT GROUP_CONCAT(rl.nombre_rol SEPARATOR ', ') 
                 FROM usuario_roles ru 
                 JOIN roles rl ON ru.rol_id = rl.id 
                 WHERE ru.usuario_id = r.autor_id) as roles,
                GROUP_CONCAT(rev_user.nombre_completo SEPARATOR ', ') as revisores_asignados
            FROM resumenes r
            JOIN usuarios u ON r.autor_id = u.id
            JOIN areas_tematicas a ON r.area_id = a.id
            LEFT JOIN revisiones rev ON r.id = rev.resumen_id
            LEFT JOIN usuarios rev_user ON rev.revisor_id = rev_user.id
            GROUP BY r.id
            ORDER BY r.id DESC";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}
/**
 * Cambia el estatus de un resumen a 'Pendiente de Asignacion'.
 * @param int $resumen_id El ID del resumen.
 * @return bool
 */
public static function devolverACoordinador(int $resumen_id): bool {
    $pdo = Database::conectar();
    $sql = "UPDATE resumenes SET estatus = 'Pendiente de Asignacion' WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $resumen_id]);
}
/**
 * Cuenta los resúmenes agrupados por área temática.
 * @return array Un array con el conteo por cada área.
 */
public static function contarPorArea(): array {
    $pdo = Database::conectar();
    $sql = "SELECT a.nombre_area, COUNT(r.id) as total
            FROM areas_tematicas a
            LEFT JOIN resumenes r ON a.id = r.area_id
            GROUP BY a.id
            ORDER BY a.nombre_area ASC";
    $stmt = $pdo->query($sql);
    // Devuelve un array asociativo ['Nombre del Área' => total]
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}
/**
 * Gets the full details of a single summary by its ID.
 * @param int $id The ID of the summary.
 * @return mixed Array with summary details or false.
 */
public static function obtenerDetallesPorId(int $id) {
    $pdo = Database::conectar();
    $sql = "SELECT
                r.*,
                u.nombre_completo as autor_nombre,
                a.nombre_area,
                (SELECT GROUP_CONCAT(rl.nombre_rol) FROM usuario_roles ur JOIN roles rl ON ur.rol_id = rl.id WHERE ur.usuario_id = u.id) as autor_roles
            FROM resumenes r
            JOIN usuarios u ON r.autor_id = u.id
            JOIN areas_tematicas a ON r.area_id = a.id
            WHERE r.id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}
}