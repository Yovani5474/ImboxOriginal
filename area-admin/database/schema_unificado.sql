-- =====================================================
-- ESQUEMA UNIFICADO - SISTEMA INTEGRADO IMBOX
-- =====================================================
-- Combina 3 sistemas:
-- 1. Almacén 1 (Corte) - Control de materiales y transferencias
-- 2. Almacén 2 (Empaque) - Control de entrada de prendas
-- 3. Admin Panel - Gestión administrativa (Clientes, Proveedores, Empleados, Deudas)
-- =====================================================

CREATE DATABASE IF NOT EXISTS imbox_sistema_unificado CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE imbox_sistema_unificado;

-- =====================================================
-- MÓDULO 1: SISTEMA DE USUARIOS Y AUTENTICACIÓN
-- =====================================================

-- Tabla unificada de usuarios (reemplaza la tabla usuarios de ambos sistemas)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    apellidos VARCHAR(255),
    rol ENUM('admin', 'supervisor', 'operador', 'recepcionista', 'user') DEFAULT 'user',
    almacen_asignado INT DEFAULT NULL,
    telefono VARCHAR(50),
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso TIMESTAMP NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MÓDULO 2: CATÁLOGOS Y CONFIGURACIÓN
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de trabajadores/costureros (del sistema de almacenes)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tipos de material
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MÓDULO 3: GESTIÓN ADMINISTRATIVA
-- =====================================================

-- Tabla de clientes (del panel administrativo)
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

-- Tabla de proveedores (combinada para materiales y administrativa)
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    empresa VARCHAR(255),
    razon_social VARCHAR(200),
    rfc VARCHAR(20),
    telefono VARCHAR(50),
    email VARCHAR(150),
    direccion TEXT,
    ciudad VARCHAR(100),
    pais VARCHAR(100) DEFAULT 'México',
    contacto_principal VARCHAR(150),
    telefono_contacto VARCHAR(50),
    tipo VARCHAR(100),
    condiciones_pago VARCHAR(100),
    dias_credito INT DEFAULT 0,
    premium TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    notas TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_premium (premium)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de empleados (del panel administrativo)
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

-- Tabla de deudas (del panel administrativo)
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

-- =====================================================
-- MÓDULO 4: CONTROL DE ENTRADA DE MATERIALES (ALMACÉN 1)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MÓDULO 5: TRANSFERENCIAS ENTRE ALMACENES
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
    FOREIGN KEY (control_entrada_material_id) REFERENCES controles_entrada_materiales(id) ON DELETE SET NULL,
    INDEX idx_referencia (referencia),
    INDEX idx_origen (almacen_origen_id),
    INDEX idx_destino (almacen_destino_id),
    INDEX idx_trabajador (trabajador_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_envio (fecha_envio),
    INDEX idx_fecha_recepcion (fecha_recepcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MÓDULO 6: CONTROL DE ENTRADA DE PRENDAS (ALMACÉN 2)
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
    FOREIGN KEY (transferencia_id) REFERENCES transferencias(id) ON DELETE SET NULL,
    INDEX idx_fecha_recepcion (fecha_recepcion),
    INDEX idx_tipo_prenda (tipo_prenda_id),
    INDEX idx_trabajador (trabajador_id),
    INDEX idx_almacen (almacen_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MÓDULO 7: AUDITORÍA Y REGISTRO
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MÓDULO 8: ESTADÍSTICAS Y REPORTES
-- =====================================================

-- Tabla de estadísticas generales
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS INICIALES Y CATÁLOGOS REALES - SISTEMA IMBOX
-- Fuente: datos_iniciales.sql
-- Fecha: Octubre 2025
-- =====================================================

-- =====================================================
-- 1. ALMACENES IMBOX
-- =====================================================

INSERT INTO almacenes (clave, nombre, ubicacion, tipo) VALUES
('ALM1', 'Almacén Corte', 'Planta A - Area de Corte', 'corte'),
('ALM2', 'Almacén Empaque', 'Planta B - Area de Empaque', 'empaque'),
('BOD1', 'Bodega General', 'Edificio Principal', 'bodega')
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre),
    ubicacion = VALUES(ubicacion),
    tipo = VALUES(tipo);

-- =====================================================
-- 2. TRABAJADORES / COSTUREROS IMBOX
-- =====================================================

INSERT INTO trabajadores (codigo, nombre, especialidad, nivel_experiencia, activo) VALUES
('TRAB-001', 'CARLOS', 'Costura textil', 'medio', 1),
('TRAB-002', 'WILIAN', 'Costura textil', 'medio', 1),
('TRAB-003', 'CLEMENTE', 'Costura textil', 'medio', 1),
('TRAB-004', 'ERIKA', 'Costura textil', 'medio', 1),
('TRAB-005', 'LUZ', 'Costura textil', 'medio', 1),
('TRAB-006', 'LIZ', 'Costura textil', 'medio', 1),
('TRAB-007', 'ELVA', 'Costura textil', 'medio', 1)
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre),
    especialidad = VALUES(especialidad),
    activo = VALUES(activo);

-- =====================================================
-- 3. ENCARGADOS DE ALMACÉN IMBOX
-- =====================================================

INSERT INTO encargados_taller (codigo, nombre, especialidad, activo) VALUES
('ENC-001', 'ARACELI', 'Empaque', 1),
('ENC-002', 'LISBETH', 'Empaque', 1),
('ENC-003', 'YOVANI', 'Empaque', 1),
('ENC-004', 'WILMER', 'Corte', 1)
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre),
    especialidad = VALUES(especialidad),
    activo = VALUES(activo);

-- =====================================================
-- 4. RECEPCIONISTAS IMBOX
-- =====================================================

INSERT INTO recepcionistas (codigo, nombre, almacen_id, activo) VALUES
('REC-001', 'ARACELI', 2, 1),
('REC-002', 'LISBETH', 2, 1),
('REC-003', 'YOVANI', 2, 1),
('REC-004', 'WILMER', 1, 1)
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre),
    almacen_id = VALUES(almacen_id),
    activo = VALUES(activo);

-- =====================================================
-- 5. TIPOS DE PRENDA - CATÁLOGO IMBOX
-- =====================================================

INSERT INTO tipos_prenda (codigo, nombre, descripcion, categoria, activo) VALUES
('PREN-001', 'POLERA CLASICA CERRADO', 'Polera clásica modelo cerrado', 'Poleras', 1),
('PREN-002', 'POLERA CLASICA CIERRE', 'Polera clásica con cierre', 'Poleras', 1),
('PREN-003', 'POLERA CUELLO REDONDO', 'Polera con cuello redondo', 'Poleras', 1),
('PREN-004', 'POLERA CLASICA - ESTAMPADO', 'Polera clásica con estampado', 'Poleras', 1),
('PREN-005', 'POLERA CLASICA - REVOLT', 'Polera clásica modelo Revolt', 'Poleras', 1),
('PREN-006', 'BUSO UNISEX', 'Buso estilo unisex', 'Buzos', 1),
('PREN-007', 'BUSO EXTRAOVERSIZE', 'Buso extra oversize', 'Buzos', 1),
('PREN-008', 'POLERA BALACLAVA ADULTO', 'Polera con balaclava para adulto', 'Poleras', 1),
('PREN-009', 'POLERA CUELLO REDONDO IMBOX', 'Polera cuello redondo marca IMBOX', 'Poleras', 1)
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    categoria = VALUES(categoria),
    activo = VALUES(activo);

-- =====================================================
-- 6. PROVEEDORES REALES
-- =====================================================

INSERT INTO proveedores (codigo, nombre, telefono, email, ciudad, pais, activo) VALUES
('PROV001', 'Textiles del Norte S.A.', '555-2001', 'ventas@textilesnorte.com', 'Monterrey', 'México', 1),
('PROV002', 'Telas Importadas CDMX', '555-2002', 'contacto@telasimport.com', 'Ciudad de México', 'México', 1),
('PROV003', 'Insumos Textiles Internacional', '555-2003', 'info@insumostextil.com', 'Guadalajara', 'México', 1)
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre),
    telefono = VALUES(telefono),
    email = VALUES(email);

-- =====================================================
-- 7. TIPOS DE MATERIAL
-- =====================================================

INSERT INTO tipos_material (codigo, nombre, categoria, unidad_medida, activo) VALUES
('TEL-ALG', 'Tela de Algodón', 'Telas', 'metros', 1),
('TEL-POL', 'Tela de Poliéster', 'Telas', 'metros', 1),
('TEL-MIX', 'Tela Mixta', 'Telas', 'metros', 1),
('HIL-001', 'Hilo de Coser', 'Insumos', 'piezas', 1),
('BOT-001', 'Botones', 'Insumos', 'piezas', 1),
('CRE-001', 'Cremalleras', 'Insumos', 'piezas', 1)
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre),
    categoria = VALUES(categoria),
    unidad_medida = VALUES(unidad_medida);

-- =====================================================
-- 8. USUARIOS DEL SISTEMA IMBOX
-- =====================================================

-- Usuario administrador: CRISTIAN (contraseña: admin123)
-- Supervisores: ARACELI, LISBETH, YOVANI, WILMER (contraseña: admin123)
INSERT INTO usuarios (username, email, password, nombre, rol, activo) VALUES
('cristian', 'cristian@imbox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CRISTIAN', 'admin', 1),
('araceli', 'araceli@imbox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ARACELI', 'supervisor', 1),
('lisbeth', 'lisbeth@imbox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'LISBETH', 'supervisor', 1),
('yovani', 'yovani@imbox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'YOVANI', 'supervisor', 1),
('wilmer', 'wilmer@imbox.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'WILMER', 'supervisor', 1)
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre),
    rol = VALUES(rol),
    activo = VALUES(activo);

-- =====================================================
-- FIN DEL ESQUEMA UNIFICADO
-- =====================================================
