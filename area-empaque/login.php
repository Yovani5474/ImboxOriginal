<?php
/**
 * Login - Sistema de Autenticación
 */

session_start();

require_once __DIR__ . '/models/Usuario.php';

$error = '';
$mensaje = '';

// Si ya está autenticado, redirigir
if (Usuario::sesionActiva()) {
    header('Location: index.php');
    exit;
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        $usuario = new Usuario();
        $resultado = $usuario->autenticar($email, $password);
        
        if ($resultado) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email o contraseña incorrectos';
        }
    }
}

// Mensaje de logout
if (isset($_GET['logout'])) {
    $mensaje = 'Sesión cerrada correctamente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Área de Empaque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #FF8C00 0%, #FFB84D 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #FF8C00, #FFB84D);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .btn-login {
            background: #FF8C00;
            border: none;
            color: white;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #D06F00;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,140,0,0.4);
        }
        .credentials-info {
            background: #fffaf0;
            border-left: 4px solid #FF8C00;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        .credentials-info h6 {
            color: #FF8C00;
            margin-bottom: 10px;
        }
        .credentials-info code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-box-open fa-3x mb-3"></i>
            <h1>Área de Empaque</h1>
            <p class="mb-0">Sistema de Gestión de Transferencias</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required autofocus>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                </div>
                
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="recordar">
                    <label class="form-check-label" for="recordar">
                        Recordar sesión
                    </label>
                </div>
                
                <button type="submit" class="btn btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                </button>
            </form>
            
            <!-- Credenciales de prueba -->
            <div class="credentials-info">
                <h6><i class="fas fa-info-circle me-2"></i>Credenciales de Prueba</h6>
                <small>
                    <strong>Administrador:</strong><br>
                    Email: <code>admin@empaque.com</code><br>
                    Password: <code>admin123</code>
                </small>
                <hr class="my-2">
                <small>
                    <strong>Supervisor:</strong><br>
                    Email: <code>supervisor@empaque.com</code><br>
                    Password: <code>super123</code>
                </small>
                <hr class="my-2">
                <small>
                    <strong>Operador:</strong><br>
                    Email: <code>operador@empaque.com</code><br>
                    Password: <code>oper123</code>
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
