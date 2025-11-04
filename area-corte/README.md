# 📦 Almacén 1 - Sistema de Corte

Sistema independiente para el Almacén 1 (Corte) con datos propios y API para integración con Almacén 2 (Empaque).

## 🧵 Materiales y Productos

### Material Principal
**Franela**: Tela de algodón combinada con poliéster

### Tipos de Prendas Fabricadas
- **Poleras** (básicas, con y sin capucha)
- **Joggers**
- **Buzos** (conjuntos de sudadera y pantalón)
- **Polos** tipo sport y casual
- **Chompas o sudaderas** de algodón-poliéster
- **Shorts deportivos y casuales** tipo jogger

## 🏗️ Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│                      ALMACÉN 1 (CORTE)                      │
│  ┌────────────────────────────────────────────────────┐     │
│  │  📁 Datos Propios (SQLite)                         │     │
│  │  - Controles de entrada                            │     │
│  │  - Base de datos local                             │     │
│  └────────────────────────────────────────────────────┘     │
│                            ↕                                │
│  ┌────────────────────────────────────────────────────┐     │
│  │  🔌 API con Autenticación                          │     │
│  │  - Solo acceso con API Key                         │     │
│  │  - Almacén 2 puede LEER datos                      │     │
│  └────────────────────────────────────────────────────┘     │
│                            ↓                                │
│  ┌────────────────────────────────────────────────────┐     │
│  │  📤 Envía Transferencias                           │     │
│  │  - A Almacén 2 vía API                             │     │
│  │  - NO puede acceder a datos del Almacén 2          │     │
│  └────────────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────┐
│                      ALMACÉN 2 (EMPAQUE)                    │
│  - Puede LEER controles de entrada del Almacén 1           │
│  - Recibe transferencias del Almacén 1                     │
│  - Tiene sus propios datos independientes                  │
└─────────────────────────────────────────────────────────────┘
```

## 📁 Estructura de Archivos

```
/1/
├── index.php                    # 📊 Dashboard principal (Página de inicio)
├── transferencias.php           # Sistema de transferencias (antes index.php)
├── control_entrada.php          # Gestión de controles de entrada
├── ver_transferencias.php       # Vista de transferencias enviadas
├── config.php                   # Configuración y base de datos
├── /api/
│   └── controles_entrada.php    # API REST (requiere API Key)
├── /data/
│   └── controles_entrada.db     # Base de datos SQLite local
├── /css/
│   ├── theme-orange.css         # Tema visual IMBOX (colores naranja)
│   └── almacen1.css             # Estilos específicos Almacén 1
└── /img/
    └── logo.jpg                 # Logo del sistema
```

## 🔐 Seguridad y Permisos

### ✅ Almacén 1 PUEDE:
- ✓ Crear y gestionar sus propios controles de entrada
- ✓ Enviar transferencias al Almacén 2
- ✓ Acceder a lista de trabajadores del Almacén 2 (solo lectura para autocompletar)

### ❌ Almacén 1 NO PUEDE:
- ✗ Acceder a la base de datos del Almacén 2
- ✗ Modificar datos del Almacén 2
- ✗ Ver información interna del Almacén 2

### ✅ Almacén 2 PUEDE:
- ✓ Leer controles de entrada del Almacén 1 (con API Key)
- ✓ Recibir transferencias del Almacén 1
- ✓ Vincular transferencias a controles de entrada

## 🔌 API - Controles de Entrada

### Endpoint
```
GET/POST http://localhost/1/api/controles_entrada.php
```

### Autenticación
```http
X-API-KEY: almacen1_secret_key_2024
```

### Ejemplos de Uso

#### Listar todos los controles
```bash
curl -H "X-API-KEY: almacen1_secret_key_2024" \
     http://localhost/1/api/controles_entrada.php
```

#### Obtener un control específico
```bash
curl -H "X-API-KEY: almacen1_secret_key_2024" \
     http://localhost/1/api/controles_entrada.php?id=1
```

#### Crear nuevo control
```bash
curl -X POST \
     -H "X-API-KEY: almacen1_secret_key_2024" \
     -H "Content-Type: application/json" \
     -d '{
       "referencia": "CE-20251013-001",
       "fecha_entrada": "2025-10-13",
       "proveedor": "Proveedor XYZ",
       "total_rollos": 50,
       "total_metros": 500.5
     }' \
     http://localhost/1/api/controles_entrada.php
```

## 🚀 Uso del Sistema

### 1. Panel de Control (Página Principal) ⭐
```
http://localhost/1/
```
**Panel de control completo con:**
- 📊 Estadísticas en tiempo real
  - Total de controles de entrada
  - Controles pendientes y completados
  - Total de rollos y metros procesados
  - Actividad en las últimas 24 horas
- 📋 Últimos controles de entrada (5 más recientes)
- 🏭 Top 5 proveedores por volumen
- ⚡ Acciones rápidas (botones de navegación)
- 🕐 Reloj en tiempo real
- 🔄 Auto-actualización cada 5 minutos

**Características del Dashboard:**
- Diseño moderno con **tema naranja IMBOX**
- Tarjetas de estadísticas con iconos
- Navegación rápida a todas las secciones
- Vista general del estado del almacén
- Responsive (móvil, tablet, desktop)

### 2. Control de Entrada
```
http://localhost/1/control_entrada.php
```
- Registra materiales recibidos
- Genera referencia única (CE-YYYYMMDD-HHMMSS)
- Almacena en base de datos local SQLite

### 3. Transferencias a Empaque
```
http://localhost/1/transferencias.php
```
- Crea transferencias de corte a empaque
- Vincula con control de entrada (opcional)
- Envía a Almacén 2 vía API
- Formulario con animaciones premium

### 4. Ver Transferencias
```
http://localhost/1/ver_transferencias.php
```
- Lista de transferencias enviadas a Empaque
- Vista de solo lectura
- Consulta el API del Almacén 2
- Filtros y búsqueda

## 📊 Base de Datos Local

### Tabla: `controles_entrada`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INTEGER | ID único |
| referencia | TEXT | Referencia única (ej: CE-20251013-001) |
| fecha_entrada | TEXT | Fecha de entrada de materiales |
| proveedor | TEXT | Nombre del proveedor |
| orden_compra | TEXT | Número de orden de compra |
| total_rollos | INTEGER | Total de rollos |
| total_metros | REAL | Total de metros |
| observaciones | TEXT | Notas adicionales |
| estado | TEXT | pendiente/completado |
| usuario_creacion | TEXT | Usuario que creó el registro |
| fecha_creacion | TEXT | Timestamp de creación |

### Tabla: `control_detalles`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INTEGER | ID único |
| control_entrada_id | INTEGER | FK a controles_entrada |
| tipo_tela | TEXT | Tipo de tela |
| color | TEXT | Color |
| cantidad_rollos | INTEGER | Cantidad de rollos |
| metros | REAL | Metros |

## 🎨 Colores del Sistema (Imbox Original)

```css
--imbox-orange: #FF8C00   /* Naranja principal */
--imbox-dark: #2C2C2C     /* Gris oscuro */
--imbox-light-gray: #f5f5f5 /* Fondo claro */
```

## 🔧 Configuración

### Cambiar API Key
Edita `config.php`:
```php
define('API_KEY', 'tu_nueva_clave_secreta');
```

### Cambiar URL de Almacén 2
Edita `index.php`:
```php
define('TARGET_URL', 'http://tu-servidor/2/api/transferencias.php');
```

## 📝 Flujo de Trabajo

### Escenario Típico:

1. **Recepción de Materiales** (Almacén 1)
   - Acceder a `control_entrada.php`
   - Registrar entrada de telas/materiales
   - Sistema genera referencia única

2. **Proceso de Corte** (Almacén 1)
   - Cortar prendas según especificaciones
   - Preparar para envío a empaque

3. **Transferencia a Empaque** (Almacén 1 → Almacén 2)
   - Acceder a `index.php`
   - Seleccionar control de entrada (opcional)
   - Ingresar cantidad de prendas
   - Enviar a Almacén 2

4. **Consulta desde Empaque** (Almacén 2)
   - Almacén 2 consulta API de Almacén 1
   - Obtiene datos de controles de entrada
   - Vincula transferencias recibidas

## 🔍 Integración con Almacén 2

El Almacén 2 puede integrar esta API para:

### En `control_entrada_almacen2.php`:
```php
// Obtener controles de entrada del Almacén 1
$ch = curl_init('http://localhost/1/api/controles_entrada.php');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-KEY: almacen1_secret_key_2024'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$controles_almacen1 = json_decode($response, true);
```

### Mostrar en Select:
```html
<select name="control_entrada_origen">
  <option value="">-- Controles del Almacén 1 --</option>
  <?php foreach ($controles_almacen1['data'] as $control): ?>
    <option value="<?= $control['id'] ?>">
      <?= $control['referencia'] ?> - <?= $control['proveedor'] ?>
    </option>
  <?php endforeach; ?>
</select>
```

## ⚠️ Notas Importantes

- **Base de datos independientes**: Cada almacén mantiene sus propios datos
- **API con autenticación**: Solo acceso autorizado desde Almacén 2
- **Flujo unidireccional de escritura**: Almacén 1 solo ENVÍA a Almacén 2
- **Lectura bidireccional**: Ambos pueden LEER datos del otro (con autenticación)

## 🛠️ Solución de Problemas

### Error: "Acceso denegado"
- Verificar que el API Key sea correcto
- Verificar header `X-API-KEY` en la petición

### Error: "Base de datos no encontrada"
- El sistema crea automáticamente la BD al acceder
- Verificar permisos de escritura en carpeta `/data/`

### No aparecen controles de entrada
- Crear al menos un control desde `control_entrada.php`
- Verificar que la base de datos tenga datos

## 📞 Soporte

Para más información consulta:
- Configuración: `config.php`
- API Documentation: Ver endpoints en `/api/`
- Almacén 2: `c:\xampp\htdocs\2\`

---

## 👥 Usuarios del Sistema

### Costureros/Textiles
1. CARLOS
2. WILIAN
3. CLEMENTE
4. ERIKA
5. LUZ
6. LIZ
7. ELVA

### Encargados de Almacén
1. **ARACELI** - Almacén 2 (Empaque)
2. **LISBETH** - Almacén 2 (Empaque)
3. **YOVANI** - Almacén 2 (Empaque)
4. **WILDER** - Almacén 1 (Corte)

### Administrador
- **CRISTIAN** - Admin del sistema

## 👕 Catálogo de Prendas

1. POLERA CLASICA CERRADO
2. POLERA CLASICA CIERRE
3. POLERA CUELLO REDONDO
4. POLERA CLASICA - ESTAMPADO
5. POLERA CLASICA - REVOLT
6. BUSO UNISEX
7. BUSO EXTRAOVERSIZE
8. POLERA BALACLAVA ADULTO
9. POLERA CUELLO REDONDO IMBOX

## 🎨 Colores y Códigos

Los colores y códigos se registran según el catálogo de producción de IMBOX.
Ver base de datos para el listado completo de colores disponibles.

---

**Versión**: 1.0  
**Fecha**: Octubre 2025  
**Estado**: ✅ Producción Ready
