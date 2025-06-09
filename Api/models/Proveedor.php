<?php
require_once __DIR__ . '/../config/database.php';

class Proveedor {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function getAll() {
        return $this->conn->query("SELECT * FROM proveedores");
    }

    public function get($id) {
        $stmt = $this->conn->prepare("SELECT * FROM proveedores WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO proveedores(nombre, telefono, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $data["nombre"], $data["telefono"], $data["email"]);
        return $stmt->execute();
    }

    public function update($id, $data) {
        $stmt = $this->conn->prepare("UPDATE proveedores SET nombre = ?, telefono = ?, email = ? WHERE id = ?");
        $stmt->bind_param("sssi", $data["nombre"], $data["telefono"], $data["email"], $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM proveedores WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>