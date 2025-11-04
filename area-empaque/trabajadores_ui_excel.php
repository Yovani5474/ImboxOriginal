<?php
// Gestión de Trabajadores - Área de Empaque - Editable tipo Excel
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Empaque | Trabajadores</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/2/css/styles.css">
  <style>
    body { background-color: #f5f5f5; }
    .main-container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 0 20px;
    }
    .header-card {
      background: white;
      border-radius: 10px;
      padding: 25px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      border-top: 4px solid #FF8C00;
    }
    .stat-card {
      background: white;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      border-left: 4px solid #FF8C00;
      transition: transform 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255,140,0,0.2);
    }
    .stat-number {
      font-size: 2rem;
      font-weight: bold;
      color: #FF8C00;
    }
    .stat-label {
      color: #6c757d;
      font-size: 0.9rem;
      text-transform: uppercase;
    }
    .table-card {
      background: white;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .badge-activo {
      background-color: #28a745;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
    }
    .badge-inactivo {
      background-color: #6c757d;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
    }
    .editable-cell {
      cursor: pointer;
      transition: background-color 0.2s;
      padding: 8px !important;
    }
    .editable-cell:hover {
      background-color: #fff3cd !important;
    }
    .cell-editing {
      background-color: #e7f3ff !important;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="main-container">
      
      <!-- Header -->
      <div class="header-card">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <img src="/2/img/logo.jpg" alt="logo" style="height:50px;margin-right:15px;" onerror="this.style.display='none'">
            <div>
              <h3 class="mb-0 text-imbox-dark"><i class="fas fa-users me-2"></i>Gestión de Trabajadores</h3>
              <small class="text-muted">Área de Empaque | Listado de trabajadores del taller - Edición tipo Excel</small>
            </div>
          </div>
          <div>
            <a href="transferencias_ui.php" class="btn btn-outline-warning me-2">
              <i class="fas fa-exchange-alt me-2"></i>Transferencias
            </a>
            <a href="/" class="btn btn-warning">
              <i class="fas fa-home me-2"></i>Inicio
            </a>
          </div>
        </div>
      </div>

      <!-- Estadísticas -->
      <div class="row g-3 mb-4" id="statsRow">
        <!-- Se llenará con JavaScript -->
      </div>

      <!-- Tabla de Trabajadores -->
      <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="text-imbox-dark mb-0">
            <i class="fas fa-list text-imbox-orange me-2"></i>
            Lista de Trabajadores
            <span class="badge bg-secondary ms-2" id="totalCount">0</span>
          </h5>
          <button class="btn btn-success" onclick="agregarTrabajador()">
            <i class="fas fa-plus me-2"></i>Agregar Trabajador
          </button>
        </div>
        
        <div class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i>
          <strong>Edición tipo Excel:</strong> Haz clic en cualquier celda para editarla. 
          Usa <kbd>Enter</kbd> para guardar y bajar, <kbd>Tab</kbd> para siguiente campo, 
          <kbd>Esc</kbd> para cancelar.
        </div>
        
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="trabTable">
            <thead class="table-light">
              <tr>
                <th style="width:60px">#</th>
                <th><i class="fas fa-user me-1"></i>Nombre</th>
                <th><i class="fas fa-phone me-1"></i>Teléfono</th>
                <th><i class="fas fa-envelope me-1"></i>Email</th>
                <th><i class="fas fa-tools me-1"></i>Especialidad</th>
                <th class="text-center">Estado</th>
                <th><i class="fas fa-calendar me-1"></i>Fecha Registro</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- Se llenará con JavaScript -->
            </tbody>
          </table>
        </div>

        <div id="noDataMsg" class="text-center py-5 d-none">
          <i class="fas fa-users-slash fa-4x text-muted mb-3"></i>
          <p class="text-muted">No hay trabajadores registrados</p>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/2/js/excel-table.js"></script>
  <script>
    let excelTable;
    
    // Función para agregar nuevo trabajador
    async function agregarTrabajador() {
      const nombre = prompt('Nombre del trabajador:');
      if (!nombre) return;
      
      const telefono = prompt('Teléfono (opcional):') || '';
      const email = prompt('Email (opcional):') || '';
      const especialidad = prompt('Especialidad (opcional):') || '';
      
      await addNewRow('trabTable', '/2/api/trabajadores.php', {
        nombre: nombre,
        telefono: telefono,
        email: email,
        especialidad: especialidad
      });
    }
    
    // Función para eliminar trabajador
    async function eliminarTrabajador(id, nombre) {
      if (!confirm('¿Seguro que deseas eliminar a ' + nombre + '?')) {
        return;
      }
      
      try {
        const response = await fetch('/2/api/trabajadores.php/' + id, {
          method: 'DELETE'
        });
        
        if (response.ok) {
          showSuccessToast('Trabajador eliminado correctamente');
          setTimeout(() => location.reload(), 1000);
        } else {
          showErrorToast('Error al eliminar el trabajador');
        }
      } catch (error) {
        console.error('Error:', error);
        showErrorToast('Error al eliminar el trabajador');
      }
    }
    
    async function load(){
      try {
        const res = await fetch('/2/api/trabajadores.php');
        const data = await res.json();
        
        // Calcular estadísticas
        const stats = {
          total: data.length,
          activos: data.filter(t => t.activo == 1).length,
          inactivos: data.filter(t => t.activo != 1).length
        };

        // Renderizar estadísticas
        const statsRow = document.getElementById('statsRow');
        statsRow.innerHTML = `
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="fas fa-users fa-2x text-imbox-orange mb-2"></i>
              <div class="stat-number">${stats.total}</div>
              <div class="stat-label">Total Trabajadores</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="fas fa-user-check fa-2x text-success mb-2"></i>
              <div class="stat-number">${stats.activos}</div>
              <div class="stat-label">Activos</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card text-center">
              <i class="fas fa-user-times fa-2x text-secondary mb-2"></i>
              <div class="stat-number">${stats.inactivos}</div>
              <div class="stat-label">Inactivos</div>
            </div>
          </div>
        `;

        // Actualizar contador
        document.getElementById('totalCount').textContent = stats.total;

        // Renderizar tabla
        const tbody = document.querySelector('#trabTable tbody');
        const noDataMsg = document.getElementById('noDataMsg');
        tbody.innerHTML = '';
        
        if (data.length === 0) {
          document.querySelector('.table-responsive').classList.add('d-none');
          noDataMsg.classList.remove('d-none');
          return;
        }

        data.forEach(t => {
          const tr = document.createElement('tr');
          tr.dataset.id = t.id;
          
          const estadoBadge = t.activo == 1 
            ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Activo</span>' 
            : '<span class="badge bg-secondary"><i class="fas fa-times me-1"></i>Inactivo</span>';
          
          const fecha = t.fecha_creacion ? new Date(t.fecha_creacion).toLocaleDateString('es-PE') : '-';
          
          tr.innerHTML = `
            <td class="text-muted"><strong>#${t.id}</strong></td>
            <td class="editable-cell" data-editable="true" data-field="nombre">
              <strong>${escapeHtml(t.nombre || '')}</strong>
            </td>
            <td class="editable-cell" data-editable="true" data-field="telefono">
              ${escapeHtml(t.telefono || '')}
            </td>
            <td class="editable-cell" data-editable="true" data-field="email">
              ${escapeHtml(t.email || '')}
            </td>
            <td class="editable-cell" data-editable="true" data-field="especialidad">
              ${escapeHtml(t.especialidad || '')}
            </td>
            <td class="text-center">${estadoBadge}</td>
            <td><small class="text-muted">${fecha}</small></td>
            <td class="text-center">
              <button class="btn btn-sm btn-danger" onclick="eliminarTrabajador(${t.id}, '${escapeHtml(t.nombre).replace(/'/g, "\\'")}');" title="Eliminar">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          `;
          tbody.appendChild(tr);
        });
        
        // Inicializar tabla editable tipo Excel
        excelTable = new ExcelTable({
          tableId: 'trabTable',
          apiEndpoint: '/2/api/trabajadores.php',
          primaryKey: 'id',
          columns: [
            { field: 'nombre', editable: true, type: 'text' },
            { field: 'telefono', editable: true, type: 'tel' },
            { field: 'email', editable: true, type: 'email' },
            { field: 'especialidad', editable: true, type: 'text' }
          ],
          onSave: (id, field, value) => {
            console.log('Guardado: ID ' + id + ', Campo ' + field + ', Valor ' + value);
          },
          onError: (error) => {
            console.error('Error al guardar:', error);
          }
        });
        
      } catch (error) {
        console.error('Error al cargar trabajadores:', error);
        document.querySelector('.table-responsive').innerHTML = '<div class="alert alert-danger">Error al cargar los datos</div>';
      }
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    load();
  </script>
</body>
</html>
