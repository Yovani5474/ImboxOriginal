<?php
/**
 * Configuración de Base de Datos
 */

// Cargar variables de entorno
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// Cargar .env
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    loadEnv($envFile);
} else {
    loadEnv(dirname(__DIR__) . '/.env.example');
}

// Configuración de la base de datos unificada
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'imbox_sistema_unificado');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// Configuración de la aplicación
if (!defined('APP_NAME')) define('APP_NAME', getenv('APP_NAME') ?: 'Panel de Administración');
if (!defined('APP_URL')) define('APP_URL', getenv('APP_URL') ?: 'http://localhost/3');
if (!defined('TIMEZONE')) define('TIMEZONE', getenv('TIMEZONE') ?: 'America/Mexico_City');

// Establecer zona horaria
date_default_timezone_set(TIMEZONE);

/**
 * Clase Database para manejar conexiones PDO
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevenir clonación del objeto
    private function __clone() {}
}
