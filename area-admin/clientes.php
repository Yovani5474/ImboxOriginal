<?php
require_once 'config/auth.php';
require_once 'config/database.php';
requireAuth();

$current_page = 'clientes';
$page_title = 'Gestión de Clientes';

$db = Database::getInstance()->getConnection();

// Obtener clientes
$stmt = $db->query("SELECT * FROM clientes ORDER BY fecha_registro DESC");
$clientes = $stmt->fetchAll();

// Estadísticas
$stmt = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
    SUM(limite_credito) as total_credito
FROM clientes");
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
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Total Clientes</div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Activos</div>
                    <div class="stat-value"><?php echo $stats['activos']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Crédito Total</div>
                    <div class="stat-value">$<?php echo number_format($stats['total_credito'], 0); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Con Empresa</div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                </div>
            </div>
        </div>

        <!-- Tabla de Clientes -->
        <div class="card fade-in" style="animation-delay: 0.1s;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-users me-2"></i>
                    Lista de Clientes
                </h2>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Nuevo Cliente
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NOMBRE</th>
                                <th>EMPRESA</th>
                                <th>EMAIL</th>
                                <th>CRÉDITO</th>
                                <th>SALDO</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><strong>#<?php echo $cliente['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['empresa'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['email'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($cliente['limite_credito'] ?? 0, 2); ?></td>
                                <td>$<?php echo number_format($cliente['saldo_actual'] ?? 0, 2); ?></td>
                                <td>
                                    <?php if ($cliente['activo']): ?>
                                    <span class="badge badge-success">ACTIVO</span>
                                    <?php else: ?>
                                    <span class="badge badge-secondary">INACTIVO</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-icon btn-sm btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-icon btn-sm btn-delete" title="Eliminar">
                                            <i class="fas fa-trash"></i>
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
