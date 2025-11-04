<?php
require_once __DIR__ . '/../config/database.php';

class ControlEntrada {
    private $conn;
    private $table_name = "control_entrada";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Crear nuevo registro de control de entrada
    public function crear($datos) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (fecha_recepcion, tipo_prenda_id, encargado_taller_id, recepcionista_id, 
                   puntos_favor, precio_10, precio_15, observaciones)
                  VALUES (:fecha_recepcion, :tipo_prenda_id, :encargado_taller_id, :recepcionista_id,
                          :puntos_favor, :precio_10, :precio_15, :observaciones)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':fecha_recepcion', $datos['fecha_recepcion']);
        $stmt->bindParam(':tipo_prenda_id', $datos['tipo_prenda_id']);
        $stmt->bindParam(':encargado_taller_id', $datos['encargado_taller_id']);
        $stmt->bindParam(':recepcionista_id', $datos['recepcionista_id']);
        $stmt->bindParam(':puntos_favor', $datos['puntos_favor']);
        $stmt->bindParam(':precio_10', $datos['precio_10']);
        $stmt->bindParam(':precio_15', $datos['precio_15']);
        $stmt->bindParam(':observaciones', $datos['observaciones']);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Obtener todos los registros con información relacionada
    public function obtenerTodos($limite = 50, $offset = 0) {
        $query = "SELECT 
                    ce.id,
                    ce.fecha_recepcion,
                    ce.puntos_favor,
                    ce.precio_10,
                    ce.precio_15,
                    ce.observaciones,
                    ce.fecha_creacion,
                    tp.nombre as tipo_prenda,
                    et.nombre as encargado_taller,
                    r.nombre as recepcionista,
                    COUNT(dp.id) as total_items
                  FROM " . $this->table_name . " ce
                  LEFT JOIN tipos_prenda tp ON ce.tipo_prenda_id = tp.id
                  LEFT JOIN encargados_taller et ON ce.encargado_taller_id = et.id
                  LEFT JOIN recepcionistas r ON ce.recepcionista_id = r.id
                  LEFT JOIN detalles_prenda dp ON ce.id = dp.control_entrada_id
                  GROUP BY ce.id, ce.fecha_recepcion, ce.puntos_favor, ce.precio_10, ce.precio_15,
                           ce.observaciones, ce.fecha_creacion, tp.nombre, et.nombre, r.nombre
                  ORDER BY ce.fecha_creacion DESC
                  LIMIT :limite OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener por ID con detalles
    public function obtenerPorId($id) {
        $query = "SELECT 
                    ce.*,
                    tp.nombre as tipo_prenda,
                    et.nombre as encargado_taller,
                    et.telefono as telefono_encargado,
                    r.nombre as recepcionista,
                    r.telefono as telefono_recepcionista
                  FROM " . $this->table_name . " ce
                  LEFT JOIN tipos_prenda tp ON ce.tipo_prenda_id = tp.id
                  LEFT JOIN encargados_taller et ON ce.encargado_taller_id = et.id
                  LEFT JOIN recepcionistas r ON ce.recepcionista_id = r.id
                  WHERE ce.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($id, $datos) {
        $query = "UPDATE " . $this->table_name . " 
                  SET fecha_recepcion = :fecha_recepcion,
                      tipo_prenda_id = :tipo_prenda_id,
                      encargado_taller_id = :encargado_taller_id,
                      recepcionista_id = :recepcionista_id,
                      puntos_favor = :puntos_favor,
                      precio_10 = :precio_10,
                      precio_15 = :precio_15,
                      observaciones = :observaciones,
                      fecha_modificacion = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fecha_recepcion', $datos['fecha_recepcion']);
        $stmt->bindParam(':tipo_prenda_id', $datos['tipo_prenda_id']);
        $stmt->bindParam(':encargado_taller_id', $datos['encargado_taller_id']);
        $stmt->bindParam(':recepcionista_id', $datos['recepcionista_id']);
        $stmt->bindParam(':puntos_favor', $datos['puntos_favor']);
        $stmt->bindParam(':precio_10', $datos['precio_10']);
        $stmt->bindParam(':precio_15', $datos['precio_15']);
        $stmt->bindParam(':observaciones', $datos['observaciones']);

        return $stmt->execute();
    }

    // Eliminar registro
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Buscar por fecha
    public function buscarPorFecha($fechaInicio, $fechaFin) {
        $query = "SELECT 
                    ce.id,
                    ce.fecha_recepcion,
                    tp.nombre as tipo_prenda,
                    et.nombre as encargado_taller,
                    r.nombre as recepcionista,
                    COUNT(dp.id) as total_items
                  FROM " . $this->table_name . " ce
                  LEFT JOIN tipos_prenda tp ON ce.tipo_prenda_id = tp.id
                  LEFT JOIN encargados_taller et ON ce.encargado_taller_id = et.id
                  LEFT JOIN recepcionistas r ON ce.recepcionista_id = r.id
                  LEFT JOIN detalles_prenda dp ON ce.id = dp.control_entrada_id
                  WHERE ce.fecha_recepcion BETWEEN :fechaInicio AND :fechaFin
                  GROUP BY ce.id, ce.fecha_recepcion, tp.nombre, et.nombre, r.nombre
                  ORDER BY ce.fecha_recepcion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fechaInicio', $fechaInicio);
        $stmt->bindParam(':fechaFin', $fechaFin);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>