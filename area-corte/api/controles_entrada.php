<?php
/**
 * API - Controles de Entrada del Área de Corte
 * Solo accesible por Empaque con API Key
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

// Verificar API Key
$headers = getallheaders();
$apiKey = $headers['X-API-KEY'] ?? $_GET['api_key'] ?? null;

if ($apiKey !== API_KEY) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

// GET - Listar controles de entrada
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        // Obtener un control específico
        $id = intval($_GET['id']);
        $stmt = $db->prepare("SELECT * FROM controles_entrada WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $control = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($control) {
            // Obtener detalles
            $stmt2 = $db->prepare("SELECT * FROM control_detalles WHERE control_entrada_id = :id");
            $stmt2->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt2->execute();
            
            $detalles = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            $control['detalles'] = $detalles;
            
            echo json_encode(['success' => true, 'data' => $control]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Control no encontrado']);
        }
    } else {
        // Listar todos
        $stmt = $db->query("SELECT * FROM controles_entrada ORDER BY id DESC");
        $controles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $controles]);
    }
}

// POST - Crear control de entrada
elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $referencia = $input['referencia'] ?? '';
    $fecha_entrada = $input['fecha_entrada'] ?? date('Y-m-d');
    $proveedor = $input['proveedor'] ?? '';
    $orden_compra = $input['orden_compra'] ?? '';
    $total_rollos = intval($input['total_rollos'] ?? 0);
    $total_metros = floatval($input['total_metros'] ?? 0);
    $observaciones = $input['observaciones'] ?? '';
    $estado = $input['estado'] ?? 'pendiente';
    $usuario_creacion = $input['usuario_creacion'] ?? 'almacen1';
    
    if (empty($referencia)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Referencia requerida']);
        exit;
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO controles_entrada 
            (referencia, fecha_entrada, proveedor, orden_compra, total_rollos, total_metros, observaciones, estado, usuario_creacion)
            VALUES (:ref, :fecha, :prov, :orden, :rollos, :metros, :obs, :estado, :user)");
        
        $stmt->bindValue(':ref', $referencia, PDO::PARAM_STR);
        $stmt->bindValue(':fecha', $fecha_entrada, PDO::PARAM_STR);
        $stmt->bindValue(':prov', $proveedor, PDO::PARAM_STR);
        $stmt->bindValue(':orden', $orden_compra, PDO::PARAM_STR);
        $stmt->bindValue(':rollos', $total_rollos, PDO::PARAM_INT);
        $stmt->bindValue(':metros', $total_metros, PDO::PARAM_STR);
        $stmt->bindValue(':obs', $observaciones, PDO::PARAM_STR);
        $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindValue(':user', $usuario_creacion, PDO::PARAM_STR);
        
        $stmt->execute();
        $id = $db->lastInsertId();
        
        // Insertar detalles si existen
        if (!empty($input['detalles']) && is_array($input['detalles'])) {
            foreach ($input['detalles'] as $detalle) {
                $stmt2 = $db->prepare("INSERT INTO control_detalles 
                    (control_entrada_id, tipo_tela, color, cantidad_rollos, metros)
                    VALUES (:control_id, :tipo, :color, :rollos, :metros)");
                
                $stmt2->bindValue(':control_id', $id, PDO::PARAM_INT);
                $stmt2->bindValue(':tipo', $detalle['tipo_tela'] ?? '', PDO::PARAM_STR);
                $stmt2->bindValue(':color', $detalle['color'] ?? '', PDO::PARAM_STR);
                $stmt2->bindValue(':rollos', intval($detalle['cantidad_rollos'] ?? 0), PDO::PARAM_INT);
                $stmt2->bindValue(':metros', floatval($detalle['metros'] ?? 0), PDO::PARAM_STR);
                $stmt2->execute();
            }
        }
        
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Control creado', 'id' => $id]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// PUT - Actualizar control
elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID requerido']);
        exit;
    }
    
    $fields = [];
    $params = [':id' => $id];
    
    if (isset($input['referencia'])) {
        $fields[] = "referencia = :ref";
        $params[':ref'] = $input['referencia'];
    }
    if (isset($input['fecha_entrada'])) {
        $fields[] = "fecha_entrada = :fecha";
        $params[':fecha'] = $input['fecha_entrada'];
    }
    if (isset($input['proveedor'])) {
        $fields[] = "proveedor = :prov";
        $params[':prov'] = $input['proveedor'];
    }
    if (isset($input['estado'])) {
        $fields[] = "estado = :estado";
        $params[':estado'] = $input['estado'];
    }
    
    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Sin campos para actualizar']);
        exit;
    }
    
    $sql = "UPDATE controles_entrada SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $db->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Control actualizado']);
}
