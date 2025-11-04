<?php
/**
 * Vista de Gestión de Modelos - Área de Empaque
 */
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Empaque | Modelos</title>
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
    .table-card { animation: fadeInUp 0.6s ease-out 0.4s backwards !important; }
    .model-card { 
      background: white; 
      border-radius: 12px; 
      padding: 20px; 
      margin-bottom: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      animation: fadeInUp 0.5s ease-out backwards;
    }
    .model-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(255,140,0,0.3);
    }
    ::selection { background: #FF8C00; color: white; }
    ::-webkit-scrollbar { width: 10px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #FF8C00, #FFA500); border-radius: 10px; }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="main-container">
      
      <!-- Header -->
      <div class="header-card fade-in">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="d-flex align-items-center">
            <img src="/2/img/logo.jpg" alt="logo IMBOX" style="height:60px;margin-right:15px;filter:drop-shadow(0 3px 10px rgba(255,140,0,0.3));animation:logoFloat 3s ease-in-out infinite;" onerror="this.style.display='none'">
            <div>
              <h2 class="mb-1 text-orange"><i class="fas fa-tag me-2"></i>Catálogo de Modelos</h2>
              <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Área de Empaque | Ver y gestionar catálogo de modelos y especificaciones</small>
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

      <!-- Filtros y Búsqueda -->
      <div class="table-card mb-4">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control" placeholder="Buscar por modelo, referencia, color...">
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select">
              <option value="">Todos los tipos</option>
              <option value="polo">Polo</option>
              <option value="camisa">Camisa</option>
              <option value="pantalon">Pantalón</option>
              <option value="short">Short</option>
            </select>
          </div>
          <div class="col-md-3">
            <button class="btn btn-orange w-100">
              <i class="fas fa-plus me-2"></i>Nuevo Modelo
            </button>
          </div>
        </div>
      </div>

      <!-- Lista de Modelos -->
      <div class="row">
        <div class="col-12">
          <div class="table-card">
            <h5 class="mb-4">
              <i class="fas fa-list text-imbox-orange me-2"></i>
              Listado de Modelos
              <span class="badge bg-secondary ms-2">0</span>
            </h5>

            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Próximamente:</strong> Sistema de gestión de modelos en desarrollo. 
              Por ahora puedes ver los modelos directamente en las transferencias.
            </div>

            <!-- Ejemplo de cards de modelos -->
            <div class="row g-3">
              <div class="col-md-4">
                <div class="model-card">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="text-orange mb-0"><i class="fas fa-tshirt me-2"></i>Polo Slim Fit</h6>
                    <span class="badge bg-success">Activo</span>
                  </div>
                  <p class="text-muted small mb-2"><strong>Referencia:</strong> PSF-001</p>
                  <p class="text-muted small mb-2"><strong>Material:</strong> Algodón 100%</p>
                  <p class="text-muted small mb-3"><strong>Colores:</strong> Azul, Negro, Blanco</p>
                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>Ver</button>
                    <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit me-1"></i>Editar</button>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="model-card">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="text-orange mb-0"><i class="fas fa-tshirt me-2"></i>Camisa Italiana</h6>
                    <span class="badge bg-success">Activo</span>
                  </div>
                  <p class="text-muted small mb-2"><strong>Referencia:</strong> CI-002</p>
                  <p class="text-muted small mb-2"><strong>Material:</strong> Algodón premium</p>
                  <p class="text-muted small mb-3"><strong>Colores:</strong> Variados</p>
                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>Ver</button>
                    <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit me-1"></i>Editar</button>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="model-card">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="text-orange mb-0"><i class="fas fa-tshirt me-2"></i>Short Nacarado</h6>
                    <span class="badge bg-success">Activo</span>
                  </div>
                  <p class="text-muted small mb-2"><strong>Referencia:</strong> SN-003</p>
                  <p class="text-muted small mb-2"><strong>Material:</strong> Microfibra</p>
                  <p class="text-muted small mb-3"><strong>Colores:</strong> Negro, Azul</p>
                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>Ver</button>
                    <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit me-1"></i>Editar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    console.log('%c🏷️ Catálogo de Modelos - Área de Empaque', 
      'font-size: 18px; font-weight: bold; background: linear-gradient(90deg, #FF8C00, #FFA500); ' +
      'color: white; padding: 10px 20px; border-radius: 10px;');
  </script>
</body>
</html>
