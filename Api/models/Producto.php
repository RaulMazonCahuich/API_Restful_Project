<?php
require_once __DIR__ . '/../config/database.php';

class Producto {
  private $conn;

  public function __construct() {
    $this->conn = Database::connect();
  }

  public function getAll() {
    return $this->conn->query("SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON p.categoria_id = c.id");
  }

  public function get($id) {
    return $this->conn->query("SELECT * FROM productos WHERE id = $id");
  }

  public function create($data) {
    $stmt = $this->conn->prepare("INSERT INTO productos(nombre, precio, categoria_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $data["nombre"], $data["precio"], $data["categoria_id"]);
    return $stmt->execute();
  }

  public function update($id, $data) {
    $stmt = $this->conn->prepare("UPDATE productos SET nombre=?, precio=?, categoria_id=? WHERE id=?");
    $stmt->bind_param("sdii", $data["nombre"], $data["precio"], $data["categoria_id"], $id);
    return $stmt->execute();
  }

  public function delete($id) {
    return $this->conn->query("DELETE FROM productos WHERE id = $id");
  }
}
?>
