<?php
require_once __DIR__ . '/../config/database.php';

class Trabajador {
    private $conn;
    private $table_name = 'trabajadores';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function obtenerTodos() {
        // Verificar primero qué columnas existen
        try {
            $query = "SELECT id, nombre, telefono, email, especialidad, activo, fecha_creacion FROM " . $this->table_name . " ORDER BY nombre";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si falla (posiblemente por columna especialidad inexistente), intentar sin ella
            $query = "SELECT id, nombre, telefono, email, activo, fecha_creacion, '' as especialidad FROM " . $this->table_name . " ORDER BY nombre";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function obtenerPorId($id) {
        try {
            $query = "SELECT id, nombre, telefono, email, especialidad, activo, fecha_creacion FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si falla, intentar sin especialidad
            $query = "SELECT id, nombre, telefono, email, activo, fecha_creacion, '' as especialidad FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    public function crear($data) {
        // Intentar primero con especialidad
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (codigo, nombre, telefono, email, especialidad, activo) 
                      VALUES (:codigo, :nombre, :telefono, :email, :especialidad, :activo)";
            
            $stmt = $this->conn->prepare($query);
            
            // Generar código automático si no existe
            $codigo = $data['codigo'] ?? 'TRAB-' . time();
            $nombre = $data['nombre'] ?? '';
            $telefono = $data['telefono'] ?? '';
            $email = $data['email'] ?? '';
            $especialidad = $data['especialidad'] ?? '';
            $activo = $data['activo'] ?? 1;
            
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':especialidad', $especialidad);
            $stmt->bindParam(':activo', $activo);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            // Si falla, intentar sin especialidad
            $query = "INSERT INTO " . $this->table_name . " 
                      (codigo, nombre, telefono, email, activo) 
                      VALUES (:codigo, :nombre, :telefono, :email, :activo)";
            
            $stmt = $this->conn->prepare($query);
            
            $codigo = $data['codigo'] ?? 'TRAB-' . time();
            $nombre = $data['nombre'] ?? '';
            $telefono = $data['telefono'] ?? '';
            $email = $data['email'] ?? '';
            $activo = $data['activo'] ?? 1;
            
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':activo', $activo);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        }
    }

    public function actualizar($id, $data) {
        // Construir query dinámicamente solo con campos presentes
        $fields = [];
        $params = [':id' => $id];
        
        $allowedFields = ['nombre', 'telefono', 'email', 'especialidad', 'activo'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false; // No hay nada que actualizar
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute($params);
    }

    public function eliminar($id) {
        // Soft delete - marcar como inactivo
        $query = "UPDATE " . $this->table_name . " SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}

?>
