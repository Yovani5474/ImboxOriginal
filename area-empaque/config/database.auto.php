<?php
/**
 * Configuración Automática de Base de Datos
 * Detecta automáticamente si está en local (XAMPP) o producción (InfinityFree)
 * 
 * INSTRUCCIÓN: Renombra este archivo a database.php
 */

require_once __DIR__ . '/config.php';

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port = 3306;
    private $conn;
    private $environment;

    public function __construct() {
        // Detectar automáticamente el entorno
        $this->detectEnvironment();
        
        if ($this->environment === 'production') {
            // Configuración para InfinityFree
            $this->host = 'sql303.infinityfree.com';
            $this->db_name = 'if0_40096200_control_almacen';
            $this->username = 'if0_40096200';
            $this->password = 'TazLBTRzaYzlV1O';
        } else {
            // Configuración para XAMPP Local
            $this->host = 'localhost';
            $this->db_name = 'control_almacen';
            $this->username = 'root';
            $this->password = ''; // Sin contraseña en XAMPP por defecto
        }
        
        // Permitir override desde variables de entorno
        if (getenv('DB_HOST')) $this->host = getenv('DB_HOST');
        if (getenv('DB_NAME')) $this->db_name = getenv('DB_NAME');
        if (getenv('DB_USER')) $this->username = getenv('DB_USER');
        if (getenv('DB_PASS')) $this->password = getenv('DB_PASS');
        if (getenv('DB_PORT')) $this->port = getenv('DB_PORT');
    }

    /**
     * Detecta automáticamente el entorno
     */
    private function detectEnvironment() {
        // Detectar si estamos en InfinityFree
        $server_name = $_SERVER['SERVER_NAME'] ?? '';
        $server_addr = $_SERVER['SERVER_ADDR'] ?? '';
        $http_host = $_SERVER['HTTP_HOST'] ?? '';
        
        // InfinityFree usa dominios .infinityfreeapp.com o .rf.gd
        if (
            strpos($server_name, '.infinityfreeapp.com') !== false ||
            strpos($server_name, '.rf.gd') !== false ||
            strpos($http_host, '.infinityfreeapp.com') !== false ||
            strpos($http_host, '.rf.gd') !== false ||
            strpos($server_name, '.epizy.com') !== false
        ) {
            $this->environment = 'production';
            return;
        }
        
        // Detectar localhost
        if (
            $server_name === 'localhost' ||
            $server_addr === '127.0.0.1' ||
            $server_addr === '::1' ||
            strpos($http_host, 'localhost') !== false
        ) {
            $this->environment = 'local';
            return;
        }
        
        // Por defecto: local
        $this->environment = 'local';
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_TIMEOUT => 10,
                    PDO::ATTR_PERSISTENT => false,
                ]
            );
            
            // Configurar zona horaria (Perú UTC-5)
            $this->conn->exec("SET time_zone = '-05:00'");
            
        } catch(PDOException $exception) {
            // Log del error
            $error_msg = sprintf(
                "[%s] Error de conexión BD (%s): %s",
                date('Y-m-d H:i:s'),
                $this->environment,
                $exception->getMessage()
            );
            error_log($error_msg);
            
            // Mensaje para el usuario
            $display_msg = "Error de conexión con la base de datos.";
            
            if ($this->environment === 'local') {
                $display_msg .= "<br><br><strong>Entorno Local Detectado</strong><br>";
                $display_msg .= "Asegúrate de que XAMPP esté iniciado y que exista la base de datos 'control_almacen'.<br>";
                $display_msg .= "Error técnico: " . $exception->getMessage();
            } else {
                $display_msg .= " Por favor, intente más tarde.";
            }
            
            die($display_msg);
        }
        
        return $this->conn;
    }
    
    /**
     * Obtener el entorno actual
     */
    public function getEnvironment() {
        return $this->environment;
    }
    
    /**
     * Obtener información de configuración (sin password)
     */
    public function getInfo() {
        return [
            'environment' => $this->environment,
            'host' => $this->host,
            'database' => $this->db_name,
            'username' => $this->username,
            'port' => $this->port
        ];
    }
    
    /**
     * Verificar conexión
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                $stmt = $conn->query("SELECT VERSION() as version");
                $version = $stmt->fetch();
                
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa',
                    'environment' => $this->environment,
                    'host' => $this->host,
                    'database' => $this->db_name,
                    'mysql_version' => $version['version'] ?? 'N/A'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión',
                'environment' => $this->environment,
                'error' => $e->getMessage()
            ];
        }
    }
}

// Función global para obtener conexión (retrocompatibilidad)
function getDatabase() {
    $database = new Database();
    return $database->getConnection();
}

?>
