<?php
/**
 * API para Controles de Entrada - Edición Tipo Excel
 * Área de Corte - Sistema IMBOX
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener ID de la URL si existe
$path = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? '';
$parts = explode('/', trim($path, '/'));
$id = null;
if (count($parts) > 0 && is_numeric(end($parts))) {
    $id = intval(end($parts));
}

// Obtener base de datos
$db = getDB();

try {
    switch ($method) {
        case 'GET':
            // Listar controles o obtener uno específico
            if ($id) {
                $stmt = $db->prepare("SELECT * FROM controles_entrada WHERE id = ?");
                $stmt->execute([$id]);
                $control = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($control) {
                    echo json_encode([
                        'success' => true,
                        'data' => $control
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Control no encontrado'
                    ]);
                }
            } else {
                $stmt = $db->query("SELECT * FROM controles_entrada ORDER BY id DESC");
                $controles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode([
                    'success' => true,
                    'data' => $controles
                ]);
            }
            break;
            
        case 'POST':
            // Crear nuevo control
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $db->prepare("
                INSERT INTO controles_entrada 
                (referencia, fecha_entrada, proveedor, orden_compra, total_rollos, total_metros, observaciones, estado, usuario_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['referencia'] ?? 'CE-' . date('YmdHis'),
                $data['fecha_entrada'] ?? date('Y-m-d'),
                $data['proveedor'] ?? '',
                $data['orden_compra'] ?? '',
                $data['total_rollos'] ?? 0,
                $data['total_metros'] ?? 0,
                $data['observaciones'] ?? '',
                $data['estado'] ?? 'pendiente',
                $data['usuario_creacion'] ?? 'sistema'
            ]);
            
            $newId = $db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'id' => $newId,
                'message' => 'Control creado correctamente'
            ]);
            break;
            
        case 'PUT':
            // Actualizar control existente
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'ID no especificado'
                ]);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Construir query dinámicamente
            $fields = [];
            $values = [];
            
            $allowedFields = ['referencia', 'fecha_entrada', 'proveedor', 'orden_compra', 'total_rollos', 'total_metros', 'observaciones', 'estado'];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            // Guardar tabla de tallas si viene
            if (isset($data['tabla_tallas'])) {
                $fields[] = "tabla_tallas_json = ?";
                $values[] = json_encode($data['tabla_tallas']);
            }
            
            // Guardar total de prendas si viene
            if (isset($data['total_prendas'])) {
                $fields[] = "total_prendas = ?";
                $values[] = intval($data['total_prendas']);
            }
            
            // Fecha de recepción
            if (isset($data['fecha_recepcion'])) {
                $fields[] = "fecha_recepcion = ?";
                $values[] = $data['fecha_recepcion'];
            }
            
            if (empty($fields)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'No hay campos válidos para actualizar'
                ]);
                break;
            }
            
            $values[] = $id;
            $sql = "UPDATE controles_entrada SET " . implode(', ', $fields) . " WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($values);
            
            // Obtener registro actualizado
            $stmt = $db->prepare("SELECT * FROM controles_entrada WHERE id = ?");
            $stmt->execute([$id]);
            $control = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $control,
                'message' => 'Control actualizado correctamente'
            ]);
            break;
            
        case 'DELETE':
            // Eliminar control
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'ID no especificado'
                ]);
                break;
            }
            
            $stmt = $db->prepare("DELETE FROM controles_entrada WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Control eliminado correctamente'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Método no permitido'
            ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
