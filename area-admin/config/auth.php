<?php
/**
 * Sistema de Autenticación
 */

// Configuración de la aplicación
define('APP_NAME', 'IMBOX Admin');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'IMBOX');

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

function requireAuth() {
    // ⚠️ LOGIN DESACTIVADO TEMPORALMENTE
    // if (!isLoggedIn()) {
    //     header('Location: login.php');
    //     exit;
    // }
    
    // Simular sesión activa para desarrollo
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_email'] = 'cristian@imbox.local';
        $_SESSION['user_name'] = 'CRISTIAN';
        $_SESSION['user_role'] = 'admin';
    }
    return true;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserEmail() {
    return $_SESSION['user_email'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Usuario';
}

function getUserRole() {
    return $_SESSION['user_role'] ?? 'user';
}

function isAdmin() {
    return getUserRole() === 'admin';
}

function login($userId, $email, $name, $role = 'user') {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = $role;
}

function logout() {
    session_unset();
    session_destroy();
}
