<?php

ob_start();
require_once __DIR__ . "../fpdf186/fpdf.php";
require_once __DIR__ . "../../../controladores/ordenController.php";

$controllerOrden = new OrdenController();
$ordenes = $controllerOrden->obtenerOrdenes();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Reporte de Ordenes de Alimentos', 0, 1, 'C');
$pdf->Ln(5);

// Encabezado
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 10, 'Orden N', 1);
$pdf->Cell(30, 10, 'Fecha', 1);
$pdf->Cell(30, 10, 'Categoria', 1);
$pdf->Cell(30, 10, 'Alimento', 1);
$pdf->Cell(20, 10, 'Cantidad', 1);
$pdf->Cell(30, 10, 'Precio Unitario', 1);
$pdf->Cell(30, 10, 'Total', 1);
$pdf->Ln();

// Datos
$pdf->SetFont('Arial', '', 10);
$sumaTotal = 0;
foreach ($ordenes as $o) {
    if ($o->getEstadoId() != 5) {
        continue;
    }
    $pdf->Cell(15, 10, $o->getId(), 1);
    $fechaFormateada = date('d/m/Y', strtotime($o->getFecha_creacion()));
    $pdf->Cell(30, 10, $fechaFormateada, 1);
    $pdf->Cell(30, 10, utf8_decode($o->getCategoriaNombre()), 1);
    $pdf->Cell(30, 10, utf8_decode($o->getAlimentoNombre()), 1);
    $pdf->Cell(20, 10, $o->getCantidad(), 1);
    $precioUnitario = number_format($o->getAlimentoPrecio(), 2, ',', '.');
    $precioUnitario = floatval(str_replace(',', '.', str_replace('$', '', $o->getAlimentoPrecio())));
    $cantidad = intval($o->getCantidad());
    $total = $precioUnitario * $cantidad;
    $sumaTotal += $total;
    $pdf->Cell(30, 10, '$' . $precioUnitario, 1, 0, 'R');
    $total = number_format($o->getAlimentoPrecio() * $o->getCantidad(), 2, ',', '.');
    $pdf->Cell(30, 10, '$' . $total, 1, 0, 'R');

    $pdf->Ln();
}

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(155, 10, 'Total General:', 1, 0, 'R');
$pdf->Cell(30, 10, '$' . number_format($sumaTotal, 2, ',', '.'), 1, 0, 'R');
$pdf->Ln();

ob_end_clean();
$pdf->Output('I', 'Reportes de ordenes');
exit;