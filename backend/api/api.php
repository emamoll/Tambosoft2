<?php
require_once __DIR__ . '/../controladores/stock_almacenController.php';
require_once __DIR__ . '../../controladores/ordenController.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$controller = new Stock_almacenController();
$ordenController = new OrdenController();

switch ($action) {
  case 'getAlimentosByAlmacen':
    $almacenId = $_GET['almacenId'] ?? null;
    if ($almacenId) {
      $alimentos = $controller->getAlimentosByAlmacenId((int) $almacenId);
      echo json_encode($alimentos);
    } else {
      echo json_encode(['error' => 'Falta el ID del almacén']);
    }
    break;

  case 'getStockForAlimento':
    $almacenId = $_GET['almacenId'] ?? null;
    $alimentoId = $_GET['alimentoId'] ?? null;
    if ($almacenId && $alimentoId) {
      $stock = $controller->getStockByAlimentoInAlmacen((int) $almacenId, (int) $alimentoId);
      echo json_encode(['stock' => $stock]);
    } else {
      echo json_encode(['error' => 'Faltan IDs de almacén o alimento']);
    }
    break;

  case 'getCancelacionDetail': // ¡Este bloque es el que falta en tu api.php actual!
    $ordenId = $_GET['ordenId'] ?? null;
    if ($ordenId) {
      $cancelacion = $ordenController->obtenerDetalleCancelacion((int) $ordenId);
      if ($cancelacion) {
        echo json_encode([
          'fecha' => date('d-m-Y', strtotime($cancelacion->getFecha())),
          'hora' => $cancelacion->getHora(),
          'descripcion' => $cancelacion->getDescripcion()
        ]);
      } else {
        echo json_encode(['error' => 'Detalle de cancelación no encontrado para esta orden.']);
      }
    } else {
      echo json_encode(['error' => 'Falta el ID de la orden.']);
    }
    break;

  default:
    echo json_encode(['error' => 'Acción no válida']);
    break;
}
?>