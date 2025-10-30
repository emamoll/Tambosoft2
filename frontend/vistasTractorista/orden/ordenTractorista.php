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
// Obtener todos los estados para el filtro y la tabla
$estados = $controllerEstado->obtenerEstados();
// Obtener el ID del estado "Creada" para el filtro
$estadoCreada = $controllerEstado->getEstadoById(1);
$estadoCreadaId = $estadoCreada ? $estadoCreada->getId() : null;
// Filtrar los estados para que no aparezca la opción "Creada" en el modal
$estadosParaFiltro = array_filter($estados, function ($e) use ($estadoCreadaId) {
  return $e->getId() != $estadoCreadaId;
});

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

  <script>
    // Variable global para almacenar el formulario actual
    let currentCancelForm = null;

    // La función showCancelModal es global para ser llamada por el onsubmit en el HTML
    function showCancelModal(form) {
      currentCancelForm = form; // Guarda una referencia al formulario
      // document.getElementById('cancelReasonModal') YA EXISTIRÁ cuando se muestre el modal por primera vez.
      const cancelReasonModal = new bootstrap.Modal(document.getElementById('cancelReasonModal'));
      cancelReasonModal.show();
      // Limpiar el textarea CADA VEZ que se abre el modal.
      // Aquí, document.getElementById('cancelReasonTextarea') puede ser null si el modal no se ha cargado en el DOM aún.
      // Pero este código solo se ejecuta cuando showCancelModal es llamada (al hacer clic en "Cancelar"),
      // y para entonces el modal y sus elementos ya deberían estar en el DOM.
      document.getElementById('cancelReasonTextarea').value = '';
      return false; // Previene el envío inmediato del formulario
    }
  </script>
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
              <?php foreach ($estadosParaFiltro as $e): ?>
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
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cancelReasonModalLabel">Motivo de Cancelación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="cancelReasonTextarea" class="form-label">Por favor, ingrese el motivo de la cancelación:</label>
            <textarea class="form-control" id="cancelReasonTextarea" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-danger" id="confirmCancelBtn">Confirmar Cancelación</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // === INICIO DEL CÓDIGO DE CANCELACIÓN (listeners de DOM) ===
      // Estos listeners se adjuntan solo cuando el DOM está completamente cargado
      document.getElementById('confirmCancelBtn').addEventListener('click', function () {
        const cancelDescription = document.getElementById('cancelReasonTextarea').value;

        if (cancelDescription.trim() === '') {
          Swal.fire({
            icon: 'warning',
            title: 'Motivo Requerido',
            text: 'Por favor, ingresa el motivo de la cancelación antes de confirmar.',
            confirmButtonText: 'Entendido'
          });
          return;
        }

        if (currentCancelForm) {
          const ordenId = currentCancelForm.elements.orden_id.value;
          const hiddenDescriptionInput = document.getElementById(`cancel_description_${ordenId}`);
          if (hiddenDescriptionInput) {
            hiddenDescriptionInput.value = cancelDescription;
          } else {
            console.error('Input oculto de descripción no encontrado para la orden:', ordenId);
            Swal.fire({
              icon: 'error',
              title: 'Error Interno',
              text: 'No se pudo asociar el motivo a la orden. Contacte a soporte.',
              confirmButtonColor: '#3085d6'
            });
            return;
          }

          const cancelReasonModalInstance = bootstrap.Modal.getInstance(document.getElementById('cancelReasonModal'));
          if (cancelReasonModalInstance) {
            cancelReasonModalInstance.hide();
          }

          currentCancelForm.submit();
        } else {
          console.error('No hay formulario de cancelación actual establecido.');
          Swal.fire({
            icon: 'error',
            title: 'Error Interno',
            text: 'Hubo un problema al procesar la cancelación. Intente de nuevo.',
            confirmButtonColor: '#3085d6'
          });
        }
      });

      document.getElementById('cancelReasonModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('cancelReasonTextarea').value = '';
      });
      // === FIN DEL CÓDIGO DE CANCELACIÓN (listeners de DOM) ===


      // Tu código JavaScript existente para filtros, stock, etc.
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

      // ... (el resto de tus funciones y event listeners que acceden al DOM) ...
      function fetchAndPopulateAlimentos(almacenId, selectedAlimentoId = null, callback = null) {
        // ... (tu código) ...
      }

      almacenSelect.addEventListener('change', function () {
        // ... (tu código) ...
      });

      alimentoSelect.addEventListener('change', function () {
        // ... (tu código) ...
      });

      cantidadInput.addEventListener('input', function () {
        // ... (tu código) ...
      });

      if (ordenModificarAlmacenIdInput && ordenModificarAlimentoIdInput && ordenModificarCantidadInput) {
        // ... (tu código) ...
      }

      limpiarFiltrosBtn.addEventListener('click', function () {
        // ... (tu código) ...
      });

      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
      }

      // Para manejar los mensajes de SweetAlert2 después de una redirección PRG
      const mensajeSwal = <?= json_encode($mensaje); ?>;
      if (mensajeSwal) {
        Swal.fire({
          icon: mensajeSwal.type,
          title: mensajeSwal.title,
          text: mensajeSwal.text,
          timer: 3000,
          showConfirmButton: false
        });
      }

      // Gráfico Chart.js
      // IMPORTANTE: La variable PHP '$estadisticas' está comentada en tu código PHP.
      // Si deseas que el gráfico funcione, debes descomentar la sección PHP de '$estadisticas'
      // para que esta variable esté definida antes de ser usada aquí.
      // Si no necesitas el gráfico en esta vista, puedes eliminar esta parte del JavaScript.


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
    });
  </script>
</body>

</html>