<?php
/**
 * Ver Transferencia - Detalles Completos
 */

require_once __DIR__ . '/models/Transferencia.php';
require_once __DIR__ . '/models/Trabajador.php';
require_once __DIR__ . '/models/DetallePrenda.php';

$t = new Transferencia();
$tr = new Trabajador();
$dp = new DetallePrenda();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener la transferencia
$transferencia = null;
if ($id > 0) {
    $transferencia = $t->obtenerPorId($id);
}

if (!$transferencia) {
    header('Location: transferencias_ui.php');
    exit;
}

// Obtener detalles de prendas
$detalles = $dp->obtenerPorTransferencia($id);

function h($s) { return htmlspecialchars($s ?? ''); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Transferencia #<?php echo $id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/2/css/styles.css">
    <link rel="stylesheet" href="/2/css/almacen1.css">
    <style>
        body { background-color: #f5f5f5; }
        .main-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info-label {
            font-weight: bold;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .info-value {
            font-size: 1.1rem;
            color: #212529;
        }
        .estado-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 1rem;
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
    <div class="container-fluid">
        <div class="main-container">
            
            <!-- Header -->
            <div class="info-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0 text-info">
                            <i class="fas fa-file-alt me-2"></i>Detalles de Transferencia #<?php echo $id; ?>
                        </h3>
                        <small class="text-muted">Información completa de la transferencia</small>
                    </div>
                    <div>
                        <a href="editar_transferencia.php?id=<?php echo $id; ?>" class="btn btn-warning me-2">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                        <a href="transferencias_ui.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información General -->
            <div class="info-card">
                <h5 class="text-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>Información General
                </h5>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="info-label">Referencia</div>
                        <div class="info-value text-imbox-orange">
                            <i class="fas fa-barcode me-2"></i><?php echo h($transferencia['referencia']); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Estado</div>
                        <div class="info-value">
                            <span class="estado-badge estado-<?php echo h($transferencia['estado']); ?>">
                                <?php 
                                    $estados = [
                                        'pendiente' => 'Pendiente',
                                        'enviado' => 'Enviado',
                                        'recibido' => 'Recibido',
                                        'parcial' => 'Falta Completar',
                                        'completado' => 'Completado'
                                    ];
                                    echo $estados[$transferencia['estado']] ?? ucfirst($transferencia['estado']); 
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Origen</div>
                        <div class="info-value">
                            <i class="fas fa-arrow-right me-2 text-muted"></i><?php echo h($transferencia['almacen_origen']); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Destino</div>
                        <div class="info-value">
                            <i class="fas fa-warehouse me-2 text-muted"></i><?php echo h($transferencia['almacen_destino']); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Total Items</div>
                        <div class="info-value">
                            <span class="badge bg-primary fs-6"><?php echo h($transferencia['total_items']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles de la Prenda -->
            <div class="info-card">
                <h5 class="text-info mb-4">
                    <i class="fas fa-tshirt me-2"></i>Detalles de la Prenda
                </h5>
                
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="info-label">Tipo de Prenda</div>
                        <div class="info-value">
                            <?php if (!empty($transferencia['tipo_prenda'])): ?>
                                <span class="badge bg-secondary fs-6">
                                    <i class="fas fa-tag me-1"></i><?php echo h($transferencia['tipo_prenda']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">No especificado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Color</div>
                        <div class="info-value">
                            <?php if (!empty($transferencia['color'])): ?>
                                <i class="fas fa-paint-brush me-2 text-muted"></i><?php echo h($transferencia['color']); ?>
                            <?php else: ?>
                                <span class="text-muted">No especificado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Talla</div>
                        <div class="info-value">
                            <?php if (!empty($transferencia['talla'])): ?>
                                <span class="badge bg-dark fs-6">
                                    <i class="fas fa-ruler-vertical me-1"></i><?php echo h($transferencia['talla']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">No especificada</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trabajador y Fechas -->
            <div class="info-card">
                <h5 class="text-info mb-4">
                    <i class="fas fa-user-clock me-2"></i>Asignación y Fechas
                </h5>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="info-label">Trabajador Encargado</div>
                        <div class="info-value">
                            <?php if (!empty($transferencia['trabajador_nombre'])): ?>
                                <i class="fas fa-user me-2 text-success"></i><?php echo h($transferencia['trabajador_nombre']); ?>
                            <?php else: ?>
                                <span class="text-muted">Sin asignar</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Control de Entrada ID</div>
                        <div class="info-value">
                            <?php if (!empty($transferencia['control_entrada_id'])): ?>
                                <span class="badge bg-info">#<?php echo h($transferencia['control_entrada_id']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">No registrado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Fecha de Creación</div>
                        <div class="info-value">
                            <i class="fas fa-calendar me-2 text-muted"></i>
                            <?php echo date('d/m/Y H:i', strtotime($transferencia['fecha_creacion'])); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Fecha de Recepción</div>
                        <div class="info-value">
                            <?php if (!empty($transferencia['fecha_recepcion']) && $transferencia['fecha_recepcion'] != '0000-00-00 00:00:00'): ?>
                                <i class="fas fa-calendar-check me-2 text-success"></i>
                                <?php echo date('d/m/Y H:i', strtotime($transferencia['fecha_recepcion'])); ?>
                            <?php else: ?>
                                <span class="text-muted">Pendiente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <?php if (!empty($transferencia['observaciones'])): ?>
            <div class="info-card">
                <h5 class="text-info mb-3">
                    <i class="fas fa-sticky-note me-2"></i>Observaciones
                </h5>
                <div class="alert alert-warning mb-0">
                    <?php echo nl2br(h($transferencia['observaciones'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Detalles de Tallas (si existen) -->
            <?php if (!empty($detalles)): ?>
            <div class="info-card">
                <h5 class="text-info mb-4">
                    <i class="fas fa-list-ul me-2"></i>Detalles de Tallas por Item
                </h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Color/Código</th>
                                <th class="text-center">2</th>
                                <th class="text-center">4</th>
                                <th class="text-center">6</th>
                                <th class="text-center">8</th>
                                <th class="text-center">10</th>
                                <th class="text-center">12</th>
                                <th class="text-center">14</th>
                                <th class="text-center">16</th>
                                <th class="text-center">XS</th>
                                <th class="text-center">S</th>
                                <th class="text-center">M</th>
                                <th class="text-center">L</th>
                                <th class="text-center">XL</th>
                                <th class="text-center">XXL</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $detalle): ?>
                            <tr>
                                <td><?php echo h($detalle['numero_item']); ?></td>
                                <td><?php echo h($detalle['color_codigo']); ?></td>
                                <td class="text-center"><?php echo $detalle['talla_2'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_4'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_6'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_8'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_10'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_12'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_14'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_16'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_xs'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_s'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_m'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_l'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_xl'] ?: '-'; ?></td>
                                <td class="text-center"><?php echo $detalle['talla_xxl'] ?: '-'; ?></td>
                                <td class="text-center">
                                    <strong><?php echo $detalle['total_fila'] ?? 0; ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
