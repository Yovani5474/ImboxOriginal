<?php
require_once 'config/auth.php';
require_once 'config/database.php';
requireAuth();

$current_page = 'deudas';
$page_title = 'Gestión de Deudas';

$db = Database::getInstance()->getConnection();

// Obtener deudas
$stmt = $db->query("SELECT * FROM deudas ORDER BY fecha_vencimiento ASC");
$deudas = $stmt->fetchAll();

// Estadísticas
$stmt = $db->query("SELECT 
    SUM(CASE WHEN tipo='cliente' AND estado!='pagada' THEN monto_pendiente ELSE 0 END) as por_cobrar,
    SUM(CASE WHEN tipo='proveedor' AND estado!='pagada' THEN monto_pendiente ELSE 0 END) as por_pagar,
    COUNT(CASE WHEN estado='vencida' THEN 1 END) as vencidas,
    COUNT(*) as total
FROM deudas");
$stats = $stmt->fetch();

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <?php include 'includes/topbar.php'; ?>
    
    <!-- Content Area -->
    <div class="content-area">
        <!-- Stats Cards -->
        <div class="stats-grid fade-in">
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Por Cobrar</div>
                    <div class="stat-value">$<?php echo number_format($stats['por_cobrar'], 0); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Por Pagar</div>
                    <div class="stat-value">$<?php echo number_format($stats['por_pagar'], 0); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Total Deudas</div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Vencidas</div>
                    <div class="stat-value"><?php echo $stats['vencidas']; ?></div>
                </div>
            </div>
        </div>

        <!-- Tabla de Deudas -->
        <div class="card fade-in" style="animation-delay: 0.1s;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Registro de Deudas
                </h2>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Nueva Deuda
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>TIPO</th>
                                <th>REFERENCIA</th>
                                <th>MONTO TOTAL</th>
                                <th>PAGADO</th>
                                <th>PENDIENTE</th>
                                <th>VENCIMIENTO</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deudas as $deuda): ?>
                            <tr>
                                <td><strong>#<?php echo $deuda['id']; ?></strong></td>
                                <td>
                                    <?php if ($deuda['tipo'] === 'cliente'): ?>
                                    <span class="badge badge-info">CLIENTE</span>
                                    <?php else: ?>
                                    <span class="badge badge-warning">PROVEEDOR</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($deuda['referencia_nombre']); ?></td>
                                <td>$<?php echo number_format($deuda['monto_total'], 2); ?></td>
                                <td>$<?php echo number_format($deuda['monto_pagado'], 2); ?></td>
                                <td><strong>$<?php echo number_format($deuda['monto_pendiente'], 2); ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($deuda['fecha_vencimiento'])); ?></td>
                                <td>
                                    <?php
                                    $badge_class = 'secondary';
                                    if ($deuda['estado'] === 'pagada') $badge_class = 'success';
                                    elseif ($deuda['estado'] === 'vencida') $badge_class = 'danger';
                                    elseif ($deuda['estado'] === 'parcial') $badge_class = 'warning';
                                    ?>
                                    <span class="badge badge-<?php echo $badge_class; ?>">
                                        <?php echo strtoupper($deuda['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-icon btn-sm btn-view" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-icon btn-sm btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
