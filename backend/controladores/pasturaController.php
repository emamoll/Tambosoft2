<?php

require_once __DIR__ . '../../DAOS/pasturaDAO.php';
require_once __DIR__ . '../../modelos/pastura/pasturaModelo.php';

class PasturaController
{
  private $pasturaDAO;

  public function __construct()
  {
    $this->pasturaDAO = new PasturaDAO();
  }

  public function procesarFormularios()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $accion = $_POST['accion'] ?? '';
      $nombre = trim($_POST['nombre'] ?? '');
      switch ($accion) {
        case 'registrar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'El nombre de la pastura es obligatorio.'];
          }

          $pastura = new Pastura(null, $nombre);
          if ($this->pasturaDAO->registrarPastura($pastura)) {
            return ['tipo' => 'success', 'mensaje' => 'Pastura registrada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Ya existe una pastura con ese nombre.'];
          }
        case 'modificar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Seleccione la pastura que desea modificar.'];
          }

          $pastura = new Pastura(null, $nombre);
          if ($this->pasturaDAO->modificarPastura($pastura)) {
            return ['tipo' => 'success', 'mensaje' => 'Pastura modificada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar la pastura.'];
          }
        case 'eliminar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Seleccione la pastura que desea eliminar.'];
          }
          if ($this->pasturaDAO->eliminarPastura($nombre)) {
            return ['tipo' => 'success', 'mensaje' => 'Pastura eliminada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar la pastura.'];
          }
      }
    }
    return null;
  }
  public function obtenerPasturas()
  {
    return $this->pasturaDAO->getAllPasturas();
  }
}