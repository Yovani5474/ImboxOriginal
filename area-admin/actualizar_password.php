<?php
/**
 * Script para actualizar la contraseña del administrador
 * Ejecutar una sola vez después de la instalación
 */

require_once 'config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Nueva contraseña
    $nueva_password = 'admin123';
    $password_hash = password_hash($nueva_password, PASSWORD_BCRYPT);
    
    // Actualizar en la base de datos
    $stmt = $db->prepare("UPDATE usuarios SET password = :password WHERE email = 'admin@admin.com'");
    $stmt->bindParam(':password', $password_hash);
    
    if ($stmt->execute()) {
        $mensaje = "✅ Contraseña actualizada correctamente";
        $tipo = "success";
    } else {
        $mensaje = "❌ Error al actualizar la contraseña";
        $tipo = "error";
    }
    
} catch (Exception $e) {
    $mensaje = "❌ Error: " . $e->getMessage();
    $tipo = "error";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #FF8C00 0%, #FFB84D 50%, #FFA500 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            color: #FF8C00;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .alert-success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        .credentials {
            background: #e7f3ff;
            border: 2px dashed #0066cc;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .credential-item {
            background: white;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            font-weight: 600;
        }
        
        .btn-login:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 140, 0, 0.4);
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔐 Actualización de Contraseña</h1>
        
        <div class="alert alert-<?php echo $tipo; ?>">
            <?php echo $mensaje; ?>
        </div>
        
        <?php if ($tipo === 'success'): ?>
        <div class="credentials">
            <h5 class="text-center mb-3" style="color: #0066cc;">✅ Nuevas Credenciales</h5>
            
            <div class="credential-item">
                <span><strong>Usuario:</strong></span>
                <code>admin@admin.com</code>
            </div>
            
            <div class="credential-item">
                <span><strong>Contraseña:</strong></span>
                <code>admin123</code>
            </div>
            
            <div class="text-center">
                <a href="login.php" class="btn-login">
                    🚪 Ir al Login
                </a>
            </div>
        </div>
        
        <div class="alert alert-warning mt-3">
            <strong>⚠️ Importante:</strong> Por seguridad, elimina este archivo después de usarlo.
        </div>
        <?php else: ?>
        <div class="alert alert-info mt-3">
            <strong>💡 Consejo:</strong> Asegúrate de que la base de datos esté correctamente instalada ejecutando primero <code>install.php</code>
        </div>
        
        <div class="text-center">
            <a href="install.php" class="btn btn-secondary">
                🔧 Ir al Instalador
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
