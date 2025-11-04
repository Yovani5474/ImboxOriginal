-- Base de datos unificada para todo el sistema IMBOX
CREATE DATABASE IF NOT EXISTS imbox_sistema_unificado CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE imbox_sistema_unificado;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'user') DEFAULT 'user',
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_sesion TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    telefono VARCHAR(50),
    direccion TEXT,
    rfc VARCHAR(13),
    empresa VARCHAR(255),
    premium TINYINT(1) DEFAULT 0,
    limite_credito DECIMAL(10, 2) DEFAULT 0,
    saldo_actual DECIMAL(10, 2) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    notas TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_email (email),
    INDEX idx_premium (premium),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de proveedores
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    empresa VARCHAR(255),
    email VARCHAR(255),
    telefono VARCHAR(50),
    direccion TEXT,
    rfc VARCHAR(13),
    tipo VARCHAR(100),
    premium TINYINT(1) DEFAULT 0,
    credito_dias INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    notas TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_premium (premium),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de empleados
CREATE TABLE IF NOT EXISTS empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    apellidos VARCHAR(255),
    email VARCHAR(255),
    telefono VARCHAR(50),
    direccion TEXT,
    puesto VARCHAR(100),
    departamento VARCHAR(100),
    salario DECIMAL(10, 2),
    fecha_contratacion DATE,
    premium TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    foto VARCHAR(255),
    notas TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_departamento (departamento),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de deudas
CREATE TABLE IF NOT EXISTS deudas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('cliente', 'proveedor') NOT NULL,
    referencia_id INT NOT NULL,
    referencia_nombre VARCHAR(255) NOT NULL,
    monto_total DECIMAL(10, 2) NOT NULL,
    monto_pagado DECIMAL(10, 2) DEFAULT 0,
    monto_pendiente DECIMAL(10, 2) NOT NULL,
    fecha_vencimiento DATE,
    estado ENUM('pendiente', 'parcial', 'pagada', 'vencida') DEFAULT 'pendiente',
    descripcion TEXT,
    notas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado),
    INDEX idx_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de pagos de deudas
CREATE TABLE IF NOT EXISTS pagos_deudas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deuda_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    metodo_pago VARCHAR(50),
    referencia VARCHAR(100),
    notas TEXT,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deuda_id) REFERENCES deudas(id) ON DELETE CASCADE,
    INDEX idx_deuda (deuda_id),
    INDEX idx_fecha (fecha_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de estadísticas (para almacenar métricas)
CREATE TABLE IF NOT EXISTS estadisticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    valor DECIMAL(15, 2),
    valor_texto TEXT,
    periodo VARCHAR(50),
    fecha DATE,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS INICIALES REALES - SISTEMA IMBOX
-- =====================================================

-- Usuario administrador: CRISTIAN (contraseña: admin123)
INSERT INTO usuarios (email, username, password, nombre, rol) VALUES
('cristian@imbox.local', 'cristian', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CRISTIAN', 'admin')
ON DUPLICATE KEY UPDATE rol = 'admin', activo = 1;

-- Proveedores reales
INSERT INTO proveedores (nombre, empresa, email, telefono, tipo, premium, credito_dias, activo) VALUES
('Textiles del Norte S.A.', 'Textiles del Norte', 'ventas@textilesnorte.com', '555-2001', 'Distribuidor', 1, 30, 1),
('Telas Importadas CDMX', 'Telas Import', 'contacto@telasimport.com', '555-2002', 'Mayorista', 1, 45, 1),
('Insumos Textiles Internacional', 'ITI Corp', 'info@insumostextil.com', '555-2003', 'Proveedor', 0, 15, 1)
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre),
    activo = 1;
