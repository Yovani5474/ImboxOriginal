<?php
/**
 * Sistema Completo - Área de Corte
 * Gestión integral: Crear, Editar, Transferir - Todo en uno
 */

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
require_once 'config.php';

// Configuración API
if (!defined('TARGET_URL')) {
    define('TARGET_URL', getenv('TARGET_URL') ?: 'http://localhost/2/api/transferencias.php');
}
if (!defined('API_KEY')) {
    define('API_KEY', getenv('API_KEY') ?: '1c810efe778ea94df3578a92e7ed6f9dfa28621cfa67944e2535e8460d05e255');
}

$result = null;
$success = false;
$error = null;

// Obtener datos
$db = getDB();
try {
    // Controles de entrada disponibles
    $stmt = $db->query("SELECT id, referencia, fecha_entrada, total_rollos, total_metros, proveedor, estado FROM controles_entrada ORDER BY id DESC LIMIT 100");
    $controles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Trabajadores
    $trabajadores = [];
    $workers_file = __DIR__ . '/data/trabajadores.json';
    if (file_exists($workers_file)) {
        $trabajadores = json_decode(file_get_contents($workers_file), true) ?: [];
    }
    
    // Estadísticas
    $stmt_stats = $db->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado='pendiente' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado='enviado' THEN 1 ELSE 0 END) as enviados,
        SUM(CASE WHEN estado='completado' THEN 1 ELSE 0 END) as completados,
        SUM(total_rollos) as total_rollos,
        SUM(total_metros) as total_metros
    FROM controles_entrada");
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
    $controles = [];
    $trabajadores = [];
    $stats = ['total' => 0, 'pendientes' => 0, 'enviados' => 0, 'completados' => 0, 'total_rollos' => 0, 'total_metros' => 0];
}

// Procesar envío de transferencia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'transferir') {
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
    
    if (!empty($_POST['trabajador_id'])) {
        $data['trabajador_id'] = intval($_POST['trabajador_id']);
        foreach ($trabajadores as $t) {
            if ($t['id'] == $data['trabajador_id']) {
                $data['trabajador_nombre'] = $t['nombre'];
                break;
            }
        }
    }

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
    <title>Sistema Completo | Área de Corte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --imbox-orange: #FF8C00;
            --imbox-dark: #2C2C2C;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            max-width: 1800px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .header-card {
            background: linear-gradient(135deg, var(--imbox-orange) 0%, #FFA500 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(255,140,0,0.3);
            color: white;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-left: 4px solid var(--imbox-orange);
            transition: all 0.3s;
            text-align: center;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255,140,0,0.2);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--imbox-orange);
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        .section-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .section-title {
            color: var(--imbox-dark);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid var(--imbox-orange);
        }
        
        .nav-pills .nav-link {
            color: #6c757d;
            border-radius: 8px;
            padding: 12px 25px;
            transition: all 0.3s;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, var(--imbox-orange) 0%, #FFA500 100%);
            color: white;
        }
        
        .nav-pills .nav-link:hover:not(.active) {
            background: #fff3cd;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--imbox-orange);
            box-shadow: 0 0 0 3px rgba(255,140,0,0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--imbox-orange) 0%, #FFA500 100%);
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,140,0,0.3);
        }
        
        .table-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .editable-cell {
            cursor: pointer;
            transition: background-color 0.2s;
            padding: 8px !important;
        }
        
        .editable-cell:hover {
            background-color: #fff3cd !important;
        }
        
        .estado-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .estado-pendiente { background-color: #ffc107; color: #000; }
        .estado-enviado { background-color: #ff8c00; color: #fff; }
        .estado-completado { background-color: #28a745; color: #fff; }
        
        .quick-actions {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        
        .fab-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--imbox-orange) 0%, #FFA500 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(255,140,0,0.4);
            font-size: 1.5rem;
            transition: all 0.3s;
            margin: 5px;
        }
        
        .fab-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(255,140,0,0.6);
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-cubes me-2"></i>
                        Sistema Completo - Área de Corte
                    </h2>
                    <p class="mb-0" style="opacity: 0.9;">
                        Gestión integral: Recepcionar, Editar, Transferir
                    </p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-home me-2"></i>
                        Inicio
                    </a>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>¡Éxito!</strong> La transferencia ha sido enviada correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error:</strong> <?php echo h($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-clipboard-list fa-2x mb-2" style="opacity: 0.3;"></i>
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-clock fa-2x mb-2" style="opacity: 0.3; color: #ffc107;"></i>
                    <div class="stat-number"><?php echo $stats['pendientes']; ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-paper-plane fa-2x mb-2" style="opacity: 0.3; color: #ff8c00;"></i>
                    <div class="stat-number"><?php echo $stats['enviados']; ?></div>
                    <div class="stat-label">Enviados</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-check-circle fa-2x mb-2" style="opacity: 0.3; color: #28a745;"></i>
                    <div class="stat-number"><?php echo $stats['completados']; ?></div>
                    <div class="stat-label">Completados</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-boxes fa-2x mb-2" style="opacity: 0.3; color: #17a2b8;"></i>
                    <div class="stat-number"><?php echo number_format($stats['total_rollos']); ?></div>
                    <div class="stat-label">Rollos</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-ruler fa-2x mb-2" style="opacity: 0.3; color: #6f42c1;"></i>
                    <div class="stat-number"><?php echo number_format($stats['total_metros'], 1); ?></div>
                    <div class="stat-label">Metros</div>
                </div>
            </div>
        </div>

        <!-- Tabs de Navegación -->
        <div class="section-card">
            <ul class="nav nav-pills mb-4" id="mainTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tabla-tab" data-bs-toggle="pill" data-bs-target="#tabla" type="button">
                        <i class="fas fa-table me-2"></i>
                        Tabla Excel - Ver y Editar
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="transferir-tab" data-bs-toggle="pill" data-bs-target="#transferir" type="button">
                        <i class="fas fa-paper-plane me-2"></i>
                        Nueva Transferencia
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="recepcionar-tab" data-bs-toggle="pill" data-bs-target="#recepcionar" type="button">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Recepcionar Entrada
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="mainTabContent">
                <!-- TAB 1: Tabla Excel -->
                <div class="tab-pane fade show active" id="tabla" role="tabpanel">
                    <!-- Tablero de Estadísticas -->
                    <?php
                    // Preparar datos para el tablero
                    $transferencia_data = [
                        'referencia' => 'Área de Corte',
                        'almacen_origen_id' => 1,
                        'total_items' => $stats['total_rollos']
                    ];
                    $detalles_prenda = [];
                    include __DIR__ . '/../includes/tablero_recepcion.php';
                    ?>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Edición rápida:</strong> Haz clic en cualquier celda para editarla. Usa <kbd>Enter</kbd>, <kbd>Tab</kbd> o flechas para navegar.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="controlesTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>OC</th>
                                    <th class="text-center">Rollos</th>
                                    <th class="text-center">Metros</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($controles as $row): ?>
                                <tr data-id="<?= $row['id'] ?>">
                                    <td><strong>#<?= h($row['id']) ?></strong></td>
                                    <td><strong style="color: var(--imbox-orange);"><?= h($row['referencia']) ?></strong></td>
                                    <td class="editable-cell" data-editable="true" data-field="fecha_entrada">
                                        <?= date('d/m/Y', strtotime($row['fecha_entrada'])) ?>
                                    </td>
                                    <td class="editable-cell" data-editable="true" data-field="proveedor">
                                        <?= h($row['proveedor'] ?? '') ?>
                                    </td>
                                    <td class="editable-cell" data-editable="true" data-field="orden_compra">
                                        <?= h($row['orden_compra'] ?? '') ?>
                                    </td>
                                    <td class="text-center editable-cell" data-editable="true" data-field="total_rollos">
                                        <?= h($row['total_rollos']) ?>
                                    </td>
                                    <td class="text-center editable-cell" data-editable="true" data-field="total_metros">
                                        <?= number_format($row['total_metros'], 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $estado = strtolower($row['estado']);
                                        ?>
                                        <span class="estado-badge estado-<?= h($estado) ?>">
                                            <?= ucfirst($estado) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-info" onclick="verTablero(<?= $row['id'] ?>, '<?= h($row['referencia']) ?>')" title="Ver Tablero">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-warning" onclick="editarTablero(<?= $row['id'] ?>, '<?= h($row['referencia']) ?>')" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-success" onclick="completarTablero(<?= $row['id'] ?>, '<?= h($row['referencia']) ?>')" title="Completar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: Nueva Transferencia -->
                <div class="tab-pane fade" id="transferir" role="tabpanel">
                    <!-- Tablero de Estadísticas -->
                    <?php
                    include __DIR__ . '/../includes/tablero_recepcion.php';
                    ?>

                    <div class="mt-4">
                        <h5 class="mb-3">
                            <i class="fas fa-paper-plane me-2" style="color: var(--imbox-orange);"></i>
                            Formulario de Transferencia
                        </h5>
                    </div>

                    <form method="POST" id="transferForm">
                        <input type="hidden" name="action" value="transferir">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Referencia *</label>
                                <input type="text" class="form-control" name="referencia" 
                                       value="COR-<?= date('Ymd-His') ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total Items *</label>
                                <input type="number" class="form-control" name="total_items" min="1" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Vincular a Control</label>
                                <select class="form-select" name="control_entrada_id">
                                    <option value="">-- Sin vincular --</option>
                                    <?php foreach($controles as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        #<?= $c['id'] ?> - <?= h($c['referencia']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Trabajador</label>
                                <select class="form-select" name="trabajador_id">
                                    <option value="">-- Sin asignar --</option>
                                    <?php foreach($trabajadores as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= h($t['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Tipo Prenda</label>
                                <input type="text" class="form-control" name="tipo_prenda" placeholder="Camisa, Pantalón...">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" name="color" placeholder="Azul, Rojo...">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Talla</label>
                                <select class="form-select" name="talla">
                                    <option value="">-- Seleccionar --</option>
                                    <option>XS</option>
                                    <option>S</option>
                                    <option>M</option>
                                    <option>L</option>
                                    <option>XL</option>
                                    <option>XXL</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="3"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Enviar Transferencia
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TAB 3: Recepcionar -->
                <div class="tab-pane fade" id="recepcionar" role="tabpanel">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Nota:</strong> Esta sección permite crear nuevos controles de entrada de material.
                    </div>
                    
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-check fa-4x text-muted mb-4"></i>
                        <h4>Control de Entrada de Material</h4>
                        <p class="text-muted mb-4">Registra nuevas entradas de material desde proveedores</p>
                        <a href="control_entrada.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>
                            Nuevo Control de Entrada
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones Flotantes -->
    <div class="quick-actions">
        <button class="fab-button" onclick="cambiarTab('transferir-tab')" title="Nueva Transferencia">
            <i class="fas fa-paper-plane"></i>
        </button>
        <button class="fab-button" onclick="cambiarTab('recepcionar-tab')" title="Recepcionar">
            <i class="fas fa-clipboard-check"></i>
        </button>
        <button class="fab-button" onclick="window.location.reload()" title="Actualizar">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>

    <!-- Modal para Ver Tablero -->
    <div class="modal fade" id="verTableroModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>
                        Ver Tablero - <span id="modalTituloVer"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="tableroContentVer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3">Cargando información del control...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar con Tablero -->
    <div class="modal fade" id="editarTableroModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%); color: #000;">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Editar - <span id="modalTituloEditar"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="tableroContentEditar">
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3">Cargando formulario...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Completar con Tablero -->
    <div class="modal fade" id="completarTableroModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>
                        Completar - <span id="modalTituloCompletar"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="tableroContentCompletar">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3">Cargando opciones...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/1/js/excel-table.js"></script>
    <script>
        // Inicializar tabla editable
        const excelTable = new ExcelTable({
            tableId: 'controlesTable',
            apiEndpoint: '/1/api/controles',
            primaryKey: 'id',
            columns: [
                { field: 'fecha_entrada', editable: true, type: 'date' },
                { field: 'proveedor', editable: true, type: 'text' },
                { field: 'orden_compra', editable: true, type: 'text' },
                { field: 'total_rollos', editable: true, type: 'number', min: 0 },
                { field: 'total_metros', editable: true, type: 'number', min: 0, step: '0.01' }
            ]
        });

        // Funciones auxiliares
        function cambiarTab(tabId) {
            const tab = new bootstrap.Tab(document.getElementById(tabId));
            tab.show();
        }

        function verDetalle(id) {
            window.location.href = 'control_entrada.php?id=' + id;
        }

        function transferirControl(id) {
            document.querySelector('select[name="control_entrada_id"]').value = id;
            cambiarTab('transferir-tab');
        }

        // Funciones para modales con tablero
        async function verTablero(id, referencia) {
            document.getElementById('modalTituloVer').textContent = referencia;
            const modal = new bootstrap.Modal(document.getElementById('verTableroModal'));
            modal.show();
            
            // Cargar tabla de tallas
            cargarTablaTallas(id, 'tableroContentVer', true);
        }

        async function editarTablero(id, referencia) {
            document.getElementById('modalTituloEditar').textContent = referencia;
            const modal = new bootstrap.Modal(document.getElementById('editarTableroModal'));
            modal.show();
            
            // Cargar tabla de tallas para edición
            cargarTablaTallas(id, 'tableroContentEditar', false);
        }

        async function completarTablero(id, referencia) {
            document.getElementById('modalTituloCompletar').textContent = referencia;
            const modal = new bootstrap.Modal(document.getElementById('completarTableroModal'));
            modal.show();
            
            // Cargar tabla de tallas para completar
            cargarTablaTallas(id, 'tableroContentCompletar', false);
        }
        
        // Función para cargar tabla de tallas
        async function cargarTablaTallas(id, targetId, soloLectura) {
            const target = document.getElementById(targetId);
            target.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando tabla de tallas...</p>
                </div>
            `;
            
            try {
                const response = await fetch(`/1/api/tabla_tallas.php?id=${id}`);
                const html = await response.text();
                target.innerHTML = html;
                
                // Si es solo lectura, deshabilitar inputs
                if (soloLectura) {
                    target.querySelectorAll('input').forEach(inp => {
                        inp.disabled = true;
                        inp.style.background = '#f8f9fa';
                    });
                }
            } catch (error) {
                target.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error al cargar la tabla de tallas
                    </div>
                `;
            }
        }

        async function guardarCambios(event, id) {
            event.preventDefault();
            
            // Recopilar datos de la tabla de tallas
            const tablaTallas = [];
            for(let i = 1; i <= 20; i++) {
                const colorInput = document.querySelector(`.color-input[data-row="${i}"]`);
                if (!colorInput) continue;
                
                const color = colorInput.value.trim();
                if (color === '') continue;
                
                const fila = {
                    numero: i,
                    color: color,
                    tallas: {}
                };
                
                // Recopilar tallas numéricas
                [2,4,6,8,10,12,14,16,20].forEach(talla => {
                    const input = document.querySelector(`.talla-input[data-row="${i}"][data-talla="${talla}"]`);
                    if (input) {
                        fila.tallas[talla] = parseInt(input.value) || 0;
                    }
                });
                
                // Recopilar tallas letras
                ['S','M','L','XL','XXL'].forEach(talla => {
                    const input = document.querySelector(`.talla-input[data-row="${i}"][data-talla="${talla}"]`);
                    if (input) {
                        fila.tallas[talla] = parseInt(input.value) || 0;
                    }
                });
                
                // Observación
                const obsInput = document.querySelector(`.obs-input[data-row="${i}"]`);
                if (obsInput) {
                    fila.observacion = obsInput.value.trim();
                }
                
                tablaTallas.push(fila);
            }
            
            const totalGeneral = document.getElementById('totalGeneral');
            const data = {
                tabla_tallas: tablaTallas,
                total_prendas: totalGeneral ? parseInt(totalGeneral.textContent) : 0,
                fecha_recepcion: document.getElementById('resumen_fecha') ? document.getElementById('resumen_fecha').value : new Date().toISOString().split('T')[0]
            };
            
            try {
                const response = await fetch(`/1/api/controles/${id}`, {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Datos guardados correctamente\n' + (data.total_prendas || 0) + ' prendas registradas');
                    bootstrap.Modal.getInstance(document.getElementById('editarTableroModal')).hide();
                    window.location.reload();
                } else {
                    alert('❌ Error al guardar cambios');
                }
            } catch (error) {
                alert('❌ Error de conexión');
            }
            
            return false;
        }

        async function confirmarCompletado(id) {
            // Recopilar datos antes de completar
            const tablaTallas = [];
            for(let i = 1; i <= 20; i++) {
                const colorInput = document.querySelector(`.color-input[data-row="${i}"]`);
                if (!colorInput) continue;
                
                const color = colorInput.value.trim();
                if (color === '') continue;
                
                const fila = {
                    numero: i,
                    color: color,
                    tallas: {}
                };
                
                [2,4,6,8,10,12,14,16,20].forEach(talla => {
                    const input = document.querySelector(`.talla-input[data-row="${i}"][data-talla="${talla}"]`);
                    if (input) {
                        fila.tallas[talla] = parseInt(input.value) || 0;
                    }
                });
                
                ['S','M','L','XL','XXL'].forEach(talla => {
                    const input = document.querySelector(`.talla-input[data-row="${i}"][data-talla="${talla}"]`);
                    if (input) {
                        fila.tallas[talla] = parseInt(input.value) || 0;
                    }
                });
                
                tablaTallas.push(fila);
            }
            
            const totalGeneral = document.getElementById('totalGeneral');
            const totalPrendas = totalGeneral ? parseInt(totalGeneral.textContent) : 0;
            
            if (totalPrendas === 0) {
                if (!confirm('⚠️ No hay datos en la tabla de tallas.\n¿Desea marcar como completado de todos modos?')) {
                    return;
                }
            }
            
            try {
                const response = await fetch(`/1/api/controles/${id}`, {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        estado: 'completado',
                        tabla_tallas: tablaTallas,
                        total_prendas: totalPrendas
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Control completado\n' + totalPrendas + ' prendas registradas');
                    bootstrap.Modal.getInstance(document.getElementById('completarTableroModal')).hide();
                    window.location.reload();
                } else {
                    alert('❌ Error al actualizar estado');
                }
            } catch (error) {
                alert('❌ Error de conexión');
            }
        }

        // Auto-scroll al mensaje
        <?php if ($success || $error): ?>
        window.scrollTo({top: 0, behavior: 'smooth'});
        <?php endif; ?>
    </script>
</body>
</html>
