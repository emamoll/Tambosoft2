<?php

require_once __DIR__ . '../../../../backend/controladores/estadoController.php';
require_once __DIR__ . '../../../../backend/controladores/almacenController.php';
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

$almacenes_ids = [];
$alimento_ids = [];
$estados_ids = [];

foreach ($ordenes as $o) {
  $almacenes_ids[] = $o->getAlmacen_id();  // estaba mal: usabas $almacen_ids
  $alimento_ids[] = $o->getAlimento_id();
  $estados_ids[] = $o->getEstado_id();
}

$almacenes_ids = array_unique($almacenes_ids);  // corregido
$alimento_ids = array_unique($alimento_ids);
$estados_ids = array_unique($estados_ids);
$controllerEstado = new EstadoController();
$estados = $controllerEstado->obtenerEstados();

$controllerAlmacen = new AlmacenController();
$almacenes = $controllerAlmacen->obtenerAlmacenes();
$controllerAlimento = new AlimentoController();
$alimentos = $controllerAlimento->obtenerAlimentos();
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

$estadisticas = [
  1 => 0,
  2 => 0,
  3 => 0,
  4 => 0,
  5 => 0,
  6 => 0,
];

foreach ($ordenes as $o) {
  $estado_id = $o->getEstado_id();
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
  <?php require_once __DIR__ . '../../secciones/header.php'; ?>
  <!--	--------------->
  <?php require_once __DIR__ . '../../secciones/navbar.php'; ?>

  <div class="main">
    <div class="form-container" id="formCampoContainer">
      <div class="form-title">Distribución de alimentos</div>
      <form method="POST">
        <div class="botones-container">
          <button type="submit" name="accion" value="crear" class="btn btn-primary">Crear ordenes</button>
          <button type="submit" name="accion" value="filtrar" class="btn btn-secondary">Filtrar por</button>
        </div>
      </form>

      <?php if ($accion === 'crear' || $ordenAModificar): ?>
      <div class="mt-4">
        <div class="form-title">Crear orden</div>
        <form method="POST">
          <input type="hidden" name="accion" value="cambio_categoria">
          <input type="hidden" name="id" value="<?= $ordenAModificar ? $ordenAModificar->getId() : '' ?>">
          <div class="form-group select-group">
            <select name="almacen_nombre">
              <option value="" disabled selected>Seleccione un almacen</option>
              <?php foreach ($almacenes as $al): ?>
              <option value="<?= htmlspecialchars($al->getNombre()) ?>">
                <?= htmlspecialchars($al->getNombre()) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group select-group">
            <select name="alimento_nombre">
              <option value="" disabled selected>Seleccione un alimento</option>
              <?php foreach ($alimentos as $al): ?>
              <option value="<?= htmlspecialchars($al->getNombre()) ?>">
                <?= htmlspecialchars($al->getNombre()) ?>
              </option>
              <?php endforeach; ?>
            </select>
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
          <?php endif; ?>
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

      <!-- Formulario de Filtro -->
      <?php if ($accion === 'filtrar'): ?>
        <div class="mt-4">
          <div class="form-title">Filtrar por</div>
          <form method="POST">
            <div class="mb-3">
              <label>Estado</label><br>
              <?php foreach ($estados as $e): ?>
                <label class="form-check-label">
                  <input class="form-check-input" type="checkbox" name="estado_id[]" value="<?= $e->getId() ?>"
                    <?= (isset($_POST['estado_id']) && in_array($e->getId(), $_POST['estado_id'])) ? 'checked' : '' ?>>
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
          <th>Fecha de Orden</th>
          <th>Hora de Orden</th>
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
                <?php
                foreach ($almacenes as $alm) {
                  if ($alm->getId() === $p->getAlmacen_id()) {
                    echo htmlspecialchars($alm->getNombre());
                    break;
                  }
                }
                ?>
              </td>
              <td>
                <?php
                foreach ($alimentos as $ali) {
                  if ($ali->getId() === $p->getAlimento_id()) {
                    echo htmlspecialchars($ali->getNombre());
                    break;
                  }
                }
                ?>
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
              } else {
                echo 'estado-cancelada';
              }
              ?>">
                <?php
                foreach ($estados as $est) {
                  if ($est->getId() === $p->getEstado_id()) {
                    echo htmlspecialchars($est->getNombre());
                    break;
                  }
                }
                ?>
              </td>
              <td>
                <?php if ($o->getEstado_id() == 1): ?>
                  <!-- Botón Modificar -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <input type="hidden" name="accion" value="modificar">
                    <button type="submit" class="btn btn-success btn-sm">Modificar</button>
                  </form>

                  <!-- Botón Enviar -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="enviar" class="btn btn-success btn-sm">Enviar</button>
                  </form>

                  <!-- Botón Cancelar -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="orden_id" value="<?= htmlspecialchars($o->getId()) ?>">
                    <button type="submit" name="accionOrden" value="cancelar" class="btn btn-success btn-sm">Cancelar</button>
                  </form>
                <?php else: ?>
                  <!-- Si está Enviado o Cancelado, no mostramos botones -->
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
    <form action="../../../backend/servicios/libs/ordenes.php" method="post" target="_blank" class="botonPDF">
      <button type="submit" name="generar_pdf" class="btn btn-danger">Descargar</button>
    </form>

    <h2 class="titulosSecciones">Estadísticas de órdenes</h2>
    <div style="max-width: 500px; margin: 0 auto 30px;">
      <canvas id="graficoEstados" width="400" height="400"></canvas>
    </div>

  </div>
  <script>
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
          legend: {
            position: 'bottom'
          },
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
  </script>
</body>

</html>