<?php
// Archivo de configuración global
// Cambia el nombre de la base de datos para apuntar a otro almacén (por ejemplo: control_almacen_almacen2)
// Puedes definir la constante DB_NAME aquí o establecer la variable de entorno DB_NAME en el sistema.

if (!defined('DB_NAME')) {
    $envDb = getenv('DB_NAME');
    if ($envDb !== false && $envDb !== '') {
        define('DB_NAME', $envDb);
    } else {
        define('DB_NAME', 'control_almacen'); // Base de datos del almacén
    }
}

// Otros settings globales pueden ir aquí en el futuro

// Opciones para sincronización entre instancias (Opción B)
// URL base del servidor remoto (por ejemplo: http://192.168.1.10) - dejar vacío para desactivar
if (!defined('REMOTE_TRANSFER_URL')) {
    $envRemote = getenv('REMOTE_TRANSFER_URL');
    define('REMOTE_TRANSFER_URL', $envRemote !== false ? $envRemote : '');
}

// Token API para llamadas entre instancias. Si está vacío, no se requiere token.
if (!defined('REMOTE_API_TOKEN')) {
    $envToken = getenv('REMOTE_API_TOKEN');
    define('REMOTE_API_TOKEN', $envToken !== false ? $envToken : '');
}

// Identificador local de almacén (opcional). Si se deja vacío, utiliza 1.
if (!defined('LOCAL_ALMACEN_ID')) {
    $envLocalId = getenv('LOCAL_ALMACEN_ID');
    define('LOCAL_ALMACEN_ID', $envLocalId !== false && $envLocalId !== '' ? (int)$envLocalId : 1);
}

?>
