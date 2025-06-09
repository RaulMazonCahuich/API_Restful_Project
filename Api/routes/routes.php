<?php
require_once __DIR__ . '/../controllers/ProductoController.php';
require_once __DIR__ . '/../controllers/CategoriaController.php';
require_once __DIR__ . '/../controllers/ProveedorController.php';
require_once __DIR__ . '/../helpers/auth.php';

// Configurar headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$controller = null;
$method = $_SERVER['REQUEST_METHOD'];

// Obtener el ID de la URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Buscar índice 
$publicIndex = array_search('public', $pathParts);
if ($publicIndex !== false && isset($pathParts[$publicIndex + 1])) {
    $resource = $pathParts[$publicIndex + 1];
    $id = isset($pathParts[$publicIndex + 2]) ? $pathParts[$publicIndex + 2] : null;
} else {
    // Fallback al método anterior
    $id = $_GET['id'] ?? null;
    $resource = '';
    
    if (strpos($_SERVER['REQUEST_URI'], 'productos') !== false) {
        $resource = 'productos';
    } elseif (strpos($_SERVER['REQUEST_URI'], 'categorias') !== false) {
        $resource = 'categorias';
    } elseif (strpos($_SERVER['REQUEST_URI'], 'proveedores') !== false) {
        $resource = 'proveedores';
    }
}

$data = json_decode(file_get_contents('php://input'), true);

// Autenticación
if (!Auth::check()) {
    http_response_code(401);
    die(json_encode(['error' => 'No autorizado']));
}

// Determinar controlador dependiendo del recurso
switch ($resource) {
    case 'productos':
        $controller = new ProductoController();
        break;
    case 'categorias':
        $controller = new CategoriaController();
        break;
    case 'proveedores':
        $controller = new ProveedorController();
        break;
    default:
        http_response_code(404);
        die(json_encode(['error' => 'Ruta no encontrada']));
}

// Manejar métodos HTTP
switch ($method) {
    case 'GET':
        if ($id) {
            $controller->show($id);
        } else {
            $controller->index();
        }
        break;
    case 'POST':
        $controller->store($data);
        break;
    case 'PUT':
        if ($id) {
            $controller->update($id, $data);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido para actualización']);
        }
        break;
    case 'DELETE':
        if ($id) {
            $controller->destroy($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido para eliminación']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>