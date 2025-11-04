<?php
/**
 * Dashboard - Almacén 1 (Corte)
 * Panel de control principal con estadísticas y resumen
 */

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
require_once 'config.php';

// Obtener base de datos
$db = getDB();

// =====================================================
// ESTADÍSTICAS GENERALES
// =====================================================

// Controles de entrada
$stmt = $db->query("SELECT COUNT(*) as total FROM controles_entrada");
$total_controles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM controles_entrada WHERE estado = 'pendiente'");
$controles_pendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM controles_entrada WHERE estado = 'completado'");
$controles_completados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Estadísticas del mes actual
$mes_actual = date('Y-m');
$stmt = $db->query("SELECT COUNT(*) as total FROM controles_entrada WHERE fecha_entrada LIKE '$mes_actual%'");
$controles_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Últimos controles de entrada
$stmt = $db->query("SELECT * FROM controles_entrada ORDER BY id DESC LIMIT 5");
$ultimos_controles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totales de materiales
$stmt = $db->query("SELECT SUM(total_rollos) as total_rollos, SUM(total_metros) as total_metros FROM controles_entrada");
$totales = $stmt->fetch(PDO::FETCH_ASSOC);
$total_rollos = $totales['total_rollos'] ?? 0;
$total_metros = $totales['total_metros'] ?? 0;

// Estadísticas por proveedor (top 5)
$stmt = $db->query("
    SELECT proveedor, COUNT(*) as cantidad, SUM(total_metros) as metros
    FROM controles_entrada 
    WHERE proveedor IS NOT NULL AND proveedor != ''
    GROUP BY proveedor 
    ORDER BY cantidad DESC 
    LIMIT 5
");
$top_proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Actividad reciente (últimas 24 horas)
$ayer = date('Y-m-d H:i:s', strtotime('-24 hours'));
$stmt = $db->query("SELECT COUNT(*) as total FROM controles_entrada WHERE fecha_creacion >= '$ayer'");
$actividad_24h = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Almacén 1 Corte | IMBOX</title>
    
    <!-- Preload crítico -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Tema IMBOX -->
    <link rel="stylesheet" href="css/theme-orange.css">
    <style>
        /* Estilos específicos del Dashboard */
        
        /* Pantalla de carga */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FF8C00 0%, #FFB84D 50%, #FFA500 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        
        #loading-screen.hidden {
            opacity: 0;
            visibility: hidden;
        }
        
        .loader {
            width: 80px;
            height: 80px;
            border: 8px solid rgba(255, 255, 255, 0.3);
            border-top: 8px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            margin-top: 30px;
            color: white;
            font-size: 1.5em;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            animation: fadeInOut 2s ease-in-out infinite;
        }
        
        @keyframes fadeInOut {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .loading-progress {
            width: 200px;
            height: 4px;
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
            margin-top: 20px;
            overflow: hidden;
        }
        
        .loading-progress-bar {
            height: 100%;
            background: white;
            border-radius: 10px;
            animation: progress 2s ease-in-out infinite;
        }
        
        @keyframes progress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }
        
        .loading-logo {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: pulse 2s ease-in-out infinite;
        }
        
        .loading-logo img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* Asegurar que los iconos sean visibles */
        i[class*="fa-"],
        i.fas,
        i.far,
        i.fal,
        i.fab {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "FontAwesome" !important;
            font-weight: 900 !important;
            font-style: normal !important;
            display: inline-block !important;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-variant: normal;
            line-height: 1;
        }
        
        /* Asegurar que el pseudo-elemento se muestre */
        i[class*="fa-"]:before,
        i.fas:before {
            display: inline-block !important;
        }
        
        body {
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #2C2C2C;
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 0;
        }
        
        .header h1 i {
            color: #FF8C00;
        }
        
        .header small {
            color: #6c757d;
            display: block;
            margin-top: 5px;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: var(--gradient-card);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            color: white;
        }

        .header-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .user-badge {
            background: #FFF5E6;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            color: #FF8C00;
            font-weight: 600;
        }
        
        .user-badge i {
            margin-right: 5px;
        }

        .date-time {
            font-size: 0.9em;
            color: #6c757d;
            font-weight: 500;
        }
        
        .date-time i {
            color: #FF8C00;
            margin: 0 3px;
        }

        .nav {
            background: white;
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-md);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }

        .nav .nav-link {
            padding: 14px 28px;
            background: #FFF5E6;
            color: #1a1a1a !important;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-weight: 700;
            font-size: 1.05em;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .nav .nav-link span,
        .nav .nav-link {
            opacity: 1 !important;
        }
        
        .nav .nav-link i {
            color: #FF8C00 !important;
            font-size: 1.2em;
        }

        .nav .nav-link:hover {
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 140, 0, 0.3);
        }
        
        .nav .nav-link:hover i {
            color: white !important;
        }

        .nav .nav-link.active {
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            color: white !important;
            box-shadow: 0 3px 8px rgba(255, 140, 0, 0.4);
        }
        
        .nav .nav-link.active i {
            color: white !important;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 60px !important;
            height: 60px !important;
            border-radius: 12px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 1.8em !important;
            flex-shrink: 0 !important;
            position: relative !important;
            opacity: 1 !important;
            transform: none !important;
            right: auto !important;
            top: auto !important;
        }
        
        .stat-icon i {
            color: white !important;
            font-size: 1em !important;
            line-height: 1 !important;
            opacity: 1 !important;
        }

        .stat-icon.orange { 
            background: #FF8C00;
        }
        
        .stat-icon.success { 
            background: #28A745;
        }
        
        .stat-icon.warning { 
            background: #FFC107;
        }
        
        .stat-icon.info { 
            background: #17A2B8;
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #2C2C2C;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.95em;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-change {
            font-size: 0.85em;
            margin-top: 10px;
            color: #6c757d;
        }

        .stat-change.up { 
            color: var(--color-success);
            font-weight: 600;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255, 140, 0, 0.2);
        }

        .card-title {
            font-size: 1.3em;
            color: #2C2C2C;
            font-weight: 600;
        }
        
        .card-title i {
            color: #FF8C00;
            margin-right: 8px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #2C2C2C;
            font-size: 0.9em;
            border-bottom: 2px solid #FF8C00;
        }
        
        table tbody td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            color: #2C2C2C;
        }
        
        table tbody tr:hover {
            background-color: #FFF5E6;
        }

        .card-action {
            color: var(--imbox-primary);
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.3s;
        }

        .card-action:hover {
            color: var(--imbox-primary-dark);
            text-decoration: underline;
        }

        .provider-list {
            list-style: none;
        }

        .provider-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: var(--imbox-light);
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .provider-item:hover {
            background: rgba(255, 140, 0, 0.15);
            transform: translateX(5px);
        }

        .provider-name {
            font-weight: 600;
            color: #2C2C2C;
        }
        
        .provider-name i {
            color: #FFA500;
            margin-right: 5px;
        }

        .provider-stats {
            display: flex;
            gap: 15px;
            font-size: 0.85em;
            color: #6c757d;
            font-weight: 500;
        }
        
        .provider-stats i {
            color: #FF8C00;
            margin-right: 3px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .action-btn {
            padding: 15px;
            background: var(--gradient-card);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .action-btn:hover {
            background: var(--gradient-header);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .action-btn.secondary {
            background: #6c757d;
        }

        .action-btn.secondary:hover {
            background: var(--imbox-dark);
        }

        .footer {
            text-align: center;
            color: white;
            margin-top: 30px;
            font-size: 0.9em;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 3em;
            margin-bottom: 15px;
            color: #FF8C00;
            opacity: 0.3;
        }
        
        .empty-state-icon i {
            color: #FF8C00;
        }
        
        .empty-state p {
            color: #6c757d;
            font-weight: 500;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Pantalla de Carga -->
    <div id="loading-screen">
        <div class="loading-logo">
            <i class="fas fa-box" style="font-size: 2.5em; color: #FF8C00;"></i>
        </div>
        <div class="loader"></div>
        <div class="loading-text">Cargando Panel de Control...</div>
        <div class="loading-progress">
            <div class="loading-progress-bar"></div>
        </div>
        <div style="margin-top: 15px; color: rgba(255,255,255,0.8); font-size: 0.9em;">
            <i class="fas fa-warehouse"></i> Almacén 1 - Área de Corte
        </div>
        <div style="margin-top: 8px; color: rgba(255,255,255,0.6); font-size: 0.85em;">
            Sistema IMBOX v1.0
        </div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <img src="img/logo.jpg" alt="Logo IMBOX" style="height:50px; border-radius: 8px;" onerror="this.style.display='none'">
                <div>
                    <h1 style="margin: 0; font-size: 1.8em;">
                        <i class="fas fa-tachometer-alt" style="color: var(--imbox-primary);"></i>
                        Panel de Control
                    </h1>
                    <small style="color: #6c757d;">Almacén 1 - Área de Corte</small>
                </div>
            </div>
            <div class="header-info">
                <div class="user-badge">
                    <i class="fas fa-user"></i> WILDER - Encargado
                </div>
                <div class="date-time">
                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y'); ?> | 
                    <i class="fas fa-clock"></i> <span id="current-time"><?php echo date('H:i:s'); ?></span>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="nav">
            <a href="index.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i> Panel de Control
            </a>
            <a href="sistema_completo.php" class="nav-link" style="background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%); color: white; font-weight: 600;">
                <i class="fas fa-star"></i> Sistema Completo
            </a>
            <a href="control_entrada.php" class="nav-link">
                <i class="fas fa-clipboard-check"></i> Control de Entrada
            </a>
            <a href="transferencias.php" class="nav-link">
                <i class="fas fa-paper-plane"></i> Transferencias
            </a>
            <a href="ver_transferencias.php" class="nav-link">
                <i class="fas fa-list-ul"></i> Ver Transferencias
            </a>
            <a href="transferencias_excel.php" class="nav-link">
                <i class="fas fa-table"></i> Vista Excel
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo number_format($total_controles); ?></div>
                        <div class="stat-label">Total Controles</div>
                    </div>
                    <div class="stat-icon orange">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
                <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?php echo $controles_mes; ?> este mes</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo number_format($controles_pendientes); ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stat-change">En proceso</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo number_format($total_rollos); ?></div>
                        <div class="stat-label">Total Rollos</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-scroll"></i>
                    </div>
                </div>
                <div class="stat-change"><?php echo number_format($total_metros, 2); ?> metros</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo number_format($actividad_24h); ?></div>
                        <div class="stat-label">Últimas 24h</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-change up">Actividad reciente</div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Controls -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-list-alt"></i> Últimos Controles de Entrada
                    </h2>
                    <a href="control_entrada.php" class="card-action">
                        Ver todos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php if (count($ultimos_controles) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Rollos</th>
                                    <th>Metros</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimos_controles as $control): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($control['referencia']); ?></strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($control['fecha_entrada'])); ?></td>
                                    <td><?php echo htmlspecialchars($control['proveedor'] ?? 'N/A'); ?></td>
                                    <td><?php echo number_format($control['total_rollos'] ?? 0); ?></td>
                                    <td><?php echo number_format($control['total_metros'] ?? 0, 2); ?></td>
                                    <td>
                                        <?php 
                                        $estado = $control['estado'] ?? 'pendiente';
                                        $badge_class = $estado === 'completado' ? 'badge-success' : 'badge-warning';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($estado); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <p>No hay controles de entrada registrados</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Quick Actions -->
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-bolt"></i> Acciones Rápidas
                        </h2>
                    </div>
                    <div class="quick-actions">
                        <a href="control_entrada.php" class="action-btn">
                            <i class="fas fa-plus-circle"></i> Nuevo Control
                        </a>
                        <a href="transferencias.php" class="action-btn">
                            <i class="fas fa-truck"></i> Nueva Transferencia
                        </a>
                        <a href="ver_transferencias.php" class="action-btn secondary">
                            <i class="fas fa-list-ul"></i> Ver Transferencias
                        </a>
                        <a href="transferencias_excel.php" class="action-btn secondary">
                            <i class="fas fa-table"></i> Vista Excel
                        </a>
                        <a href="#" class="action-btn secondary">
                            <i class="fas fa-chart-bar"></i> Reportes
                        </a>
                    </div>
                </div>

                <!-- Top Providers -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-industry"></i> Top Proveedores
                        </h2>
                    </div>
                    <?php if (count($top_proveedores) > 0): ?>
                        <ul class="provider-list">
                            <?php foreach ($top_proveedores as $index => $proveedor): ?>
                            <li class="provider-item">
                                <div>
                                    <div class="provider-name">
                                        <i class="fas fa-star" style="color: #FFA500; font-size: 0.8em;"></i>
                                        <?php echo ($index + 1); ?>. <?php echo htmlspecialchars($proveedor['proveedor']); ?>
                                    </div>
                                </div>
                                <div class="provider-stats">
                                    <span><i class="fas fa-box"></i> <?php echo $proveedor['cantidad']; ?></span>
                                    <span><i class="fas fa-ruler"></i> <?php echo number_format($proveedor['metros'], 2); ?>m</span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-industry"></i>
                            </div>
                            <p>No hay proveedores registrados</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>IMBOX</strong> - Sistema de Control de Almacén | Almacén 1 - Área de Corte</p>
            <p>Versión 1.0 - Octubre 2025</p>
        </div>
    </div>

    <script>
        // Ocultar pantalla de carga cuando todo esté listo
        window.addEventListener('load', function() {
            // Esperar un momento adicional para animación suave
            setTimeout(function() {
                const loadingScreen = document.getElementById('loading-screen');
                loadingScreen.classList.add('hidden');
                
                // Remover del DOM después de la transición
                setTimeout(function() {
                    loadingScreen.remove();
                }, 500);
            }, 300);
        });
        
        // Ocultar si tarda mucho (máximo 5 segundos)
        setTimeout(function() {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                loadingScreen.classList.add('hidden');
                setTimeout(function() {
                    loadingScreen.remove();
                }, 500);
            }
        }, 5000);
        
        // Verificar que Font Awesome se haya cargado
        window.addEventListener('load', function() {
            const testIcon = document.querySelector('.fas');
            if (testIcon) {
                const computedStyle = window.getComputedStyle(testIcon, ':before');
                const content = computedStyle.getPropertyValue('content');
                
                if (content === 'none' || content === '') {
                    console.error('Font Awesome no se cargó correctamente');
                    // Recargar Font Awesome desde CDN alternativo
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
                    document.head.appendChild(link);
                }
            }
        });

        // Actualizar reloj en tiempo real
        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('current-time').textContent = timeStr;
        }
        
        setInterval(updateTime, 1000);
        
        // Auto-refresh cada 5 minutos para actualizar estadísticas
        setTimeout(() => {
            location.reload();
        }, 5 * 60 * 1000);
    </script>
</body>
</html>
