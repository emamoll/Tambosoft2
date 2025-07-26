<?php

require_once __DIR__ . '../../DAOS/almacenDAO.php';
require_once __DIR__ . '../../DAOS/alimentoDAO.php';
require_once __DIR__ . '../../DAOS/campoDAO.php';
require_once __DIR__ . '../../modelos/almacen/almacenModelo.php';

class AlmacenController
{
  private $almacenDAO;

  public function __construct()
  {
    $this->almacenDAO = new AlmacenDAO();
  }

  public function procesarFormularios()
  {
    $mensaje = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $accion = $_POST['accion'] ?? '';
      $nombre = trim($_POST['nombre'] ?? '');
      $campo_nombre = trim($_POST['campo_nombre'] ?? '');

      switch ($accion) {
        case 'registrar':
          if (empty($nombre) || empty($campo_nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, completá todos los campos para registrar.'];
          }

          $campoDAO = new CampoDAO();
          $campo = $campoDAO->getCampoByNombre($campo_nombre);

          if (!$campo) {
            return ['tipo' => 'error', 'mensaje' => 'El campo seleccionado no existe.'];
          }

          $campo_id = $campo->getId();
          $almacenExistente = $this->almacenDAO->getAlmacenByCampoId($campo->getId());

          $almacen = new Almacen(null, $nombre, $campo_id);

          if ($this->almacenDAO->registrarAlmacen($almacen)) {
            return ['tipo' => 'success', 'mensaje' => 'Almacen registrado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error: ya existe un almacen con ese nombre'];
          }
        case 'modificar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre de la categoría que querés modificar.'];
          }

          $almacenActual = $this->almacenDAO->getAlmacenByNombre($nombre);

          if (!$almacenActual) {
            return ['tipo' => 'error', 'mensaje' => 'El almacen no existe para modificar.'];
          }

          // Si el campo fue cambiado, validar que no esté ocupado por otro almacen
          if (!empty($campo_nombre)) {
            $campoDAO = new CampoDAO();
            $campo = $campoDAO->getCampoByNombre($campo_nombre);
            if (!$campo) {
              return ['tipo' => 'error', 'mensaje' => 'El campo no existe.'];
            }
            $campo_id_nuevo = $campo->getId();
          } else {
            $campo_id_nuevo = $almacenActual->getCampo_id();
          }

          $almacenModificado = new Almacen(null, $nombre, $campo_id_nuevo);

          if ($this->almacenDAO->modificarAlmacen($almacenModificado)) {
            return ['tipo' => 'success', 'mensaje' => 'Almacen modificado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar el almacen'];
          }
        case 'eliminar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del almacen que querés eliminar.'];
          }

          $almacenActual = $this->almacenDAO->getAlmacenByNombre($nombre);

          if (!$almacenActual) {
            return ['tipo' => 'error', 'mensaje' => 'Almacen no existe para modificar'];
          }

          if ($this->almacenDAO->eliminarAlmacen($nombre)) {
            return ['tipo' => 'success', 'mensaje' => 'Almacen eliminado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar el almacen'];
          }
        default:
          return ['tipo' => 'error', 'mensaje' => 'Acción no válida.'];

      }
    }
    return null;
  }
  public function obtenerAlmacenes()
  {
    return $this->almacenDAO->getAllAlmacenes();
  }

  public function getAlmacenByNombre($almacen_nombre)
  {
    return $this->almacenDAO->getAlmacenByNombre($almacen_nombre);
  }
}