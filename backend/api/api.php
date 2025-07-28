<?php
require_once __DIR__ . '/../controladores/stock_almacenController.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$controller = new Stock_almacenController();

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

  default:
    echo json_encode(['error' => 'Acción no válida']);
    break;
}
?>