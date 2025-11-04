<?php
/**
 * API para cargar tabla de tallas por color
 */

require_once __DIR__ . '/../config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo '<div class="alert alert-danger">ID no especificado</div>';
    exit;
}

// Obtener datos del control
$db = getDB();
try {
    $stmt = $db->prepare("SELECT * FROM controles_entrada WHERE id = ?");
    $stmt->execute([$id]);
    $control = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$control) {
        echo '<div class="alert alert-danger">Control no encontrado</div>';
        exit;
    }
    
    // Aquí podrías cargar datos guardados de la tabla si existen
    // Por ahora mostramos la tabla vacía
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    exit;
}

// Incluir el componente de tabla
include __DIR__ . '/../../includes/tabla_tallas_color.php';

// Script para inicializar con datos del control
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar resumen con datos del control
    document.getElementById('resumen_transferencia_id').value = '<?= $control['id'] ?>';
    document.getElementById('resumen_tipo_prenda').value = '<?= $control['referencia'] ?? '' ?>';
});
</script>
