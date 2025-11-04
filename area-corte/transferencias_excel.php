<?php
/**
 * Vista de Transferencias con Edición Tipo Excel
 * Área de Corte - Sistema IMBOX
 */

require_once __DIR__ . '/config.php';

// Función helper
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// Obtener transferencias desde SQLite
$db = getDB();
$success = $_GET['success'] ?? null;

try {
    // Consultar transferencias
    $stmt = $db->query("
        SELECT * FROM controles_entrada 
        ORDER BY fecha_creacion DESC 
        LIMIT 100
    ");
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular estadísticas
    $stats = [
        'total' => count($list),
        'pendiente' => 0,
        'enviado' => 0,
        'completado' => 0,
        'total_rollos' => 0,
        'total_metros' => 0
    ];
    
    foreach ($list as $row) {
        $estado = strtolower($row['estado'] ?? 'pendiente');
        if ($estado === 'pendiente') {
            $stats['pendiente']++;
        } elseif ($estado === 'enviado') {
            $stats['enviado']++;
        } elseif ($estado === 'completado') {
            $stats['completado']++;
        }
        $stats['total_rollos'] += intval($row['total_rollos']);
        $stats['total_metros'] += floatval($row['total_metros']);
    }
    
} catch (Exception $e) {
    $error = "Error al cargar transferencias: " . $e->getMessage();
    $list = [];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corte | Transferencias - Edición Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
        .main-container {
            max-width: 1600px;
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
            text-align: center;
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
        .text-imbox-orange {
            color: #FF8C00;
        }
        .text-imbox-dark {
            color: #2C2C2C;
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
        .editable-cell {
            cursor: pointer;
            transition: background-color 0.2s;
            padding: 8px !important;
        }
        .editable-cell:hover {
            background-color: #fff3cd !important;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="main-container">
            
            <!-- Header -->
            <div class="header-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0 text-imbox-dark">
                            <i class="fas fa-table me-2"></i>
                            Controles de Entrada - Edición Excel
                        </h3>
                        <small class="text-muted">Área de Corte | Click para editar directamente</small>
                    </div>
                    <div>
                        <a href="transferencias.php" class="btn btn-warning me-2">
                            <i class="fas fa-paper-plane me-2"></i>Nueva Transferencia
                        </a>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home me-2"></i>Inicio
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>¡Éxito!</strong> Los cambios se guardaron correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-inbox fa-2x text-imbox-orange mb-2"></i>
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Total Controles</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <div class="stat-number"><?php echo $stats['pendiente']; ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-boxes fa-2x text-info mb-2"></i>
                        <div class="stat-number"><?php echo number_format($stats['total_rollos']); ?></div>
                        <div class="stat-label">Total Rollos</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="fas fa-ruler fa-2x text-success mb-2"></i>
                        <div class="stat-number"><?php echo number_format($stats['total_metros'], 1); ?></div>
                        <div class="stat-label">Total Metros</div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de Controles -->
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-imbox-dark mb-0">
                        <i class="fas fa-table text-imbox-orange me-2"></i>
                        Registro de Controles de Entrada
                        <span class="badge bg-secondary ms-2"><?php echo $stats['total']; ?></span>
                    </h5>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Edición rápida:</strong> Haz clic en cualquier celda para editarla directamente. 
                    Los cambios se guardan automáticamente. Usa <kbd>Enter</kbd>, <kbd>Tab</kbd> o flechas para navegar.
                </div>
                
                <?php if (empty($list)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No hay controles de entrada registrados</p>
                    <a href="control_entrada.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Crear Primer Control
                    </a>
                </div>
                <?php else: ?>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="controlesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th><i class="fas fa-barcode me-1"></i>Referencia</th>
                                <th><i class="fas fa-calendar me-1"></i>Fecha Entrada</th>
                                <th><i class="fas fa-truck me-1"></i>Proveedor</th>
                                <th><i class="fas fa-file-invoice me-1"></i>Orden Compra</th>
                                <th class="text-center"><i class="fas fa-boxes me-1"></i>Rollos</th>
                                <th class="text-center"><i class="fas fa-ruler me-1"></i>Metros</th>
                                <th class="text-center">Estado</th>
                                <th><i class="fas fa-clock me-1"></i>Creación</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($list as $row): ?>
                            <tr data-id="<?= $row['id'] ?>">
                                <td class="text-muted"><strong>#<?= h($row['id']) ?></strong></td>
                                <td>
                                    <strong class="text-imbox-orange"><?= h($row['referencia']) ?></strong>
                                </td>
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
                                        $estados_map = [
                                            'pendiente' => 'Pendiente',
                                            'enviado' => 'Enviado',
                                            'completado' => 'Completado'
                                        ];
                                    ?>
                                    <span class="estado-badge estado-<?= h($estado) ?>">
                                        <?= $estados_map[$estado] ?? ucfirst($estado) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($row['fecha_creacion'] ?? $row['fecha_entrada'])) ?>
                                        <br><?= date('H:i', strtotime($row['fecha_creacion'] ?? $row['fecha_entrada'])) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="control_entrada.php?id=<?= $row['id'] ?>" 
                                           class="btn btn-info btn-sm" 
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="control_entrada.php?id=<?= $row['id'] ?>&editar=1" 
                                           class="btn btn-warning btn-sm"
                                           title="Editar completo">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
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
    <script src="/1/js/excel-table.js"></script>
    <script>
        // Inicializar tabla editable tipo Excel
        const excelTable = new ExcelTable({
            tableId: 'controlesTable',
            apiEndpoint: '/1/api/controles.php',
            primaryKey: 'id',
            columns: [
                { field: 'fecha_entrada', editable: true, type: 'date' },
                { field: 'proveedor', editable: true, type: 'text' },
                { field: 'orden_compra', editable: true, type: 'text' },
                { field: 'total_rollos', editable: true, type: 'number', min: 0 },
                { field: 'total_metros', editable: true, type: 'number', min: 0, step: '0.01' }
            ],
            onSave: (id, field, value, result) => {
                console.log('Campo actualizado:', field, value);
            },
            onError: (error) => {
                console.error('Error al guardar:', error);
            }
        });
    </script>
</body>
</html>
