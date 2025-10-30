He agregado el botón "Ir a órdenes" que te redirige a la página de órdenes.

Aquí está el código completo y actualizado para tu archivo frontend/vistasTractorista/usuario/tractoristaHome.php.

PHP

<?php
session_start();
require_once __DIR__ . '../../../../backend/controladores/usuarioController.php';
require_once __DIR__ . '../../../../backend/controladores/ordenController.php';
require_once __DIR__ . '../../../../backend/controladores/alimentoController.php';
require_once __DIR__ . '../../../../backend/controladores/almacenController.php';
require_once __DIR__ . '../../../../backend/controladores/estadoController.php';

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

if ($usuario->getRol_id() != 2) {
  header('Location: ../../index.php');
  exit;
}

$controllerOrden = new OrdenController();
$controllerAlimento = new AlimentoController();
$controllerAlmacen = new AlmacenController();
$controllerEstado = new EstadoController();

// Obtener el ID del estado "Creada"
$estadoCreada = $controllerEstado->getEstadoById(1);
$estadoCreadaId = $estadoCreada ? $estadoCreada->getId() : null;

// Obtener todas las órdenes
$todasLasOrdenes = $controllerOrden->obtenerOrdenes();

// Filtrar las órdenes para excluir las que están en estado "Creada"
$ordenesFiltradas = array_filter($todasLasOrdenes, function ($orden) use ($estadoCreadaId) {
  return $orden->getEstado_id() != $estadoCreadaId;
});

// Ordenar las órdenes restantes por fecha de creación de forma descendente
usort($ordenesFiltradas, function ($a, $b) {
  return strtotime($b->getFecha_creacion()) - strtotime($a->getFecha_creacion());
});

// Tomar solo las primeras 10 (las más recientes)
$ultimasOrdenes = array_slice($ordenesFiltradas, 0, 10);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambosoft: Home Tractorista</title>
  <link rel="icon" href="../../../img/logo2.png" type="image/png">
  <link rel="stylesheet" href="../../css/estilos.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body class="bodyHome">
  <?php require_once __DIR__ . '../../secciones/headerTractorista.php'; ?>
  <?php require_once __DIR__ . '../../secciones/navbarTractorista.php'; ?>
  <h1 class="mensajeBienvenida">Bienvenido <?php echo $usuario->getUsername(); ?></h1>

  <div class="main">
    <div class="container mt-2">
      <div class="row">
        <div class="col-lg-2"></div>
        <div class="col-lg-10">
          <h2 class="titulosSecciones">Últimas Órdenes</h2>
          <div class="table-responsive">
            <table class="table table-striped table-bordered">
              <thead class="thead-dark">
                <tr>
                  <th>ID</th>
                  <th>Fecha de Creación</th>
                  <th>Almacén</th>
                  <th>Alimento</th>
                  <th>Cantidad</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($ultimasOrdenes)): ?>
                  <?php foreach ($ultimasOrdenes as $orden): ?>
                    <?php
                    $almacen = $controllerAlmacen->getAlmacenById($orden->getAlmacen_id());
                    $alimento = $controllerAlimento->getAlimentoById($orden->getAlimento_id());
                    $estado = $controllerEstado->getEstadoById($orden->getEstado_id());
                    ?>
                    <tr>
                      <td><?php echo $orden->getId(); ?></td>
                      <td><?php echo $orden->getFecha_creacion(); ?></td>
                      <td><?php echo $almacen->getNombre(); ?></td>
                      <td><?php echo $alimento->getNombre(); ?></td>
                      <td><?php echo $orden->getCantidad(); ?> kg</td>
                      <td><?php echo $estado->getNombre(); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center">No hay órdenes registradas para mostrar.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="mt-3">
            <a href="../orden/ordenTractorista.php" class="btn btn-primary">Ir a Órdenes</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
</body>

</html>