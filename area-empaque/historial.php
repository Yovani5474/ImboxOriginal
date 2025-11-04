<?php
/**
 * Historial de Cambios - Auditoría Completa
 */

session_start();

require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/HistorialCambios.php';
require_once __DIR__ . '/models/Transferencia.php';

// Verificar autenticación
if (!Usuario::sesionActiva()) {
    header('Location: login.php');
    exit;
}

// Verificar permiso
if (!Usuario::tienePermiso('ver_historial')) {
    die('Acceso denegado. No tienes permisos para ver el historial.');
}

$historial = new HistorialCambios();
$t = new Transferencia();

// Obtener filtros
$filtros = [
    'transferencia_id' => $_GET['transferencia_id'] ?? '',
    'usuario' => $_GET['usuario'] ?? '',
    'accion' => $_GET['accion'] ?? '',
    'fecha_desde' => $_GET['fecha_desde'] ?? '',
    'fecha_hasta' => $_GET['fecha_hasta'] ?? date('Y-m-d')
];

// Paginación
$registros_por_pagina = 50;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener datos
$cambios = $historial->listar($filtros, $registros_por_pagina, $offset);
$total_registros = $historial->contar($filtros);
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Estadísticas
$stats = $historial->obtenerEstadisticas();

// Transferencias para el filtro
$transferencias = $t->listar(100, 0);

$usuario_actual = Usuario::usuarioActual();

function h($s) { return htmlspecialchars($s ?? ''); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empaque | Historial de Cambios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/2/css/theme-orange.css">
    <link rel="stylesheet" href="/2/css/styles.css">
    <style>
        .stat-mini {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid var(--imbox-primary);
            transition: all 0.3s ease;
        }
        .stat-mini:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(255,140,0,0.2);
        }
        .stat-mini-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--imbox-primary);
        }
        .stat-mini-label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .cambio-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .cambio-item.modificacion { border-left-color: #FFC107; }
        .cambio-item.creacion { border-left-color: #28A745; }
        .cambio-item.eliminacion { border-left-color: #DC3545; }
        .valor-cambio {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.9rem;
        }
        .valor-anterior {
            background: #ffe6e6;
            color: #dc3545;
            text-decoration: line-through;
        }
        .valor-nuevo {
            background: #d4edda;
            color: #28a745;
        }
        .user-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
        }
        .user-badge.admin { background: #dc3545; color: white; }
        .user-badge.supervisor { background: #ffc107; color: black; }
        .user-badge.operador { background: #17a2b8; color: white; }
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
    <?php $loading_message = 'Cargando Historial de Cambios...'; ?>
    <?php include __DIR__ . '/includes/loading_screen.php'; ?>
    
    <div class="container-fluid">
        <div class="main-container">
            
            <!-- Header -->
            <div class="header-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="/2/img/logo.jpg" alt="Logo" style="height:50px;margin-right:15px;" onerror="this.style.display='none'">
                        <div>
                            <h3 class="mb-0 text-info">
                                <i class="fas fa-history me-2"></i>Historial de Cambios
                            </h3>
                            <small class="text-muted">Área de Empaque | Auditoría de modificaciones</small>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-info me-2">
                            <i class="fas fa-user me-1"></i><?php echo h($usuario_actual['nombre']); ?>
                        </span>
                        <a href="transferencias_ui.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-list me-2"></i>Transferencias
                        </a>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-mini">
                        <i class="fas fa-list-alt fa-2x text-info mb-2"></i>
                        <div class="stat-mini-number"><?php echo $total_registros; ?></div>
                        <div class="stat-mini-label">Total Cambios</div>
                    </div>
                </div>
                <?php foreach ($stats as $stat): ?>
                <div class="col-md-3">
                    <div class="stat-mini">
                        <i class="fas fa-edit fa-2x text-warning mb-2"></i>
                        <div class="stat-mini-number"><?php echo $stat['total_por_accion']; ?></div>
                        <div class="stat-mini-label"><?php echo ucfirst($stat['accion']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Filtros -->
            <div class="filter-card">
                <h6 class="text-info mb-3">
                    <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
                </h6>
                <form method="GET" id="filtrosForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Transferencia</label>
                            <select class="form-select form-select-sm" name="transferencia_id">
                                <option value="">Todas</option>
                                <?php foreach ($transferencias as $tf): ?>
                                    <option value="<?php echo $tf['id']; ?>" 
                                            <?php echo ($filtros['transferencia_id'] == $tf['id']) ? 'selected' : ''; ?>>
                                        #<?php echo $tf['id']; ?> - <?php echo h($tf['referencia']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Usuario</label>
                            <input type="text" class="form-control form-control-sm" name="usuario" 
                                   value="<?php echo h($filtros['usuario']); ?>" placeholder="Buscar...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Acción</label>
                            <select class="form-select form-select-sm" name="accion">
                                <option value="">Todas</option>
                                <option value="creacion" <?php echo ($filtros['accion'] == 'creacion') ? 'selected' : ''; ?>>Creación</option>
                                <option value="modificacion" <?php echo ($filtros['accion'] == 'modificacion') ? 'selected' : ''; ?>>Modificación</option>
                                <option value="eliminacion" <?php echo ($filtros['accion'] == 'eliminacion') ? 'selected' : ''; ?>>Eliminación</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Desde</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_desde" 
                                   value="<?php echo h($filtros['fecha_desde']); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hasta</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_hasta" 
                                   value="<?php echo h($filtros['fecha_hasta']); ?>">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-info btn-sm w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Lista de Cambios -->
            <div class="mb-3">
                <h5 class="text-muted">
                    <i class="fas fa-clock me-2"></i>Registro de Actividad
                    <span class="badge bg-secondary ms-2"><?php echo $total_registros; ?> registros</span>
                </h5>
            </div>

            <?php if (empty($cambios)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <p class="mb-0">No se encontraron cambios con los filtros aplicados</p>
                </div>
            <?php else: ?>
                <?php foreach ($cambios as $cambio): ?>
                <div class="cambio-item <?php echo $cambio['accion']; ?>">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <strong class="text-info">#<?php echo $cambio['transferencia_id']; ?></strong>
                            <?php if ($cambio['transferencia_ref']): ?>
                                <br><small class="text-muted"><?php echo h($cambio['transferencia_ref']); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2">
                            <span class="user-badge <?php echo $cambio['rol']; ?>">
                                <i class="fas fa-user me-1"></i><?php echo h($cambio['usuario']); ?>
                            </span>
                            <br><small class="text-muted"><?php echo ucfirst($cambio['rol']); ?></small>
                        </div>
                        <div class="col-md-2">
                            <strong><?php echo ucfirst($cambio['accion']); ?></strong>
                            <br><small class="text-muted"><?php echo h($cambio['campo_modificado']); ?></small>
                        </div>
                        <div class="col-md-4">
                            <?php if ($cambio['valor_anterior']): ?>
                                <span class="valor-cambio valor-anterior">
                                    <?php echo h($cambio['valor_anterior']); ?>
                                </span>
                                <i class="fas fa-arrow-right mx-2 text-muted"></i>
                            <?php endif; ?>
                            <span class="valor-cambio valor-nuevo">
                                <?php echo h($cambio['valor_nuevo']); ?>
                            </span>
                        </div>
                        <div class="col-md-2 text-end">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('d/m/Y', strtotime($cambio['fecha_cambio'])); ?>
                                <br>
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('H:i:s', strtotime($cambio['fecha_cambio'])); ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if ($cambio['ip_address'] || $cambio['user_agent']): ?>
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted">
                            <?php if ($cambio['ip_address']): ?>
                                <i class="fas fa-network-wired me-1"></i>IP: <?php echo h($cambio['ip_address']); ?>
                            <?php endif; ?>
                            <?php if ($cambio['user_agent']): ?>
                                | <i class="fas fa-desktop me-1"></i><?php echo h(substr($cambio['user_agent'], 0, 80)); ?>...
                            <?php endif; ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>&<?php echo http_build_query($filtros); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagina_actual - 2); $i <= min($total_paginas, $pagina_actual + 2); $i++): ?>
                        <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $i; ?>&<?php echo http_build_query($filtros); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>&<?php echo http_build_query($filtros); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
