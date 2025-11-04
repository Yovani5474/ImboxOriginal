# 🔐 CREDENCIALES DEL SISTEMA IMBOX

## 👤 USUARIOS DISPONIBLES

### **Administrador Principal**

```
┌─────────────────────────────────────┐
│  Email:      cristian@imbox.local   │
│  Username:   cristian               │
│  Contraseña: admin123               │
│  Rol:        admin                  │
└─────────────────────────────────────┘
```

**Permisos:**
- ✅ Acceso total al sistema
- ✅ Gestión de usuarios
- ✅ Configuración del sistema
- ✅ Todos los módulos (Almacén 1, 2 y Admin)

---

### **Supervisores**

#### **ARACELI**
```
Email:      araceli@imbox.local
Username:   araceli
Contraseña: admin123
Rol:        supervisor
Área:       Empaque
```

#### **LISBETH**
```
Email:      lisbeth@imbox.local
Username:   lisbeth
Contraseña: admin123
Rol:        supervisor
Área:       Empaque
```

#### **YOVANI**
```
Email:      yovani@imbox.local
Username:   yovani
Contraseña: admin123
Rol:        supervisor
Área:       Empaque
```

#### **WILMER**
```
Email:      wilmer@imbox.local
Username:   wilmer
Contraseña: admin123
Rol:        supervisor
Área:       Corte
```

**Permisos de Supervisores:**
- ✅ Gestión de transferencias
- ✅ Control de entrada de materiales/prendas
- ✅ Visualización de reportes
- ⚠️ Sin acceso a configuración del sistema
- ⚠️ Sin gestión de usuarios

---

## 👷 TRABAJADORES / COSTUREROS

Los siguientes trabajadores están registrados en el sistema:

| Código | Nombre | Especialidad | Nivel |
|--------|--------|--------------|-------|
| TRAB-001 | CARLOS | Costura textil | Medio |
| TRAB-002 | WILIAN | Costura textil | Medio |
| TRAB-003 | CLEMENTE | Costura textil | Medio |
| TRAB-004 | ERIKA | Costura textil | Medio |
| TRAB-005 | LUZ | Costura textil | Medio |
| TRAB-006 | LIZ | Costura textil | Medio |
| TRAB-007 | ELVA | Costura textil | Medio |

**Nota:** Los trabajadores NO tienen acceso al sistema web. Solo aparecen en los registros de control de entrada.

---

## 📍 ALMACENES CONFIGURADOS

| Clave | Nombre | Ubicación | Tipo |
|-------|--------|-----------|------|
| ALM1 | Almacén Corte | Planta A - Area de Corte | Corte |
| ALM2 | Almacén Empaque | Planta B - Area de Empaque | Empaque |
| BOD1 | Bodega General | Edificio Principal | Bodega |

---

## 🔑 CÓMO INICIAR SESIÓN

### **Opción 1: Por Email**
```
Email:      cristian@imbox.local
Contraseña: admin123
```

### **Opción 2: Por Username**
```
Username:   cristian
Contraseña: admin123
```

**URLs de Acceso:**
- Almacén 1 (Corte): `http://localhost/1/`
- Almacén 2 (Empaque): `http://localhost/2/`
- Admin Panel: `http://localhost/3/login.php`

---

## ⚠️ SEGURIDAD

### **Cambiar Contraseñas**

Es **MUY IMPORTANTE** cambiar las contraseñas por defecto después de la instalación.

**Para generar una nueva contraseña:**

1. **Usando PHP:**
   ```php
   <?php
   $nueva_password = 'TuContraseñaSegura123!';
   $hash = password_hash($nueva_password, PASSWORD_BCRYPT);
   echo $hash;
   ?>
   ```

2. **Actualizar en la base de datos:**
   ```sql
   UPDATE usuarios 
   SET password = '$2y$10$HASH_GENERADO' 
   WHERE username = 'cristian';
   ```

3. **O usar el script:**
   ```
   http://localhost/3/actualizar_password.php
   ```

---

## 👥 PERSONAL ADICIONAL

### **Encargados de Almacén**

| Código | Nombre | Especialidad | Almacén |
|--------|--------|--------------|---------|
| ENC-001 | ARACELI | Empaque | Almacén 2 |
| ENC-002 | LISBETH | Empaque | Almacén 2 |
| ENC-003 | YOVANI | Empaque | Almacén 2 |
| ENC-004 | WILMER | Corte | Almacén 1 |

### **Recepcionistas**

| Código | Nombre | Almacén Asignado |
|--------|--------|------------------|
| REC-001 | ARACELI | Almacén 2 (Empaque) |
| REC-002 | LISBETH | Almacén 2 (Empaque) |
| REC-003 | YOVANI | Almacén 2 (Empaque) |
| REC-004 | WILMER | Almacén 1 (Corte) |

---

## 📋 TIPOS DE PRENDA CONFIGURADOS

El sistema incluye los siguientes tipos de prenda de IMBOX:

| Código | Nombre | Categoría |
|--------|--------|-----------|
| PREN-001 | POLERA CLASICA CERRADO | Poleras |
| PREN-002 | POLERA CLASICA CIERRE | Poleras |
| PREN-003 | POLERA CUELLO REDONDO | Poleras |
| PREN-004 | POLERA CLASICA - ESTAMPADO | Poleras |
| PREN-005 | POLERA CLASICA - REVOLT | Poleras |
| PREN-006 | BUSO UNISEX | Buzos |
| PREN-007 | BUSO EXTRAOVERSIZE | Buzos |
| PREN-008 | POLERA BALACLAVA ADULTO | Poleras |
| PREN-009 | POLERA CUELLO REDONDO IMBOX | Poleras |

---

## 🏭 PROVEEDORES CONFIGURADOS

| Código | Nombre | Ciudad | País |
|--------|--------|--------|------|
| PROV001 | Textiles del Norte S.A. | Monterrey | México |
| PROV002 | Telas Importadas CDMX | Ciudad de México | México |
| PROV003 | Insumos Textiles Internacional | Guadalajara | México |

---

## 🧵 TIPOS DE MATERIAL

| Código | Nombre | Categoría | Unidad |
|--------|--------|-----------|--------|
| TEL-ALG | Tela de Algodón | Telas | metros |
| TEL-POL | Tela de Poliéster | Telas | metros |
| TEL-MIX | Tela Mixta | Telas | metros |
| HIL-001 | Hilo de Coser | Insumos | piezas |
| BOT-001 | Botones | Insumos | piezas |
| CRE-001 | Cremalleras | Insumos | piezas |

---

## 🔒 ROLES Y PERMISOS

### **Admin (Administrador)**
```
✅ Acceso completo
✅ Gestión de usuarios
✅ Configuración del sistema
✅ Todos los módulos
✅ Reportes y estadísticas
✅ Gestión de catálogos
```

### **Supervisor**
```
✅ Gestión de transferencias
✅ Control de entrada
✅ Visualización de reportes
✅ Gestión de trabajadores
⚠️ Sin configuración del sistema
⚠️ Sin gestión de usuarios
```

### **Operador**
```
✅ Registro de entrada de materiales
✅ Registro de entrada de prendas
✅ Consulta de transferencias
⚠️ Sin modificar configuración
⚠️ Sin acceso a reportes completos
```

### **Recepcionista**
```
✅ Registro de recepción
✅ Control de entrada básico
⚠️ Solo lectura en otros módulos
```

---

## 📊 RESUMEN DE DATOS INICIALES

```
✓ Usuarios del sistema: 5
  - 1 Administrador (CRISTIAN)
  - 4 Supervisores (ARACELI, LISBETH, YOVANI, WILMER)

✓ Trabajadores/Costureros: 7
  - CARLOS, WILIAN, CLEMENTE, ERIKA, LUZ, LIZ, ELVA

✓ Almacenes: 3
  - ALM1 (Corte), ALM2 (Empaque), BOD1 (Bodega)

✓ Tipos de prenda: 9
  - 7 Poleras, 2 Buzos

✓ Proveedores: 3
  - Textiles del Norte, Telas Importadas, Insumos Textiles

✓ Tipos de material: 6
  - 3 Telas, 3 Insumos
```

---

## 🚀 PRIMER ACCESO

1. **Acceder al instalador:**
   ```
   http://localhost/3/install.php
   ```

2. **Instalar base de datos** (click en el botón)

3. **Ir al login:**
   ```
   http://localhost/3/login.php
   ```

4. **Ingresar credenciales:**
   ```
   Username:   cristian
   Contraseña: admin123
   ```

5. **¡Listo! Ya puedes usar el sistema**

---

## 📞 SOPORTE

Para agregar más usuarios o cambiar credenciales:

- 📧 Contactar al administrador del sistema
- 🌐 Revisar documentación en `README.md`
- 🔧 Usar script `actualizar_password.php`

---

**Sistema IMBOX Unificado v1.0.0**  
**© 2025 Todos los derechos reservados**

**Última actualización:** 02/11/2025
