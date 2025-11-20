<?php

require_once __DIR__ . '../../../../backend/controladores/estadoController.php';
require_once __DIR__ . '../../../../backend/controladores/almacenController.php';
require_once __DIR__ . '../../../../backend/controladores/alimentoController.php';
require_once __DIR__ . '../../../../backend/controladores/ordenController.php';
require_once __DIR__ . '../../../../backend/controladores/categoriaController.php';
require_once __DIR__ . '../../../../backend/servicios/libs/fpdf.php';

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
  header('Location: ../../../index.php');
  exit;
}

$accion = $_POST['accion'] ?? '';
$accionOrden = $_POST['accionOrden'] ?? '';

$controllerOrden = new OrdenController();

$controllerEstado = new EstadoController();
$estados = $controllerEstado->obtenerEstados();

$controllerAlmacen = new AlmacenController();
$almacenes = $controllerAlmacen->obtenerAlmacenes();

$controllerAlimento = new AlimentoController();
$alimentos = $controllerAlimento->obtenerAlimentos();

$controllerCategoria = new CategoriaController();
$categorias = $controllerCategoria->obtenerCategorias();

$ordenAModificar = null;
if ($accion === 'modificar' && isset($_POST['orden_id'])) {
  $ordenAModificar = $controllerOrden->obtenerOrdenPorId($_POST['orden_id']);
}

$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accionOrden'])) {
  $mensaje = $controllerOrden->procesarFormulario();

  if (in_array($_POST['accionOrden'], ['enviar', 'cancelar']) && isset($mensaje['tipo']) && $mensaje['tipo'] === 'success') {
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
  }
}

// Siempre llamamos a procesarFiltro.
$ordenes = $controllerOrden->procesarFiltro();

// Filtros aplicados (incluye fechas)
$filtrosAplicados = [
  'estado_id' => $_GET['estado_id'] ?? [],
  'almacen_id' => $_GET['almacen_id'] ?? [],
  'categoria_id' => $_GET['categoria_id'] ?? [],
  'alimento_id' => $_GET['alimento_id'] ?? [],
  'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
  'fecha_fin' => $_GET['fecha_fin'] ?? ''
];

// Estadísticas para el gráfico
$estadisticas = [
  1 => 0, // Creada
  2 => 0, // Enviada
  3 => 0, // En Preparación
  4 => 0, // En Traslado
  5 => 0, // Entregada
  6 => 0  // Cancelada
];

foreach ($ordenes as $o) {
  $estado_id = $o->getEstado_id();
  if (isset($estadisticas[$estado_id])) {
    $estadisticas[$estado_id]++;
  }
}

// Mapeo opcional id->nombre (por si querés usarlo más adelante)
$labelsEstados = [];
foreach ($estados as $e) {
  $labelsEstados[$e->getId()] = $e->getNombre();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Ordenes</title>
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
      <div class="form-title">Distribución de alimentos</div>
      <form method="POST">
        <div class="botones-container">
          <button type="submit" name="accion" value="crear" class="btn btn-primary">Crear ordenes</button>
          <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#filtroModal">
            Filtrar por
          </button>
        </div>
      </form>

      <?php if ($accion === 'crear' || $ordenAModificar): ?>
        <div class="mt-4">
          <div class="form-title">Crear orden</div>
          <form method="POST">
            <input type="hidden" name="accion" value="cambio_categoria">
            <input type="hidden" name="id" value="<?= $ordenAModificar ? $ordenAModificar->getId() : '' ?>">

            <?php if ($ordenAModificar): ?>
              <input type="hidden" id="orden_modificar_almacen_id"
                value="<?= htmlspecialchars($ordenAModificar->getAlmacen_id()) ?>">
              <input type="hidden" id="orden_modificar_categoria_id"
                value="<?= htmlspecialchars($ordenAModificar->getCategoria_id()) ?>">
              <input type="hidden" id="orden_modificar_alimento_id"
                value="<?= htmlspecialchars($ordenAModificar->getAlimento_id()) ?>">
              <input type="hidden" id="orden_modificar_cantidad"
                value="<?= htmlspecialchars($ordenAModificar->getCantidad()) ?>">
            <?php endif; ?>

            <div class="form-group select-group">
              <select name="almacen_id" id="almacen_nombre_select">
                <option value="" disabled selected>Seleccione un campo</option>
                <?php foreach ($almacenes as $al): ?>
                  <option value="<?= htmlspecialchars($al->getId()) ?>" <?= (isset($_POST['almacen_id']) && $_POST['almacen_id'] == $al->getId()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($al->getNombre()) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group select-group">
              <select name="categoria_id" id="categoria_nombre_select">
                <option value="" disabled selected>Seleccione una categoría</option>
                <?php foreach ($categorias as $ca): ?>
                  <option value="<?= htmlspecialchars($ca->getId()) ?>" <?= (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $ca->getId()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ca->getNombre()) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group select-group">
              <select name="alimento_id" id="alimento_nombre_select">
                <option value="" disabled selected>Seleccione un alimento</option>
              </select>
            </div>
            <div class="form-group" id="stock_disponible_container"
              style="display:none; margin-top:5px; font-size:0.85em;">
              Stock disponible:
              <span id="stock_disponible" style="font-weight:bold; color:#333;"></span>
            </div>
            <div class="form-group">
              <input type="number" id="cantidad" name="cantidad"
                value="<?= htmlspecialchars($_POST['cantidad'] ?? ($ordenAModificar ? $ordenAModificar->getCantidad() : '')) ?>"
                placeholder=" ">
              <label for="cantidad">Cantidad</label>
            </div>
            <input type="hidden" name="accion" value="crear">
            <button type="submit" name="accionOrden" value="<?= $ordenAModificar ? 'modificar' : 'crear' ?>">
              <?= $ordenAModificar ? 'Modificar orden' : 'Crear orden' ?>
            </button>
            <?php if (!empty($mensaje)): ?>
              <script>
                Swal.fire({
                  icon: '<?= $mensaje["tipo"] ?>',
                  title: '<?= $mensaje["tipo"] === "success" ? "Éxito" : "Atención" ?>',
                  text: <?= json_encode($mensaje["mensaje"]) ?>,
                  confirmButtonColor: '#3085d6'
                }).then(() => {
                  <?php if ($mensaje["tipo"] === "success"): ?>
                    window.location.href = window.location.pathname;
                  <?php endif; ?>
                });
              </script>
            <?php endif; ?>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <h2 class="titulosSecciones">Ordenes</h2>
    <table class="tabla" id="tablaContainer">
      <thead>
        <tr>
          <th>Orden N</th>
          <th>Campo</th>
          <th>Alimento</th>
          <th>Categoría</th>
          <th>Cantidad</th>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Estado</th>
          <th>Opciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($ordenes)): ?>
          <?php foreach ($ordenes as $o): ?>
            <?php
            // Clases de estado (las tenés definidas en estilos.css)
            switch ($o->getEstado_id()) {
              case 1:
                $estadoClass = 'estado-creada';
                break;
              case 2:
                $estadoClass = 'estado-enviada';
                break;
              case 3:
                $estadoClass = 'estado-enPreparacion';
                break;
              case 4:
                $estadoClass = 'estado-enTraslado';
                break;
              case 5:
                $estadoClass = 'estado-entregada';
                break;
              case 6:
                $estadoClass = 'estado-cancelada';
                break;
              default:
                $estadoClass = '';
                break;
            }
            ?>
            <tr>
              <td><?= htmlspecialchars($o->getId()) ?></td>
              <td><?= htmlspecialchars($o->almacen_nombre ?? '') ?></td>
              <td><?= htmlspecialchars($o->alimento_nombre ?? '') ?></td>
              <td><?= htmlspecialchars($o->categoria_nombre ?? '') ?></td>
              <td><?= htmlspecialchars($o->getCantidad()) ?></td>
              <td>
                <?php
                $fecha = $o->getFecha_actualizacion();
                $fechaFormateada = date('d-m-Y', strtotime($fecha));
                echo htmlspecialchars($fechaFormateada);
                ?>
              </td>
              <td><?= htmlspecialchars($o->getHora_actualizacion()) ?></td>
              <td>
                <span class="<?= $estadoClass ?>">
                  <?= htmlspecialchars($o->estado_nombre ?? '') ?>
                </span>
              </td>
              <td class="acciones-cell">
                <?php if ($o->getEstado_id() == 1): ?>
                  <!-- Modificar -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <input type="hidden" name="accion" value="modificar">
                    <button type="submit" class="btn btn-sm btn-info" style="color:white" title="Modificar">Modificar</button>
                  </form>

                  <!-- Enviar -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="enviar" class="btn btn-sm btn-success"
                      title="Enviar">Enviar</button>
                  </form>

                  <!-- Cancelar (con motivo) -->
                  <form method="POST" style="display:inline;" onsubmit="return showCancelModal(this);">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <input type="hidden" name="accionOrden" value="cancelar">
                    <input type="hidden" name="descripcion" id="cancel_description_<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" class="btn btn-sm btn-danger" title="Cancelar">Cancelar</button>
                  </form>

                <?php elseif ($o->getEstado_id() == 6): ?>
                  <!-- Ver motivo de cancelación -->
                  <button type="button" class="btn btn-sm btn-dark" title="Ver Motivo"
                    onclick="viewCancelReason(<?= htmlspecialchars($o->getId()) ?>)">Ver Motivo</button>

                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="10">No hay ordenes cargadas.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table><br>

    <form action="../../../backend/servicios/libs/ordenes.php" method="GET" target="_blank" class="botonPDF">
      <button type="submit" name="generar_pdf" class="btn btn-danger">Descargar PDF</button>
      <?php
      foreach ($filtrosAplicados as $filterName => $filterValues) {
        if (is_array($filterValues)) {
          if (!empty($filterValues)) {
            foreach ($filterValues as $value) {
              echo '<input type="hidden" name="' . htmlspecialchars($filterName) . '[]" value="' . htmlspecialchars($value) . '">';
            }
          }
        } elseif (!empty($filterValues)) {
          echo '<input type="hidden" name="' . htmlspecialchars($filterName) . '" value="' . htmlspecialchars($filterValues) . '">';
        }
      }
      ?>
    </form>

    <h2 class="titulosSecciones">Distribución de órdenes por estado</h2>
    <div style="max-width: 500px; margin: 0 auto 30px;">
      <canvas id="graficoEstados" width="400" height="400"></canvas>
    </div>
  </div>

  <!-- MODAL FILTROS -->
  <div class="modal fade" id="filtroModal" tabindex="-1" aria-labelledby="filtroModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filtroModalLabel">Filtrar Órdenes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="filtroForm" method="GET">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Rango de Fecha de Actualización</label>
              <div class="row">
                <div class="col-6">
                  <label for="fecha_inicio" class="form-label">Desde:</label>
                  <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                    value="<?= htmlspecialchars($filtrosAplicados['fecha_inicio']) ?>">
                </div>
                <div class="col-6">
                  <label for="fecha_fin" class="form-label">Hasta:</label>
                  <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                    value="<?= htmlspecialchars($filtrosAplicados['fecha_fin']) ?>">
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Estado</label><br>
              <?php foreach ($estados as $e): ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" name="estado_id[]" value="<?= $e->getId() ?>"
                    id="estado_<?= $e->getId() ?>" <?= (isset($_GET['estado_id']) && in_array($e->getId(), $_GET['estado_id'])) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="estado_<?= $e->getId() ?>">
                    <?= htmlspecialchars($e->getNombre()) ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
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
              <label class="form-label">Categorias</label><br>
              <?php foreach ($categorias as $po): ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" name="categoria_id[]" value="<?= $po->getId() ?>"
                    id="categoria_<?= $po->getId() ?>" <?= (isset($_GET['categoria_id']) && in_array($po->getId(), $_GET['categoria_id'])) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="categoria_<?= $po->getId() ?>">
                    <?= htmlspecialchars($po->getNombre()) ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mb-3">
              <label class="form-label">Alimentos</label><br>
              <?php foreach ($alimentos as $a): ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" name="alimento_id[]" value="<?= $a->getId() ?>"
                    id="alimento_<?= $a->getId() ?>" <?= (isset($_GET['alimento_id']) && in_array($a->getId(), $_GET['alimento_id'])) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="alimento_<?= $a->getId() ?>">
                    <?= htmlspecialchars($a->getNombre()) ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="botones-container botones-filtros">
            <button type="submit" class="btn btn-primary" name="aplicar_filtros" value="true">Aplicar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- MODAL CANCELAR (INGRESAR MOTIVO) -->
  <div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cancelReasonModalLabel">Motivo de Cancelación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <textarea class="form-control" id="cancelReasonTextarea" rows="3"
            placeholder="Ingresá el motivo de la cancelación"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="confirmCancelBtn">Confirmar Cancelación</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL VER MOTIVO -->
  <div class="modal fade" id="viewCancelReasonModal" tabindex="-1" aria-labelledby="viewCancelReasonModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewCancelReasonModalLabel">Detalle de Cancelación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p><strong>Fecha de Cancelación:</strong> <span id="viewCancelDate"></span></p>
          <p><strong>Hora de Cancelación:</strong> <span id="viewCancelHour"></span></p>
          <p><strong>Motivo:</strong> <span id="viewCancelDescription"></span></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {

      const almacenSelect = document.getElementById('almacen_nombre_select');
      const alimentoSelect = document.getElementById('alimento_nombre_select');
      const categoriaSelect = document.getElementById('categoria_nombre_select');
      const stockDisplay = document.getElementById('stock_disponible');
      const stockContainer = document.getElementById('stock_disponible_container');
      const cantidadInput = document.getElementById('cantidad');

      const ordenModificarAlmacenIdInput = document.getElementById('orden_modificar_almacen_id');
      const ordenModificarAlimentoIdInput = document.getElementById('orden_modificar_alimento_id');
      const ordenModificarCategoriaIdInput = document.getElementById('orden_modificar_categoria_id');
      const ordenModificarCantidadInput = document.getElementById('orden_modificar_cantidad');

      // ============================
      //   BLOQUE FORMULARIO ORDEN
      // ============================
      if (
        almacenSelect &&
        alimentoSelect &&
        categoriaSelect &&
        stockDisplay &&
        stockContainer &&
        cantidadInput
      ) {

        stockContainer.style.display = 'none';

        function fetchAndPopulateAlimentos(almacenId, selectedAlimentoId = null, callback = null) {

          alimentoSelect.innerHTML =
            '<option value="" disabled selected>Cargando alimentos...</option>';

          stockDisplay.textContent = '';
          stockContainer.style.display = 'none';

          if (almacenId) {
            fetch(`../../../backend/api/api.php?action=getAlimentosByAlmacen&almacenId=${almacenId}`)
              .then(resp => resp.json())
              .then(data => {
                alimentoSelect.innerHTML = '<option value="" disabled selected>Seleccione un alimento</option>';

                data.forEach(alimento => {
                  const op = document.createElement('option');
                  op.value = alimento.id;
                  op.textContent = alimento.nombre;
                  alimentoSelect.appendChild(op);
                });

                if (selectedAlimentoId) {
                  alimentoSelect.value = selectedAlimentoId;
                  alimentoSelect.dispatchEvent(new Event('change'));
                }

                if (callback) callback();
              })
              .catch(err => {
                alimentoSelect.innerHTML =
                  `<option value="" disabled selected>Error al cargar: ${err.message}</option>`;
              });

          } else {
            alimentoSelect.innerHTML =
              '<option value="" disabled selected>Seleccione un campo primero</option>';
          }
        }

        almacenSelect.addEventListener('change', function () {
          fetchAndPopulateAlimentos(this.value);
          stockDisplay.textContent = '';
          stockContainer.style.display = 'none';
        });

        alimentoSelect.addEventListener('change', function () {

          const almacenId = almacenSelect.value;
          const alimentoId = this.value;

          stockDisplay.textContent = '';
          stockContainer.style.display = 'none';

          if (almacenId && alimentoId) {
            fetch(`../../../backend/api/api.php?action=getStockForAlimento&almacenId=${almacenId}&alimentoId=${alimentoId}`)
              .then(resp => resp.json())
              .then(data => {

                if (data.stock !== undefined) {
                  stockDisplay.textContent = data.stock;
                  stockContainer.style.display = 'block';
                } else {
                  stockDisplay.textContent = '0';
                  stockContainer.style.display = 'none';
                }

              })
              .catch(e => {
                stockDisplay.textContent = 'Error';
                stockContainer.style.display = 'block';
              });
          }
        });

        cantidadInput.addEventListener('input', function () {
          const req = parseInt(this.value);
          const disp = parseInt(stockDisplay.textContent || '0');
          if (!isNaN(req) && !isNaN(disp) && req > disp) {
            this.setCustomValidity('La cantidad supera el stock disponible');
          } else {
            this.setCustomValidity('');
          }
        });

        if (
          ordenModificarAlmacenIdInput &&
          ordenModificarAlimentoIdInput &&
          ordenModificarCategoriaIdInput &&
          ordenModificarCantidadInput
        ) {

          const alm = ordenModificarAlmacenIdInput.value;
          const ali = ordenModificarAlimentoIdInput.value;
          const cat = ordenModificarCategoriaIdInput.value;
          const cant = ordenModificarCantidadInput.value;

          if (alm) almacenSelect.value = alm;
          if (cat) categoriaSelect.value = cat;
          if (cant) cantidadInput.value = cant;

          if (alm) {
            fetchAndPopulateAlimentos(alm, ali);
          }
        }
      }

      // ============================
      //   GRÁFICO CHART.JS
      // ============================
      const estadisticas = <?= json_encode($estadisticas) ?>;
      const total = <?= array_sum($estadisticas) ?>;

      const canvas = document.getElementById('graficoEstados');
      if (canvas) {
        const ctx = canvas.getContext('2d');

        new Chart(ctx, {
          type: 'pie',
          data: {
            labels: ['Creada', 'Enviada', 'En preparación', 'Traslado', 'Entregada', 'Cancelada'],
            datasets: [{
              data: [
                estadisticas[1],
                estadisticas[2],
                estadisticas[3],
                estadisticas[4],
                estadisticas[5],
                estadisticas[6]
              ],
              backgroundColor: [
                '#a81d6a', // Creada
                '#1d6ea8', // Enviada
                '#e6df1c', // En preparación
                '#e6661c', // Traslado
                '#5cb85c', // Entregada
                '#db3630'  // Cancelada
              ]
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { position: 'bottom' },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    const label = context.label || '';
                    const value = context.raw || 0;
                    const perc = total > 0 ? (value / total * 100).toFixed(1) : 0;
                    return `${label}: ${value} (${perc}%)`;
                  }
                }
              }
            }
          }
        });
      }

      // ============================
      //   CANCELAR CON MOTIVO
      // ============================
      let currentCancelForm = null;

      window.showCancelModal = function (form) {
        currentCancelForm = form;
        const modalEl = document.getElementById('cancelReasonModal');
        if (!modalEl) return false;
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        return false; // evita submit inmediato
      };

      const confirmCancelBtn = document.getElementById('confirmCancelBtn');
      const cancelReasonTextarea = document.getElementById('cancelReasonTextarea');

      if (confirmCancelBtn && cancelReasonTextarea) {
        confirmCancelBtn.addEventListener('click', function () {
          const motivo = cancelReasonTextarea.value.trim();

          if (!motivo) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Debés ingresar un motivo para cancelar.'
            });
            return;
          }

          if (currentCancelForm) {
            const ordenId = currentCancelForm.querySelector('input[name="orden_id"]').value;
            const hiddenInput = currentCancelForm.querySelector('#cancel_description_' + ordenId);
            if (hiddenInput) {
              hiddenInput.value = motivo;
            }

            const modalEl = document.getElementById('cancelReasonModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();

            currentCancelForm.submit();
          }
        });
      }

      const cancelReasonModal = document.getElementById('cancelReasonModal');
      if (cancelReasonModal && cancelReasonTextarea) {
        cancelReasonModal.addEventListener('hidden.bs.modal', function () {
          cancelReasonTextarea.value = '';
        });
      }

      // ============================
      //   VER MOTIVO DE CANCELACIÓN
      // ============================
      window.viewCancelReason = function (ordenId) {
        fetch(`../../../backend/api/api.php?action=getCancelacionDetail&ordenId=${ordenId}`)
          .then(resp => resp.json())
          .then(data => {
            if (data.error) {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error
              });
              return;
            }

            const dateEl = document.getElementById('viewCancelDate');
            const hourEl = document.getElementById('viewCancelHour');
            const descEl = document.getElementById('viewCancelDescription');

            if (dateEl) dateEl.textContent = data.fecha || '';
            if (hourEl) hourEl.textContent = data.hora || '';
            if (descEl) descEl.textContent = data.descripcion || '';

            const modalEl = document.getElementById('viewCancelReasonModal');
            if (!modalEl) return;
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
          })
          .catch(err => {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Hubo un problema al cargar el motivo de la cancelación.'
            });
          });
      };

    });
  </script>
</body>

</html>