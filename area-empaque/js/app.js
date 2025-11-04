// Variables globales
let catalogos = {};
let registroActual = null;
let contadorDetalles = 0;

// Configuración de la API
const API_BASE = 'api/';

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    cargarCatalogos();
    cargarRegistros();
    configurarFormulario();
    
    // Establecer fecha actual por defecto
    document.getElementById('fecha_recepcion').value = new Date().toISOString().split('T')[0];
});

// Funciones de navegación
function mostrarSeccion(seccion) {
    // Ocultar todas las secciones
    document.querySelectorAll('.seccion').forEach(s => s.classList.remove('active'));
    
    // Mostrar la sección seleccionada
    document.getElementById(`seccion-${seccion}`).classList.add('active');
    
    // Actualizar navegación
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    event.target.classList.add('active');
    
    // Cargar datos según la sección
    if (seccion === 'lista') {
        cargarRegistros();
    } else if (seccion === 'catalogos') {
        cargarCatalogos();
        mostrarCatalogos();
    } else if (seccion === 'nuevo') {
        limpiarFormulario();
    }
}

// Funciones de API
async function apiRequest(endpoint, options = {}) {
    try {
        const response = await fetch(API_BASE + endpoint, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Error en la petición');
        }
        
        return data;
    } catch (error) {
        console.error('Error en API:', error);
        mostrarAlerta('Error: ' + error.message, 'danger');
        throw error;
    }
}

// Cargar catálogos
async function cargarCatalogos() {
    try {
        const response = await apiRequest('catalogos.php');
        catalogos = response.data;
        
        // Llenar selects
        llenarSelect('tipo_prenda_id', catalogos.tiposPrenda, 'id', 'nombre');
        llenarSelect('encargado_taller_id', catalogos.encargados, 'id', 'nombre');
        llenarSelect('recepcionista_id', catalogos.recepcionistas, 'id', 'nombre');
        
    } catch (error) {
        console.error('Error cargando catálogos:', error);
    }
}

function llenarSelect(selectId, datos, valorCampo, textoCampo) {
    const select = document.getElementById(selectId);
    
    // Limpiar opciones existentes (excepto la primera)
    while (select.children.length > 1) {
        select.removeChild(select.lastChild);
    }
    
    // Agregar nuevas opciones
    datos.forEach(item => {
        const option = document.createElement('option');
        option.value = item[valorCampo];
        option.textContent = item[textoCampo];
        select.appendChild(option);
    });
}

// Cargar registros
async function cargarRegistros() {
    try {
        const response = await apiRequest('control_entrada.php');
        const registros = response.data;
        
        const tbody = document.querySelector('#tabla-registros tbody');
        tbody.innerHTML = '';
        
        registros.forEach(registro => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${registro.id}</td>
                <td>${formatearFecha(registro.fecha_recepcion)}</td>
                <td>${registro.tipo_prenda}</td>
                <td>${registro.encargado_taller}</td>
                <td>${registro.recepcionista}</td>
                <td>${registro.total_items}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalle(${registro.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="editarRegistro(${registro.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarRegistro(${registro.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(fila);
        });
        
    } catch (error) {
        console.error('Error cargando registros:', error);
    }
}

// Configurar formulario
function configurarFormulario() {
    const form = document.getElementById('form-control-entrada');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        await guardarRegistro();
    });
}

// Agregar detalle de prenda
function agregarDetalle() {
    contadorDetalles++;
    const contenedor = document.getElementById('contenedor-detalles');
    
    const detalleDiv = document.createElement('div');
    detalleDiv.className = 'detalle-item fade-in';
    detalleDiv.id = `detalle-${contadorDetalles}`;
    
    detalleDiv.innerHTML = `
        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" onclick="eliminarDetalle(${contadorDetalles})">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="row">
            <div class="col-md-2">
                <label class="form-label">N°</label>
                <input type="number" class="form-control" name="numero_item" value="${contadorDetalles}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Color/Código</label>
                <input type="text" class="form-control" name="color_codigo">
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado de Entrega</label>
                <input type="text" class="form-control" name="estado_entrega">
            </div>
            <div class="col-md-3">
                <label class="form-label">Observación</label>
                <input type="text" class="form-control" name="observacion_item">
            </div>
        </div>
        
        <div class="tallas-grid">
            <div>
                <div class="talla-label">2</div>
                <input type="number" class="form-control talla-input" name="talla_2" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">4</div>
                <input type="number" class="form-control talla-input" name="talla_4" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">6</div>
                <input type="number" class="form-control talla-input" name="talla_6" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">8</div>
                <input type="number" class="form-control talla-input" name="talla_8" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">10</div>
                <input type="number" class="form-control talla-input" name="talla_10" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">12</div>
                <input type="number" class="form-control talla-input" name="talla_12" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">14</div>
                <input type="number" class="form-control talla-input" name="talla_14" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">16</div>
                <input type="number" class="form-control talla-input" name="talla_16" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">XS</div>
                <input type="number" class="form-control talla-input" name="talla_xs" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">S</div>
                <input type="number" class="form-control talla-input" name="talla_s" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">M</div>
                <input type="number" class="form-control talla-input" name="talla_m" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">L</div>
                <input type="number" class="form-control talla-input" name="talla_l" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">XL</div>
                <input type="number" class="form-control talla-input" name="talla_xl" value="0" min="0">
            </div>
            <div>
                <div class="talla-label">XXL</div>
                <input type="number" class="form-control talla-input" name="talla_xxl" value="0" min="0">
            </div>
        </div>
    `;
    
    contenedor.appendChild(detalleDiv);
}

function eliminarDetalle(id) {
    const detalle = document.getElementById(`detalle-${id}`);
    if (detalle) {
        detalle.remove();
    }
}

// Guardar registro
async function guardarRegistro() {
    try {
        const form = document.getElementById('form-control-entrada');
        const formData = new FormData(form);
        
        // Datos del control de entrada
        const controlEntrada = {
            fecha_recepcion: formData.get('fecha_recepcion') || document.getElementById('fecha_recepcion').value,
            tipo_prenda_id: formData.get('tipo_prenda_id') || document.getElementById('tipo_prenda_id').value,
            encargado_taller_id: formData.get('encargado_taller_id') || document.getElementById('encargado_taller_id').value,
            recepcionista_id: formData.get('recepcionista_id') || document.getElementById('recepcionista_id').value,
            puntos_favor: formData.get('puntos_favor') || document.getElementById('puntos_favor').value || 0,
            precio_10: formData.get('precio_10') || document.getElementById('precio_10').value || 0,
            precio_15: formData.get('precio_15') || document.getElementById('precio_15').value || 0,
            observaciones: formData.get('observaciones') || document.getElementById('observaciones').value || ''
        };
        
        // Recopilar detalles
        const detalles = [];
        const detalleItems = document.querySelectorAll('.detalle-item');
        
        detalleItems.forEach(item => {
            const detalle = {
                numero_item: item.querySelector('[name="numero_item"]').value,
                color_codigo: item.querySelector('[name="color_codigo"]').value || '',
                estado_entrega: item.querySelector('[name="estado_entrega"]').value || '',
                observacion_item: item.querySelector('[name="observacion_item"]').value || '',
                talla_2: parseInt(item.querySelector('[name="talla_2"]').value) || 0,
                talla_4: parseInt(item.querySelector('[name="talla_4"]').value) || 0,
                talla_6: parseInt(item.querySelector('[name="talla_6"]').value) || 0,
                talla_8: parseInt(item.querySelector('[name="talla_8"]').value) || 0,
                talla_10: parseInt(item.querySelector('[name="talla_10"]').value) || 0,
                talla_12: parseInt(item.querySelector('[name="talla_12"]').value) || 0,
                talla_14: parseInt(item.querySelector('[name="talla_14"]').value) || 0,
                talla_16: parseInt(item.querySelector('[name="talla_16"]').value) || 0,
                talla_xs: parseInt(item.querySelector('[name="talla_xs"]').value) || 0,
                talla_s: parseInt(item.querySelector('[name="talla_s"]').value) || 0,
                talla_m: parseInt(item.querySelector('[name="talla_m"]').value) || 0,
                talla_l: parseInt(item.querySelector('[name="talla_l"]').value) || 0,
                talla_xl: parseInt(item.querySelector('[name="talla_xl"]').value) || 0,
                talla_xxl: parseInt(item.querySelector('[name="talla_xxl"]').value) || 0
            };
            detalles.push(detalle);
        });
        
        const datos = {
            controlEntrada,
            detalles
        };
        
        await apiRequest('control_entrada.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
        
        mostrarAlerta('Registro guardado exitosamente', 'success');
        limpiarFormulario();
        mostrarSeccion('lista');
        
    } catch (error) {
        console.error('Error guardando registro:', error);
    }
}

// Limpiar formulario
function limpiarFormulario() {
    document.getElementById('form-control-entrada').reset();
    document.getElementById('contenedor-detalles').innerHTML = '';
    document.getElementById('fecha_recepcion').value = new Date().toISOString().split('T')[0];
    contadorDetalles = 0;
    registroActual = null;
    document.getElementById('titulo-formulario').textContent = 'Nuevo Registro';
}

// Ver detalle de registro
async function verDetalle(id) {
    try {
        const response = await apiRequest(`control_entrada.php/${id}`);
        const registro = response.data;
        
        // Mostrar modal o sección con detalles
        console.log('Detalle del registro:', registro);
        // Aquí puedes implementar un modal para mostrar los detalles
        
    } catch (error) {
        console.error('Error obteniendo detalle:', error);
    }
}

// Editar registro
async function editarRegistro(id) {
    try {
        const response = await apiRequest(`control_entrada.php/${id}`);
        const registro = response.data;
        
        // Llenar formulario con datos existentes
        document.getElementById('fecha_recepcion').value = registro.fecha_recepcion;
        document.getElementById('tipo_prenda_id').value = registro.tipo_prenda_id;
        document.getElementById('encargado_taller_id').value = registro.encargado_taller_id;
        document.getElementById('recepcionista_id').value = registro.recepcionista_id;
        document.getElementById('puntos_favor').value = registro.puntos_favor;
        document.getElementById('precio_10').value = registro.precio_10;
        document.getElementById('precio_15').value = registro.precio_15;
        document.getElementById('observaciones').value = registro.observaciones;
        
        // Cargar detalles
        document.getElementById('contenedor-detalles').innerHTML = '';
        contadorDetalles = 0;
        
        if (registro.detalles && registro.detalles.length > 0) {
            registro.detalles.forEach(detalle => {
                agregarDetalle();
                const detalleDiv = document.getElementById(`detalle-${contadorDetalles}`);
                
                detalleDiv.querySelector('[name="numero_item"]').value = detalle.numero_item;
                detalleDiv.querySelector('[name="color_codigo"]').value = detalle.color_codigo;
                detalleDiv.querySelector('[name="estado_entrega"]').value = detalle.estado_entrega;
                detalleDiv.querySelector('[name="observacion_item"]').value = detalle.observacion_item;
                
                // Llenar tallas
                ['2', '4', '6', '8', '10', '12', '14', '16', 'xs', 's', 'm', 'l', 'xl', 'xxl'].forEach(talla => {
                    const input = detalleDiv.querySelector(`[name="talla_${talla}"]`);
                    if (input) {
                        input.value = detalle[`talla_${talla}`] || 0;
                    }
                });
            });
        }
        
        registroActual = id;
        document.getElementById('titulo-formulario').textContent = 'Editar Registro';
        mostrarSeccion('nuevo');
        
    } catch (error) {
        console.error('Error cargando registro para editar:', error);
    }
}

// Eliminar registro
async function eliminarRegistro(id) {
    if (!confirm('¿Está seguro de que desea eliminar este registro?')) {
        return;
    }
    
    try {
        await apiRequest(`control_entrada.php/${id}`, {
            method: 'DELETE'
        });
        
        mostrarAlerta('Registro eliminado exitosamente', 'success');
        cargarRegistros();
        
    } catch (error) {
        console.error('Error eliminando registro:', error);
    }
}

// Funciones de catálogos
function mostrarCatalogos() {
    mostrarListaCatalogo('tipos-prenda', catalogos.tiposPrenda, 'lista-tipos-prenda');
    mostrarListaCatalogo('encargados', catalogos.encargados, 'lista-encargados');
    mostrarListaCatalogo('recepcionistas', catalogos.recepcionistas, 'lista-recepcionistas');
}

function mostrarListaCatalogo(tipo, datos, contenedorId) {
    const contenedor = document.getElementById(contenedorId);
    contenedor.innerHTML = '';
    
    datos.forEach(item => {
        const div = document.createElement('div');
        div.className = 'catalogo-item';
        div.innerHTML = `
            <span>${item.nombre}</span>
            <div class="catalogo-acciones">
                <button class="btn btn-sm btn-outline-warning" onclick="editarCatalogo('${tipo}', ${item.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarCatalogo('${tipo}', ${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        contenedor.appendChild(div);
    });
}

// Funciones auxiliares
function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES');
}

function mostrarAlerta(mensaje, tipo = 'info') {
    const alertaDiv = document.createElement('div');
    alertaDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
    alertaDiv.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.insertBefore(alertaDiv, document.body.firstChild);
    
    setTimeout(() => {
        alertaDiv.remove();
    }, 5000);
}

// Agregar un detalle inicial al cargar
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        agregarDetalle();
    }, 500);
});