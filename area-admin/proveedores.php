<?php
require_once 'config/auth.php';
require_once 'config/database.php';
requireAuth();

$current_page = 'proveedores';
$page_title = 'Gestión de Proveedores';

$db = Database::getInstance()->getConnection();

// Obtener proveedores
$stmt = $db->query("SELECT * FROM proveedores ORDER BY fecha_creacion DESC");
$proveedores = $stmt->fetchAll();

// Estadísticas
$stmt = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
    AVG(dias_credito) as promedio_credito
FROM proveedores");
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
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Total Proveedores</div>
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
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Crédito Promedio</div>
                    <div class="stat-value"><?php echo round($stats['promedio_credito']); ?> días</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Total Registrados</div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                </div>
            </div>
        </div>

        <!-- Tabla de Proveedores -->
        <div class="card fade-in" style="animation-delay: 0.1s;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-truck me-2"></i>
                    Lista de Proveedores
                </h2>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Nuevo Proveedor
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
                                <th>TIPO</th>
                                <th>CRÉDITO (DÍAS)</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedores as $proveedor): ?>
                            <tr>
                                <td><strong>#<?php echo $proveedor['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($proveedor['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['empresa'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['tipo'] ?? 'N/A'); ?></td>
                                <td><?php echo $proveedor['dias_credito'] ?? 0; ?> días</td>
                                <td>
                                    <?php if ($proveedor['activo']): ?>
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
