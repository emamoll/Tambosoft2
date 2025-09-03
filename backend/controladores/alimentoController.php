<?php

// Incluye los archivos necesarios para las operaciones con la base de datos y los modelos.
require_once __DIR__ . '../../DAOS/alimentoDAO.php';
require_once __DIR__ . '../../modelos/alimento/alimentoModelo.php';

/**
 * Clase controladora para gestionar las operaciones relacionadas con los alimentos.
 */
class AlimentoController
{
  // Propiedad privada para la instancia de AlimentoDAO.
  private $alimentoDAO;

  /**
   * Constructor de la clase.
   * Inicializa la propiedad `$alimentoDAO`.
   */
  public function __construct()
  {
    $this->alimentoDAO = new AlimentoDAO();
  }

  /**
   * Procesa los formularios de registro, modificación y eliminación de alimentos.
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
      $nombre = trim($_POST['nombre'] ?? '');
      $precio = trim($_POST['precio'] ?? '');
      $descripcion = trim($_POST['descripcion'] ?? '');
      $peso = trim($_POST['peso'] ?? '');
      $fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '');
      $fecha_vencimiento = trim($fecha_vencimiento);

      // Evalúa la acción.
      switch ($accion) {
        case 'registrar':
          // Valida que todos los campos obligatorios estén completos y sean válidos.
          if (empty($nombre) || empty($precio) || empty($descripcion) || empty($peso) || empty($fecha_vencimiento)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, completá todos los campos para registrar.'];
          }
          if (!is_numeric($precio) || $precio <= 0) {
            return ['tipo' => 'error', 'mensaje' => 'El precio debe ser un número positivo.'];
          }
          if (!is_numeric($peso) || $peso <= 0) {
            return ['tipo' => 'error', 'mensaje' => 'El peso debe ser un número positivo.'];
          }
          if ($fecha_vencimiento < date('Y-m-d')) {
            return ['tipo' => 'error', 'mensaje' => 'La fecha de vencimiento no puede ser pasada.'];
          }

          // Crea el objeto Alimento y lo registra.
          $alimento = new Alimento(null, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
          if ($this->alimentoDAO->registrarAlimento($alimento)) {
            return ['tipo' => 'success', 'mensaje' => 'Alimento registrado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error: ya existe un alimento con ese nombre'];
          }
        case 'modificar':
          // Valida que el nombre no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del alimento que querés modificar.'];
          }

          // Obtiene el alimento actual.
          $alimentoActual = $this->alimentoDAO->getAlimentoByNombre($nombre);
          if (!$alimentoActual) {
            return ['tipo' => 'error', 'mensaje' => 'Alimento no existe para modificar'];
          }

          // Determina los nuevos valores.
          $precioNuevo = $precio !== '' ? $precio : $alimentoActual->getPrecio();
          $descripcionNueva = $descripcion !== '' ? $descripcion : $alimentoActual->getDescripcion();
          $pesoNuevo = $peso !== '' ? $peso : $alimentoActual->getPeso();
          $fechaNueva = $fecha_vencimiento !== '' ? $fecha_vencimiento : $alimentoActual->getFecha_vencimiento();

          // Valida los nuevos valores.
          if (!is_numeric($precioNuevo) || $precioNuevo <= 0) {
            return ['tipo' => 'error', 'mensaje' => 'El precio debe ser un número positivo.'];
          }
          if (!is_numeric($pesoNuevo) || $pesoNuevo <= 0) {
            return ['tipo' => 'error', 'mensaje' => 'El peso debe ser un número positivo.'];
          }
          if ($fechaNueva < date('Y-m-d')) {
            return ['tipo' => 'error', 'mensaje' => 'La nueva fecha de vencimiento no puede ser pasada.'];
          }

          // Crea el objeto Alimento con los datos modificados y lo actualiza.
          $alimentoModificado = new Alimento(null, $nombre, $precioNuevo, $descripcionNueva, $pesoNuevo, $fechaNueva);
          if ($this->alimentoDAO->modificarAlimento($alimentoModificado)) {
            return ['tipo' => 'success', 'mensaje' => 'Alimento modificado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar el alimento'];
          }
        case 'eliminar':
          // Valida que el nombre no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del alimento que querés eliminar.'];
          }
          // Verifica que el alimento exista.
          $alimentoActual = $this->alimentoDAO->getAlimentoByNombre($nombre);
          if (!$alimentoActual) {
            return ['tipo' => 'error', 'mensaje' => 'Alimento no existe para modificar'];
          }
          // Intenta eliminar el alimento.
          if ($this->alimentoDAO->eliminarAlimento($nombre)) {
            return ['tipo' => 'success', 'mensaje' => 'Alimento eliminado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar el alimento'];
          }
        default:
          return ['tipo' => 'error', 'mensaje' => 'Acción no válida.'];
      }
    }
    return null;
  }

  /**
   * Obtiene todos los alimentos de la base de datos.
   *
   * @return array Un array de objetos Alimento.
   */
  public function obtenerAlimentos()
  {
    return $this->alimentoDAO->getAllAlimentos();
  }

  /**
   * Obtiene alimentos específicos por un array de IDs.
   *
   * @param array $ids Un array de IDs de alimentos.
   * @return array Un array de objetos Alimento.
   */
  public function obtenerAlimentosPorIds(array $ids)
  {
    return $this->alimentoDAO->getAlimentosPorIds($ids);
  }
}