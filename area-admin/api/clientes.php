<?php
require_once '../config/database.php';
require_once '../config/auth.php';

header('Content-Type: application/json');
requireAuth();

$db = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Listar clientes
            $id = $_GET['id'] ?? null;
            
            if ($id) {
                $stmt = $db->prepare("SELECT * FROM clientes WHERE id = ?");
                $stmt->execute([$id]);
                $cliente = $stmt->fetch();
                echo json_encode(['success' => true, 'data' => $cliente]);
            } else {
                $stmt = $db->query("SELECT * FROM clientes ORDER BY fecha_registro DESC");
                $clientes = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $clientes]);
            }
            break;
            
        case 'POST':
            // Crear cliente
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $db->prepare("
                INSERT INTO clientes (nombre, email, telefono, direccion, rfc, empresa, 
                                     premium, limite_credito, activo, notas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['email'] ?? null,
                $data['telefono'] ?? null,
                $data['direccion'] ?? null,
                $data['rfc'] ?? null,
                $data['empresa'] ?? null,
                $data['premium'] ?? 0,
                $data['limite_credito'] ?? 0,
                $data['activo'] ?? 1,
                $data['notas'] ?? null
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cliente creado correctamente',
                'id' => $db->lastInsertId()
            ]);
            break;
            
        case 'PUT':
            // Actualizar cliente
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $db->prepare("
                UPDATE clientes 
                SET nombre = ?, email = ?, telefono = ?, direccion = ?, rfc = ?, 
                    empresa = ?, premium = ?, limite_credito = ?, activo = ?, notas = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['email'] ?? null,
                $data['telefono'] ?? null,
                $data['direccion'] ?? null,
                $data['rfc'] ?? null,
                $data['empresa'] ?? null,
                $data['premium'] ?? 0,
                $data['limite_credito'] ?? 0,
                $data['activo'] ?? 1,
                $data['notas'] ?? null,
                $data['id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cliente actualizado correctamente'
            ]);
            break;
            
        case 'DELETE':
            // Eliminar cliente
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $db->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cliente eliminado correctamente'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
