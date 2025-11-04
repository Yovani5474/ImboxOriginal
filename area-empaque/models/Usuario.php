<?php
/**
 * Modelo Usuario
 * Sistema de usuarios con roles y permisos
 */

require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $conn;
    private $table_name = 'usuarios';

    // Roles predefinidos
    const ROL_ADMIN = 'admin';
    const ROL_SUPERVISOR = 'supervisor';
    const ROL_OPERADOR = 'operador';
    const ROL_VISUALIZADOR = 'visualizador';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->crearTablas();
        $this->crearUsuariosDefault();
    }

    /**
     * Crear tablas si no existen
     */
    private function crearTablas() {
        // Tabla usuarios
        $query_usuarios = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            rol VARCHAR(50) NOT NULL DEFAULT 'operador',
            activo INTEGER DEFAULT 1,
            ultimo_acceso TIMESTAMP,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $this->conn->exec($query_usuarios);
        } catch (Exception $e) {
            error_log("Error creando tabla usuarios: " . $e->getMessage());
        }
    }

    /**
     * Crear usuarios por defecto
     */
    private function crearUsuariosDefault() {
        // Verificar si ya existen usuarios
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] == 0) {
            // Crear usuarios de ejemplo
            $usuarios_default = [
                [
                    'nombre' => 'Administrador',
                    'email' => 'admin@empaque.com',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'rol' => self::ROL_ADMIN
                ],
                [
                    'nombre' => 'Supervisor',
                    'email' => 'supervisor@empaque.com',
                    'password' => password_hash('super123', PASSWORD_DEFAULT),
                    'rol' => self::ROL_SUPERVISOR
                ],
                [
                    'nombre' => 'Operador',
                    'email' => 'operador@empaque.com',
                    'password' => password_hash('oper123', PASSWORD_DEFAULT),
                    'rol' => self::ROL_OPERADOR
                ]
            ];
            
            foreach ($usuarios_default as $usuario) {
                $this->crear($usuario);
            }
        }
    }

    /**
     * Crear usuario
     */
    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " 
            (nombre, email, password, rol, activo)
            VALUES (:nombre, :email, :password, :rol, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':rol', $data['rol']);
        $activo = $data['activo'] ?? 1;
        $stmt->bindParam(':activo', $activo);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Autenticar usuario
     */
    public function autenticar($email, $password) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE email = :email AND activo = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            // Actualizar último acceso
            $this->actualizarUltimoAcceso($usuario['id']);
            
            // Guardar en sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            
            return $usuario;
        }
        
        return false;
    }

    /**
     * Actualizar último acceso
     */
    private function actualizarUltimoAcceso($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET ultimo_acceso = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos() {
        $query = "SELECT id, nombre, email, rol, activo, ultimo_acceso, fecha_creacion 
                  FROM " . $this->table_name . " 
                  ORDER BY nombre";
        
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT id, nombre, email, rol, activo, ultimo_acceso, fecha_creacion 
                  FROM " . $this->table_name . " 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar permiso
     */
    public static function tienePermiso($accion) {
        if (!isset($_SESSION['usuario_rol'])) {
            return false;
        }
        
        $rol = $_SESSION['usuario_rol'];
        $permisos = self::obtenerPermisos($rol);
        
        return in_array($accion, $permisos);
    }

    /**
     * Obtener permisos según rol
     */
    public static function obtenerPermisos($rol) {
        $permisos = [
            self::ROL_ADMIN => [
                'ver_transferencias',
                'crear_transferencias',
                'editar_transferencias',
                'eliminar_transferencias',
                'procesar_recepcion',
                'ver_historial',
                'gestionar_usuarios',
                'exportar_reportes',
                'ver_trabajadores',
                'editar_trabajadores'
            ],
            self::ROL_SUPERVISOR => [
                'ver_transferencias',
                'crear_transferencias',
                'editar_transferencias',
                'procesar_recepcion',
                'ver_historial',
                'exportar_reportes',
                'ver_trabajadores',
                'editar_trabajadores'
            ],
            self::ROL_OPERADOR => [
                'ver_transferencias',
                'procesar_recepcion',
                'ver_trabajadores'
            ],
            self::ROL_VISUALIZADOR => [
                'ver_transferencias',
                'ver_trabajadores'
            ]
        ];
        
        return $permisos[$rol] ?? [];
    }

    /**
     * Obtener descripción del rol
     */
    public static function obtenerDescripcionRol($rol) {
        $descripciones = [
            self::ROL_ADMIN => 'Administrador - Acceso total',
            self::ROL_SUPERVISOR => 'Supervisor - Gestión completa',
            self::ROL_OPERADOR => 'Operador - Procesamiento básico',
            self::ROL_VISUALIZADOR => 'Visualizador - Solo lectura'
        ];
        
        return $descripciones[$rol] ?? 'Desconocido';
    }

    /**
     * Cerrar sesión
     */
    public static function cerrarSesion() {
        session_destroy();
    }

    /**
     * Verificar si hay sesión activa
     */
    public static function sesionActiva() {
        // ⚠️ LOGIN DESACTIVADO TEMPORALMENTE
        // Simular sesión activa para desarrollo
        if (!isset($_SESSION['usuario_id'])) {
            $_SESSION['usuario_id'] = 1;
            $_SESSION['usuario_nombre'] = 'CRISTIAN';
            $_SESSION['usuario_email'] = 'cristian@imbox.local';
            $_SESSION['usuario_rol'] = 'admin';
        }
        return true;
        // return isset($_SESSION['usuario_id']);
    }

    /**
     * Obtener usuario actual
     */
    public static function usuarioActual() {
        self::sesionActiva(); // Asegurar que la sesión esté inicializada
        return [
            'id' => $_SESSION['usuario_id'] ?? 1,
            'nombre' => $_SESSION['usuario_nombre'] ?? 'CRISTIAN',
            'email' => $_SESSION['usuario_email'] ?? 'cristian@imbox.local',
            'rol' => $_SESSION['usuario_rol'] ?? 'admin'
        ];
    }
}
?>
