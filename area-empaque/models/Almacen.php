<?php
require_once __DIR__ . '/../config/database.php';

class Almacen {
    private $conn;
    private $table_name = 'almacenes';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function obtenerPorId($id) {
        $query = "SELECT id, clave, nombre, ubicacion, fecha_creacion FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTodos() {
        $query = "SELECT id, clave, nombre, ubicacion FROM " . $this->table_name . " ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
