<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../models/ControlEntrada.php';
require_once '../models/DetallePrenda.php';

$controlEntrada = new ControlEntrada();
$detallePrenda = new DetallePrenda();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

try {
    switch($method) {
        case 'GET':
            if (empty($request[0])) {
                // Obtener todos los registros
                $limite = $_GET['limite'] ?? 50;
                $offset = $_GET['offset'] ?? 0;
                $registros = $controlEntrada->obtenerTodos($limite, $offset);
                
                echo json_encode([
                    'success' => true,
                    'data' => $registros
                ]);
            } elseif ($request[0] == 'buscar' && $request[1] == 'fecha') {
                // Buscar por fecha
                $fechaInicio = $_GET['fechaInicio'] ?? '';
                $fechaFin = $_GET['fechaFin'] ?? '';
                
                if (empty($fechaInicio) || empty($fechaFin)) {
                    throw new Exception('Se requieren fechaInicio y fechaFin');
                }
                
                $registros = $controlEntrada->buscarPorFecha($fechaInicio, $fechaFin);
                echo json_encode([
                    'success' => true,
                    'data' => $registros
                ]);
            } elseif (is_numeric($request[0])) {
                // Obtener por ID con detalles
                $id = $request[0];
                $registro = $controlEntrada->obtenerPorId($id);
                
                if (!$registro) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Registro no encontrado'
                    ]);
                    exit;
                }
                
                $detalles = $detallePrenda->obtenerPorControlEntrada($id);
                $resumen = $detallePrenda->obtenerResumenTallas($id);
                
                echo json_encode([
                    'success' => true,
                    'data' => array_merge($registro, [
                        'detalles' => $detalles,
                        'resumen' => $resumen
                    ])
                ]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Datos inválidos');
            }
            
            $controlEntradaData = $input['controlEntrada'];
            $detalles = $input['detalles'] ?? [];
            
            // Crear el registro principal
            $controlEntradaId = $controlEntrada->crear($controlEntradaData);
            
            if (!$controlEntradaId) {
                throw new Exception('Error creando registro principal');
            }
            
            // Crear los detalles si existen
            if (!empty($detalles)) {
                foreach ($detalles as &$detalle) {
                    $detalle['control_entrada_id'] = $controlEntradaId;
                }
                $detallePrenda->crearMultiples($detalles);
            }
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Registro creado exitosamente',
                'data' => ['id' => $controlEntradaId]
            ]);

            // Si se configuró REMOTE_TRANSFER_URL en config, notificar al servidor remoto para crear una transferencia
            require_once __DIR__ . '/../config/config.php';
            if (defined('REMOTE_TRANSFER_URL') && REMOTE_TRANSFER_URL !== '') {
                // Preparar payload mínimo
                $payload = [
                    'referencia' => 'TR-' . $controlEntradaId,
                    'almacen_origen_id' => LOCAL_ALMACEN_ID,
                    'almacen_destino_id' => null, // el destino deberá determinarse en el servidor remoto o configurarse manualmente
                    'control_entrada_id' => $controlEntradaId,
                    'total_items' => $detalles ? count($detalles) : 0,
                    'usuario_creacion' => $controlEntradaData['recepcionista_id'] ?? 'sistema',
                    'observaciones' => 'Auto-transfer from control_entrada ' . $controlEntradaId
                ];

                // Intentar POST al endpoint remoto (sin bloquear el flujo si falla)
                try {
                    $url = rtrim(REMOTE_TRANSFER_URL, '/') . '/api/transferencias.php';
                    $options = [
                        'http' => [
                            'header'  => "Content-type: application/json\r\n" . (defined('REMOTE_API_TOKEN') && REMOTE_API_TOKEN !== '' ? "X-API-TOKEN: " . REMOTE_API_TOKEN . "\r\n" : ''),
                            'method'  => 'POST',
                            'content' => json_encode($payload),
                            'timeout' => 5
                        ]
                    ];
                    $context  = stream_context_create($options);
                    @file_get_contents($url, false, $context);
                } catch (Exception $e) {
                    // Silenciar errores de notificación remota
                }
            }
            break;
            
        case 'PUT':
            if (empty($request[0]) || !is_numeric($request[0])) {
                throw new Exception('ID requerido');
            }
            
            $id = $request[0];
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['controlEntrada'])) {
                throw new Exception('Datos inválidos');
            }
            
            $result = $controlEntrada->actualizar($id, $input['controlEntrada']);
            
            if (!$result) {
                throw new Exception('Error actualizando registro');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Registro actualizado exitosamente'
            ]);
            break;
            
        case 'DELETE':
            if (empty($request[0]) || !is_numeric($request[0])) {
                throw new Exception('ID requerido');
            }
            
            $id = $request[0];
            $result = $controlEntrada->eliminar($id);
            
            if (!$result) {
                throw new Exception('Error eliminando registro');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Registro eliminado exitosamente'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>