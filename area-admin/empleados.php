<?php
require_once 'config/auth.php';
require_once 'config/database.php';
requireAuth();

$current_page = 'empleados';
$page_title = 'Gestión de Empleados';

$db = Database::getInstance()->getConnection();

// Obtener empleados
$stmt = $db->query("SELECT * FROM empleados ORDER BY fecha_contratacion DESC");
$empleados = $stmt->fetchAll();

// Estadísticas
$stmt = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
    AVG(salario) as salario_promedio
FROM empleados");
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
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Total Empleados</div>
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
                    <div class="stat-label">Salario Promedio</div>
                    <div class="stat-value">$<?php echo number_format($stats['salario_promedio'], 0); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-label">Total Registrados</div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                </div>
            </div>
        </div>

        <!-- Tabla de Empleados -->
        <div class="card fade-in" style="animation-delay: 0.1s;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-user-tie me-2"></i>
                    Lista de Empleados
                </h2>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Nuevo Empleado
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NOMBRE</th>
                                <th>EMAIL</th>
                                <th>PUESTO</th>
                                <th>DEPARTAMENTO</th>
                                <th>SALARIO</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empleados as $emp): ?>
                            <tr>
                                <td><strong>#<?php echo $emp['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($emp['nombre'] . ' ' . $emp['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($emp['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($emp['puesto'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($emp['departamento'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($emp['salario'], 2); ?></td>
                                <td>
                                    <?php if ($emp['activo']): ?>
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
