<?php
/**
 * Dashboard Principal - Área de Empaque
 */

session_start();

require_once __DIR__ . '/models/Transferencia.php';
require_once __DIR__ . '/models/Trabajador.php';
require_once __DIR__ . '/models/Usuario.php';

$t = new Transferencia();
$tr = new Trabajador();

// Usuario actual (si está autenticado)
$usuario_actual = null;
if (Usuario::sesionActiva()) {
    $usuario_actual = Usuario::usuarioActual();
}

// Obtener estadísticas
$transferencias = $t->listar(100, 0);
$trabajadores = $tr->obtenerTodos();

$stats = [
    'total_transferencias' => count($transferencias),
    'pendientes' => 0,
    'recibidas' => 0,
    'completadas' => 0,
    'total_trabajadores' => count($trabajadores),
    'trabajadores_activos' => 0
];

foreach ($transferencias as $tf) {
    $estado = strtolower($tf['estado'] ?? 'pendiente');
    if ($estado === 'pendiente' || $estado === 'enviado') {
        $stats['pendientes']++;
    } elseif ($estado === 'recibido' || $estado === 'parcial') {
        $stats['recibidas']++;
    } elseif ($estado === 'completado') {
        $stats['completadas']++;
    }
}

foreach ($trabajadores as $trabajador) {
    if (isset($trabajador['activo']) && $trabajador['activo'] == 1) {
        $stats['trabajadores_activos']++;
    }
}

// Últimas transferencias
$ultimas_transferencias = array_slice($transferencias, 0, 5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empaque | Panel de Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/2/css/styles.css">
    <link rel="stylesheet" href="/2/css/almacen1.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ================================================
           PANTALLA DE CARGA
           ================================================ */
        
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
        
        .loading-logo i {
            font-size: 2.5em;
            color: #FF8C00;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* ================================================
           DISEÑO ULTRA-PREMIUM DASHBOARD - ALMACÉN 2
           ================================================ */
        
        /* Animación de fondo con gradiente dinámico */
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        body { 
            background: linear-gradient(-45deg, #FF8C00, #FFB84D, #FFA500, #FF9933);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            padding: 30px 0;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Partículas flotantes en el fondo */
        body::before,
        body::after {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            opacity: 0.1;
            z-index: 0;
        }
        
        body::before {
            background: radial-gradient(circle, white, transparent);
            top: -200px;
            left: -200px;
            animation: float 20s ease-in-out infinite;
        }
        
        body::after {
            background: radial-gradient(circle, white, transparent);
            bottom: -200px;
            right: -200px;
            animation: float 25s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(50px, -50px) scale(1.1); }
            50% { transform: translate(-30px, 30px) scale(0.9); }
            75% { transform: translate(40px, 20px) scale(1.05); }
        }
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }
        
        /* Animación de entrada */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,140,0,0.1);
            border-top: 6px solid #FF8C00;
            animation: fadeInUp 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        /* Efecto de brillo en el borde del welcome card */
        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 20px;
            padding: 2px;
            background: linear-gradient(45deg, #FF8C00, #FFB84D, #FFA500, #FF8C00);
            background-size: 300% 300%;
            animation: borderGlow 5s ease infinite;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0.3;
            pointer-events: none;
        }
        
        @keyframes borderGlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .welcome-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #FF8C00;
            margin-bottom: 10px;
            animation: titleFloat 3s ease-in-out infinite;
            position: relative;
            z-index: 2;
        }
        
        @keyframes titleFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        .welcome-title i {
            animation: iconPulse 2s ease-in-out infinite;
            display: inline-block;
        }
        
        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
        .welcome-subtitle {
            font-size: 1.2rem;
            color: #6c757d;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 5px solid;
            animation: fadeInUp 0.6s ease-out backwards;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255,140,0,0.1), transparent);
            border-radius: 50%;
            transform: translate(50%, -50%);
            transition: all 0.4s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 40px rgba(255,140,0,0.4);
        }
        
        .stat-card:hover::after {
            transform: translate(50%, -50%) scale(2);
        }
        
        /* Delays escalonados para las cards */
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card.orange { border-left-color: #FF8C00; }
        .stat-card.warning { border-left-color: #FFC107; }
        .stat-card.success { border-left-color: #28A745; }
        .stat-card.info { border-left-color: #17A2B8; }
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            margin: 10px 0;
            animation: numberPulse 2s ease-in-out infinite;
            position: relative;
            z-index: 2;
        }
        
        @keyframes numberPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .action-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
            border: 2px solid transparent;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255,140,0,0.3);
            border-color: #FF8C00;
            color: inherit;
        }
        .action-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 15px;
        }
        .action-icon.orange { background: linear-gradient(135deg, #FF8C00, #FFB84D); color: white; }
        .action-icon.success { background: linear-gradient(135deg, #28A745, #5CB85C); color: white; }
        .action-icon.info { background: linear-gradient(135deg, #17A2B8, #5BC0DE); color: white; }
        .action-icon.warning { background: linear-gradient(135deg, #FFC107, #FFD54F); color: white; }
        .action-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .action-desc {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .recent-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .recent-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        .recent-item:hover {
            background: #fffaf0;
        }
        .recent-item:last-child {
            border-bottom: none;
        }
        .badge-custom {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        /* Animación del logo */
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(2deg); }
        }
        
        /* Action cards mejoradas */
        .action-card {
            animation: fadeInUp 0.6s ease-out backwards;
        }
        .action-card:nth-child(1) { animation-delay: 0.5s; }
        .action-card:nth-child(2) { animation-delay: 0.6s; }
        .action-card:nth-child(3) { animation-delay: 0.7s; }
        .action-card:nth-child(4) { animation-delay: 0.8s; }
        
        .action-icon {
            animation: iconBounce 2s ease-in-out infinite;
        }
        
        @keyframes iconBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* Scrollbar personalizado */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #FF8C00, #FFA500);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #E67E00, #FF8C00);
        }
        
        /* Selección personalizada */
        ::selection {
            background: #FF8C00;
            color: white;
        }
        
        /* MEJORAS PREMIUM PARA ÚLTIMAS TRANSFERENCIAS */
        .recent-card {
            animation: fadeInUp 0.8s ease-out 1s backwards !important;
            position: relative;
            overflow: hidden;
        }
        
        .recent-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #FF8C00, transparent);
            animation: lineMove 3s ease-in-out infinite;
        }
        
        @keyframes lineMove {
            0%, 100% { left: -100%; }
            50% { left: 100%; }
        }
        
        .recent-card h5 {
            animation: slideInLeft 0.6s ease-out 1.2s backwards;
            position: relative;
        }
        
        .recent-card h5::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #FF8C00, transparent);
            animation: widthGrow 0.6s ease-out 1.4s backwards;
        }
        
        @keyframes widthGrow {
            from { width: 0; }
            to { width: 60px; }
        }
        
        .recent-item {
            animation: slideInItem 0.5s ease-out backwards;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 3px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .recent-item:nth-child(1) { animation-delay: 1.4s; }
        .recent-item:nth-child(2) { animation-delay: 1.5s; }
        .recent-item:nth-child(3) { animation-delay: 1.6s; }
        .recent-item:nth-child(4) { animation-delay: 1.7s; }
        .recent-item:nth-child(5) { animation-delay: 1.8s; }
        
        @keyframes slideInItem {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .recent-item:hover {
            background: linear-gradient(90deg, rgba(255,140,0,0.05), transparent) !important;
            border-left-color: #FF8C00;
            transform: translateX(5px);
            box-shadow: 0 3px 10px rgba(255,140,0,0.2);
        }
        
        .recent-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 3px;
            height: 0;
            background: linear-gradient(to bottom, #FF8C00, #FFA500);
            transition: height 0.3s ease;
        }
        
        .recent-item:hover::before {
            height: 100%;
        }
        
        .badge-custom {
            transition: all 0.3s ease;
            animation: badgeFloat 2s ease-in-out infinite;
        }
        
        @keyframes badgeFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }
        
        .badge-custom:hover {
            transform: scale(1.1) !important;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        }
        
        /* Botón Ver mejorado */
        .btn-outline-primary {
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
        }
        
        .btn-outline-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,140,0,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.3s ease, height 0.3s ease;
        }
        
        .btn-outline-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-outline-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
            border-color: #FF8C00;
            color: #FF8C00;
        }
        
        /* Botón Ver Todas mejorado */
        .text-center .btn-outline-primary {
            animation: btnPulse 2s ease-in-out infinite;
        }
        
        @keyframes btnPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(0,123,255,0.4); }
            50% { box-shadow: 0 0 0 10px rgba(0,123,255,0); }
        }
        
        /* BOTÓN GENERAR REPORTES - DESTACADO */
        .btn-generar-reportes {
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 5px 20px rgba(255, 140, 0, 0.4);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
            animation: btnBreath 3s ease-in-out infinite, slideInRight 0.6s ease-out 1.2s backwards;
        }
        
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes btnBreath {
            0%, 100% { box-shadow: 0 5px 20px rgba(255, 140, 0, 0.4); }
            50% { box-shadow: 0 8px 30px rgba(255, 140, 0, 0.6); }
        }
        
        .btn-generar-reportes::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.5s ease, height 0.5s ease;
        }
        
        .btn-generar-reportes:hover {
            background: linear-gradient(135deg, #E67E00 0%, #FF8C00 100%);
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 35px rgba(255, 140, 0, 0.6), 0 0 20px rgba(255, 140, 0, 0.4);
            color: white;
        }
        
        .btn-generar-reportes:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-generar-reportes:active {
            transform: translateY(-2px) scale(1.02);
        }
        
        .btn-generar-reportes i {
            animation: iconBounce2 2s ease-in-out infinite;
        }
        
        @keyframes iconBounce2 {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
        
        /* Estilos para gráficos Chart.js */
        #chartEstados,
        #chartActividad {
            max-width: 100% !important;
            max-height: 250px !important;
            width: 100% !important;
            height: 250px !important;
        }
        
        .stat-card canvas {
            display: block !important;
            box-sizing: border-box !important;
        }
    </style>
</head>
<body>
    <!-- Pantalla de Carga -->
    <div id="loading-screen">
        <div class="loading-logo">
            <i class="fas fa-box-open"></i>
        </div>
        <div class="loader"></div>
        <div class="loading-text">Cargando Panel de Control...</div>
        <div class="loading-progress">
            <div class="loading-progress-bar"></div>
        </div>
        <div style="margin-top: 15px; color: rgba(255,255,255,0.8); font-size: 0.9em;">
            <i class="fas fa-warehouse"></i> Almacén 2 - Área de Empaque
        </div>
        <div style="margin-top: 8px; color: rgba(255,255,255,0.6); font-size: 0.85em;">
            Sistema IMBOX v1.0
        </div>
    </div>

    <div class="dashboard-container">
        
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <img src="/2/img/logo.jpg" alt="Logo IMBOX" style="height:90px;margin-right:20px;filter:drop-shadow(0 5px 15px rgba(255,140,0,0.3));animation:logoFloat 3s ease-in-out infinite;" onerror="this.style.display='none'">
                        <div>
                            <h1 class="welcome-title">
                                <i class="fas fa-box-open me-3"></i>Área de Empaque
                            </h1>
                            <p class="welcome-subtitle mb-0">
                                ¿Qué deseas hacer hoy?
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-muted">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <?php echo date('d/m/Y'); ?>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-clock me-2"></i>
                        <span id="currentTime"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-label">Total Transferencias</div>
                            <div class="stat-number text-warning"><?php echo $stats['total_transferencias']; ?></div>
                        </div>
                        <div>
                            <i class="fas fa-boxes fa-3x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-label">Pendientes</div>
                            <div class="stat-number text-warning"><?php echo $stats['pendientes']; ?></div>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-3x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-label">Completadas</div>
                            <div class="stat-number text-success"><?php echo $stats['completadas']; ?></div>
                        </div>
                        <div>
                            <i class="fas fa-check-circle fa-3x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="stat-label">Trabajadores Activos</div>
                            <div class="stat-number text-info"><?php echo $stats['trabajadores_activos']; ?></div>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Principales -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <a href="transferencias_ui.php" class="action-card">
                    <div class="text-center">
                        <div class="action-icon orange mx-auto">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="action-title">Ver Transferencias</div>
                        <div class="action-desc">
                            Gestionar y procesar todas las transferencias recibidas desde Corte
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="control_entrada_almacen2.php" class="action-card">
                    <div class="text-center">
                        <div class="action-icon success mx-auto">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="action-title">Nueva Recepción</div>
                        <div class="action-desc">
                            Procesar la recepción de una nueva transferencia de prendas
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="trabajadores_ui.php" class="action-card">
                    <div class="text-center">
                        <div class="action-icon info mx-auto">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="action-title">Trabajadores</div>
                        <div class="action-desc">
                            Administrar trabajadores, costureros y su asignación
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="modelos_ui.php" class="action-card">
                    <div class="text-center">
                        <div class="action-icon warning mx-auto">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="action-title">Modelos</div>
                        <div class="action-desc">
                            Ver y gestionar catálogo de modelos y especificaciones
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Acciones Secundarias -->
        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <div class="stat-card" style="background: linear-gradient(135deg, #28A745 0%, #20C997 100%); border: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="text-white mb-1">
                                <i class="fas fa-chart-bar me-2"></i>Reportes y Análisis
                            </h5>
                            <p class="text-white mb-0" style="opacity: 0.9;">
                                <i class="fas fa-download me-1"></i>Exportar datos a Excel o PDF
                            </p>
                        </div>
                        <div>
                            <a href="reportes.php" class="btn btn-light btn-lg shadow-sm">
                                <i class="fas fa-file-export me-2"></i>Generar Reportes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos de Estadísticas -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="stat-card h-100">
                    <h6 class="mb-3">
                        <i class="fas fa-chart-pie text-warning me-2"></i>
                        Distribución por Estado
                    </h6>
                    <div style="position: relative; height: 250px; max-height: 250px;">
                        <canvas id="chartEstados" width="400" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card h-100">
                    <h6 class="mb-3">
                        <i class="fas fa-chart-line text-success me-2"></i>
                        Actividad de Transferencias
                    </h6>
                    <div style="position: relative; height: 250px; max-height: 250px;">
                        <canvas id="chartActividad" width="400" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="row">
            <div class="col-lg-12">
                <div class="recent-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            <i class="fas fa-history text-imbox-orange me-2"></i>
                            Últimas Transferencias
                        </h5>
                        <a href="reportes.php" class="btn btn-generar-reportes">
                            <i class="fas fa-file-export me-2"></i>Generar Reportes
                        </a>
                    </div>
                    
                    <?php if (empty($ultimas_transferencias)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No hay transferencias registradas aún</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ultimas_transferencias as $tf): ?>
                        <div class="recent-item">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <strong class="text-imbox-orange">#<?php echo $tf['id']; ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($tf['referencia']); ?></small>
                                </div>
                                <div class="col-md-2">
                                    <?php if (!empty($tf['tipo_prenda'])): ?>
                                        <span class="badge bg-secondary badge-custom">
                                            <i class="fas fa-tshirt me-1"></i><?php echo htmlspecialchars($tf['tipo_prenda']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="badge bg-primary badge-custom">
                                        <?php echo $tf['total_items']; ?> items
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <?php if (!empty($tf['trabajador_nombre'])): ?>
                                        <i class="fas fa-user text-success me-1"></i>
                                        <small><?php echo htmlspecialchars($tf['trabajador_nombre']); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Sin asignar</small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-2">
                                    <?php
                                    $estado = strtolower($tf['estado'] ?? 'pendiente');
                                    $badge_class = [
                                        'pendiente' => 'warning',
                                        'enviado' => 'warning',
                                        'recibido' => 'info',
                                        'completado' => 'success'
                                    ];
                                    $class = $badge_class[$estado] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $class; ?> badge-custom">
                                        <?php echo ucfirst($estado); ?>
                                    </span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($tf['fecha_creacion'])); ?>
                                    </small>
                                    <br>
                                    <a href="transferencias_ui.php" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-eye me-1"></i>Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="transferencias_ui.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>Ver Todas las Transferencias
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4">
            <small class="text-white">
                <i class="fas fa-box-open me-2"></i>
                Sistema de Gestión de Empaque | <?php echo date('Y'); ?>
            </small>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ==========================================
        // EFECTOS INTERACTIVOS AVANZADOS - DASHBOARD
        // ==========================================
        
        // Reloj en tiempo real con animación
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = `${hours}:${minutes}:${seconds}`;
                timeElement.style.animation = 'pulse 1s ease';
                setTimeout(() => timeElement.style.animation = '', 1000);
            }
        }
        
        updateTime();
        setInterval(updateTime, 1000);

        // Sistema de partículas al mover el mouse
        let particleCount = 0;
        const maxParticles = 15;
        
        document.addEventListener('mousemove', function(e) {
            if (particleCount >= maxParticles) return;
            if (Math.random() > 0.03) return; // 3% probabilidad
            
            particleCount++;
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: fixed;
                width: 4px;
                height: 4px;
                background: radial-gradient(circle, #FF8C00, transparent);
                border-radius: 50%;
                pointer-events: none;
                z-index: 9999;
                left: ${e.clientX}px;
                top: ${e.clientY}px;
                opacity: 0.6;
                animation: particleFade 1s ease-out forwards;
            `;
            document.body.appendChild(particle);
            
            setTimeout(() => {
                particle.remove();
                particleCount--;
            }, 1000);
        });
        
        // Agregar estilos de partículas
        const particleStyle = document.createElement('style');
        particleStyle.textContent = `
            @keyframes particleFade {
                0% { transform: translateY(0) scale(1); opacity: 0.6; }
                100% { transform: translateY(-40px) scale(0); opacity: 0; }
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
        `;
        document.head.appendChild(particleStyle);

        // Efectos en iconos de action cards
        document.addEventListener('DOMContentLoaded', function() {
            const actionIcons = document.querySelectorAll('.action-icon');
            actionIcons.forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.2) rotate(10deg)';
                    this.style.transition = 'all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                });
                
                icon.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) rotate(0deg)';
                });
            });
            
            // Efecto hover en stat numbers
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(num => {
                num.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.15)';
                    this.style.color = '#FF8C00';
                    this.style.transition = 'all 0.3s ease';
                });
                
                num.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.color = '';
                });
            });
            
            // Animación de contador para números
            const animateValue = (element, start, end, duration) => {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    element.textContent = value;
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            };
            
            // Animar números al cargar
            statNumbers.forEach(num => {
                const finalValue = parseInt(num.textContent);
                if (!isNaN(finalValue)) {
                    num.textContent = '0';
                    setTimeout(() => {
                        animateValue(num, 0, finalValue, 1500);
                    }, 500);
                }
            });
            
            // Efecto ripple en action cards
            const actionCards = document.querySelectorAll('.action-card');
            actionCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    const ripple = document.createElement('div');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 140, 0, 0.4);
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        animation: rippleEffect 0.6s ease-out;
                        pointer-events: none;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 600);
                });
            });
            
            // Efectos adicionales
            const additionalStyles = document.createElement('style');
            additionalStyles.textContent = `
                @keyframes rippleEffect {
                    0% { transform: scale(0); opacity: 1; }
                    100% { transform: scale(2); opacity: 0; }
                }
                
                .stat-card:hover .stat-number {
                    text-shadow: 0 0 20px rgba(255, 140, 0, 0.5);
                }
                
                .action-card:active {
                    transform: translateY(-3px) scale(0.98);
                }
                
                /* Efecto de brillo en logo */
                img[src*='logo']:hover {
                    filter: drop-shadow(0 5px 20px rgba(255, 140, 0, 0.6)) !important;
                    transform: scale(1.05) rotate(-2deg);
                }
                
                /* Badges animados */
                .badge-custom {
                    transition: all 0.3s ease;
                }
                
                .badge-custom:hover {
                    transform: scale(1.1);
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                }
            `;
            document.head.appendChild(additionalStyles);
        });
        
        // Ocultar pantalla de carga
        window.addEventListener('load', function() {
            setTimeout(function() {
                const loadingScreen = document.getElementById('loading-screen');
                if (loadingScreen) {
                    loadingScreen.classList.add('hidden');
                    setTimeout(function() {
                        loadingScreen.remove();
                    }, 500);
                }
            }, 300);
        });
        
        setTimeout(function() {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                loadingScreen.classList.add('hidden');
                setTimeout(function() {
                    loadingScreen.remove();
                }, 500);
            }
        }, 5000);
        
        // ==========================================
    // GRÁFICOS CON CHART.JS
    // ==========================================
    
    // Variables globales para los gráficos
    let chartEstadosInstance = null;
    let chartActividadInstance = null;
    let chartsInitialized = false;
    
    // Función para inicializar gráficos
    function initCharts() {
        // Evitar inicialización múltiple
        if (chartsInitialized) {
            console.log('Gráficos ya inicializados');
            return;
        }
        
        // Gráfico de Distribución por Estado
        const ctxEstados = document.getElementById('chartEstados');
        if (ctxEstados && !chartEstadosInstance) {
            chartEstadosInstance = new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'Recibidas', 'Completadas'],
                datasets: [{
                    data: [<?php echo $stats['pendientes']; ?>, <?php echo $stats['recibidas']; ?>, <?php echo $stats['completadas']; ?>],
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.8)',   // Warning - Pendientes
                        'rgba(23, 162, 184, 0.8)',  // Info - Recibidas
                        'rgba(40, 167, 69, 0.8)'    // Success - Completadas
                    ],
                    borderColor: [
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(40, 167, 69, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false, // Deshabilitar animación completamente
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#2C2C2C',
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        borderColor: '#FF8C00',
                        borderWidth: 2
                    }
                }
            }
        });
    }
    
    // Gráfico de Actividad de Transferencias
    const ctxActividad = document.getElementById('chartActividad');
    if (ctxActividad && !chartActividadInstance) {
        chartActividadInstance = new Chart(ctxActividad, {
            type: 'bar',
            data: {
                labels: ['Total', 'Pendientes', 'Recibidas', 'Completadas', 'Trabajadores'],
                datasets: [{
                    label: 'Cantidad',
                    data: [
                        <?php echo $stats['total_transferencias']; ?>,
                        <?php echo $stats['pendientes']; ?>,
                        <?php echo $stats['recibidas']; ?>,
                        <?php echo $stats['completadas']; ?>,
                        <?php echo $stats['trabajadores_activos']; ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 140, 0, 0.8)',   // Naranja - Total
                        'rgba(255, 193, 7, 0.8)',   // Warning - Pendientes
                        'rgba(23, 162, 184, 0.8)',  // Info - Recibidas
                        'rgba(40, 167, 69, 0.8)',   // Success - Completadas
                        'rgba(108, 117, 125, 0.8)'  // Gray - Trabajadores
                    ],
                    borderColor: [
                        'rgba(255, 140, 0, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false, // Deshabilitar animación completamente
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#2C2C2C',
                            font: {
                                size: 11,
                                weight: '600'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#2C2C2C',
                            font: {
                                size: 11,
                                weight: '600'
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        borderColor: '#FF8C00',
                        borderWidth: 2
                    }
                }
            }
        });
    }
    
        // Marcar como inicializados
        chartsInitialized = true;
        console.log('✅ Gráficos inicializados correctamente');
    }
    
    // Llamar a la función de inicialización solo una vez cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        // DOM ya está listo
        initCharts();
    }
    
    // Console message personalizado
    console.log('%c🎨 Dashboard Premium IMBOX - Área de Empaque', 
        'font-size: 18px; font-weight: bold; background: linear-gradient(90deg, #FF8C00, #FFA500); ' +
        'color: white; padding: 10px 20px; border-radius: 10px;');
    console.log('%c✨ Diseño con Animaciones Avanzadas', 
        'font-size: 14px; color: #FF8C00; font-weight: bold;');
    console.log('%c📊 Gráficos Chart.js Implementados', 
        'font-size: 14px; color: #28A745; font-weight: bold;');
    </script>
</body>
</html>
