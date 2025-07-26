<?php
require_once __DIR__ . "../fpdf186/fpdf.php";
require_once __DIR__ . "../../../controladores/ordenController.php";

$controllerOrden = new OrdenController();
$ordenes = $controllerOrden->obtenerOrdenes();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Listado de Ordenes de Alimentos', 0, 1, 'C');
$pdf->Ln(5);

// Encabezado
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 10, 'Orden N', 1);
$pdf->Cell(30, 10, 'Categoria', 1);
$pdf->Cell(30, 10, 'Alimento', 1);
$pdf->Cell(20, 10, 'Cantidad', 1);
$pdf->Cell(30, 10, 'Fecha', 1);
$pdf->Cell(20, 10, 'Hora', 1);
$pdf->Cell(30, 10, 'Estado', 1);
$pdf->Ln();

// Datos
$pdf->SetFont('Arial', '', 10);
foreach ($ordenes as $o) {
  $pdf->Cell(15, 10, $o->getId(), 1);
  $pdf->Cell(30, 10, utf8_decode($o->getCategoriaNombre()), 1);
  $pdf->Cell(30, 10, utf8_decode($o->getAlimentoNombre()), 1);
  $pdf->Cell(20, 10, $o->getCantidad(), 1);
  $fecha = date('d-m-Y', strtotime($o->getFecha_creacion()));
  $pdf->Cell(30, 10, $fecha, 1);
  $pdf->Cell(20, 10, $o->getHora_creacion(), 1);
  $pdf->Cell(30, 10, utf8_decode($o->getEstadoNombre()), 1);
  $pdf->Ln();
}

$pdf->Output('I', 'ordenes.pdf');