<?php
/**
 * Configuración Unificada - Sistema IMBOX
 * Base de datos MySQL compartida entre todos los módulos
 */

// Configuración de la base de datos MySQL unificada
define('DB_HOST', 'localhost');
define('DB_NAME', 'imbox_sistema_unificado');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// API Key para comunicación entre módulos
define('API_KEY', getenv('API_KEY') ?: '1c810efe778ea94df3578a92e7ed6f9dfa28621cfa67944e2535e8460d05e255');

// URLs de los diferentes módulos
define('URL_ADMIN', 'http://localhost/3');
define('URL_ALMACEN1', 'http://localhost/1');
define('URL_ALMACEN2', 'http://localhost/2');

// API Endpoints
define('API_TRANSFERENCIAS', 'http://localhost/2/api/transferencias.php');
define('API_ADMIN', 'http://localhost/3/api');

// Configuración de la aplicación
define('APP_NAME', 'IMBOX Sistema Unificado');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'America/Mexico_City');

// Establecer zona horaria
date_default_timezone_set(TIMEZONE);

/**
 * Función para obtener conexión PDO a MySQL
 */
function getDB() {
    static $db = null;
    
    if ($db === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $db = new PDO($dsn, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            error_log('Error de conexión MySQL: ' . $e->getMessage());
            
            if (isProduction()) {
                die('Error de configuración. Contacte al administrador.');
            } else {
                die('Error de conexión a base de datos: ' . $e->getMessage());
            }
        }
    }
    
    return $db;
}

/**
 * Clase Database singleton (compatible con sistema admin)
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
    
    private function __clone() {}
}

/**
 * Detectar si estamos en producción
 */
function isProduction() {
    return getenv('APP_ENV') === 'production' || 
           (isset($_SERVER['HTTP_HOST']) && 
            strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
}

/**
 * Función helper para escapar HTML
 */
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Función para hacer peticiones HTTP
 */
function httpRequest($url, $data = null, $method = 'GET', $headers = []) {
    $ch = curl_init();
    
    $defaultHeaders = [
        'Content-Type: application/json',
        'X-API-Key: ' . API_KEY
    ];
    
    $headers = array_merge($defaultHeaders, $headers);
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT' || $method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'code' => $httpCode,
        'data' => json_decode($response, true) ?: $response
    ];
}

/**
 * Función para registrar logs
 */
function logAction($nivel, $categoria, $mensaje, $contexto = []) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO logs_sistema (nivel, categoria, mensaje, contexto, usuario, ip_address, user_agent, url)
            VALUES (:nivel, :categoria, :mensaje, :contexto, :usuario, :ip, :user_agent, :url)
        ");
        
        $stmt->execute([
            'nivel' => $nivel,
            'categoria' => $categoria,
            'mensaje' => $mensaje,
            'contexto' => json_encode($contexto),
            'usuario' => $_SESSION['username'] ?? 'Sistema',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'url' => $_SERVER['REQUEST_URI'] ?? null
        ]);
    } catch (Exception $e) {
        error_log('Error al guardar log: ' . $e->getMessage());
    }
}
