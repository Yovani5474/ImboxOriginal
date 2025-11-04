<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../models/Transferencia.php';
require_once '../models/Almacen.php';
require_once '../models/Trabajador.php';
require_once __DIR__ . '/../config/config.php';

$transferenciaModel = new Transferencia();
$almacenModel = new Almacen();
 $trabajadorModel = new Trabajador();

$method = $_SERVER['REQUEST_METHOD'];

// Parsear la ruta - manejar tanto PATH_INFO como REQUEST_URI
$path_info = $_SERVER['PATH_INFO'] ?? '';
if (empty($path_info)) {
    // Intentar extraer de REQUEST_URI
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($uri, $script_name) === 0) {
        $path_info = substr($uri, strlen($script_name));
    }
    // Remover query string
    if (($pos = strpos($path_info, '?')) !== false) {
        $path_info = substr($path_info, 0, $pos);
    }
}

$request = explode('/', trim($path_info, '/'));

// Log para debugging
error_log("API Request - Method: $method, Path: $path_info, Request: " . json_encode($request));

try {
    switch ($method) {
        case 'GET':
            if (empty($request[0])) {
                $limit = $_GET['limit'] ?? 50;
                $offset = $_GET['offset'] ?? 0;
                $data = $transferenciaModel->listar((int)$limit, (int)$offset);
                echo json_encode(['success' => true, 'data' => $data]);
                exit;
            } elseif (is_numeric($request[0])) {
                $id = $request[0];
                $data = $transferenciaModel->obtenerPorId($id);
                if (!$data) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Transferencia no encontrada']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $data]);
                exit;
            }
            break;

        case 'POST':
            // Verificar si es una confirmación desde el formulario
            if (isset($request[0]) && is_numeric($request[0]) && isset($request[1]) && $request[1] == 'confirmar_trabajador') {
                $id = $request[0];
                $input = $_POST;
                
                // Log para debugging
                error_log("Confirmación trabajador - ID: $id, POST data: " . json_encode(array_keys($input)));
                
                // Validaciones específicas con mensajes descriptivos
                $errores = [];
                
                if (empty($input)) {
                    throw new Exception('No se recibieron datos del formulario. Por favor intente nuevamente.');
                }
                
                // Validar campos requeridos
                if (!isset($input['trabajador_id']) || empty($input['trabajador_id'])) {
                    $errores[] = '• Falta seleccionar el TRABAJADOR encargado';
                }
                
                if (!isset($input['fecha_recepcion']) || empty($input['fecha_recepcion'])) {
                    $errores[] = '• Falta agregar la FECHA DE RECEPCIÓN';
                }
                
                // Validar que haya al menos un item con datos
                $tiene_items = false;
                if (isset($input['items']) && is_array($input['items'])) {
                    foreach ($input['items'] as $item) {
                        if (!empty($item['color_codigo']) || 
                            !empty(array_filter($item, function($v, $k) {
                                return strpos($k, 'talla_') === 0 && $v > 0;
                            }, ARRAY_FILTER_USE_BOTH))) {
                            $tiene_items = true;
                            break;
                        }
                    }
                }
                
                if (!$tiene_items) {
                    $errores[] = '• No hay datos ingresados en la tabla de TALLAS';
                }
                
                // Si hay errores, lanzar excepción con todos los mensajes
                if (!empty($errores)) {
                    throw new Exception("Faltan datos requeridos:\n\n" . implode("\n", $errores));
                }
                
                // Validar que el trabajador exista
                $trab = $trabajadorModel->obtenerPorId($input['trabajador_id']);
                if (!$trab) {
                    throw new Exception('El trabajador seleccionado no existe en el sistema');
                }
                
                // Procesar los items del formulario si existen
                $items_procesados = [];
                if (isset($input['items'])) {
                    foreach ($input['items'] as $numero => $item) {
                        // Solo procesar items que tengan al menos un campo lleno
                        $item_data = [
                            'numero_item' => $numero,
                            'color_codigo' => $item['color_codigo'] ?? '',
                            'talla_2' => intval($item['talla_2'] ?? 0),
                            'talla_4' => intval($item['talla_4'] ?? 0),
                            'talla_6' => intval($item['talla_6'] ?? 0),
                            'talla_8' => intval($item['talla_8'] ?? 0),
                            'talla_10' => intval($item['talla_10'] ?? 0),
                            'talla_12' => intval($item['talla_12'] ?? 0),
                            'talla_14' => intval($item['talla_14'] ?? 0),
                            'talla_16' => intval($item['talla_16'] ?? 0),
                            'talla_xs' => intval($item['talla_xs'] ?? 0),
                            'talla_s' => intval($item['talla_s'] ?? 0),
                            'talla_m' => intval($item['talla_m'] ?? 0),
                            'talla_l' => intval($item['talla_l'] ?? 0),
                            'talla_xl' => intval($item['talla_xl'] ?? 0),
                            'talla_xxl' => intval($item['talla_xxl'] ?? 0),
                            'observacion' => $item['observacion'] ?? ''
                        ];
                        
                        // Verificar si tiene datos (suma de todas las tallas)
                        $suma_tallas = $item_data['talla_2'] + $item_data['talla_4'] + $item_data['talla_6'] + 
                                      $item_data['talla_8'] + $item_data['talla_10'] + $item_data['talla_12'] + 
                                      $item_data['talla_14'] + $item_data['talla_16'] + $item_data['talla_xs'] + 
                                      $item_data['talla_s'] + $item_data['talla_m'] + $item_data['talla_l'] + 
                                      $item_data['talla_xl'] + $item_data['talla_xxl'];
                        
                        if (!empty($item_data['color_codigo']) || $suma_tallas > 0 || !empty($item_data['observacion'])) {
                            $items_procesados[] = $item_data;
                        }
                    }
                }
                
                // Calcular total de items
                $total_items_calculado = 0;
                foreach ($items_procesados as $item) {
                    $total_items_calculado += intval($item['talla_2'] ?? 0);
                    $total_items_calculado += intval($item['talla_4'] ?? 0);
                    $total_items_calculado += intval($item['talla_6'] ?? 0);
                    $total_items_calculado += intval($item['talla_8'] ?? 0);
                    $total_items_calculado += intval($item['talla_10'] ?? 0);
                    $total_items_calculado += intval($item['talla_12'] ?? 0);
                    $total_items_calculado += intval($item['talla_14'] ?? 0);
                    $total_items_calculado += intval($item['talla_16'] ?? 0);
                    $total_items_calculado += intval($item['talla_xs'] ?? 0);
                    $total_items_calculado += intval($item['talla_s'] ?? 0);
                    $total_items_calculado += intval($item['talla_m'] ?? 0);
                    $total_items_calculado += intval($item['talla_l'] ?? 0);
                    $total_items_calculado += intval($item['talla_xl'] ?? 0);
                    $total_items_calculado += intval($item['talla_xxl'] ?? 0);
                }
                
                $datos_recepcion = [
                    'fecha_recepcion' => $input['fecha_recepcion'] ?? date('Y-m-d'),
                    'tipo_prenda' => $input['tipo_prenda'] ?? '',
                    'items' => $items_procesados,
                    'total_items' => $total_items_calculado
                ];
                
                $faltantes = [];
                
                try {
                    $ok = $transferenciaModel->confirmarPorTrabajador($id, $input['trabajador_id'], $faltantes, $datos_recepcion);
                    if (!$ok) {
                        throw new Exception('Error confirmando por trabajador');
                    }
                    
                    // Actualizar campos adicionales en la transferencia
                    $transferenciaModel->actualizar($id, [
                        'tipo_prenda' => $input['tipo_prenda'] ?? null,
                        'total_items' => $total_items_calculado
                    ]);
                    
                    // Obtener datos actualizados de la transferencia
                    $transferencia_actualizada = $transferenciaModel->obtenerPorId($id);
                    $estado = $transferencia_actualizada['estado'];
                    
                    // Redirigir con mensaje apropiado según el estado
                    if ($estado == 'completado') {
                        header('Location: /2/control_entrada_almacen2.php?success=completado&id=' . $id);
                    } elseif ($estado == 'parcial') {
                        header('Location: /2/control_entrada_almacen2.php?success=parcial&id=' . $id);
                    } else {
                        header('Location: /2/control_entrada_almacen2.php?success=recibido&id=' . $id);
                    }
                    exit;
                } catch (Exception $e) {
                    // Log del error
                    error_log("Error en confirmación: " . $e->getMessage());
                    throw new Exception('Error confirmando por trabajador: ' . $e->getMessage());
                }
            }

            // Si se ha configurado REMOTE_API_TOKEN, validar el token en el header 'X-API-TOKEN'
            if (defined('REMOTE_API_TOKEN') && REMOTE_API_TOKEN !== '') {
                $headers = getallheaders();
                $provided = $headers['X-API-TOKEN'] ?? $headers['x-api-token'] ?? '';
                if ($provided !== REMOTE_API_TOKEN) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Token invalido']);
                    exit;
                }
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception('Datos invalidos');

            // Campos esperados: referencia, almacen_origen_id, almacen_destino_id, control_entrada_id (opcional), total_items, usuario_creacion, observaciones
            $required = ['referencia','almacen_origen_id','almacen_destino_id','total_items','usuario_creacion'];
            foreach ($required as $r) {
                if (!isset($input[$r])) throw new Exception('Campo requerido: ' . $r);
            }

            // Verificar que almacenes existan
            $origen = $almacenModel->obtenerPorId($input['almacen_origen_id']);
            $destino = $almacenModel->obtenerPorId($input['almacen_destino_id']);
            if (!$origen || !$destino) throw new Exception('Almacen origen o destino no existe');

            // Si se envió trabajador_id validar que exista
            if (isset($input['trabajador_id']) && $input['trabajador_id'] !== null) {
                $trab = $trabajadorModel->obtenerPorId($input['trabajador_id']);
                if (!$trab) throw new Exception('Trabajador no encontrado');
            }

            $payload = [
                'referencia' => $input['referencia'],
                'almacen_origen_id' => $input['almacen_origen_id'],
                'almacen_destino_id' => $input['almacen_destino_id'],
                'control_entrada_id' => $input['control_entrada_id'] ?? null,
                'trabajador_id' => $input['trabajador_id'] ?? null,
                'trabajador_nombre' => $input['trabajador_nombre'] ?? null,
                'total_items' => $input['total_items'],
                'tipo_prenda' => $input['tipo_prenda'] ?? null,
                'color' => $input['color'] ?? null,
                'talla' => $input['talla'] ?? null,
                'estado' => $input['estado'] ?? 'pendiente',
                'usuario_creacion' => $input['usuario_creacion'],
                'observaciones' => $input['observaciones'] ?? null
            ];

            $id = $transferenciaModel->crear($payload);
            if (!$id) throw new Exception('Error creando transferencia');

            http_response_code(201);
            echo json_encode(['success' => true, 'data' => ['id' => $id]]);
            exit;
            break;

        case 'PUT':
            // PUT /api/transferencias.php/{id} - Actualizar transferencia
            // PUT /api/transferencias.php/{id}/recibir - Marcar como recibido
            if (empty($request[0]) || !is_numeric($request[0])) throw new Exception('ID requerido');
            $id = $request[0];

            $action = $request[1] ?? '';
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);

            if ($action == 'recibir') {
                // Acción específica: marcar como recibido
                if (!$input || !isset($input['usuario_recepcion'])) throw new Exception('usuario_recepcion requerido');
                $ok = $transferenciaModel->marcarRecibido($id, $input['usuario_recepcion']);
                if (!$ok) throw new Exception('Error marcando como recibido');
                echo json_encode(['success' => true, 'message' => 'Transferencia marcada como recibida']);
                exit;
            } else if (empty($action)) {
                // Actualización general de campos (para edición tipo Excel)
                if (empty($input)) throw new Exception('No se enviaron datos para actualizar');
                
                $ok = $transferenciaModel->actualizar($id, $input);
                if (!$ok) throw new Exception('Error al actualizar la transferencia');
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Transferencia actualizada correctamente',
                    'id' => $id
                ]);
                exit;
            } else {
                throw new Exception('Accion no valida');
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            break;
    }
} catch (Exception $e) {
    // Log del error para debugging
    error_log("API Error: " . $e->getMessage());
    error_log("Request URI: " . $_SERVER['REQUEST_URI']);
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI']
        ]
    ]);
}

?>
