<?php
require_once __DIR__ . '/models/Transferencia.php';
require_once __DIR__ . '/models/Trabajador.php';
require_once __DIR__ . '/models/ControlEntrada.php';
require_once __DIR__ . '/models/DetallePrenda.php';

$transferencia = new Transferencia();
$trabajador = new Trabajador();
$trabajadores = $trabajador->obtenerTodos();

// Obtener transferencia si se especifica ID, o crear una nueva
$transferencia_data = null;
$detalles_prenda = [];
$transferencia_id = $_GET['id'] ?? null;
$es_nueva = false;

if ($transferencia_id) {
    // Caso 1: Se especificó un ID - cargar transferencia existente
    $transferencia_data = $transferencia->obtenerPorId($transferencia_id);
    
    // Validar que la transferencia exista
    if (!$transferencia_data) {
        die("
        <!DOCTYPE html>
        <html lang=\"es\">
        <head>
            <meta charset=\"UTF-8\">
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
            <title>Error - Transferencia no encontrada</title>
            <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
            <link href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css\" rel=\"stylesheet\">
        <style>
@keyframes gradientShift { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
body { background: linear-gradient(-45deg, #FF8C00, #FFB84D, #FFA500, #FF9933) !important; background-size: 400% 400% !important; animation: gradientShift 15s ease infinite !important; position: relative; overflow-x: hidden; }
body::before, body::after { content: ''; position: fixed; width: 400px; height: 400px; border-radius: 50%; opacity: 0.1; z-index: 0; }
body::before { background: radial-gradient(circle, white, transparent); top: -200px; left: -200px; animation: float 20s ease-in-out infinite; }
body::after { background: radial-gradient(circle, white, transparent); bottom: -200px; right: -200px; animation: float 25s ease-in-out infinite reverse; }
@keyframes float { 0%,100%{transform:translate(0,0) scale(1)} 25%{transform:translate(50px,-50px) scale(1.1)} 50%{transform:translate(-30px,30px) scale(0.9)} 75%{transform:translate(40px,20px) scale(1.05)} }
@keyframes fadeInDown { from{opacity:0;transform:translateY(-30px)} to{opacity:1;transform:translateY(0)} }
@keyframes fadeInUp { from{opacity:0;transform:translateY(30px) scale(0.95)} to{opacity:1;transform:translateY(0) scale(1)} }
@keyframes logoFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
@keyframes slideIn { from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:translateX(0)} }
.header-card, .welcome-card { animation: fadeInDown 0.6s ease-out !important; position: relative; z-index: 10; }
.stat-card { animation: fadeInUp 0.6s ease-out backwards !important; }
.stat-card:nth-child(1) { animation-delay: 0.1s !important; }
.stat-card:nth-child(2) { animation-delay: 0.2s !important; }
.stat-card:nth-child(3) { animation-delay: 0.3s !important; }
.stat-card:nth-child(4) { animation-delay: 0.4s !important; }
.table-card, .form-card { animation: fadeInUp 0.6s ease-out 0.4s backwards !important; }
tbody tr { animation: slideIn 0.4s ease-out backwards; }
tbody tr:nth-child(1) { animation-delay: 0.6s; }
tbody tr:nth-child(2) { animation-delay: 0.65s; }
tbody tr:nth-child(3) { animation-delay: 0.7s; }
tbody tr:nth-child(4) { animation-delay: 0.75s; }
tbody tr:nth-child(5) { animation-delay: 0.8s; }
tbody tr:nth-child(6) { animation-delay: 0.85s; }
tbody tr:nth-child(7) { animation-delay: 0.9s; }
tbody tr:nth-child(8) { animation-delay: 0.95s; }
tbody tr:nth-child(9) { animation-delay: 1s; }
tbody tr:nth-child(10) { animation-delay: 1.05s; }
::selection { background: #FF8C00; color: white; }
::-webkit-scrollbar { width: 10px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #FF8C00, #FFA500); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: linear-gradient(to bottom, #E67E00, #FF8C00); }
</style>
</head>
        <body>
            <div class=\"container mt-5\">
                <div class=\"alert alert-danger text-center\" role=\"alert\">
                    <i class=\"fas fa-times-circle fa-3x mb-3\"></i>
                    <h3>Error: Transferencia #$transferencia_id no encontrada</h3>
                    <p>La transferencia que busca no existe en el sistema.</p>
                    <hr>
                    <a href=\"transferencias_ui.php\" class=\"btn btn-warning btn-lg\">
                        <i class=\"fas fa-arrow-left me-2\"></i>Ir a Lista de Transferencias
                    </a>
                </div>
            </div>
        </body>
        </html>
        ");
    }
    
    // Obtener detalles de prendas si existen en JSON
    if (!empty($transferencia_data['datos_recepcion_json'])) {
        $detalles_prenda = json_decode($transferencia_data['datos_recepcion_json'], true);
    } else {
        // Si no hay en JSON, intentar desde detalle_prendas
        $detallePrenda = new DetallePrenda();
        $detalles_prenda = $detallePrenda->obtenerPorTransferencia($transferencia_id);
    }
} else {
    // Caso 2: No se especificó ID - crear nueva transferencia automáticamente
    $es_nueva = true;
    
    // Generar referencia única
    $referencia = 'EMP-' . date('Ymd-His') . '-' . sprintf('%04d', rand(1, 9999));
    
    // Crear nueva transferencia con datos por defecto
    $nueva_transferencia = [
        'referencia' => $referencia,
        'almacen_origen_id' => 1, // Almacén de Corte
        'almacen_destino_id' => 2, // Almacén de Empaque
        'control_entrada_id' => null,
        'trabajador_id' => null,
        'trabajador_nombre' => null,
        'total_items' => 0, // Se calculará al guardar
        'tipo_prenda' => null,
        'color' => null,
        'talla' => null,
        'estado' => 'pendiente',
        'usuario_creacion' => 'almacen2_directo',
        'observaciones' => 'Creado directamente desde Almacén 2'
    ];
    
    // Insertar en la base de datos
    try {
        $transferencia_id = $transferencia->crear($nueva_transferencia);
        if (!$transferencia_id) {
            throw new Exception('No se pudo crear la transferencia');
        }
        
        // Recargar la página con el nuevo ID
        header("Location: control_entrada_almacen2.php?id=$transferencia_id&nueva=1");
        exit;
    } catch (Exception $e) {
        die("
        <!DOCTYPE html>
        <html lang=\"es\">
        <head>
            <meta charset=\"UTF-8\">
            <title>Error</title>
            <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
        <style>
@keyframes gradientShift { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
body { background: linear-gradient(-45deg, #FF8C00, #FFB84D, #FFA500, #FF9933) !important; background-size: 400% 400% !important; animation: gradientShift 15s ease infinite !important; position: relative; overflow-x: hidden; }
body::before, body::after { content: ''; position: fixed; width: 400px; height: 400px; border-radius: 50%; opacity: 0.1; z-index: 0; }
body::before { background: radial-gradient(circle, white, transparent); top: -200px; left: -200px; animation: float 20s ease-in-out infinite; }
body::after { background: radial-gradient(circle, white, transparent); bottom: -200px; right: -200px; animation: float 25s ease-in-out infinite reverse; }
@keyframes float { 0%,100%{transform:translate(0,0) scale(1)} 25%{transform:translate(50px,-50px) scale(1.1)} 50%{transform:translate(-30px,30px) scale(0.9)} 75%{transform:translate(40px,20px) scale(1.05)} }
@keyframes fadeInDown { from{opacity:0;transform:translateY(-30px)} to{opacity:1;transform:translateY(0)} }
@keyframes fadeInUp { from{opacity:0;transform:translateY(30px) scale(0.95)} to{opacity:1;transform:translateY(0) scale(1)} }
@keyframes logoFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
@keyframes slideIn { from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:translateX(0)} }
.header-card, .welcome-card { animation: fadeInDown 0.6s ease-out !important; position: relative; z-index: 10; }
.stat-card { animation: fadeInUp 0.6s ease-out backwards !important; }
.stat-card:nth-child(1) { animation-delay: 0.1s !important; }
.stat-card:nth-child(2) { animation-delay: 0.2s !important; }
.stat-card:nth-child(3) { animation-delay: 0.3s !important; }
.stat-card:nth-child(4) { animation-delay: 0.4s !important; }
.table-card, .form-card { animation: fadeInUp 0.6s ease-out 0.4s backwards !important; }
tbody tr { animation: slideIn 0.4s ease-out backwards; }
tbody tr:nth-child(1) { animation-delay: 0.6s; }
tbody tr:nth-child(2) { animation-delay: 0.65s; }
tbody tr:nth-child(3) { animation-delay: 0.7s; }
tbody tr:nth-child(4) { animation-delay: 0.75s; }
tbody tr:nth-child(5) { animation-delay: 0.8s; }
tbody tr:nth-child(6) { animation-delay: 0.85s; }
tbody tr:nth-child(7) { animation-delay: 0.9s; }
tbody tr:nth-child(8) { animation-delay: 0.95s; }
tbody tr:nth-child(9) { animation-delay: 1s; }
tbody tr:nth-child(10) { animation-delay: 1.05s; }
::selection { background: #FF8C00; color: white; }
::-webkit-scrollbar { width: 10px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #FF8C00, #FFA500); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: linear-gradient(to bottom, #E67E00, #FF8C00); }
</style>
</head>
        <body>
            <div class=\"container mt-5\">
                <div class=\"alert alert-danger\">
                    <h3>Error al crear transferencia</h3>
                    <p>" . $e->getMessage() . "</p>
                    <a href=\"transferencias_ui.php\" class=\"btn btn-warning\">Volver</a>
                </div>
            </div>
        </body>
        </html>
        ");
    }
}

// Organizar detalles por número de item
$items_data = [];
if (is_array($detalles_prenda)) {
    foreach ($detalles_prenda as $detalle) {
        $numero = $detalle['numero_item'] ?? 0;
        if ($numero > 0) {
            $items_data[$numero] = $detalle;
        }
    }
}
$tiene_datos = !empty($items_data);

// Calcular estadísticas para el tablero
$stats_colores = [];
$stats_tallas = [];
$total_items_tablero = 0;

foreach ($items_data as $item) {
    $color = trim($item['color_codigo'] ?? $item['color'] ?? '');
    
    // Procesar todas las tallas
    $tallas = ['2', '4', '6', '8', '10', '12', '14', '16', 'XS', 'S', 'M', 'L', 'XL', 'XXL'];
    foreach ($tallas as $talla) {
        $key = 'talla_' . $talla;
        $cantidad = intval($item[$key] ?? 0);
        
        if ($cantidad > 0) {
            $total_items_tablero += $cantidad;
            
            // Contar por color
            if ($color !== '') {
                if (!isset($stats_colores[$color])) {
                    $stats_colores[$color] = 0;
                }
                $stats_colores[$color] += $cantidad;
            }
            
            // Contar por talla
            if (!isset($stats_tallas[$talla])) {
                $stats_tallas[$talla] = 0;
            }
            $stats_tallas[$talla] += $cantidad;
        }
    }
}

// Ordenar
arsort($stats_colores);
ksort($stats_tallas);

function h($s) { return htmlspecialchars($s ?? ''); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empaque | Procesar Recepción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
        .main-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .header-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-top: 4px solid #FF8C00;
        }
        .formulario-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .tabla-tallas {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla-tallas th, .tabla-tallas td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
            font-size: 11px;
        }
        .tabla-tallas th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .tabla-tallas input {
            width: 45px;
            text-align: center;
            border: none;
            background: transparent;
            padding: 4px;
        }
        .tabla-tallas input:focus {
            background-color: #fff3cd;
            outline: 2px solid #ffc107;
        }
        .tabla-tallas input.bg-warning {
            background-color: rgba(255, 193, 7, 0.15) !important;
            border: 1px solid #ffc107;
        }
        .color-codigo { width: 100px; }
        .observacion { width: 120px; }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .input-changed {
            background-color: #fff3cd !important;
            border-color: #ffc107 !important;
        }
        .total-display {
            background: linear-gradient(135deg, #FF8C00, #FFB84D);
            color: white;
            padding: 20px 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(255,140,0,0.5), 0 0 0 3px rgba(255,255,255,0.3);
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 200px;
            animation: totalFloat 3s ease-in-out infinite, totalFadeIn 0.6s ease-out;
            transition: all 0.3s ease;
        }
        
        @keyframes totalFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        
        @keyframes totalFadeIn {
            from { opacity: 0; transform: scale(0.8) translateY(-20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        
        .total-display:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(255,140,0,0.6), 0 0 0 3px rgba(255,255,255,0.5);
        }
        
        .total-display h4 {
            margin: 8px 0;
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: numberPulse 2s ease-in-out infinite;
        }
        
        @keyframes numberPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
        
        .total-display small {
            opacity: 0.95;
            font-size: 0.8rem;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .total-display i {
            animation: iconSpin 3s ease-in-out infinite;
        }
        
        @keyframes iconSpin {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(10deg); }
        }
        .validation-error {
            border-color: #dc3545 !important;
            background-color: #f8d7da !important;
        }
        .validation-success {
            border-color: #28a745 !important;
            background-color: #d4edda !important;
        }
    </style>
<style>
@keyframes gradientShift { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
body { background: linear-gradient(-45deg, #FF8C00, #FFB84D, #FFA500, #FF9933) !important; background-size: 400% 400% !important; animation: gradientShift 15s ease infinite !important; position: relative; overflow-x: hidden; }
body::before, body::after { content: ''; position: fixed; width: 400px; height: 400px; border-radius: 50%; opacity: 0.1; z-index: 0; }
body::before { background: radial-gradient(circle, white, transparent); top: -200px; left: -200px; animation: float 20s ease-in-out infinite; }
body::after { background: radial-gradient(circle, white, transparent); bottom: -200px; right: -200px; animation: float 25s ease-in-out infinite reverse; }
@keyframes float { 0%,100%{transform:translate(0,0) scale(1)} 25%{transform:translate(50px,-50px) scale(1.1)} 50%{transform:translate(-30px,30px) scale(0.9)} 75%{transform:translate(40px,20px) scale(1.05)} }
@keyframes fadeInDown { from{opacity:0;transform:translateY(-30px)} to{opacity:1;transform:translateY(0)} }
@keyframes fadeInUp { from{opacity:0;transform:translateY(30px) scale(0.95)} to{opacity:1;transform:translateY(0) scale(1)} }
@keyframes logoFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
@keyframes slideIn { from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:translateX(0)} }
.header-card, .welcome-card { animation: fadeInDown 0.6s ease-out !important; position: relative; z-index: 10; }
.stat-card { animation: fadeInUp 0.6s ease-out backwards !important; }
.stat-card:nth-child(1) { animation-delay: 0.1s !important; }
.stat-card:nth-child(2) { animation-delay: 0.2s !important; }
.stat-card:nth-child(3) { animation-delay: 0.3s !important; }
.stat-card:nth-child(4) { animation-delay: 0.4s !important; }
.table-card, .form-card { animation: fadeInUp 0.6s ease-out 0.4s backwards !important; }
tbody tr { animation: slideIn 0.4s ease-out backwards; }
tbody tr:nth-child(1) { animation-delay: 0.6s; }
tbody tr:nth-child(2) { animation-delay: 0.65s; }
tbody tr:nth-child(3) { animation-delay: 0.7s; }
tbody tr:nth-child(4) { animation-delay: 0.75s; }
tbody tr:nth-child(5) { animation-delay: 0.8s; }
tbody tr:nth-child(6) { animation-delay: 0.85s; }
tbody tr:nth-child(7) { animation-delay: 0.9s; }
tbody tr:nth-child(8) { animation-delay: 0.95s; }
tbody tr:nth-child(9) { animation-delay: 1s; }
tbody tr:nth-child(10) { animation-delay: 1.05s; }
::selection { background: #FF8C00; color: white; }
::-webkit-scrollbar { width: 10px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #FF8C00, #FFA500); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: linear-gradient(to bottom, #E67E00, #FF8C00); }
</style>
</head>
<body>
    <?php $loading_message = 'Cargando Control de Entrada...'; ?>
    <?php include __DIR__ . '/includes/loading_screen.php'; ?>
    
    <div class="container-fluid">
        <div class="main-container">
            
            <!-- Header -->
            <div class="header-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="/2/img/logo.jpg" alt="logo" style="height:50px;margin-right:15px;" onerror="this.style.display='none'">
                        <div>
                            <h3 class="mb-0"><i class="fas fa-clipboard-check me-2 text-warning"></i>Procesar Recepción</h3>
                            <small class="text-muted">Área de Empaque | Control de entrada de transferencias</small>
                        </div>
                    </div>
                    <div>
                        <a href="transferencias_ui.php" class="btn btn-warning">
                            <i class="fas fa-arrow-left me-2"></i>Volver a Lista
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Mensaje de Nueva Transferencia -->
            <?php if (isset($_GET['nueva'])): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-plus-circle me-2"></i>Nueva Transferencia Creada
                </h5>
                <p class="mb-0">Se ha creado una nueva transferencia con ID <strong>#<?= $transferencia_id ?></strong></p>
                <p class="mb-0">Referencia: <strong><?= htmlspecialchars($transferencia_data['referencia']) ?></strong></p>
                <hr>
                <small class="text-muted">Complete los datos a continuación y haga click en "Guardar y Confirmar"</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <!-- Mensaje de Éxito -->
            <?php if (isset($_GET['success'])): ?>
                <?php
                $tipo_mensaje = $_GET['success'];
                $mensajes = [
                    'completado' => [
                        'icono' => 'fa-check-circle',
                        'titulo' => '¡Transferencia Completada!',
                        'texto' => 'La transferencia ha sido procesada completamente. Todas las prendas han sido recibidas.',
                        'clase' => 'alert-success'
                    ],
                    'parcial' => [
                        'icono' => 'fa-exclamation-triangle',
                        'titulo' => 'Recepción Parcial Guardada',
                        'texto' => 'Los datos han sido guardados correctamente. Aún faltan prendas por completar.',
                        'clase' => 'alert-warning'
                    ],
                    'recibido' => [
                        'icono' => 'fa-check',
                        'titulo' => 'Datos Guardados',
                        'texto' => 'La información de recepción ha sido guardada correctamente.',
                        'clase' => 'alert-info'
                    ]
                ];
                $mensaje = $mensajes[$tipo_mensaje] ?? $mensajes['recibido'];
                ?>
                <div class="alert <?= $mensaje['clase'] ?> alert-dismissible fade show shadow-sm" role="alert">
                    <h5 class="alert-heading">
                        <i class="fas <?= $mensaje['icono'] ?> me-2"></i><?= $mensaje['titulo'] ?>
                    </h5>
                    <p class="mb-0"><?= $mensaje['texto'] ?></p>
                    <hr>
                    <div class="mb-0">
                        <a href="transferencias_ui.php" class="btn btn-sm btn-outline-dark me-2">
                            <i class="fas fa-list me-1"></i>Ver Todas las Transferencias
                        </a>
                        <?php if ($tipo_mensaje === 'parcial'): ?>
                        <a href="control_entrada_almacen2.php?id=<?= $_GET['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Agregar Más Prendas
                        </a>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Información de Transferencia -->
            <div class="formulario-container">
                <?php if ($transferencia_data): ?>
                <div class="alert alert-info border-info mb-4">
                    <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Información de Transferencia</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <strong><i class="fas fa-barcode me-1"></i>Referencia:</strong><br>
                            <span class="text-warning fs-5"><?= htmlspecialchars($transferencia_data['referencia']) ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-arrow-right me-1"></i>Origen:</strong><br>
                            <?= htmlspecialchars($transferencia_data['almacen_origen']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-warehouse me-1"></i>Destino:</strong><br>
                            <?= htmlspecialchars($transferencia_data['almacen_destino']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-boxes me-1"></i>Total Items:</strong><br>
                            <span class="badge bg-primary fs-6"><?= htmlspecialchars($transferencia_data['total_items']) ?></span>
                        </div>
                    </div>
                </div>
                
                <?php if ($tiene_datos): ?>
                    <?php
                    // Calcular total recibido
                    $total_recibido = 0;
                    foreach ($items_data as $item) {
                        $total_recibido += intval($item['talla_2'] ?? 0);
                        $total_recibido += intval($item['talla_4'] ?? 0);
                        $total_recibido += intval($item['talla_6'] ?? 0);
                        $total_recibido += intval($item['talla_8'] ?? 0);
                        $total_recibido += intval($item['talla_10'] ?? 0);
                        $total_recibido += intval($item['talla_12'] ?? 0);
                        $total_recibido += intval($item['talla_14'] ?? 0);
                        $total_recibido += intval($item['talla_16'] ?? 0);
                        $total_recibido += intval($item['talla_xs'] ?? 0);
                        $total_recibido += intval($item['talla_s'] ?? 0);
                        $total_recibido += intval($item['talla_m'] ?? 0);
                        $total_recibido += intval($item['talla_l'] ?? 0);
                        $total_recibido += intval($item['talla_xl'] ?? 0);
                        $total_recibido += intval($item['talla_xxl'] ?? 0);
                    }
                    $total_esperado = $transferencia_data['total_items'];
                    $porcentaje = $total_esperado > 0 ? round(($total_recibido / $total_esperado) * 100, 1) : 0;
                    $faltante = $total_esperado - $total_recibido;
                    ?>
                    
                    <?php if ($total_recibido > 0): ?>
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Progreso de Recepción:</strong>
                            </div>
                            <div>
                                <span class="badge bg-primary me-2"><?= $total_recibido ?> / <?= $total_esperado ?> items</span>
                                <span class="badge bg-<?= $porcentaje >= 100 ? 'success' : 'warning' ?>"><?= $porcentaje ?>%</span>
                            </div>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar <?= $porcentaje >= 100 ? 'bg-success' : 'bg-warning' ?>" 
                                 role="progressbar" 
                                 style="width: <?= min($porcentaje, 100) ?>%;" 
                                 aria-valuenow="<?= $porcentaje ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= $porcentaje ?>%
                            </div>
                        </div>
                        <?php if ($faltante > 0): ?>
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Faltan <strong><?= $faltante ?></strong> prendas por completar
                        </small>
                        <?php else: ?>
                        <small class="text-success mt-2 d-block">
                            <i class="fas fa-check-circle me-1"></i>
                            Todas las prendas han sido recibidas
                        </small>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Datos Pre-cargados:</strong> Los datos fueron enviados por Área de Corte. Puedes editarlos si hay diferencias.
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php endif; ?>
        
                <form id="formulario-control" method="POST" action="/2/api/transferencias.php/<?= $transferencia_id ?>/confirmar_trabajador">
                    
                    <!-- Información General -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning bg-opacity-25">
                            <i class="fas fa-calendar me-2"></i>Información de Recepción
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Fecha de Recepción <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="fecha_recepcion" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Tipo de Prenda</label>
                                    <input type="text" class="form-control <?= !empty($transferencia_data['tipo_prenda']) ? 'bg-warning bg-opacity-10' : '' ?>" 
                                           name="tipo_prenda" 
                                           value="<?= htmlspecialchars($transferencia_data['tipo_prenda'] ?? '') ?>" 
                                           placeholder="Ej: Camisa dama...">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Trabajador Encargado <span class="text-danger">*</span></label>
                                    <select class="form-select" name="trabajador_id" required>
                                        <option value="">-- Seleccionar trabajador --</option>
                                        <?php foreach ($trabajadores as $trab): ?>
                                            <option value="<?= $trab['id'] ?>" 
                                                <?= (isset($transferencia_data['trabajador_id']) && $transferencia_data['trabajador_id'] == $trab['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($trab['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (!empty($transferencia_data['trabajador_nombre'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>Asignado: <?= htmlspecialchars($transferencia_data['trabajador_nombre']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Datos del Recepcionista -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning bg-opacity-25">
                            <i class="fas fa-user-check me-2"></i>Datos del Recepcionista
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Nombre del Recepcionista</label>
                                    <input type="text" class="form-control" name="recepcionista" placeholder="Nombre completo">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label"><i class="fas fa-star text-warning me-1"></i>Puntos a Favor</label>
                                    <div class="d-flex gap-2">
                                        <div>
                                            <small class="text-muted d-block">S/. 0.00</small>
                                            <input type="number" class="form-control" step="0.01" name="puntos_1" placeholder="0.00" style="width: 90px;">
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">S/. 10.00</small>
                                            <input type="number" class="form-control" step="0.01" name="puntos_2" placeholder="0.00" style="width: 90px;">
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">S/. 15.00</small>
                                            <input type="number" class="form-control" step="0.01" name="puntos_3" placeholder="0.00" style="width: 90px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tablero de Estadísticas de Prendas -->
                    <?php if ($transferencia_data): ?>
                    <div class="card mb-3" style="background: linear-gradient(135deg, #FFF8F0 0%, #FFFFFF 100%); border-left: 4px solid #FF8C00;">
                        <div class="card-header bg-warning bg-opacity-25">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-bar text-imbox-orange me-2"></i>
                                Resumen de Datos Recibidos
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Total de Items -->
                            <div class="alert alert-warning border-0 mb-3" style="background: linear-gradient(135deg, #FFA500 0%, #FFB84D 100%);">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <?php if ($total_items_tablero > 0): ?>
                                        <h4 class="text-white mb-0">
                                            <i class="fas fa-boxes me-2"></i>
                                            Total de Prendas Ingresadas: <strong><?php echo number_format($total_items_tablero); ?></strong>
                                        </h4>
                                        <p class="text-white mb-0" style="opacity: 0.9; font-size: 0.9em;">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Suma de todas las tallas en la tabla
                                            <?php if (!empty($transferencia_data['tipo_prenda'])): ?>
                                                · Tipo: <strong><?= h($transferencia_data['tipo_prenda']) ?></strong>
                                            <?php endif; ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="text-white" style="font-size: 2.5rem; font-weight: bold; opacity: 0.3;">
                                            <?php echo $total_items_tablero > 0 ? '✅' : '📦'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Por Color -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-palette me-2"></i>
                                                Distribución por Color
                                            </h6>
                                        </div>
                                        <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Color / Código</th>
                                                        <th class="text-end">Cantidad</th>
                                                        <th class="text-end">%</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($stats_colores as $color => $cantidad): 
                                                        $porcentaje = $total_items_tablero > 0 ? ($cantidad / $total_items_tablero) * 100 : 0;
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-light text-dark">
                                                                <?php echo h($color); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong class="text-success"><?php echo number_format($cantidad); ?></strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <small class="text-muted"><?php echo number_format($porcentaje, 1); ?>%</small>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <?php if (empty($stats_colores)): ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">
                                                            <i class="fas fa-arrow-down fa-2x mb-2 d-block"></i>
                                                            <strong>Ingresa colores en la tabla</strong>
                                                            <br>
                                                            <small>Los datos aparecerán aquí automáticamente</small>
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Por Talla -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="fas fa-ruler me-2"></i>
                                                Distribución por Talla
                                            </h6>
                                        </div>
                                        <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Talla</th>
                                                        <th class="text-end">Cantidad</th>
                                                        <th class="text-end">%</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($stats_tallas as $talla => $cantidad): 
                                                        $porcentaje = $total_items_tablero > 0 ? ($cantidad / $total_items_tablero) * 100 : 0;
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-light text-dark">
                                                                <?php echo h($talla); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong class="text-warning"><?php echo number_format($cantidad); ?></strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <small class="text-muted"><?php echo number_format($porcentaje, 1); ?>%</small>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <?php if (empty($stats_tallas)): ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">
                                                            <i class="fas fa-arrow-down fa-2x mb-2 d-block"></i>
                                                            <strong>Ingresa cantidades por talla</strong>
                                                            <br>
                                                            <small>El resumen aparecerá aquí automáticamente</small>
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nota informativa -->
                            <?php if ($total_items_tablero > 0): ?>
                            <div class="alert alert-success border-0 mt-3 mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Datos ingresados:</strong> El resumen muestra <?php echo number_format($total_items_tablero); ?> prendas registradas.
                                <?php 
                                $esperado = intval($transferencia_data['total_items'] ?? 0);
                                if ($esperado > 0 && $total_items_tablero != $esperado): 
                                    $diferencia = abs($esperado - $total_items_tablero);
                                ?>
                                    <br><i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>Nota:</strong> Se esperaban <?php echo $esperado; ?> items 
                                    (diferencia: <?php echo $total_items_tablero > $esperado ? '+' : '-'; ?><?php echo $diferencia; ?>)
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Tabla de Tallas -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning bg-opacity-25">
                            <i class="fas fa-table me-2"></i>Detalle de Tallas por Color
                            <?php if ($tiene_datos): ?>
                                <span class="badge bg-success ms-2"><i class="fas fa-check me-1"></i>Con datos</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="tabla-tallas">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">N°</th>
                                            <th rowspan="2">COLOR / CÓDIGO</th>
                                            <th colspan="8">TALLAS NUMÉRICAS</th>
                                            <th colspan="6">TALLAS LETRAS</th>
                                            <th rowspan="2">TOTAL</th>
                                            <th rowspan="2">OBSERVACIÓN</th>
                                        </tr>
                                        <tr>
                                            <th>2</th><th>4</th><th>6</th><th>8</th><th>10</th><th>12</th><th>14</th><th>16</th>
                                            <th>XS</th><th>S</th><th>M</th><th>L</th><th>XL</th><th>XXL</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-items">
                                        <?php for ($i = 1; $i <= 20; $i++): 
                                        $item = $items_data[$i] ?? null;
                                        ?>
                                        <tr>
                                            <td><strong><?= $i ?></strong></td>
                                            <td>
                                                <input type="text" name="items[<?= $i ?>][color_codigo]" 
                                                       class="color-codigo <?= $item ? 'bg-warning' : '' ?>" 
                                                       value="<?= htmlspecialchars($item['color_codigo'] ?? $item['color'] ?? '') ?>">
                                            </td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_2]" class="talla-input <?= ($item && !empty($item['talla_2'])) ? 'bg-warning' : '' ?>" data-talla="2" min="0" value="<?= $item['talla_2'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_4]" class="talla-input <?= ($item && !empty($item['talla_4'])) ? 'bg-warning' : '' ?>" data-talla="4" min="0" value="<?= $item['talla_4'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_6]" class="talla-input <?= ($item && !empty($item['talla_6'])) ? 'bg-warning' : '' ?>" data-talla="6" min="0" value="<?= $item['talla_6'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_8]" class="talla-input <?= ($item && !empty($item['talla_8'])) ? 'bg-warning' : '' ?>" data-talla="8" min="0" value="<?= $item['talla_8'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_10]" class="talla-input <?= ($item && !empty($item['talla_10'])) ? 'bg-warning' : '' ?>" data-talla="10" min="0" value="<?= $item['talla_10'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_12]" class="talla-input <?= ($item && !empty($item['talla_12'])) ? 'bg-warning' : '' ?>" data-talla="12" min="0" value="<?= $item['talla_12'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_14]" class="talla-input <?= ($item && !empty($item['talla_14'])) ? 'bg-warning' : '' ?>" data-talla="14" min="0" value="<?= $item['talla_14'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_16]" class="talla-input <?= ($item && !empty($item['talla_16'])) ? 'bg-warning' : '' ?>" data-talla="16" min="0" value="<?= $item['talla_16'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_xs]" class="talla-input <?= ($item && !empty($item['talla_xs'])) ? 'bg-warning' : '' ?>" data-talla="xs" min="0" value="<?= $item['talla_xs'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_s]" class="talla-input <?= ($item && !empty($item['talla_s'])) ? 'bg-warning' : '' ?>" data-talla="s" min="0" value="<?= $item['talla_s'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_m]" class="talla-input <?= ($item && !empty($item['talla_m'])) ? 'bg-warning' : '' ?>" data-talla="m" min="0" value="<?= $item['talla_m'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_l]" class="talla-input <?= ($item && !empty($item['talla_l'])) ? 'bg-warning' : '' ?>" data-talla="l" min="0" value="<?= $item['talla_l'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_xl]" class="talla-input <?= ($item && !empty($item['talla_xl'])) ? 'bg-warning' : '' ?>" data-talla="xl" min="0" value="<?= $item['talla_xl'] ?? '' ?>"></td>
                                            <td><input type="number" name="items[<?= $i ?>][talla_xxl]" class="talla-input <?= ($item && !empty($item['talla_xxl'])) ? 'bg-warning' : '' ?>" data-talla="xxl" min="0" value="<?= $item['talla_xxl'] ?? '' ?>"></td>
                                            <td><span class="total-fila fw-bold" id="total-fila-<?= $i ?>">0</span></td>
                                            <td><input type="text" name="items[<?= $i ?>][observacion]" class="observacion" placeholder="..." value="<?= htmlspecialchars($item['observacion'] ?? '') ?>"></td>
                                        </tr>
                                        <?php endfor; ?>
                                        <tr class="total-row">
                                            <td colspan="2">TOTAL</td>
                                            <td><span id="total-2">0</span></td>
                                            <td><span id="total-4">0</span></td>
                                            <td><span id="total-6">0</span></td>
                                            <td><span id="total-8">0</span></td>
                                            <td><span id="total-10">0</span></td>
                                            <td><span id="total-12">0</span></td>
                                            <td><span id="total-14">0</span></td>
                                            <td><span id="total-16">0</span></td>
                                            <td><span id="total-xs">0</span></td>
                                            <td><span id="total-s">0</span></td>
                                            <td><span id="total-m">0</span></td>
                                            <td><span id="total-l">0</span></td>
                                            <td><span id="total-xl">0</span></td>
                                            <td><span id="total-xxl">0</span></td>
                                            <td><span id="total-general" class="fs-5">0</span></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumen de Datos a Enviar -->
                    <div class="card mb-3 bg-light">
                        <div class="card-header bg-info bg-opacity-25">
                            <i class="fas fa-info-circle me-2"></i>Resumen de Datos a Enviar
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <th>Transferencia ID:</th>
                                            <td><strong><?= $transferencia_id ?? 'N/A' ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Trabajador:</th>
                                            <td id="resumen-trabajador">-</td>
                                        </tr>
                                        <tr>
                                            <th>Fecha Recepción:</th>
                                            <td id="resumen-fecha">-</td>
                                        </tr>
                                        <tr>
                                            <th>Tipo Prenda:</th>
                                            <td id="resumen-tipo">-</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <th>Total Prendas:</th>
                                            <td><strong id="resumen-total-prendas">0</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Items con Datos:</th>
                                            <td id="resumen-items-count">0</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="fas fa-check me-2"></i>Guardar y Confirmar
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg px-5" onclick="limpiarFormulario()">
                            <i class="fas fa-eraser me-2"></i>Limpiar
                        </button>
                        <a href="transferencias_ui.php" class="btn btn-warning btn-lg px-4 ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/2/js/tabla-tallas-excel.js"></script>
    <script>
        function actualizarResumen() {
            const form = document.getElementById('formulario-control');
            const trabajadorSelect = form.querySelector('[name="trabajador_id"]');
            const fechaInput = form.querySelector('[name="fecha_recepcion"]');
            const tipoPrendaInput = form.querySelector('[name="tipo_prenda"]');
            
            // Actualizar resumen
            const trabajadorText = trabajadorSelect.options[trabajadorSelect.selectedIndex]?.text || '-';
            document.getElementById('resumen-trabajador').textContent = trabajadorText;
            document.getElementById('resumen-fecha').textContent = fechaInput.value || '-';
            document.getElementById('resumen-tipo').textContent = tipoPrendaInput.value || '-';
            
            // Contar items con datos
            let itemsCount = 0;
            for (let i = 1; i <= 20; i++) {
                const colorInput = form.querySelector(`[name="items[${i}][color_codigo]"]`);
                if (colorInput && colorInput.value.trim()) {
                    itemsCount++;
                }
            }
            document.getElementById('resumen-items-count').textContent = itemsCount;
            document.getElementById('resumen-total-prendas').textContent = document.getElementById('total-general').textContent;
        }
        
        function calcularTotales() {
            const tallas = ['2', '4', '6', '8', '10', '12', '14', '16', 'xs', 's', 'm', 'l', 'xl', 'xxl'];
            const totalesPorTalla = {};
            let totalGeneral = 0;
            
            tallas.forEach(talla => totalesPorTalla[talla] = 0);
            
            for (let i = 1; i <= 20; i++) {
                let totalFila = 0;
                tallas.forEach(talla => {
                    const input = document.querySelector(`input[name="items[${i}][talla_${talla}]"]`);
                    const valor = parseInt(input.value) || 0;
                    totalesPorTalla[talla] += valor;
                    totalFila += valor;
                });
                document.getElementById(`total-fila-${i}`).textContent = totalFila;
                totalGeneral += totalFila;
            }
            
            tallas.forEach(talla => {
                document.getElementById(`total-${talla}`).textContent = totalesPorTalla[talla];
            });
            
            document.getElementById('total-general').textContent = totalGeneral;
            
            // Actualizar resumen
            actualizarResumen();
            
            // Actualizar total flotante
            const totalFlotante = document.getElementById('total-flotante-numero');
            if (totalFlotante) {
                totalFlotante.textContent = totalGeneral;
                // Animación
                totalFlotante.style.transform = 'scale(1.2)';
                setTimeout(() => totalFlotante.style.transform = 'scale(1)', 200);
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Event listeners para calcular totales
            document.querySelectorAll('.talla-input, .color-codigo').forEach(input => {
                input.addEventListener('input', calcularTotales);
                // Guardar valor original
                input.dataset.originalValue = input.value;
            });
            
            // Actualizar resumen cuando cambian los campos principales
            document.querySelector('[name="trabajador_id"]')?.addEventListener('change', actualizarResumen);
            document.querySelector('[name="fecha_recepcion"]')?.addEventListener('change', actualizarResumen);
            document.querySelector('[name="tipo_prenda"]')?.addEventListener('input', actualizarResumen);
            
            calcularTotales();
            actualizarResumen();
            mostrarIndicadorTotal();
        });
        
        // Marcar campos modificados
        function marcarCambio(input) {
            if (input.value !== input.dataset.originalValue) {
                input.classList.add('input-changed');
            } else {
                input.classList.remove('input-changed');
            }
        }
        
        // Validar que sea número
        function validarNumero(input) {
            const valor = parseInt(input.value);
            if (input.value && (isNaN(valor) || valor < 0)) {
                input.classList.add('validation-error');
                input.classList.remove('validation-success');
            } else if (input.value) {
                input.classList.add('validation-success');
                input.classList.remove('validation-error');
                setTimeout(() => input.classList.remove('validation-success'), 1000);
            } else {
                input.classList.remove('validation-error', 'validation-success');
            }
        }
        
        // Mostrar indicador de total fijo
        function mostrarIndicadorTotal() {
            // Crear div flotante si no existe
            if (!document.getElementById('total-flotante')) {
                const div = document.createElement('div');
                div.id = 'total-flotante';
                div.className = 'total-display';
                div.innerHTML = `
                    <small><i class="fas fa-clipboard-list me-1"></i><strong>Total Prendas</strong></small>
                    <h4 id="total-flotante-numero">0</h4>
                    <small>items registrados</small>
                `;
                document.body.appendChild(div);
                
                // Actualizar el total inmediatamente
                setTimeout(() => {
                    const totalGeneral = parseInt(document.getElementById('total-general')?.textContent) || 0;
                    document.getElementById('total-flotante-numero').textContent = totalGeneral;
                }, 100);
            }
        }
        
        function limpiarFormulario() {
            if (confirm('¿Está seguro de que desea limpiar el formulario?')) {
                document.getElementById('formulario-control').reset();
                calcularTotales();
            }
        }
        
        document.getElementById('formulario-control').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const trabajadorId = document.querySelector('select[name="trabajador_id"]').value;
            if (!trabajadorId) {
                alert('❌ Debe seleccionar un trabajador encargado.');
                return;
            }
            
            const totalGeneral = parseInt(document.getElementById('total-general').textContent);
            if (totalGeneral === 0) {
                if (!confirm('⚠️ No se han registrado cantidades. ¿Desea continuar?')) {
                    return;
                }
            }
            
            // Mostrar indicador de carga
            const btnSubmit = document.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
            
            try {
                // Enviar formulario
                const formData = new FormData(this);
                
                // Log para debugging
                console.log('=== ENVIANDO DATOS ===');
                console.log('URL:', this.action);
                console.log('Trabajador ID:', formData.get('trabajador_id'));
                console.log('Fecha:', formData.get('fecha_recepcion'));
                console.log('Tipo Prenda:', formData.get('tipo_prenda'));
                
                // Contar items
                let itemsCount = 0;
                for (let i = 1; i <= 20; i++) {
                    const color = formData.get(`items[${i}][color_codigo]`);
                    if (color) itemsCount++;
                }
                console.log('Items con datos:', itemsCount);
                
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Status:', response.status);
                console.log('URL final:', response.url);
                
                const contentType = response.headers.get('content-type');
                
                // Si la respuesta es JSON, procesarla
                if (contentType && contentType.includes('application/json')) {
                    const result = await response.json();
                    
                    if (result.success) {
                        // Mensaje de éxito
                        const mensaje = result.message || 'Datos guardados correctamente';
                        
                        // Crear modal de éxito personalizado
                        const modal = document.createElement('div');
                        modal.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:15px;box-shadow:0 10px 40px rgba(0,0,0,0.3);z-index:10000;max-width:500px;text-align:center;';
                        modal.innerHTML = `
                            <div style="color:#28a745;font-size:60px;margin-bottom:20px;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 style="color:#28a745;margin-bottom:15px;">¡Éxito!</h3>
                            <p style="color:#666;margin-bottom:25px;">${mensaje}</p>
                            <button onclick="window.location.href='${result.redirect || this.action}'" 
                                    style="background:#28a745;color:white;border:none;padding:12px 30px;border-radius:8px;font-size:16px;cursor:pointer;">
                                Continuar
                            </button>
                        `;
                        document.body.appendChild(modal);
                    } else {
                        // Mensaje de error formateado
                        const mensaje = result.message || 'No se pudo guardar';
                        
                        // Crear overlay de fondo
                        const overlay = document.createElement('div');
                        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;';
                        overlay.onclick = function() { 
                            overlay.remove(); 
                            modal.remove();
                        };
                        document.body.appendChild(overlay);
                        
                        // Crear modal de error personalizado
                        const modal = document.createElement('div');
                        modal.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:15px;box-shadow:0 10px 40px rgba(0,0,0,0.3);z-index:10000;max-width:500px;';
                        modal.innerHTML = `
                            <div style="color:#dc3545;font-size:50px;text-align:center;margin-bottom:20px;">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <h3 style="color:#dc3545;text-align:center;margin-bottom:15px;">Error al Guardar</h3>
                            <div style="background:#fff3cd;padding:15px;border-radius:8px;border-left:4px solid #ffc107;margin-bottom:20px;">
                                <p style="color:#856404;margin:0;white-space:pre-line;text-align:left;font-size:14px;line-height:1.6;">${mensaje}</p>
                            </div>
                            <div style="text-align:center;">
                                <button onclick="this.parentElement.parentElement.previousSibling.remove();this.parentElement.parentElement.remove()" 
                                        style="background:#dc3545;color:white;border:none;padding:12px 30px;border-radius:8px;font-size:16px;cursor:pointer;">
                                    Entendido
                                </button>
                            </div>
                        `;
                        document.body.appendChild(modal);
                        
                        // Restaurar botón
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalText;
                    }
                } else {
                    // Si no es JSON, es una redirección exitosa
                    const text = await response.text();
                    
                    // Si la respuesta contiene HTML con un mensaje de éxito
                    if (text.includes('success=')) {
                        window.location.href = response.url;
                    } else {
                        // Mostrar el HTML para debugging
                        console.error('Respuesta inesperada:', text);
                        alert('❌ Error inesperado. Ver consola para detalles.');
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalText;
                    }
                }
                
            } catch (error) {
                console.error('Error al enviar:', error);
                alert('❌ Error de conexión: ' + error.message);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>
