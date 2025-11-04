<?php
require_once 'config/auth.php';
require_once 'config/database.php';
requireAuth();

$db = Database::getInstance()->getConnection();

// Obtener todos los usuarios
$stmt = $db->query("SELECT * FROM usuarios ORDER BY fecha_creacion DESC");
$usuarios = $stmt->fetchAll();

// Estadísticas
$stats = [];
$stmt = $db->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
    SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as admins,
    SUM(CASE WHEN rol = 'supervisor' THEN 1 ELSE 0 END) as supervisores
FROM usuarios");
$stats = $stmt->fetch();

// Procesar acciones
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'crear') {
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $nombre = trim($_POST['nombre']);
        $password = $_POST['password'];
        $rol = $_POST['rol'];
        
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $stmt = $db->prepare("INSERT INTO usuarios (email, username, password, nombre, rol) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$email, $username, $password_hash, $nombre, $rol]);
            $mensaje = "Usuario creado exitosamente";
            $tipo_mensaje = "success";
            
            // Recargar usuarios
            $stmt = $db->query("SELECT * FROM usuarios ORDER BY fecha_creacion DESC");
            $usuarios = $stmt->fetchAll();
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
    
    if ($accion === 'eliminar') {
        $id = $_POST['id'];
        try {
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $mensaje = "Usuario eliminado";
            $tipo_mensaje = "success";
            
            // Recargar usuarios
            $stmt = $db->query("SELECT * FROM usuarios ORDER BY fecha_creacion DESC");
            $usuarios = $stmt->fetchAll();
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
    
    if ($accion === 'toggle_estado') {
        $id = $_POST['id'];
        try {
            $stmt = $db->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
            $stmt->execute([$id]);
            $mensaje = "Estado actualizado";
            $tipo_mensaje = "success";
            
            // Recargar usuarios
            $stmt = $db->query("SELECT * FROM usuarios ORDER BY fecha_creacion DESC");
            $usuarios = $stmt->fetchAll();
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-home me-2"></i>
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
                        <a class="nav-link active" href="usuarios.php">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <?php echo getUserName(); ?>
                        </a>
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
                        <h1><i class="fas fa-users me-2"></i>Gestión de Usuarios</h1>
                        <p>Administrar usuarios del sistema</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                        <i class="fas fa-plus me-2"></i>Nuevo Usuario
                    </button>
                </div>
            </div>

            <!-- Mensaje -->
            <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card fade-in" style="animation-delay: 0.1s; border-left-color: var(--primary-color);">
                        <div class="stat-label">Total Usuarios</div>
                        <div class="stat-value" style="color: var(--primary-color);">
                            <?php echo $stats['total']; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card fade-in" style="animation-delay: 0.2s; border-left-color: var(--success-color);">
                        <div class="stat-label">Activos</div>
                        <div class="stat-value" style="color: var(--success-color);">
                            <?php echo $stats['activos']; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card fade-in" style="animation-delay: 0.3s; border-left-color: var(--danger-color);">
                        <div class="stat-label">Administradores</div>
                        <div class="stat-value" style="color: var(--danger-color);">
                            <?php echo $stats['admins']; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card fade-in" style="animation-delay: 0.4s; border-left-color: var(--info-color);">
                        <div class="stat-label">Supervisores</div>
                        <div class="stat-value" style="color: var(--info-color);">
                            <?php echo $stats['supervisores']; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="table-container fade-in" style="animation-delay: 0.5s;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email / Username</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><strong>#<?php echo $usuario['id']; ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($usuario['email']); ?></div>
                                    <?php if ($usuario['username']): ?>
                                    <small class="text-muted">@<?php echo htmlspecialchars($usuario['username']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badge_class = $usuario['rol'] === 'admin' ? 'danger' : 'info';
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                        <?php echo ucfirst($usuario['rol']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($usuario['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="accion" value="toggle_estado">
                                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Cambiar estado">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar usuario?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

    <!-- Modal Nuevo Usuario -->
    <div class="modal fade" id="modalNuevoUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="crear">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-select" name="rol" required>
                                <option value="user">Usuario</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
