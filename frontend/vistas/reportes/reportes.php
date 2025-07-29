<?php

require_once __DIR__ . '../../../../backend/controladores/estadoController.php';
require_once __DIR__ . '../../../../backend/controladores/categoriaController.php'; // Se mantiene si es necesario para los alimentos, aunque no se filtre por ella.
require_once __DIR__ . '../../../../backend/controladores/alimentoController.php';
require_once __DIR__ . '../../../../backend/controladores/almacenController.php'; // Necesario para obtener nombres de almacenes en filtros.
require_once __DIR__ . '../../../../backend/controladores/ordenController.php';
require_once __DIR__ . '../../../../backend/servicios/libs/fpdf.php'; // Se mantiene si hay botón de descarga de PDF.

session_start(); // Iniciar sesión al principio para usar $_SESSION

if (!isset($_SESSION['username']) || !isset($_SESSION['rol_id'])) {
  header('Location: ../../../index.php');
  exit;
}

// Inicializar mensaje Swala de la sesión (si se usan mensajes en esta vista)
$mensaje = $_SESSION['form_message'] ?? null;
unset($_SESSION['form_message']); // Eliminar el mensaje de la sesión una vez leído

$accion = $_POST['accion'] ?? '';
$accionOrden = $_POST['accionOrden'] ?? ''; // Acciones como enviar/cancelar (aunque gerencia no las use)

$controllerOrden = new OrdenController();

$controllerEstado = new EstadoController();
$estados = $controllerEstado->obtenerEstados();

$controllerCategoria = new CategoriaController();
$categorias = $controllerCategoria->obtenerCategorias(); // Se mantiene para el modal si se desean checkboxes de categorías

$controllerAlimento = new AlimentoController();
$alimentos = $controllerAlimento->obtenerAlimentos(); // Se mantiene para el modal si se desean checkboxes de alimentos

$controllerAlmacen = new AlmacenController(); // Necesario para el modal de filtros de almacenes.
$almacenes = $controllerAlmacen->obtenerAlmacenes();

// Gerencia no debería crear/modificar órdenes ni cambiar su estado desde esta vista.
// Solo se cargan los datos para la tabla, posiblemente filtrados.
// Si hay un formulario POST en esta página, lo manejaría aquí.
// Por ahora, asumimos que no hay acciones POST que afecten la base de datos de órdenes aquí.
// Si las hubiera, se usaría $controllerOrden->procesarFormulario(); y redirección.

// Obtener las órdenes para mostrar en la tabla, aplicando filtros si vienen en $_GET.
// Se usa procesarFiltro() para que maneje el PRG y cargue los nombres enriquecidos.
$ordenes = $controllerOrden->procesarFiltro();

// Gerencia solo debe ver órdenes ENTREGADAS (estado 5)
// Filtrar las órdenes después de que procesarFiltro las traiga y enriquezca.
$ordenesGerencia = [];
foreach ($ordenes as $orden) {
  if ($orden->getEstado_id() == 5) { // Suponiendo que 5 es el ID de estado "Entregada"
    $ordenesGerencia[] = $orden;
  }
}

// PARA MANTENER EL ESTADO DE LOS CHECKBOXES EN EL MODAL DESPUÉS DE UNA REDIRECCIÓN
// Recuperar los filtros aplicados que ahora vienen por $_GET
$filtrosAplicados = [
  'estado_id' => $_GET['estado_id'] ?? [],
  'almacen_id' => $_GET['almacen_id'] ?? [],
  'alimento_id' => $_GET['alimento_id'] ?? []
];

// Lógica de estadísticas (si se quieren mostrar, aunque la solicitud original dice "sin el gráfico")
// Si no quieres el gráfico, puedes eliminar todo el bloque JS del gráfico y estas variables.
/*
$estadisticas = [
  1 => 0, // Creada
  2 => 0, // Enviada
  3 => 0, // En Preparación
  4 => 0, // En Traslado
  5 => 0, // Entregada
  6 => 0, // Cancelada
];

foreach ($ordenesGerencia as $o) { // Usar $ordenesGerencia para las estadísticas
  $estado_id = $o->getEstado_id();
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
  <title>Tambosoft: Reporte de Órdenes</title>
  <link rel="icon" href="../../../img/logo2.png" type="image/png">
  <link rel="stylesheet" href="../../css/estilos.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bodyHome">
  <?php require_once __DIR__ . '../../secciones/header.php'; ?>
  <?php require_once __DIR__ . '../../secciones/navbar.php'; ?>
  <div class="main">
    <div class="form-container" id="formCampoContainer">
      <div class="form-title">Reporte de Órdenes Entregadas</div>
      <!-- <form method="POST">
        <div class="botones-container">
          <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#filtroModal">
            Filtrar por
          </button>
        </div>
      </form> -->
    </div>
    <table class="tabla" id="tablaContainer">
      <thead>
        <tr>
          <th>Orden N</th>
          <th>Almacen</th>
          <th>Alimento</th>
          <th>Cantidad</th>
          <th>Precio unitario</th>
          <th>Total</th>
          <th>Fecha Entrega</th>
          <th>Hora Entrega</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($ordenesGerencia)): ?>
          <?php
          $sumaTotal = 0; // Inicializar la variable de suma total para esta tabla
          foreach ($ordenesGerencia as $o):
            // El precio unitario ya debe estar adjuntado al objeto $o por ordenController->enrichOrdenesWithNames()
            $precioUnitario = $o->alimento_precio ?? 0; // Si por alguna razón no se adjuntó, usar 0
        
            $subtotal = $precioUnitario * $o->getCantidad();
            $sumaTotal += $subtotal; // Sumar al total general de la tabla
            ?>
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
                $
                <?= number_format($precioUnitario, 2, ',', '.') ?>
              </td>
              <td>$
                <?= number_format($subtotal, 2, ',', '.') ?>
              </td>
              <td>
                <?php
                // Mostrar la fecha de actualización si el estado es entregado
                // Esta condición ($o->getEstado_id() == 5) es redundante aquí porque $ordenesGerencia ya filtra por estado 5.
                $fecha = $o->getFecha_actualizacion();
                $fechaFormateada = date('d-m-Y', strtotime($fecha));
                echo htmlspecialchars($fechaFormateada);
                ?>
              </td>
              <td>
                <?php
                // Esta condición ($o->getEstado_id() == 5) es redundante aquí porque $ordenesGerencia ya filtra por estado 5.
                echo htmlspecialchars($o->getHora_actualizacion());
                ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <tr style="font-weight: bold;">
            <td colspan="5" style="text-align: right;">Total general:</td>
            <td>$
              <?= number_format($sumaTotal, 2, ',', '.') ?>
            </td>
            <td colspan="2"></td>
          </tr>
        <?php else: ?>
          <tr>
            <td colspan="8">No hay órdenes entregadas que coincidan con los filtros.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table></br>
    <form action="../../../backend/servicios/libs/ReporteOrdenes.php" method="GET" target="_blank" class="botonPDF">
      <button type="submit" name="generar_pdf" class="btn btn-danger">Descargar PDF</button>
      <?php
      // Añadir inputs ocultos para cada filtro aplicado que viene en $_GET
      // Asegurarse de que $filtrosAplicados sea un array antes del foreach
      if (isset($filtrosAplicados) && is_array($filtrosAplicados)) {
        foreach ($filtrosAplicados as $filterName => $filterValues) {
          if (is_array($filterValues) && !empty($filterValues)) {
            foreach ($filterValues as $value) {
              echo '<input type="hidden" name="' . htmlspecialchars($filterName) . '[]" value="' . htmlspecialchars($value) . '">';
            }
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
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-warning" name="limpiar_filtros" value="true">Limpiar Filtros</button>
              <button type="submit" class="btn btn-primary" name="aplicar_filtros" value="true">Aplicar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Remover selectores y lógica no usada en esta vista (Gerencia no crea/modifica órdenes)
      // const almacenSelect = document.getElementById('almacen_nombre_select');
      // const alimentoSelect = document.getElementById('alimento_nombre_select');
      // const stockDisplay = document.getElementById('stock_disponible');
      // const stockContainer = document.getElementById('stock_disponible_container');
      // const cantidadInput = document.getElementById('cantidad');
      // const ordenModificarAlmacenIdInput = document.getElementById('orden_modificar_almacen_id');
      // const ordenModificarAlimentoIdInput = document.getElementById('orden_modificar_alimento_id');
      // const ordenModificarCantidadInput = document.getElementById('orden_modificar_cantidad');

      const limpiarFiltrosBtn = document.getElementById('limpiarFiltrosBtn');
      const filtroForm = document.getElementById('filtroForm');

      // Funciones de carga de alimentos y stock no necesarias aquí
      // function fetchAndPopulateAlimentos(...) {...}
      // almacenSelect.addEventListener('change', ...)
      // alimentoSelect.addEventListener('change', ...)
      // cantidadInput.addEventListener('input', ...)
      // if (ordenModificarAlmacenIdInput ...) {...}

      // JavaScript para desmarcar checkboxes AL ABRIR el modal, si no hay filtros aplicados
      const filtroModal = document.getElementById('filtroModal');
      if (filtroModal) {
        filtroModal.addEventListener('show.bs.modal', function (event) {
          const urlParams = new URLSearchParams(window.location.search);
          const hasFiltersInUrl = urlParams.has('estado_id[]') || urlParams.has('almacen_id[]') || urlParams.has('alimento_id[]') ||
            urlParams.has('estado_id') || urlParams.has('almacen_id') || urlParams.has('alimento_id');

          if (!hasFiltersInUrl) {
            const filtroFormElement = document.getElementById('filtroForm');
            if (filtroFormElement) {
              const checkboxes = filtroFormElement.querySelectorAll('input[type="checkbox"]');
              checkboxes.forEach(chk => {
                chk.checked = false;
              });
            }
          }
        });
      }

      // Lógica para el botón Limpiar Filtros (hace submit, pero el controlador lo interpreta como limpieza)
      if (limpiarFiltrosBtn && filtroForm) {
        limpiarFiltrosBtn.addEventListener('click', function () {
          console.log('Limpiar filtros: se hizo click en Gerencia');
          // No se desmarcan con JS aquí, el submit con 'limpiar_filtros' recarga la página sin ellos.
          // Si quisieras que se desmarquen visualmente ANTES del submit, podrías añadir la lógica aquí.
          // Pero con PRG, es más común que la recarga los desmarque.
        });
      }

      // NO hay Gráfico Chart.js en esta vista, por lo que el script del gráfico se elimina.
    });
  </script>
</body>

</html>