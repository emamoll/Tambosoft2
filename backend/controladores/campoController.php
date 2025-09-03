<?php

// Incluye los archivos necesarios para las operaciones con la base de datos y los modelos.
require_once __DIR__ . '../../DAOS/campoDAO.php';
require_once __DIR__ . '../../modelos/campo/campoModelo.php';

/**
 * Clase controladora para gestionar las operaciones relacionadas con los campos.
 */
class CampoController
{
  // Propiedad privada para la instancia de CampoDAO.
  private $campoDAO;

  /**
   * Constructor de la clase.
   * Inicializa la propiedad `$campoDAO`.
   */
  public function __construct()
  {
    $this->campoDAO = new CampoDAO();
  }

  /**
   * Procesa los formularios de registro, modificación y eliminación de campos.
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
      $nombre = $_POST['nombre'] ?? '';
      $ubicacion = $_POST['ubicacion'] ?? '';

      $campo = new Campo(null, $nombre, $ubicacion);

      // Evalúa la acción.
      switch ($accion) {
        case 'registrar':
          // Valida que los campos no estén vacíos.
          if (empty($nombre) || empty($ubicacion)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, completá todos los campos para registrar.'];
          }
          // Crea el objeto Campo y lo registra.
          $campo = new Campo(null, $nombre, $ubicacion);
          if ($this->campoDAO->registrarCampo($campo)) {
            return ['tipo' => 'success', 'mensaje' => 'Campo y almacen registrados correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error: ya existe un campo con ese nombre'];
          }
        case 'modificar':
          // Valida que el nombre no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del campo que querés modificar.'];
          }

          // Obtiene el campo actual.
          $campoActual = $this->campoDAO->getCampoByNombre($nombre);
          if (!$campoActual) {
            return ['tipo' => 'error', 'mensaje' => 'Campo no existe para modificar'];
          }

          // Determina el nuevo valor para la ubicación.
          $ubicacionNueva = $ubicacion !== '' ? $ubicacion : $campoActual->getUbicacion();
          // Crea el objeto Campo con los datos modificados y lo actualiza.
          $campoModificado = new Campo(null, $nombre, $ubicacionNueva);
          if ($this->campoDAO->modificarCampo($campoModificado)) {
            return ['tipo' => 'success', 'mensaje' => 'Campo modificado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar el campo'];
          }
        case 'eliminar':
          // Valida que el nombre no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del campo que querés eliminar.'];
          }
          // Intenta eliminar el campo.
          if ($this->campoDAO->eliminarCampo($nombre)) {
            return ['tipo' => 'success', 'mensaje' => 'Campo eliminado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar el campo'];
          }
      }
    }
    return null;
  }

  /**
   * Obtiene todos los campos de la base de datos.
   *
   * @return array Un array de objetos Campo.
   */
  public function obtenerCampos()
  {
    return $this->campoDAO->getAllCampos();
  }

  /**
   * Obtiene un campo por su ID.
   *
   * @param int $id El ID del campo.
   * @return Campo|null El objeto Campo o null si no se encuentra.
   */
  public function getCampoById($id)
  {
    return $this->campoDAO->getCampoById($id);
  }
}
