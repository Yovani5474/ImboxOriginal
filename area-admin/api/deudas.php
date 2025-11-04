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
            $stmt = $db->prepare("INSERT INTO deudas (tipo, referencia_id, referencia_nombre, monto_total, monto_pendiente, fecha_vencimiento, descripcion, estado) VALUES (?, 0, ?, ?, ?, ?, ?, 'pendiente')");
            $stmt->execute([$data['tipo'], $data['referencia_nombre'], $data['monto_total'], $data['monto_total'], $data['fecha_vencimiento'] ?? null, $data['descripcion'] ?? null]);
            echo json_encode(['success' => true, 'message' => 'Deuda creada']);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("UPDATE deudas SET tipo=?, referencia_nombre=?, monto_total=?, fecha_vencimiento=?, descripcion=? WHERE id=?");
            $stmt->execute([$data['tipo'], $data['referencia_nombre'], $data['monto_total'], $data['fecha_vencimiento'] ?? null, $data['descripcion'] ?? null, $data['id']]);
            echo json_encode(['success' => true, 'message' => 'Deuda actualizada']);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("DELETE FROM deudas WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true, 'message' => 'Deuda eliminada']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
