<?php

require_once __DIR__ . '../../DAOS/alimentoDAO.php';
require_once __DIR__ . '../../modelos/alimento/alimentoModelo.php';

class AlimentoController
{
  private $alimentoDAO;

  public function __construct()
  {
    $this->alimentoDAO = new AlimentoDAO();
  }

  public function procesarFormularios()
  {
    $mensaje = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $accion = $_POST['accion'] ?? '';
      $nombre = trim($_POST['nombre'] ?? '');
      $precio = trim($_POST['precio'] ?? '');
      $descripcion = trim($_POST['descripcion'] ?? '');
      $peso = trim($_POST['peso'] ?? '');
      $fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '');
      $fecha_vencimiento = trim($fecha_vencimiento);

      switch ($accion) {
        case 'registrar':
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

          $alimento = new Alimento(null, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);

          if ($this->alimentoDAO->registrarAlimento($alimento)) {
            return ['tipo' => 'success', 'mensaje' => 'Alimento registrado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error: ya existe un alimento con ese nombre'];
          }
        case 'modificar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del alimento que querés modificar.'];
          }

          $alimentoActual = $this->alimentoDAO->getAlimentoByNombre($nombre);
          if (!$alimentoActual) {
            return ['tipo' => 'error', 'mensaje' => 'Alimento no existe para modificar'];
          }

          $precioNuevo = $precio !== '' ? $precio : $alimentoActual->getPrecio();
          $descripcionNueva = $descripcion !== '' ? $descripcion : $alimentoActual->getDescripcion();
          $pesoNuevo = $peso !== '' ? $peso : $alimentoActual->getPeso();
          $fechaNueva = $fecha_vencimiento !== '' ? $fecha_vencimiento : $alimentoActual->getFecha_vencimiento();

          if (!is_numeric($precioNuevo) || $precioNuevo <= 0) {
            return ['tipo' => 'error', 'mensaje' => 'El precio debe ser un número positivo.'];
          }

          if (!is_numeric($pesoNuevo) || $pesoNuevo <= 0) {
            return ['tipo' => 'error', 'mensaje' => 'El peso debe ser un número positivo.'];
          }

          if ($fechaNueva < date('Y-m-d')) {
            return ['tipo' => 'error', 'mensaje' => 'La nueva fecha de vencimiento no puede ser pasada.'];
          }

          $alimentoModificado = new Alimento(null, $nombre, $precioNuevo, $descripcionNueva, $pesoNuevo, $fechaNueva);

          if ($this->alimentoDAO->modificarAlimento($alimentoModificado)) {
            return ['tipo' => 'success', 'mensaje' => 'Alimento modificado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar el alimento'];
          }
        case 'eliminar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del alimento que querés eliminar.'];
          }

          $alimentoActual = $this->alimentoDAO->getAlimentoByNombre($nombre);

          if (!$alimentoActual) {
            return ['tipo' => 'error', 'mensaje' => 'Alimento no existe para modificar'];
          }

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

  public function obtenerAlimentos()
  {
    return $this->alimentoDAO->getAllAlimentos();
  }

  public function obtenerAlimentosPorIds(array $ids)
  {
    return $this->alimentoDAO->getAlimentosPorIds($ids);
  }

}