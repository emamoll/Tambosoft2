<?php

require_once __DIR__ . '../../../../backend/controladores/alimentoController.php';
require_once __DIR__ . '../../../../backend/controladores/campoController.php';
require_once __DIR__ . '../../../../backend/controladores/almacenController.php';
require_once __DIR__ . '../../../../backend/controladores/categoriaController.php';
require_once __DIR__ . '../../../../backend/controladores/stock_almacenController.php';
require_once __DIR__ . '../../../../backend/servicios/libs/fpdf.php';

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
$controllerCategoria = new CategoriaController();
$categorias = $controllerCategoria->obtenerCategorias();

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

$stock_almacenes = $controllerStock_almacen->procesarFiltro();
$total_economico_general = $controllerStock_almacen->getTotalEconomicValue();

$filtrosAplicados = [
  'almacen_id' => $_GET['almacen_id'] ?? [],
  'alimento_id' => $_GET['alimento_id'] ?? []
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Stock</title>
  <link rel="icon" href="../../../img/logo2.png" type="image/png">
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
  <?php require_once __DIR__ . '../../secciones/header.php'; ?>
  <?php require_once __DIR__ . '../../secciones/navbar.php'; ?>

  <div class="main">
    <div class="form-container" id="formCampoContainer">
      <div class="form-title">Stocks</div>
      <form method="POST">
        <div class="form-group select-group">
          <select name="almacen_nombre">
            <option value="" disabled <?= empty($_POST['almacen_nombre']) ? 'selected' : '' ?>>Seleccione un campo
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
          <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#filtroModal">
            Filtrar por
          </button>
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

    <h2 class="titulosSecciones">Stock por Campos</h2>
    <table class="tabla" id="tablaContainerPo">
      <thead>
        <tr>
          <th>Campo</th>
          <th>Alimento</th>
          <th>Cantidad</th>
          <th>Valor x Unidad</th>
          <th>Valor Total del Alimento</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $total_valor_economico = 0;
        $total_valor_alimento = 0; // Nueva variable para el total de la columna
        ?>
        <?php if (!empty($stock_almacenes)): ?>
          <?php foreach ($stock_almacenes as $sa): ?>
            <?php
            $almacen_nombre = $sa->getAlmacen_nombre() ?? 'Desconocido';
            $alimento_nombre = $sa->getAlimento_nombre() ?? 'Desconocido';
            $stock = $sa->getStock();
            $precio = $sa->getAlimento_precio() ?? 0;

            $valor = $precio * $stock;
            $total_valor_economico += $valor;

            $total_alimento = $sa->getTotalStock() * $precio;
            $total_valor_alimento += $total_alimento; // Acumula el valor de la nueva columna
            ?>
            <tr>
              <td><?= htmlspecialchars($almacen_nombre) ?></td>
              <td><?= htmlspecialchars($alimento_nombre) ?></td>
              <td><?= htmlspecialchars($stock) ?></td>
              <td>$<?= htmlspecialchars(number_format($valor, 2)) ?></td>
              <td>$<?= htmlspecialchars(number_format($total_alimento, 2)) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5">No hay stocks registrados.</td>
          </tr>
        <?php endif; ?>

        <tr class="total-row">
          <td colspan="4" style="text-align: right; font-weight: bold;">Valor Total de Alimentos:</td>
          <td style="font-weight: bold;">$<?= htmlspecialchars(number_format($total_valor_alimento, 2)) ?></td>
        </tr>
      </tbody>
    </table>

    <form action="../../../backend/servicios/libs/stocks.php" method="GET" target="_blank" class="botonPDF">
      <button type="submit" name="generar_pdf" class="btn btn-danger">Descargar PDF</button>
      <?php
      // Añadir inputs ocultos para cada filtro aplicado que viene en $_GET
      foreach ($filtrosAplicados as $filterName => $filterValues) {
        if (is_array($filterValues) && !empty($filterValues)) {
          foreach ($filterValues as $value) {
            echo '<input type="hidden" name="' . htmlspecialchars($filterName) . '[]" value="' . htmlspecialchars($value) . '">';
          }
        }
      }
      ?>
    </form>
  </div>

  <div class="modal fade" id="filtroModal" tabindex="-1" aria-labelledby="filtroModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filtroModalLabel">Filtrar Stock</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="filtroForm" method="GET">
          <div class="mb-3">
            <label class="form-label">Campos</label><br>
            <?php foreach ($almacenes as $al): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="almacen_id[]" value="<?= $al->getId() ?>"
                  id="almacen_<?= $al->getId() ?>" <?= (isset($_GET['almacen_id']) && in_array($al->getId(), $_GET['almacen_id'])) ? 'checked' : '' ?>>
                <label class="form-check-label" for="almacen_<?= $al->getId() ?>">
                  <?= htmlspecialchars($al->getNombre()) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Alimentos</label><br>
            <?php foreach ($alimentos as $al): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="alimento_id[]" value="<?= $al->getId() ?>"
                  id="alimento_<?= $al->getId() ?>" <?= (isset($_GET['alimento_id']) && in_array($al->getId(), $_GET['alimento_id'])) ? 'checked' : '' ?>>
                <label class="form-check-label" for="alimento_<?= $al->getId() ?>">
                  <?= htmlspecialchars($al->getNombre()) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="botones-container botones-filtros">
            <button type="submit" class="btn btn-primary" name="aplicar_filtros" value="true">Aplicar</button>
            <!-- <button type="button" class="btn btn-primary" id="limpiarFiltrosBtn">Limpiar Filtros</button> -->
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      //const limpiarFiltrosBtn = document.getElementById('limpiarFiltrosBtn');
      const filtroForm = document.getElementById('filtroForm');



      //   limpiarFiltrosBtn.addEventListener('click', function () {
      //     // Desmarcar todos los checkboxes
      //     const checkboxes = filtroForm.querySelectorAll('input[type="checkbox"]');
      //     checkboxes.forEach(chk => chk.checked = false);

      //     // Crear un input oculto para indicar que se están limpiando filtros
      //     const inputReset = document.createElement('input');
      //     inputReset.type = 'hidden';
      //     inputReset.name = 'limpiar_filtros';
      //     inputReset.value = 'true';
      //     filtroForm.appendChild(inputReset);

      //     // Enviar el formulario
      //     filtroForm.submit();
      //   });
      //   // Evitar reenvío al actualizar
      //   if (window.history.replaceState) {
      //     window.history.replaceState(null, null, window.location.href);
      //   }
      // });
    })

  </script>
</body>

</html>