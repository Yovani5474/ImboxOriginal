<?php
/**
 * Cerrar Sesión
 */

session_start();
session_destroy();

header('Location: login.php?logout=1');
exit;
?>
