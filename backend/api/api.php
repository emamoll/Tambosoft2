<?php
// Incluye los controladores necesarios para la API.
require_once __DIR__ . '/../controladores/stock_almacenController.php';
require_once __DIR__ . '../../controladores/ordenController.php';

// Establece el encabezado para que la respuesta sea de tipo JSON.
header('Content-Type: application/json');

// Obtiene la acción solicitada desde los parámetros de la URL (GET).
// Si no se especifica ninguna acción, se asigna una cadena vacía.
$action = $_GET['action'] ?? '';

// Instancia los controladores que se usarán para manejar las solicitudes.
$controller = new Stock_almacenController();
$ordenController = new OrdenController();

// Un "switch" para manejar las diferentes acciones de la API.
// Evalúa la variable $action para determinar qué bloque de código ejecutar.
switch ($action) {
  // Caso para obtener los alimentos disponibles en un almacén específico.
  case 'getAlimentosByAlmacen':
    // Obtiene el ID del almacén desde la URL.
    $almacenId = $_GET['almacenId'] ?? null;
    if ($almacenId) {
      // Llama al método del controlador para obtener los alimentos y los codifica en formato JSON.
      $alimentos = $controller->getAlimentosByAlmacenId((int) $almacenId);
      echo json_encode($alimentos);
    } else {
      // Si falta el ID del almacén, devuelve un error en formato JSON.
      echo json_encode(['error' => 'Falta el ID del almacén']);
    }
    break;

  // Caso para obtener el stock de un alimento específico en un almacén.
  case 'getStockForAlimento':
    // Obtiene los IDs del almacén y del alimento desde la URL.
    $almacenId = $_GET['almacenId'] ?? null;
    $alimentoId = $_GET['alimentoId'] ?? null;
    if ($almacenId && $alimentoId) {
      // Llama al método del controlador para obtener el stock y lo codifica en JSON.
      $stock = $controller->getStockByAlimentoInAlmacen((int) $almacenId, (int) $alimentoId);
      echo json_encode(['stock' => $stock]);
    } else {
      // Si faltan IDs, devuelve un error.
      echo json_encode(['error' => 'Faltan IDs de almacén o alimento']);
    }
    break;

  // Caso para obtener los detalles de la cancelación de una orden.
  case 'getCancelacionDetail':
    // Obtiene el ID de la orden desde la URL.
    $ordenId = $_GET['ordenId'] ?? null;
    if ($ordenId) {
      // Llama al método del controlador para obtener el detalle de cancelación.
      $cancelacion = $ordenController->obtenerDetalleCancelacion((int) $ordenId);
      if ($cancelacion) {
        // Si se encuentra la cancelación, formatea los datos y los devuelve como JSON.
        echo json_encode([
          'fecha' => date('d-m-Y', strtotime($cancelacion->getFecha())),
          'hora' => $cancelacion->getHora(),
          'descripcion' => $cancelacion->getDescripcion()
        ]);
      } else {
        // Si no se encuentra el detalle de cancelación, devuelve un error.
        echo json_encode(['error' => 'Detalle de cancelación no encontrado para esta orden.']);
      }
    } else {
      // Si falta el ID de la orden, devuelve un error.
      echo json_encode(['error' => 'Falta el ID de la orden.']);
    }
    break;

  // Caso por defecto para manejar acciones no válidas.
  default:
    // Si la acción solicitada no coincide con ningún caso, se devuelve un error.
    echo json_encode(['error' => 'Acción no válida']);
    break;
}
?>