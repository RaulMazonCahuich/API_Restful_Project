<?php
require_once __DIR__ . '/../controllers/ProductoController.php';
require_once __DIR__ . '/../helpers/auth.php';

$controller = new ProductoController();

if (!Auth::check()) {
  http_response_code(401);
  die(json_encode(["error" => "No autorizado"]));
}

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
  case 'GET': $id ? $controller->show($id) : $controller->index(); break;
  case 'POST': $controller->store($data); break;
  case 'PUT': $controller->update($id, $data); break;
  case 'DELETE': $controller->destroy($id); break;
}
