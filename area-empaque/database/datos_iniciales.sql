-- =====================================================
-- DATOS INICIALES Y CATÁLOGOS COMPLETOS - BASE DE DATOS
-- Sistema de Control de Almacén IMBOX
-- Incluye: configuración inicial + usuarios reales + catálogos
-- Fecha: Octubre 2025
-- =====================================================

USE control_almacen;

-- =====================================================
-- 1. ALMACENES
-- =====================================================

INSERT IGNORE INTO almacenes (clave, nombre, ubicacion, tipo) VALUES
    ('ALM1', 'Almacén Corte', 'Planta A - Area de Corte', 'corte'),
    ('ALM2', 'Almacén Empaque', 'Planta B - Area de Empaque', 'empaque'),
    ('BOD1', 'Bodega General', 'Edificio Principal', 'bodega');

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
-- 4. RECEPCIONISTAS
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
-- 6. PROVEEDORES
-- =====================================================

INSERT IGNORE INTO proveedores (codigo, nombre, telefono, email, ciudad, pais) VALUES
    ('PROV001', 'Textiles del Norte S.A.', '555-2001', 'ventas@textilesnorte.com', 'Monterrey', 'México'),
    ('PROV002', 'Telas Importadas CDMX', '555-2002', 'contacto@telasimport.com', 'Ciudad de México', 'México'),
    ('PROV003', 'Insumos Textiles Internacional', '555-2003', 'info@insumostextil.com', 'Guadalajara', 'México');

-- =====================================================
-- 7. TIPOS DE MATERIAL
-- =====================================================

INSERT IGNORE INTO tipos_material (codigo, nombre, categoria, unidad_medida) VALUES
    ('TEL-ALG', 'Tela de Algodón', 'Telas', 'metros'),
    ('TEL-POL', 'Tela de Poliéster', 'Telas', 'metros'),
    ('TEL-MIX', 'Tela Mixta', 'Telas', 'metros'),
    ('HIL-001', 'Hilo de Coser', 'Insumos', 'piezas'),
    ('BOT-001', 'Botones', 'Insumos', 'piezas'),
    ('CRE-001', 'Cremalleras', 'Insumos', 'piezas');

-- =====================================================
-- 8. COLORES (OPCIONAL - Descomentar si existe tabla)
-- =====================================================

-- CREATE TABLE IF NOT EXISTS colores (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     codigo VARCHAR(50) UNIQUE,
--     nombre VARCHAR(100) NOT NULL,
--     hex VARCHAR(7),
--     activo TINYINT(1) DEFAULT 1
-- ) ENGINE=InnoDB;

-- INSERT INTO colores (codigo, nombre, hex, activo) VALUES
-- ('COL-001', 'Negro', '#000000', 1),
-- ('COL-002', 'Blanco', '#FFFFFF', 1),
-- ('COL-003', 'Gris', '#808080', 1),
-- ('COL-004', 'Azul Marino', '#000080', 1),
-- ('COL-005', 'Verde', '#008000', 1),
-- ('COL-006', 'Rojo', '#FF0000', 1),
-- ('COL-007', 'Naranja IMBOX', '#FF8C00', 1),
-- ('COL-008', 'Beige', '#F5F5DC', 1),
-- ('COL-009', 'Marrón', '#A52A2A', 1),
-- ('COL-010', 'Amarillo', '#FFFF00', 1)
-- ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), hex = VALUES(hex);

-- =====================================================
-- 9. USUARIOS DEL SISTEMA
-- =====================================================

-- Usuario administrador: CRISTIAN (contraseña: admin123)
-- Supervisores por defecto (contraseña: admin123)
INSERT INTO usuarios (username, password_hash, nombre_completo, email, rol, activo) VALUES
    ('cristian', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CRISTIAN', 'cristian@imbox.local', 'admin', 1),
    ('araceli', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ARACELI', 'araceli@imbox.local', 'supervisor', 1),
    ('lisbeth', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'LISBETH', 'lisbeth@imbox.local', 'supervisor', 1),
    ('yovani', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'YOVANI', 'yovani@imbox.local', 'supervisor', 1),
    ('wilDer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'WILDER', 'wilDer@imbox.local', 'supervisor', 1)
ON DUPLICATE KEY UPDATE 
    nombre_completo = VALUES(nombre_completo),
    rol = VALUES(rol),
    activo = VALUES(activo);

-- =====================================================
-- VERIFICACIÓN DE DATOS INSERTADOS
-- =====================================================

SELECT '✓ Datos iniciales y catálogos insertados correctamente' AS Resultado;

SELECT 'Trabajadores IMBOX:' AS Info;
SELECT COUNT(*) AS total FROM trabajadores WHERE activo = 1;

SELECT 'Encargados de Almacén:' AS Info;
SELECT COUNT(*) AS total FROM encargados_taller WHERE activo = 1;

SELECT 'Recepcionistas:' AS Info;
SELECT COUNT(*) AS total FROM recepcionistas WHERE activo = 1;

SELECT 'Tipos de Prenda:' AS Info;
SELECT COUNT(*) AS total FROM tipos_prenda WHERE activo = 1;

SELECT 'Usuarios del Sistema:' AS Info;
SELECT COUNT(*) AS total FROM usuarios WHERE activo = 1;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
