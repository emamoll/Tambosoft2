<?php

// Incluye los archivos necesarios para las operaciones con la base de datos y los modelos.
require_once __DIR__ . '../../../backend/DAOS/categoriaDAO.php';
require_once __DIR__ . '../../../backend/modelos/categoria/categoriaModelo.php';

/**
 * Clase controladora para gestionar las operaciones relacionadas con las categorías.
 */
class CategoriaController
{
  // Propiedad privada para la instancia de CategoriaDAO.
  private $categoriaDAO;

  /**
   * Constructor de la clase.
   * Inicializa la propiedad `$categoriaDAO`.
   */
  public function __construct()
  {
    $this->categoriaDAO = new CategoriaDAO();
  }

  /**
   * Procesa los formularios de registro, modificación y eliminación de categorías.
   *
   * @return array|null Un array con el tipo y mensaje de la respuesta,
   * o null si la petición no es de tipo POST.
   */
  public function procesarFormularios()
  {
    // Verifica si la petición es de tipo POST.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // Obtiene la acción y el nombre de la categoría.
      $accion = $_POST['accion'] ?? '';
      $nombre = trim($_POST['nombre'] ?? '');
      
      // Evalúa la acción.
      switch ($accion) {
        case 'registrar':
          // Valida que el nombre no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'El nombre de la categoría es obligatorio.'];
          }
          // Intenta registrar la categoría.
          $categoria = new Categoria(null, $nombre);
          if ($this->categoriaDAO->registrarCategoria($categoria)) {
            return ['tipo' => 'success', 'mensaje' => 'Categoría registrada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Ya existe una categoria con ese nombre.'];
          }
        case 'modificar':
          // Valida que el nombre no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Seleccione la pastura que desea modificar.'];
          }
          // Intenta modificar la categoría.
          $categoria = new Categoria(null, $nombre);
          if ($this->categoriaDAO->modificarCategoria($categoria)) {
            return ['tipo' => 'success', 'mensaje' => 'Categoría modificada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar la categoría.'];
          }
        case 'eliminar':
          // Valida que el nombre no esté vacío.
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Selecciona la categoria que desea eliminar.'];
          }
          // Intenta eliminar la categoría.
          if ($this->categoriaDAO->eliminarCategoria($nombre)) {
            return ['tipo' => 'success', 'mensaje' => 'Categoría eliminada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar la categoría.'];
          }
      }
    }
    return null;
  }

  /**
   * Obtiene todas las categorías de la base de datos.
   *
   * @return array Un array de objetos Categoria.
   */
  public function obtenerCategorias()
  {
    return $this->categoriaDAO->getAllCategorias();
  }
}
