<?php

require_once __DIR__ . '../../../../backend/controladores/alimentoController.php';
require_once __DIR__ . '../../../../backend/controladores/campoController.php';
require_once __DIR__ . '../../../../backend/controladores/almacenController.php';
require_once __DIR__ . '../../../../backend/controladores/stock_almacenController.php';

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
  header('Location: ../../../index.php');
  exit;
}

$controllerStock_almacen = new Stock_almacenController();
$mensaje = $controllerStock_almacen->procesarFormularios();
$stock_almacenes = $controllerStock_almacen->getAllStock_almacenes();
$controllerAlimento = new AlimentoController();
$alimentos = $controllerAlimento->obtenerAlimentos();
$controllerAlmacen = new AlmacenController();
$almacenes = $controllerAlmacen->obtenerAlmacenes();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'consultar') {
  if (isset($mensaje['stock_almacenes']) && is_array($mensaje['stock_almacenes'])) {
    $stock_almacenes = $mensaje['stock_almacenes'];
  } else {
    $stock_almacenes = [];
  }
}

// Mapa por ID para acceder más rápido a nombres y precios
$mapaAlmacenes = [];
foreach ($almacenes as $a) {
  $mapaAlmacenes[$a->getId()] = $a->getNombre();
}

$mapaAlimentos = [];
foreach ($alimentos as $a) {
  $mapaAlimentos[$a->getId()] = [
    'nombre' => $a->getNombre(),
    'precio' => $a->getPrecio()
  ];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Tambosoft: Stocks</title>
  <link rel="icon" href=".../../../../img/logo2.png" type="image/png">
  <link rel="stylesheet" href="../../css/estilos.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bodyHome">
  <?php require_once __DIR__ . '../../secciones/header.php'; ?>
  <?php require_once __DIR__ . '../../secciones/navbar.php'; ?>

  <div class="main">
    <div class="form-container" id="formCampoContainer">
      <div class="form-title">Stocks</div>
      <form method="POST">
        <div class="form-group select-group">
          <select name="almacen_nombre">
            <option value="" disabled <?= empty($_POST['almacen_nombre']) ? 'selected' : '' ?>>Seleccione un almacen
            </option>
            <?php foreach ($almacenes as $a): ?>
              <option value="<?= htmlspecialchars($a->getNombre()) ?>" <?= (isset($_POST['almacen_nombre']) && $_POST['almacen_nombre'] === $a->getNombre()) ? 'selected' : '' ?>>
                <?= htmlspecialchars($a->getNombre()) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group select-group">
          <select name="alimento_nombre">
            <option value="" disabled <?= empty($_POST['alimento_nombre']) ? 'selected' : '' ?>>Seleccione un alimento
            </option>
            <?php foreach ($alimentos as $a): ?>
              <option value="<?= htmlspecialchars($a->getNombre()) ?>" <?= (isset($_POST['alimento_nombre']) && $_POST['alimento_nombre'] === $a->getNombre()) ? 'selected' : '' ?>>
                <?= htmlspecialchars($a->getNombre()) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <input type="number" id="cantidad" name="cantidad" value="<?= htmlspecialchars($_POST['cantidad'] ?? '') ?>"
            placeholder=" ">
          <label for="cantidad">Cantidad</label>
        </div>
        <div class="botones-container">
          <button type="submit" name="accion" value="actualizar">Registrar stock</button>
          <button type="submit" name="accion" value="consultar">Consultar stock</button>
        </div>
        <?php if (!empty($mensaje)): ?>
          <script>
            Swal.fire({
              icon: '<?= $mensaje["tipo"] ?>',
              title: '<?= $mensaje["tipo"] === "success" ? "Éxito" : "Atención" ?>',
              text: <?= json_encode($mensaje["mensaje"]) ?>,
              confirmButtonColor: '#3085d6'
            }).then(() => {
              <?php if ($mensaje["tipo"] === "success" && ($_POST['accion'] ?? '') === 'actualizar'): ?>
                window.location.href = window.location.pathname;
              <?php endif; ?>
            });
          </script>
        <?php endif; ?>
      </form>
    </div>

    <h2 class="titulosSecciones">Almacenes</h2>
    <table class="tabla" id="tablaContainerPo">
      <thead>
        <tr>
          <th>Almacén</th>
          <th>Alimento</th>
          <th>Stock</th>
          <th>Valor Económico</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $total_valor_economico = 0;
        $total_stock = 0;
        ?>
        <?php if (!empty($stock_almacenes)): ?>
          <?php foreach ($stock_almacenes as $sa): ?>
            <?php
            $idAlmacen = $sa->getAlmacen_id();
            $idAlimento = $sa->getAlimento_id();
            $stock = $sa->getStock();
            $precio = $mapaAlimentos[$idAlimento]['precio'] ?? 0;
            $valor = $precio * $stock;
            $total_valor_economico += $valor;
            $total_stock += $stock;
            ?>
            <tr>
              <td><?= htmlspecialchars($mapaAlmacenes[$idAlmacen] ?? 'Desconocido') ?></td>
              <td><?= htmlspecialchars($mapaAlimentos[$idAlimento]['nombre'] ?? 'Desconocido') ?></td>
              <td><?= htmlspecialchars($stock) ?></td>
              <td>$<?= htmlspecialchars(number_format($valor, 2)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4">No hay stocks registrados.</td>
          </tr>
        <?php endif; ?>

        <?php if (!empty($stock_almacenes) && isset($_POST['almacen_nombre']) && !empty($_POST['almacen_nombre'])): ?>
          <tr class="total-row">
            <td colspan="3" style="text-align: right; font-weight: bold;">Valor Económico Total del Almacén:</td>
            <td style="font-weight: bold;">$<?= htmlspecialchars(number_format($total_valor_economico, 2)) ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>

</html>