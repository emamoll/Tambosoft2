<?php

require_once __DIR__ . '../../DAOS/campoDAO.php';
require_once __DIR__ . '../../modelos/campo/campoModelo.php';

class CampoController
{
  private $campoDAO;

  public function __construct()
  {
    $this->campoDAO = new CampoDAO();
  }

  public function procesarFormularios()
  {
    $mensaje = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $accion = $_POST['accion'] ?? '';
      $nombre = $_POST['nombre'] ?? '';
      $ubicacion = $_POST['ubicacion'] ?? '';

      $campo = new Campo(null, $nombre, $ubicacion);

      switch ($accion) {
        case 'registrar':
          if (empty($nombre) || empty($ubicacion)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, completá todos los campos para registrar.'];
          }

          $campo = new Campo(null, $nombre, $ubicacion);

          if ($this->campoDAO->registrarCampo($campo)) {
            return ['tipo' => 'success', 'mensaje' => 'Campo y almacen registrados correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error: ya existe un campo con ese nombre'];
          }
        case 'modificar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del campo que querés modificar.'];
          }

          $campoActual = $this->campoDAO->getCampoByNombre($nombre);
          if (!$campoActual) {
            return ['tipo' => 'error', 'mensaje' => 'Campo no existe para modificar'];
          }

          $ubicacionNueva = $ubicacion !== '' ? $ubicacion : $campoActual->getUbicacion();

          $campoModificado = new Campo(null, $nombre, $ubicacionNueva);

          if ($this->campoDAO->modificarCampo($campoModificado)) {
            return ['tipo' => 'success', 'mensaje' => 'Campo modificado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar el campo'];
          }
        case 'eliminar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del campo que querés eliminar.'];
          }

          if ($this->campoDAO->eliminarCampo($nombre)) {
            return ['tipo' => 'success', 'mensaje' => 'Campo eliminado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar el campo'];
          }
      }
    }
    return null;
  }

  public function obtenerCampos()
  {
    return $this->campoDAO->getAllCampos();
  }

  public function getCampoById($id)
  {
    return $this->campoDAO->getCampoById($id);
  }
}
