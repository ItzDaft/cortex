<?php

class Usuario {
    public ?int $id; 
    public string $nombre_completo;
    public string $correo;
    public string $contrasena;
    public ?string $institucion_procedencia; 
    public ?int $area_id=null; 
    private $pdo;

    public function __construct() {
        $this->pdo = Database::conectar();
    }

    /**
     * Guarda un nuevo usuario en la base de datos.
     * Incluye el hasheo de la contraseña por seguridad.
     * @return bool Devuelve true si se guardó correctamente, false en caso contrario.
     */
    public function guardar(): bool {
        try {
            $hashedPassword = password_hash($this->contrasena, PASSWORD_BCRYPT);

            $sql = "INSERT INTO usuarios (nombre_completo, correo, contrasena, institucion_procedencia,area_id) 
                    VALUES (:nombre, :correo, :pass, :institucion, :area_id)";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':nombre', $this->nombre_completo);
            $stmt->bindParam(':correo', $this->correo);
            $stmt->bindParam(':pass', $hashedPassword);
            $stmt->bindParam(':institucion', $this->institucion_procedencia);
            $stmt->bindParam(':area_id', $this->area_id);

            return $stmt->execute();

        } catch (PDOException $e) {
             error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Busca un usuario por su dirección de correo electrónico.
     * Es un método estático para poder llamarlo sin crear un objeto Usuario.
     * @param string $correo El correo a buscar.
     * @return mixed Devuelve los datos del usuario si lo encuentra, o false si no.
     */
    public static function buscarPorCorreo(string $correo) {
        $pdo = Database::conectar();
        $sql = "SELECT * FROM usuarios WHERE correo = :correo";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        return $stmt->fetch(); 
    }
        /**
     * Asigna un rol específico a un usuario.
     * @param int $usuario_id El ID del usuario.
     * @param int $rol_id El ID del rol a asignar.
     * @return bool True si la asignación fue exitosa.
     */
    public static function asignarRol(int $usuario_id, int $rol_id): bool {
        try {
            $pdo = Database::conectar();
            $sql = "INSERT INTO usuario_roles (usuario_id, rol_id) VALUES (:usuario_id, :rol_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':rol_id', $rol_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    /**
     * Obtiene los nombres de los roles de un usuario específico.
     * @param int $usuario_id El ID del usuario.
     * @return array Una lista con los nombres de los roles del usuario.
     */
    public static function obtenerRoles(int $usuario_id): array {
        $pdo = Database::conectar();
        $sql = "SELECT r.nombre_rol 
                FROM usuario_roles ur
                JOIN roles r ON ur.rol_id = r.id
                WHERE ur.usuario_id = :usuario_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        
        // fetchAll(PDO::FETCH_COLUMN) devuelve un array plano con los valores de una sola columna.
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 
    }

/**
 * Busca un usuario específico por su ID y obtiene sus roles.
 * @param int $id El ID del usuario.
 * @return mixed Los datos del usuario (incluyendo roles) o false si no se encuentra.
 */
public static function buscarPorId(int $id) {
    $pdo = Database::conectar();
    $sql = "SELECT u.*, GROUP_CONCAT(r.nombre_rol SEPARATOR ', ') as roles
            FROM usuarios u
            LEFT JOIN usuario_roles ur ON u.id = ur.usuario_id
            LEFT JOIN roles r ON ur.rol_id = r.id
            WHERE u.id = :id
            GROUP BY u.id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch();
}


/**
 * Obtiene todos los usuarios con sus roles.
 * @return array Una lista de todos los usuarios.
 */
public static function obtenerTodos(): array {
    $pdo = Database::conectar();
    $sql = "SELECT u.*, GROUP_CONCAT(r.nombre_rol SEPARATOR ', ') as roles
            FROM usuarios u
            LEFT JOIN usuario_roles ur ON u.id = ur.usuario_id
            LEFT JOIN roles r ON ur.rol_id = r.id
            GROUP BY u.id";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Actualiza los datos de un usuario existente.
 * @param int $id El ID del usuario a actualizar.
 * @param array $datos Un array asociativo con los datos a cambiar.
 * @return bool True si la actualización fue exitosa.
 */
public static function actualizar(int $id, array $datos): bool {
    $pdo = Database::conectar();
    $sql = "UPDATE usuarios SET nombre_completo = :nombre, correo = :correo, institucion_procedencia = :institucion, area_id = :area_id WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'nombre' => $datos['nombre_completo'],
        'correo' => $datos['correo'],
        'institucion' => $datos['institucion_procedencia'],
        'area_id' => $datos['area_id'],
        'id' => $id
    ]);
}

/**
 * Elimina un usuario de la base de datos.
 * @param int $id El ID del usuario a eliminar.
 * @return bool True si la eliminación fue exitosa.
 */
public static function eliminar(int $id): bool {
    $pdo = Database::conectar();
    $sql = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $id]);
}
/**
 * Realiza una eliminación lógica de un usuario (lo desactiva).
 * @param int $id El ID del usuario a desactivar.
 * @return bool True si la operación fue exitosa.
 */
public static function eliminarLogico(int $id): bool {
    $pdo = Database::conectar();
    $sql = "UPDATE usuarios SET activo = 0 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $id]);
}
/**
 * Reactiva un usuario que fue desactivado lógicamente.
 * @param int $id El ID del usuario a reactivar.
 * @return bool True si la operación fue exitosa.
 */
public static function reactivar(int $id): bool {
    $pdo = Database::conectar();
    $sql = "UPDATE usuarios SET activo = 1 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $id]);
}
/**
 * Actualiza los roles de un usuario. Borra los anteriores e inserta los nuevos.
 * @param int $usuario_id El ID del usuario.
 * @param array $roles_ids Un array de IDs de los nuevos roles.
 * @return bool True si la operación fue exitosa.
 */
public static function actualizarRoles(int $usuario_id, array $roles_ids): bool {
    $pdo = Database::conectar();
    try {
        $pdo->beginTransaction();
        $stmt_delete = $pdo->prepare("DELETE FROM usuario_roles WHERE usuario_id = :usuario_id");
        $stmt_delete->execute(['usuario_id' => $usuario_id]);

        $stmt_insert = $pdo->prepare("INSERT INTO usuario_roles (usuario_id, rol_id) VALUES (:usuario_id, :rol_id)");
        foreach ($roles_ids as $rol_id) {
            $stmt_insert->execute(['usuario_id' => $usuario_id, 'rol_id' => (int)$rol_id]);
        }
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}
/**
 * Cambia la contraseña de un usuario específico.
 * @param int $id El ID del usuario.
 * @param string $nuevaContrasena La nueva contraseña (sin hashear).
 * @return bool True si el cambio fue exitoso.
 */
public static function cambiarContrasena(int $id, string $nuevaContrasena): bool {
    $pdo = Database::conectar();
    $hashedPassword = password_hash($nuevaContrasena, PASSWORD_BCRYPT);
    $sql = "UPDATE usuarios SET contrasena = :pass WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['pass' => $hashedPassword, 'id' => $id]);
}
/**
 * Actualiza la contraseña de un usuario basado en su email.
 */
public static function actualizarContrasenaPorEmail(string $email, string $nuevaContrasena): bool {
    $pdo = Database::conectar();
    $hashedPassword = password_hash($nuevaContrasena, PASSWORD_BCRYPT);
    $sql = "UPDATE usuarios SET contrasena = :pass WHERE correo = :email";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['pass' => $hashedPassword, 'email' => $email]);
}
/**
 * Obtiene los IDs de los roles de un usuario específico.
 * @param int $usuario_id El ID del usuario.
 * @return array Una lista con los IDs de los roles.
 */
public static function obtenerIdsRoles(int $usuario_id): array {
    $pdo = Database::conectar();
    $sql = "SELECT rol_id FROM usuario_roles WHERE usuario_id = :usuario_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
/**
 * Busca un Coordinador de Area disponible de un área específica con la menor cantidad de revisiones asignadas.
 * @param int $area_id El ID del área.
 * @return mixed Array con los datos del usuario o false si no hay ninguno.
 */
public static function buscarRevisorDisponiblePorArea(int $area_id) {
    $pdo = Database::conectar();
    // Esta consulta busca al usuario con el rol 'Coordinador de Area' en el área especificada
    // que tenga la menor cantidad de revisiones pendientes, y lo selecciona.
    $sql = "SELECT u.id, u.nombre_completo, COUNT(r.id) as revisiones_pendientes
            FROM usuarios u
            JOIN usuario_roles ur ON u.id = ur.usuario_id
            JOIN roles rl ON ur.rol_id = rl.id
            LEFT JOIN revisiones r ON u.id = r.revisor_id AND r.veredicto = 'Pendiente'
            WHERE u.area_id = :area_id AND rl.nombre_rol = 'Coordinador de Area' AND u.activo = 1
            GROUP BY u.id
            ORDER BY revisiones_pendientes ASC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['area_id' => $area_id]);
    return $stmt->fetch();
}
/**
 * Guarda o actualiza el perfil detallado de un Revisor de Extensos.
 * @param array $datos Los datos del perfil a guardar.
 * @return bool True si la operación fue exitosa.
 */
    /**
     * Guarda o actualiza el perfil detallado de un Revisor de Extensos.
     * MEJORA: Utiliza COALESCE en el UPDATE para no borrar archivos si no se suben nuevos.
     */
    public static function guardarPerfilRevisorExtenso(array $datos, int $area_id): bool {
        $pdo = Database::conectar();
        try {
            $pdo->beginTransaction();
            
            $sql_perfil = "INSERT INTO revisores_extensos_perfil 
                                (usuario_id, grado_academico, afiliacion_institucional, cargo_actual, area_especialidad, orcid, google_scholar_id, comprobante_sni_ruta, foto_ruta, acepta_terminos)
                            VALUES
                                (:usuario_id, :grado_academico, :afiliacion_institucional, :cargo_actual, :area_especialidad, :orcid, :google_scholar_id, :comprobante_sni_ruta, :foto_ruta, :acepta_terminos) AS new_perfil
                            ON DUPLICATE KEY UPDATE
                                grado_academico = new_perfil.grado_academico,
                                afiliacion_institucional = new_perfil.afiliacion_institucional,
                                cargo_actual = new_perfil.cargo_actual,
                                area_especialidad = new_perfil.area_especialidad,
                                orcid = new_perfil.orcid,
                                google_scholar_id = new_perfil.google_scholar_id,
                                comprobante_sni_ruta = COALESCE(new_perfil.comprobante_sni_ruta, revisores_extensos_perfil.comprobante_sni_ruta),
                                foto_ruta = COALESCE(new_perfil.foto_ruta, revisores_extensos_perfil.foto_ruta),
                                acepta_terminos = new_perfil.acepta_terminos";
            
            $stmt_perfil = $pdo->prepare($sql_perfil);
            $stmt_perfil->execute($datos);

            $sql_usuario = "UPDATE usuarios SET area_id = :area_id WHERE id = :usuario_id";
            $stmt_usuario = $pdo->prepare($sql_usuario);
            $stmt_usuario->execute(['area_id' => $area_id, 'usuario_id' => $datos['usuario_id']]);

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el perfil detallado de un Revisor de Extensos, sin modificar el área.
     * @param array $datos Los datos del perfil a actualizar.
     * @return bool True si la operación fue exitosa.
     */
    public static function actualizarPerfilRevisorExtenso(array $datos): bool {
        $pdo = Database::conectar();
        try {
            $sql = "UPDATE revisores_extensos_perfil SET
                        grado_academico = :grado_academico,
                        afiliacion_institucional = :afiliacion_institucional,
                        cargo_actual = :cargo_actual,
                        area_especialidad = :area_especialidad,
                        orcid = :orcid,
                        google_scholar_id = :google_scholar_id,
                        comprobante_sni_ruta = COALESCE(:comprobante_sni_ruta, comprobante_sni_ruta),
                        foto_ruta = COALESCE(:foto_ruta, foto_ruta)
                    WHERE usuario_id = :usuario_id";
            
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el perfil detallado de un revisor (Útil para llenar el formulario de edición).
     */
    public static function obtenerPerfilRevisorExtenso(int $usuario_id) {
        $pdo = Database::conectar();
        $sql = "SELECT p.*, u.area_id 
                FROM revisores_extensos_perfil p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.usuario_id = :usuario_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
        return $stmt->fetch();
    }

/**
 * Verifica si un Revisor de Extensos ya ha completado su perfil.
 * @param int $usuario_id El ID del usuario.
 * @return bool True si el perfil está completo.
 */
    public static function perfilRevisorEstaCompleto(int $usuario_id): bool {
    $pdo = Database::conectar();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM revisores_extensos_perfil WHERE usuario_id = :usuario_id AND acepta_terminos = 1");
    $stmt->execute(['usuario_id' => $usuario_id]);
    return $stmt->fetchColumn() > 0;
}
/**
 * Busca Revisores de Extensos de un área específica con su perfil y carga de trabajo.
 */
    public static function buscarRevisoresExtensosPorArea(int $area_id): array {
        $pdo = Database::conectar();
        
        $sql = "SELECT 
                    u.id, 
                    u.nombre_completo, 
                    u.correo,
                    p.grado_academico, 
                    p.area_especialidad, 
                    p.foto_ruta, 
                    p.comprobante_sni_ruta,
                    (SELECT COUNT(DISTINCT ev.extenso_id) 
                     FROM evaluaciones_extensos ee 
                     JOIN extenso_versiones ev ON ee.extenso_version_id = ev.id
                     WHERE ee.revisor_id = u.id 
                     AND ee.estatus_evaluacion NOT IN ('Validada')
                    ) as carga_actual
                FROM usuarios u
                JOIN usuario_roles ur ON u.id = ur.usuario_id
                JOIN roles r ON ur.rol_id = r.id
                LEFT JOIN revisores_extensos_perfil p ON u.id = p.usuario_id
                WHERE r.nombre_rol = 'Revisor de Extensos' 
                AND u.area_id = :area_id 
                AND u.activo = 1
                ORDER BY u.nombre_completo";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['area_id' => $area_id]);
        return $stmt->fetchAll();
    }
/**
 * Busca todos los usuarios que pertenecen a una lista de roles.
 * @param array $nombresRoles Array con los nombres de los roles a buscar.
 * @return array Lista de usuarios con su nombre y correo.
 */
public static function buscarPorRoles(array $nombresRoles): array {
    $pdo = Database::conectar();
    // Se crea una cadena de placeholders (?,?,?) para la consulta IN
    $placeholders = implode(',', array_fill(0, count($nombresRoles), '?'));

    $sql = "SELECT u.nombre_completo, u.correo FROM usuarios u
            JOIN usuario_roles ur ON u.id = ur.usuario_id
            JOIN roles r ON ur.rol_id = r.id
            WHERE r.nombre_rol IN ($placeholders)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($nombresRoles);
    return $stmt->fetchAll();
}
/**
     * Obtiene todos los usuarios que tengan un rol específico.
     * @param int $rol_id El ID del rol a filtrar.
     * @return array Un array de objetos Usuario.
     */
    public static function obtenerPorRol(int $rol_id): array {
        $pdo = Database::conectar();
        // Hacemos JOIN con la tabla pivote usuario_roles para filtrar por el rol_id
        $sql = "SELECT u.* FROM usuarios u 
                JOIN usuario_roles ur ON u.id = ur.usuario_id 
                WHERE ur.rol_id = :rol_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':rol_id' => $rol_id]);
        
        // Devolvemos objetos de la clase Usuario directamente
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    /**
     * Actualiza la contraseña de la instancia actual del usuario.
     * @param string $nuevaContrasena La nueva contraseña en texto plano.
     * @return bool True si la actualización fue exitosa.
     */
    public function actualizarContrasena(string $nuevaContrasena): bool {
        if (!$this->id) {
            return false; // No se puede actualizar sin un ID
        }
        
        try {
            $pdo = Database::conectar();
            $hashedPassword = password_hash($nuevaContrasena, PASSWORD_BCRYPT);
            
            $sql = "UPDATE usuarios SET contrasena = :pass WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            return $stmt->execute([
                ':pass' => $hashedPassword, 
                ':id' => $this->id
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }
}