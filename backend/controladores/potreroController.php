<?php

require_once __DIR__ . '../../DAOS/potreroDAO.php';
require_once __DIR__ . '../../../backend/modelos/potrero/potreroModelo.php';
require_once __DIR__ . '../../DAOS/campoDAO.php';
require_once __DIR__ . '../../DAOS/categoriaDAO.php';
require_once __DIR__ . '../../DAOS/pasturaDAO.php';

class PotreroController
{
  private $potreroDAO;
  private $campoDAO;

  public function __construct()
  {
    $this->potreroDAO = new PotreroDAO();
    $this->campoDAO = new CampoDAO();
    $this->categoriaDAO = new CategoriaDAO();
    $this->pasturaDAO = new PasturaDAO();
  }

  public function procesarFormularios()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $accion = $_POST['accion'] ?? '';
      $nombre = trim($_POST['nombre'] ?? '');
      $superficie = trim($_POST['superficie'] ?? '');
      $pastura_nombre = trim($_POST['pastura_nombre'] ?? '');
      $categoria_nombre = trim($_POST['categoria_nombre'] ?? '');
      $campo_nombre = trim($_POST['campo_nombre'] ?? '');
      switch ($accion) {
        case 'registrar':
          if (empty($nombre) || empty($superficie) || empty($pastura_nombre) || empty($categoria_nombre) || empty($campo_nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Complete todos los campos.'];
          }

          $pastura = $this->pasturaDAO->getPasturaByNombre($pastura_nombre);
          if (!$pastura) {
            return ['tipo' => 'error', 'mensaje' => 'La pastura seleccionada no existe.'];
          }

          $pastura_id = $pastura->getId();

          $categoria = $this->categoriaDAO->getCategoriaByNombre($categoria_nombre);
          if (!$categoria) {
            return ['tipo' => 'error', 'mensaje' => 'La categoria seleccionada no existe.'];
          }

          $categoria_id = $categoria->getId();

          $campo = $this->campoDAO->getCampoByNombre($campo_nombre);
          if (!$campo) {
            return ['tipo' => 'error', 'mensaje' => 'El campo seleccionado no existe.'];
          }

          $campo_id = $campo->getId();

          $potrero = new Potrero(null, $nombre, $superficie, $pastura_id, $categoria_id, $campo_id);

          if ($this->potreroDAO->registrarPotrero($potrero)) {
            return ['tipo' => 'success', 'mensaje' => 'Potrero registrado con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error: ya existe un potrero con ese nombre.'];
          }
        case 'modificar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del potrero que querés modificar.'];
          }

          $potreroActual = $this->potreroDAO->getPotreroByNombre($nombre);
          if (!$potreroActual) {
            return ['tipo' => 'error', 'mensaje' => 'Potrero no existe para modificar'];
          }

          $superficieNueva = $superficie !== '' ? $superficie : $potreroActual->getSuperficie();

          if (!empty($pastura_nombre)) {
            $pastura = $this->pasturaDAO->getPasturaByNombre($pastura_nombre);
            if (!$pastura) {
              return ['tipo' => 'error', 'mensaje' => 'Pastura no existe'];
            }
            $pastura_id_nuevo = $pastura->getId();
          } else {
            $pastura_id_nuevo = $potreroActual->getPastura_id();
          }

          if (!empty($categoria_nombre)) {
            $categoria = $this->categoriaDAO->getCategoriaByNombre($categoria_nombre);
            if (!$categoria) {
              return ['tipo' => 'error', 'mensaje' => 'Categoria no existe'];
            }
            $categoria_id_nuevo = $categoria->getId();
          } else {
            $categoria_id_nuevo = $potreroActual->getCategoria_id();
          }

          if (!empty($campo_nombre)) {
            $campoDAO = new CampoDAO();
            $campo = $campoDAO->getCampoByNombre($campo_nombre);
            if (!$campo) {
              return ['tipo' => 'error', 'mensaje' => 'Campo no existe'];
            }
            $campo_id_nuevo = $campo->getId();
          } else {
            $campo_id_nuevo = $potreroActual->getCampo_id();
          }

          $potreroModificado = new Potrero(null, $nombre, $superficieNueva, $pastura_id_nuevo, $categoria_id_nuevo, $campo_id_nuevo);

          if ($this->potreroDAO->modificarPotrero($potreroModificado)) {
            return ['tipo' => 'success', 'mensaje' => 'Potrero modificado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar el potrero'];
          }
        case 'eliminar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del potrero que querés eliminar.'];
          }

          $potreroActual = $this->potreroDAO->getPotreroByNombre($nombre);

          if (!$potreroActual) {
            return ['tipo' => 'error', 'mensaje' => 'Potrero no existe para modificar'];
          }

          if ($this->potreroDAO->eliminarPotrero($nombre)) {
            return ['tipo' => 'success', 'mensaje' => 'Potrero eliminado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar el potrero'];
          }
        default:
          return ['tipo' => 'error', 'mensaje' => 'Acción no válida.'];
      }
    }
    return null;
  }

  public function obtenerPotreros()
  {
    return $this->potreroDAO->getAllPotreros();
  }
}
























