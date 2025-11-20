<?php

session_start();
require_once __DIR__ . '../../../../backend/controladores/usuarioController.php';
require_once __DIR__ . '../../../../backend/controladores/ordenController.php';
require_once __DIR__ . '../../../../backend/controladores/stock_almacenController.php';

if (!isset($_SESSION['token'])) {
  header("Location: index.php");
  exit;
}

$controller = new UsuarioController();
$usuario = $controller->getUsuarioByToken($_SESSION['token']);

if (!$usuario) {
  session_destroy();
  header("Location: index.php");
  exit;
}

if ($usuario->getRol_id() != 1) {
  header('Location: usuario.php');
  exit;
}

// Lógica para obtener los datos requeridos
$controllerOrden = new OrdenController();
$controllerStock = new Stock_almacenController();

// Obtener todas las órdenes para el gráfico de estados
$ordenes = $controllerOrden->obtenerOrdenes();

// Lógica de estadísticas del grafico
$estadisticas = [
  1 => 0, // Creada
  2 => 0, // Enviada
  3 => 0, // En Preparación
  4 => 0, // En Traslado
  5 => 0, // Entregada
  6 => 0, // Cancelada
];

// Calcular el total de órdenes por estado para el gráfico
foreach ($ordenes as $o) {
  $estado_id = $o->getEstado_id();
  if (isset($estadisticas[$estado_id])) {
    $estadisticas[$estado_id]++;
  }
}

// Obtener el valor económico total del stock
$total_stock_value = $controllerStock->getTotalEconomicValue();

// Calcular el valor total de las órdenes entregadas
$total_ordenes_entregadas_value = 0;
foreach ($ordenes as $o) {
  if ($o->getEstado_id() == 5) { // Suponiendo que 5 es el ID de "Entregada"
    $total_ordenes_entregadas_value += $o->alimento_precio * $o->getCantidad();
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Home</title>
  <link rel="icon" href=".../../../../img/logo2.png" type="image/png">
  <link rel="stylesheet" href="../../css/estilos.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
</head>

<body class="bodyHome">
  <?php require_once __DIR__ . '../../secciones/header.php'; ?>
  <?php require_once __DIR__ . '../../secciones/navbar.php'; ?>


  <div class="main">
    <h1 class="mensajeBienvenida" style="margin-top: -30px;">Bienvenido <?php echo $usuario->getUsername() ?></h1>
    <div class="container">
      <div class="row">
        <div class="col-lg-2"></div>
        <div class="col-lg-5 col-md-8 text-center mt-3 mb-4">
          <h2 class="titulosSecciones"></h2>
          <div style="max-width: 500px; margin: 0 auto;">
            <canvas id="graficoEstados" width="400" height="400"></canvas>
          </div>
        </div>
        <div class="col-lg-5 col-md-8 mb-4">
          <div class="d-flex flex-column h-100 justify-content-center">
            <a href="../stock/stock.php" style="text-decoration: none;">
              <div class="card mb-4">
                <div class="card-body">
                  <h5 class="card-title">Valor total de stock</h5>
                  <p class="card-text fs-3">
                    $<?= number_format($total_stock_value, 2, ',', '.') ?>
                  </p>
                </div>
              </div>
            </a>
            <a href="../reportes/reportes.php" style="text-decoration: none;">
              <div class="card" style="border: 2px solid red;">
                <div class="card-body">
                  <h5 class="card-title" style="color: red;">Valor total de órdenes entregadas</h5>
                  <p class="card-text fs-3" style="color: red;">
                    $<?= number_format($total_ordenes_entregadas_value, 2, ',', '.') ?></p>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
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
          labels: ['Creada', 'Enviada', 'En preparacion para envio', 'Trasladando a campo', 'Entregada en campo', 'Cancelada'],
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
    });
  </script>
</body>

</html>