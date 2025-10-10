<?php

class Revision {
    public ?int $id;
    public int $resumen_id;
    public int $revisor_id;
    public string $veredicto; 
    public ?string $comentarios;
    public string $fecha_asignacion;
    public ?string $fecha_revision;

    private $pdo;

    public function __construct() {
        $this->pdo = Database::conectar();
    }

    /**
     * Guarda o actualiza una revisión.
     * Si un revisor abre un resumen pero no lo termina, se puede actualizar.
     * @return bool True si la operación es exitosa, false si no.
     */
    public function guardar(): bool {
        try {
   
            if (empty($this->id)) {
                $sql = "INSERT INTO revisiones (resumen_id, revisor_id, veredicto, comentarios) 
                        VALUES (:resumen_id, :revisor_id, :veredicto, :comentarios)";
            } else {
                $sql = "UPDATE revisiones SET 
                            veredicto = :veredicto, 
                            comentarios = :comentarios,
                            fecha_revision = CURRENT_TIMESTAMP
                        WHERE id = :id";
            }

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':veredicto', $this->veredicto);
            $stmt->bindParam(':comentarios', $this->comentarios);
            
            if (empty($this->id)) {
                $stmt->bindParam(':resumen_id', $this->resumen_id);
                $stmt->bindParam(':revisor_id', $this->revisor_id);
            } else {
                $stmt->bindParam(':id', $this->id);
            }
            
            return $stmt->execute();

        } catch (PDOException $e) {
            // error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Busca todas las revisiones asociadas a un resumen específico.
     * @param int $resumen_id El ID del resumen.
     * @return array Una lista de las revisiones de ese resumen.
     */
    public static function buscarPorResumen(int $resumen_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT r.*, u.nombre_completo as revisor_nombre 
                FROM revisiones r
                JOIN usuarios u ON r.revisor_id = u.id
                WHERE r.resumen_id = :resumen_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':resumen_id', $resumen_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cuenta cuántas revisiones tiene asignadas un resumen.
     * Útil para saber si un resumen ya alcanzó el límite de 3 revisores.
     * @param int $resumen_id El ID del resumen.
     * @return int El número de revisores asignados.
     */
    public static function contarPorResumen(int $resumen_id): int {
        $pdo = Database::conectar();
        $sql = "SELECT COUNT(*) FROM revisiones WHERE resumen_id = :resumen_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':resumen_id', $resumen_id);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Verifica si un revisor específico ya ha reclamado un resumen en particular.
     * Crucial para cumplir la regla de "un revisor solo puede evaluar un resumen una vez".
     * @param int $resumen_id El ID del resumen.
     * @param int $revisor_id El ID del revisor.
     * @return bool True si ya existe una revisión, false si no.
     */
    public static function existe(int $resumen_id, int $revisor_id): bool {
        $pdo = Database::conectar();
        $sql = "SELECT COUNT(*) FROM revisiones WHERE resumen_id = :resumen_id AND revisor_id = :revisor_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':resumen_id', $resumen_id);
        $stmt->bindParam(':revisor_id', $revisor_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
        /**
     * Busca un registro de revisión específico por el ID del resumen y del revisor.
     * @param int $resumen_id El ID del resumen.
     * @param int $revisor_id El ID del revisor.
     * @return mixed Los datos de la revisión o false si no se encuentra.
     */
    public static function buscarPorResumenYRevisor(int $resumen_id, int $revisor_id) {
            $pdo = Database::conectar();
            $sql = "SELECT * FROM revisiones WHERE resumen_id = :resumen_id AND revisor_id = :revisor_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':resumen_id', $resumen_id);
            $stmt->bindParam(':revisor_id', $revisor_id);
            $stmt->execute();
            return $stmt->fetch();
    }
    /**
 * Busca las revisiones que un revisor ha reclamado pero aún no ha completado.
 * @param int $revisor_id El ID del revisor.
 * @return array Una lista de las revisiones pendientes de ese revisor.
 */
public static function buscarAsignadasPorRevisor(int $revisor_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT r.*, res.titulo,
                (SELECT GROUP_CONCAT(rl.nombre_rol) FROM usuarios u
                 JOIN usuario_roles ur ON u.id = ur.usuario_id
                 JOIN roles rl ON ur.rol_id = rl.id
                 WHERE u.id = res.autor_id) as autor_roles
            FROM revisiones r
            JOIN resumenes res ON r.resumen_id = res.id
            WHERE r.revisor_id = :revisor_id AND r.veredicto = 'Pendiente'
            ORDER BY r.fecha_asignacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':revisor_id', $revisor_id);
    $stmt->execute();
    return $stmt->fetchAll();
}
/**
 * Elimina todas las revisiones asociadas a un resumen específico.
 * @param int $resumen_id El ID del resumen.
 * @return bool
 */
public static function eliminarPorResumenId(int $resumen_id): bool {
    $pdo = Database::conectar();
    $sql = "DELETE FROM revisiones WHERE resumen_id = :resumen_id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['resumen_id' => $resumen_id]);
}
/**
 * Busca las revisiones que un usuario ya ha completado (Aceptado/Rechazado).
 * @param int $revisor_id El ID del usuario (Coordinador de Area).
 * @return array Una lista de las revisiones completadas.
 */
public static function buscarCompletadasPorRevisor(int $revisor_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT r.*, res.titulo,
                (SELECT GROUP_CONCAT(rl.nombre_rol) FROM usuarios u
                 JOIN usuario_roles ur ON u.id = ur.usuario_id
                 JOIN roles rl ON ur.rol_id = rl.id
                 WHERE u.id = res.autor_id) as autor_roles
            FROM revisiones r
            JOIN resumenes res ON r.resumen_id = res.id
            WHERE r.revisor_id = :revisor_id AND r.veredicto != 'Pendiente'
            ORDER BY r.fecha_revision DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':revisor_id', $revisor_id);
    $stmt->execute();
    return $stmt->fetchAll();
}
}