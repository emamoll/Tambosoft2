<?php

// Incluye los archivos necesarios para las operaciones con la base de datos y los modelos.
require_once __DIR__ . '../../DAOS/pasturaDAO.php';
require_once __DIR__ . '../../modelos/pastura/pasturaModelo.php';

/**
 * Clase controladora para gestionar las operaciones relacionadas con las pasturas.
 */
class PasturaController
{
  // Propiedad privada para la instancia de PasturaDAO.
  private $pasturaDAO;

  /**
   * Constructor de la clase.
   * Inicializa la propiedad `$pasturaDAO`.
   */
  public function __construct()
  {
    $this->pasturaDAO = new PasturaDAO();
  }

  /**
   * Procesa los formularios de registro, modificación y eliminación de pasturas.
   *
   * @return array|null Un array con el tipo y mensaje de la respuesta,
   * o null si la petición no es de tipo POST.
   */
  public function procesarFormularios()
  {
    // Verifica si la petición es de tipo POST.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Obtiene la acción y el nombre de la pastura.
      $accion = $_POST['accion'] ?? '';
      $nombre = trim($_POST['nombre'] ?? '');
      
      // Evalúa la acción.
      switch ($accion) {
        case 'registrar':
          // Valida que el nombre no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'El nombre de la pastura es obligatorio.'];
          }
          // Intenta registrar la pastura.
          $pastura = new Pastura(null, $nombre);
          if ($this->pasturaDAO->registrarPastura($pastura)) {
            return ['tipo' => 'success', 'mensaje' => 'Pastura registrada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Ya existe una pastura con ese nombre.'];
          }
        case 'modificar':
          // Valida que el nombre no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Seleccione la pastura que desea modificar.'];
          }
          // Intenta modificar la pastura.
          $pastura = new Pastura(null, $nombre);
          if ($this->pasturaDAO->modificarPastura($pastura)) {
            return ['tipo' => 'success', 'mensaje' => 'Pastura modificada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar la pastura.'];
          }
        case 'eliminar':
          // Valida que el nombre no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Seleccione la pastura que desea eliminar.'];
          }
          // Intenta eliminar la pastura.
          if ($this->pasturaDAO->eliminarPastura($nombre)) {
            return ['tipo' => 'success', 'mensaje' => 'Pastura eliminada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar la pastura.'];
          }
      }
    }
    return null;
  }

  /**
   * Obtiene todas las pasturas de la base de datos.
   *
   * @return array Un array de objetos Pastura.
   */
  public function obtenerPasturas()
  {
    return $this->pasturaDAO->getAllPasturas();
  }
}