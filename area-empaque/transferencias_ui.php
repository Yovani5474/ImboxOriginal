<?php
require_once __DIR__ . '/models/Transferencia.php';
require_once __DIR__ . '/models/Trabajador.php';

$t = new Transferencia();
$tr = new Trabajador();
$list = $t->listar(100,0);
$trabajadores = $tr->obtenerTodos();

function h($s){return htmlspecialchars($s);} 

// Detectar si es modo solo lectura (viene desde Almacén 1)
$modo_solo_lectura = isset($_GET['modo']) && $_GET['modo'] === 'ver';

// Mostrar mensaje de éxito si existe
$success = $_GET['success'] ?? null;

// Calcular estadísticas
$stats = [
    'total' => count($list),
    'pendiente' => 0,
    'recibido' => 0,
    'completado' => 0,
    'total_items' => 0
];

foreach ($list as $row) {
    $estado = strtolower($row['estado']);
    if ($estado === 'pendiente' || $estado === 'enviado') {
        $stats['pendiente']++;
    } elseif ($estado === 'recibido' || $estado === 'parcial') {
        $stats['recibido']++;
    } elseif ($estado === 'completado') {
        $stats['completado']++;
    }
    $stats['total_items'] += intval($row['total_items']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empaque | Transferencias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/2/css/theme-orange.css">
    <link rel="stylesheet" href="/2/css/styles.css">
    <style>
        /* DISEÑO ULTRA-PREMIUM - TRANSFERENCIAS */
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        body {
            background: linear-gradient(-45deg, #FF8C00, #FFB84D, #FFA500, #FF9933) !important;
            background-size: 400% 400% !important;
            animation: gradientShift 15s ease infinite !important;
        }
        body::before, body::after {
            content: ''; position: fixed; width: 400px; height: 400px;
            border-radius: 50%; opacity: 0.1; z-index: 0;
        }
        body::before {
            background: radial-gradient(circle, white, transparent);
            top: -200px; left: -200px; animation: float 20s ease-in-out infinite;
        }
        body::after {
            background: radial-gradient(circle, white, transparent);
            bottom: -200px; right: -200px; animation: float 25s ease-in-out infinite reverse;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, -50px) scale(1.1); }
            50% { transform: translate(-30px, 30px) scale(0.9); }
            75% { transform: translate(40px, 20px) scale(1.05); }
        }
        .header-card {
            animation: fadeInDown 0.6s ease-out !important;
            position: relative; z-index: 10;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stat-card {
            animation: fadeInUp 0.6s ease-out backwards !important;
        }
        .stat-card:nth-child(1) { animation-delay: 0.1s !important; }
        .stat-card:nth-child(2) { animation-delay: 0.2s !important; }
        .stat-card:nth-child(3) { animation-delay: 0.3s !important; }
        .stat-card:nth-child(4) { animation-delay: 0.4s !important; }
        .table-card {
            animation: fadeInUp 0.6s ease-out 0.5s backwards !important;
        }
        tr {
            animation: slideIn 0.4s ease-out backwards;
        }
        tbody tr:nth-child(1) { animation-delay: 0.6s; }
        tbody tr:nth-child(2) { animation-delay: 0.65s; }
        tbody tr:nth-child(3) { animation-delay: 0.7s; }
        tbody tr:nth-child(4) { animation-delay: 0.75s; }
        tbody tr:nth-child(5) { animation-delay: 0.8s; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .badge {
            animation: badgePulse 2s ease-in-out infinite;
        }
        @keyframes badgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        ::selection { background: #FF8C00; color: white; }
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #FF8C00, #FFA500); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: linear-gradient(to bottom, #E67E00, #FF8C00); }
    </style>
</head>
<body>
    <?php $loading_message = 'Cargando Transferencias...'; ?>
    <?php include __DIR__ . '/includes/loading_screen.php'; ?>
    
    <div class="container-fluid">
        <div class="main-container">
            
            <!-- Header -->
            <div class="header-card fade-in">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <img src="/2/img/logo.jpg" alt="logo IMBOX" style="height:60px;margin-right:15px;filter:drop-shadow(0 3px 10px rgba(255,140,0,0.3));animation:logoFloat 3s ease-in-out infinite;" onerror="this.style.display='none'">
                        <style>@keyframes logoFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }</style>
                        <div>
                            <h2 class="mb-1 text-orange"><i class="fas fa-exchange-alt me-2"></i>Transferencias Recibidas</h2>
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Área de Empaque | Verificar entregas y confirmar con el costurero</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="control_entrada_almacen2.php" class="btn btn-orange">
                            <i class="fas fa-clipboard-check me-2"></i>Nueva Recepción
                        </a>
                        <a href="reportes.php" class="btn btn-success">
                            <i class="fas fa-file-export me-2"></i>Reportes
                        </a>
                        <a href="index.php" class="btn btn-outline-orange">
                            <i class="fas fa-home me-2"></i>Inicio
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if ($modo_solo_lectura): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-eye me-2"></i>
                <strong>Modo Solo Lectura</strong> - Estás visualizando desde el Almacén de Corte. No puedes realizar cambios desde aquí.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>¡Éxito!</strong> La transferencia ha sido confirmada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card orange text-center slide-in">
                        <i class="stat-icon fas fa-inbox"></i>
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Total Transferencias</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card yellow text-center slide-in" style="animation-delay: 0.1s">
                        <i class="stat-icon fas fa-clock"></i>
                        <div class="stat-number" style="color: #FFC107;"><?php echo $stats['pendiente']; ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card green text-center slide-in" style="animation-delay: 0.2s">
                        <i class="stat-icon fas fa-check-circle"></i>
                        <div class="stat-number" style="color: #28A745;"><?php echo $stats['recibido'] + $stats['completado']; ?></div>
                        <div class="stat-label">Completadas</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card blue text-center slide-in" style="animation-delay: 0.3s">
                        <i class="stat-icon fas fa-boxes"></i>
                        <div class="stat-number" style="color: #17A2B8;"><?php echo $stats['total_items']; ?></div>
                        <div class="stat-label">Total Items</div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de Transferencias -->
            <div class="table-card fade-in">
                <h5 class="text-orange mb-4">
                    <i class="fas fa-table me-2"></i>
                    Registro de Transferencias
                    <span class="badge badge-orange ms-2"><?php echo $stats['total']; ?></span>
                </h5>
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Vista completa con detalles de tipo de prenda, color, talla y trabajador asignado
                </p>
                
                <?php if (empty($list)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No hay transferencias disponibles en este momento</p>
                </div>
                <?php else: ?>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th><i class="fas fa-barcode me-1"></i>Referencia</th>
                                <th class="text-center"><i class="fas fa-boxes me-1"></i>Items</th>
                                <th><i class="fas fa-tshirt me-1"></i>Tipo Prenda</th>
                                <th><i class="fas fa-palette me-1"></i>Color</th>
                                <th><i class="fas fa-ruler me-1"></i>Talla</th>
                                <th><i class="fas fa-user-tie me-1"></i>Trabajador</th>
                                <th class="text-center">Estado</th>
                                <th><i class="fas fa-clock me-1"></i>Fecha</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($list as $row): ?>
                            <tr>
                                <td class="text-muted"><strong>#<?= h($row['id']) ?></strong></td>
                                <td>
                                    <strong class="text-imbox-orange"><?= h($row['referencia']) ?></strong>
                                    <?php if (!empty($row['observaciones'])): ?>
                                        <br><small class="text-muted"><?= h(substr($row['observaciones'], 0, 30)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= h($row['total_items']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($row['tipo_prenda'])): ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-tag me-1"></i><?= h($row['tipo_prenda']) ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['color'])): ?>
                                        <span class="text-dark">
                                            <i class="fas fa-paint-brush me-1 text-muted"></i><?= h($row['color']) ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['talla'])): ?>
                                        <span class="badge bg-dark">
                                            <i class="fas fa-ruler-vertical me-1"></i><?= h($row['talla']) ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['trabajador_nombre']): ?>
                                        <i class="fas fa-user me-1 text-success"></i><?= h($row['trabajador_nombre']) ?>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-user-slash me-1"></i>Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="estado-badge estado-<?= h($row['estado']) ?>">
                                        <?php 
                                            $estados = [
                                                'pendiente' => 'Pendiente',
                                                'enviado' => 'Enviado',
                                                'recibido' => 'Recibido',
                                                'parcial' => 'Falta Completar',
                                                'completado' => 'Completado'
                                            ];
                                            echo $estados[$row['estado']] ?? ucfirst($row['estado']); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($row['fecha_creacion'])) ?>
                                        <br><?= date('H:i', strtotime($row['fecha_creacion'])) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <!-- Botón Ver -->
                                        <a href="ver_transferencia.php?id=<?= $row['id'] ?>" 
                                           class="btn btn-info btn-sm" 
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Botón Editar -->
                                        <a href="editar_transferencia.php?id=<?= $row['id'] ?>" 
                                           class="btn btn-warning btn-sm" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- Botón Procesar o Completado -->
                                        <?php if ($row['estado'] == 'enviado' || $row['estado'] == 'pendiente' || $row['estado'] == 'parcial'): ?>
                                            <a href="control_entrada_almacen2.php?id=<?= $row['id'] ?>" 
                                               class="btn btn-success btn-sm"
                                               title="<?= $row['estado'] == 'parcial' ? 'Completar recepción' : 'Procesar recepción' ?>">
                                                <i class="fas fa-clipboard-check"></i>
                                            </a>
                                        <?php elseif ($row['estado'] == 'completado'): ?>
                                            <button class="btn btn-success btn-sm" disabled title="Completado">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php elseif ($row['estado'] == 'recibido'): ?>
                                            <a href="control_entrada_almacen2.php?id=<?= $row['id'] ?>" 
                                               class="btn btn-warning btn-sm"
                                               title="Revisar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php endif; ?>
            </div>
            <!-- Fin tabla-card -->
            
        </div>
        <!-- Fin main-container -->
    </div>
    <!-- Fin container-fluid -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
