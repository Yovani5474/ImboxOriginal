<?php
require_once __DIR__ . '/../config/database.php';

class RegistroCambios {
    private $conn;
    private $table_name = "registro_cambios";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Registrar un cambio
    public function registrar($tabla_referencia, $id_referencia, $tipo_cambio, $datos_anteriores = null, $datos_nuevos = null, $usuario = null, $descripcion = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (tabla_referencia, id_referencia, tipo_cambio, datos_anteriores, datos_nuevos, usuario, descripcion)
                  VALUES (:tabla_referencia, :id_referencia, :tipo_cambio, :datos_anteriores, :datos_nuevos, :usuario, :descripcion)";

        $stmt = $this->conn->prepare($query);

        $datos_anteriores_json = $datos_anteriores ? json_encode($datos_anteriores) : null;
        $datos_nuevos_json = $datos_nuevos ? json_encode($datos_nuevos) : null;

        $stmt->bindParam(':tabla_referencia', $tabla_referencia);
        $stmt->bindParam(':id_referencia', $id_referencia);
        $stmt->bindParam(':tipo_cambio', $tipo_cambio);
        $stmt->bindParam(':datos_anteriores', $datos_anteriores_json);
        $stmt->bindParam(':datos_nuevos', $datos_nuevos_json);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':descripcion', $descripcion);

        return $stmt->execute();
    }

    // Obtener historial de cambios por tabla e ID
    public function obtenerHistorial($tabla_referencia, $id_referencia) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE tabla_referencia = :tabla_referencia AND id_referencia = :id_referencia
                  ORDER BY fecha_cambio DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tabla_referencia', $tabla_referencia);
        $stmt->bindParam(':id_referencia', $id_referencia);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener cambios recientes
    public function obtenerRecientes($limite = 50) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  ORDER BY fecha_cambio DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registrar cambio en transferencia
    public function registrarCambioTransferencia($transferencia_id, $tipo_cambio, $datos_anteriores = null, $datos_nuevos = null, $usuario = null) {
        $descripcion = '';
        switch ($tipo_cambio) {
            case 'creacion':
                $descripcion = 'Transferencia creada';
                break;
            case 'actualizacion':
                $descripcion = 'Transferencia actualizada';
                break;
            case 'recepcion':
                $descripcion = 'Recepción confirmada';
                break;
        }

        return $this->registrar('transferencias', $transferencia_id, $tipo_cambio, $datos_anteriores, $datos_nuevos, $usuario, $descripcion);
    }

    // Registrar cambio en control de entrada
    public function registrarCambioControlEntrada($control_id, $tipo_cambio, $datos_anteriores = null, $datos_nuevos = null, $usuario = null) {
        $descripcion = '';
        switch ($tipo_cambio) {
            case 'creacion':
                $descripcion = 'Control de entrada creado';
                break;
            case 'actualizacion':
                $descripcion = 'Control de entrada actualizado';
                break;
        }

        return $this->registrar('control_entrada', $control_id, $tipo_cambio, $datos_anteriores, $datos_nuevos, $usuario, $descripcion);
    }

    // Registrar cambio en detalle de prenda
    public function registrarCambioDetallePrenda($detalle_id, $tipo_cambio, $datos_anteriores = null, $datos_nuevos = null, $usuario = null) {
        $descripcion = '';
        switch ($tipo_cambio) {
            case 'creacion':
                $descripcion = 'Detalle de prenda creado';
                break;
            case 'actualizacion':
                $descripcion = 'Detalle de prenda actualizado';
                break;
            case 'eliminacion':
                $descripcion = 'Detalle de prenda eliminado';
                break;
        }

        return $this->registrar('detalles_prenda', $detalle_id, $tipo_cambio, $datos_anteriores, $datos_nuevos, $usuario, $descripcion);
    }
}
?>