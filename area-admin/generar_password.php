<?php
/**
 * Generador de Hash de Contraseña
 * Usa este script para generar hashes bcrypt seguros
 */

// Contraseña deseada
$password = 'admin123';

// Generar hash
$hash = password_hash($password, PASSWORD_BCRYPT);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Hash</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #FF8C00;
        }
        .hash-box {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #FF8C00;
            margin: 20px 0;
            word-break: break-all;
            font-family: monospace;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Generador de Hash de Contraseña</h1>
        
        <p><strong>Contraseña:</strong> <code><?php echo $password; ?></code></p>
        
        <h3>Hash Generado:</h3>
        <div class="hash-box">
            <?php echo $hash; ?>
        </div>
        
        <div class="info">
            <h4>📝 Cómo usar este hash:</h4>
            <ol>
                <li>Copia el hash de arriba</li>
                <li>Actualiza la base de datos con este SQL:</li>
            </ol>
            <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;">
UPDATE usuarios 
SET password = '<?php echo $hash; ?>' 
WHERE email = 'admin@admin.com';
            </pre>
        </div>
        
        <div class="info" style="background: #fff3cd; margin-top: 20px;">
            <strong>⚠️ Nota:</strong> Este hash es único cada vez que se genera, pero todos son válidos para la misma contraseña.
        </div>
    </div>
</body>
</html>
