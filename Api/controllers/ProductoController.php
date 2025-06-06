<?php
require_once __DIR__ . '/../models/Producto.php';

class ProductoController {
  private $model;

  public function __construct() {
    $this->model = new Producto();
  }

  public function index() {
    $data = $this->model->getAll();
    echo json_encode($data->fetch_all(MYSQLI_ASSOC));
  }

  public function show($id) {
    $data = $this->model->get($id);
    echo json_encode($data->fetch_assoc());
  }

  public function store($data) {
    echo json_encode(['success' => $this->model->create($data)]);
  }

  public function update($id, $data) {
    echo json_encode(['success' => $this->model->update($id, $data)]);
  }

  public function destroy($id) {
    echo json_encode(['success' => $this->model->delete($id)]);
  }
}
