<?php
require_once '../config/database.php';
require_once '../librerias/fpdf/fpdf.php';
require_once '../helpers/auth.php';

// Autenticación
if (!Auth::check()) {
    http_response_code(401);
    die(json_encode(['error' => 'No autorizado']));
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Times', 'B', 14);
        $this->Cell(0, 10, 'Reporte de Productos', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Times', 'I', 10);
        $this->Cell(0, 10, 'Pagina '.$this->PageNo(), 0, 0, 'C');
    }
}

try {
    $conn = Database::connect();
    $query = "
        SELECT p.id, p.nombre, p.precio, c.nombre AS categoria
        FROM productos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        ORDER BY p.id
    ";
    $resultado = $conn->query($query);
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }

    $pdf = new PDF();
    $pdf->AddPage();

    // Encabezados de la tabla
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(20, 10, 'ID', 1, 0, 'C');
    $pdf->Cell(60, 10, 'Nombre', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Precio', 1, 0, 'C');
    $pdf->Cell(60, 10, 'Categoria', 1, 0, 'C');
    $pdf->Ln();
    
    // Datos de la tabla
    $pdf->SetFont('Arial', '', 10);
    while ($row = $resultado->fetch_assoc()) {
        $pdf->Cell(20, 8, $row['id'], 1);
        $pdf->Cell(60, 8, utf8_decode($row['nombre']), 1);
        $pdf->Cell(30, 8, '$' . number_format($row['precio'], 2), 1, 0, 'R');
        $pdf->Cell(60, 8, utf8_decode($row['categoria']), 1);
        $pdf->Ln();
    }
    $conn->close();
    $pdf->Output('D', 'reporte_productos.pdf');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error generando reporte: ' . $e->getMessage()]);
}
?>