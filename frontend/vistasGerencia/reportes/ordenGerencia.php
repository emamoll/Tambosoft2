<?php

require_once __DIR__ . '../../../../backend/controladores/estadoController.php';
require_once __DIR__ . '../../../../backend/controladores/categoriaController.php';
require_once __DIR__ . '../../../../backend/controladores/alimentoController.php';
require_once __DIR__ . '../../../../backend/controladores/ordenController.php';
require_once __DIR__ . '../../../../backend/servicios/libs/fpdf.php';

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
  header('Location: ../../../index.php');
  exit;
}

$accion = $_POST['accion'] ?? '';
$accionOrden = $_POST['accionOrden'] ?? '';

$controllerOrden = new OrdenController();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'filtrar') {
  $ordenes = $controllerOrden->procesarFiltro();
} else {
  $ordenes = $controllerOrden->obtenerOrdenes();
}
$estados_ids = [];
$categoria_ids = [];
$alimento_ids = [];
foreach ($ordenes as $o) {
  $estados_ids[] = $o->getEstadoId();
  $categoria_ids[] = $o->getCategoriaId();
  $alimento_ids[] = $o->getAlimentoId();
}
$estados_ids = array_unique($estados_ids);
$categoria_ids = array_unique($categoria_ids);
$alimento_ids = array_unique($alimento_ids);

$controllerEstado = new EstadoController();
$estados = $controllerEstado->obtenerEstados();

$controllerCategoria = new CategoriaController();
$categorias = $controllerCategoria->obtenerCategorias();

$controllerAlimento = new AlimentoController();
$alimentos = $controllerAlimento->obtenerAlimentos();
$ordenAModificar = null;
if ($accion === 'modificar' && isset($_POST['orden_id'])) {
  $ordenAModificar = $controllerOrden->obtenerOrdenPorId($_POST['orden_id']);
}
$mensaje = null;

$campoAsociado = null;
$almacenAsociado = null;

$estadisticas = [
  1 => 0,
  2 => 0,
  3 => 0,
  4 => 0,
  5 => 0,
  6 => 0,
];

foreach ($ordenes as $o) {
  $estado_id = $o->getEstadoId();
  if (isset($estadisticas[$estado_id])) {
    $estadisticas[$estado_id]++;
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Reporte de orden</title>
  <link rel="icon" href=".../../../../img/logo2.png" type="image/png">
  <link rel="stylesheet" href="../../css/estilos.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
</head>

<body class="bodyHome">
  <?php require_once __DIR__ . '../../secciones/headerGerencia.php'; ?>
  <!--	--------------->
  <?php require_once __DIR__ . '../../secciones/navbarGerencia.php'; ?>
  <div class="main">
    <div class="form-container" id="formCampoContainer">
      <div class="form-title">Distribución de alimentos</div>
    </div>
    <table class="tabla" id="tablaContainer">
      <thead>
        <tr>
          <th>Orden N</th>
          <th>Fecha</th>
          <th>Categoria</th>
          <th>Alimento</th>
          <th>Cantidad</th>
          <th>Precio unitario</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($ordenes)): ?>
        <?php
        $sumaTotal = 0;
        foreach ($ordenes as $o):
          if ($o->getEstadoId() != 5)
            continue;
          $subtotal = $o->getAlimentoPrecio() * $o->getCantidad();
          $sumaTotal += $subtotal;
          ?>
        <tr>
          <td>
            <?= htmlspecialchars($o->getId()) ?>
          </td>
          <td>
            <?php
            $fecha = $o->getFecha_creacion();
            $fechaFormateada = date('d-m-Y', strtotime($fecha));
            echo htmlspecialchars($fechaFormateada);
            ?>
          </td>
          <td>
            <?= htmlspecialchars($o->getCategoriaNombre()) ?>
          </td>
          <td>
            <?= htmlspecialchars($o->getAlimentoNombre()) ?>
          </td>
          <td>
            <?= htmlspecialchars($o->getCantidad()) ?>
          </td>
          <td>
            $
            <?= number_format($o->getAlimentoPrecio(), 2, ',', '.') ?>
          </td>
          <td>$
            <?= number_format($subtotal, 2, ',', '.') ?>
          </td>
          <td>
          </td>
        </tr>
        <?php endforeach; ?>
        <tr style="font-weight: bold;">
          <td colspan="6" style="text-align: right;">Total general:</td>
          <td>$
            <?= number_format($sumaTotal, 2, ',', '.') ?>
          </td>
          <td></td>
        </tr>
        <?php else: ?>
        <tr>
          <td colspan="7">No hay ordenes cargadas.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table></br>
    <form action="../../../backend/servicios/libs/ReporteOrdenes.php" method="post" target="_blank" class="botonPDF">
      <button type="submit" name="generar_pdf" class="btn btn-danger">Descargar</button>
    </form>
  </div>
</body>

</html>