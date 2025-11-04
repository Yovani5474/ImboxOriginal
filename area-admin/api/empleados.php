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
            $stmt = $db->prepare("INSERT INTO empleados (nombre, apellidos, email, telefono, puesto, departamento, salario, fecha_contratacion, premium, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['nombre'], $data['apellidos'] ?? null, $data['email'] ?? null, $data['telefono'] ?? null, $data['puesto'] ?? null, $data['departamento'] ?? null, $data['salario'] ?? 0, $data['fecha_contratacion'] ?? null, $data['premium'] ?? 0, $data['activo'] ?? 1]);
            echo json_encode(['success' => true, 'message' => 'Empleado creado']);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("UPDATE empleados SET nombre=?, apellidos=?, email=?, telefono=?, puesto=?, departamento=?, salario=?, fecha_contratacion=?, premium=?, activo=? WHERE id=?");
            $stmt->execute([$data['nombre'], $data['apellidos'] ?? null, $data['email'] ?? null, $data['telefono'] ?? null, $data['puesto'] ?? null, $data['departamento'] ?? null, $data['salario'] ?? 0, $data['fecha_contratacion'] ?? null, $data['premium'] ?? 0, $data['activo'] ?? 1, $data['id']]);
            echo json_encode(['success' => true, 'message' => 'Empleado actualizado']);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("DELETE FROM empleados WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true, 'message' => 'Empleado eliminado']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
