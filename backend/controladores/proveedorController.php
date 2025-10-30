<?php

require_once __DIR__ . '../../DAOS/proveedorDAO.php';

class ProveedorController
{
  private $proveedorDAO;

  /**
   * Constructor de la clase.
   * Inicializa la propiedad `$proveedorDAO`.
   */
  public function __construct()
  {
    $this->proveedorDAO = new ProveedorDAO();
  }

  public function procesarFormularios()
  {
    // Verifica si la petición es de tipo POST.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $accion = $_POST['accion'] ?? '';
      $nombre = trim($_POST['nombre'] ?? '');
      $direccion = trim($_POST['direccion'] ?? '');
      $telefono = preg_replace('/\D/', '', $_POST['telefono'] ?? '');
      $email = trim($_POST['email'] ?? '');

      // Evalúa la acción.
      switch ($accion) {
        case 'registrar':
          // Valida que todos los campos obligatorios estén completos.
          if (empty($nombre) || empty($direccion) || empty($telefono) || empty($email)) {
            return ['tipo' => 'error', 'mensaje' => 'Complete todos los campos.'];
          }

          $existeNombre = $this->proveedorDAO->getProveedorByNombre($nombre);
          $existeEmail = $this->proveedorDAO->getProveedorByEmail($email);

          // Validar teléfono: 7 a 11 dígitos
          if (!preg_match('/^\d{7,11}$/', $telefono)) {
            return [
              'tipo' => 'error',
              'mensaje' => 'El teléfono debe contener entre 7 y 11 dígitos.'
            ];
          }

          // Verificar si el proveedor ya existe por nombre
          $existe = $this->proveedorDAO->getProveedorByNombre($nombre);
          if ($existe) {
            return [
              'tipo' => 'error',
              'mensaje' => 'Ya existe un proveedor con ese nombre.'
            ];
          }

          $proveedor = new Proveedor(null, $nombre, $direccion, $telefono, $email);

          if ($this->proveedorDAO->registrarProveedor($proveedor)) {
            return ['tipo' => 'success', 'mensaje' => 'Proveedor registrado con éxito.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error: ya existe un proveedor con ese nombre.'];
          }
        case 'modificar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del proveedor que querés modificar.'];
          }

          $proveedorActual = $this->proveedorDAO->getProveedorByNombre($nombre);
          if (!$proveedorActual) {
            return ['tipo' => 'error', 'mensaje' => 'Proveedor no existe para modificar'];
          }

          $direccionNueva = $direccion !== '' ? $direccion : $proveedorActual->getDireccion();
          $telefonoNuevo = $telefono !== '' ? $telefono : $proveedorActual->getTelefono();
          $emailNuevo = $email !== '' ? $email : $proveedorActual->getEmail();


          $proveedorModificado = new Proveedor(null, $nombre, $direccionNueva, $telefonoNuevo, $emailNuevo);
          if ($this->proveedorDAO->modificarProveedor($proveedorModificado)) {
            return ['tipo' => 'success', 'mensaje' => 'Proveedor modificado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar el proveedor'];
          }
        case 'eliminar':
          if (empty($nombre)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, ingresá el nombre del proveedor que querés eliminar.'];
          }
          $proveedorActual = $this->proveedorDAO->getProveedorByNombre($nombre);
          if (!$proveedorActual) {
            return ['tipo' => 'error', 'mensaje' => 'Proveedor no existe para modificar'];
          }
          if ($this->proveedorDAO->eliminarProveedor($nombre)) {
            return ['tipo' => 'success', 'mensaje' => 'Proveedor eliminado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar el proveedor'];
          }
        default:
          return ['tipo' => 'error', 'mensaje' => 'Acción no válida.'];
      }
    }
    return null;
  }

  public function obtenerProveedores()
  {
    return $this->proveedorDAO->getAllProveedores();
  }

  public function getProveedorByNombre($nombre)
  {
    return $this->proveedorDAO->getProveedorByNombre($nombre);
  }

  public function getProveedorByEmail($email)
  {
    return $this->proveedorDAO->getProveedorByEmail($email);
  }

}
