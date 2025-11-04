<?php
/**
 * Script para crear la tabla historial_cambios (OPCIONAL)
 * Esta tabla permite registrar cambios en las transferencias para auditoría
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "=== CREANDO TABLA HISTORIAL_CAMBIOS ===\n\n";
    
    // Verificar si la tabla ya existe
    $query = "SHOW TABLES LIKE 'historial_cambios'";
    $stmt = $conn->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "⚠️  La tabla 'historial_cambios' YA EXISTE.\n";
        echo "No se realizarán cambios.\n";
        exit;
    }
    
    // Crear la tabla
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS historial_cambios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tabla_afectada VARCHAR(50) NOT NULL,
        registro_id INT NOT NULL,
        tipo_cambio ENUM('creacion', 'actualizacion', 'eliminacion') DEFAULT 'actualizacion',
        datos_anteriores TEXT,
        datos_nuevos TEXT,
        usuario VARCHAR(100),
        usuario_rol VARCHAR(50),
        fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ip_usuario VARCHAR(45),
        descripcion TEXT,
        INDEX idx_tabla_registro (tabla_afectada, registro_id),
        INDEX idx_fecha (fecha_cambio),
        INDEX idx_usuario (usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conn->exec($createTableSQL);
    
    echo "✅ Tabla 'historial_cambios' creada exitosamente!\n\n";
    
    // Mostrar estructura
    echo "=== ESTRUCTURA DE LA TABLA ===\n\n";
    $query = "DESCRIBE historial_cambios";
    $stmt = $conn->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo sprintf("%-20s %-30s %-10s\n", "Campo", "Tipo", "Null");
    echo str_repeat("-", 70) . "\n";
    
    foreach ($columns as $col) {
        echo sprintf("%-20s %-30s %-10s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null']
        );
    }
    
    echo "\n✅ Script completado!\n";
    echo "\nPara habilitar el historial, descomenta las líneas en:\n";
    echo "  - editar_transferencia.php (líneas 11, 16, 78)\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
