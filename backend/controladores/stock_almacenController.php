<?php

// Incluye los archivos de las capas de acceso a datos para las operaciones de stock, almacenes y alimentos.
require_once __DIR__ . '../../DAOS/stock_almacenDAO.php';
require_once __DIR__ . '../../DAOS/almacenDAO.php';
require_once __DIR__ . '../../DAOS/alimentoDAO.php';

/**
 * Clase controladora para gestionar las operaciones relacionadas con el stock en los almacenes.
 */
class Stock_almacenController
{
  // Propiedades privadas para las instancias de las clases DAO.
  private $stock_almacenDAO;
  private $almacenDAO;
  private $alimentoDAO;

  /**
   * Constructor de la clase.
   * Inicializa la propiedad `$stock_almacenDAO`.
   */
  public function __construct()
  {
    $this->stock_almacenDAO = new Stock_AlmacenDAO();
  }

  /**
   * Procesa los formularios para actualizar o consultar el stock.
   *
   * @return array|null Un array con el tipo y mensaje de la respuesta,
   * o null si la petición no es de tipo POST.
   */
  public function procesarFormularios()
  {
    $mensaje = '';

    // Verifica si la petición es de tipo POST.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Obtiene la acción y los datos del formulario.
      $accion = $_POST['accion'] ?? '';
      $almacen_nombre = trim($_POST['almacen_nombre'] ?? '');
      $alimento_nombre = trim($_POST['alimento_nombre'] ?? '');
      $cantidad = trim($_POST['cantidad'] ?? '');

      // Evalúa la acción.
      switch ($accion) {
        case 'actualizar':
          // Lógica para actualizar el stock.
          if (empty($almacen_nombre) || empty($alimento_nombre) || empty($cantidad)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, completá todos los campos para actualizar el stock.'];
          }
          if (!is_numeric($cantidad) || $cantidad <= 0) {
            return ['tipo' => 'error', 'mensaje' => 'La cantidad debe ser un número positivo.'];
          }

          // Obtiene los IDs del almacén y el alimento.
          $almacenDAO = new AlmacenDAO();
          $almacen = $almacenDAO->getAlmacenByNombre($almacen_nombre);
          if (!$almacen) {
            return ['tipo' => 'error', 'mensaje' => 'El almacen seleccionado no existe.'];
          }
          $almacen_id = $almacen->getId();

          $alimentoDAO = new AlimentoDAO();
          $alimento = $alimentoDAO->getAlimentoByNombre($alimento_nombre);
          if (!$alimento) {
            return ['tipo' => 'error', 'mensaje' => 'El alimento seleccionado no existe.'];
          }
          $alimento_id = $alimento->getId();

          // Intenta actualizar el stock.
          if ($this->stock_almacenDAO->actualizarStock_almacen($almacen_id, $alimento_id, $cantidad)) {
            return ['tipo' => 'success', 'mensaje' => 'Stock actualizado correctamente.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al actualizar el stock.'];
          }
        case 'consultar':
          // Lógica para consultar el stock.
          if (empty($almacen_nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, seleccion un almacen para consultar el stock.'];
          }

          if (empty($almacen_nombre)) {
            // Si no se selecciona un almacén, obtiene todos los registros.
            $stock_almacenes = $this->stock_almacenDAO->getAllStock_almacenes();
          } else {
            // Busca el almacén y obtiene su stock.
            $almacenDAO = new AlmacenDAO();
            $almacen = $almacenDAO->getAlmacenByNombre($almacen_nombre);
            if (!$almacen) {
              return ['tipo' => 'error', 'mensaje' => 'El almacén seleccionado no existe.'];
            }
            $almacen_id = $almacen->getId();
            $stock_almacenes = $this->stock_almacenDAO->getStock_almacenByAlmacenId($almacen_id);
          }

          return [
            'tipo' => 'success',
            'mensaje' => 'Consulta realizada correctamente.',
            'stock_almacenes' => $stock_almacenes
          ];
      }
    }
  }

  /**
   * Obtiene los elementos de stock para un almacén específico.
   *
   * @param int $almacen_id El ID del almacén.
   * @return array Un array de objetos Stock_almacen.
   */
  public function getStock_almacenesItemsByAlmacenId($almacen_id)
  {
    return $this->stock_almacenDAO->getStock_almacenByAlmacenId($almacen_id);
  }

  /**
   * Obtiene todos los registros de stock de todos los almacenes.
   *
   * @return array Un array de objetos Stock_almacen.
   */
  public function getAllStock_almacenes()
  {
    return $this->stock_almacenDAO->getAllStock_almacenes();
  }

  /**
   * Obtiene una lista de alimentos con stock en un almacén.
   *
   * @param int $almacen_id El ID del almacén.
   * @return array Un array de objetos de alimentos.
   */
  public function getAlimentosByAlmacenId($almacen_id)
  {
    return $this->stock_almacenDAO->getAlimentosConStockByAlmacenId($almacen_id);
  }

  /**
   * Actualiza el stock de un alimento en un almacén.
   *
   * @param int $almacen_id El ID del almacén.
   * @param int $alimento_id El ID del alimento.
   * @param int $cantidad La cantidad a agregar o actualizar.
   * @return bool True si la actualización fue exitosa, de lo contrario, false.
   */
  public function actualizarStock_almacen($almacen_id, $alimento_id, $cantidad)
  {
    return $this->stock_almacenDAO->actualizarStock_almacen($almacen_id, $alimento_id, $cantidad);
  }

  /**
   * Obtiene la cantidad de stock de un alimento en un almacén específico.
   *
   * @param int $almacen_id El ID del almacén.
   * @param int $alimento_id El ID del alimento.
   * @return int La cantidad de stock, o 0 si no existe.
   */
  public function getStockByAlimentoInAlmacen($almacen_id, $alimento_id)
  {
    $stock_almacen = $this->stock_almacenDAO->getStock_almacenByAlmacenIdAndAlimentoId($almacen_id, $alimento_id);
    return $stock_almacen ? $stock_almacen->getStock() : 0;
  }

  /**
   * Reduce el stock de un alimento en un almacén.
   *
   * @param int $almacen_id El ID del almacén.
   * @param int $alimento_id El ID del alimento.
   * @param int $cantidad La cantidad a reducir.
   * @return bool True si la reducción fue exitosa, de lo contrario, false.
   */
  public function reducirStock($almacen_id, $alimento_id, $cantidad)
  {
    return $this->stock_almacenDAO->reducirStock_almacen($almacen_id, $alimento_id, $cantidad);
  }
}