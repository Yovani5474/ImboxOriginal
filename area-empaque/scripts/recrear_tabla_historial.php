<?php
/**
 * Script para recrear la tabla historial_cambios con la estructura correcta
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "=== RECREANDO TABLA HISTORIAL_CAMBIOS ===\n\n";
    
    // Eliminar tabla existente
    echo "Eliminando tabla anterior si existe...\n";
    $conn->exec("DROP TABLE IF EXISTS historial_cambios");
    echo "✅ Tabla anterior eliminada\n\n";
    
    // Crear la tabla con el esquema correcto
    echo "Creando tabla con estructura correcta...\n";
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS historial_cambios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transferencia_id INT NOT NULL,
        usuario VARCHAR(100),
        rol VARCHAR(50),
        accion VARCHAR(50) NOT NULL,
        campo_modificado VARCHAR(100),
        valor_anterior TEXT,
        valor_nuevo TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_transferencia (transferencia_id),
        INDEX idx_usuario (usuario),
        INDEX idx_fecha (fecha_cambio),
        INDEX idx_accion (accion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conn->exec($createTableSQL);
    
    echo "✅ Tabla 'historial_cambios' creada exitosamente!\n\n";
    
    // Mostrar estructura
    echo "=== ESTRUCTURA DE LA TABLA ===\n\n";
    $query = "DESCRIBE historial_cambios";
    $stmt = $conn->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo sprintf("%-25s %-30s %-10s\n", "Campo", "Tipo", "Null");
    echo str_repeat("-", 70) . "\n";
    
    foreach ($columns as $col) {
        echo sprintf("%-25s %-30s %-10s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null']
        );
    }
    
    echo "\n✅ Script completado!\n";
    echo "\nLa tabla ahora tiene las columnas correctas:\n";
    echo "  - transferencia_id (no tabla_afectada/registro_id)\n";
    echo "  - campo_modificado, valor_anterior, valor_nuevo\n";
    echo "  - usuario, rol, accion\n";
    echo "  - ip_address, user_agent\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
