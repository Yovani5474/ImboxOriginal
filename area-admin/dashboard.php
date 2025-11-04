<?php
require_once 'config/auth.php';
require_once 'config/database.php';
requireAuth();

$db = Database::getInstance()->getConnection();

// Obtener estadísticas
$stats = [];

// Total de clientes
$stmt = $db->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
$stats['clientes'] = $stmt->fetch()['total'] ?? 0;

// Total de proveedores
$stmt = $db->query("SELECT COUNT(*) as total FROM proveedores WHERE activo = 1");
$stats['proveedores'] = $stmt->fetch()['total'] ?? 0;

// Total de empleados
$stmt = $db->query("SELECT COUNT(*) as total FROM empleados WHERE activo = 1");
$stats['empleados'] = $stmt->fetch()['total'] ?? 0;

// Total de deudas pendientes
$stmt = $db->query("SELECT SUM(monto_pendiente) as total FROM deudas WHERE estado != 'pagada'");
$stats['deudas_pendientes'] = $stmt->fetch()['total'] ?? 0;

// Últimos clientes
$stmt = $db->query("SELECT * FROM clientes ORDER BY fecha_registro DESC LIMIT 10");
$ultimos_clientes = $stmt->fetchAll();

// Deudas recientes
$stmt = $db->query("SELECT * FROM deudas ORDER BY fecha_creacion DESC LIMIT 10");
$ultimas_deudas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IMBOX Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>
                    <i class="fas fa-box-open"></i>
                    IMBOX Admin
                </h2>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                
                <div class="menu-section">Gestión</div>
                
                <a href="clientes.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
                
                <a href="proveedores.php" class="menu-item">
                    <i class="fas fa-truck"></i>
                    <span>Proveedores</span>
                </a>
                
                <a href="empleados.php" class="menu-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Empleados</span>
                </a>
                
                <a href="deudas.php" class="menu-item">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Deudas</span>
                </a>
                
                <div class="menu-section">Reportes</div>
                
                <a href="estadisticas.php" class="menu-item">
                    <i class="fas fa-chart-pie"></i>
                    <span>Estadísticas</span>
                </a>
                
                <a href="reportes.php" class="menu-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Reportes</span>
                </a>
                
                <div class="menu-section">Administración</div>
                
                <a href="usuarios.php" class="menu-item">
                    <i class="fas fa-users-cog"></i>
                    <span>Usuarios</span>
                </a>
                
                <a href="configuracion.php" class="menu-item">
                    <i class="fas fa-cogs"></i>
                    <span>Configuración</span>
                </a>
                
                <a href="logs.php" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Logs</span>
                </a>
                
                <a href="backup.php" class="menu-item">
                    <i class="fas fa-database"></i>
                    <span>Backups</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1 class="page-title">Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr(getUserName(), 0, 1)); ?>
                    </div>
                    <span class="user-name"><?php echo getUserName(); ?></span>
                </div>
            </div>

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
                            <div class="stat-value"><?php echo $stats['clientes']; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-label">Proveedores</div>
                            <div class="stat-value"><?php echo $stats['proveedores']; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-label">Empleados</div>
                            <div class="stat-value"><?php echo $stats['empleados']; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon red">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-label">Deudas Pendientes</div>
                            <div class="stat-value">$<?php echo number_format($stats['deudas_pendientes'], 2); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Últimos Clientes -->
                <div class="card fade-in" style="animation-delay: 0.1s;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-users me-2"></i>
                            Últimos Clientes Registrados
                        </h2>
                        <a href="clientes.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            Nuevo Cliente
                        </a>
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
                                        <th>ESTADO</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimos_clientes as $cliente): ?>
                                    <tr>
                                        <td><strong>#<?php echo $cliente['id']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['empresa'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['email'] ?? 'N/A'); ?></td>
                                        <td>$<?php echo number_format($cliente['limite_credito'] ?? 0, 2); ?></td>
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

                <!-- Últimas Deudas -->
                <div class="card fade-in" style="animation-delay: 0.2s;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            Deudas Recientes
                        </h2>
                        <a href="deudas.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            Nueva Deuda
                        </a>
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
                                        <th>MONTO PENDIENTE</th>
                                        <th>VENCIMIENTO</th>
                                        <th>ESTADO</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimas_deudas as $deuda): ?>
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
        </main>
    </div>
</body>
</html>
