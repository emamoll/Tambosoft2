<?php

require_once __DIR__ . "/fpdf186/fpdf.php";
require_once __DIR__ . "/../../../backend/controladores/stock_almacenController.php";
require_once __DIR__ . "/../../../backend/controladores/almacenController.php";
require_once __DIR__ . "/../../../backend/controladores/alimentoController.php";

// Crea una instancia del controlador de stock_almacen.
$controllerStock_almacen = new Stock_almacenController();

// Obtiene los stocks filtrados.
$stocks = $controllerStock_almacen->procesarFiltro();

// Obtiene el total económico de todos los stocks.
$total_economico_general = $controllerStock_almacen->getTotalEconomicValue();

// Crea una nueva instancia de FPDF.
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('Stocks de Alimentos'), 0, 1, 'C');
$pdf->Ln(5);

// Encabezado de la tabla del PDF
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(45, 10, utf8_decode('Campo'), 1, 0, 'C');
$pdf->Cell(45, 10, 'Alimento', 1, 0, 'C');
$pdf->Cell(30, 10, 'Cantidad', 1, 0, 'C');
$pdf->Cell(35, 10, utf8_decode('Valor x Unidad'), 1, 0, 'C');
$pdf->Cell(35, 10, utf8_decode('Valor Total del Alimento'), 1, 1, 'C');
$pdf->SetFont('Arial', '', 8);

$total_valor_economico = 0;
$total_valor_alimento = 0;

foreach ($stocks as $sa) {
  $almacen_nombre = $sa->getAlmacen_nombre() ?? 'N/A';
  $alimento_nombre = $sa->getAlimento_nombre() ?? 'N/A';
  $stock_cantidad = $sa->getStock();
  $alimento_precio = $sa->getAlimento_precio() ?? 0;

  // El valor individual del stock
  $valor = $stock_cantidad * $alimento_precio;
  $total_valor_economico += $valor;

  // El valor total del alimento en todos los almacenes
  $total_alimento = $sa->getTotalStock() * $alimento_precio;
  $total_valor_alimento += $total_alimento;

  $pdf->Cell(45, 10, utf8_decode($almacen_nombre), 1);
  $pdf->Cell(45, 10, utf8_decode($alimento_nombre), 1);
  $pdf->Cell(30, 10, $stock_cantidad, 1, 0, 'C');
  $pdf->Cell(35, 10, '$' . number_format($valor, 2), 1, 0, 'R');
  $pdf->Cell(35, 10, '$' . number_format($total_alimento, 2), 1, 1, 'R');
}



// Fila para el total del valor de todos los alimentos
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(155, 10, utf8_decode('Valor Total de Alimentos:'), 1, 0, 'R');
$pdf->Cell(35, 10, '$' . number_format($total_valor_alimento, 2), 1, 1, 'R');

$pdf->Output('I', 'stocks.pdf');

?>