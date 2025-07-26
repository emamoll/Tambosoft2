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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accionOrden'])) {
  $mensaje = $controllerOrden->procesarFormulario();

  if (isset($mensaje['tipo']) && $mensaje['tipo'] === 'success') {
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
  }
}

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
  <title>Tambosoft: Ordenes</title>
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
  <?php require_once __DIR__ . '../../secciones/headerTractorista.php'; ?>
  <!--	--------------->
  <?php require_once __DIR__ . '../../secciones/navbarTractorista.php'; ?>

  <div class="main">
    <div class="form-container" id="formCampoContainer">
      <div class="form-title">Distribución de alimentos</div>
      <div class="mt-4">
        <div class="form-title">Filtrar por</div>
        <form method="POST">
          <div class="mb-3">
            <label>Estado</label><br>
            <?php foreach ($estados as $e): ?>
            <?php if ($e->getId() == 1)
              continue; ?>
            <label class="form-check-label">
              <input class="form-check-input" type="checkbox" name="estado_id[]" value="<?= $e->getId() ?>"
                <?= (isset($_POST['estado_id']) && in_array($c->getId(), $_POST['estado_id'])) ? 'checked' : '' ?>>
              <?= htmlspecialchars($e->getNombre()) ?>
            </label><br>
            <?php endforeach; ?>
          </div>
          <!-- Filtro Categoría -->
          <div class="mb-3">
            <label>Categorías</label><br>
            <?php foreach ($categorias as $c): ?>
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="categoria_id[]" value="<?= $c->getId() ?>"
                  <?= (isset($_POST['categoria_id']) && in_array($c->getId(), $_POST['categoria_id'])) ? 'checked' : '' ?>>
                <?= htmlspecialchars($c->getNombre()) ?>
              </label><br>
            <?php endforeach; ?>
          </div>

          <!-- Filtro Alimento -->
          <div class="mb-3">
            <label>Alimentos</label><br>
            <?php foreach ($alimentos as $a): ?>
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="alimento_id[]" value="<?= $a->getId() ?>"
                  <?= (isset($_POST['alimento_id']) && in_array($a->getId(), $_POST['alimento_id'])) ? 'checked' : '' ?>>
                <?= htmlspecialchars($a->getNombre()) ?>
              </label><br>
            <?php endforeach; ?>
          </div>
          <button type="submit" name="accion" value="filtrar" class="btn btn-secondary">Filtrar</button>
        </form>
      </div>
    </div>
    <h2 class="titulosSecciones">Ordenes</h2>
    <table class="tabla" id="tablaContainer">
      <thead>
        <tr>
          <th>Orden N</th>
          <th>Categoria</th>
          <th>Alimento</th>
          <th>Cantidad</th>
          <th>Fecha de Orden</th>
          <th>Hora de Orden</th>
          <th>Estado</th>
          <th>Opciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($ordenes)): ?>
          <?php foreach ($ordenes as $o): ?>
            <?php if ($o->getEstadoId() == 1)
              continue; ?>
            <tr>
              <td>
                <?= htmlspecialchars($o->getId()) ?>
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
                <?php
                $fecha = $o->getFecha_creacion();
                $fechaFormateada = date('d-m-Y', strtotime($fecha));
                echo htmlspecialchars($fechaFormateada);
                ?>
              </td>
              <td>
                <?= htmlspecialchars($o->getHora_creacion()) ?>
              </td>
              <td class="<?php
              $estado_id = $o->getEstadoid();
              if ($estado_id == 1) {
                echo 'estado-creada';
              } elseif ($estado_id == 2) {
                echo 'estado-enviada';
              } elseif ($estado_id == 3) {
                echo 'estado-enPreparacion';
              } elseif ($estado_id == 4) {
                echo 'estado-enTraslado';
              } elseif ($estado_id == 5) {
                echo 'estado-entregada';
              } else {
                echo 'estado-cancelada';
              }
              ?>">
                <?= htmlspecialchars($o->getEstadoNombre()) ?>
              </td>
              <td>
                <?php if ($o->getEstadoId() == 2): ?>
                  <!-- Botón Preparar -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="preparar" class="btn btn-success btn-sm">Preparar</button>
                  </form>

                  <!-- Botón Cancelar -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="cancelar" class="btn btn-success btn-sm">Cancelar</button>
                  </form>

                <?php elseif ($o->getEstadoId() == 3): ?>
                  <!-- Botón Trasladar -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="trasladar"
                      class="btn btn-success btn-sm">Trasladar</button>
                  </form>

                <?php elseif ($o->getEstadoId() == 4): ?>
                  <!-- Botón Entregar -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="entregar" class="btn btn-success btn-sm">Entregar</button>
                  </form>

                <?php else: ?>
                  <!-- Sin acciones -->
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3">No hay ordenes cargadas.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table></br>

</body>

</html>