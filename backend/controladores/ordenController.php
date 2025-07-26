<?php

require_once __DIR__ . '../../DAOS/ordenDAO.php';
require_once __DIR__ . '../../DAOS/estadoDAO.php';
require_once __DIR__ . '../../DAOS/alimentoDAO.php';
require_once __DIR__ . '../../DAOS/almacenDAO.php';

class OrdenController
{
  private $ordenDAO;

  public function __construct()
  {
    $this->ordenDAO = new OrdenDAO();
  }

  public function procesarFormulario()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return '';
    }

    $accionOrden = $_POST['accionOrden'] ?? '';
    if (!in_array($accionOrden, ['crear', 'modificar', 'enviar', 'preparar', 'trasladar', 'entregar', 'cancelar'])) {
      return ''; // Evita procesar si no es una acción válida
    }
    $mensaje = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $accionOrden = $_POST['accionOrden'] ?? '';
      $id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_POST['orden_id']) ? intval($_POST['orden_id']) : null);
      $almacen_nombre = trim($_POST['almacen_nombre'] ?? '');
      $alimento_nombre = trim($_POST['alimento_nombre'] ?? '');
      $cantidad = trim($_POST['cantidad'] ?? '');
      $fecha_creacion = date('Y-m-d');
      date_default_timezone_set('America/Argentina/Buenos_Aires');
      $hora_creacion = date('H:i');
      $fecha_actualizacion = date('Y-m-d');
      date_default_timezone_set('America/Argentina/Buenos_Aires');
      $hora_actualizacion = date('H:i');
      $estado_id = 1;

      switch ($accionOrden) {
        case 'crear':
          if (empty($almacen_nombre) || empty($alimento_nombre) || empty($cantidad)) {
            return ['tipo' => 'error', 'mensaje' => 'Por favor, completá todos los campos para crear la orden.'];
          }

          if (!is_numeric($cantidad) || $cantidad <= 0) {
            return ['tipo' => 'error', 'mensaje' => 'La cantidad debe ser un número positivo.'];
          }

          $almacenDAO = new AlmacenDAO();
          $almacen = $almacenDAO->getAlmacenByNombre($almacen_nombre);

          if (!$almacen) {
            return ['tipo' => 'error', 'mensaje' => 'El almacen seleccionado no existe.'];
          }

          $almacen_id = $almacen->getId();

          $alimentoDAO = new AlimentoDAO();
          $alimento = $alimentoDAO->getAlimentoByNombre($alimento_nombre);

          if (!$alimento) {
            return ['tipo' => 'error', 'mensaje' => 'El alimento seleccionado no existe.'];
          }

          $alimento_id = $alimento->getId();

          $orden = new Orden(
            null,
            $almacen_id,
            $alimento_id,
            $cantidad,
            $fecha_creacion,
            $hora_creacion,
            $fecha_actualizacion,
            $hora_actualizacion,
            $estado_id
          );

          if ($this->ordenDAO->registrarOrden($orden)) {
            return ['tipo' => 'success', 'mensaje' => 'Orden registrado correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al registrar la orden'];
          }
        case 'modificar':
          $ordenActual = $this->ordenDAO->getOrdenById($id);
          if (!$ordenActual) {
            return ['tipo' => 'error', 'mensaje' => 'La orden no existe para modificar'];
          }

          if (!empty($almacen_nombre)) {
            $almacenDAO = new AlmacenDAO();
            $almacen = $almacenDAO->getAlmacenByNombre($almacen_nombre);
            if (!$almacen) {
              return ['tipo' => 'error', 'mensaje' => 'El almacen no existe'];
            }
            $almacen_id_nuevo = $almacen->getId();
          } else {
            $almacen_id_nuevo = $ordenActual->getAlmacen_id();
          }

          if (!empty($alimento_nombre)) {
            $alimentoDAO = new AlimentoDAO();
            $alimento = $alimentoDAO->getAlimentoByNombre($alimento_nombre);
            if (!$alimento) {
              return ['tipo' => 'error', 'mensaje' => 'El alimento no existe'];
            }
            $alimento_id_nuevo = $alimento->getId();
          } else {
            $alimento_id_nuevo = $ordenActual->getAlimento_id();
          }

          $cantidadNueva = $cantidad !== '' ? $cantidad : $ordenActual->getCantidad();

          if (!is_numeric($cantidadNueva) || $cantidadNueva <= 0) {
            return ['tipo' => 'error', 'mensaje' => 'La cantidad debe ser un número positivo.'];
          }

          $fechaNueva = date('d/m/Y');
          $horaNueva = date('H:i');

          $ordenModificada = new Orden(
            $id,
            $almacen_id_nuevo,
            $alimento_id_nuevo,
            $cantidadNueva,
            $ordenActual->getFecha_creacion(),
            $ordenActual->getHora_creacion(),
            $fechaNueva,
            $horaNueva,
            $estado_id
          );

          if ($this->ordenDAO->modificarOrden($ordenModificada)) {
            return ['tipo' => 'success', 'mensaje' => 'Orden modificada correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al modificar la orden'];
          }
        case 'eliminar':
          $ordenActual = $this->ordenDAO->getOrdenById($id);
          if (!$ordenActual) {
            return ['tipo' => 'error', 'mensaje' => 'La orden no existe para eliminar'];
          }

          if ($this->ordenDAO->eliminarOrden($id)) {
            return ['tipo' => 'success', 'mensaje' => 'Orden eliminada correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al eliminar la orden'];
          }
        case 'enviar':
          $ordenActual = $this->ordenDAO->getOrdenById($id);
          if (!$ordenActual) {
            return ['tipo' => 'error', 'mensaje' => 'La orden no existe para enviar'];
          }

          // Solo se puede enviar si está pendiente
          if ($ordenActual->getEstado_id() !== 1) {
            return ['tipo' => 'error', 'mensaje' => 'Solo se pueden enviar órdenes pendientes'];
          }

          $fechaNueva = date('Y-m-d');
          $horaNueva = date('H:i');
          $estadoNuevo_id = 2;

          $ordenEnviada = new Orden(
            $id,
            $ordenActual->getAlmacen_id(),
            $ordenActual->getAlimento_id(),
            $ordenActual->getCantidad(),
            $ordenActual->getFecha_creacion(),
            $ordenActual->getHora_creacion(),
            $fechaNueva,
            $horaNueva,
            $estadoNuevo_id
          );

          if ($this->ordenDAO->modificarOrden($ordenEnviada)) {
            return ['tipo' => 'success', 'mensaje' => 'Orden enviada correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al enviar la orden'];
          }
        case 'preparar':
          $ordenActual = $this->ordenDAO->getOrdenById($id);
          if (!$ordenActual) {
            return ['tipo' => 'error', 'mensaje' => 'La orden no existe para preparar'];
          }

          // Solo se puede enviar si está pendiente
          if ($ordenActual->getEstado_id() !== 2) {
            return ['tipo' => 'error', 'mensaje' => 'Solo se pueden preparar órdenes enviadas'];
          }

          $fechaNueva = date('Y-m-d');
          $horaNueva = date('H:i');
          $estadoNuevo_id = 3;

          $ordenEnviada = new Orden(
            $id,
            $ordenActual->getAlmacen_id(),
            $ordenActual->getAlimento_id(),
            $ordenActual->getCantidad(),
            $ordenActual->getFecha_creacion(),
            $ordenActual->getHora_creacion(),
            $fechaNueva,
            $horaNueva,
            $estadoNuevo_id
          );

          if ($this->ordenDAO->modificarOrden($ordenEnviada)) {
            return ['tipo' => 'success', 'mensaje' => 'Orden preparada correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al preparar la orden'];
          }
        case 'trasladar':
          $ordenActual = $this->ordenDAO->getOrdenById($id);
          if (!$ordenActual) {
            return ['tipo' => 'error', 'mensaje' => 'La orden no existe para trasladar'];
          }

          // Solo se puede enviar si está pendiente
          if ($ordenActual->getEstado_id() !== 3) {
            return ['tipo' => 'error', 'mensaje' => 'Solo se pueden trasladar órdenes preparadas'];
          }

          $fechaNueva = date('Y-m-d');
          $horaNueva = date('H:i');
          $estadoNuevo_id = 4;

          $ordenEnviada = new Orden(
            $id,
            $ordenActual->getAlmacen_id(),
            $ordenActual->getAlimento_id(),
            $ordenActual->getCantidad(),
            $ordenActual->getFecha_creacion(),
            $ordenActual->getHora_creacion(),
            $fechaNueva,
            $horaNueva,
            $estadoNuevo_id
          );

          if ($this->ordenDAO->modificarOrden($ordenEnviada)) {
            return ['tipo' => 'success', 'mensaje' => 'Orden trasladada correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al trasladar la orden'];
          }
        case 'entregar':
          $ordenActual = $this->ordenDAO->getOrdenById($id);
          if (!$ordenActual) {
            return ['tipo' => 'error', 'mensaje' => 'La orden no existe para entregar'];
          }

          // Solo se puede enviar si está pendiente
          if ($ordenActual->getEstado_id() !== 4) {
            return ['tipo' => 'error', 'mensaje' => 'Solo se pueden entregar órdenes trasladadas'];
          }

          $fechaNueva = date('Y-m-d');
          $horaNueva = date('H:i');
          $estadoNuevo_id = 5;

          $ordenEnviada = new Orden(
            $id,
            $ordenActual->getAlmacen_id(),
            $ordenActual->getAlimento_id(),
            $ordenActual->getCantidad(),
            $ordenActual->getFecha_creacion(),
            $ordenActual->getHora_creacion(),
            $fechaNueva,
            $horaNueva,
            $estadoNuevo_id
          );

          if ($this->ordenDAO->modificarOrden($ordenEnviada)) {
            return ['tipo' => 'success', 'mensaje' => 'Orden entregada correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al entregar la orden'];
          }
        case 'cancelar':
          $ordenActual = $this->ordenDAO->getOrdenById($id);
          if (!$ordenActual) {
            return ['tipo' => 'error', 'mensaje' => 'La orden no existe para cancelar'];
          }

          $fechaNueva = date('Y-m-d');
          $horaNueva = date('H:i');
          $estadoNuevo_id = 5;


          $ordenCancelada = new Orden(
            $id,
            $ordenActual->getAlmacen_id(),
            $ordenActual->getAlimento_id(),
            $ordenActual->getCantidad(),
            $ordenActual->getFecha_creacion(),
            $ordenActual->getHora_creacion(),
            $fechaNueva,
            $horaNueva,
            $estadoNuevo_id
          );

          if ($this->ordenDAO->modificarOrden($ordenCancelada)) {
            return ['tipo' => 'success', 'mensaje' => 'Orden cancelada correctamente'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Error al cancelar la orden'];
          }
        default:
          return ['tipo' => 'error', 'mensaje' => 'Acción no válida.'];
      }
    }
    return null;
  }

  public function obtenerOrdenes()
  {
    return $this->ordenDAO->getAllOrdenes();
  }

  public function obtenerOrdenPorId($id)
  {
    return $this->ordenDAO->getOrdenById($id);
  }

  public function procesarFiltro()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'filtrar') {
      $almacen_id = $_POST['almacen_id'] ?? [];
      $alimento_id = $_POST['alimento_id'] ?? [];
      $estado_id = $_POST['estado_id'] ?? [];

      $ordenes = $this->ordenDAO->getOrdenesFiltradas($almacen_id, $alimento_id, $estado_id);

      $almacenDAO = new AlmacenDAO();
      $alimentoDAO = new AlimentoDAO();
      $estadoDAO = new EstadoDAO();

      foreach ($ordenes as $orden) {
        $almacen = $almacenDAO->getAlmacenById($orden->getAlmacen_id());
        $alimento = $alimentoDAO->getAlimentoById($orden->getAlimento_id());
        $estado = $estadoDAO->getEstadoById($orden->getEstado_id());

        $orden->setAlmacen_id($almacen ? $almacen->getNombre() : 'Sin almacen');
        $orden->setAlimento_id($alimento ? $alimento->getNombre() : 'Sin alimento');
        $orden->setEstado_id($estado ? $estado->getNombre() : 'Sin estado');
      }
      return $ordenes;
    }

    return [];
  }
}
