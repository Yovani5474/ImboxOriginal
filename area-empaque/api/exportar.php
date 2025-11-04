<?php
/**
 * API de Exportación - Generar reportes en Excel (CSV) y PDF
 */

require_once __DIR__ . '/../models/Transferencia.php';

$t = new Transferencia();
$transferencias = $t->listar(10000, 0);

// Aplicar filtros si existen
$formato = $_GET['formato'] ?? 'excel';
$estado = $_GET['estado'] ?? '';
$trabajador_id = $_GET['trabajador_id'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Filtrar datos
$datos_filtrados = array_filter($transferencias, function($tf) use ($estado, $trabajador_id, $fecha_desde, $fecha_hasta) {
    if ($estado && strtolower($tf['estado']) !== strtolower($estado)) {
        return false;
    }
    if ($trabajador_id && $tf['trabajador_id'] != $trabajador_id) {
        return false;
    }
    if ($fecha_desde && strtotime($tf['fecha_creacion']) < strtotime($fecha_desde)) {
        return false;
    }
    if ($fecha_hasta && strtotime($tf['fecha_creacion']) > strtotime($fecha_hasta . ' 23:59:59')) {
        return false;
    }
    return true;
});

// Generar según formato
if ($formato === 'excel' || $formato === 'csv') {
    exportarExcel($datos_filtrados);
} elseif ($formato === 'pdf') {
    exportarPDF($datos_filtrados, $estado, $trabajador_id, $fecha_desde, $fecha_hasta);
}

/**
 * Exportar a Excel (CSV)
 */
function exportarExcel($datos) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="transferencias_' . date('Y-m-d_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados
    fputcsv($output, [
        'ID',
        'Referencia',
        'Total Items',
        'Tipo Prenda',
        'Color',
        'Talla',
        'Trabajador',
        'Estado',
        'Observaciones',
        'Control Entrada ID',
        'Fecha Creación'
    ]);
    
    // Datos
    foreach ($datos as $row) {
        fputcsv($output, [
            $row['id'],
            $row['referencia'],
            $row['total_items'],
            $row['tipo_prenda'] ?? '',
            $row['color'] ?? '',
            $row['talla'] ?? '',
            $row['trabajador_nombre'] ?? 'Sin asignar',
            ucfirst($row['estado']),
            $row['observaciones'] ?? '',
            $row['control_entrada_id'] ?? '',
            date('d/m/Y H:i', strtotime($row['fecha_creacion']))
        ]);
    }
    
    fclose($output);
    exit;
}

/**
 * Exportar a PDF
 */
function exportarPDF($datos, $estado, $trabajador_id, $fecha_desde, $fecha_hasta) {
    // Calcular estadísticas
    $stats = [
        'total' => count($datos),
        'pendiente' => 0,
        'enviado' => 0,
        'recibido' => 0,
        'completado' => 0
    ];
    
    foreach ($datos as $row) {
        $est = strtolower($row['estado'] ?? 'pendiente');
        if (isset($stats[$est])) {
            $stats[$est]++;
        }
    }
    
    // Generar HTML para PDF
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Transferencias</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 10px;
                margin: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px solid #FF8C00;
                padding-bottom: 15px;
            }
            .header h1 {
                color: #FF8C00;
                margin: 0;
                font-size: 24px;
            }
            .header p {
                margin: 5px 0;
                color: #666;
            }
            .stats {
                display: table;
                width: 100%;
                margin-bottom: 20px;
            }
            .stat-item {
                display: table-cell;
                width: 25%;
                padding: 10px;
                background: #f8f9fa;
                border: 1px solid #ddd;
                text-align: center;
            }
            .stat-number {
                font-size: 24px;
                font-weight: bold;
                color: #FF8C00;
            }
            .stat-label {
                font-size: 11px;
                color: #666;
                text-transform: uppercase;
            }
            .filters {
                background: #fffaf0;
                padding: 10px;
                margin-bottom: 20px;
                border-left: 4px solid #FF8C00;
            }
            .filters strong {
                color: #FF8C00;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th {
                background: #FF8C00;
                color: white;
                padding: 8px;
                text-align: left;
                font-size: 9px;
                text-transform: uppercase;
            }
            td {
                padding: 6px;
                border-bottom: 1px solid #ddd;
                font-size: 9px;
            }
            tr:nth-child(even) {
                background: #f8f9fa;
            }
            .badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 10px;
                font-size: 8px;
                font-weight: bold;
                text-transform: uppercase;
            }
            .badge-pendiente { background: #FFC107; color: #000; }
            .badge-enviado { background: #FF8C00; color: #fff; }
            .badge-recibido { background: #17A2B8; color: #fff; }
            .badge-completado { background: #28A745; color: #fff; }
            .footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
                color: #666;
                font-size: 9px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>📦 REPORTE DE TRANSFERENCIAS</h1>
            <p><strong>Área de Empaque</strong></p>
            <p>Generado: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
        
        <?php if ($estado || $trabajador_id || $fecha_desde || $fecha_hasta): ?>
        <div class="filters">
            <strong>Filtros Aplicados:</strong>
            <?php if ($estado): ?>
                Estado: <strong><?php echo ucfirst($estado); ?></strong> |
            <?php endif; ?>
            <?php if ($fecha_desde): ?>
                Desde: <strong><?php echo date('d/m/Y', strtotime($fecha_desde)); ?></strong> |
            <?php endif; ?>
            <?php if ($fecha_hasta): ?>
                Hasta: <strong><?php echo date('d/m/Y', strtotime($fecha_hasta)); ?></strong>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['pendiente'] + $stats['enviado']; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['recibido']; ?></div>
                <div class="stat-label">Recibidas</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['completado']; ?></div>
                <div class="stat-label">Completadas</div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width:5%">ID</th>
                    <th style="width:12%">Referencia</th>
                    <th style="width:5%">Items</th>
                    <th style="width:10%">Tipo</th>
                    <th style="width:8%">Color</th>
                    <th style="width:6%">Talla</th>
                    <th style="width:12%">Trabajador</th>
                    <th style="width:10%">Estado</th>
                    <th style="width:22%">Observaciones</th>
                    <th style="width:10%">Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datos as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><strong><?php echo htmlspecialchars($row['referencia']); ?></strong></td>
                    <td style="text-align:center"><?php echo htmlspecialchars($row['total_items']); ?></td>
                    <td><?php echo htmlspecialchars($row['tipo_prenda'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['color'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['talla'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['trabajador_nombre'] ?? 'Sin asignar'); ?></td>
                    <td>
                        <span class="badge badge-<?php echo strtolower($row['estado']); ?>">
                            <?php echo ucfirst($row['estado']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars(substr($row['observaciones'] ?? '', 0, 80)); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p><strong>Sistema de Gestión de Empaque</strong> | Reporte generado automáticamente</p>
            <p>Total de registros: <?php echo count($datos); ?></p>
        </div>
    </body>
    </html>
    <?php
    $html = ob_get_clean();
    
    // Para PDF, usaremos una versión simple que el navegador puede "Imprimir como PDF"
    // Si quieres usar una librería como TCPDF o DOMPDF, instálala con Composer
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    echo '<script>window.print();</script>';
    exit;
}
?>
