<?php
/**
 * Script de Instalación Automática
 * Panel de Administrador IMBOX
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'imbox_sistema_unificado';

$success_messages = [];
$error_messages = [];

// Función para ejecutar SQL
function ejecutarSQL($conn, $sql, $descripcion) {
    global $success_messages, $error_messages;
    
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        $success_messages[] = "✅ " . $descripcion;
        return true;
    } else {
        $error_messages[] = "❌ Error en " . $descripcion . ": " . $conn->error;
        return false;
    }
}

// Procesar instalación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    
    // Paso 1: Conectar sin base de datos para crearla
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    if ($conn->connect_error) {
        $error_messages[] = "❌ Error de conexión: " . $conn->connect_error;
    } else {
        $success_messages[] = "✅ Conexión al servidor MySQL exitosa";
        
        // Paso 2: Crear base de datos
        $sql = "CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (ejecutarSQL($conn, $sql, "Creación de base de datos '{$db_name}'")) {
            
            // Paso 3: Seleccionar base de datos
            $conn->select_db($db_name);
            $success_messages[] = "✅ Base de datos '{$db_name}' seleccionada";
            
            // Paso 4: Leer archivo schema.sql
            $schema_file = __DIR__ . '/database/schema.sql';
            if (file_exists($schema_file)) {
                $sql = file_get_contents($schema_file);
                
                // Eliminar la línea CREATE DATABASE y USE (ya lo hicimos)
                $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
                $sql = preg_replace('/USE.*?;/i', '', $sql);
                
                // Generar hash correcto para la contraseña admin123
                $password_hash = password_hash('admin123', PASSWORD_BCRYPT);
                
                // Reemplazar el hash en el SQL
                $sql = preg_replace(
                    "/INSERT INTO usuarios.*?VALUES.*?\('admin@admin\.com', '.*?',/",
                    "INSERT INTO usuarios (email, password, nombre, rol) VALUES ('admin@admin.com', '$password_hash',",
                    $sql
                );
                
                // Ejecutar schema
                if (ejecutarSQL($conn, $sql, "Creación de tablas e inserción de datos")) {
                    $success_messages[] = "✅ ¡Instalación completada exitosamente!";
                    $success_messages[] = "🎉 El sistema está listo para usarse";
                }
            } else {
                $error_messages[] = "❌ No se encontró el archivo schema.sql";
            }
        }
        
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - IMBOX Admin</title>
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
        
        .install-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .logo-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-header h1 {
            color: #FF8C00;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .logo-header p {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        .step-indicator {
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }
        
        .info-box {
            background: #FFF5E6;
            border-left: 4px solid #FF8C00;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .info-box h6 {
            color: #FF8C00;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .btn-install {
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            border: none;
            color: white;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 50px;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-install:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 140, 0, 0.4);
        }
        
        .success-message {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            color: #155724;
        }
        
        .error-message {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            color: #721c24;
        }
        
        .credentials-box {
            background: #e7f3ff;
            border: 2px dashed #0066cc;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .credentials-box h6 {
            color: #0066cc;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .credential-item {
            background: white;
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .credential-label {
            font-weight: 600;
            color: #4b5563;
        }
        
        .credential-value {
            font-family: monospace;
            color: #0066cc;
            font-weight: 700;
        }
        
        .btn-access {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-access:hover {
            background: #218838;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="logo-header">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#FF8C00" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <h1>IMBOX Admin</h1>
            <p>Panel de Administrador</p>
        </div>
        
        <?php if (empty($_POST)): ?>
        
        <div class="step-indicator">
            🚀 Instalación Rápida en 1 Click
        </div>
        
        <div class="info-box">
            <h6>📋 Requisitos Previos</h6>
            <ul>
                <li>XAMPP o servidor similar instalado</li>
                <li>Apache y MySQL activos</li>
                <li>PHP 7.4 o superior</li>
            </ul>
        </div>
        
        <div class="info-box">
            <h6>⚙️ Configuración Actual</h6>
            <ul>
                <li><strong>Servidor:</strong> <?php echo $db_host; ?></li>
                <li><strong>Usuario:</strong> <?php echo $db_user; ?></li>
                <li><strong>Base de Datos:</strong> <?php echo $db_name; ?></li>
            </ul>
        </div>
        
        <div class="alert alert-warning">
            <strong>⚠️ Importante:</strong> Este proceso creará la base de datos e insertará datos de ejemplo.
            Si ya existe una base de datos con el mismo nombre, se sobrescribirán los datos.
        </div>
        
        <form method="POST">
            <button type="submit" name="install" class="btn-install">
                🔧 Instalar Sistema Ahora
            </button>
        </form>
        
        <?php else: ?>
        
        <div class="step-indicator">
            📊 Resultado de la Instalación
        </div>
        
        <?php foreach ($success_messages as $msg): ?>
            <div class="success-message"><?php echo $msg; ?></div>
        <?php endforeach; ?>
        
        <?php foreach ($error_messages as $msg): ?>
            <div class="error-message"><?php echo $msg; ?></div>
        <?php endforeach; ?>
        
        <?php if (empty($error_messages) && !empty($success_messages)): ?>
        
        <div class="credentials-box">
            <h6>🔐 Credenciales de Acceso</h6>
            
            <div class="credential-item">
                <span class="credential-label">Email:</span>
                <span class="credential-value">cristian@imbox.local</span>
            </div>
            
            <div class="credential-item">
                <span class="credential-label">Username:</span>
                <span class="credential-value">cristian</span>
            </div>
            
            <div class="credential-item">
                <span class="credential-label">Contraseña:</span>
                <span class="credential-value">admin123</span>
            </div>
            
            <div class="credential-item">
                <span class="credential-label">Rol:</span>
                <span class="credential-value">Administrador</span>
            </div>
            
            <div class="mt-3 p-2 bg-light rounded">
                <small class="text-muted">
                    <strong>Supervisores también disponibles:</strong><br>
                    araceli, lisbeth, yovani, wilmer<br>
                    (Contraseña: admin123)
                </small>
            </div>
            
            <div class="text-center">
                <a href="login.php" class="btn-access">
                    🚪 Acceder al Sistema
                </a>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <strong>💡 Consejo:</strong> Cambia la contraseña después del primer inicio de sesión.
        </div>
        
        <div class="alert alert-success mt-3">
            <strong>🎉 ¡Felicidades!</strong> Tu panel de administrador está listo para usar.
        </div>
        
        <?php else: ?>
        
        <div class="alert alert-danger mt-3">
            <strong>❌ Error:</strong> La instalación no se completó correctamente. 
            Verifica que MySQL esté activo y que las credenciales sean correctas.
        </div>
        
        <a href="install.php" class="btn btn-secondary mt-3">
            🔄 Intentar de Nuevo
        </a>
        
        <?php endif; ?>
        <?php endif; ?>
        
        <hr class="my-4">
        
        <div class="text-center text-muted">
            <small>
                <strong>IMBOX Admin</strong> v1.0.0<br>
                Desarrollado con ❤️ por IMBOX<br>
                © 2025 Todos los derechos reservados
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
