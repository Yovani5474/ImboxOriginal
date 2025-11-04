<?php
require_once __DIR__ . '/../config/database.php';

class Transferencia {
    private $conn;
    private $table_name = 'transferencias';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function crear($data) {
        $query = "INSERT INTO " . $this->table_name . " 
            (referencia, almacen_origen_id, almacen_destino_id, control_entrada_id, total_items, 
             trabajador_id, trabajador_nombre, tipo_prenda, color, talla, estado, usuario_creacion, observaciones)
            VALUES (:referencia, :origen, :destino, :control_entrada_id, :total_items, 
                    :trabajador_id, :trabajador_nombre, :tipo_prenda, :color, :talla, :estado, :usuario_creacion, :observaciones)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':referencia', $data['referencia']);
        $stmt->bindParam(':origen', $data['almacen_origen_id']);
        $stmt->bindParam(':destino', $data['almacen_destino_id']);
        $stmt->bindParam(':control_entrada_id', $data['control_entrada_id']);
        $stmt->bindParam(':trabajador_id', $data['trabajador_id']);
        $stmt->bindParam(':trabajador_nombre', $data['trabajador_nombre']);
        $stmt->bindParam(':total_items', $data['total_items']);
        $stmt->bindParam(':tipo_prenda', $data['tipo_prenda']);
        $stmt->bindParam(':color', $data['color']);
        $stmt->bindParam(':talla', $data['talla']);
        $stmt->bindParam(':estado', $data['estado']);
        $stmt->bindParam(':usuario_creacion', $data['usuario_creacion']);
        $stmt->bindParam(':observaciones', $data['observaciones']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function asignarTrabajador($id, $trabajadorId) {
        $query = "UPDATE " . $this->table_name . " SET trabajador_id = :trabajador WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':trabajador', $trabajadorId);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Actualizar transferencia - Permite corregir datos incompletos o erróneos
     */
    public function actualizar($id, $data) {
        try {
            // Construir query dinámicamente solo con campos que se proporcionan
            $campos = [];
            $params = [':id' => $id];
            
            if (isset($data['referencia'])) {
                $campos[] = "referencia = :referencia";
                $params[':referencia'] = $data['referencia'];
            }
            if (isset($data['total_items'])) {
                $campos[] = "total_items = :total_items";
                $params[':total_items'] = $data['total_items'];
            }
            if (isset($data['tipo_prenda'])) {
                $campos[] = "tipo_prenda = :tipo_prenda";
                $params[':tipo_prenda'] = $data['tipo_prenda'];
            }
            if (isset($data['color'])) {
                $campos[] = "color = :color";
                $params[':color'] = $data['color'];
            }
            if (isset($data['talla'])) {
                $campos[] = "talla = :talla";
                $params[':talla'] = $data['talla'];
            }
            if (isset($data['trabajador_id'])) {
                $campos[] = "trabajador_id = :trabajador_id";
                $params[':trabajador_id'] = $data['trabajador_id'];
            }
            if (isset($data['trabajador_nombre'])) {
                $campos[] = "trabajador_nombre = :trabajador_nombre";
                $params[':trabajador_nombre'] = $data['trabajador_nombre'];
            }
            if (isset($data['estado'])) {
                $campos[] = "estado = :estado";
                $params[':estado'] = $data['estado'];
            }
            if (isset($data['observaciones'])) {
                $campos[] = "observaciones = :observaciones";
                $params[':observaciones'] = $data['observaciones'];
            }
            
            if (empty($campos)) {
                return false; // No hay nada que actualizar
            }
            
            $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $campos) . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error en actualizar transferencia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Confirmación por trabajador: acepta un array de faltantes que se guardan en JSON.
     */
    public function confirmarPorTrabajador($id, $trabajadorId, $faltantes = [], $datos_recepcion = null) {
        try {
            $this->conn->beginTransaction();
            
            // Obtener datos anteriores para el registro de cambios
            $datos_anteriores = $this->obtenerPorId($id);
            
            $faltantesJson = !empty($faltantes) ? json_encode($faltantes) : null;
            
            // Calcular total recibido si hay items
            $total_recibido = 0;
            $total_esperado = $datos_anteriores['total_items'];
            
            if ($datos_recepcion && !empty($datos_recepcion['items'])) {
                foreach ($datos_recepcion['items'] as $item) {
                    $total_recibido += intval($item['talla_2'] ?? 0);
                    $total_recibido += intval($item['talla_4'] ?? 0);
                    $total_recibido += intval($item['talla_6'] ?? 0);
                    $total_recibido += intval($item['talla_8'] ?? 0);
                    $total_recibido += intval($item['talla_10'] ?? 0);
                    $total_recibido += intval($item['talla_12'] ?? 0);
                    $total_recibido += intval($item['talla_14'] ?? 0);
                    $total_recibido += intval($item['talla_16'] ?? 0);
                    $total_recibido += intval($item['talla_xs'] ?? 0);
                    $total_recibido += intval($item['talla_s'] ?? 0);
                    $total_recibido += intval($item['talla_m'] ?? 0);
                    $total_recibido += intval($item['talla_l'] ?? 0);
                    $total_recibido += intval($item['talla_xl'] ?? 0);
                    $total_recibido += intval($item['talla_xxl'] ?? 0);
                }
            }
            
            // Determinar estado según cantidad recibida
            $nuevo_estado = 'recibido'; // Estado por defecto
            if ($total_recibido > 0) {
                if ($total_recibido >= $total_esperado) {
                    $nuevo_estado = 'completado';
                } else {
                    $nuevo_estado = 'parcial'; // Falta completar
                }
            }
            
            // Actualizar la transferencia
            $query = "UPDATE " . $this->table_name . " 
                      SET confirmado_por_trabajador = 1, fecha_confirmacion_trabajador = NOW(), 
                          faltantes_json = :faltantes, trabajador_id = :trabajador, estado = :estado
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':faltantes', $faltantesJson);
            $stmt->bindParam(':trabajador', $trabajadorId);
            $stmt->bindParam(':estado', $nuevo_estado);
            $stmt->bindParam(':id', $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Error actualizando transferencia');
            }
            
            // Guardar datos de recepción en JSON para poder completar después
            if ($datos_recepcion && !empty($datos_recepcion['items'])) {
                // Obtener datos previos si existen
                $datos_previos = [];
                if (!empty($datos_anteriores['datos_recepcion_json'])) {
                    $datos_previos = json_decode($datos_anteriores['datos_recepcion_json'], true);
                }
                
                // Combinar con datos nuevos (sumar cantidades)
                $datos_combinados = $datos_previos;
                
                foreach ($datos_recepcion['items'] as $item) {
                    $numero = $item['numero_item'];
                    
                    if (!isset($datos_combinados[$numero])) {
                        $datos_combinados[$numero] = $item;
                    } else {
                        // Sumar las tallas
                        $datos_combinados[$numero]['talla_2'] = ($datos_combinados[$numero]['talla_2'] ?? 0) + ($item['talla_2'] ?? 0);
                        $datos_combinados[$numero]['talla_4'] = ($datos_combinados[$numero]['talla_4'] ?? 0) + ($item['talla_4'] ?? 0);
                        $datos_combinados[$numero]['talla_6'] = ($datos_combinados[$numero]['talla_6'] ?? 0) + ($item['talla_6'] ?? 0);
                        $datos_combinados[$numero]['talla_8'] = ($datos_combinados[$numero]['talla_8'] ?? 0) + ($item['talla_8'] ?? 0);
                        $datos_combinados[$numero]['talla_10'] = ($datos_combinados[$numero]['talla_10'] ?? 0) + ($item['talla_10'] ?? 0);
                        $datos_combinados[$numero]['talla_12'] = ($datos_combinados[$numero]['talla_12'] ?? 0) + ($item['talla_12'] ?? 0);
                        $datos_combinados[$numero]['talla_14'] = ($datos_combinados[$numero]['talla_14'] ?? 0) + ($item['talla_14'] ?? 0);
                        $datos_combinados[$numero]['talla_16'] = ($datos_combinados[$numero]['talla_16'] ?? 0) + ($item['talla_16'] ?? 0);
                        $datos_combinados[$numero]['talla_xs'] = ($datos_combinados[$numero]['talla_xs'] ?? 0) + ($item['talla_xs'] ?? 0);
                        $datos_combinados[$numero]['talla_s'] = ($datos_combinados[$numero]['talla_s'] ?? 0) + ($item['talla_s'] ?? 0);
                        $datos_combinados[$numero]['talla_m'] = ($datos_combinados[$numero]['talla_m'] ?? 0) + ($item['talla_m'] ?? 0);
                        $datos_combinados[$numero]['talla_l'] = ($datos_combinados[$numero]['talla_l'] ?? 0) + ($item['talla_l'] ?? 0);
                        $datos_combinados[$numero]['talla_xl'] = ($datos_combinados[$numero]['talla_xl'] ?? 0) + ($item['talla_xl'] ?? 0);
                        $datos_combinados[$numero]['talla_xxl'] = ($datos_combinados[$numero]['talla_xxl'] ?? 0) + ($item['talla_xxl'] ?? 0);
                    }
                }
                
                // Guardar en la base de datos
                $datos_json = json_encode(array_values($datos_combinados));
                $query_update = "UPDATE " . $this->table_name . " SET datos_recepcion_json = :datos WHERE id = :id";
                $stmt_update = $this->conn->prepare($query_update);
                $stmt_update->bindParam(':datos', $datos_json);
                $stmt_update->bindParam(':id', $id);
                $stmt_update->execute();
            }
            
            // Registrar cambio
            require_once __DIR__ . '/RegistroCambios.php';
            $registro = new RegistroCambios();
            $datos_nuevos = $this->obtenerPorId($id);
            $registro->registrarCambioTransferencia($id, 'recepcion', $datos_anteriores, $datos_nuevos, 'trabajador_' . $trabajadorId);
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error en confirmarPorTrabajador: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorId($id) {
        $query = "SELECT t.*, ao.nombre as almacen_origen, ad.nombre as almacen_destino 
                  FROM " . $this->table_name . " t
                  LEFT JOIN almacenes ao ON t.almacen_origen_id = ao.id
                  LEFT JOIN almacenes ad ON t.almacen_destino_id = ad.id
                  WHERE t.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listar($limit = 50, $offset = 0) {
        $query = "SELECT t.id, t.referencia, t.estado, t.total_items, t.fecha_creacion, t.trabajador_id,
                         t.tipo_prenda, t.color, t.talla, t.observaciones, t.control_entrada_id,
                         ao.nombre as origen, ad.nombre as destino, tr.nombre as trabajador_nombre
                  FROM " . $this->table_name . " t
                  LEFT JOIN almacenes ao ON t.almacen_origen_id = ao.id
                  LEFT JOIN almacenes ad ON t.almacen_destino_id = ad.id
                  LEFT JOIN trabajadores tr ON t.trabajador_id = tr.id
                  ORDER BY t.fecha_creacion DESC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarRecibido($id, $usuario_recepcion) {
        $query = "UPDATE " . $this->table_name . " SET estado = 'recibido', fecha_recepcion = NOW(), usuario_recepcion = :usuario WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario', $usuario_recepcion);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}

?>
