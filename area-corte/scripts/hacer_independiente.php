<?php
/**
 * Script para hacer Almacén 1 completamente independiente
 * Copia el archivo de trabajadores desde Almacén 2
 */

echo "=== HACIENDO ALMACÉN 1 INDEPENDIENTE ===\n\n";

$almacen1 = __DIR__ . '/..';
$almacen2 = __DIR__ . '/../../2';

// 1. Crear carpeta data en Almacén 1
echo "1. Creando carpeta data en Almacén 1...\n";
$data_dir = $almacen1 . '/data';
if (!file_exists($data_dir)) {
    mkdir($data_dir, 0755, true);
    echo "   ✅ Carpeta creada: $data_dir\n";
} else {
    echo "   ⚠️  Carpeta ya existe\n";
}

// 2. Copiar archivo de trabajadores
echo "\n2. Copiando archivo de trabajadores...\n";
$trabajadores_origen = $almacen2 . '/data/trabajadores.json';
$trabajadores_destino = $data_dir . '/trabajadores.json';

if (file_exists($trabajadores_origen)) {
    if (copy($trabajadores_origen, $trabajadores_destino)) {
        echo "   ✅ Archivo copiado exitosamente\n";
        echo "   Origen: $trabajadores_origen\n";
        echo "   Destino: $trabajadores_destino\n";
    } else {
        echo "   ❌ Error al copiar archivo\n";
    }
} else {
    // Crear archivo vacío si no existe
    echo "   ⚠️  Archivo no encontrado en Almacén 2\n";
    echo "   Creando archivo vacío...\n";
    file_put_contents($trabajadores_destino, json_encode([], JSON_PRETTY_PRINT));
    echo "   ✅ Archivo vacío creado\n";
}

echo "\n=== RESUMEN ===\n\n";
echo "✅ Carpeta creada: /1/data/\n";
echo "✅ Archivo creado: /1/data/trabajadores.json\n";

echo "\n📝 SIGUIENTE PASO:\n";
echo "   El archivo index.php será actualizado para usar /1/data/trabajadores.json\n";
echo "   en lugar de ../2/data/trabajadores.json\n";

echo "\n✅ Script completado!\n";
?>
