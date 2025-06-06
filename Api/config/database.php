<?php
class Database {
  public static function connect() {
    $conn = new mysqli("localhost", "root", "", "tienda");
    if ($conn->connect_error) {
      die("Conexión fallida: " . $conn->connect_error);
    }
    return $conn;
  }
}
