<?php
/**
 * Ver Transferencias - Área de Corte (Solo Lectura)
 * Lista de transferencias enviadas a Empaque
 */

// Obtener transferencias desde Empaque
$transferencias = [];
$error_msg = '';

try {
    $api_url = 'http://localhost/2/api/transferencias.php?almacen_origen=1';  // API Empaque
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            $transferencias = $data['data'] ?? [];
        }
    }
} catch (Exception $e) {
    $error_msg = 'Error al obtener transferencias: ' . $e->getMessage();
}

// Estadísticas
$stats = [
    'total' => count($transferencias),
    'pendiente' => 0,
    'recibido' => 0,
    'completado' => 0
];

foreach ($transferencias as $t) {
    $estado = strtolower($t['estado'] ?? 'pendiente');
    if (isset($stats[$estado])) {
        $stats[$estado]++;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Corte | Ver Transferencias</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/theme-orange.css">
  <style>
    /* Pantalla de carga */
    #loading-screen {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, #FF8C00 0%, #FFB84D 50%, #FFA500 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      transition: opacity 0.5s ease, visibility 0.5s ease;
    }
    
    #loading-screen.hidden {
      opacity: 0;
      visibility: hidden;
    }
    
    .loader {
      width: 80px;
      height: 80px;
      border: 8px solid rgba(255, 255, 255, 0.3);
      border-top: 8px solid white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .loading-text {
      margin-top: 30px;
      color: white;
      font-size: 1.5em;
      font-weight: 600;
      text-shadow: 0 2px 4px rgba(0,0,0,0.2);
      animation: fadeInOut 2s ease-in-out infinite;
    }
    
    @keyframes fadeInOut {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }
    
    .loading-progress {
      width: 200px;
      height: 4px;
      background: rgba(255,255,255,0.3);
      border-radius: 10px;
      margin-top: 20px;
      overflow: hidden;
    }
    
    .loading-progress-bar {
      height: 100%;
      background: white;
      border-radius: 10px;
      animation: progress 2s ease-in-out infinite;
    }
    
    @keyframes progress {
      0% { width: 0%; }
      50% { width: 70%; }
      100% { width: 100%; }
    }
    
    .loading-logo {
      width: 100px;
      height: 100px;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      animation: pulse 2s ease-in-out infinite;
    }
    
    .loading-logo i {
      font-size: 2.5em;
      color: #FF8C00;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    
    body {
      background: linear-gradient(135deg, #FF8C00 0%, #FFB84D 50%, #FFA500 100%);
      min-height: 100vh;
    }
    .badge-readonly {
      background-color: #6c757d;
      padding: 4px 8px;
      font-size: 0.75rem;
    }
    .alert-info-custom {
      background: #e7f3ff;
      border-left: 4px solid #0d6efd;
      padding: 15px;
      border-radius: 5px;
    }
  </style>
</head>
<body>
  <!-- Pantalla de Carga -->
  <div id="loading-screen">
    <div class="loading-logo">
      <i class="fas fa-list-ul"></i>
    </div>
    <div class="loader"></div>
    <div class="loading-text">Cargando Transferencias...</div>
    <div class="loading-progress">
      <div class="loading-progress-bar"></div>
    </div>
    <div style="margin-top: 15px; color: rgba(255,255,255,0.8); font-size: 0.9em;">
      <i class="fas fa-warehouse"></i> Almacén 1 - Área de Corte
    </div>
    <div style="margin-top: 8px; color: rgba(255,255,255,0.6); font-size: 0.85em;">
      Sistema IMBOX v1.0
    </div>
  </div>

  <div class="container-fluid">
    <div class="main-container" style="max-width: 1400px; margin: 30px auto; padding: 0 20px;">
      
      <!-- Header -->
      <div class="header-card">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <img src="/1/img/logo.jpg" alt="Logo" style="height:50px;margin-right:15px;" onerror="this.style.display='none'">
            <div>
              <h3 class="mb-0 text-imbox-dark">
                <i class="fas fa-list-ul me-2"></i>Transferencias Enviadas
              </h3>
              <small class="text-muted">Área de Corte | Vista de Solo Lectura</small>
            </div>
          </div>
          <div>
            <a href="index.php" class="btn btn-info me-2">
              <i class="fas fa-tachometer-alt me-2"></i>Panel de Control
            </a>
            <a href="transferencias.php" class="btn btn-warning me-2">
              <i class="fas fa-paper-plane me-2"></i>Nueva Transferencia
            </a>
            <a href="control_entrada.php" class="btn btn-outline-warning">
              <i class="fas fa-clipboard-check me-2"></i>Control de Entrada
            </a>
          </div>
        </div>
      </div>

      <!-- Alerta de Solo Lectura -->
      <div class="alert-info-custom mb-4">
        <div class="d-flex align-items-center">
          <i class="fas fa-info-circle fa-2x text-primary me-3"></i>
          <div>
            <strong>Vista de Solo Lectura</strong><br>
            <small>Aquí puedes ver las transferencias que has enviado a Empaque. No puedes editarlas desde aquí. 
            Para modificaciones, contacta al área de Empaque.</small>
          </div>
        </div>
      </div>

      <?php if ($error_msg): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_msg); ?>
        </div>
      <?php endif; ?>

      <!-- Estadísticas -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-paper-plane fa-2x text-imbox-orange mb-2"></i>
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Enviadas</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
            <div class="stat-number"><?php echo $stats['pendiente']; ?></div>
            <div class="stat-label">Pendientes</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-box-open fa-2x text-info mb-2"></i>
            <div class="stat-number"><?php echo $stats['recibido']; ?></div>
            <div class="stat-label">Recibidas</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
            <div class="stat-number"><?php echo $stats['completado']; ?></div>
            <div class="stat-label">Completadas</div>
          </div>
        </div>
      </div>

      <!-- Tabla de Transferencias -->
      <div class="table-card">
        <h5 class="text-imbox-dark mb-4">
          <i class="fas fa-exchange-alt text-imbox-orange me-2"></i>
          Historial de Transferencias
          <span class="badge bg-secondary ms-2"><?php echo $stats['total']; ?></span>
        </h5>

        <?php if (empty($transferencias)): ?>
          <div class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <p class="text-muted">No hay transferencias enviadas</p>
            <a href="index.php" class="btn btn-warning mt-2">
              <i class="fas fa-plus me-2"></i>Crear Primera Transferencia
            </a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width:60px">#</th>
                  <th><i class="fas fa-barcode me-1"></i>Referencia</th>
                  <th><i class="fas fa-warehouse me-1"></i>Destino</th>
                  <th class="text-center"><i class="fas fa-boxes me-1"></i>Items</th>
                  <th><i class="fas fa-tshirt me-1"></i>Detalles</th>
                  <th><i class="fas fa-user-tie me-1"></i>Trabajador</th>
                  <th class="text-center">Estado</th>
                  <th><i class="fas fa-clock me-1"></i>Fecha</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($transferencias as $t): ?>
                  <tr>
                    <td class="text-muted"><strong>#<?php echo $t['id']; ?></strong></td>
                    <td>
                      <strong class="text-imbox-orange"><?php echo htmlspecialchars($t['referencia']); ?></strong>
                      <?php if (!empty($t['observaciones'])): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($t['observaciones'], 0, 30)); ?>...</small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <small class="text-muted">Área de Empaque</small>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-primary"><?php echo $t['total_items']; ?></span>
                    </td>
                    <td>
                      <?php if (!empty($t['tipo_prenda']) || !empty($t['color']) || !empty($t['talla'])): ?>
                        <small>
                          <?php if (!empty($t['tipo_prenda'])): ?>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($t['tipo_prenda']); ?></span>
                          <?php endif; ?>
                          <?php if (!empty($t['color'])): ?>
                            <span class="badge bg-info"><?php echo htmlspecialchars($t['color']); ?></span>
                          <?php endif; ?>
                          <?php if (!empty($t['talla'])): ?>
                            <span class="badge bg-dark"><?php echo htmlspecialchars($t['talla']); ?></span>
                          <?php endif; ?>
                        </small>
                      <?php else: ?>
                        <small class="text-muted">-</small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($t['trabajador_nombre'])): ?>
                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($t['trabajador_nombre']); ?>
                      <?php else: ?>
                        <small class="text-muted">Sin asignar</small>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <?php 
                        $estado = strtolower($t['estado'] ?? 'pendiente');
                        $badge_class = 'warning';
                        $icon = 'clock';
                        if ($estado === 'recibido') {
                          $badge_class = 'info';
                          $icon = 'box-open';
                        } elseif ($estado === 'completado') {
                          $badge_class = 'success';
                          $icon = 'check-circle';
                        }
                      ?>
                      <span class="badge bg-<?php echo $badge_class; ?>">
                        <i class="fas fa-<?php echo $icon; ?> me-1"></i><?php echo ucfirst($estado); ?>
                      </span>
                    </td>
                    <td>
                      <small class="text-muted">
                        <?php echo date('d/m/Y', strtotime($t['fecha_creacion'] ?? 'now')); ?>
                        <br><?php echo date('H:i', strtotime($t['fecha_creacion'] ?? 'now')); ?>
                      </small>
                    </td>
                    <td class="text-center">
                      <span class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Solo visualización
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Info -->
      <div class="mt-4 p-4 bg-light rounded border border-info">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h6 class="text-info mb-2">
              <i class="fas fa-question-circle me-2"></i>¿Necesitas Hacer Cambios?
            </h6>
            <p class="mb-0">
              <small class="text-muted">
                Las transferencias solo pueden ser editadas por el área de Empaque. 
                Si necesitas corregir algo, contacta directamente al área de Empaque o 
                <a href="http://localhost/2/" target="_blank">accede al sistema de Empaque</a>.
              </small>
            </p>
          </div>
          <div class="col-md-4 text-end">
            <a href="index.php" class="btn btn-warning">
              <i class="fas fa-plus-circle me-1"></i>Nueva Transferencia
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Ocultar pantalla de carga
    window.addEventListener('load', function() {
      setTimeout(function() {
        const loadingScreen = document.getElementById('loading-screen');
        loadingScreen.classList.add('hidden');
        setTimeout(function() {
          loadingScreen.remove();
        }, 500);
      }, 300);
    });
    
    setTimeout(function() {
      const loadingScreen = document.getElementById('loading-screen');
      if (loadingScreen) {
        loadingScreen.classList.add('hidden');
        setTimeout(function() {
          loadingScreen.remove();
        }, 500);
      }
    }, 5000);
    
    // Auto-refresh cada 30 segundos
    setTimeout(function() {
      location.reload();
    }, 30000);
  </script>
</body>
</html>
