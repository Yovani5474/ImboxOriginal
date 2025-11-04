# 🎯 Panel de Administrador IMBOX

Sistema de gestión administrativo completo con diseño profesional y tema naranja corporativo.

## 📋 Características Principales

### ✨ Módulos Principales

1. **⏰ Reloj Analógico**
   - Reloj en tiempo real con manecillas
   - Hora digital
   - Fecha actual
   - Diseño elegante y responsive

2. **💰 Gestión de Deudas**
   - Control de cuentas por cobrar (clientes)
   - Control de cuentas por pagar (proveedores)
   - Estados: Pendiente, Pagada, Vencida
   - Historial de pagos
   - Recordatorios automáticos

3. **📊 Estadísticas y Reportes**
   - Gráficos de pastel (estado de deudas)
   - Gráficos de barras (deudas por mes)
   - Top 5 clientes con mayor deuda
   - Top 5 proveedores a pagar
   - Resumen financiero completo
   - Métricas en tiempo real

4. **👥 Gestión de Clientes**
   - Registro completo de clientes
   - Información de contacto
   - Historial de transacciones
   - Búsqueda y filtros avanzados
   - Exportación de datos

5. **📦 Gestión de Proveedores**
   - Catálogo de proveedores
   - Datos de contacto
   - Historial de compras
   - Control de pagos
   - Evaluación de proveedores

6. **👨‍💼 Gestión de Empleados**
   - Registro de personal
   - Información laboral
   - Control de asistencia
   - Gestión de roles y permisos
   - Historial de empleados

## 🎨 Diseño y Colores

### Tema Naranja IMBOX

```css
--primary-color: #FF8C00       /* Naranja principal */
--primary-dark: #E67E00        /* Naranja oscuro */
--primary-light: #FFB84D       /* Naranja claro */
--secondary-color: #FFA500     /* Naranja secundario */
```

### Gradientes

- **Principal**: `linear-gradient(135deg, #FF8C00 0%, #FFB84D 50%, #FFA500 100%)`
- **Tarjetas**: `linear-gradient(135deg, #FF8C00 0%, #FFA500 100%)`
- **Header**: `linear-gradient(90deg, #FF8C00 0%, #FFB84D 100%)`

## 🚀 Tecnologías Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL/MariaDB
- **Framework CSS**: Bootstrap 5.3
- **Gráficos**: Chart.js
- **Iconos**: Font Awesome 6 + SVG personalizados

## 📁 Estructura del Proyecto

```
c:\xampp\htdocs\3\
├── index.php              # Dashboard principal
├── estadisticas.php       # Módulo de estadísticas
├── clientes.php           # Gestión de clientes
├── proveedores.php        # Gestión de proveedores
├── empleados.php          # Gestión de empleados
├── deudas.php             # Gestión de deudas
├── login.php              # Página de inicio de sesión
├── logout.php             # Cerrar sesión
├── config/
│   ├── auth.php           # Autenticación
│   └── database.php       # Conexión a BD
├── css/
│   └── style.css          # Estilos con tema naranja
├── js/
│   ├── clock.js           # Funcionalidad del reloj
│   ├── clientes.js        # Scripts de clientes
│   ├── deudas.js          # Scripts de deudas
│   ├── empleados.js       # Scripts de empleados
│   └── proveedores.js     # Scripts de proveedores
├── api/
│   ├── clientes.php       # API REST clientes
│   ├── deudas.php         # API REST deudas
│   ├── empleados.php      # API REST empleados
│   └── proveedores.php    # API REST proveedores
└── database/
    └── schema.sql         # Esquema de base de datos
```

## 🔧 Instalación

### Requisitos Previos

- XAMPP o servidor similar (Apache + MySQL + PHP)
- PHP 7.4 o superior
- MySQL 5.7 o superior

### Pasos de Instalación

1. **Clonar o copiar archivos**
   ```bash
   # Copiar a la carpeta de XAMPP
   cp -r 3 c:\xampp\htdocs\
   ```

2. **Crear base de datos**
   ```sql
   CREATE DATABASE admin_imbox;
   USE admin_imbox;
   ```

3. **Importar esquema**
   ```bash
   # Desde phpMyAdmin o línea de comandos
   mysql -u root -p admin_imbox < database/schema.sql
   ```

4. **Configurar conexión**
   - Editar `config/database.php`
   - Ajustar credenciales de BD

5. **Acceder al sistema**
   - URL: `http://localhost/3/`
   - Usuario: admin
   - Contraseña: (según configuración)

## 📊 Funcionalidades del Dashboard

### Tarjetas Interactivas

Cada módulo tiene una tarjeta en el dashboard con:
- ✅ Icono SVG personalizado
- ✅ Badge de verificación premium
- ✅ Contador o valor principal
- ✅ Descripción del módulo
- ✅ Animación de hover
- ✅ Enlace directo al módulo

### Estadísticas en Tiempo Real

El módulo de estadísticas muestra:
- 📈 Gráfico de pastel (estado de deudas)
- 📊 Gráfico de barras (histórico mensual)
- 💰 Total por cobrar/pagar
- 🏆 Rankings de clientes y proveedores
- 💵 Resumen financiero

## 🔐 Seguridad

- ✅ Sistema de autenticación
- ✅ Sesiones seguras
- ✅ Protección contra SQL Injection
- ✅ Validación de datos
- ✅ Control de acceso por roles
- ✅ Cierre de sesión automático

## 🎯 Características Avanzadas

### Gestión de Deudas

- **Estados**:
  - 🟡 Pendiente
  - 🟢 Pagada
  - 🔴 Vencida

- **Funciones**:
  - Registro de nuevas deudas
  - Pagos parciales o completos
  - Recordatorios automáticos
  - Historial completo
  - Filtros avanzados

### Reportes y Análisis

- Exportación a Excel/PDF
- Gráficos interactivos
- Filtros por fecha
- Búsqueda avanzada
- Comparativas mensuales

## 🎨 Personalización

### Cambiar Colores

Editar `css/style.css`:

```css
:root {
    --primary-color: #TU_COLOR;
    --primary-dark: #TU_COLOR_OSCURO;
    --primary-light: #TU_COLOR_CLARO;
}
```

### Agregar Módulos

1. Crear archivo PHP del módulo
2. Agregar API en carpeta `/api/`
3. Crear scripts JS en `/js/`
4. Actualizar menú en `index.php`

## 📝 Uso

### Dashboard Principal

1. **Inicio de Sesión**
   - Ingresar credenciales
   - El sistema redirige al dashboard

2. **Navegación**
   - Menú superior con todos los módulos
   - Tarjetas interactivas en el dashboard
   - Breadcrumbs para ubicación

3. **Gestión de Datos**
   - Botón "Agregar" en cada módulo
   - Formularios modales
   - Validación en tiempo real
   - Confirmación de acciones

### Módulo de Estadísticas

1. **Ver Gráficos**
   - Gráficos automáticos al cargar
   - Datos en tiempo real
   - Actualización dinámica

2. **Rankings**
   - Top 5 clientes con deuda
   - Top 5 proveedores a pagar
   - Ordenamiento automático

3. **Resumen Financiero**
   - Totales generales
   - Montos pendientes
   - Montos pagados

## 🐛 Solución de Problemas

### Error de Conexión a BD

```php
// Verificar en config/database.php
$host = 'localhost';
$dbname = 'admin_imbox';
$username = 'root';
$password = '';
```

### Gráficos No Se Muestran

1. Verificar que Chart.js esté cargado
2. Revisar consola del navegador
3. Confirmar que hay datos en la BD

### Estilos No Se Aplican

1. Limpiar caché del navegador
2. Verificar ruta de `style.css`
3. Revisar consola de errores

## 📞 Soporte

Para soporte o consultas:
- 📧 Email: soporte@imbox.com
- 🌐 Web: www.imbox.com
- 📱 WhatsApp: +51 XXX XXX XXX

## 📜 Licencia

Todos los derechos reservados © 2025 IMBOX

---

**Desarrollado con ❤️ usando tecnología web moderna**

**Versión**: 1.0.0  
**Última actualización**: 2025-11-02
