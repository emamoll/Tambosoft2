<?php

require_once __DIR__ . '../../DAOS/stock_almacenDAO.php';
require_once __DIR__ . '../../DAOS/almacenDAO.php';
require_once __DIR__ . '../../DAOS/alimentoDAO.php';

class Stock_almacenController
{
  private $stock_almacenDAO;
  private $almacenDAO;
  private $alimentoDAO;

  public function __construct()
  {
    $this->stock_almacenDAO = new Stock_AlmacenDAO();
  }

  public function procesarFormularios()
  {
    $mensaje = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $accion = $_POST['accion'] ?? '';
      $almacen_nombre = trim($_POST['almacen_nombre'] ?? '');
      $alimento_nombre = trim($_POST['alimento_nombre'] ?? '');
      $cantidad = trim($_POST['cantidad'] ?? '');

      switch ($accion) {
        case 'actualizar':
          if (empty($almacen_nombre) || empty($alimento_nombre) || empty($cantidad)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, completá todos los campos para actualizar el stock.'];
          }

          if (!is_numeric($cantidad) || $cantidad <= 0) {
            return ['tipo' => 'error', 'mensaje' => 'La cantidad debe ser un número positivo.'];
          }

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

          if ($this->stock_almacenDAO->actualizarStock_almacen($almacen_id, $alimento_id, $cantidad)) {
            return ['tipo' => 'success', 'mensaje' => 'Stock actualizado correctamente.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al actualizar el stock.'];
          }
        case 'consultar':
          if (empty($almacen_nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, seleccion un almacen para consultar el stock.'];
          }

          if (empty($almacen_nombre)) {

            // Si no se seleccionó ningún almacén, mostrar todos los registros
            $stock_almacenes = $this->stock_almacenDAO->getAllStock_almacenes();
          } else {
            // Buscar el almacén por nombre
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

  public function getStock_almacenesItemsByAlmacenId($almacen_id)
  {
    return $this->stock_almacenDAO->getStock_almacenByAlmacenId($almacen_id);
  }

  public function getAllStock_almacenes()
  {
    return $this->stock_almacenDAO->getAllStock_almacenes();
  }

}