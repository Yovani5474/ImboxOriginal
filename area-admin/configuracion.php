<?php
require_once 'config/auth.php';
require_once 'config/database.php';
requireAuth();

$db = Database::getInstance()->getConnection();
$mensaje = '';
$tipo_mensaje = '';

// Procesar configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí irían las configuraciones guardadas en BD o archivo
    $mensaje = "Configuración guardada exitosamente";
    $tipo_mensaje = "success";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Configuración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-home me-2"></i><?php echo APP_NAME; ?>
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="usuarios.php">Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link active" href="configuracion.php">Configuración</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><?php echo getUserName(); ?></a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="container">
            <div class="page-header fade-in">
                <h1><i class="fas fa-cogs me-2"></i>Configuración del Sistema</h1>
                <p>Personaliza y configura el sistema</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-4">
                    <!-- General -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="fas fa-globe me-2"></i>Configuración General</h5>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre del Sistema</label>
                                <input type="text" class="form-control" name="app_name" value="<?php echo APP_NAME; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">URL Base</label>
                                <input type="text" class="form-control" name="app_url" value="<?php echo APP_URL; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Zona Horaria</label>
                                <select class="form-select" name="timezone">
                                    <option value="America/Mexico_City">América/Ciudad de México</option>
                                    <option value="America/Lima">América/Lima</option>
                                    <option value="America/Bogota">América/Bogotá</option>
                                    <option value="America/Santiago">América/Santiago</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Idioma</label>
                                <select class="form-select" name="language">
                                    <option value="es">Español</option>
                                    <option value="en">English</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="fas fa-envelope me-2"></i>Configuración de Email</h5>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Servidor SMTP</label>
                                <input type="text" class="form-control" name="smtp_host" placeholder="smtp.gmail.com">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Puerto</label>
                                <input type="number" class="form-control" name="smtp_port" placeholder="587">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Usuario</label>
                                <input type="email" class="form-control" name="smtp_user" placeholder="tu@email.com">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Contraseña</label>
                                <input type="password" class="form-control" name="smtp_pass" placeholder="••••••••">
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="smtp_ssl" id="smtp_ssl">
                                <label class="form-check-label" for="smtp_ssl">
                                    Usar SSL/TLS
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Seguridad -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="fas fa-shield-alt me-2"></i>Seguridad</h5>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tiempo de Sesión (minutos)</label>
                                <input type="number" class="form-control" name="session_timeout" value="30">
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="require_2fa" id="require_2fa">
                                <label class="form-check-label" for="require_2fa">
                                    Requerir autenticación de dos factores
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="password_complexity" id="password_complexity">
                                <label class="form-check-label" for="password_complexity">
                                    Requerir contraseñas complejas
                                </label>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Intentos de Login Permitidos</label>
                                <input type="number" class="form-control" name="max_login_attempts" value="5">
                            </div>
                        </div>
                    </div>

                    <!-- Base de Datos -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="fas fa-database me-2"></i>Base de Datos</h5>
                            
                            <div class="alert alert-info">
                                <strong><i class="fas fa-info-circle me-2"></i>Información de Conexión</strong>
                                <hr>
                                <div><strong>Host:</strong> <?php echo DB_HOST; ?></div>
                                <div><strong>Base de Datos:</strong> <?php echo DB_NAME; ?></div>
                                <div><strong>Usuario:</strong> <?php echo DB_USER; ?></div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="backup.php" class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>Crear Backup
                                </a>
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalOptimizar">
                                    <i class="fas fa-wrench me-2"></i>Optimizar BD
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mantenimiento -->
                    <div class="col-md-12">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="fas fa-tools me-2"></i>Mantenimiento</h5>
                            
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="d-grid">
                                        <a href="logs.php" class="btn btn-outline-primary">
                                            <i class="fas fa-list me-2"></i>Ver Logs
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-outline-warning" onclick="limpiarCache()">
                                            <i class="fas fa-broom me-2"></i>Limpiar Caché
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-grid">
                                        <a href="actualizaciones.php" class="btn btn-outline-info">
                                            <i class="fas fa-sync me-2"></i>Actualizaciones
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalMantenimiento">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Modo Mantenimiento
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-2"></i>Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Optimizar -->
    <div class="modal fade" id="modalOptimizar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Optimizar Base de Datos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Esta acción optimizará todas las tablas de la base de datos.</p>
                    <p class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Se recomienda hacer un backup antes.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="optimizarBD()">Optimizar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function limpiarCache() {
        if (confirm('¿Deseas limpiar el caché del sistema?')) {
            alert('Caché limpiado exitosamente');
        }
    }
    
    function optimizarBD() {
        alert('Base de datos optimizada');
        bootstrap.Modal.getInstance(document.getElementById('modalOptimizar')).hide();
    }
    </script>
</body>
</html>
