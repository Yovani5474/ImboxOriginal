<?php
/**
 * Script para verificar y agregar la columna 'especialidad' a la tabla trabajadores
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "=== VERIFICANDO COLUMNA ESPECIALIDAD ===\n\n";
    
    // Verificar si la columna existe
    $query = "SHOW COLUMNS FROM trabajadores LIKE 'especialidad'";
    $stmt = $conn->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "✅ La columna 'especialidad' YA EXISTE en la tabla trabajadores.\n";
        echo "Tipo: " . $result['Type'] . "\n";
        echo "Null: " . $result['Null'] . "\n";
        echo "Default: " . $result['Default'] . "\n";
    } else {
        echo "❌ La columna 'especialidad' NO EXISTE en la tabla trabajadores.\n";
        echo "Agregando columna...\n\n";
        
        // Agregar la columna
        $alterQuery = "ALTER TABLE trabajadores 
                       ADD COLUMN especialidad VARCHAR(100) NULL 
                       AFTER email";
        
        $conn->exec($alterQuery);
        
        echo "✅ Columna 'especialidad' agregada exitosamente!\n";
        
        // Crear índice
        try {
            $conn->exec("CREATE INDEX idx_especialidad ON trabajadores(especialidad)");
            echo "✅ Índice creado para 'especialidad'\n";
        } catch (Exception $e) {
            echo "⚠️  Índice no creado (puede que ya exista): " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== ESTRUCTURA ACTUAL DE LA TABLA ===\n\n";
    
    // Mostrar estructura completa
    $query = "DESCRIBE trabajadores";
    $stmt = $conn->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo sprintf("%-20s %-20s %-10s %-10s\n", "Campo", "Tipo", "Null", "Default");
    echo str_repeat("-", 70) . "\n";
    
    foreach ($columns as $col) {
        echo sprintf("%-20s %-20s %-10s %-10s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Default'] ?? 'NULL'
        );
    }
    
    echo "\n✅ Script completado exitosamente!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
