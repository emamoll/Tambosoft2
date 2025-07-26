<?php

require_once __DIR__ . '../../../backend/DAOS/categoriaDAO.php';
require_once __DIR__ . '../../../backend/modelos/categoria/categoriaModelo.php';

class CategoriaController
{
  private $categoriaDAO;

  public function __construct()
  {
    $this->categoriaDAO = new CategoriaDAO();
  }

  public function procesarFormularios()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $accion = $_POST['accion'] ?? '';
      $nombre = trim($_POST['nombre'] ?? '');
      switch ($accion) {
        case 'registrar':
          $nombre = $_POST['nombre'] ?? '';

          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'El nombre de la categoría es obligatorio.'];
          }

          // Constructor modificado
          $categoria = new Categoria(null, $nombre);
          if ($this->categoriaDAO->registrarCategoria($categoria)) {
            return ['tipo' => 'success', 'mensaje' => 'Categoría registrada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Ya existe una categoria con ese nombre.'];
          }
        // case 'modificar':
        //   if (empty($nombre)) {
        //     return ['tipo' => 'error', 'mensaje' => 'ID y nombre de categoría son obligatorios para modificar.'];
        //   }

        //   // Constructor modificado
        //   $categoria = new Categoria($id, $nombre);
        //   if ($this->categoriaDAO->modificarCategoria($categoria)) {
        //     return ['tipo' => 'success', 'mensaje' => 'Categoría modificada con éxito.'];
        //   } else {
        //     return ['tipo' => 'error', 'mensaje' => 'Error al modificar la categoría.'];
        //   }
        case 'eliminar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Selecciona la categoria que desea eliminar.'];
          }
          if ($this->categoriaDAO->eliminarCategoria($nombre)) {
            return ['tipo' => 'success', 'mensaje' => 'Categoría eliminada con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar la categoría.'];
          }
      }
    }
    return null;
  }
  public function obtenerCategorias()
  {
    return $this->categoriaDAO->getAllCategorias();
  }
}

