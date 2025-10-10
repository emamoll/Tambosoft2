<?php

// Incluye los archivos necesarios para las operaciones con la base de datos y los modelos.
require_once __DIR__ . '../../DAOS/potreroDAO.php';
require_once __DIR__ . '../../../backend/modelos/potrero/potreroModelo.php';
require_once __DIR__ . '../../DAOS/campoDAO.php';
require_once __DIR__ . '../../DAOS/categoriaDAO.php';
require_once __DIR__ . '../../DAOS/pasturaDAO.php';

/**
 * Clase controladora para gestionar las operaciones relacionadas con los potreros.
 */
class PotreroController
{
  // Propiedades privadas para las instancias de las clases DAO.
  private $potreroDAO;
  private $campoDAO;
  private $categoriaDAO;
  private $pasturaDAO;

  /**
   * Constructor de la clase.
   * Inicializa las propiedades DAO.
   */
  public function __construct()
  {
    $this->potreroDAO = new PotreroDAO();
    $this->campoDAO = new CampoDAO();
    $this->categoriaDAO = new CategoriaDAO();
    $this->pasturaDAO = new PasturaDAO();
  }

  /**
   * Procesa los formularios de registro, modificación y eliminación de potreros.
   *
   * @return array|null Un array con el tipo y mensaje de la respuesta,
   * o null si la petición no es de tipo POST.
   */
  public function procesarFormularios()
  {
    // Verifica si la petición es de tipo POST.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Obtiene la acción y los datos del formulario.
      $accion = $_POST['accion'] ?? '';
      $nombre = trim($_POST['nombre'] ?? '');
      $superficie = trim($_POST['superficie'] ?? '');
      $pastura_nombre = trim($_POST['pastura_nombre'] ?? '');
      $categoria_nombre = trim($_POST['categoria_nombre'] ?? '');
      $campo_nombre = trim($_POST['campo_nombre'] ?? '');

      // Evalúa la acción.
      switch ($accion) {
        case 'registrar':
          // Valida que todos los campos obligatorios estén completos.
          if (empty($nombre) || empty($superficie) || empty($pastura_nombre) || empty($categoria_nombre) || empty($campo_nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Complete todos los campos.'];
          }

          // Busca la pastura, categoría y campo por nombre para obtener sus IDs.
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

          // Crea el objeto Potrero y lo registra.
          $potrero = new Potrero(null, $nombre, $superficie, $pastura_id, $categoria_id, $campo_id);
          if ($this->potreroDAO->registrarPotrero($potrero)) {
            return ['tipo' => 'success', 'mensaje' => 'Potrero registrado con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error: ya existe un potrero con ese nombre.'];
          }
        case 'modificar':
          // Valida que el nombre del potrero a modificar no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del potrero que querés modificar.'];
          }

          // Obtiene el potrero actual.
          $potreroActual = $this->potreroDAO->getPotreroByNombre($nombre);
          if (!$potreroActual) {
            return ['tipo' => 'error', 'mensaje' => 'Potrero no existe para modificar'];
          }

          // Determina los nuevos valores para los campos.
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

          // Crea el objeto Potrero con los datos modificados y lo actualiza.
          $potreroModificado = new Potrero(null, $nombre, $superficieNueva, $pastura_id_nuevo, $categoria_id_nuevo, $campo_id_nuevo);
          if ($this->potreroDAO->modificarPotrero($potreroModificado)) {
            return ['tipo' => 'success', 'mensaje' => 'Potrero modificado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar el potrero'];
          }
        case 'eliminar':
          // Valida que el nombre del potrero a eliminar no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del potrero que querés eliminar.'];
          }
          // Verifica que el potrero exista.
          $potreroActual = $this->potreroDAO->getPotreroByNombre($nombre);
          if (!$potreroActual) {
            return ['tipo' => 'error', 'mensaje' => 'Potrero no existe para modificar'];
          }
          // Intenta eliminar el potrero.
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

  /**
   * Obtiene todos los potreros de la base de datos.
   *
   * @return array Un array de objetos Potrero.
   */
  public function obtenerPotreros()
  {
    return $this->potreroDAO->getAllPotreros();
  }

  public function getPotreroByCampoId($campo_id)
  {
    return $this->potreroDAO->getPotreroByCampo($campo_id);
  }
}
























