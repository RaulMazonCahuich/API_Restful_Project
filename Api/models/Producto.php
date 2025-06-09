<?php
require_once __DIR__ . '/../config/database.php';

class Producto {
  private $conn;

  public function __construct() {
    $this->conn = Database::connect();
  }

  public function getAll() {
    return $this->conn->query("
      SELECT p.*, c.nombre AS categoria, pr.nombre AS proveedor 
      FROM productos p 
      JOIN categorias c ON p.categoria_id = c.id 
      LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
    ");
  }

  public function get($id) {
    $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result();
  }

  public function create($data) {
    if ($data["proveedor_id"] === null) {
        $stmt = $this->conn->prepare("INSERT INTO productos(nombre, precio, categoria_id, proveedor_id) VALUES (?, ?, ?, NULL)");
        $stmt->bind_param("sdi", $data["nombre"], $data["precio"], $data["categoria_id"]);
    } else {
        $stmt = $this->conn->prepare("INSERT INTO productos(nombre, precio, categoria_id, proveedor_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdii", $data["nombre"], $data["precio"], $data["categoria_id"], $data["proveedor_id"]);
    }
    return $stmt->execute();
  }

  public function update($id, $data) {
    if ($data["proveedor_id"] === null) {
        $stmt = $this->conn->prepare("UPDATE productos SET nombre=?, precio=?, categoria_id=?, proveedor_id=NULL WHERE id=?");
        $stmt->bind_param("sdii", $data["nombre"], $data["precio"], $data["categoria_id"], $id);
    } else {
        $stmt = $this->conn->prepare("UPDATE productos SET nombre=?, precio=?, categoria_id=?, proveedor_id=? WHERE id=?");
        $stmt->bind_param("sdiii", $data["nombre"], $data["precio"], $data["categoria_id"], $data["proveedor_id"], $id);
    }
    return $stmt->execute();
  }

  public function delete($id) {
    $stmt = $this->conn->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
  }
}
?>