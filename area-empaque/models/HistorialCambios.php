<?php
/**
 * Modelo HistorialCambios
 * Registra todos los cambios realizados en transferencias
 */

require_once __DIR__ . '/../config/database.php';

class HistorialCambios {
    private $conn;
    private $table_name = 'historial_cambios';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        // No auto-crear tabla, debe crearse manualmente con el script
        // $this->crearTabla();
    }

    /**
     * Crear tabla si no existe (DESHABILITADO - usar script de creación)
     * La tabla debe crearse con: php scripts/recrear_tabla_historial.php
     */
    private function crearTabla() {
        // COMENTADO: Usar script en su lugar para evitar problemas de sintaxis SQLite vs MySQL
        /*
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            transferencia_id INTEGER NOT NULL,
            usuario VARCHAR(100),
            rol VARCHAR(50),
            accion VARCHAR(50) NOT NULL,
            campo_modificado VARCHAR(100),
            valor_anterior TEXT,
            valor_nuevo TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (transferencia_id) REFERENCES transferencias(id)
        )";
        
        try {
            $this->conn->exec($query);
        } catch (Exception $e) {
            error_log("Error creando tabla historial_cambios: " . $e->getMessage());
        }
        */
    }

    /**
     * Registrar un cambio
     */
    public function registrar($data) {
        $query = "INSERT INTO " . $this->table_name . " 
            (transferencia_id, usuario, rol, accion, campo_modificado, valor_anterior, valor_nuevo, ip_address, user_agent)
            VALUES (:transferencia_id, :usuario, :rol, :accion, :campo_modificado, :valor_anterior, :valor_nuevo, :ip_address, :user_agent)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':transferencia_id', $data['transferencia_id']);
        $stmt->bindParam(':usuario', $data['usuario']);
        $stmt->bindParam(':rol', $data['rol']);
        $stmt->bindParam(':accion', $data['accion']);
        $stmt->bindParam(':campo_modificado', $data['campo_modificado']);
        $stmt->bindParam(':valor_anterior', $data['valor_anterior']);
        $stmt->bindParam(':valor_nuevo', $data['valor_nuevo']);
        $stmt->bindParam(':ip_address', $data['ip_address']);
        $stmt->bindParam(':user_agent', $data['user_agent']);
        
        return $stmt->execute();
    }

    /**
     * Registrar cambio en transferencia (comparar valores)
     */
    public function registrarCambioTransferencia($transferencia_id, $datos_anteriores, $datos_nuevos, $usuario = 'sistema', $rol = 'operador') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Comparar campos
        $campos_comparar = ['referencia', 'total_items', 'tipo_prenda', 'color', 'talla', 'trabajador_id', 'trabajador_nombre', 'estado', 'observaciones'];
        
        foreach ($campos_comparar as $campo) {
            $valor_anterior = $datos_anteriores[$campo] ?? null;
            $valor_nuevo = $datos_nuevos[$campo] ?? null;
            
            // Solo registrar si hay cambio
            if ($valor_anterior != $valor_nuevo) {
                $this->registrar([
                    'transferencia_id' => $transferencia_id,
                    'usuario' => $usuario,
                    'rol' => $rol,
                    'accion' => 'modificacion',
                    'campo_modificado' => $campo,
                    'valor_anterior' => $valor_anterior,
                    'valor_nuevo' => $valor_nuevo,
                    'ip_address' => $ip,
                    'user_agent' => $user_agent
                ]);
            }
        }
    }

    /**
     * Obtener historial de una transferencia
     */
    public function obtenerPorTransferencia($transferencia_id, $limit = 100) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE transferencia_id = :transferencia_id 
                  ORDER BY fecha_cambio DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':transferencia_id', $transferencia_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todo el historial (con filtros opcionales)
     */
    public function listar($filters = [], $limit = 100, $offset = 0) {
        $where_clauses = [];
        $params = [];
        
        if (!empty($filters['transferencia_id'])) {
            $where_clauses[] = "h.transferencia_id = :transferencia_id";
            $params[':transferencia_id'] = $filters['transferencia_id'];
        }
        
        if (!empty($filters['usuario'])) {
            $where_clauses[] = "h.usuario LIKE :usuario";
            $params[':usuario'] = '%' . $filters['usuario'] . '%';
        }
        
        if (!empty($filters['accion'])) {
            $where_clauses[] = "h.accion = :accion";
            $params[':accion'] = $filters['accion'];
        }
        
        if (!empty($filters['fecha_desde'])) {
            $where_clauses[] = "DATE(h.fecha_cambio) >= :fecha_desde";
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }
        
        if (!empty($filters['fecha_hasta'])) {
            $where_clauses[] = "DATE(h.fecha_cambio) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }
        
        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
        
        $query = "SELECT h.*, t.referencia as transferencia_ref
                  FROM " . $this->table_name . " h
                  LEFT JOIN transferencias t ON h.transferencia_id = t.id
                  $where_sql
                  ORDER BY h.fecha_cambio DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar registros (para paginación)
     */
    public function contar($filters = []) {
        $where_clauses = [];
        $params = [];
        
        if (!empty($filters['transferencia_id'])) {
            $where_clauses[] = "transferencia_id = :transferencia_id";
            $params[':transferencia_id'] = $filters['transferencia_id'];
        }
        
        if (!empty($filters['usuario'])) {
            $where_clauses[] = "usuario LIKE :usuario";
            $params[':usuario'] = '%' . $filters['usuario'] . '%';
        }
        
        if (!empty($filters['accion'])) {
            $where_clauses[] = "accion = :accion";
            $params[':accion'] = $filters['accion'];
        }
        
        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
        
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " " . $where_sql;
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }

    /**
     * Obtener estadísticas de cambios
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                    COUNT(*) as total_cambios,
                    COUNT(DISTINCT transferencia_id) as transferencias_modificadas,
                    COUNT(DISTINCT usuario) as usuarios_activos,
                    accion,
                    COUNT(*) as total_por_accion
                  FROM " . $this->table_name . "
                  GROUP BY accion";
        
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
