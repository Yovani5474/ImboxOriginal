<?php
require_once '../config/database.php';
require_once '../config/auth.php';
header('Content-Type: application/json');
requireAuth();

$db = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("INSERT INTO proveedores (nombre, empresa, email, telefono, tipo, premium, credito_dias, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['nombre'], $data['empresa'] ?? null, $data['email'] ?? null, $data['telefono'] ?? null, $data['tipo'] ?? null, $data['premium'] ?? 0, $data['credito_dias'] ?? 0, $data['activo'] ?? 1]);
            echo json_encode(['success' => true, 'message' => 'Proveedor creado']);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("UPDATE proveedores SET nombre=?, empresa=?, email=?, telefono=?, tipo=?, premium=?, credito_dias=?, activo=? WHERE id=?");
            $stmt->execute([$data['nombre'], $data['empresa'] ?? null, $data['email'] ?? null, $data['telefono'] ?? null, $data['tipo'] ?? null, $data['premium'] ?? 0, $data['credito_dias'] ?? 0, $data['activo'] ?? 1, $data['id']]);
            echo json_encode(['success' => true, 'message' => 'Proveedor actualizado']);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("DELETE FROM proveedores WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true, 'message' => 'Proveedor eliminado']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
