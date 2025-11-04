<?php
// Gestión de Trabajadores - Área de Empaque
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Empaque | Trabajadores</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/2/css/theme-orange.css">
  <link rel="stylesheet" href="/2/css/styles.css">
  <style>
    @keyframes gradientShift { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
    body { background: linear-gradient(-45deg, #FF8C00, #FFB84D, #FFA500, #FF9933) !important; background-size: 400% 400% !important; animation: gradientShift 15s ease infinite !important; }
    body::before, body::after { content: ''; position: fixed; width: 400px; height: 400px; border-radius: 50%; opacity: 0.1; z-index: 0; }
    body::before { background: radial-gradient(circle, white, transparent); top: -200px; left: -200px; animation: float 20s ease-in-out infinite; }
    body::after { background: radial-gradient(circle, white, transparent); bottom: -200px; right: -200px; animation: float 25s ease-in-out infinite reverse; }
    @keyframes float { 0%,100%{transform:translate(0,0) scale(1)} 25%{transform:translate(50px,-50px) scale(1.1)} 50%{transform:translate(-30px,30px) scale(0.9)} 75%{transform:translate(40px,20px) scale(1.05)} }
    @keyframes fadeInDown { from{opacity:0;transform:translateY(-30px)} to{opacity:1;transform:translateY(0)} }
    @keyframes fadeInUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
    @keyframes logoFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
    .header-card { animation: fadeInDown 0.6s ease-out !important; position: relative; z-index: 10; }
    .stat-card { animation: fadeInUp 0.6s ease-out backwards !important; }
    .stat-card:nth-child(1) { animation-delay: 0.1s !important; }
    .stat-card:nth-child(2) { animation-delay: 0.2s !important; }
    .stat-card:nth-child(3) { animation-delay: 0.3s !important; }
    .table-card { animation: fadeInUp 0.6s ease-out 0.4s backwards !important; }
    ::selection { background: #FF8C00; color: white; }
    ::-webkit-scrollbar { width: 10px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #FF8C00, #FFA500); border-radius: 10px; }
  </style>
</head>
<body>
  <?php $loading_message = 'Cargando Trabajadores...'; ?>
  <?php include __DIR__ . '/includes/loading_screen.php'; ?>
  
  <div class="container-fluid">
    <div class="main-container">
      
      <!-- Header -->
      <div class="header-card fade-in">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="d-flex align-items-center">
            <img src="/2/img/logo.jpg" alt="logo IMBOX" style="height:60px;margin-right:15px;filter:drop-shadow(0 3px 10px rgba(255,140,0,0.3));animation:logoFloat 3s ease-in-out infinite;" onerror="this.style.display='none'">
            <div>
              <h2 class="mb-1 text-orange"><i class="fas fa-users me-2"></i>Gestión de Trabajadores</h2>
              <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Área de Empaque | Administrar trabajadores, costureros y su asignación</small>
            </div>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <a href="transferencias_ui.php" class="btn btn-outline-orange">
              <i class="fas fa-exchange-alt me-2"></i>Transferencias
            </a>
            <a href="index.php" class="btn btn-orange">
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
        <h5 class="text-imbox-dark mb-4">
          <i class="fas fa-list text-imbox-orange me-2"></i>
          Lista de Trabajadores
          <span class="badge bg-secondary ms-2" id="totalCount">0</span>
        </h5>
        
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="trabTable">
            <thead class="table-light">
              <tr>
                <th style="width:60px">#</th>
                <th><i class="fas fa-user me-1"></i>Nombre</th>
                <th><i class="fas fa-phone me-1"></i>Teléfono</th>
                <th><i class="fas fa-envelope me-1"></i>Email</th>
                <th class="text-center">Estado</th>
                <th><i class="fas fa-calendar me-1"></i>Fecha Registro</th>
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
  <script>
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
          const estadoBadge = t.activo == 1 
            ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Activo</span>' 
            : '<span class="badge bg-secondary"><i class="fas fa-times me-1"></i>Inactivo</span>';
          
          const fecha = t.fecha_registro ? new Date(t.fecha_registro).toLocaleDateString('es-PE') : '-';
          
          tr.innerHTML = `
            <td class="text-muted"><strong>#${t.id}</strong></td>
            <td>
              <strong>${escapeHtml(t.nombre)}</strong>
              ${t.activo == 1 ? '<i class="fas fa-circle text-success ms-1" style="font-size:0.5rem"></i>' : ''}
            </td>
            <td>
              ${t.telefono ? '<i class="fas fa-phone text-muted me-1"></i>' + escapeHtml(t.telefono) : '<span class="text-muted">-</span>'}
            </td>
            <td>
              ${t.email ? '<i class="fas fa-envelope text-muted me-1"></i>' + escapeHtml(t.email) : '<span class="text-muted">-</span>'}
            </td>
            <td class="text-center">${estadoBadge}</td>
            <td><small class="text-muted">${fecha}</small></td>
          `;
          tbody.appendChild(tr);
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
