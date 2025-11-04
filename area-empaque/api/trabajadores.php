<?php
require_once __DIR__ . '/../models/Trabajador.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$trabajador = new Trabajador();
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Extraer ID de la URL si existe (formato: /api/trabajadores.php/123)
$pathInfo = $_SERVER['PATH_INFO'] ?? '';
$id = null;
if (preg_match('/\/(\d+)$/', $pathInfo, $matches)) {
    $id = $matches[1];
}

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Obtener un trabajador específico
                $result = $trabajador->obtenerPorId($id);
                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Trabajador no encontrado']);
                }
            } else {
                // Obtener todos los trabajadores
                $result = $trabajador->obtenerTodos();
                echo json_encode($result);
            }
            break;
            
        case 'POST':
            // Crear nuevo trabajador
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['nombre']) || empty($data['nombre'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El nombre es requerido']);
                exit;
            }
            
            $nuevoId = $trabajador->crear($data);
            
            if ($nuevoId) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Trabajador creado correctamente',
                    'id' => $nuevoId
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear el trabajador']);
            }
            break;
            
        case 'PUT':
            // Actualizar trabajador existente
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID no especificado']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data)) {
                http_response_code(400);
                echo json_encode(['error' => 'No se enviaron datos para actualizar']);
                exit;
            }
            
            $resultado = $trabajador->actualizar($id, $data);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Trabajador actualizado correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al actualizar el trabajador']);
            }
            break;
            
        case 'DELETE':
            // Eliminar (soft delete) trabajador
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID no especificado']);
                exit;
            }
            
            $resultado = $trabajador->eliminar($id);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Trabajador eliminado correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al eliminar el trabajador']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
