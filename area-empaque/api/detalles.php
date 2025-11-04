<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../models/DetallePrenda.php';

$detallePrenda = new DetallePrenda();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

try {
    switch($method) {
        case 'GET':
            if ($request[0] == 'control' && is_numeric($request[1])) {
                // Obtener detalles por control de entrada
                $controlEntradaId = $request[1];
                $detalles = $detallePrenda->obtenerPorControlEntrada($controlEntradaId);
                
                echo json_encode([
                    'success' => true,
                    'data' => $detalles
                ]);
            } elseif ($request[0] == 'resumen' && is_numeric($request[1])) {
                // Obtener resumen de tallas
                $controlEntradaId = $request[1];
                $resumen = $detallePrenda->obtenerResumenTallas($controlEntradaId);
                
                echo json_encode([
                    'success' => true,
                    'data' => $resumen
                ]);
            } else {
                throw new Exception('Ruta no válida');
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos inválidos');
            }
            
            $id = $detallePrenda->crear($input);
            
            if (!$id) {
                throw new Exception('Error creando detalle');
            }
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Detalle creado exitosamente',
                'data' => ['id' => $id]
            ]);
            break;
            
        case 'PUT':
            if (empty($request[0]) || !is_numeric($request[0])) {
                throw new Exception('ID requerido');
            }
            
            $id = $request[0];
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos inválidos');
            }
            
            $result = $detallePrenda->actualizar($id, $input);
            
            if (!$result) {
                throw new Exception('Error actualizando detalle');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Detalle actualizado exitosamente'
            ]);
            break;
            
        case 'DELETE':
            if (empty($request[0]) || !is_numeric($request[0])) {
                throw new Exception('ID requerido');
            }
            
            $id = $request[0];
            $result = $detallePrenda->eliminar($id);
            
            if (!$result) {
                throw new Exception('Error eliminando detalle');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Detalle eliminado exitosamente'
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