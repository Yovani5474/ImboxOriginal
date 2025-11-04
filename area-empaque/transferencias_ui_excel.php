<?php
require_once __DIR__ . '/models/Transferencia.php';
require_once __DIR__ . '/models/Trabajador.php';

$t = new Transferencia();
$tr = new Trabajador();
$list = $t->listar(100,0);
$trabajadores = $tr->obtenerTodos();

function h($s){return htmlspecialchars($s);} 

// Detectar si es modo solo lectura (viene desde Almacén 1)
$modo_solo_lectura = isset($_GET['modo']) && $_GET['modo'] === 'ver';

// Mostrar mensaje de éxito si existe
$success = $_GET['success'] ?? null;

// Calcular estadísticas
$stats = [
    'total' => count($list),
    'pendiente' => 0,
    'recibido' => 0,
    'completado' => 0,
    'total_items' => 0
];

foreach ($list as $row) {
    $estado = strtolower($row['estado']);
    if ($estado === 'pendiente' || $estado === 'enviado') {
        $stats['pendiente']++;
    } elseif ($estado === 'recibido' || $estado === 'parcial') {
        $stats['recibido']++;
    } elseif ($estado === 'completado') {
        $stats['completado']++;
    }
    $stats['total_items'] += intval($row['total_items']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empaque | Transferencias - Edición Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/2/css/styles.css">
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
        .estado-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .estado-pendiente { background-color: #ffc107; color: #000; }
        .estado-enviado { background-color: #ff8c00; color: #fff; }
        .estado-recibido { background-color: #17a2b8; color: #fff; }
        .estado-parcial { background-color: #fd7e14; color: #fff; }
        .estado-completado { background-color: #28a745; color: #fff; }
        .editable-cell {
            cursor: pointer;
            transition: background-color 0.2s;
            padding: 8px !important;
        }
        .editable-cell:hover {
            background-color: #fff3cd !important;
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
                            <h3 class="mb-0 text-imbox-dark"><i class="fas fa-exchange-alt me-2"></i>Transferencias Recibidas</h3>
                            <small class="text-muted">Área de Empaque | Edición tipo Excel - Click para editar</small>
                        </div>
                    </div>
                    <div>
                        <a href="trabajadores_ui_excel.php" class="btn btn-info me-2">
                            <i class="fas fa-users me-2"></i>Trabajadores
                        </a>
                        <?php if (!$modo_solo_lectura): ?>
                        <a href="control_entrada_almacen2.php" class="btn btn-warning me-2">
                            <i class="fas fa-clipboard-check me-2"></i>Nueva Recepción
                        </a>
                        <?php endif; ?>
                        <a href="<?= $modo_solo_lectura ? '/1/index.php' : '/' ?>" class="btn btn-outline-primary">
                            <i class="fas fa-<?= $modo_solo_lectura ? 'arrow-left' : 'home' ?> me-2"></i><?= $modo_solo_lectura ? 'Volver a Corte' : 'Inicio' ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if ($modo_solo_lectura): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-eye me-2"></i>
                <strong>Modo Solo Lectura</strong> - Estás visualizando desde el Almacén de Corte. No puedes realizar cambios desde aquí.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>¡Éxito!</strong> La transferencia ha sido confirmada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <i class="fas fa-inbox fa-2x text-imbox-orange mb-2"></i>
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Total Transferencias</div>
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
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <div class="stat-number"><?php echo $stats['recibido'] + $stats['completado']; ?></div>
                        <div class="stat-label">Completadas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <i class="fas fa-boxes fa-2x text-info mb-2"></i>
                        <div class="stat-number"><?php echo $stats['total_items']; ?></div>
                        <div class="stat-label">Total Items</div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de Transferencias -->
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-imbox-dark mb-0">
                        <i class="fas fa-table text-imbox-orange me-2"></i>
                        Registro de Transferencias
                        <span class="badge bg-secondary ms-2"><?php echo $stats['total']; ?></span>
                    </h5>
                </div>
                
                <?php if (!$modo_solo_lectura): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Edición rápida:</strong> Haz clic en cualquier celda para editarla directamente. 
                    Los cambios se guardan automáticamente. Usa <kbd>Enter</kbd>, <kbd>Tab</kbd> o flechas para navegar.
                </div>
                <?php else: ?>
                <div class="alert alert-secondary">
                    <i class="fas fa-eye me-2"></i>
                    <strong>Visualización:</strong> Puedes ver los detalles de las transferencias pero no realizar cambios.
                </div>
                <?php endif; ?>
                
                <?php if (empty($list)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No hay transferencias disponibles en este momento</p>
                </div>
                <?php else: ?>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="transferenciasTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th><i class="fas fa-barcode me-1"></i>Referencia</th>
                                <th class="text-center"><i class="fas fa-boxes me-1"></i>Items</th>
                                <th><i class="fas fa-tshirt me-1"></i>Tipo Prenda</th>
                                <th><i class="fas fa-palette me-1"></i>Color</th>
                                <th><i class="fas fa-ruler me-1"></i>Talla</th>
                                <th><i class="fas fa-user-tie me-1"></i>Trabajador</th>
                                <th class="text-center">Estado</th>
                                <th><i class="fas fa-clock me-1"></i>Fecha</th>
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
                                <td class="text-center editable-cell" data-editable="true" data-field="total_items">
                                    <?= h($row['total_items']) ?>
                                </td>
                                <td class="editable-cell" data-editable="true" data-field="tipo_prenda">
                                    <?= h($row['tipo_prenda'] ?? '') ?>
                                </td>
                                <td class="editable-cell" data-editable="true" data-field="color">
                                    <?= h($row['color'] ?? '') ?>
                                </td>
                                <td class="editable-cell" data-editable="true" data-field="talla">
                                    <?= h($row['talla'] ?? '') ?>
                                </td>
                                <td class="editable-cell" data-editable="true" data-field="trabajador_id" data-trabajador-nombre="<?= h($row['trabajador_nombre'] ?? '') ?>">
                                    <?php if ($row['trabajador_nombre']): ?>
                                        <?= h($row['trabajador_nombre']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="estado-badge estado-<?= h($row['estado']) ?>">
                                        <?php 
                                            $estados = [
                                                'pendiente' => 'Pendiente',
                                                'enviado' => 'Enviado',
                                                'recibido' => 'Recibido',
                                                'parcial' => 'Falta Completar',
                                                'completado' => 'Completado'
                                            ];
                                            echo $estados[$row['estado']] ?? ucfirst($row['estado']); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($row['fecha_creacion'])) ?>
                                        <br><?= date('H:i', strtotime($row['fecha_creacion'])) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="ver_transferencia.php?id=<?= $row['id'] ?>" 
                                           class="btn btn-info btn-sm" 
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if (!$modo_solo_lectura && ($row['estado'] == 'enviado' || $row['estado'] == 'pendiente' || $row['estado'] == 'parcial')): ?>
                                            <a href="control_entrada_almacen2.php?id=<?= $row['id'] ?>" 
                                               class="btn btn-success btn-sm"
                                               title="<?= $row['estado'] == 'parcial' ? 'Completar recepción' : 'Procesar recepción' ?>">
                                                <i class="fas fa-clipboard-check"></i>
                                            </a>
                                        <?php endif; ?>
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
    <script src="/2/js/excel-table.js"></script>
    <script>
        const modoSoloLectura = <?php echo $modo_solo_lectura ? 'true' : 'false'; ?>;
        
        <?php if (!$modo_solo_lectura): ?>
        // Preparar lista de trabajadores para el select
        const trabajadores = <?php echo json_encode($trabajadores); ?>;
        const trabajadoresOptions = trabajadores.map(t => ({
            value: t.id,
            label: t.nombre
        }));
        trabajadoresOptions.unshift({ value: '', label: 'Sin asignar' });
        
        // Inicializar tabla editable tipo Excel
        const excelTable = new ExcelTable({
            tableId: 'transferenciasTable',
            apiEndpoint: '/2/api/transferencias.php',
            primaryKey: 'id',
            columns: [
                { field: 'total_items', editable: true, type: 'number', min: 0 },
                { field: 'tipo_prenda', editable: true, type: 'text' },
                { field: 'color', editable: true, type: 'text' },
                { field: 'talla', editable: true, type: 'text' },
                { 
                    field: 'trabajador_id', 
                    editable: true, 
                    type: 'select',
                    options: trabajadoresOptions
                }
            ],
            onSave: (id, field, value, result) => {
                console.log('Campo actualizado:', field, value);
                if (field === 'trabajador_id') {
                    // Actualizar visualmente el nombre del trabajador
                    const trabajador = trabajadores.find(t => t.id == value);
                    const cell = document.querySelector(`tr[data-id="${id}"] td[data-field="trabajador_id"]`);
                    if (cell && trabajador) {
                        cell.dataset.trabajadorNombre = trabajador.nombre;
                    }
                }
            },
            onError: (error) => {
                console.error('Error al guardar:', error);
            }
        });
        <?php endif; // Fin de modo editable ?>
        
        <?php if ($modo_solo_lectura): ?>
        // Deshabilitar clic en celdas en modo solo lectura
        document.querySelectorAll('#transferenciasTable .editable-cell').forEach(cell => {
            cell.style.cursor = 'default';
            cell.removeAttribute('data-editable');
        });
        <?php endif; ?>
    </script>
</body>
</html>
