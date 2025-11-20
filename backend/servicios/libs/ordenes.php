<?php
require_once __DIR__ . "/fpdf186/fpdf.php";
require_once __DIR__ . "/../../../backend/controladores/ordenController.php";
require_once __DIR__ . "/../../../backend/controladores/almacenController.php";

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


// Aquí, en lugar de obtener solo todas las órdenes, llamaremos a procesarFiltro()
// que se encargará de obtener los datos filtrados (si vienen por GET) o todos los datos.
$controllerOrden = new OrdenController();

// procesarFiltro() en OrdenController ahora ya espera los filtros en $_GET.
$ordenes = $controllerOrden->procesarFiltro();


$pdf = new PDF(); // CAMBIO: Usar clase PDF personalizada
$pdf->AliasNbPages(); // CAMBIO: Habilita {nb} para el total de páginas
$pdf->AddPage();
// El Header() se llama automáticamente aquí y establece Y=20

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('Órdenes de Alimentos'), 0, 1, 'C');
$pdf->Ln(5);

// Encabezado
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(8, 10, 'N', 1);
$pdf->Cell(30, 10, utf8_decode('Campo'), 1);
$pdf->Cell(25, 10, 'Alimento', 1);
$pdf->Cell(18, 10, 'Cantidad', 1);
$pdf->Cell(20, 10, 'Categoria', 1);
$pdf->Cell(30, 10, 'Fecha', 1);
$pdf->Cell(20, 10, 'Hora', 1);
$pdf->Cell(44, 10, 'Estado', 1);
$pdf->Ln();

// Datos
$pdf->SetFont('Arial', '', 10);
foreach ($ordenes as $o) {
  $pdf->Cell(8, 10, $o->getId(), 1);
  $pdf->Cell(30, 10, utf8_decode($o->almacen_nombre ?? 'N/A'), 1);
  $pdf->Cell(25, 10, utf8_decode($o->alimento_nombre ?? 'N/A'), 1);
  $pdf->Cell(18, 10, $o->getCantidad(), 1);
  $pdf->Cell(20, 10, utf8_decode($o->categoria_nombre ?? 'N/A'), 1);
  $fecha = date('d-m-Y', strtotime($o->getFecha_actualizacion()));
  $pdf->Cell(30, 10, $fecha, 1);
  $pdf->Cell(20, 10, $o->getHora_actualizacion(), 1);
  $pdf->Cell(44, 10, utf8_decode($o->estado_nombre ?? 'N/A'), 1);
  $pdf->Ln();
}

$pdf->Output('I', 'ordenes.pdf');
?>