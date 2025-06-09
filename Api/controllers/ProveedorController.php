<?php
require_once __DIR__ . '/../models/Proveedor.php';

class ProveedorController {
    private $model;

    public function __construct() {
        $this->model = new Proveedor();
    }

    public function index() {
        try {
            $data = $this->model->getAll();
            if ($data) {
                echo json_encode($data->fetch_all(MYSQLI_ASSOC));
            } else {
                echo json_encode([]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener proveedores: ' . $e->getMessage()]);
        }
    }

    public function show($id) {
        try {
            $data = $this->model->get($id);
            if ($data) {
                $result = $data->fetch_assoc();
                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Proveedor no encontrado']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Proveedor no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener proveedor: ' . $e->getMessage()]);
        }
    }

    public function store($data) {
        try {
            if (!$data || !isset($data['nombre']) || !isset($data['telefono']) || !isset($data['email'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                return;
            }
            
            $success = $this->model->create($data);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear proveedor: ' . $e->getMessage()]);
        }
    }

    public function update($id, $data) {
        try {
            if (!$data || !isset($data['nombre']) || !isset($data['telefono']) || !isset($data['email'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                return;
            }
            
            $success = $this->model->update($id, $data);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar proveedor: ' . $e->getMessage()]);
        }
    }

    public function destroy($id) {
        try {
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
                return;
            }
            
            $success = $this->model->delete($id);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar proveedor: ' . $e->getMessage()]);
        }
    }
}
?>