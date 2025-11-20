<?php

require_once __DIR__ . "/fpdf186/fpdf.php";
require_once __DIR__ . "/../../../backend/controladores/stock_almacenController.php";
require_once __DIR__ . "/../../../backend/controladores/almacenController.php";
require_once __DIR__ . "/../../../backend/controladores/alimentoController.php";

// ***************************************************************
// INICIO: Clase PDF personalizada para incluir Logo y Pie de Página
// ***************************************************************

class PDF extends FPDF
{
  // Ruta al logo: desde backend/servicios/libs/ hasta frontend/img/logoChico.png
  private $logoPath = "../../../frontend/img/logoChico.png";

  // Cabecera de página
  function Header()
  {
    // Logo (x=10, y=8, ancho=30mm)
    if (file_exists($this->logoPath)) {
      // Ajustar la posición y tamaño (x, y, w)
      $this->Image($this->logoPath, 10, 8, 30);
    }

    // Mover el punto Y un poco más abajo para el inicio del contenido principal
    $this->SetY(20);
  }

  // Pie de página
  function Footer()
  {
    // Posición a 15 mm del final
    $this->SetY(-15);

    // 1. Número de página
    $this->SetFont('Arial', 'I', 8);
    $textoPagina = utf8_decode('Página ') . $this->PageNo() . '/{nb}';
    // Ancho 0 (hasta el margen derecho), altura 5, texto, borde 0, salto de línea 0, alineación C (centrado).
    $this->Cell(0, 5, $textoPagina, 0, 0, 'C');
    $this->Ln(4); // Pequeño salto de línea para el texto adicional

    // 2. Texto adicional del pie de página
    $this->SetFont('Arial', '', 7);
    $textoAdicional = utf8_decode('Reporte generado por TamboSoft - Sistema de Gestión de Alimentos');
    $this->Cell(0, 5, $textoAdicional, 0, 0, 'C');
  }
}

// ***************************************************************
// FIN: Clase PDF personalizada
// ***************************************************************

// Crea una instancia del controlador de stock_almacen.
$controllerStock_almacen = new Stock_almacenController();

// Obtiene los stocks filtrados.
$stocks = $controllerStock_almacen->procesarFiltro();

// Obtiene el total económico de todos los stocks.
$total_economico_general = $controllerStock_almacen->getTotalEconomicValue();

// Crea una nueva instancia de FPDF.
$pdf = new PDF(); // CAMBIO: Usar clase PDF personalizada
$pdf->AliasNbPages(); // CAMBIO: Habilita {nb} para el total de páginas
$pdf->AddPage();
// El Header() se llama automáticamente aquí y establece Y=20

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