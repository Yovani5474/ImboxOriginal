-- =====================================================
-- ESQUEMA DE TABLAS - BASE DE DATOS
-- Sistema de Control de Almacén - Integrado
-- Soporta: Almacén 1 (Corte) y Almacén 2 (Empaque)
-- Solo estructura de tablas (sin datos)
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS control_almacen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE control_almacen;

-- =====================================================
-- TABLAS DE USUARIOS Y SEGURIDAD
-- =====================================================

-- Tabla de usuarios del sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE,
    rol ENUM('admin', 'supervisor', 'operador', 'recepcionista') DEFAULT 'operador',
    almacen_asignado INT DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso TIMESTAMP NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rol (rol)
) ENGINE=InnoDB;

-- Tabla de sesiones
CREATE TABLE IF NOT EXISTS sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(50),
    user_agent VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NULL,
    activa TINYINT(1) DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB;

-- =====================================================
-- TABLAS DE CATÁLOGOS Y CONFIGURACIÓN
-- =====================================================

-- Tabla de almacenes
CREATE TABLE IF NOT EXISTS almacenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    ubicacion VARCHAR(255),
    tipo ENUM('corte', 'empaque', 'bodega', 'distribucion') DEFAULT 'bodega',
    responsable VARCHAR(150),
    telefono VARCHAR(50),
    direccion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_clave (clave),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB;

-- Tabla de tipos de prenda
CREATE TABLE IF NOT EXISTS tipos_prenda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    categoria VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB;

-- Tabla de encargados de taller
CREATE TABLE IF NOT EXISTS encargados_taller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    especialidad VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de recepcionistas
CREATE TABLE IF NOT EXISTS recepcionistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    almacen_id INT DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (almacen_id) REFERENCES almacenes(id) ON DELETE SET NULL,
    INDEX idx_almacen (almacen_id)
) ENGINE=InnoDB;

-- Tabla de trabajadores/costureros
CREATE TABLE IF NOT EXISTS trabajadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(50),
    email VARCHAR(150),
    especialidad VARCHAR(100),
    nivel_experiencia ENUM('junior', 'medio', 'senior', 'experto') DEFAULT 'medio',
    fecha_ingreso DATE,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_especialidad (especialidad)
) ENGINE=InnoDB;

-- Tabla de proveedores (para almacén 1)
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    razon_social VARCHAR(200),
    rfc VARCHAR(20),
    telefono VARCHAR(50),
    email VARCHAR(150),
    direccion TEXT,
    ciudad VARCHAR(100),
    pais VARCHAR(100) DEFAULT 'México',
    contacto_principal VARCHAR(150),
    telefono_contacto VARCHAR(50),
    condiciones_pago VARCHAR(100),
    dias_credito INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB;

-- Tabla de tipos de material (para almacén 1)
CREATE TABLE IF NOT EXISTS tipos_material (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(100),
    unidad_medida ENUM('metros', 'kilogramos', 'piezas', 'rollos', 'cajas') DEFAULT 'metros',
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB;

-- =====================================================
-- TABLAS DE CONTROL DE ENTRADA - ALMACÉN 1 (CORTE)
-- =====================================================

-- Tabla principal de control de entrada de materiales
CREATE TABLE IF NOT EXISTS controles_entrada_materiales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referencia VARCHAR(100) NOT NULL UNIQUE,
    almacen_id INT NOT NULL,
    proveedor_id INT DEFAULT NULL,
    fecha_entrada DATE NOT NULL,
    orden_compra VARCHAR(100),
    factura VARCHAR(100),
    remision VARCHAR(100),
    total_rollos INT DEFAULT 0,
    total_metros DECIMAL(12,2) DEFAULT 0,
    total_kilos DECIMAL(12,2) DEFAULT 0,
    costo_total DECIMAL(12,2) DEFAULT 0,
    moneda VARCHAR(10) DEFAULT 'MXN',
    estado ENUM('pendiente', 'parcial', 'completo', 'cancelado') DEFAULT 'pendiente',
    observaciones TEXT,
    usuario_creacion VARCHAR(100),
    usuario_recepcion VARCHAR(100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (almacen_id) REFERENCES almacenes(id),
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL,
    INDEX idx_referencia (referencia),
    INDEX idx_fecha_entrada (fecha_entrada),
    INDEX idx_almacen (almacen_id),
    INDEX idx_proveedor (proveedor_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB;

-- Tabla de detalles de materiales recibidos
CREATE TABLE IF NOT EXISTS detalles_materiales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    control_entrada_material_id INT NOT NULL,
    numero_item INT NOT NULL,
    tipo_material_id INT DEFAULT NULL,
    tipo_tela VARCHAR(100),
    color VARCHAR(100),
    ancho_cm DECIMAL(8,2),
    cantidad_rollos INT DEFAULT 0,
    metros_por_rollo DECIMAL(10,2) DEFAULT 0,
    metros_total DECIMAL(10,2) DEFAULT 0,
    kilos DECIMAL(10,2) DEFAULT 0,
    precio_unitario DECIMAL(10,2) DEFAULT 0,
    precio_total DECIMAL(12,2) DEFAULT 0,
    lote VARCHAR(100),
    observaciones VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (control_entrada_material_id) REFERENCES controles_entrada_materiales(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_material_id) REFERENCES tipos_material(id) ON DELETE SET NULL,
    INDEX idx_control_material (control_entrada_material_id),
    INDEX idx_tipo_material (tipo_material_id)
) ENGINE=InnoDB;

-- =====================================================
-- TABLAS DE CONTROL DE ENTRADA - ALMACÉN 2 (EMPAQUE)
-- =====================================================

-- Tabla principal de control de entrada de prendas
CREATE TABLE IF NOT EXISTS control_entrada (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transferencia_id INT DEFAULT NULL,
    control_entrada_material_id INT DEFAULT NULL,
    almacen_id INT NOT NULL DEFAULT 2,
    fecha_recepcion DATE NOT NULL,
    tipo_prenda VARCHAR(100) DEFAULT NULL,
    tipo_prenda_id INT DEFAULT NULL,
    trabajador_id INT DEFAULT NULL,
    encargado_taller_id INT DEFAULT NULL,
    recepcionista_id INT DEFAULT NULL,
    estado VARCHAR(50) DEFAULT 'pendiente',
    puntos_favor DECIMAL(10, 2) DEFAULT 0,
    precio_10 DECIMAL(10, 2) DEFAULT 0,
    precio_15 DECIMAL(10, 2) DEFAULT 0,
    total_prendas_recibidas INT DEFAULT 0,
    total_prendas_esperadas INT DEFAULT 0,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (almacen_id) REFERENCES almacenes(id),
    FOREIGN KEY (tipo_prenda_id) REFERENCES tipos_prenda(id),
    FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id) ON DELETE SET NULL,
    FOREIGN KEY (encargado_taller_id) REFERENCES encargados_taller(id),
    FOREIGN KEY (recepcionista_id) REFERENCES recepcionistas(id),
    FOREIGN KEY (control_entrada_material_id) REFERENCES controles_entrada_materiales(id) ON DELETE SET NULL,
    INDEX idx_fecha_recepcion (fecha_recepcion),
    INDEX idx_tipo_prenda (tipo_prenda_id),
    INDEX idx_trabajador (trabajador_id),
    INDEX idx_almacen (almacen_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB;

-- Tabla de detalles de prendas (con todas las tallas)
CREATE TABLE IF NOT EXISTS detalles_prenda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    control_entrada_id INT NOT NULL,
    numero_item INT NOT NULL,
    color_codigo VARCHAR(50),
    -- Tallas numéricas infantiles
    talla_2 INT DEFAULT 0,
    talla_4 INT DEFAULT 0,
    talla_6 INT DEFAULT 0,
    talla_8 INT DEFAULT 0,
    talla_10 INT DEFAULT 0,
    talla_12 INT DEFAULT 0,
    talla_14 INT DEFAULT 0,
    talla_16 INT DEFAULT 0,
    -- Tallas con letras
    talla_xs INT DEFAULT 0,
    talla_s INT DEFAULT 0,
    talla_m INT DEFAULT 0,
    talla_l INT DEFAULT 0,
    talla_xl INT DEFAULT 0,
    talla_xxl INT DEFAULT 0,
    talla_xxxl INT DEFAULT 0,
    -- Campo calculado de total
    total_prendas INT GENERATED ALWAYS AS (
        talla_2 + talla_4 + talla_6 + talla_8 + talla_10 + talla_12 + talla_14 + talla_16 + 
        talla_xs + talla_s + talla_m + talla_l + talla_xl + talla_xxl + talla_xxxl
    ) STORED,
    estado_entrega VARCHAR(100),
    observacion_item VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (control_entrada_id) REFERENCES control_entrada(id) ON DELETE CASCADE,
    INDEX idx_control_entrada (control_entrada_id),
    INDEX idx_color (color_codigo)
) ENGINE=InnoDB;

-- =====================================================
-- TABLAS DE TRANSFERENCIAS ENTRE ALMACENES
-- =====================================================

-- Tabla de transferencias entre almacenes
CREATE TABLE IF NOT EXISTS transferencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referencia VARCHAR(100) NOT NULL UNIQUE,
    almacen_origen_id INT NOT NULL,
    almacen_destino_id INT NOT NULL,
    control_entrada_id INT DEFAULT NULL,
    control_entrada_material_id INT DEFAULT NULL,
    total_items INT DEFAULT 0,
    tipo_prenda VARCHAR(100),
    color VARCHAR(100),
    talla VARCHAR(20),
    trabajador_id INT DEFAULT NULL,
    trabajador_nombre VARCHAR(150),
    confirmado_por_trabajador TINYINT(1) DEFAULT 0,
    fecha_confirmacion_trabajador TIMESTAMP NULL,
    faltantes_json TEXT NULL,
    estado ENUM('pendiente', 'enviado', 'en_transito', 'recibido', 'parcial', 'completado', 'cancelado') DEFAULT 'pendiente',
    prioridad ENUM('baja', 'normal', 'alta', 'urgente') DEFAULT 'normal',
    tipo_transporte VARCHAR(100),
    numero_guia VARCHAR(100),
    fecha_envio TIMESTAMP NULL,
    fecha_recepcion TIMESTAMP NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(100),
    usuario_envio VARCHAR(100),
    usuario_recepcion VARCHAR(100),
    observaciones TEXT,
    costo_envio DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (almacen_origen_id) REFERENCES almacenes(id),
    FOREIGN KEY (almacen_destino_id) REFERENCES almacenes(id),
    FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id) ON DELETE SET NULL,
    FOREIGN KEY (control_entrada_id) REFERENCES control_entrada(id) ON DELETE SET NULL,
    FOREIGN KEY (control_entrada_material_id) REFERENCES controles_entrada_materiales(id) ON DELETE SET NULL,
    INDEX idx_referencia (referencia),
    INDEX idx_origen (almacen_origen_id),
    INDEX idx_destino (almacen_destino_id),
    INDEX idx_trabajador (trabajador_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_envio (fecha_envio),
    INDEX idx_fecha_recepcion (fecha_recepcion)
) ENGINE=InnoDB;

-- Añadir foreign key desde control_entrada hacia transferencias
ALTER TABLE control_entrada
ADD CONSTRAINT fk_control_entrada_transferencia 
FOREIGN KEY (transferencia_id) REFERENCES transferencias(id) ON DELETE SET NULL;

-- Tabla de detalles de items en transferencia
CREATE TABLE IF NOT EXISTS transferencia_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transferencia_id INT NOT NULL,
    numero_item INT NOT NULL,
    tipo_prenda_id INT DEFAULT NULL,
    color VARCHAR(100),
    talla VARCHAR(20),
    cantidad_enviada INT DEFAULT 0,
    cantidad_recibida INT DEFAULT 0,
    cantidad_faltante INT DEFAULT 0,
    observaciones VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transferencia_id) REFERENCES transferencias(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_prenda_id) REFERENCES tipos_prenda(id) ON DELETE SET NULL,
    INDEX idx_transferencia (transferencia_id)
) ENGINE=InnoDB;

-- =====================================================
-- TABLAS DE AUDITORÍA Y REGISTRO
-- =====================================================

-- Tabla de registro de cambios
CREATE TABLE IF NOT EXISTS registro_cambios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla_referencia VARCHAR(50) NOT NULL,
    id_referencia INT NOT NULL,
    tipo_cambio ENUM('creacion', 'actualizacion', 'eliminacion') NOT NULL,
    datos_anteriores JSON DEFAULT NULL,
    datos_nuevos JSON DEFAULT NULL,
    usuario VARCHAR(100) DEFAULT NULL,
    ip_address VARCHAR(50),
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descripcion TEXT DEFAULT NULL,
    INDEX idx_tabla_referencia (tabla_referencia, id_referencia),
    INDEX idx_fecha_cambio (fecha_cambio),
    INDEX idx_usuario (usuario)
) ENGINE=InnoDB;

-- Tabla de logs del sistema
CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nivel ENUM('debug', 'info', 'warning', 'error', 'critical') DEFAULT 'info',
    categoria VARCHAR(50),
    mensaje TEXT NOT NULL,
    contexto JSON DEFAULT NULL,
    usuario VARCHAR(100),
    ip_address VARCHAR(50),
    user_agent VARCHAR(255),
    url VARCHAR(500),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nivel (nivel),
    INDEX idx_categoria (categoria),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB;

-- =====================================================
-- TABLAS DE REPORTES Y ESTADÍSTICAS
-- =====================================================

-- Vista materializada de estadísticas de transferencias
CREATE TABLE IF NOT EXISTS estadisticas_transferencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    almacen_origen_id INT NOT NULL,
    almacen_destino_id INT NOT NULL,
    total_transferencias INT DEFAULT 0,
    total_items INT DEFAULT 0,
    transferencias_completadas INT DEFAULT 0,
    transferencias_pendientes INT DEFAULT 0,
    tiempo_promedio_horas DECIMAL(8,2) DEFAULT 0,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (almacen_origen_id) REFERENCES almacenes(id),
    FOREIGN KEY (almacen_destino_id) REFERENCES almacenes(id),
    UNIQUE KEY uk_estadistica (fecha, almacen_origen_id, almacen_destino_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB;

-- =====================================================
-- FIN DEL ESQUEMA DE TABLAS
-- =====================================================
