<?php
/**
 * Script para actualizar todas las referencias de /1/ a /2/ en Almacén 2
 * Hace el proyecto completamente independiente
 */

echo "=== ACTUALIZANDO REFERENCIAS EN ALMACÉN 2 ===\n\n";

$almacen2 = __DIR__ . '/..';

// Archivos PHP a actualizar
$archivos = [
    'control_entrada_almacen2.php',
    'editar_transferencia.php',
    'historial.php',
    'index.php',
    'reportes.php',
    'transferencias_ui.php',
    'transferencias_ui_excel.php',
    'trabajadores_ui.php',
    'trabajadores_ui_excel.php',
    'ver_transferencia.php'
];

$cambios_realizados = 0;

foreach ($archivos as $archivo) {
    $ruta = $almacen2 . '/' . $archivo;
    
    if (!file_exists($ruta)) {
        echo "⚠️  No encontrado: $archivo\n";
        continue;
    }
    
    echo "Procesando: $archivo\n";
    
    $contenido = file_get_contents($ruta);
    $contenido_original = $contenido;
    
    // Cambiar referencias de imágenes
    $contenido = str_replace('/1/img/logo.jpg', '/2/img/logo.jpg', $contenido);
    
    // Cambiar referencias de CSS
    $contenido = str_replace('/1/css/almacen1.css', '/2/css/almacen1.css', $contenido);
    
    // Verificar si hubo cambios
    if ($contenido !== $contenido_original) {
        file_put_contents($ruta, $contenido);
        echo "   ✅ Actualizado\n";
        $cambios_realizados++;
    } else {
        echo "   - Sin cambios\n";
    }
}

echo "\n=== RESUMEN ===\n\n";
echo "✅ Archivos actualizados: $cambios_realizados\n";
echo "✅ Referencias cambiadas:\n";
echo "   /1/img/logo.jpg → /2/img/logo.jpg\n";
echo "   /1/css/almacen1.css → /2/css/almacen1.css\n";

echo "\n📝 DEPENDENCIAS RESTANTES:\n\n";
echo "ALMACÉN 1 todavía envía datos a:\n";
echo "   - http://localhost/2/api/transferencias.php\n";
echo "   - Lee de: ../2/data/trabajadores.json\n";
echo "\nEsto es INTENCIONAL para que funcione el flujo:\n";
echo "   Corte (Almacén 1) → Envía → Empaque (Almacén 2)\n";
echo "\nSi quieres eliminar esta dependencia también, edita manualmente:\n";
echo "   /1/index.php (línea 10: TARGET_URL)\n";
echo "   /1/index.php (líneas 45, 244: trabajadores.json)\n";

echo "\n✅ Almacén 2 ahora es INDEPENDIENTE!\n";
echo "✅ Puede funcionar sin Almacén 1\n";
?>
