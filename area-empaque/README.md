# 📦 Sistema de Control de Almacén - IMBOX

## ⚡ Inicio Rápido

### 🏠 Desarrollo Local (XAMPP)

**¡IMPORTANTE! No puedes conectarte a InfinityFree desde tu PC.**

1. **Activar auto-detección:**
   ```bash
   copy config\database.auto.php config\database.php
   ```

2. **Iniciar XAMPP** (Apache + MySQL)

3. **Crear BD local:**
   - Abrir phpMyAdmin: `http://localhost/phpmyadmin`
   - Crear BD: `control_almacen`
   - Importar: `database/schema_tablas.sql`
   - Importar: `database/datos_iniciales.sql`

4. **Probar:** `http://localhost/2/test_infinityfree.php`

**Ver guía completa:** `INICIO_RAPIDO.md`

---

# Sistema de Control de Almacén - Transferencias

Sistema completo de control de entrada de almacén con gestión de transferencias entre almacenes, basado en el formulario de control de recepción.

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

## Características

- **Gestión de transferencias entre almacenes** (Corte → Empaque)
- **Control de tallas por prenda** (2, 4, 6, 8, 10, 12, 14, 16, XS, S, M, L, XL, XXL)
- **Asignación de costureros/trabajadores** en transferencias
- **Interfaz de recepción** que replica el formulario físico
- **Confirmación con registro de faltantes** y observaciones
- **API REST** para todas las operaciones
- **Base de datos MySQL** con relaciones y validaciones

## Flujo del Sistema

### Almacén 1 (Corte)
- Crea transferencias asignando costureros
- Envía prendas al Almacén 2 (Empaque)
- Estado: `pendiente` → `enviado`

### Almacén 2 (Empaque/Recepción)
- Recibe transferencias del Almacén 1
- Ve el trabajador asignado por el almacén de corte
- Confirma recepción registrando:
  - Fecha de recepción
  - Tipo de prenda (poleras, joggers, buzos, polos, chompas, shorts)
  - Detalles por tallas en tabla como el formulario físico
  - Observaciones y estado de entrega
- Estado: `enviado` → `recibido`

## Estructura del Proyecto

```
sistema-control-almacen/
├── config/
│   └── database.php          # Configuración de base de datos
├── database/
│   └── schema.sql            # Esquema de base de datos
├── models/
│   ├── ControlEntrada.php    # Modelo principal
│   ├── DetallePrenda.php     # Modelo de detalles
│   └── Catalogos.php         # Modelo de catálogos
├── api/
│   ├── control_entrada.php   # API de control de entrada
│   ├── detalles.php          # API de detalles
│   └── catalogos.php         # API de catálogos
├── css/
│   └── styles.css            # Estilos personalizados
├── js/
│   └── app.js                # JavaScript principal
├── index.php                 # Página principal
└── README.md
```

## Instalación y Configuración

### Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)

### Pasos de instalación

1. **Configuración automática**
   - Abrir `setup.php` en el navegador
   - O ejecutar: `php setup.php`
   - Esto creará automáticamente la base de datos, tablas y datos iniciales

2. **Verificar instalación**
   - Abrir `verificar_sistema.php` en el navegador
   - O ejecutar: `php verificar_sistema.php`

3. **Configurar conexión** (si es necesario)
   - Editar `config/database.php` con tus credenciales de MySQL

4. **Acceder al sistema**
   - Página principal: `index.php`
   - Sistema de transferencias: `transferencias_ui.php`

## Cómo Probar el Sistema

### 1. Interfaz Web de Recepción
```
http://localhost/transferencias_ui.php
```
Esta interfaz muestra:
- Lista de transferencias pendientes de recepción
- Formulario de recepción que replica el formato físico
- Tabla de tallas como se muestra en la imagen

### 2. Script de Prueba Automatizada
```bash
php scripts/test_recepcion.php
```

### 3. Crear Transferencia vía API (simular Almacén 1)
```bash
curl -X POST http://localhost/api/transferencias.php \
  -H "Content-Type: application/json" \
  -d '{
    "referencia": "TRANS-001",
    "almacen_origen_id": 1,
    "almacen_destino_id": 2,
    "total_items": 100,
    "trabajador_id": 1,
    "estado": "enviado",
    "usuario_creacion": "admin"
  }'
```

## Configuración de Base de Datos

Ahora la configuración de la base de datos es configurable desde `config/config.php`.

Por defecto la constante `DB_NAME` se inicializa en `config/config.php` con el valor `control_almacen` (almacén 1). Para apuntar a otro almacén:

- Edita `config/config.php` y cambia la constante `DB_NAME` al nombre de la base de datos del almacén 2.
- O bien, en Windows PowerShell puedes exportar la variable de entorno antes de iniciar Apache (o configurar el entorno del servicio):

```powershell
$env:DB_NAME = 'control_almacen_almacen2'
```

La conexión (usuario/host/contraseña) sigue en `config/database.php` si necesitas ajustarlos.

## Opción B: Sincronización entre instancias vía API (no compartir BD)

Si no deseas que ambas carpetas apunten a la misma base de datos, puedes configurar una sincronización simple por API entre instancias.

Configuración (en `config/config.php` o mediante variables de entorno):

- `REMOTE_TRANSFER_URL` : URL base del servidor remoto (por ejemplo `http://192.168.1.20`)
- `REMOTE_API_TOKEN` : token secreto compartido para autenticación de llamadas entre instancias (opcional pero recomendado)
- `LOCAL_ALMACEN_ID` : id del almacén local (ej. 1)

Ejemplo en PowerShell antes de arrancar el servidor (o configurar como variables del servicio):

```powershell
$env:REMOTE_TRANSFER_URL = 'http://192.168.1.20'
$env:REMOTE_API_TOKEN = 'mi-secreto'
$env:LOCAL_ALMACEN_ID = '1'
```

Cómo funciona:
- Cuando se crea un `control_entrada` en la instancia local, el servidor intentará hacer un POST a `REMOTE_TRANSFER_URL/api/transferencias.php` con la información mínima de transferencia.
- El servidor remoto validará el header `X-API-TOKEN` si `REMOTE_API_TOKEN` está definido.
- El intento de notificación remota es no-bloqueante para que una falla de la red no impida crear el `control_entrada` local.

Nota: la lógica actual envía `almacen_destino_id` como `null` en la creación automática; puedes adaptar tu servidor remoto para mapear destino por reglas internas o modificar el payload en `api/control_entrada.php` para enviar un destino específico.

## Asignación de costureros y confirmación en Empaque

Nuevo flujo implementado:

- Almacén 1 (Corte) puede asignar un `trabajador_id` (costurero) cuando crea una transferencia.
- Almacén 2 (Empaque) verá en la lista la transferencia y el `trabajador_id` asignado y podrá confirmar la entrega con la persona.
- La confirmación por trabajador permite registrar un array de `faltantes` (guardado como JSON en la columna `faltantes_json`).

Rutas nuevas / actualizadas:

- `POST /api/transferencias.php` - ahora acepta `trabajador_id` (opcional).
- `PUT /api/transferencias.php/{id}/confirmar_trabajador` - body JSON: { "trabajador_id": 123, "faltantes": [ {"numero_item":1, "faltante":2}, ... ] }

UI de prueba:

- `http://localhost/1/` - formulario simple para enviar transferencias desde Almacén 1 (campo `trabajador_id`).
- `http://localhost/2/transferencias_ui.php` - lista de transferencias y formulario para confirmar por trabajador desde Empaque.

## Funcionalidades

### 1. Control de Entrada
- Registro de fecha de recepción
- Selección de tipo de prenda
- Asignación de encargado de taller
- Registro de recepcionista
- Control de puntos a favor y precios
- Observaciones generales

### 2. Detalles de Prendas
- Múltiples items por registro
- Control de color/código
- Registro de todas las tallas estándar
- Estado de entrega
- Observaciones por item
- Cálculo automático de totales

### 3. Catálogos
- **Tipos de Prenda**: Gestión de categorías de prendas
- **Encargados de Taller**: Información de contacto
- **Recepcionistas**: Datos del personal de recepción

### 4. Reportes y Consultas
- Lista de todos los registros
- Búsqueda por fechas
- Resumen de tallas por registro
- Detalles completos por entrada

## API Endpoints

### Control de Entrada
- `GET /api/control_entrada.php` - Listar registros
- `GET /api/control_entrada.php/{id}` - Obtener registro específico
- `POST /api/control_entrada.php` - Crear nuevo registro
- `PUT /api/control_entrada.php/{id}` - Actualizar registro
- `DELETE /api/control_entrada.php/{id}` - Eliminar registro

### Detalles
- `GET /api/detalles.php/control/{id}` - Detalles por control
- `GET /api/detalles.php/resumen/{id}` - Resumen de tallas
- `POST /api/detalles.php` - Crear detalle
- `PUT /api/detalles.php/{id}` - Actualizar detalle
- `DELETE /api/detalles.php/{id}` - Eliminar detalle

### Catálogos
- `GET /api/catalogos.php` - Todos los catálogos
- `GET /api/catalogos.php/tipos-prenda` - Tipos de prenda
- `GET /api/catalogos.php/encargados` - Encargados
- `GET /api/catalogos.php/recepcionistas` - Recepcionistas

### Transferencias entre almacenes
- `GET /api/transferencias.php` - Listar transferencias
- `GET /api/transferencias.php/{id}` - Obtener transferencia por id
- `POST /api/transferencias.php` - Crear nueva transferencia. JSON body:
   - referencia (string), almacen_origen_id (int), almacen_destino_id (int), total_items (int), usuario_creacion (string)
   - control_entrada_id (int, opcional), observaciones (string, opcional)
- `PUT /api/transferencias.php/{id}/recibir` - Marcar transferencia como recibida. JSON body: { "usuario_recepcion": "nombre" }

## Uso del Sistema

### 1. Configuración inicial
- Ejecutar `setup.php` para crear la base de datos y datos iniciales
- Verificar con `verificar_sistema.php` que todo esté funcionando

### 2. Crear transferencias de prueba
- Usar `crear_transferencia_demo.php` para generar transferencias de ejemplo
- Esto simula el envío desde el Almacén 1 (Corte)

### 3. Procesar recepción en Almacén 2
- Acceder a `transferencias_ui.php` para ver la lista de transferencias
- Hacer clic en "Procesar Recepción" para abrir el formulario de control de entrada
- El formulario replica exactamente el formato físico del almacén

### 4. Formulario de Control de Entrada
- Soporte para todas las tallas: 2, 4, 6, 8, 10, 12, 14, 16, XS, S, M, L, XL, XXL
- Cálculo automático de totales por fila y por talla
- Campos para observaciones y estado de entrega
- Validación de datos antes del envío

### 5. Flujo completo de trabajo
1. Crear transferencia de demostración
2. Ver lista de transferencias pendientes
3. Procesar recepción con el formulario de control
4. Confirmar recepción con detalles de tallas
5. El sistema registra automáticamente todos los cambios

## Personalización

### Agregar Nuevas Tallas
Editar en `database/schema.sql` y `js/app.js` para agregar nuevas columnas de tallas.

### Modificar Campos
Actualizar los modelos PHP y el JavaScript para agregar nuevos campos al formulario.

### Cambiar Estilos
Modificar `css/styles.css` para personalizar la apariencia.

## Soporte

Para soporte técnico o consultas sobre el sistema, revisar:
- Logs de PHP para errores del servidor
- Consola del navegador para errores de JavaScript
- Verificar conexión a base de datos

## Licencia

Este proyecto está desarrollado para uso interno y educativo.
   
## usuarios

textil o custureros (REEMPLAZAR EN BASE DE DATOS)

1.CARLOS
2.WILIAN
3.CLEMENTE
4.ERIKA
5.LUZ
6.LIZ
7.ELVA

## encargados

1. ARACELI Almacen 2
2. LISBETH Almacen 2
3. YOVANI   Almacen 2
3. WILDER   Almacen 1

Admin: cristian


## ROPA

1.POLERA CLASICA CERRADO
2.POLERA CLASICA CIERRE
3.POLERA CUELLO REDONDO
4.POLERA CLASICA - ESTAMPADO
5.POLERA CLASICA - REVOLT
6.BUSO UNISEX
7.BUSO EXTRAOVERSIEZE
8.POLERA BALACLAVA ADULTO
9.POLERA CUELLO REDONDO IMBOX

## colores y codigos

Agrega todos los colores y codigos que se usan en el registro de sistema

---

## 🚀 Despliegue en InfinityFree

### Credenciales de Producción

**Base de Datos MySQL:**
```
Host:     sql303.infinityfree.com
Puerto:   3306
Usuario:  if0_40096200
Password: TazLBTRzaYzlV1O
Database: if0_40096200_control_almacen
```

### Archivos de Configuración

Para desplegar en InfinityFree:

1. **Renombrar archivos:**
   ```bash
   copy config\database.php.infinityfree config\database.php
   copy .env.infinityfree .env
   ```

2. **Importar base de datos:**
   - Acceder a phpMyAdmin en InfinityFree
   - Importar `database/schema_tablas.sql`
   - Importar `database/datos_iniciales.sql`

3. **Subir archivos por FTP:**
   - Host: ftpupload.net
   - Usuario: if0_40096200
   - Subir todos los archivos a `/htdocs/2/`

4. **Verificar instalación:**
   - Acceder a `test_infinityfree.php`
   - Verificar que todo esté funcionando
   - **Eliminar** el archivo de test

### Documentación Completa

Ver: `c:\xampp\htdocs\GUIA_DESPLIEGUE_INFINITYFREE.md`

### Usuarios por Defecto

- **Admin**: cristian / admin123
- **Supervisores**: araceli, lisbeth, yovani, wilmer / admin123

cambiar el nombre a (ALMACEN SOTANO)
registre de salida de prendas