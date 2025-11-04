<?php
/**
 * Control de Entrada - Área de Corte
 * Gestión de entradas de materiales
 */

require_once 'config.php';

$db = getDB();
$mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'crear') {
        $referencia = trim($_POST['referencia'] ?? '');
        $fecha_entrada = $_POST['fecha_entrada'] ?? date('Y-m-d');
        $proveedor = trim($_POST['proveedor'] ?? '');
        $orden_compra = trim($_POST['orden_compra'] ?? '');
        $total_rollos = intval($_POST['total_rollos'] ?? 0);
        $total_metros = floatval($_POST['total_metros'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');
        $usuario_creacion = 'almacen1';
        
        if (!empty($referencia)) {
            try {
                $stmt = $db->prepare("INSERT INTO controles_entrada 
                    (referencia, fecha_entrada, proveedor, orden_compra, total_rollos, total_metros, observaciones, usuario_creacion)
                    VALUES (:ref, :fecha, :prov, :orden, :rollos, :metros, :obs, :user)");
                
                $stmt->bindValue(':ref', $referencia, PDO::PARAM_STR);
                $stmt->bindValue(':fecha', $fecha_entrada, PDO::PARAM_STR);
                $stmt->bindValue(':prov', $proveedor, PDO::PARAM_STR);
                $stmt->bindValue(':orden', $orden_compra, PDO::PARAM_STR);
                $stmt->bindValue(':rollos', $total_rollos, PDO::PARAM_INT);
                $stmt->bindValue(':metros', $total_metros, PDO::PARAM_STR);
                $stmt->bindValue(':obs', $observaciones, PDO::PARAM_STR);
                $stmt->bindValue(':user', $usuario_creacion, PDO::PARAM_STR);
                
                $stmt->execute();
                $mensaje = '<div class="alert alert-success">Control de entrada creado correctamente</div>';
                
            } catch (Exception $e) {
                $mensaje = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            }
        }
    }
}

// Obtener lista de controles
$stmt = $db->query("SELECT * FROM controles_entrada ORDER BY id DESC LIMIT 50");
$controles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas
$stats = [
    'total' => 0,
    'pendiente' => 0,
    'completado' => 0,
    'total_rollos' => 0,
    'total_metros' => 0
];

foreach ($controles as $c) {
    $stats['total']++;
    if ($c['estado'] === 'pendiente') $stats['pendiente']++;
    if ($c['estado'] === 'completado') $stats['completado']++;
    $stats['total_rollos'] += intval($c['total_rollos']);
    $stats['total_metros'] += floatval($c['total_metros']);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Corte | Control de Entrada</title>
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
    .main-container {
      max-width: 1400px;
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
      letter-spacing: 0.5px;
    }
    .form-card {
      background: white;
      border-radius: 10px;
      padding: 25px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .table-card {
      background: white;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .btn-save {
      background: #FF8C00;
      border-color: #FF8C00;
      color: white;
      padding: 10px 30px;
      font-weight: 600;
    }
    .btn-save:hover {
      background: #e67e00;
      border-color: #e67e00;
      color: white;
    }
    .badge-status {
      padding: 6px 12px;
      font-size: 0.85rem;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <!-- Pantalla de Carga -->
  <div id="loading-screen">
    <div class="loading-logo">
      <i class="fas fa-clipboard-check"></i>
    </div>
    <div class="loader"></div>
    <div class="loading-text">Cargando Control de Entrada...</div>
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
    <div class="main-container">
      
      <!-- Header -->
      <div class="header-card">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <img src="/1/img/logo.jpg" alt="Logo" style="height:50px;margin-right:15px;" onerror="this.style.display='none'">
            <div>
              <h3 class="mb-0 text-imbox-dark"><i class="fas fa-clipboard-check me-2"></i>Control de Entrada</h3>
              <small class="text-muted">Área de Corte | Registro de materiales</small>
            </div>
          </div>
          <div>
            <a href="index.php" class="btn btn-info me-2">
              <i class="fas fa-tachometer-alt me-2"></i>Panel de Control
            </a>
            <a href="transferencias.php" class="btn btn-warning me-2">
              <i class="fas fa-paper-plane me-2"></i>Nueva Transferencia
            </a>
            <a href="ver_transferencias.php" class="btn btn-outline-primary">
              <i class="fas fa-list-ul me-2"></i>Ver Transferencias
            </a>
          </div>
        </div>
      </div>

      <?php if ($mensaje): ?>
        <div class="mb-3"><?php echo $mensaje; ?></div>
      <?php endif; ?>

      <!-- Estadísticas -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-clipboard-list fa-2x text-imbox-orange mb-2"></i>
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Controles</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i>
            <div class="stat-number"><?php echo $stats['pendiente']; ?></div>
            <div class="stat-label">Pendientes</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-boxes fa-2x text-success mb-2"></i>
            <div class="stat-number"><?php echo number_format($stats['total_rollos']); ?></div>
            <div class="stat-label">Total Rollos</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-center">
            <i class="fas fa-ruler fa-2x text-info mb-2"></i>
            <div class="stat-number"><?php echo number_format($stats['total_metros'], 1); ?></div>
            <div class="stat-label">Total Metros</div>
          </div>
        </div>
      </div>

      <!-- Formulario Nuevo Control -->
      <div class="form-card mb-4">
        <h5 class="text-imbox-dark mb-4">
          <i class="fas fa-plus-circle text-imbox-orange me-2"></i>
          Nuevo Control de Entrada
        </h5>
        <form method="post" id="formControl">
          <input type="hidden" name="action" value="crear">
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">
                <i class="fas fa-barcode me-1"></i>Referencia <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control form-control-lg" name="referencia" 
                     value="CE-<?php echo date('Ymd-His'); ?>" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label fw-bold">
                <i class="fas fa-calendar me-1"></i>Fecha de Entrada
              </label>
              <input type="date" class="form-control form-control-lg" name="fecha_entrada" 
                     value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="col-md-6">
              <label class="form-label fw-bold">
                <i class="fas fa-truck me-1"></i>Proveedor
              </label>
              <input type="text" class="form-control" name="proveedor" 
                     placeholder="Nombre del proveedor">
            </div>
            
            <div class="col-md-6">
              <label class="form-label fw-bold">
                <i class="fas fa-file-invoice me-1"></i>Orden de Compra
              </label>
              <input type="text" class="form-control" name="orden_compra" 
                     placeholder="Número de OC">
            </div>
            
            <div class="col-md-6">
              <label class="form-label fw-bold">
                <i class="fas fa-boxes me-1"></i>Total Rollos
              </label>
              <input type="number" class="form-control" name="total_rollos" 
                     min="0" value="0" placeholder="0">
            </div>
            
            <div class="col-md-6">
              <label class="form-label fw-bold">
                <i class="fas fa-ruler me-1"></i>Total Metros
              </label>
              <input type="number" step="0.01" class="form-control" name="total_metros" 
                     min="0" value="0" placeholder="0.00">
            </div>
            
            <div class="col-12">
              <label class="form-label fw-bold">
                <i class="fas fa-comment-alt me-1"></i>Observaciones
              </label>
              <textarea class="form-control" name="observaciones" rows="3" 
                        placeholder="Detalles adicionales del material recibido..."></textarea>
            </div>
            
            <div class="col-12 text-end">
              <button type="reset" class="btn btn-outline-secondary me-2">
                <i class="fas fa-times me-2"></i>Limpiar
              </button>
              <button type="submit" class="btn btn-save">
                <i class="fas fa-save me-2"></i>Guardar Control
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Lista de Controles -->
      <div class="table-card">
        <h5 class="text-imbox-dark mb-4">
          <i class="fas fa-list text-imbox-orange me-2"></i>
          Controles Registrados
          <span class="badge bg-secondary ms-2"><?php echo $stats['total']; ?></span>
        </h5>
        
        <?php if (empty($controles)): ?>
          <div class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <p class="text-muted">No hay controles de entrada registrados</p>
            <small class="text-muted">Crea tu primer control usando el formulario arriba</small>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width:60px">#</th>
                  <th><i class="fas fa-barcode me-1"></i>Referencia</th>
                  <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                  <th><i class="fas fa-truck me-1"></i>Proveedor</th>
                  <th><i class="fas fa-file-invoice me-1"></i>OC</th>
                  <th class="text-center"><i class="fas fa-boxes me-1"></i>Rollos</th>
                  <th class="text-center"><i class="fas fa-ruler me-1"></i>Metros</th>
                  <th class="text-center">Estado</th>
                  <th><i class="fas fa-clock me-1"></i>Creado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($controles as $control): ?>
                  <tr>
                    <td class="text-muted"><strong>#<?php echo $control['id']; ?></strong></td>
                    <td>
                      <strong class="text-imbox-orange"><?php echo htmlspecialchars($control['referencia']); ?></strong>
                      <?php if (!empty($control['observaciones'])): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($control['observaciones'], 0, 40)); ?>...</small>
                      <?php endif; ?>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($control['fecha_entrada'])); ?></td>
                    <td><?php echo htmlspecialchars($control['proveedor']) ?: '-'; ?></td>
                    <td><?php echo htmlspecialchars($control['orden_compra']) ?: '-'; ?></td>
                    <td class="text-center">
                      <span class="badge bg-success"><?php echo number_format($control['total_rollos']); ?></span>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-info"><?php echo number_format($control['total_metros'], 2); ?> m</span>
                    </td>
                    <td class="text-center">
                      <?php if ($control['estado'] === 'completado'): ?>
                        <span class="badge badge-status bg-success">
                          <i class="fas fa-check-circle me-1"></i>Completado
                        </span>
                      <?php else: ?>
                        <span class="badge badge-status bg-warning text-dark">
                          <i class="fas fa-hourglass-half me-1"></i>Pendiente
                        </span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <small class="text-muted">
                        <?php echo date('d/m/Y', strtotime($control['fecha_creacion'])); ?>
                        <br><?php echo date('H:i', strtotime($control['fecha_creacion'])); ?>
                      </small>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
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
    
    // Confirmación antes de guardar
    document.getElementById('formControl').addEventListener('submit', function(e) {
      const ref = document.querySelector('input[name="referencia"]').value;
      const prov = document.querySelector('input[name="proveedor"]').value;
      const rollos = document.querySelector('input[name="total_rollos"]').value;
      const metros = document.querySelector('input[name="total_metros"]').value;
      
      const msg = `¿Confirmar registro del control de entrada?\n\n` +
                  `Referencia: ${ref}\n` +
                  `Proveedor: ${prov || 'Sin especificar'}\n` +
                  `Rollos: ${rollos} | Metros: ${metros}`;
      
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });

    // Auto-fade de mensajes de éxito
    const alertas = document.querySelectorAll('.alert-success');
    alertas.forEach(alert => {
      setTimeout(() => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
      }, 3000);
    });

    // Scroll suave al formulario después de éxito
    <?php if ($mensaje && strpos($mensaje, 'success') !== false): ?>
      setTimeout(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }, 100);
    <?php endif; ?>
  </script>
</body>
</html>
