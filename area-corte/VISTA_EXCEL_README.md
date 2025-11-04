# 📊 Vista Excel - Área de Corte

## ✅ NUEVA FUNCIONALIDAD AGREGADA

Se ha agregado una vista tipo Excel para gestionar las transferencias del Área de Corte.

---

## 📁 ARCHIVO CREADO

```
c:\xampp\htdocs\1\transferencias_excel.php
```

---

## 🎯 CARACTERÍSTICAS

### **Vista de Tabla Excel:**
- ✅ Diseño similar a Microsoft Excel
- ✅ Todas las transferencias en una sola vista
- ✅ Búsqueda en tiempo real
- ✅ Exportación directa a Excel (.xlsx)
- ✅ Estadísticas en tiempo real
- ✅ Acciones rápidas (Ver/Editar)

### **Funcionalidades:**

1. **Búsqueda Instantánea**
   - Filtra por referencia, proveedor, orden de compra
   - Resultados en tiempo real mientras escribes

2. **Exportar a Excel**
   - Descarga el archivo .xlsx con un solo clic
   - Nombre automático: transferencias_corte_YYYY-MM-DD.xlsx
   - Compatible con Microsoft Excel, LibreOffice, Google Sheets

3. **Estadísticas Visuales**
   - Total de transferencias
   - Transferencias pendientes
   - Total de rollos
   - Total de metros

4. **Acciones Rápidas**
   - Ver detalle completo
   - Editar transferencia
   - Todo con un solo clic

---

## 🚀 CÓMO ACCEDER

### **Opción 1: Desde el Panel Principal**

```
http://localhost/1/
```

En el menú de navegación superior encontrarás:
```
┌─────────────────────────────────────────┐
│ Control de Entrada | Transferencias    │
│ Ver Transferencias | Vista Excel ⭐    │
└─────────────────────────────────────────┘
```

### **Opción 2: Desde Acciones Rápidas**

En el dashboard principal, sección "Acciones Rápidas":
```
┌────────────────────────────────────┐
│ Nuevo Control                      │
│ Nueva Transferencia                │
│ Ver Transferencias                 │
│ Vista Excel ⭐ NUEVO               │
│ Reportes                           │
└────────────────────────────────────┘
```

### **Opción 3: URL Directa**

```
http://localhost/1/transferencias_excel.php
```

---

## 📊 VISTA PREVIA

### **Pantalla Principal:**

```
┌─────────────────────────────────────────────────────────┐
│  📊 Transferencias - Vista Excel                        │
│  Área de Corte | Envío de prendas a Empaque            │
│                                      [ Panel Control ]  │
├─────────────────────────────────────────────────────────┤
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐  │
│  │   50     │ │    12    │ │  1,250   │ │  5,678   │  │
│  │  Total   │ │Pendientes│ │  Rollos  │ │  Metros  │  │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘  │
├─────────────────────────────────────────────────────────┤
│  [🔍 Buscar...]              [📊 Exportar a Excel]     │
├─────────────────────────────────────────────────────────┤
│  ID │Referencia │Fecha │Proveedor│OC   │Rollos│Estado │
│  ──────────────────────────────────────────────────────│
│  50 │COR-2025..│05/11 │Proveedor│P-123│  25  │Enviado│
│  49 │COR-2025..│04/11 │Proveedor│P-122│  30  │Comple.│
│  48 │COR-2025..│03/11 │Proveedor│P-121│  20  │Pendi. │
│  ...                                                    │
└─────────────────────────────────────────────────────────┘
```

---

## 💡 FUNCIONES JAVASCRIPT

### **Búsqueda en Tiempo Real:**

```javascript
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchText = this.value.toLowerCase();
    // Filtra las filas de la tabla
});
```

### **Exportar a Excel:**

```javascript
function exportToExcel() {
    const table = document.getElementById('transferenciasTable');
    const wb = XLSX.utils.table_to_book(table);
    XLSX.writeFile(wb, 'transferencias_corte.xlsx');
}
```

### **Acciones:**

```javascript
function verDetalle(id) {
    window.location.href = 'control_entrada.php?id=' + id;
}

function editarTransferencia(id) {
    window.location.href = 'control_entrada.php?id=' + id + '&editar=1';
}
```

---

## 🎨 DISEÑO

### **Colores:**
- **Primario:** #FF8C00 (Naranja IMBOX)
- **Secundario:** #FFA500 (Naranja claro)
- **Fondo:** #f5f5f5 (Gris claro)
- **Cards:** #FFFFFF (Blanco)

### **Estados:**
- **Pendiente:** Amarillo (#ffc107)
- **Enviado:** Naranja (#FF8C00)
- **Completado:** Verde (#28a745)

---

## 📦 DEPENDENCIAS

### **Incluidas en CDN:**

```html
<!-- Bootstrap 5.1.3 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">

<!-- Font Awesome 6.0 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- SheetJS (XLSX) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
```

---

## 🗄️ CONSULTAS SQL

### **Obtener Transferencias:**

```sql
SELECT * FROM controles_entrada 
ORDER BY fecha_creacion DESC 
LIMIT 100
```

### **Estadísticas:**

```sql
-- Total por estado
SELECT estado, COUNT(*) as total 
FROM controles_entrada 
GROUP BY estado

-- Totales de materiales
SELECT 
    SUM(total_rollos) as total_rollos,
    SUM(total_metros) as total_metros 
FROM controles_entrada
```

---

## 🔧 PERSONALIZACIÓN

### **Cambiar Límite de Registros:**

En `transferencias_excel.php`, línea ~22:

```php
// Cambiar de 100 a tu valor deseado
$stmt = $db->query("
    SELECT * FROM controles_entrada 
    ORDER BY fecha_creacion DESC 
    LIMIT 100  <-- Cambiar aquí
");
```

### **Agregar Columnas:**

1. Modificar la consulta SQL
2. Agregar `<th>` en el thead
3. Agregar `<td>` en el tbody

---

## ✨ VENTAJAS

```
✅ Visualización rápida de todas las transferencias
✅ Búsqueda instantánea sin recargar página
✅ Exportación a Excel con un clic
✅ Estadísticas en tiempo real
✅ Diseño profesional y moderno
✅ Responsive (funciona en móviles)
✅ No requiere instalación
✅ Compatible con SQLite
```

---

## 🚀 PRÓXIMAS MEJORAS

Posibles funcionalidades futuras:

- [ ] Filtros avanzados (por fecha, estado, proveedor)
- [ ] Ordenamiento por columnas
- [ ] Paginación
- [ ] Impresión directa
- [ ] Exportar a PDF
- [ ] Gráficos estadísticos
- [ ] Modo oscuro

---

## 📞 SOPORTE

Para problemas o sugerencias, documenta:

1. URL donde ocurre el error
2. Mensaje de error (si existe)
3. Navegador utilizado
4. Captura de pantalla

---

**Sistema IMBOX v6.1**  
**Área de Corte - Vista Excel**  
**Fecha: 02/11/2025**  
**Estado: ✅ FUNCIONAL**
