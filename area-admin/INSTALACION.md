# 🚀 Guía de Instalación Rápida - Panel IMBOX Admin

## ⚡ Instalación en 5 Minutos

### 📋 Requisitos Previos

- ✅ XAMPP instalado
- ✅ Apache y MySQL activos
- ✅ PHP 7.4 o superior
- ✅ Navegador web moderno

---

## 🔧 Paso 1: Base de Datos

### Crear la Base de Datos

1. **Abrir phpMyAdmin**
   - URL: `http://localhost/phpmyadmin`

2. **Crear nueva base de datos**
   ```sql
   CREATE DATABASE admin_imbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Seleccionar la base de datos**
   ```sql
   USE admin_imbox;
   ```

### Importar el Esquema

Ejecutar el script SQL (`database/schema.sql`):

```sql
-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    direccion TEXT,
    ruc VARCHAR(11),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de proveedores
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    direccion TEXT,
    ruc VARCHAR(11),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de empleados
CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    cargo VARCHAR(50),
    salario DECIMAL(10,2),
    fecha_ingreso DATE,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de deudas
CREATE TABLE deudas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('cliente', 'proveedor') NOT NULL,
    cliente_id INT NULL,
    proveedor_id INT NULL,
    descripcion TEXT,
    monto_total DECIMAL(10,2) NOT NULL,
    monto_pagado DECIMAL(10,2) DEFAULT 0,
    monto_pendiente DECIMAL(10,2) NOT NULL,
    fecha_vencimiento DATE,
    estado ENUM('pendiente', 'pagada', 'vencida') DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL
);

-- Tabla de pagos
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deuda_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50),
    comprobante VARCHAR(100),
    observaciones TEXT,
    fecha_pago DATE NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deuda_id) REFERENCES deudas(id) ON DELETE CASCADE
);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, password, role) 
VALUES ('Administrador', 'admin@admin.com', '$2y$10$[HASH_GENERADO_AUTOMATICAMENTE]', 'admin');
-- Contraseña: admin123
```

---

## ⚙️ Paso 2: Configuración

### Verificar Archivo de Configuración

Abrir `config/database.php` y ajustar si es necesario:

```php
<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private $host = 'localhost';      // ← Verificar
    private $database = 'admin_imbox'; // ← Verificar
    private $username = 'root';        // ← Cambiar si es necesario
    private $password = '';            // ← Cambiar si es necesario
    
    // ... resto del código
}
```

---

## 🌐 Paso 3: Acceder al Sistema

### URL de Acceso

```
http://localhost/3/
```

### Credenciales Por Defecto

```
Usuario: admin@admin.com
Contraseña: admin123
```

⚠️ **IMPORTANTE**: Cambiar la contraseña después del primer inicio de sesión.

---

## ✅ Verificación de Instalación

### Checklist Post-Instalación

- [ ] Base de datos creada
- [ ] Tablas importadas correctamente
- [ ] Apache y MySQL activos
- [ ] Página de login carga correctamente
- [ ] Puedes iniciar sesión
- [ ] Dashboard muestra las 6 tarjetas
- [ ] Reloj analógico funciona
- [ ] Módulos accesibles (Clientes, Proveedores, etc.)

### Solución de Problemas Comunes

#### ❌ Error: "Call to undefined function getUserName()"

**Solución**: Verificar que `config/auth.php` esté incluido correctamente.

```php
require_once 'config/auth.php';
```

#### ❌ Error: "Connection failed"

**Solución**: 
1. Verificar que MySQL esté activo en XAMPP
2. Revisar credenciales en `config/database.php`
3. Confirmar que la base de datos existe

#### ❌ Estilos no se cargan

**Solución**:
1. Verificar ruta de `css/style.css`
2. Limpiar caché del navegador (Ctrl + F5)
3. Revisar consola del navegador (F12)

---

## 📊 Paso 4: Datos de Prueba (Opcional)

### Insertar Datos de Ejemplo

```sql
-- Clientes de prueba
INSERT INTO clientes (nombre, email, telefono, ruc) VALUES
('Empresa ABC', 'abc@empresa.com', '987654321', '20123456789'),
('Corporación XYZ', 'xyz@corp.com', '987654322', '20987654321'),
('Distribuidora 123', 'ventas@dist123.com', '987654323', '20111222333');

-- Proveedores de prueba
INSERT INTO proveedores (nombre, email, telefono, ruc) VALUES
('Proveedor A', 'contacto@proveedora.com', '987654324', '20444555666'),
('Suministros B', 'info@suministrosb.com', '987654325', '20777888999');

-- Empleados de prueba
INSERT INTO empleados (nombre, email, telefono, cargo, salario, fecha_ingreso) VALUES
('Juan Pérez', 'juan@imbox.com', '987654326', 'Gerente', 3500.00, '2024-01-15'),
('María García', 'maria@imbox.com', '987654327', 'Contador', 2800.00, '2024-02-01'),
('Carlos López', 'carlos@imbox.com', '987654328', 'Vendedor', 2200.00, '2024-03-10');

-- Deudas de prueba
INSERT INTO deudas (tipo, cliente_id, descripcion, monto_total, monto_pagado, monto_pendiente, fecha_vencimiento, estado) VALUES
('cliente', 1, 'Factura #001 - Productos varios', 5000.00, 2000.00, 3000.00, '2024-12-31', 'pendiente'),
('cliente', 2, 'Factura #002 - Servicios', 8000.00, 0.00, 8000.00, '2024-11-30', 'pendiente'),
('proveedor', NULL, 'Compra de insumos', 3500.00, 1500.00, 2000.00, '2024-12-15', 'pendiente');
```

---

## 🎯 Paso 5: Primeros Pasos

### Explorar el Dashboard

1. **Reloj Analógico** ⏰
   - Muestra la hora actual en tiempo real
   
2. **Módulo de Deudas** 💰
   - Ver resumen de cuentas por cobrar/pagar
   - Registrar nuevas deudas
   - Gestionar pagos

3. **Módulo de Estadísticas** 📊
   - Gráficos interactivos
   - Rankings y reportes
   - Análisis financiero

4. **Gestión de Clientes** 👥
   - Agregar nuevos clientes
   - Editar información
   - Ver historial

5. **Gestión de Proveedores** 📦
   - Catálogo de proveedores
   - Control de compras
   
6. **Gestión de Empleados** 👨‍💼
   - Registro de personal
   - Control laboral

### Personalización Inicial

1. **Cambiar Contraseña**
   ```sql
   UPDATE usuarios 
   SET password = '$2y$10$TU_HASH_AQUI' 
   WHERE email = 'admin@imbox.com';
   ```

2. **Agregar Más Usuarios** (opcional)
   - Ir al módulo de administración
   - Crear usuarios con diferentes roles

3. **Configurar Logo**
   - Reemplazar el SVG en el navbar
   - Personalizar colores si es necesario

---

## 🎨 Personalización Avanzada

### Cambiar Colores del Tema

Editar `css/style.css`:

```css
:root {
    --primary-color: #FF8C00;    /* Tu color */
    --primary-dark: #E67E00;     /* Versión oscura */
    --primary-light: #FFB84D;    /* Versión clara */
}
```

### Agregar Nuevo Módulo

1. Crear archivo PHP (ej: `modulo_nuevo.php`)
2. Copiar estructura de módulo existente
3. Agregar enlace en `index.php`
4. Crear API en carpeta `api/`
5. Agregar scripts JS en `js/`

---

## 📱 Acceso Móvil

El sistema es completamente responsive:

- ✅ Diseño adaptable
- ✅ Menú hamburguesa en móviles
- ✅ Tarjetas optimizadas
- ✅ Tablas con scroll horizontal

---

## 🔒 Seguridad

### Recomendaciones

1. **Cambiar contraseñas por defecto**
2. **Usar HTTPS en producción**
3. **Actualizar PHP regularmente**
4. **Hacer backups de la BD**
5. **Revisar logs de acceso**

---

## 📞 Soporte

¿Problemas con la instalación?

- 📧 Email: soporte@imbox.com
- 📱 WhatsApp: +51 XXX XXX XXX
- 🌐 Web: www.imbox.com

---

## ✨ ¡Listo!

Tu Panel de Administrador IMBOX está instalado y funcionando.

**Disfruta de todas las funcionalidades premium del sistema** 🚀

---

**Desarrollado con ❤️ por IMBOX**  
**Versión 1.0.0**
