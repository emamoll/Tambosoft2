<?php

require_once __DIR__ . '../../../../backend/controladores/estadoController.php';
require_once __DIR__ . '../../../../backend/controladores/categoriaController.php';
require_once __DIR__ . '../../../../backend/controladores/alimentoController.php';
require_once __DIR__ . '../../../../backend/controladores/ordenController.php';
// NO NECESITAMOS fpdf.php AQUÍ, ya que no habrá botón de descarga de PDF
// require_once __DIR__ . '../../../../backend/servicios/libs/fpdf.php';
require_once __DIR__ . '../../../../backend/controladores/almacenController.php'; // Necesario para el filtro de almacenes

session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
  header('Location: ../../../index.php');
  exit;
}

// Inicializar mensaje Swala de la sesión
$mensaje = $_SESSION['form_message'] ?? null;
unset($_SESSION['form_message']); // Eliminar el mensaje de la sesión una vez leído

$accion = $_POST['accion'] ?? '';
$accionOrden = $_POST['accionOrden'] ?? '';

$controllerOrden = new OrdenController();

$controllerEstado = new EstadoController();
$estados = $controllerEstado->obtenerEstados();

$controllerAlmacen = new AlmacenController(); // Necesario para el filtro de almacenes
$almacenes = $controllerAlmacen->obtenerAlmacenes();

$controllerAlimento = new AlimentoController();
$alimentos = $controllerAlimento->obtenerAlimentos();

$controllerCategoria = new CategoriaController();
$categorias = $controllerCategoria->obtenerCategorias(); // Aunque no se filtre por categoría, se mantiene para la estructura.

// Procesar acciones de orden (preparar, trasladar, entregar, cancelar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accionOrden'])) {
  $controllerOrden->procesarFormulario(); // El controlador maneja la redirección PRG
}

// Obtener las órdenes para mostrar en la tabla, aplicando filtros si vienen en $_GET
$ordenes = $controllerOrden->procesarFiltro();

// PARA MANTENER EL ESTADO DE LOS CHECKBOXES EN EL MODAL DESPUÉS DE UNA REDIRECCIÓN
// Recuperar los filtros aplicados que ahora vienen por $_GET
$filtrosAplicados = [
  'estado_id' => $_GET['estado_id'] ?? [],
  'almacen_id' => $_GET['almacen_id'] ?? [],
  'alimento_id' => $_GET['alimento_id'] ?? []
];

// Lógica de estadísticas (aquí no la necesitaremos, pero la pongo comentada si se llegara a usar en el futuro)
/*
$estadisticas = [
  1 => 0, // Creada
  2 => 0, // Enviada
  3 => 0, // En Preparación
  4 => 0, // En Traslado
  5 => 0, // Entregada
  6 => 0, // Cancelada
];

foreach ($ordenes as $o) {
  $estado_id = $o->getEstado_id(); // O getEstadoId() si ese es el método
  if (isset($estadisticas[$estado_id])) {
    $estadisticas[$estado_id]++;
  }
}
*/

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Ordenes Tractorista</title>
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
  <?php require_once __DIR__ . '../../secciones/headerTractorista.php'; ?>
  <?php require_once __DIR__ . '../../secciones/navbarTractorista.php'; ?>

  <div class="main">
    <div class="form-container" id="formCampoContainer">
      <div class="form-title">Órdenes de Distribución</div>
      <form method="POST">
        <div class="botones-container">
          <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#filtroModal">
            Filtrar por
          </button>
        </div>
      </form>
    </div>

    <h2 class="titulosSecciones">Órdenes Pendientes</h2>
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
        <?php
        $hayOrdenesVisibles = false;

        if (!empty($ordenes)) {
          foreach ($ordenes as $o):
            if (in_array($o->getEstado_id(), [1, 5, 6]))
              continue;

            $hayOrdenesVisibles = true;
            ?>
            <tr>
              <td><?= htmlspecialchars($o->getId()) ?></td>
              <td><?= htmlspecialchars($o->almacen_nombre ?? '') ?></td>
              <td><?= htmlspecialchars($o->alimento_nombre ?? '') ?></td>
              <td><?= htmlspecialchars($o->getCantidad()) ?></td>
              <td><?= htmlspecialchars(date('d-m-Y', strtotime($o->getFecha_actualizacion()))) ?></td>
              <td><?= htmlspecialchars($o->getHora_actualizacion()) ?></td>
              <td>
                <span class="<?php
                switch ($o->getEstado_id()) {
                  case 1:
                    echo 'estado-creada';
                    break;
                  case 2:
                    echo 'estado-enviada';
                    break;
                  case 3:
                    echo 'estado-enPreparacion';
                    break;
                  case 4:
                    echo 'estado-enTraslado';
                    break;
                  case 5:
                    echo 'estado-entregada';
                    break;
                  case 6:
                    echo 'estado-cancelada';
                    break;
                  default:
                    echo 'estado-desconocido';
                    break;
                }
                ?>">
                  <?= htmlspecialchars($o->estado_nombre ?? '') ?>
                </span>
              </td>
              <td>
                <?php if ($o->getEstado_id() == 2): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="preparar" class="btn btn-success btn-sm">Preparar</button>
                  </form>
                  <form method="POST" style="display:inline;" onsubmit="return showCancelModal(this);">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <input type="hidden" name="accionOrden" value="cancelar">
                    <input type="hidden" name="descripcion" id="cancel_description_<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Cancelar</button>
                  </form>
                <?php elseif ($o->getEstado_id() == 3): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="trasladar"
                      class="btn btn-success btn-sm">Trasladar</button>
                  </form>
                <?php elseif ($o->getEstado_id() == 4): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="entregar" class="btn btn-success btn-sm">Entregar</button>
                  </form>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php
          endforeach;
        }

        if (!$hayOrdenesVisibles):
          ?>
          <tr>
            <td colspan="8">No hay órdenes para mostrar.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table></br>
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
                  <input class="form-check-input" type="checkbox" name="estado_id[]"
                    value="<?= htmlspecialchars($e->getId()) ?>" id="estado_<?= htmlspecialchars($e->getId()) ?>"
                    <?= (isset($filtrosAplicados['estado_id']) && is_array($filtrosAplicados['estado_id']) && in_array($e->getId(), $filtrosAplicados['estado_id'])) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="estado_<?= htmlspecialchars($e->getId()) ?>">
                    <?= htmlspecialchars($e->getNombre()) ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mb-3">
              <label class="form-label">Almacenes</label><br>
              <?php foreach ($almacenes as $al): ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" name="almacen_id[]"
                    value="<?= htmlspecialchars($al->getId()) ?>" id="almacen_<?= htmlspecialchars($al->getId()) ?>"
                    <?= (isset($filtrosAplicados['almacen_id']) && is_array($filtrosAplicados['almacen_id']) && in_array($al->getId(), $filtrosAplicados['almacen_id'])) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="almacen_<?= htmlspecialchars($al->getId()) ?>">
                    <?= htmlspecialchars($al->getNombre()) ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="mb-3">
              <label class="form-label">Alimentos</label><br>
              <?php foreach ($alimentos as $a): ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" name="alimento_id[]"
                    value="<?= htmlspecialchars($a->getId()) ?>" id="alimento_<?= htmlspecialchars($a->getId()) ?>"
                    <?= (isset($filtrosAplicados['alimento_id']) && is_array($filtrosAplicados['alimento_id']) && in_array($a->getId(), $filtrosAplicados['alimento_id'])) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="alimento_<?= htmlspecialchars($a->getId()) ?>">
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
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel" aria-hidden="true">
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
            display: true,
            text: 'Distribución de órdenes por estado'
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

    let currentCancelForm = null;

    function showCancelModal(form) {
      currentCancelForm = form;
      const cancelReasonModal = new bootstrap.Modal(document.getElementById('cancelReasonModal'));
      cancelReasonModal.show();
      return false; // Prevenir el envío inmediato del formulario
    }

    document.getElementById('confirmCancelBtn').addEventListener('click', function () {
      const description = document.getElementById('cancelReasonTextarea').value;
      if (currentCancelForm && description) {
        // Encontrar el input oculto con el ID específico para el orden_id del formulario actual
        // Asegúrate de que este ID coincida con el 'id' del input oculto en el formulario del botón "Cancelar"
        currentCancelForm.querySelector('#cancel_description_' + currentCancelForm.elements.orden_id.value).value = description;

        // Ocultar modal y enviar formulario
        const cancelModalInstance = bootstrap.Modal.getInstance(document.getElementById('cancelReasonModal'));
        if (cancelModalInstance) {
          cancelModalInstance.hide();
        }
        currentCancelForm.submit();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Por favor, ingresá el motivo de la cancelación.',
          confirmButtonColor: '#3085d6'
        });
      }
    });

    // Restablecer el textarea cuando el modal se cierra
    document.getElementById('cancelReasonModal').addEventListener('hidden.bs.modal', function () {
      document.getElementById('cancelReasonTextarea').value = '';
    });
  </script>
</body>

</html>