<?php
require_once 'config/auth.php';
require_once 'config/database.php';
requireAuth();

$db = Database::getInstance()->getConnection();

// Obtener estadísticas generales
$stats = [];

// Total de clientes
$stmt = $db->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
$stats['clientes_total'] = $stmt->fetch()['total'];

// Total de proveedores
$stmt = $db->query("SELECT COUNT(*) as total FROM proveedores WHERE activo = 1");
$stats['proveedores_total'] = $stmt->fetch()['total'];

// Total de empleados
$stmt = $db->query("SELECT COUNT(*) as total FROM empleados WHERE activo = 1");
$stats['empleados_total'] = $stmt->fetch()['total'];

// Estadísticas de deudas
$stmt = $db->query("SELECT 
    COUNT(*) as total_deudas,
    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado = 'pagada' THEN 1 ELSE 0 END) as pagadas,
    SUM(CASE WHEN estado = 'vencida' THEN 1 ELSE 0 END) as vencidas,
    SUM(monto_total) as monto_total,
    SUM(monto_pendiente) as monto_pendiente,
    SUM(monto_pagado) as monto_pagado
FROM deudas");
$deudas_stats = $stmt->fetch();
$stats = array_merge($stats, $deudas_stats);

// Deudas por cobrar (clientes)
$stmt = $db->query("SELECT 
    SUM(monto_pendiente) as total,
    COUNT(*) as cantidad
FROM deudas WHERE tipo = 'cliente' AND estado != 'pagada'");
$por_cobrar = $stmt->fetch();
$stats['por_cobrar_monto'] = $por_cobrar['total'] ?? 0;
$stats['por_cobrar_cantidad'] = $por_cobrar['cantidad'];

// Deudas por pagar (proveedores)
$stmt = $db->query("SELECT 
    SUM(monto_pendiente) as total,
    COUNT(*) as cantidad
FROM deudas WHERE tipo = 'proveedor' AND estado != 'pagada'");
$por_pagar = $stmt->fetch();
$stats['por_pagar_monto'] = $por_pagar['total'] ?? 0;
$stats['por_pagar_cantidad'] = $por_pagar['cantidad'];

// Deudas por mes (últimos 6 meses)
$stmt = $db->query("SELECT 
    DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
    COUNT(*) as cantidad,
    SUM(monto_total) as monto
FROM deudas 
WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
ORDER BY mes ASC");
$deudas_por_mes = $stmt->fetchAll();

// Top 5 clientes con más deuda
$stmt = $db->query("SELECT 
    referencia_nombre as nombre,
    SUM(monto_pendiente) as deuda_total
FROM deudas
WHERE tipo = 'cliente' AND estado != 'pagada'
GROUP BY referencia_nombre
ORDER BY deuda_total DESC
LIMIT 5");
$top_clientes_deuda = $stmt->fetchAll();

// Top 5 proveedores a pagar
$stmt = $db->query("SELECT 
    referencia_nombre as nombre,
    SUM(monto_pendiente) as deuda_total
FROM deudas
WHERE tipo = 'proveedor' AND estado != 'pagada'
GROUP BY referencia_nombre
ORDER BY deuda_total DESC
LIMIT 5");
$top_proveedores_deuda = $stmt->fetchAll();

// Calcular porcentajes
$total_deudas_count = max($stats['total_deudas'], 1); // Evitar división por cero
$porcentaje_pendientes = ($stats['pendientes'] / $total_deudas_count) * 100;
$porcentaje_pagadas = ($stats['pagadas'] / $total_deudas_count) * 100;
$porcentaje_vencidas = ($stats['vencidas'] / $total_deudas_count) * 100;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Estadísticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="estadisticas.php">Estadísticas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><?php echo getUserName(); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <div class="container">
            <!-- Header -->
            <div class="page-header fade-in">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1>📊 Estadísticas y Reportes</h1>
                        <p>Análisis detallado del negocio</p>
                    </div>
                    <a href="index.php" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 5px;">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Volver al Dashboard
                    </a>
                </div>
            </div>

            <!-- Stats Cards Resumen -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s; border-left-color: var(--primary-color);">
                        <div class="stat-label">Total Clientes</div>
                        <div class="stat-value" style="color: var(--primary-color);">
                            <?php echo $stats['clientes_total']; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card fade-in" style="animation-delay: 0.2s; border-left-color: var(--secondary-color);">
                        <div class="stat-label">Total Proveedores</div>
                        <div class="stat-value" style="color: var(--secondary-color);">
                            <?php echo $stats['proveedores_total']; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card fade-in" style="animation-delay: 0.3s; border-left-color: var(--info-color);">
                        <div class="stat-label">Total Empleados</div>
                        <div class="stat-value" style="color: var(--info-color);">
                            <?php echo $stats['empleados_total']; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card fade-in" style="animation-delay: 0.4s; border-left-color: var(--danger-color);">
                        <div class="stat-label">Total Deudas</div>
                        <div class="stat-value" style="color: var(--danger-color);">
                            <?php echo $stats['total_deudas']; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos Principales -->
            <div class="row g-4 mb-4">
                <!-- Gráfico de Pastel - Estado de Deudas -->
                <div class="col-md-6">
                    <div class="chart-container fade-in" style="animation-delay: 0.5s;">
                        <h5 class="mb-4">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                            </svg>
                            Estado de Deudas
                        </h5>
                        <canvas id="pieChart" style="max-height: 300px;"></canvas>
                        <div class="mt-3 text-center">
                            <span class="badge badge-warning me-2">Pendientes: <?php echo $stats['pendientes']; ?></span>
                            <span class="badge badge-success me-2">Pagadas: <?php echo $stats['pagadas']; ?></span>
                            <span class="badge badge-danger">Vencidas: <?php echo $stats['vencidas']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Barras - Deudas por Mes -->
                <div class="col-md-6">
                    <div class="chart-container fade-in" style="animation-delay: 0.6s;">
                        <h5 class="mb-4">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                            Deudas por Mes (Últimos 6 Meses)
                        </h5>
                        <canvas id="barChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Deudas por Cobrar y Pagar -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="chart-container fade-in" style="animation-delay: 0.7s;">
                        <h5 class="mb-3 text-success">
                            💰 Por Cobrar (Clientes)
                        </h5>
                        <div class="alert alert-success mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">$<?php echo number_format($stats['por_cobrar_monto'], 2); ?></h3>
                                    <small><?php echo $stats['por_cobrar_cantidad']; ?> deudas pendientes</small>
                                </div>
                                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                        </div>
                        <h6>Top 5 Clientes con Mayor Deuda</h6>
                        <div class="list-group">
                            <?php foreach ($top_clientes_deuda as $index => $cliente): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?php echo ($index + 1) . '. ' . htmlspecialchars($cliente['nombre']); ?></span>
                                <strong class="text-danger">$<?php echo number_format($cliente['deuda_total'], 2); ?></strong>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($top_clientes_deuda)): ?>
                            <div class="list-group-item text-center text-muted">
                                No hay deudas pendientes de clientes
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="chart-container fade-in" style="animation-delay: 0.8s;">
                        <h5 class="mb-3 text-danger">
                            💸 Por Pagar (Proveedores)
                        </h5>
                        <div class="alert alert-danger mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">$<?php echo number_format($stats['por_pagar_monto'], 2); ?></h3>
                                    <small><?php echo $stats['por_pagar_cantidad']; ?> deudas pendientes</small>
                                </div>
                                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                        </div>
                        <h6>Top 5 Proveedores a Pagar</h6>
                        <div class="list-group">
                            <?php foreach ($top_proveedores_deuda as $index => $proveedor): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?php echo ($index + 1) . '. ' . htmlspecialchars($proveedor['nombre']); ?></span>
                                <strong class="text-danger">$<?php echo number_format($proveedor['deuda_total'], 2); ?></strong>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($top_proveedores_deuda)): ?>
                            <div class="list-group-item text-center text-muted">
                                No hay deudas pendientes con proveedores
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen Financiero -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="chart-container fade-in" style="animation-delay: 0.9s;">
                        <h5 class="mb-4">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="3" y1="9" x2="21" y2="9"></line>
                                <line x1="9" y1="21" x2="9" y2="9"></line>
                            </svg>
                            Resumen Financiero
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="alert alert-primary mb-0">
                                    <h6>Monto Total en Deudas</h6>
                                    <h4 class="mb-0">$<?php echo number_format($stats['monto_total'], 2); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-warning mb-0">
                                    <h6>Monto Pendiente</h6>
                                    <h4 class="mb-0">$<?php echo number_format($stats['monto_pendiente'], 2); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-success mb-0">
                                    <h6>Monto Pagado</h6>
                                    <h4 class="mb-0">$<?php echo number_format($stats['monto_pagado'], 2); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfico de Pastel - Estado de Deudas
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'Pagadas', 'Vencidas'],
                datasets: [{
                    data: [
                        <?php echo $stats['pendientes']; ?>,
                        <?php echo $stats['pagadas']; ?>,
                        <?php echo $stats['vencidas']; ?>
                    ],
                    backgroundColor: [
                        '#FFC107',
                        '#28A745',
                        '#ef4444'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de Barras - Deudas por Mes
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($deudas_por_mes, 'mes')); ?>,
                datasets: [{
                    label: 'Cantidad de Deudas',
                    data: <?php echo json_encode(array_column($deudas_por_mes, 'cantidad')); ?>,
                    backgroundColor: 'rgba(255, 140, 0, 0.8)',
                    borderColor: '#FF8C00',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
