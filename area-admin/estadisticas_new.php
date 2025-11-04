<?php
require_once 'config/auth.php';
require_once 'config/database.php';
requireAuth();

$current_page = 'estadisticas';
$page_title = 'Estadísticas del Sistema';

$db = Database::getInstance()->getConnection();

// Estadísticas generales
$stmt = $db->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
$clientes_total = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM proveedores WHERE activo = 1");
$proveedores_total = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM empleados WHERE activo = 1");
$empleados_total = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM deudas");
$deudas_total = $stmt->fetch()['total'];

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
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Clientes Activos</div>
                    <div class="stat-value"><?php echo $clientes_total; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Proveedores Activos</div>
                    <div class="stat-value"><?php echo $proveedores_total; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Empleados Activos</div>
                    <div class="stat-value"><?php echo $empleados_total; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Total Deudas</div>
                    <div class="stat-value"><?php echo $deudas_total; ?></div>
                </div>
            </div>
        </div>

        <!-- Resumen -->
        <div class="card fade-in" style="animation-delay: 0.1s;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-chart-bar me-2"></i>
                    Resumen General del Sistema
                </h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">Gestión de Personas</h5>
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>CATEGORÍA</th>
                                    <th>TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-users text-primary me-2"></i>Clientes</td>
                                    <td><strong><?php echo $clientes_total; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-truck text-primary me-2"></i>Proveedores</td>
                                    <td><strong><?php echo $proveedores_total; ?></strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-user-tie text-primary me-2"></i>Empleados</td>
                                    <td><strong><?php echo $empleados_total; ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="mb-3">Finanzas</h5>
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>CONCEPTO</th>
                                    <th>CANTIDAD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-file-invoice-dollar text-warning me-2"></i>Total Deudas</td>
                                    <td><strong><?php echo $deudas_total; ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        <small>Ver más detalles en módulo de Deudas</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
