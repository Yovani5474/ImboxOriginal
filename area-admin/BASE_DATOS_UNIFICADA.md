# 🗄️ BASE DE DATOS UNIFICADA - SISTEMA IMBOX

## 📋 RESUMEN

Hemos unificado las 3 bases de datos independientes en una sola base de datos compartida llamada **`imbox_sistema_unificado`**.

---

## 🎯 ANTES vs DESPUÉS

### **ANTES (3 Bases de Datos Separadas)**

```
┌────────────────────────────────────────┐
│  c:\xampp\htdocs\1\                    │
│  Base de Datos: SQLite (local)        │
│  - Control de materiales               │
│  - Transferencias                      │
└────────────────────────────────────────┘

┌────────────────────────────────────────┐
│  c:\xampp\htdocs\2\                    │
│  Base de Datos: control_almacen        │
│  - Control de entrada prendas          │
│  - Transferencias                      │
│  - Catálogos                           │
└────────────────────────────────────────┘

┌────────────────────────────────────────┐
│  c:\xampp\htdocs\3\                    │
│  Base de Datos: admin_panel            │
│  - Clientes                            │
│  - Proveedores                         │
│  - Empleados                           │
│  - Deudas                              │
└────────────────────────────────────────┘
```

### **DESPUÉS (1 Base de Datos Unificada)**

```
┌──────────────────────────────────────────────┐
│  IMBOX_SISTEMA_UNIFICADO (MySQL)             │
├──────────────────────────────────────────────┤
│                                              │
│  📂 MÓDULO 1: Usuarios y Autenticación       │
│  - usuarios                                  │
│  - sesiones                                  │
│                                              │
│  📂 MÓDULO 2: Catálogos                      │
│  - almacenes                                 │
│  - tipos_prenda                              │
│  - trabajadores                              │
│  - encargados_taller                         │
│  - recepcionistas                            │
│  - tipos_material                            │
│                                              │
│  📂 MÓDULO 3: Gestión Administrativa         │
│  - clientes                                  │
│  - proveedores                               │
│  - empleados                                 │
│  - deudas                                    │
│  - pagos_deudas                              │
│                                              │
│  📂 MÓDULO 4: Control Materiales (Almacén 1) │
│  - controles_entrada_materiales              │
│  - detalles_materiales                       │
│                                              │
│  📂 MÓDULO 5: Transferencias                 │
│  - transferencias                            │
│  - transferencia_detalles                    │
│                                              │
│  📂 MÓDULO 6: Control Prendas (Almacén 2)    │
│  - control_entrada                           │
│  - detalles_prenda                           │
│                                              │
│  📂 MÓDULO 7: Auditoría                      │
│  - registro_cambios                          │
│  - logs_sistema                              │
│                                              │
│  📂 MÓDULO 8: Estadísticas                   │
│  - estadisticas                              │
│  - estadisticas_transferencias               │
│                                              │
└──────────────────────────────────────────────┘

         ↑            ↑            ↑
         │            │            │
    ┌────┴────┐  ┌───┴────┐  ┌───┴────┐
    │ Almacén │  │ Almacén│  │  Admin │
    │    1    │  │    2   │  │  Panel │
    │(Corte)  │  │(Empaq) │  │  (3)   │
    └─────────┘  └────────┘  └────────┘
```

---

## 🚀 INSTALACIÓN DE LA BASE DE DATOS UNIFICADA

### **Opción 1: Instalador Automático** ⭐ RECOMENDADO

1. **Accede al instalador:**
   ```
   http://localhost/3/install.php
   ```

2. **Click en "Instalar Sistema Ahora"**

3. **Espera 5 segundos**

4. **¡Listo!** La base de datos `imbox_sistema_unificado` se habrá creado con:
   - ✅ Todas las tablas (8 módulos)
   - ✅ Relaciones entre tablas
   - ✅ Índices optimizados
   - ✅ Datos de ejemplo
   - ✅ Usuario administrador

---

### **Opción 2: Instalación Manual (phpMyAdmin)**

1. **Abrir phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Importar schema completo:**
   - Click en "Importar"
   - Seleccionar: `c:\xampp\htdocs\3\database\schema_unificado.sql`
   - Click en "Continuar"

3. **Verificar:**
   - Base de datos: `imbox_sistema_unificado`
   - Tablas: Deben aparecer todas las tablas

---

## 📊 ESTRUCTURA DE LA BASE DE DATOS

### **Tablas por Módulo**

| Módulo | Tablas | Descripción |
|--------|--------|-------------|
| **1. Usuarios** | `usuarios`, `sesiones` | Autenticación y sesiones |
| **2. Catálogos** | `almacenes`, `tipos_prenda`, `trabajadores`, etc. | Datos maestros |
| **3. Admin** | `clientes`, `proveedores`, `empleados`, `deudas` | Gestión administrativa |
| **4. Materiales** | `controles_entrada_materiales`, `detalles_materiales` | Almacén 1 (Corte) |
| **5. Transferencias** | `transferencias`, `transferencia_detalles` | Entre almacenes |
| **6. Prendas** | `control_entrada`, `detalles_prenda` | Almacén 2 (Empaque) |
| **7. Auditoría** | `registro_cambios`, `logs_sistema` | Trazabilidad |
| **8. Estadísticas** | `estadisticas`, `estadisticas_transferencias` | Reportes |

---

## 🔗 CONFIGURACIÓN EN CADA CARPETA

### **Carpeta 1 (Almacén Corte)**

**Archivo:** `c:\xampp\htdocs\1\config_unificado.php`

```php
define('DB_NAME', 'imbox_sistema_unificado');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
```

**Uso:**
```php
require_once 'config_unificado.php';
$db = getDB();
```

---

### **Carpeta 2 (Almacén Empaque)**

**Archivo:** `c:\xampp\htdocs\2\config\config.php`

```php
define('DB_NAME', 'imbox_sistema_unificado');
```

**Archivo:** `c:\xampp\htdocs\2\config\database.php`

```php
$db = new Database();
$conn = $db->getConnection();
```

---

### **Carpeta 3 (Admin Panel)**

**Archivo:** `c:\xampp\htdocs\3\config\database.php`

```php
define('DB_NAME', 'imbox_sistema_unificado');
```

**Uso:**
```php
require_once 'config/database.php';
$db = Database::getInstance()->getConnection();
```

---

## ✅ VENTAJAS DE LA UNIFICACIÓN

### **1. Datos Compartidos**
```
✅ Un solo registro de usuarios
✅ Un solo catálogo de trabajadores
✅ Un solo catálogo de tipos de prenda
✅ Un solo catálogo de proveedores
```

### **2. Integridad Referencial**
```
✅ Transferencias relacionadas con controles
✅ Deudas relacionadas con clientes/proveedores
✅ Todo vinculado con almacenes
```

### **3. Consultas Cruzadas**
```sql
-- Ejemplo: Ver transferencias con datos del cliente
SELECT 
    t.referencia,
    c.nombre as cliente_nombre,
    t.total_items,
    t.estado
FROM transferencias t
INNER JOIN clientes c ON t.cliente_id = c.id;
```

### **4. Reportes Unificados**
```
✅ Dashboard global con datos de todos los módulos
✅ Estadísticas consolidadas
✅ Reportes financieros integrados
```

### **5. Mantenimiento Simplificado**
```
✅ Un solo backup
✅ Una sola migración
✅ Una sola optimización
```

---

## 🔄 MIGRACIÓN DE DATOS ANTIGUOS

Si ya tenías datos en las bases de datos anteriores:

### **Desde SQLite (Carpeta 1)**

```bash
# Exportar SQLite a SQL
sqlite3 c:\xampp\htdocs\1\data\controles_entrada.db .dump > datos_almacen1.sql

# Importar a MySQL
mysql -u root imbox_sistema_unificado < datos_almacen1.sql
```

### **Desde control_almacen (Carpeta 2)**

```sql
-- En phpMyAdmin, exportar tablas
-- Luego importar a imbox_sistema_unificado
```

### **Desde admin_panel (Carpeta 3)**

```sql
-- Copiar datos entre bases
INSERT INTO imbox_sistema_unificado.clientes
SELECT * FROM admin_panel.clientes;

INSERT INTO imbox_sistema_unificado.proveedores
SELECT * FROM admin_panel.proveedores;

-- etc...
```

---

## 📝 CREDENCIALES DE ACCESO

Después de instalar la base de datos unificada:

```
┌─────────────────────────────────────┐
│  Usuario:    cristian@imbox.local   │
│  Username:   cristian               │
│  Contraseña: admin123               │
│  Rol:        Administrador          │
└─────────────────────────────────────┘

Supervisores (contraseña: admin123):
• araceli@imbox.local
• lisbeth@imbox.local
• yovani@imbox.local
• wilmer@imbox.local
```

**Valido para:**
- ✅ `http://localhost/1/` (Almacén 1)
- ✅ `http://localhost/2/` (Almacén 2)
- ✅ `http://localhost/3/` (Admin Panel)

---

## 🧪 VERIFICACIÓN

### **1. Verificar Conexión**

```php
<?php
require_once 'config_unificado.php';

try {
    $db = getDB();
    echo "✅ Conexión exitosa a: " . DB_NAME;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

### **2. Verificar Tablas**

```sql
-- En phpMyAdmin o MySQL CLI
USE imbox_sistema_unificado;
SHOW TABLES;
```

Deberías ver **28 tablas** aproximadamente.

### **3. Verificar Datos**

```sql
-- Usuario admin
SELECT * FROM usuarios WHERE email = 'admin@admin.com';

-- Almacenes
SELECT * FROM almacenes;

-- Clientes de ejemplo
SELECT * FROM clientes;
```

---

## 🆘 SOLUCIÓN DE PROBLEMAS

### **Error: "Unknown database 'imbox_sistema_unificado'"**

**Solución:**
```
1. Ejecuta install.php
   http://localhost/3/install.php

2. O crea manualmente:
   CREATE DATABASE imbox_sistema_unificado;
```

---

### **Error: "Table doesn't exist"**

**Solución:**
```
1. Importa el schema:
   Archivo: c:\xampp\htdocs\3\database\schema_unificado.sql

2. En phpMyAdmin:
   - Selecciona la base de datos
   - Click en "Importar"
   - Selecciona el archivo SQL
```

---

### **Conexión lenta**

**Solución:**
```sql
-- Optimizar tablas
OPTIMIZE TABLE usuarios, clientes, proveedores, transferencias;

-- Reconstruir índices
ANALYZE TABLE usuarios, clientes, proveedores, transferencias;
```

---

## 📊 DIAGRAMA DE RELACIONES

```
                 ┌──────────────┐
                 │   USUARIOS   │
                 └──────┬───────┘
                        │
        ┌───────────────┼───────────────┐
        │               │               │
   ┌────▼────┐    ┌────▼────┐    ┌────▼────┐
   │ALMACENES│    │CLIENTES │    │PROVEEDO.│
   └────┬────┘    └────┬────┘    └────┬────┘
        │              │              │
        │         ┌────▼────┐    ┌────▼────┐
        │         │ DEUDAS  │    │MATERIALES│
        │         └─────────┘    └─────────┘
        │
   ┌────▼──────────────┐
   │  TRANSFERENCIAS   │
   └────┬──────────────┘
        │
   ┌────▼──────────────┐
   │CONTROL_ENTRADA    │
   └────┬──────────────┘
        │
   ┌────▼──────────────┐
   │DETALLES_PRENDA    │
   └───────────────────┘
```

---

## 🎯 PRÓXIMOS PASOS

1. **Ejecutar instalador:**
   ```
   http://localhost/3/install.php
   ```

2. **Iniciar sesión:**
   ```
   Usuario: admin@admin.com
   Contraseña: admin123
   ```

3. **Verificar módulos:**
   - Almacén 1: `http://localhost/1/`
   - Almacén 2: `http://localhost/2/`
   - Admin Panel: `http://localhost/3/`

4. **Probar funcionalidad:**
   - Crear transferencias
   - Registrar deudas
   - Ver estadísticas

---

## 📞 SOPORTE

¿Problemas con la base de datos unificada?

- 📧 Email: soporte@imbox.com
- 📱 WhatsApp: +51 XXX XXX XXX
- 🌐 Documentación: Ver `README.md`

---

**✨ ¡Base de datos unificada lista para usar!**

**Sistema IMBOX Unificado v1.0.0**  
**© 2025 Todos los derechos reservados**
