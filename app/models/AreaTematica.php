<?php

class AreaTematica {
    // Propiedades que mapean a la tabla 'areas_tematicas'
    public ?int $id;
    public string $nombre_area;
    public string $descripcion;

    private $pdo;

    public function __construct() {
        $this->pdo = Database::conectar();
    }

    /**
     * Obtiene todas las áreas temáticas de la base de datos.
     * Esencial para los formularios de registro de resúmenes.
     * @return array Una lista de todas las áreas.
     */
    public static function obtenerTodas(): array {
        $pdo = Database::conectar();
        $sql = "SELECT * FROM areas_tematicas ORDER BY nombre_area ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Busca un área temática específica por su ID.
     * @param int $id El ID del área.
     * @return mixed Los datos del área o false si no se encuentra.
     */
    public static function buscarPorId(int $id) {
        $pdo = Database::conectar();
        $sql = "SELECT * FROM areas_tematicas WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Guarda o actualiza un área temática.
     * Esta función será utilizada en el panel de administración.
     * @return bool True si la operación es exitosa.
     */
    public function guardar(): bool {
        try {
            if (empty($this->id)) {
                $sql = "INSERT INTO areas_tematicas (nombre_area, descripcion) VALUES (:nombre, :descripcion)";
            } else {
                $sql = "UPDATE areas_tematicas SET nombre_area = :nombre, descripcion = :descripcion WHERE id = :id";
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':nombre', $this->nombre_area);
            $stmt->bindParam(':descripcion', $this->descripcion);
            if (!empty($this->id)) {
                $stmt->bindParam(':id', $this->id);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            // error_log($e->getMessage());
            return false;
        }
    }
}