<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../models/Catalogos.php';

$catalogos = new Catalogos();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

try {
    switch($method) {
        case 'GET':
            if (empty($request[0])) {
                // Obtener todos los catálogos
                $data = $catalogos->obtenerTodosCatalogos();
                echo json_encode([
                    'success' => true,
                    'data' => $data
                ]);
            } elseif ($request[0] == 'tipos-prenda') {
                $data = $catalogos->obtenerTiposPrenda();
                echo json_encode(['success' => true, 'data' => $data]);
            } elseif ($request[0] == 'encargados') {
                $data = $catalogos->obtenerEncargadosTaller();
                echo json_encode(['success' => true, 'data' => $data]);
            } elseif ($request[0] == 'recepcionistas') {
                $data = $catalogos->obtenerRecepcionistas();
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                throw new Exception('Ruta no válida');
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos inválidos');
            }
            
            $id = null;
            if ($request[0] == 'tipos-prenda') {
                $id = $catalogos->crearTipoPrenda($input);
            } elseif ($request[0] == 'encargados') {
                $id = $catalogos->crearEncargadoTaller($input);
            } elseif ($request[0] == 'recepcionistas') {
                $id = $catalogos->crearRecepcionista($input);
            } else {
                throw new Exception('Tipo de catálogo no válido');
            }
            
            if (!$id) {
                throw new Exception('Error creando registro');
            }
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => ['id' => $id]
            ]);
            break;
            
        case 'PUT':
            if (empty($request[1]) || !is_numeric($request[1])) {
                throw new Exception('ID requerido');
            }
            
            $id = $request[1];
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos inválidos');
            }
            
            $result = false;
            if ($request[0] == 'tipos-prenda') {
                $result = $catalogos->actualizarTipoPrenda($id, $input);
            } elseif ($request[0] == 'encargados') {
                $result = $catalogos->actualizarEncargadoTaller($id, $input);
            } elseif ($request[0] == 'recepcionistas') {
                $result = $catalogos->actualizarRecepcionista($id, $input);
            } else {
                throw new Exception('Tipo de catálogo no válido');
            }
            
            if (!$result) {
                throw new Exception('Error actualizando registro');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Registro actualizado exitosamente'
            ]);
            break;
            
        case 'DELETE':
            if (empty($request[1]) || !is_numeric($request[1])) {
                throw new Exception('ID requerido');
            }
            
            $id = $request[1];
            $result = false;
            
            if ($request[0] == 'tipos-prenda') {
                $result = $catalogos->eliminarTipoPrenda($id);
            } elseif ($request[0] == 'encargados') {
                $result = $catalogos->eliminarEncargadoTaller($id);
            } elseif ($request[0] == 'recepcionistas') {
                $result = $catalogos->eliminarRecepcionista($id);
            } else {
                throw new Exception('Tipo de catálogo no válido');
            }
            
            if (!$result) {
                throw new Exception('Error eliminando registro');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Registro eliminado exitosamente'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>