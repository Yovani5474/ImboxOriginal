<?php
/**
 * Script para hacer los proyectos Almacén 1 y Almacén 2 independientes
 * Copia recursos compartidos y actualiza referencias
 */

echo "=== INDEPENDIZANDO PROYECTOS ALMACÉN 1 Y 2 ===\n\n";

// Rutas
$almacen1 = __DIR__ . '/../../1';
$almacen2 = __DIR__ . '/..';

// 1. Crear carpeta img en Almacén 2
echo "1. Creando carpeta img en Almacén 2...\n";
$img_dir = $almacen2 . '/img';
if (!file_exists($img_dir)) {
    mkdir($img_dir, 0755, true);
    echo "   ✅ Carpeta creada: $img_dir\n";
} else {
    echo "   ⚠️  Carpeta ya existe\n";
}

// 2. Copiar logo
echo "\n2. Copiando logo...\n";
$logo_origen = $almacen1 . '/img/logo.jpg';
$logo_destino = $img_dir . '/logo.jpg';

if (file_exists($logo_origen)) {
    if (copy($logo_origen, $logo_destino)) {
        echo "   ✅ Logo copiado exitosamente\n";
        echo "   Origen: $logo_origen\n";
        echo "   Destino: $logo_destino\n";
    } else {
        echo "   ❌ Error al copiar logo\n";
    }
} else {
    echo "   ❌ Logo no encontrado en Almacén 1\n";
}

// 3. Copiar CSS de Almacén 1 (almacen1.css) a Almacén 2
echo "\n3. Copiando CSS compartido...\n";
$css_origen = $almacen1 . '/css/almacen1.css';
$css_destino = $almacen2 . '/css/almacen1.css';

if (file_exists($css_origen)) {
    if (copy($css_origen, $css_destino)) {
        echo "   ✅ CSS copiado exitosamente\n";
        echo "   Origen: $css_origen\n";
        echo "   Destino: $css_destino\n";
    } else {
        echo "   ❌ Error al copiar CSS\n";
    }
} else {
    echo "   ❌ CSS no encontrado en Almacén 1\n";
}

// 4. Resumen
echo "\n=== RESUMEN ===\n\n";
echo "✅ Recursos copiados a Almacén 2:\n";
echo "   - /2/img/logo.jpg\n";
echo "   - /2/css/almacen1.css\n";

echo "\n📝 SIGUIENTE PASO:\n";
echo "   Ejecuta: php " . __DIR__ . "/actualizar_referencias.php\n";
echo "   Esto cambiará todas las referencias de /1/ a /2/ en los archivos\n";

echo "\n✅ Script completado!\n";
?>
