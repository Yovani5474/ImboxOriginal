<?php
require_once 'config/auth.php';
require_once 'config/database.php';
requireAuth();

$db = Database::getInstance()->getConnection();

// Obtener estadísticas
$stats = [];

// Contar clientes
$stmt = $db->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
$stats['clientes'] = $stmt->fetch()['total'];

// Contar proveedores
$stmt = $db->query("SELECT COUNT(*) as total FROM proveedores WHERE activo = 1");
$stats['proveedores'] = $stmt->fetch()['total'];

// Contar empleados
$stmt = $db->query("SELECT COUNT(*) as total FROM empleados WHERE activo = 1");
$stats['empleados'] = $stmt->fetch()['total'];

// Total deudas pendientes
$stmt = $db->query("SELECT SUM(monto_pendiente) as total FROM deudas WHERE estado != 'pagada'");
$stats['deudas_pendientes'] = $stmt->fetch()['total'] ?? 0;

// Deudas por cobrar (clientes)
$stmt = $db->query("SELECT SUM(monto_pendiente) as total FROM deudas WHERE tipo = 'cliente' AND estado != 'pagada'");
$stats['por_cobrar'] = $stmt->fetch()['total'] ?? 0;

// Deudas por pagar (proveedores)
$stmt = $db->query("SELECT SUM(monto_pendiente) as total FROM deudas WHERE tipo = 'proveedor' AND estado != 'pagada'");
$stats['por_pagar'] = $stmt->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
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
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <?php echo getUserName(); ?>
                        </a>
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
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="text-white fw-bold">Bienvenido, <?php echo getUserName(); ?></h1>
                    <p class="text-white-50">Panel de administración y control</p>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="row g-4">
                <!-- Reloj -->
                <div class="col-md-6 col-lg-4">
                    <div class="clock-container fade-in">
                        <div class="analog-clock">
                            <div class="clock-numbers">
                                <span class="clock-number" style="top: 5%; left: 50%; transform: translateX(-50%);">12</span>
                                <span class="clock-number" style="top: 50%; right: 5%; transform: translateY(-50%);">3</span>
                                <span class="clock-number" style="bottom: 5%; left: 50%; transform: translateX(-50%);">6</span>
                                <span class="clock-number" style="top: 50%; left: 5%; transform: translateY(-50%);">9</span>
                            </div>
                            <div class="clock-hand hour-hand" id="hourHand"></div>
                            <div class="clock-hand minute-hand" id="minuteHand"></div>
                            <div class="clock-hand second-hand" id="secondHand"></div>
                            <div class="clock-center"></div>
                        </div>
                        <div class="digital-time" id="digitalTime"></div>
                        <div class="current-date" id="currentDate"></div>
                    </div>
                </div>

                <!-- Deudas -->
                <div class="col-md-6 col-lg-4">
                    <a href="deudas.php" class="dashboard-card card-deudas fade-in" style="animation-delay: 0.1s;">
                        <div class="card-icon">
                            <svg viewBox="0 0 100 100" fill="none">
                                <rect x="20" y="30" width="60" height="40" rx="5" fill="#34d399"/>
                                <circle cx="50" cy="50" r="12" fill="#fbbf24"/>
                                <path d="M50 30 L50 20" stroke="#06b6d4" stroke-width="4" stroke-linecap="round"/>
                                <path d="M50 70 L50 80" stroke="#f97316" stroke-width="4" stroke-linecap="round"/>
                                <polygon points="50,15 45,23 55,23" fill="#06b6d4"/>
                                <polygon points="50,85 45,77 55,77" fill="#f97316"/>
                            </svg>
                            <div class="icon-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="card-title">Deudas</h3>
                        <div class="card-value">$<?php echo number_format($stats['deudas_pendientes'], 2); ?></div>
                        <p class="card-subtitle">Por Cobrar: $<?php echo number_format($stats['por_cobrar'], 2); ?></p>
                        <p class="card-subtitle">Por Pagar: $<?php echo number_format($stats['por_pagar'], 2); ?></p>
                    </a>
                </div>

                <!-- Estadísticas -->
                <div class="col-md-6 col-lg-4">
                    <a href="estadisticas.php" class="dashboard-card card-estadisticas fade-in" style="animation-delay: 0.2s;">
                        <div class="card-icon">
                            <svg viewBox="0 0 100 100" fill="none">
                                <circle cx="50" cy="50" r="35" fill="#fbbf24"/>
                                <path d="M50 50 L50 15 A35 35 0 0 1 77 35 Z" fill="#ef4444"/>
                                <path d="M50 50 L77 35 A35 35 0 0 1 73 70 Z" fill="#10b981"/>
                                <circle cx="68" cy="25" r="8" fill="#2563eb"/>
                            </svg>
                            <div class="icon-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="card-title">Estadísticas</h3>
                        <div class="card-value">Ver Reportes</div>
                        <p class="card-subtitle">Análisis y métricas del negocio</p>
                    </a>
                </div>

                <!-- Clientes -->
                <div class="col-md-6 col-lg-4">
                    <a href="clientes.php" class="dashboard-card card-clientes fade-in" style="animation-delay: 0.3s;">
                        <div class="card-icon">
                            <svg viewBox="0 0 100 100" fill="none">
                                <rect x="25" y="20" width="50" height="65" rx="5" fill="#fbbf24"/>
                                <circle cx="50" cy="45" r="12" fill="white"/>
                                <path d="M38 60 Q50 55 62 60 L62 75 L38 75 Z" fill="white"/>
                                <rect x="30" y="25" width="40" height="3" fill="#f59e0b"/>
                            </svg>
                            <div class="icon-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="card-title">Clientes</h3>
                        <div class="card-value"><?php echo $stats['clientes']; ?></div>
                        <p class="card-subtitle">Gestión de clientes</p>
                    </a>
                </div>

                <!-- Proveedores -->
                <div class="col-md-6 col-lg-4">
                    <a href="proveedores.php" class="dashboard-card card-proveedores fade-in" style="animation-delay: 0.4s;">
                        <div class="card-icon">
                            <svg viewBox="0 0 100 100" fill="none">
                                <rect x="30" y="45" width="25" height="25" rx="2" fill="#fb923c" stroke="#ea580c" stroke-width="2"/>
                                <rect x="30" y="20" width="25" height="25" rx="2" fill="#fdba74" stroke="#f97316" stroke-width="2"/>
                                <rect x="45" y="45" width="25" height="25" rx="2" fill="#fed7aa" stroke="#fb923c" stroke-width="2"/>
                                <path d="M42 45 L42 20 M55 45 L55 20" stroke="#ea580c" stroke-width="2"/>
                            </svg>
                            <div class="icon-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="card-title">Proveedores</h3>
                        <div class="card-value"><?php echo $stats['proveedores']; ?></div>
                        <p class="card-subtitle">Gestión de proveedores</p>
                    </a>
                </div>

                <!-- Empleados -->
                <div class="col-md-6 col-lg-4">
                    <a href="empleados.php" class="dashboard-card card-empleados fade-in" style="animation-delay: 0.5s;">
                        <div class="card-icon">
                            <svg viewBox="0 0 100 100" fill="none">
                                <circle cx="50" cy="35" r="12" fill="#38bdf8"/>
                                <circle cx="30" cy="55" r="10" fill="#7dd3fc"/>
                                <circle cx="70" cy="55" r="10" fill="#7dd3fc"/>
                                <path d="M50 47 L30 65 M50 47 L70 65" stroke="#0ea5e9" stroke-width="3" stroke-linecap="round"/>
                                <circle cx="50" cy="47" r="3" fill="#0ea5e9"/>
                                <circle cx="30" cy="65" r="3" fill="#0ea5e9"/>
                                <circle cx="70" cy="65" r="3" fill="#0ea5e9"/>
                            </svg>
                            <div class="icon-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="card-title">Empleados</h3>
                        <div class="card-value"><?php echo $stats['empleados']; ?></div>
                        <p class="card-subtitle">Gestión de empleados</p>
                    </a>
                </div>

                <!-- Usuarios -->
                <div class="col-md-6 col-lg-4">
                    <a href="usuarios.php" class="dashboard-card fade-in" style="animation-delay: 0.6s; border-top: 4px solid #8B5CF6;">
                        <div class="card-icon" style="background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%);">
                            <svg viewBox="0 0 100 100" fill="none">
                                <circle cx="50" cy="30" r="15" fill="white" opacity="0.9"/>
                                <path d="M30 70 Q50 60 70 70 L70 80 L30 80 Z" fill="white" opacity="0.9"/>
                            </svg>
                            <div class="icon-badge">
                                <i class="fas fa-users-cog" style="font-size: 18px;"></i>
                            </div>
                        </div>
                        <h3 class="card-title">Usuarios</h3>
                        <div class="card-value">Gestionar</div>
                        <p class="card-subtitle">Control de acceso y roles</p>
                    </a>
                </div>

                <!-- Configuración -->
                <div class="col-md-6 col-lg-4">
                    <a href="configuracion.php" class="dashboard-card fade-in" style="animation-delay: 0.7s; border-top: 4px solid #6366F1;">
                        <div class="card-icon" style="background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);">
                            <svg viewBox="0 0 100 100" fill="none">
                                <circle cx="50" cy="50" r="25" fill="white" opacity="0.3"/>
                                <circle cx="50" cy="50" r="15" fill="white" opacity="0.9"/>
                                <rect x="48" y="20" width="4" height="10" fill="white" rx="2"/>
                                <rect x="48" y="70" width="4" height="10" fill="white" rx="2"/>
                                <rect x="20" y="48" width="10" height="4" fill="white" rx="2"/>
                                <rect x="70" y="48" width="10" height="4" fill="white" rx="2"/>
                            </svg>
                            <div class="icon-badge">
                                <i class="fas fa-cogs" style="font-size: 18px;"></i>
                            </div>
                        </div>
                        <h3 class="card-title">Configuración</h3>
                        <div class="card-value">Sistema</div>
                        <p class="card-subtitle">Ajustes y preferencias</p>
                    </a>
                </div>

                <!-- Logs -->
                <div class="col-md-6 col-lg-4">
                    <a href="logs.php" class="dashboard-card fade-in" style="animation-delay: 0.8s; border-top: 4px solid #10B981;">
                        <div class="card-icon" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                            <svg viewBox="0 0 100 100" fill="none">
                                <rect x="25" y="20" width="50" height="60" rx="5" fill="white" opacity="0.3"/>
                                <line x1="35" y1="35" x2="65" y2="35" stroke="white" stroke-width="3"/>
                                <line x1="35" y1="45" x2="65" y2="45" stroke="white" stroke-width="3"/>
                                <line x1="35" y1="55" x2="55" y2="55" stroke="white" stroke-width="3"/>
                            </svg>
                            <div class="icon-badge">
                                <i class="fas fa-file-alt" style="font-size: 18px;"></i>
                            </div>
                        </div>
                        <h3 class="card-title">Logs</h3>
                        <div class="card-value">Actividad</div>
                        <p class="card-subtitle">Registro de eventos</p>
                    </a>
                </div>

                <!-- Backups -->
                <div class="col-md-6 col-lg-4">
                    <a href="backup.php" class="dashboard-card fade-in" style="animation-delay: 0.9s; border-top: 4px solid #F59E0B;">
                        <div class="card-icon" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                            <svg viewBox="0 0 100 100" fill="none">
                                <rect x="20" y="30" width="60" height="50" rx="5" fill="white" opacity="0.3"/>
                                <path d="M50 40 L40 50 L45 50 L45 65 L55 65 L55 50 L60 50 Z" fill="white" opacity="0.9"/>
                            </svg>
                            <div class="icon-badge">
                                <i class="fas fa-download" style="font-size: 18px;"></i>
                            </div>
                        </div>
                        <h3 class="card-title">Backups</h3>
                        <div class="card-value">Respaldo</div>
                        <p class="card-subtitle">Copias de seguridad</p>
                    </a>
                </div>

                <!-- Reportes -->
                <div class="col-md-6 col-lg-4">
                    <a href="reportes.php" class="dashboard-card fade-in" style="animation-delay: 1.0s; border-top: 4px solid #EC4899;">
                        <div class="card-icon" style="background: linear-gradient(135deg, #EC4899 0%, #DB2777 100%);">
                            <svg viewBox="0 0 100 100" fill="none">
                                <rect x="25" y="60" width="15" height="20" fill="white" opacity="0.9"/>
                                <rect x="43" y="45" width="15" height="35" fill="white" opacity="0.9"/>
                                <rect x="61" y="30" width="15" height="50" fill="white" opacity="0.9"/>
                            </svg>
                            <div class="icon-badge">
                                <i class="fas fa-chart-bar" style="font-size: 18px;"></i>
                            </div>
                        </div>
                        <h3 class="card-title">Reportes</h3>
                        <div class="card-value">Generar</div>
                        <p class="card-subtitle">Informes y análisis</p>
                    </a>
                </div>

                <!-- Importar/Exportar -->
                <div class="col-md-6 col-lg-4">
                    <a href="importar.php" class="dashboard-card fade-in" style="animation-delay: 1.1s; border-top: 4px solid #14B8A6;">
                        <div class="card-icon" style="background: linear-gradient(135deg, #14B8A6 0%, #0D9488 100%);">
                            <svg viewBox="0 0 100 100" fill="none">
                                <path d="M35 40 L25 50 L30 50 L30 65 L40 65 L40 50 L45 50 Z" fill="white" opacity="0.9"/>
                                <path d="M65 60 L75 50 L70 50 L70 35 L60 35 L60 50 L55 50 Z" fill="white" opacity="0.9"/>
                            </svg>
                            <div class="icon-badge">
                                <i class="fas fa-exchange-alt" style="font-size: 18px;"></i>
                            </div>
                        </div>
                        <h3 class="card-title">Importar/Exportar</h3>
                        <div class="card-value">Datos</div>
                        <p class="card-subtitle">Migración de información</p>
                    </a>
                </div>

                <!-- Notificaciones -->
                <div class="col-md-6 col-lg-4">
                    <a href="notificaciones.php" class="dashboard-card fade-in" style="animation-delay: 1.2s; border-top: 4px solid #EF4444;">
                        <div class="card-icon" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);">
                            <svg viewBox="0 0 100 100" fill="none">
                                <path d="M50 25 C40 25 35 35 35 45 L35 55 L30 65 L70 65 L65 55 L65 45 C65 35 60 25 50 25 Z" fill="white" opacity="0.9"/>
                                <rect x="45" y="65" width="10" height="5" rx="2" fill="white" opacity="0.9"/>
                            </svg>
                            <div class="icon-badge">
                                <i class="fas fa-bell" style="font-size: 18px;"></i>
                            </div>
                        </div>
                        <h3 class="card-title">Notificaciones</h3>
                        <div class="card-value">Alertas</div>
                        <p class="card-subtitle">Sistema de avisos</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/clock.js"></script>
</body>
</html>
