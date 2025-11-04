<?php
require_once __DIR__ . '/../config/database.php';

class Catalogos {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // TIPOS DE PRENDA
    public function obtenerTiposPrenda() {
        $query = "SELECT id, nombre, descripcion FROM tipos_prenda WHERE activo = 1 ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearTipoPrenda($datos) {
        $query = "INSERT INTO tipos_prenda (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function actualizarTipoPrenda($id, $datos) {
        $query = "UPDATE tipos_prenda SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        return $stmt->execute();
    }

    public function eliminarTipoPrenda($id) {
        $query = "UPDATE tipos_prenda SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ENCARGADOS DE TALLER
    public function obtenerEncargadosTaller() {
        $query = "SELECT id, nombre, telefono, email FROM encargados_taller WHERE activo = 1 ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearEncargadoTaller($datos) {
        $query = "INSERT INTO encargados_taller (nombre, telefono, email) VALUES (:nombre, :telefono, :email)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':email', $datos['email']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function actualizarEncargadoTaller($id, $datos) {
        $query = "UPDATE encargados_taller SET nombre = :nombre, telefono = :telefono, email = :email WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':email', $datos['email']);
        return $stmt->execute();
    }

    public function eliminarEncargadoTaller($id) {
        $query = "UPDATE encargados_taller SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // RECEPCIONISTAS
    public function obtenerRecepcionistas() {
        $query = "SELECT id, nombre, telefono, email FROM recepcionistas WHERE activo = 1 ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearRecepcionista($datos) {
        $query = "INSERT INTO recepcionistas (nombre, telefono, email) VALUES (:nombre, :telefono, :email)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':email', $datos['email']);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function actualizarRecepcionista($id, $datos) {
        $query = "UPDATE recepcionistas SET nombre = :nombre, telefono = :telefono, email = :email WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':email', $datos['email']);
        return $stmt->execute();
    }

    public function eliminarRecepcionista($id) {
        $query = "UPDATE recepcionistas SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Obtener todos los catálogos de una vez
    public function obtenerTodosCatalogos() {
        return [
            'tiposPrenda' => $this->obtenerTiposPrenda(),
            'encargados' => $this->obtenerEncargadosTaller(),
            'recepcionistas' => $this->obtenerRecepcionistas()
        ];
    }
}
?>