<?php
/**
 * Nueva Transferencia - Área de Corte
 * Sistema IMBOX - Envío de prendas a Empaque
 */

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
require_once 'config.php';

// Configuración
if (!defined('TARGET_URL')) {
    define('TARGET_URL', getenv('TARGET_URL') ?: 'http://localhost/2/api/transferencias.php');
}
if (!defined('API_KEY')) {
    define('API_KEY', getenv('API_KEY') ?: '1c810efe778ea94df3578a92e7ed6f9dfa28621cfa67944e2535e8460d05e255');
}

$result = null;
$success = false;
$error = null;

// Obtener controles de entrada disponibles
$db = getDB();
try {
    $stmt = $db->query("SELECT id, referencia, fecha_entrada, total_rollos, total_metros FROM controles_entrada ORDER BY id DESC LIMIT 50");
    $controles_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener trabajadores
    $trabajadores = [];
    $workers_file = __DIR__ . '/data/trabajadores.json';
    if (file_exists($workers_file)) {
        $trabajadores = json_decode(file_get_contents($workers_file), true) ?: [];
    }
    
    // Estadísticas rápidas
    $stmt_stats = $db->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado='pendiente' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado='completado' THEN 1 ELSE 0 END) as completados,
        SUM(total_rollos) as total_rollos
    FROM controles_entrada");
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
    $controles_disponibles = [];
    $trabajadores = [];
    $stats = ['total' => 0, 'pendientes' => 0, 'completados' => 0, 'total_rollos' => 0];
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'referencia' => trim($_POST['referencia'] ?? ''),
        'almacen_origen_id' => 1,
        'almacen_destino_id' => 2,
        'control_entrada_id' => !empty($_POST['control_entrada_id']) ? intval($_POST['control_entrada_id']) : null,
        'total_items' => intval($_POST['total_items'] ?? 0),
        'usuario_creacion' => trim($_POST['usuario_creacion'] ?? 'corte_almacen1'),
        'observaciones' => trim($_POST['observaciones'] ?? ''),
        'tipo_prenda' => !empty($_POST['tipo_prenda']) ? trim($_POST['tipo_prenda']) : null,
        'color' => !empty($_POST['color']) ? trim($_POST['color']) : null,
        'talla' => !empty($_POST['talla']) ? trim($_POST['talla']) : null,
    ];
    
    // Buscar trabajador por ID
    if (!empty($_POST['trabajador_id'])) {
        $data['trabajador_id'] = intval($_POST['trabajador_id']);
        foreach ($trabajadores as $t) {
            if ($t['id'] == $data['trabajador_id']) {
                $data['trabajador_nombre'] = $t['nombre'];
                break;
            }
        }
    }

    // Enviar a API
    $ch = curl_init(TARGET_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-Key: ' . API_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $resp = curl_exec($ch);
    $curl_error = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = [
        'http_code' => $code,
        'curl_error' => $curl_error ?: null,
        'json' => json_decode($resp, true),
        'raw' => $resp
    ];
    
    if ($code == 200 && !$curl_error) {
        $success = true;
    } else {
        $error = "Error al enviar transferencia: " . ($curl_error ?: "Código HTTP: $code");
    }
}

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Transferencia | Área de Corte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --imbox-orange: #FF8C00;
            --imbox-dark: #2C2C2C;
            --imbox-light: #FFB84D;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .header-card {
            background: linear-gradient(135deg, var(--imbox-orange) 0%, #FFA500 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(255,140,0,0.3);
            color: white;
        }
        
        .header-card h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 600;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-left: 5px solid var(--imbox-orange);
            transition: all 0.3s ease;
            height: 100%;
            text-align: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(255,140,0,0.2);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--imbox-orange);
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-top: 8px;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--imbox-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--imbox-orange);
            display: inline-block;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--imbox-orange);
            box-shadow: 0 0 0 3px rgba(255,140,0,0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--imbox-orange) 0%, #FFA500 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,140,0,0.3);
            background: linear-gradient(135deg, #E67E00 0%, var(--imbox-orange) 100%);
        }
        
        .btn-secondary {
            background: white;
            border: 2px solid var(--imbox-orange);
            color: var(--imbox-orange);
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background: var(--imbox-orange);
            color: white;
        }
        
        .alert-custom {
            border-radius: 10px;
            padding: 20px;
            border-left: 5px solid;
        }
        
        .alert-custom.success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .alert-custom.error {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .required::after {
            content: " *";
            color: #dc3545;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .quick-select-card {
            background: #fff9f0;
            border: 2px dashed var(--imbox-orange);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .control-option {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .control-option:hover {
            border-color: var(--imbox-orange);
            background: #fff9f0;
            transform: translateX(5px);
        }
        
        .control-option.selected {
            background: var(--imbox-orange);
            color: white;
            border-color: var(--imbox-orange);
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1>
                        <i class="fas fa-paper-plane me-3"></i>
                        Nueva Transferencia
                    </h1>
                    <p class="mb-0 mt-2" style="opacity: 0.9;">
                        <i class="fas fa-layer-group me-2"></i>
                        Área de Corte → Empaque
                    </p>
                </div>
                <div>
                    <a href="ver_transferencias.php" class="btn btn-light me-2">
                        <i class="fas fa-list me-2"></i>
                        Ver Transferencias
                    </a>
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-home me-2"></i>
                        Panel Principal
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-clipboard-list fa-2x text-muted mb-3" style="opacity: 0.3;"></i>
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Controles</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-clock fa-2x text-warning mb-3" style="opacity: 0.3;"></i>
                    <div class="stat-number"><?php echo $stats['pendientes']; ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-check-circle fa-2x text-success mb-3" style="opacity: 0.3;"></i>
                    <div class="stat-number"><?php echo $stats['completados']; ?></div>
                    <div class="stat-label">Completados</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-boxes fa-2x text-info mb-3" style="opacity: 0.3;"></i>
                    <div class="stat-number"><?php echo number_format($stats['total_rollos']); ?></div>
                    <div class="stat-label">Total Rollos</div>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($success): ?>
        <div class="alert-custom success mb-4">
            <h5><i class="fas fa-check-circle me-2"></i>¡Transferencia Enviada Exitosamente!</h5>
            <p class="mb-0">La transferencia ha sido registrada y enviada al área de Empaque.</p>
            <?php if (isset($result['json']['id'])): ?>
            <p class="mb-0 mt-2">
                <strong>ID de Transferencia:</strong> #<?php echo $result['json']['id']; ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert-custom error mb-4">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
            <p class="mb-0"><?php echo h($error); ?></p>
        </div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="form-card">
            <form method="POST" id="transferForm">
                
                <!-- Sección 1: Información Básica -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-info-circle me-2"></i>
                        Información Básica
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Referencia</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="referencia" 
                                   id="referencia"
                                   placeholder="Ej: COR-20251102-001"
                                   value="<?php echo isset($_POST['referencia']) ? h($_POST['referencia']) : 'COR-' . date('Ymd') . '-' . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT); ?>"
                                   required>
                            <small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                Código único para identificar esta transferencia
                            </small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label required">Total de Items</label>
                            <input type="number" 
                                   class="form-control" 
                                   name="total_items" 
                                   id="total_items"
                                   min="1"
                                   placeholder="Cantidad de prendas"
                                   required>
                            <small class="text-muted">
                                <i class="fas fa-boxes me-1"></i>
                                Número total de prendas a transferir
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Control de Entrada (Opcional) -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-link me-2"></i>
                        Vincular a Control de Entrada (Opcional)
                    </div>
                    
                    <div class="info-box">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Sugerencia:</strong> Puedes vincular esta transferencia a un control de entrada existente para mejor trazabilidad.
                    </div>
                    
                    <div class="quick-select-card">
                        <h6 class="mb-3">
                            <i class="fas fa-mouse-pointer me-2"></i>
                            Selección Rápida de Controles
                        </h6>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach (array_slice($controles_disponibles, 0, 5) as $control): ?>
                            <div class="control-option" 
                                 onclick="selectControl(<?php echo $control['id']; ?>, '<?php echo h($control['referencia']); ?>')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>#<?php echo $control['id']; ?> - <?php echo h($control['referencia']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($control['fecha_entrada'])); ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-info"><?php echo $control['total_rollos']; ?> rollos</span>
                                        <br>
                                        <small class="text-muted"><?php echo number_format($control['total_metros'], 2); ?> m</small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <select class="form-select" name="control_entrada_id" id="control_entrada_id">
                        <option value="">-- Sin vincular --</option>
                        <?php foreach ($controles_disponibles as $control): ?>
                        <option value="<?php echo $control['id']; ?>">
                            #<?php echo $control['id']; ?> - <?php echo h($control['referencia']); ?> 
                            (<?php echo date('d/m/Y', strtotime($control['fecha_entrada'])); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sección 3: Detalles de la Prenda -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-tshirt me-2"></i>
                        Detalles de la Prenda (Opcional)
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Prenda</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="tipo_prenda" 
                                   placeholder="Ej: Camisa, Pantalón, Playera"
                                   list="tipo_prenda_list">
                            <datalist id="tipo_prenda_list">
                                <option value="Camisa">
                                <option value="Pantalón">
                                <option value="Playera">
                                <option value="Chamarra">
                                <option value="Sudadera">
                            </datalist>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="color" 
                                   placeholder="Ej: Azul, Rojo, Negro"
                                   list="color_list">
                            <datalist id="color_list">
                                <option value="Blanco">
                                <option value="Negro">
                                <option value="Azul">
                                <option value="Rojo">
                                <option value="Verde">
                                <option value="Gris">
                            </datalist>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Talla</label>
                            <select class="form-select" name="talla">
                                <option value="">-- Seleccionar --</option>
                                <option value="XS">XS</option>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                                <option value="XXXL">XXXL</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sección 4: Asignación de Trabajador -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-user-tie me-2"></i>
                        Asignación de Trabajador (Opcional)
                    </div>
                    
                    <select class="form-select" name="trabajador_id">
                        <option value="">-- Sin asignar --</option>
                        <?php foreach ($trabajadores as $trabajador): ?>
                        <option value="<?php echo $trabajador['id']; ?>">
                            <?php echo h($trabajador['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Selecciona el trabajador responsable de esta transferencia
                    </small>
                </div>

                <!-- Sección 5: Observaciones -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-comment-alt me-2"></i>
                        Observaciones
                    </div>
                    
                    <textarea class="form-control" 
                              name="observaciones" 
                              rows="4"
                              placeholder="Notas adicionales, instrucciones especiales, etc."></textarea>
                </div>

                <!-- Botones de Acción -->
                <div class="row g-3 mt-4">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>
                            Enviar Transferencia
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="reset" class="btn btn-secondary w-100 btn-lg">
                            <i class="fas fa-undo me-2"></i>
                            Limpiar
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="index.php" class="btn btn-outline-secondary w-100 btn-lg">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Selección rápida de control
        function selectControl(id, referencia) {
            document.getElementById('control_entrada_id').value = id;
            
            // Visual feedback
            document.querySelectorAll('.control-option').forEach(el => {
                el.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            // Scroll suave al select
            document.getElementById('control_entrada_id').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Validación del formulario
        document.getElementById('transferForm').addEventListener('submit', function(e) {
            const totalItems = document.getElementById('total_items').value;
            const referencia = document.getElementById('referencia').value;
            
            if (!totalItems || totalItems < 1) {
                e.preventDefault();
                alert('Por favor ingresa el total de items (debe ser mayor a 0)');
                document.getElementById('total_items').focus();
                return false;
            }
            
            if (!referencia || referencia.trim() === '') {
                e.preventDefault();
                alert('Por favor ingresa una referencia válida');
                document.getElementById('referencia').focus();
                return false;
            }
            
            // Confirmación
            if (!confirm('¿Estás seguro de enviar esta transferencia?')) {
                e.preventDefault();
                return false;
            }
            
            // Deshabilitar botón para evitar doble envío
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
        });

        // Auto-scroll al mensaje de éxito/error
        <?php if ($success || $error): ?>
        window.scrollTo({top: 0, behavior: 'smooth'});
        <?php endif; ?>
    </script>
</body>
</html>
