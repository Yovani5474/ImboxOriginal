<?php
require_once __DIR__ . '/../config/database.php';

class DetallePrenda {
    private $conn;
    private $table_name = "detalles_prenda";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Crear nuevo detalle de prenda
    public function crear($datos) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (control_entrada_id, numero_item, color_codigo, 
                   talla_2, talla_4, talla_6, talla_8, talla_10, talla_12, talla_14, talla_16,
                   talla_xs, talla_s, talla_m, talla_l, talla_xl, talla_xxl, 
                   estado_entrega, observacion_item)
                  VALUES 
                  (:control_entrada_id, :numero_item, :color_codigo,
                   :talla_2, :talla_4, :talla_6, :talla_8, :talla_10, :talla_12, :talla_14, :talla_16,
                   :talla_xs, :talla_s, :talla_m, :talla_l, :talla_xl, :talla_xxl,
                   :estado_entrega, :observacion_item)";


    $stmt = $this->conn->prepare($query);

    // Create variables for bindParam (bindParam requires variables passed by reference)
    $control_entrada_id = $datos['control_entrada_id'];
    $numero_item = $datos['numero_item'];
    $color_codigo = $datos['color_codigo'];
    $talla_2 = $datos['talla_2'] ?? 0;
    $talla_4 = $datos['talla_4'] ?? 0;
    $talla_6 = $datos['talla_6'] ?? 0;
    $talla_8 = $datos['talla_8'] ?? 0;
    $talla_10 = $datos['talla_10'] ?? 0;
    $talla_12 = $datos['talla_12'] ?? 0;
    $talla_14 = $datos['talla_14'] ?? 0;
    $talla_16 = $datos['talla_16'] ?? 0;
    $talla_xs = $datos['talla_xs'] ?? 0;
    $talla_s = $datos['talla_s'] ?? 0;
    $talla_m = $datos['talla_m'] ?? 0;
    $talla_l = $datos['talla_l'] ?? 0;
    $talla_xl = $datos['talla_xl'] ?? 0;
    $talla_xxl = $datos['talla_xxl'] ?? 0;
    $estado_entrega = $datos['estado_entrega'];
    $observacion_item = $datos['observacion_item'];

    $stmt->bindParam(':control_entrada_id', $control_entrada_id);
    $stmt->bindParam(':numero_item', $numero_item);
    $stmt->bindParam(':color_codigo', $color_codigo);
    $stmt->bindParam(':talla_2', $talla_2);
    $stmt->bindParam(':talla_4', $talla_4);
    $stmt->bindParam(':talla_6', $talla_6);
    $stmt->bindParam(':talla_8', $talla_8);
    $stmt->bindParam(':talla_10', $talla_10);
    $stmt->bindParam(':talla_12', $talla_12);
    $stmt->bindParam(':talla_14', $talla_14);
    $stmt->bindParam(':talla_16', $talla_16);
    $stmt->bindParam(':talla_xs', $talla_xs);
    $stmt->bindParam(':talla_s', $talla_s);
    $stmt->bindParam(':talla_m', $talla_m);
    $stmt->bindParam(':talla_l', $talla_l);
    $stmt->bindParam(':talla_xl', $talla_xl);
    $stmt->bindParam(':talla_xxl', $talla_xxl);
    $stmt->bindParam(':estado_entrega', $estado_entrega);
    $stmt->bindParam(':observacion_item', $observacion_item);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Obtener detalles por control de entrada
    public function obtenerPorControlEntrada($controlEntradaId) {
        $query = "SELECT 
                    id,
                    numero_item,
                    color_codigo,
                    talla_2, talla_4, talla_6, talla_8, talla_10, talla_12, talla_14, talla_16,
                    talla_xs, talla_s, talla_m, talla_l, talla_xl, talla_xxl,
                    total_prendas,
                    estado_entrega,
                    observacion_item,
                    fecha_creacion
                  FROM " . $this->table_name . " 
                  WHERE control_entrada_id = :controlEntradaId
                  ORDER BY numero_item";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':controlEntradaId', $controlEntradaId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar detalle
    public function actualizar($id, $datos) {
        $query = "UPDATE " . $this->table_name . " 
                  SET numero_item = :numero_item,
                      color_codigo = :color_codigo,
                      talla_xs = :talla_xs,
                      talla_s = :talla_s,
                      talla_m = :talla_m,
                      talla_l = :talla_l,
                      talla_xl = :talla_xl,
                      talla_xxl = :talla_xxl,
                      estado_entrega = :estado_entrega,
                      observacion_item = :observacion_item
                  WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    // Variables para bindParam
    $numero_item = $datos['numero_item'];
    $color_codigo = $datos['color_codigo'];
    $talla_xs = $datos['talla_xs'] ?? 0;
    $talla_s = $datos['talla_s'] ?? 0;
    $talla_m = $datos['talla_m'] ?? 0;
    $talla_l = $datos['talla_l'] ?? 0;
    $talla_xl = $datos['talla_xl'] ?? 0;
    $talla_xxl = $datos['talla_xxl'] ?? 0;
    $estado_entrega = $datos['estado_entrega'];
    $observacion_item = $datos['observacion_item'];

    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':numero_item', $numero_item);
    $stmt->bindParam(':color_codigo', $color_codigo);
    $stmt->bindParam(':talla_xs', $talla_xs);
    $stmt->bindParam(':talla_s', $talla_s);
    $stmt->bindParam(':talla_m', $talla_m);
    $stmt->bindParam(':talla_l', $talla_l);
    $stmt->bindParam(':talla_xl', $talla_xl);
    $stmt->bindParam(':talla_xxl', $talla_xxl);
    $stmt->bindParam(':estado_entrega', $estado_entrega);
    $stmt->bindParam(':observacion_item', $observacion_item);

    return $stmt->execute();
    }

    // Eliminar detalle
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Crear múltiples detalles
    public function crearMultiples($detalles) {
        $resultados = [];
        foreach ($detalles as $detalle) {
            $id = $this->crear($detalle);
            if ($id) {
                $resultados[] = $id;
            }
        }
        return $resultados;
    }

    // Obtener detalles por transferencia
    public function obtenerPorTransferencia($transferencia_id) {
        $query = "SELECT dp.* 
                  FROM " . $this->table_name . " dp
                  INNER JOIN control_entrada ce ON dp.control_entrada_id = ce.id
                  WHERE ce.transferencia_id = :transferencia_id
                  ORDER BY dp.numero_item";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':transferencia_id', $transferencia_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener resumen de tallas por control de entrada
    public function obtenerResumenTallas($controlEntradaId) {
        $query = "SELECT 
                    SUM(talla_xs) as total_talla_xs,
                    SUM(talla_s) as total_talla_s,
                    SUM(talla_m) as total_talla_m,
                    SUM(talla_l) as total_talla_l,
                    SUM(talla_xl) as total_talla_xl,
                    SUM(talla_xxl) as total_talla_xxl,
                    SUM(total_prendas) as gran_total
                  FROM " . $this->table_name . " 
                  WHERE control_entrada_id = :controlEntradaId";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':controlEntradaId', $controlEntradaId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>