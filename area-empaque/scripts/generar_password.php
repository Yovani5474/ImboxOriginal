<?php
/**
 * Generador de Hash de Contraseñas
 * Uso: php generar_password.php [contraseña]
 */

// Obtener contraseña desde línea de comandos o usar la predeterminada
$password = $argv[1] ?? 'imbox2025';

// Generar hash bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  GENERADOR DE HASH DE CONTRASEÑAS - SISTEMA IMBOX\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n";
echo "Contraseña:  {$password}\n";
echo "Hash BCrypt: {$hash}\n";
echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n";
echo "INSTRUCCIONES:\n";
echo "1. Copia el hash generado arriba\n";
echo "2. Úsalo en la columna 'password_hash' de la tabla 'usuarios'\n";
echo "3. Para generar otro hash, ejecuta:\n";
echo "   php generar_password.php tu_contraseña\n";
echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n";

// Verificar que funciona
if (password_verify($password, $hash)) {
    echo "✓ Verificación exitosa: El hash es válido\n\n";
} else {
    echo "✗ Error: El hash no es válido\n\n";
}

// Generar hashes para usuarios comunes
echo "HASHES PREDEFINIDOS PARA USUARIOS:\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n";

$usuarios = [
    'admin123' => 'Contraseña de administrador',
    'imbox2025' => 'Contraseña general del sistema',
    'cristian123' => 'Contraseña para Cristian',
    'wilmer123' => 'Contraseña para Wilmer',
    'araceli123' => 'Contraseña para Araceli',
];

foreach ($usuarios as $pass => $desc) {
    $h = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 10]);
    echo "{$desc}:\n";
    echo "  Contraseña: {$pass}\n";
    echo "  Hash: {$h}\n\n";
}

echo "═══════════════════════════════════════════════════════════\n";
?>
