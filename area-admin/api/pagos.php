<?php
require_once '../config/database.php';
require_once '../config/auth.php';
header('Content-Type: application/json');
requireAuth();

$db = Database::getInstance()->getConnection();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $db->beginTransaction();
        
        // Registrar pago
        $stmt = $db->prepare("INSERT INTO pagos_deudas (deuda_id, monto, metodo_pago, referencia, notas) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['deuda_id'], $data['monto'], $data['metodo_pago'] ?? null, $data['referencia'] ?? null, $data['notas'] ?? null]);
        
        // Actualizar deuda
        $stmt = $db->prepare("SELECT monto_total, monto_pagado FROM deudas WHERE id = ?");
        $stmt->execute([$data['deuda_id']]);
        $deuda = $stmt->fetch();
        
        $nuevo_pagado = $deuda['monto_pagado'] + $data['monto'];
        $nuevo_pendiente = $deuda['monto_total'] - $nuevo_pagado;
        
        $estado = 'pendiente';
        if ($nuevo_pendiente <= 0) {
            $estado = 'pagada';
            $nuevo_pendiente = 0;
        } elseif ($nuevo_pagado > 0) {
            $estado = 'parcial';
        }
        
        $stmt = $db->prepare("UPDATE deudas SET monto_pagado = ?, monto_pendiente = ?, estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_pagado, $nuevo_pendiente, $estado, $data['deuda_id']]);
        
        $db->commit();
        
        echo json_encode(['success' => true, 'message' => 'Pago registrado correctamente']);
    }
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
