<?php
/**
 * Generador de Reportes - Área de Empaque
 * Exportar transferencias a Excel (CSV) y PDF
 */

require_once __DIR__ . '/models/Transferencia.php';
require_once __DIR__ . '/models/Trabajador.php';

$t = new Transferencia();
$tr = new Trabajador();

// Obtener transferencias y trabajadores
$transferencias = $t->listar(1000, 0);
$trabajadores = $tr->obtenerTodos();

// Calcular estadísticas
$stats = [
    'total' => count($transferencias),
    'pendiente' => 0,
    'enviado' => 0,
    'recibido' => 0,
    'completado' => 0
];

$stats_prendas = [];
$stats_colores = [];
$stats_tallas = [];
$total_items = 0;

foreach ($transferencias as $row) {
    $estado = strtolower($row['estado'] ?? 'pendiente');
    if (isset($stats[$estado])) {
        $stats[$estado]++;
    }
    
    // Contar items totales
    $items = intval($row['total_items'] ?? 0);
    $total_items += $items;
    
    // Contar por tipo de prenda
    $tipo = $row['tipo_prenda'] ?? 'Sin especificar';
    if (!isset($stats_prendas[$tipo])) {
        $stats_prendas[$tipo] = 0;
    }
    $stats_prendas[$tipo] += $items;
    
    // Contar por color
    $color = $row['color'] ?? 'Sin especificar';
    if ($color && $color !== '') {
        if (!isset($stats_colores[$color])) {
            $stats_colores[$color] = 0;
        }
        $stats_colores[$color] += $items;
    }
    
    // Contar por talla
    $talla = $row['talla'] ?? 'Sin especificar';
    if ($talla && $talla !== '') {
        if (!isset($stats_tallas[$talla])) {
            $stats_tallas[$talla] = 0;
        }
        $stats_tallas[$talla] += $items;
    }
}

// Ordenar por cantidad (mayor a menor)
arsort($stats_prendas);
arsort($stats_colores);
ksort($stats_tallas); // Ordenar tallas alfabéticamente

function h($s) { return htmlspecialchars($s ?? ''); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empaque | Reportes y Exportación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/2/css/theme-orange.css">
    <link rel="stylesheet" href="/2/css/styles.css">
    <style>
        .export-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-left: 4px solid;
            transition: all 0.3s;
        }
        .export-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .export-card.excel { border-left-color: #107C41; }
        .export-card.pdf { border-left-color: #D83B01; }
        .export-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .export-icon.excel { background: linear-gradient(135deg, #107C41, #33A65C); color: white; }
        .export-icon.pdf { background: linear-gradient(135deg, #D83B01, #FF6347); color: white; }
        .stat-mini {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin-bottom: 10px;
        }
        .stat-mini-number {
            font-size: 2rem;
            font-weight: bold;
            color: #FF8C00;
        }
        .stat-mini-label {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
    </style>
<style>
@keyframes gradientShift { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
body { background: linear-gradient(-45deg, #FF8C00, #FFB84D, #FFA500, #FF9933) !important; background-size: 400% 400% !important; animation: gradientShift 15s ease infinite !important; position: relative; overflow-x: hidden; }
body::before, body::after { content: ''; position: fixed; width: 400px; height: 400px; border-radius: 50%; opacity: 0.1; z-index: 0; }
body::before { background: radial-gradient(circle, white, transparent); top: -200px; left: -200px; animation: float 20s ease-in-out infinite; }
body::after { background: radial-gradient(circle, white, transparent); bottom: -200px; right: -200px; animation: float 25s ease-in-out infinite reverse; }
@keyframes float { 0%,100%{transform:translate(0,0) scale(1)} 25%{transform:translate(50px,-50px) scale(1.1)} 50%{transform:translate(-30px,30px) scale(0.9)} 75%{transform:translate(40px,20px) scale(1.05)} }
@keyframes fadeInDown { from{opacity:0;transform:translateY(-30px)} to{opacity:1;transform:translateY(0)} }
@keyframes fadeInUp { from{opacity:0;transform:translateY(30px) scale(0.95)} to{opacity:1;transform:translateY(0) scale(1)} }
@keyframes logoFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
@keyframes slideIn { from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:translateX(0)} }
.header-card, .welcome-card { animation: fadeInDown 0.6s ease-out !important; position: relative; z-index: 10; }
.stat-card { animation: fadeInUp 0.6s ease-out backwards !important; }
.stat-card:nth-child(1) { animation-delay: 0.1s !important; }
.stat-card:nth-child(2) { animation-delay: 0.2s !important; }
.stat-card:nth-child(3) { animation-delay: 0.3s !important; }
.stat-card:nth-child(4) { animation-delay: 0.4s !important; }
.table-card, .form-card { animation: fadeInUp 0.6s ease-out 0.4s backwards !important; }
tbody tr { animation: slideIn 0.4s ease-out backwards; }
tbody tr:nth-child(1) { animation-delay: 0.6s; }
tbody tr:nth-child(2) { animation-delay: 0.65s; }
tbody tr:nth-child(3) { animation-delay: 0.7s; }
tbody tr:nth-child(4) { animation-delay: 0.75s; }
tbody tr:nth-child(5) { animation-delay: 0.8s; }
tbody tr:nth-child(6) { animation-delay: 0.85s; }
tbody tr:nth-child(7) { animation-delay: 0.9s; }
tbody tr:nth-child(8) { animation-delay: 0.95s; }
tbody tr:nth-child(9) { animation-delay: 1s; }
tbody tr:nth-child(10) { animation-delay: 1.05s; }
::selection { background: #FF8C00; color: white; }
::-webkit-scrollbar { width: 10px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #FF8C00, #FFA500); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: linear-gradient(to bottom, #E67E00, #FF8C00); }
</style>
</head>
<body>
    <?php $loading_message = 'Cargando Reportes...'; ?>
    <?php include __DIR__ . '/includes/loading_screen.php'; ?>
    
    <div class="container-fluid">
        <div class="main-container">
            
            <!-- Header -->
            <div class="header-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="/2/img/logo.jpg" alt="Logo" style="height:50px;margin-right:15px;" onerror="this.style.display='none'">
                        <div>
                            <h3 class="mb-0 text-imbox-dark">
                                <i class="fas fa-file-export me-2"></i>Reportes y Exportación
                            </h3>
                            <small class="text-muted">Área de Empaque | Generar reportes en Excel y PDF</small>
                        </div>
                    </div>
                    <div>
                        <a href="transferencias_ui.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-list me-2"></i>Ver Tabla
                        </a>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Rápidas -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-mini">
                        <div class="stat-mini-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-mini-label">Total Registros</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-mini">
                        <div class="stat-mini-number"><?php echo $stats['pendiente'] + $stats['enviado']; ?></div>
                        <div class="stat-mini-label">Pendientes</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-mini">
                        <div class="stat-mini-number"><?php echo $stats['recibido']; ?></div>
                        <div class="stat-mini-label">Recibidas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-mini">
                        <div class="stat-mini-number"><?php echo $stats['completado']; ?></div>
                        <div class="stat-mini-label">Completadas</div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filter-card">
                <h5 class="text-imbox-dark mb-3">
                    <i class="fas fa-filter text-imbox-orange me-2"></i>
                    Filtros de Exportación
                </h5>
                <form id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="filtroEstado">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="enviado">Enviado</option>
                                <option value="recibido">Recibido</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Trabajador</label>
                            <select class="form-select" name="trabajador_id" id="filtroTrabajador">
                                <option value="">Todos</option>
                                <?php foreach ($trabajadores as $trabajador): ?>
                                    <option value="<?php echo $trabajador['id']; ?>">
                                        <?php echo h($trabajador['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" name="fecha_desde" id="filtroFechaDesde">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" name="fecha_hasta" id="filtroFechaHasta" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tablero de Estadísticas Detalladas -->
            <div class="filter-card" style="background: linear-gradient(135deg, #FFF8F0 0%, #FFFFFF 100%); border-left: 4px solid #FF8C00;">
                <h5 class="text-imbox-dark mb-4">
                    <i class="fas fa-chart-bar text-imbox-orange me-2"></i>
                    Resumen de Datos para Exportación
                </h5>
                
                <!-- Total de Items -->
                <div class="alert alert-warning border-0 mb-4" style="background: linear-gradient(135deg, #FFA500 0%, #FFB84D 100%);">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-0">
                                <i class="fas fa-boxes me-2"></i>
                                Total de Items: <strong><?php echo number_format($total_items); ?></strong>
                            </h3>
                            <p class="text-white mb-0" style="opacity: 0.9;">
                                <i class="fas fa-info-circle me-1"></i>
                                Suma de todas las prendas en las <?php echo $stats['total']; ?> transferencias
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-white" style="font-size: 3rem; font-weight: bold; opacity: 0.3;">
                                📦
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Por Tipo de Prenda -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-tshirt me-2"></i>
                                    Por Tipo de Prenda
                                </h6>
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats_prendas as $tipo => $cantidad): 
                                            $porcentaje = $total_items > 0 ? ($cantidad / $total_items) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo h($tipo); ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-primary"><?php echo number_format($cantidad); ?></strong>
                                            </td>
                                            <td class="text-end">
                                                <small class="text-muted"><?php echo number_format($porcentaje, 1); ?>%</small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($stats_prendas)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">
                                                <i class="fas fa-inbox me-1"></i>Sin datos
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Por Color -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-palette me-2"></i>
                                    Por Color
                                </h6>
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Color</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats_colores as $color => $cantidad): 
                                            $porcentaje = $total_items > 0 ? ($cantidad / $total_items) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo h($color); ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success"><?php echo number_format($cantidad); ?></strong>
                                            </td>
                                            <td class="text-end">
                                                <small class="text-muted"><?php echo number_format($porcentaje, 1); ?>%</small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($stats_colores)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">
                                                <i class="fas fa-inbox me-1"></i>Sin datos
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Por Talla -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-ruler me-2"></i>
                                    Por Talla
                                </h6>
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Talla</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats_tallas as $talla => $cantidad): 
                                            $porcentaje = $total_items > 0 ? ($cantidad / $total_items) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo h($talla); ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-warning"><?php echo number_format($cantidad); ?></strong>
                                            </td>
                                            <td class="text-end">
                                                <small class="text-muted"><?php echo number_format($porcentaje, 1); ?>%</small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($stats_tallas)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">
                                                <i class="fas fa-inbox me-1"></i>Sin datos
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nota informativa -->
                <div class="alert alert-info border-0 mt-3 mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Nota:</strong> Estos datos reflejan TODAS las transferencias. 
                    Usa los filtros arriba para ver estadísticas específicas antes de exportar.
                </div>
            </div>

            <!-- Opciones de Exportación -->
            <div class="row g-4">
                
                <!-- Excel/CSV -->
                <div class="col-md-6">
                    <div class="export-card excel">
                        <div class="text-center">
                            <div class="export-icon excel mx-auto">
                                <i class="fas fa-file-excel"></i>
                            </div>
                            <h4 class="mb-3">Exportar a Excel (CSV)</h4>
                            <p class="text-muted mb-4">
                                Descarga todos los datos en formato CSV compatible con Excel, Google Sheets y otros programas de hojas de cálculo.
                            </p>
                            <div class="d-grid gap-2">
                                <button onclick="exportarExcel()" class="btn btn-success btn-lg">
                                    <i class="fas fa-download me-2"></i>Descargar Excel
                                </button>
                                <button onclick="exportarExcelFiltrado()" class="btn btn-outline-success">
                                    <i class="fas fa-filter me-2"></i>Descargar con Filtros
                                </button>
                            </div>
                            <small class="text-muted mt-3 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Incluye todas las columnas: ID, Referencia, Items, Tipo, Color, Talla, Trabajador, Estado, Observaciones, Fecha
                            </small>
                        </div>
                    </div>
                </div>

                <!-- PDF -->
                <div class="col-md-6">
                    <div class="export-card pdf">
                        <div class="text-center">
                            <div class="export-icon pdf mx-auto">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <h4 class="mb-3">Exportar a PDF</h4>
                            <p class="text-muted mb-4">
                                Genera un reporte profesional en PDF listo para imprimir o compartir. Incluye estadísticas y tabla formateada.
                            </p>
                            <div class="d-grid gap-2">
                                <button onclick="exportarPDF()" class="btn btn-danger btn-lg">
                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                </button>
                                <button onclick="exportarPDFFiltrado()" class="btn btn-outline-danger">
                                    <i class="fas fa-filter me-2"></i>PDF con Filtros
                                </button>
                            </div>
                            <small class="text-muted mt-3 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Formato profesional con logo, estadísticas y tabla de datos
                            </small>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Información Adicional -->
            <div class="alert alert-info mt-4">
                <h6 class="alert-heading">
                    <i class="fas fa-lightbulb me-2"></i>Consejos de Uso
                </h6>
                <ul class="mb-0">
                    <li><strong>Excel (CSV):</strong> Mejor para análisis de datos, tablas dinámicas y gráficas personalizadas.</li>
                    <li><strong>PDF:</strong> Ideal para reportes ejecutivos, presentaciones y archivo documental.</li>
                    <li><strong>Filtros:</strong> Usa los filtros arriba para exportar solo los datos que necesitas.</li>
                    <li><strong>Frecuencia:</strong> Recomendamos generar reportes semanales para llevar control histórico.</li>
                </ul>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Exportar a Excel (CSV) - Siempre aplica filtros si existen
        function exportarExcel() {
            const params = obtenerFiltros();
            const url = params ? 'api/exportar.php?formato=excel&' + params : 'api/exportar.php?formato=excel';
            
            // Log para verificar filtros
            if (params) {
                console.log('✅ Exportando Excel CON filtros:', params);
            } else {
                console.log('📊 Exportando Excel SIN filtros (todos los datos)');
            }
            
            window.location.href = url;
        }

        // Exportar a Excel con filtros (alias para compatibilidad)
        function exportarExcelFiltrado() {
            exportarExcel();
        }

        // Exportar a PDF - Siempre aplica filtros si existen
        function exportarPDF() {
            const params = obtenerFiltros();
            const url = params ? 'api/exportar.php?formato=pdf&' + params : 'api/exportar.php?formato=pdf';
            window.open(url, '_blank');
        }

        // Exportar a PDF con filtros (alias para compatibilidad)
        function exportarPDFFiltrado() {
            exportarPDF();
        }

        // Obtener parámetros de filtros
        function obtenerFiltros() {
            const estado = document.getElementById('filtroEstado')?.value || '';
            const trabajador = document.getElementById('filtroTrabajador')?.value || '';
            const fechaDesde = document.getElementById('filtroFechaDesde')?.value || '';
            const fechaHasta = document.getElementById('filtroFechaHasta')?.value || '';

            console.log('🔍 Valores de filtros detectados:', {
                estado: estado,
                trabajador: trabajador,
                fechaDesde: fechaDesde,
                fechaHasta: fechaHasta
            });

            let params = [];
            if (estado && estado !== '') params.push('estado=' + encodeURIComponent(estado));
            if (trabajador && trabajador !== '') params.push('trabajador_id=' + encodeURIComponent(trabajador));
            if (fechaDesde && fechaDesde !== '') params.push('fecha_desde=' + encodeURIComponent(fechaDesde));
            if (fechaHasta && fechaHasta !== '') params.push('fecha_hasta=' + encodeURIComponent(fechaHasta));

            const queryString = params.join('&');
            console.log('📤 Query string generado:', queryString);
            
            return queryString;
        }

        // Mostrar indicador visual cuando hay filtros activos
        function actualizarIndicadorFiltros() {
            const estado = document.getElementById('filtroEstado').value;
            const trabajador = document.getElementById('filtroTrabajador').value;
            const fechaDesde = document.getElementById('filtroFechaDesde').value;
            const fechaHasta = document.getElementById('filtroFechaHasta').value;
            
            const hayFiltros = estado || trabajador || fechaDesde || fechaHasta;
            
            // Actualizar botones de exportación
            const botonesExportar = document.querySelectorAll('.btn-success, .btn-danger');
            botonesExportar.forEach(btn => {
                if (hayFiltros) {
                    // Cambiar texto del botón para indicar que usa filtros
                    if (btn.textContent.includes('Excel')) {
                        btn.innerHTML = '<i class="fas fa-filter me-2"></i>Descargar Excel (Con Filtros)';
                    } else if (btn.textContent.includes('PDF')) {
                        btn.innerHTML = '<i class="fas fa-filter me-2"></i>Generar PDF (Con Filtros)';
                    }
                    btn.classList.add('border', 'border-warning', 'border-3');
                } else {
                    // Texto original
                    if (btn.textContent.includes('Excel')) {
                        btn.innerHTML = '<i class="fas fa-download me-2"></i>Descargar Excel';
                    } else if (btn.textContent.includes('PDF')) {
                        btn.innerHTML = '<i class="fas fa-file-pdf me-2"></i>Generar PDF';
                    }
                    btn.classList.remove('border', 'border-warning', 'border-3');
                }
            });
        }
        
        // Ejecutar al cargar y al cambiar filtros
        document.addEventListener('DOMContentLoaded', actualizarIndicadorFiltros);
        document.getElementById('filtroEstado')?.addEventListener('change', actualizarIndicadorFiltros);
        document.getElementById('filtroTrabajador')?.addEventListener('change', actualizarIndicadorFiltros);
        document.getElementById('filtroFechaDesde')?.addEventListener('change', actualizarIndicadorFiltros);
        document.getElementById('filtroFechaHasta')?.addEventListener('change', actualizarIndicadorFiltros);
    </script>
</body>
</html>
