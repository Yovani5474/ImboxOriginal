# 🏢 Sistema IMBOX - Gestión Integral

Sistema completo de gestión empresarial IMBOX dividido en 3 áreas operativas.

---

## 📂 Estructura del Proyecto

```
ImboxOriginal/
│
├── area-corte/          → Área de Corte (Carpeta 1)
├── area-empaque/        → Área de Empaque (Carpeta 2)
└── area-admin/          → Panel Administrativo (Carpeta 3)
```

---

## 🎯 Descripción de Áreas

### **1. Área de Corte** (`area-corte/`)
Sistema para gestión de materiales y transferencias desde el área de corte.

**Características:**
- ✅ Control de entrada de materiales
- ✅ Tabla tipo Excel editable
- ✅ Tabla de tallas por color (20 filas editables)
- ✅ Modales interactivos (Ver/Editar/Completar)
- ✅ Envío de transferencias a Empaque
- ✅ API REST completa
- ✅ Cálculos automáticos de totales

**Archivos principales:**
```
sistema_completo.php
transferencias_excel.php
api/controles.php
api/tabla_tallas.php
js/excel-table.js
```

### **2. Área de Empaque** (`area-empaque/`)
Sistema para recepción y procesamiento de transferencias desde el área de corte.

**Características:**
- ✅ Recepción de transferencias
- ✅ Procesamiento de prendas
- ✅ Gestión de trabajadores
- ✅ Tablero de distribución por tallas
- ✅ Historial y reportes
- ✅ Control de calidad

**Archivos principales:**
```
control_entrada_almacen2.php
transferencias_ui.php
trabajadores_ui.php
models/Transferencia.php
```

### **3. Panel Administrativo** (`area-admin/`)
Sistema administrativo central para gestión empresarial.

**Características:**
- ✅ Dashboard con estadísticas
- ✅ Gestión de clientes
- ✅ Gestión de proveedores
- ✅ Control de deudas y pagos
- ✅ Gestión de empleados
- ✅ Reportes financieros
- ✅ Configuración del sistema

**Archivos principales:**
```
dashboard.php
clientes.php
proveedores.php
deudas.php
empleados.php
estadisticas.php
```

---

## 🚀 Instalación

### **Requisitos:**
- PHP 8.2+
- SQLite o MySQL
- Apache/Nginx
- Extensiones: PDO, JSON, cURL

### **Paso 1: Clonar el repositorio**
```bash
git clone https://github.com/Yovani5474/ImboxOriginal.git
cd ImboxOriginal
```

### **Paso 2: Configurar cada área**

**Área de Corte:**
```bash
cd area-corte
cp config.example.php config.php
# Editar config.php con tus credenciales
```

**Área de Empaque:**
```bash
cd area-empaque
# Importar database/datos_iniciales.sql
```

**Panel Admin:**
```bash
cd area-admin
cp .env.example .env
# Importar database/schema_unificado.sql
```

---

## 🔄 Flujo de Trabajo

```
┌─────────────────────┐
│   ÁREA DE CORTE     │
│  (area-corte/)      │
│                     │
│  • Recibe material  │
│  • Control entrada  │
│  • Tabla de tallas  │
└──────────┬──────────┘
           │
           │ Envía transferencia
           ↓
┌─────────────────────┐
│  ÁREA DE EMPAQUE    │
│  (area-empaque/)    │
│                     │
│  • Recibe transfer. │
│  • Procesa prendas  │
│  • Asigna trabaj.   │
└──────────┬──────────┘
           │
           │ Reporta a
           ↓
┌─────────────────────┐
│  PANEL ADMIN        │
│  (area-admin/)      │
│                     │
│  • Dashboard        │
│  • Estadísticas     │
│  • Finanzas         │
└─────────────────────┘
```

---

## 🛠️ Tecnologías Utilizadas

| Tecnología | Versión | Uso |
|------------|---------|-----|
| PHP | 8.2+ | Backend |
| SQLite | 3.x | Base de datos (áreas 1 y 2) |
| MySQL | 5.7+ | Base de datos (área 3) |
| Bootstrap | 5.1.3 | Frontend |
| JavaScript | ES6+ | Interactividad |
| FontAwesome | 6.0 | Iconos |

---

## 📊 Estadísticas del Proyecto

```
Total de archivos:    602
Líneas de código:     19,289
Archivos PHP:         150+
Archivos JS:          20+
Archivos CSS:         15+
APIs REST:            12
```

---

## 🔐 Seguridad

- ✅ Autenticación por usuario
- ✅ Protección CSRF
- ✅ Sanitización de inputs
- ✅ Prepared statements (PDO)
- ✅ Validación de permisos
- ✅ Sesiones seguras

---

## 📱 Características del Sistema

### **Tablas Tipo Excel**
- Edición inline
- Navegación con teclado (Enter, Tab, Flechas)
- Guardado automático
- Feedback visual
- Cálculos automáticos

### **Tabla de Tallas por Color**
- 20 filas editables
- Tallas numéricas: 2, 4, 6, 8, 10, 12, 14, 16, 20
- Tallas letras: S, M, L, XL, XXL
- Totales automáticos por fila y columna
- Resumen de datos en tiempo real

### **Modales Interactivos**
- Ver: Visualización rápida
- Editar: Formularios completos
- Completar: Confirmación de estado

---

## 🎨 Diseño

- **Tema principal:** Naranja IMBOX (#FF8C00)
- **Diseño:** Moderno y profesional
- **Responsive:** Compatible con móviles
- **Animaciones:** Suaves y profesionales
- **UX:** Optimizada para productividad

---

## 📖 Documentación por Área

Cada área contiene su propia documentación detallada:

- **Área Corte:** `area-corte/EXCEL_INTEGRATION.md`
- **Área Empaque:** `area-empaque/README.md`
- **Panel Admin:** `area-admin/RESUMEN_SISTEMA.md`

---

## 👥 Colaboradores

- **Yovani5474** - Desarrollador Principal
- **Danny160511** - Colaborador

---

## 📝 Changelog

### **v6.6 (Noviembre 2025)**
- ✅ Sistema completo integrado
- ✅ Tabla de tallas por color implementada
- ✅ Modales interactivos en las 3 áreas
- ✅ API REST actualizada
- ✅ Componentes reutilizables
- ✅ Documentación completa

---

## 🔜 Próximas Mejoras

- [ ] Autenticación única (SSO)
- [ ] Dashboard consolidado
- [ ] API centralizada
- [ ] Reportes en PDF
- [ ] Notificaciones en tiempo real
- [ ] App móvil nativa

---

## 📞 Soporte

Para soporte técnico o consultas:
- Email: soporte@imbox.com
- Issues: [GitHub Issues](https://github.com/Yovani5474/ImboxOriginal/issues)

---

## 📄 Licencia

Este proyecto es propiedad de IMBOX. Todos los derechos reservados.

---

**Desarrollado con ❤️ por el equipo IMBOX** 🚀

**Versión:** 6.6  
**Última actualización:** Noviembre 3, 2025
