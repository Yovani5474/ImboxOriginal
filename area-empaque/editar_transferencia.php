<?php
/**
 * Editar Transferencia - Área de Empaque
 * Permite corregir y completar datos de transferencias incompletas o con errores
 */

session_start();

require_once __DIR__ . '/models/Transferencia.php';
require_once __DIR__ . '/models/Trabajador.php';
require_once __DIR__ . '/models/HistorialCambios.php';
require_once __DIR__ . '/models/Usuario.php';

$t = new Transferencia();
$tr = new Trabajador();
$historial = new HistorialCambios();
$trabajadores = $tr->obtenerTodos();

// Obtener usuario actual (si está autenticado)
$usuario_nombre = 'sistema';
$usuario_rol = 'operador';
if (Usuario::sesionActiva()) {
    $usuario_actual = Usuario::usuarioActual();
    $usuario_nombre = $usuario_actual['nombre'];
    $usuario_rol = $usuario_actual['rol'];
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mensaje = '';
$error = '';

// Obtener la transferencia
$transferencia = null;
if ($id > 0) {
    $list = $t->listar(1, 0);
    foreach ($list as $item) {
        if ($item['id'] == $id) {
            $transferencia = $item;
            break;
        }
    }
}

if (!$transferencia) {
    header('Location: transferencias_ui.php');
    exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Guardar datos anteriores para el historial
        $datos_anteriores = $transferencia;
        
        $data = [
            'referencia' => trim($_POST['referencia'] ?? ''),
            'total_items' => intval($_POST['total_items'] ?? 0),
            'tipo_prenda' => trim($_POST['tipo_prenda'] ?? ''),
            'color' => trim($_POST['color'] ?? ''),
            'talla' => trim($_POST['talla'] ?? ''),
            'trabajador_id' => !empty($_POST['trabajador_id']) ? intval($_POST['trabajador_id']) : null,
            'trabajador_nombre' => trim($_POST['trabajador_nombre'] ?? ''),
            'estado' => $_POST['estado'] ?? 'pendiente',
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];
        
        if ($t->actualizar($id, $data)) {
            // Recargar datos
            $list = $t->listar(1, 0);
            foreach ($list as $item) {
                if ($item['id'] == $id) {
                    $transferencia = $item;
                    break;
                }
            }
            
            // Registrar cambios en el historial
            $historial->registrarCambioTransferencia($id, $datos_anteriores, $transferencia, $usuario_nombre, $usuario_rol);
            
            $mensaje = 'Transferencia actualizada correctamente. Cambios registrados en el historial.';
        } else {
            $error = 'Error al actualizar la transferencia';
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

function h($s) { return htmlspecialchars($s ?? ''); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empaque | Editar Transferencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/2/css/theme-orange.css">
    <link rel="stylesheet" href="/2/css/styles.css">
    <style>
        .main-container {
            max-width: 1000px;
        }
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
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

/* Estilos para campos animados */
.form-control-animated {
    transition: all 0.3s ease;
    border: 2px solid #e0e0e0;
}

.form-control-animated:focus {
    border-color: #FF8C00;
    box-shadow: 0 0 0 0.2rem rgba(255, 140, 0, 0.25);
    transform: translateY(-2px);
}

.form-control-animated:hover {
    border-color: #FFB84D;
}

/* Mejora visual para badges de feedback */
.form-text {
    margin-top: 0.5rem;
    font-size: 0.875em;
    padding: 0.25rem 0.5rem;
    background: #f8f9fa;
    border-radius: 5px;
    display: inline-block;
}

/* Efecto hover en botones */
.btn-outline-secondary:hover {
    background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
    border-color: #FF8C00;
    color: white;
}

/* Validación visual */
input:required:invalid {
    border-left: 3px solid #FFC107;
}

input:required:valid {
    border-left: 3px solid #28A745;
}
</style>
</head>
<body>
    <?php $loading_message = 'Cargando Editor de Transferencias...'; ?>
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
                                <i class="fas fa-edit me-2"></i>Editar Transferencia #<?= h($transferencia['id']) ?>
                            </h3>
                            <small class="text-muted">Área de Empaque | Corregir datos incompletos o erróneos</small>
                        </div>
                    </div>
                    <a href="transferencias_ui.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>

            <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <strong>¡Éxito!</strong> <?= h($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error:</strong> <?= h($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Formulario de Edición -->
            <div class="form-card">
                <form method="post" id="formEditar">
                    <div class="row g-3">
                        
                        <!-- Información Básica -->
                        <div class="col-12">
                            <h6 class="text-imbox-orange mb-3">
                                <i class="fas fa-info-circle me-2"></i>Información Básica
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Referencia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="referencia" 
                                   value="<?= h($transferencia['referencia']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-boxes me-1"></i>Total Items <span class="text-danger">*</span>
                                <i class="fas fa-info-circle ms-1" data-bs-toggle="tooltip" 
                                   title="Cantidad total de prendas" style="font-size: 0.8em; color: #6c757d;"></i>
                            </label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary" type="button" onclick="decrementarItems()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="form-control form-control-animated text-center" 
                                       name="total_items" id="totalItems"
                                       value="<?= h($transferencia['total_items']) ?>" min="1" required
                                       style="font-weight: 700; font-size: 1.2em;">
                                <button class="btn btn-outline-secondary" type="button" onclick="incrementarItems()">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <span class="input-group-text bg-success text-white">
                                    <i class="fas fa-check"></i>
                                </span>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-calculator text-info me-1"></i>
                                <span id="itemsText"><?= h($transferencia['total_items']) ?> item<?= $transferencia['total_items'] > 1 ? 's' : '' ?> seleccionado<?= $transferencia['total_items'] > 1 ? 's' : '' ?></span>
                            </div>
                        </div>

                        <!-- Detalles de Prenda -->
                        <div class="col-12">
                            <hr class="my-3">
                            <h6 class="text-imbox-orange mb-3">
                                <i class="fas fa-tshirt me-2"></i>Detalles de la Prenda
                            </h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-tshirt me-1"></i>Tipo de Prenda
                                <i class="fas fa-info-circle ms-1" data-bs-toggle="tooltip" 
                                   title="Tipo de prenda a transferir" style="font-size: 0.8em; color: #6c757d;"></i>
                            </label>
                            <select class="form-select form-control-animated" name="tipo_prenda" id="tipoPrenda">
                                <option value="">-- Seleccionar tipo --</option>
                                <?php
                                $tipos = [
                                    'Camisa' => '👔 Camisa',
                                    'Pantalón' => '👖 Pantalón',
                                    'Blusa' => '👚 Blusa',
                                    'Vestido' => '👗 Vestido',
                                    'Falda' => '🩱 Falda',
                                    'Chaqueta' => '🧥 Chaqueta',
                                    'Polo' => '👕 Polo',
                                    'Short' => '🩳 Short',
                                    'Otros' => '🎽 Otros'
                                ];
                                foreach ($tipos as $valor => $texto):
                                    $selected = ($transferencia['tipo_prenda'] === $valor) ? 'selected' : '';
                                ?>
                                    <option value="<?= h($valor) ?>" <?= $selected ?>><?= $texto ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-tag text-primary me-1"></i>
                                <span id="prendaSeleccionada"><?= $transferencia['tipo_prenda'] ? h($transferencia['tipo_prenda']) . ' seleccionado' : 'Tipo no seleccionado' ?></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-palette me-1"></i>Color
                                <i class="fas fa-info-circle ms-1" data-bs-toggle="tooltip" 
                                   title="Color de la prenda" style="font-size: 0.8em; color: #6c757d;"></i>
                            </label>
                            <input type="text" class="form-control form-control-animated" name="color" id="colorPrenda"
                                   value="<?= h($transferencia['color']) ?>" placeholder="Ej: Azul, Rojo, Negro..." list="coloresComunes">
                            <datalist id="coloresComunes">
                                <option value="Blanco">
                                <option value="Negro">
                                <option value="Azul">
                                <option value="Rojo">
                                <option value="Verde">
                                <option value="Amarillo">
                                <option value="Rosa">
                                <option value="Gris">
                                <option value="Marrón">
                                <option value="Beige">
                                <option value="Naranja">
                                <option value="Morado">
                            </datalist>
                            <div class="form-text">
                                <i class="fas fa-brush text-success me-1"></i>
                                <span id="colorTexto"><?= $transferencia['color'] ? 'Color: ' . h($transferencia['color']) : 'Escribe o selecciona un color' ?></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Talla</label>
                            <select class="form-select" name="talla">
                                <option value="">-- Seleccionar --</option>
                                <optgroup label="Tallas Numéricas">
                                    <?php for ($i = 2; $i <= 20; $i += 2): 
                                        $selected = ($transferencia['talla'] == $i) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $i ?>" <?= $selected ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </optgroup>
                                <optgroup label="Tallas por Letra">
                                    <?php
                                    $tallas = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
                                    foreach ($tallas as $talla):
                                        $selected = ($transferencia['talla'] === $talla) ? 'selected' : '';
                                    ?>
                                        <option value="<?= h($talla) ?>" <?= $selected ?>><?= h($talla) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Otras">
                                    <option value="Única" <?= ($transferencia['talla'] === 'Única') ? 'selected' : '' ?>>Talla Única</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Asignación -->
                        <div class="col-12">
                            <hr class="my-3">
                            <h6 class="text-imbox-orange mb-3">
                                <i class="fas fa-user-tie me-2"></i>Asignación de Trabajador
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Trabajador (por ID)</label>
                            <select class="form-select" name="trabajador_id">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($trabajadores as $trabajador): 
                                    $selected = ($transferencia['trabajador_id'] == $trabajador['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $trabajador['id'] ?>" <?= $selected ?>>
                                        <?= h($trabajador['nombre']) ?> (ID: <?= $trabajador['id'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nombre del Trabajador</label>
                            <input type="text" class="form-control" name="trabajador_nombre" 
                                   value="<?= h($transferencia['trabajador_nombre']) ?>" 
                                   placeholder="Nombre del trabajador...">
                            <small class="text-muted">Se usará si no se selecciona ID</small>
                        </div>

                        <!-- Estado -->
                        <div class="col-12">
                            <hr class="my-3">
                            <h6 class="text-imbox-orange mb-3">
                                <i class="fas fa-traffic-light me-2"></i>Estado
                            </h6>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Estado de la Transferencia</label>
                            <select class="form-select" name="estado">
                                <option value="pendiente" <?= ($transferencia['estado'] === 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                                <option value="enviado" <?= ($transferencia['estado'] === 'enviado') ? 'selected' : '' ?>>Enviado</option>
                                <option value="recibido" <?= ($transferencia['estado'] === 'recibido') ? 'selected' : '' ?>>Recibido</option>
                                <option value="completado" <?= ($transferencia['estado'] === 'completado') ? 'selected' : '' ?>>Completado</option>
                            </select>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-12">
                            <hr class="my-3">
                            <h6 class="text-imbox-orange mb-3">
                                <i class="fas fa-comment-dots me-2"></i>Observaciones
                            </h6>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observaciones y Notas</label>
                            <textarea class="form-control" name="observaciones" rows="4" 
                                      placeholder="Detalles, correcciones, notas adicionales..."><?= h($transferencia['observaciones']) ?></textarea>
                            <small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                Usa este espacio para anotar correcciones, fallos detectados, o información adicional
                            </small>
                        </div>

                        <!-- Botones -->
                        <div class="col-12 text-end mt-4">
                            <a href="transferencias_ui.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Info adicional -->
            <div class="mt-4 p-3 bg-light rounded border border-warning">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Nota:</strong> Esta función permite corregir datos que llegaron incompletos o con errores desde Corte. 
                    Todos los cambios quedarán registrados en el sistema.
                </small>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmación antes de guardar
        document.getElementById('formEditar').addEventListener('submit', function(e) {
            if (!confirm('¿Confirmar actualización de la transferencia #<?= $transferencia['id'] ?>?')) {
                e.preventDefault();
            }
        });

        // Auto-fade para alertas de éxito
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-success');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000);
        
        // ========== FUNCIONES INTERACTIVAS ==========
        
        // Incrementar/Decrementar items
        function incrementarItems() {
            const input = document.getElementById('totalItems');
            let value = parseInt(input.value) || 0;
            input.value = value + 1;
            actualizarTextoItems();
        }
        
        function decrementarItems() {
            const input = document.getElementById('totalItems');
            let value = parseInt(input.value) || 0;
            if (value > 1) {
                input.value = value - 1;
                actualizarTextoItems();
            }
        }
        
        function actualizarTextoItems() {
            const value = parseInt(document.getElementById('totalItems').value) || 0;
            const texto = value === 1 ? '1 item seleccionado' : `${value} items seleccionados`;
            document.getElementById('itemsText').textContent = texto;
        }
        
        // Actualizar texto de tipo de prenda
        document.getElementById('tipoPrenda')?.addEventListener('change', function() {
            if (this.value) {
                document.getElementById('prendaSeleccionada').innerHTML = `<strong>${this.value}</strong> seleccionado`;
            } else {
                document.getElementById('prendaSeleccionada').textContent = 'Tipo no seleccionado';
            }
        });
        
        // Actualizar texto de color
        document.getElementById('colorPrenda')?.addEventListener('input', function() {
            if (this.value) {
                document.getElementById('colorTexto').innerHTML = `Color: <strong>${this.value}</strong>`;
            } else {
                document.getElementById('colorTexto').textContent = 'Escribe o selecciona un color';
            }
        });
        
        // Actualizar contador de items en tiempo real
        document.getElementById('totalItems')?.addEventListener('input', function() {
            actualizarTextoItems();
        });
        
        // Inicializar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>
