# 📦 Sistema IMBOX - Estructura del Repositorio

Este repositorio contiene el **Sistema Completo IMBOX** dividido en 3 áreas independientes.

---

## 🌳 **Estructura de Branches**

```
Yovani5474/imbox
│
├── main                    → Carpeta 1: Área de Corte
├── carpeta-2-empaque      → Carpeta 2: Área de Empaque  
└── carpeta-3-admin        → Carpeta 3: Panel Administrativo
```

---

## 📂 **Contenido de cada Branch**

### **1. Main (Carpeta 1 - Área de Corte)**
```
Sistema completo v6.6
├── sistema_completo.php          → Vista unificada completa
├── transferencias_excel.php      → Tabla tipo Excel
├── api/controles.php             → API REST
├── api/tabla_tallas.php          → Endpoint tabla de tallas
├── js/excel-table.js             → Edición inline
├── includes/tabla_tallas_color.php → Componente reutilizable
└── config.php                    → Configuración BD
```

**Funcionalidades:**
- ✅ Control de entrada de materiales
- ✅ Tabla tipo Excel editable
- ✅ Envío de transferencias a Empaque
- ✅ Tabla de tallas por color (20 filas)
- ✅ Modales interactivos (Ver/Editar/Completar)
- ✅ Cálculos automáticos de totales
- ✅ API REST completa

### **2. Branch: carpeta-2-empaque (Área de Empaque)**
```
Sistema Empaque
├── control_entrada_almacen2.php  → Recepción de transferencias
├── transferencias_ui.php         → Gestión de transferencias
├── trabajadores_ui.php           → Gestión de trabajadores
├── models/                       → Modelos de datos
└── database/                     → Esquema BD
```

**Funcionalidades:**
- ✅ Recepción de transferencias desde Corte
- ✅ Procesamiento de prendas
- ✅ Gestión de trabajadores
- ✅ Historial y reportes
- ✅ Tablero de distribución por tallas

### **3. Branch: carpeta-3-admin (Panel Administrativo)**
```
Panel Admin
├── dashboard.php                 → Dashboard principal
├── clientes.php                  → Gestión de clientes
├── proveedores.php               → Gestión de proveedores
├── deudas.php                    → Control de deudas
├── empleados.php                 → Gestión de empleados
├── estadisticas.php              → Estadísticas generales
└── api/                          → APIs REST
```

**Funcionalidades:**
- ✅ Dashboard con estadísticas
- ✅ Gestión de clientes
- ✅ Gestión de proveedores
- ✅ Control de deudas y pagos
- ✅ Gestión de empleados
- ✅ Reportes financieros
- ✅ Configuración del sistema

---

## 🚀 **Cómo Usar**

### **Clonar el repositorio completo:**
```bash
git clone https://github.com/Yovani5474/imbox.git
cd imbox
```

### **Acceder a cada área:**

**Área de Corte (Main):**
```bash
git checkout main
# Ya estás en la rama principal
```

**Área de Empaque:**
```bash
git checkout carpeta-2-empaque
```

**Panel Administrativo:**
```bash
git checkout carpeta-3-admin
```

---

## 🔄 **Flujo de Trabajo**

```
┌─────────────────┐
│  Área de Corte  │
│   (Main Branch) │
└────────┬────────┘
         │
         │ Envía transferencias
         ↓
┌─────────────────┐
│ Área de Empaque │
│  (Branch: 2)    │
└────────┬────────┘
         │
         │ Reporta a
         ↓
┌─────────────────┐
│  Panel Admin    │
│  (Branch: 3)    │
└─────────────────┘
```

---

## 📊 **Estadísticas del Repositorio**

### **Carpeta 1 (Main):**
- 25 archivos
- 7,585 líneas de código
- PHP, JavaScript, CSS

### **Carpeta 2 (Empaque):**
- 103 archivos
- 2,068 inserciones
- PHP, TypeScript, SQL

### **Carpeta 3 (Admin):**
- 52 archivos
- 9,636 líneas de código
- PHP, JavaScript, SQL

---

## 🔐 **Configuración**

Cada carpeta tiene su propia configuración de base de datos:

**Carpeta 1:**
```php
// config.php
define('DB_PATH', __DIR__ . '/database/almacen.db');
```

**Carpeta 2:**
```php
// config/config.php
$db_file = __DIR__ . '/../database/almacen2.db';
```

**Carpeta 3:**
```php
// config/database.php
$db_host = 'localhost';
$db_name = 'imbox_admin';
```

---

## 📝 **Instalación**

### **1. Área de Corte (Main):**
```bash
git checkout main
cp config.example.php config.php
# Configurar base de datos
```

### **2. Área de Empaque:**
```bash
git checkout carpeta-2-empaque
# Importar database/datos_iniciales.sql
```

### **3. Panel Admin:**
```bash
git checkout carpeta-3-admin
cp .env.example .env
# Importar database/schema_unificado.sql
```

---

## 👥 **Colaboradores**

- **Yovani5474** (Owner)
- **Danny160511** (Colaborador)

---

## 🛠️ **Tecnologías**

- **Backend:** PHP 8.2+
- **Base de Datos:** SQLite / MySQL
- **Frontend:** Bootstrap 5, JavaScript
- **APIs:** REST con JSON
- **Control de versiones:** Git

---

## 📖 **Documentación**

Cada branch contiene su propia documentación:

- `main` → `EXCEL_INTEGRATION.md`
- `carpeta-2-empaque` → `README.md`
- `carpeta-3-admin` → `RESUMEN_SISTEMA.md`

---

## ✨ **Versión Actual**

- **Sistema Completo:** v6.6
- **Última actualización:** 3 de Noviembre, 2025
- **Commit:** Sistema completo integrado con tablas Excel y tablero de tallas

---

## 🎯 **Próximos Pasos**

1. Integrar las 3 áreas en un monorepo unificado
2. Crear API centralizada
3. Implementar autenticación única (SSO)
4. Dashboard unificado
5. Reportes consolidados

---

**Desarrollado por el equipo IMBOX** 🚀
