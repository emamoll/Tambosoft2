<?php

require_once __DIR__ . '../../../../backend/controladores/estadoController.php';
require_once __DIR__ . '../../../../backend/controladores/almacenController.php';
require_once __DIR__ . '../../../../backend/controladores/alimentoController.php';
require_once __DIR__ . '../../../../backend/controladores/ordenController.php';
require_once __DIR__ . '../../../../backend/servicios/libs/fpdf.php';
require_once __DIR__ . '../../../../backend/controladores/categoriaController.php';

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

// SIMPLIFICADO: Siempre llamamos a procesarFiltro.
// procesarFiltro() se encargará de devolver todas las órdenes si no se aplicó un filtro,
// o las órdenes filtradas si el modal de filtro fue enviado.
$ordenes = $controllerOrden->procesarFiltro();

$filtrosAplicados = [
  'estado_id' => $_GET['estado_id'] ?? [],
  'almacen_id' => $_GET['almacen_id'] ?? [],
  'alimento_id' => $_GET['alimento_id'] ?? []
];

$estadisticas = [
  1 => 0, // Creada
  2 => 0, // Enviada
  3 => 0, // En Preparación
  4 => 0, // En Traslado
  5 => 0, // Entregada
  6 => 0, // Cancelada
];

foreach ($ordenes as $o) {
  $estado_id = $o->getEstado_id();
  if (isset($estadisticas[$estado_id])) {
    $estadisticas[$estado_id]++;
  }
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
              <input type="hidden" id="orden_modificar_alimento_id"
                value="<?= htmlspecialchars($ordenAModificar->getAlimento_id()) ?>">
              <input type="hidden" id="orden_modificar_cantidad"
                value="<?= htmlspecialchars($ordenAModificar->getCantidad()) ?>">
            <?php endif; ?>

            <div class="form-group select-group">
              <select name="almacen_id" id="almacen_nombre_select">
                <option value="" disabled selected>Seleccione un almacén</option>
                <?php foreach ($almacenes as $al): ?>
                  <option value="<?= htmlspecialchars($al->getId()) ?>" <?= (isset($_POST['almacen_id']) && $_POST['almacen_id'] == $al->getId()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($al->getNombre()) ?>
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
              style="display: none; margin-top: 5px; font-size: 0.85em;">
              Stock disponible: <span id="stock_disponible" style="font-weight: bold; color: #333;"></span>
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
                    window.location.href = window.location.pathname; // recargar sin reenviar POST
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
          <th>Almacen</th>
          <th>Alimento</th>
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
            <tr>
              <td>
                <?= htmlspecialchars($o->getId()) ?>
              </td>
              <td>
                <?= htmlspecialchars($o->almacen_nombre ?? '') ?>
              </td>
              <td>
                <?= htmlspecialchars($o->alimento_nombre ?? '') ?>
              </td>
              <td>
                <?= htmlspecialchars($o->getCantidad()) ?>
              </td>
              <td>
                <?php
                $fecha = $o->getFecha_actualizacion();
                $fechaFormateada = date('d-m-Y', strtotime($fecha));
                echo htmlspecialchars($fechaFormateada);
                ?>
              </td>
              <td>
                <?= htmlspecialchars($o->getHora_actualizacion()) ?>
              </td>
              <td>
                <span class="<?php
                $estado_id = $o->getEstado_id();
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
                } elseif ($estado_id == 6) {
                  echo 'estado-cancelada';
                } else {
                  echo 'estado-desconocido';
                }
                ?>">
                  <?= htmlspecialchars($o->estado_nombre ?? '') ?>
                </span>
              </td>
              <td>
                <?php if ($o->getEstado_id() == 1): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <input type="hidden" name="accion" value="modificar">
                    <button type="submit" class="btn btn-success btn-sm">Modificar</button>
                  </form>

                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="enviar" class="btn btn-success btn-sm">Enviar</button>
                  </form>

                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="cancelar" class="btn btn-success btn-sm">Cancelar</button>
                  </form>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8">No hay ordenes cargadas.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table></br>
    <form action="../../../backend/servicios/libs/ordenes.php" method="GET" target="_blank" class="botonPDF">
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

    <h2 class="titulosSecciones">Distribución de órdenes por estado</h2>
    <div style="max-width: 500px; margin: 0 auto 30px;">
      <canvas id="graficoEstados" width="400" height="400"></canvas>
    </div>

  </div>

  <div class="modal fade" id="filtroModal" tabindex="-1" aria-labelledby="filtroModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="filtroModalLabel">Filtrar Órdenes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="filtroForm" method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Estado</label><br>
              <?php foreach ($estados as $e): ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" name="estado_id[]" value="<?= $e->getId() ?>"
                    id="estado_<?= $e->getId() ?>" <?= (isset($_POST['estado_id']) && in_array($e->getId(), $_POST['estado_id'])) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="estado_<?= $e->getId() ?>">
                    <?= htmlspecialchars($e->getNombre()) ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mb-3">
              <label class="form-label">Almacenes</label><br>
              <?php foreach ($almacenes as $al): ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" name="almacen_id[]" value="<?= $al->getId() ?>"
                    id="almacen_<?= $al->getId() ?>" <?= (isset($_POST['almacen_id']) && in_array($al->getId(), $_POST['almacen_id'])) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="almacen_<?= $al->getId() ?>">
                    <?= htmlspecialchars($al->getNombre()) ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mb-3">
              <label class="form-label">Alimentos</label><br>
              <?php foreach ($alimentos as $a): ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" name="alimento_id[]" value="<?= $a->getId() ?>"
                    id="alimento_<?= $a->getId() ?>" <?= (isset($_POST['alimento_id']) && in_array($a->getId(), $_POST['alimento_id'])) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="alimento_<?= $a->getId() ?>">
                    <?= htmlspecialchars($a->getNombre()) ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
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
      const almacenSelect = document.getElementById('almacen_nombre_select');
      const alimentoSelect = document.getElementById('alimento_nombre_select');
      const stockDisplay = document.getElementById('stock_disponible');
      const stockContainer = document.getElementById('stock_disponible_container');
      const cantidadInput = document.getElementById('cantidad');
      const limpiarFiltrosBtn = document.getElementById('limpiarFiltrosBtn');
      const filtroForm = document.getElementById('filtroForm');
      const ordenModificarAlmacenIdInput = document.getElementById('orden_modificar_almacen_id');
      const ordenModificarAlimentoIdInput = document.getElementById('orden_modificar_alimento_id');
      const ordenModificarCantidadInput = document.getElementById('orden_modificar_cantidad');

      stockContainer.style.display = 'none';

      function fetchAndPopulateAlimentos(almacenId, selectedAlimentoId = null, callback = null) {
        alimentoSelect.innerHTML = '<option value="" disabled selected>Cargando alimentos...</option>';
        stockDisplay.textContent = '';
        stockContainer.style.display = 'none';

        if (almacenId) {
          fetch(`../../../backend/api/api.php?action=getAlimentosByAlmacen&almacenId=${almacenId}`)
            .then(response => {
              if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
              return response.json();
            })
            .then(data => {
              if (data.error) {
                throw new Error(data.error);
              }
              alimentoSelect.innerHTML = '<option value="" disabled selected>Seleccione un alimento</option>';
              data.forEach(alimento => {
                const option = document.createElement('option');
                option.value = alimento.id;
                option.textContent = alimento.nombre;
                alimentoSelect.appendChild(option);
              });

              if (selectedAlimentoId) {
                alimentoSelect.value = selectedAlimentoId;
                alimentoSelect.dispatchEvent(new Event('change'));
              }

              if (callback) callback();
            })
            .catch(error => {
              console.error('Error al obtener alimentos:', error);
              alimentoSelect.innerHTML = `<option value="" disabled selected>Error al cargar alimentos: ${error.message}</option>`;
              stockContainer.style.display = 'none';
            });
        } else {
          alimentoSelect.innerHTML = '<option value="" disabled selected>Seleccione un almacén primero</option>';
          stockDisplay.textContent = '';
          stockContainer.style.display = 'none';
        }
      }

      almacenSelect.addEventListener('change', function () {
        const almacenId = this.value;
        fetchAndPopulateAlimentos(almacenId);
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
            .then(response => {
              if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
              return response.json();
            })
            .then(data => {
              if (data.error) {
                throw new Error(data.error);
              }
              if (data.stock !== undefined) {
                stockDisplay.textContent = data.stock;
                stockContainer.style.display = 'block';
              } else {
                stockDisplay.textContent = '0';
                stockContainer.style.display = 'none';
              }
            })
            .catch(error => {
              console.error('Error al obtener stock:', error);
              stockDisplay.textContent = 'Error';
              stockContainer.style.display = 'block';
            });
        }
      });

      cantidadInput.addEventListener('input', function () {
        const requestedQuantity = parseInt(this.value);
        const availableStock = parseInt(stockDisplay.textContent);

        if (requestedQuantity > availableStock) {
          this.setCustomValidity('La cantidad solicitada excede el stock disponible.');
        } else {
          this.setCustomValidity('');
        }
      });

      // Prepopular campos si estamos en modo modificar
      if (ordenModificarAlmacenIdInput && ordenModificarAlimentoIdInput && ordenModificarCantidadInput) {
        const initialAlmacenId = ordenModificarAlmacenIdInput.value;
        const initialAlimentoId = ordenModificarAlimentoIdInput.value;
        const initialCantidad = ordenModificarCantidadInput.value;

        almacenSelect.value = initialAlmacenId;
        cantidadInput.value = initialCantidad;

        fetchAndPopulateAlimentos(initialAlmacenId, initialAlimentoId);
      }

      limpiarFiltrosBtn.addEventListener('click', function () {
        // Desmarcar todos los checkboxes
        const checkboxes = filtroForm.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(chk => chk.checked = false);

        // Crear un input oculto para indicar que se están limpiando filtros
        const inputReset = document.createElement('input');
        inputReset.type = 'hidden';
        inputReset.name = 'limpiar_filtros';
        inputReset.value = 'true';
        filtroForm.appendChild(inputReset);

        // Enviar el formulario
        filtroForm.submit();
      });
      // Evitar reenvío al actualizar
      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
      }
    });

    // Gráfico Chart.js
    const total = <?= array_sum($estadisticas) ?>;
    const dataEstados = [
      <?= $estadisticas[1] ?>,
      <?= $estadisticas[2] ?>,
      <?= $estadisticas[3] ?>,
      <?= $estadisticas[4] ?>,
      <?= $estadisticas[5] ?>,
      <?= $estadisticas[6] ?>
    ];

    const ctx = document.getElementById('graficoEstados').getContext('2d');
    new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Creada', 'Enviada', 'En Preparacion', 'En Traslado', 'Entregada', 'Cancelada'],
        datasets: [{
          label: 'Órdenes por estado',
          data: dataEstados,
          backgroundColor: ['#a81d6a', '#1d6ea8', '#e6df1c', '#e6661c', '#5cb85c', '#db3630']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          title: {
            // display: true,
            // text: 'Distribución de órdenes por estado'
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const label = context.label || '';
                const value = context.raw;
                const percentage = (value / total * 100).toFixed(1);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  </script>
</body>

</html>