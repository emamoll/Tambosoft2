<?php

require_once __DIR__ . '../../DAOS/ordenDAO.php';
require_once __DIR__ . '../../DAOS/estadoDAO.php';
require_once __DIR__ . '../../DAOS/alimentoDAO.php';
require_once __DIR__ . '../../DAOS/almacenDAO.php';
require_once __DIR__ . '../../DAOS/stock_almacenDAO.php';
require_once __DIR__ . '../../controladores/stock_almacenController.php';

class OrdenController
{
  private $ordenDAO;
  private $stock_almacenController;
  private $alimentoDAO;
  private $almacenDAO;
  private $estadoDAO;

  public function __construct()
  {
    $this->ordenDAO = new OrdenDAO();
    $this->stock_almacenController = new Stock_almacenController();
    $this->alimentoDAO = new AlimentoDAO();
    $this->almacenDAO = new AlmacenDAO();
    $this->estadoDAO = new EstadoDAO();
  }


  public function procesarFormulario()
  {
    // Este método procesa las acciones de crear/modificar/enviar/cancelar.
    // Los filtros se procesan en procesarFiltro().
    // NO debe haber lógica de 'limpiar_filtros' o 'aplicar_filtros' aquí.

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return '';
    }

    $accionOrden = $_POST['accionOrden'] ?? '';
    if (!in_array($accionOrden, ['crear', 'modificar', 'enviar', 'preparar', 'trasladar', 'entregar', 'cancelar'])) {
      return ''; // Evita procesar si no es una acción válida
    }
    $mensaje = '';

    // No necesitamos el if ($_SERVER['REQUEST_METHOD'] === 'POST') aquí, ya se verificó al principio
    $id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_POST['orden_id']) ? intval($_POST['orden_id']) : null);
    $almacenId = trim($_POST['almacen_id'] ?? '');
    $alimentoId = trim($_POST['alimento_id'] ?? '');
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
        if (empty($almacenId) || empty($alimentoId) || empty($cantidad)) {
          return ['tipo' => 'error', 'mensaje' => 'Por favor, completá todos los campos para crear la orden.'];
        }

        if (!is_numeric($cantidad) || $cantidad <= 0) {
          return ['tipo' => 'error', 'mensaje' => 'La cantidad debe ser un número positivo.'];
        }

        $almacenDAO = new AlmacenDAO();
        $almacen = $almacenDAO->getAlmacenById($almacenId);

        if (!$almacen) {
          return ['tipo' => 'error', 'mensaje' => 'El almacen seleccionado no existe.'];
        }

        $almacen_id = $almacen->getId();

        $alimentoDAO = new AlimentoDAO();
        $alimento = $alimentoDAO->getAlimentoById($alimentoId);

        if (!$alimento) {
          return ['tipo' => 'error', 'mensaje' => 'El alimento seleccionado no existe.'];
        }

        $alimento_id = $alimento->getId();

        $stockDisponible = $this->stock_almacenController->getStockByAlimentoInAlmacen($almacen_id, $alimento_id);
        if ($stockDisponible < $cantidad) {
          return ['tipo' => 'error', 'mensaje' => 'No hay suficiente stock disponible para el alimento seleccionado en este almacén. Stock actual: ' . $stockDisponible];
        }

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
          if ($this->stock_almacenController->reducirStock($almacen_id, $alimento_id, $cantidad)) {
            return ['tipo' => 'success', 'mensaje' => 'Orden registrada correctamente.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Orden registrada, pero hubo un error al descontar el stock.'];
          }
        } else {
          return ['tipo' => 'error', 'mensaje' => 'Error al registrar la orden'];
        }
      case 'modificar':
        $ordenActual = $this->ordenDAO->getOrdenById($id);
        if (!$ordenActual) {
          return ['tipo' => 'error', 'mensaje' => 'La orden no existe para modificar'];
        }

        if (!empty($almacenId)) {
          $almacenDAO = new AlmacenDAO();
          $almacen = $almacenDAO->getAlmacenById($almacenId);
          if (!$almacen) {
            return ['tipo' => 'error', 'mensaje' => 'El almacen no existe'];
          }
          $almacen_id_nuevo = $almacen->getId();
        } else {
          $almacen_id_nuevo = $ordenActual->getAlmacen_id();
        }

        if (!empty($alimentoId)) {
          $alimentoDAO = new AlimentoDAO();
          $alimento = $alimentoDAO->getAlimentoById($alimentoId);
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

        $cantidadVieja = $ordenActual->getCantidad();
        $diferenciaDeStock = $cantidadNueva - $cantidadVieja;

        $stockDisponible = $this->stock_almacenController->getStockByAlimentoInAlmacen($almacen_id_nuevo, $alimento_id_nuevo);

        if ($diferenciaDeStock > 0 && $stockDisponible < $diferenciaDeStock) {
          return ['tipo' => 'error', 'mensaje' => 'No hay suficiente stock para aumentar la cantidad de la orden. Stock actual: ' . $stockDisponible];
        }

        $fechaNueva = date('Y-m-d');
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
          if ($diferenciaDeStock != 0) {
            $stockModificado = false;
            if ($diferenciaDeStock > 0) {
              $stockModificado = $this->stock_almacenController->reducirStock($almacen_id_nuevo, $alimento_id_nuevo, $diferenciaDeStock);
            } else {
              $stockModificado = $this->stock_almacenController->actualizarStock_almacen($almacen_id_nuevo, $alimento_id_nuevo, abs($diferenciaDeStock));
            }

            if (!$stockModificado) {
              return ['tipo' => 'error', 'mensaje' => 'Orden modificada, pero hubo un error al ajustar el stock.'];
            }
          }
          return ['tipo' => 'success', 'mensaje' => 'Orden modificada y stock ajustado correctamente.'];
        } else {
          return ['tipo' => 'error', 'mensaje' => 'Error al modificar la orden.'];
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
        $estadoNuevo_id = 6;


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

  public function obtenerOrdenes()
  {
    $ordenes = $this->ordenDAO->getAllOrdenes();
    return $this->enrichOrdenesWithNames($ordenes);
  }

  public function obtenerOrdenPorId($id)
  {
    return $this->ordenDAO->getOrdenById($id);
  }

  public function procesarFiltro()
  {
    $ordenes = [];

    // Lógica para aplicar el patrón PRG aquí:
    // Si la solicitud es POST y contiene 'aplicar_filtros' o 'limpiar_filtros'
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['aplicar_filtros']) || isset($_POST['limpiar_filtros']))) {
      // Redirigir a la misma página, pasando los parámetros de filtro vía GET
      // Construir la URL de redirección
      $redirectUrl = $_SERVER['PHP_SELF'];
      $queryParams = [];

      if (isset($_POST['limpiar_filtros'])) {
        // No se añaden parámetros, la URL ya está limpia
      } elseif (isset($_POST['aplicar_filtros'])) {
        // Añadir los filtros seleccionados a los parámetros de la URL
        if (!empty($_POST['estado_id'])) {
          foreach ($_POST['estado_id'] as $id) {
            $queryParams[] = 'estado_id[]=' . urlencode($id);
          }
        }
        if (!empty($_POST['almacen_id'])) {
          foreach ($_POST['almacen_id'] as $id) {
            $queryParams[] = 'almacen_id[]=' . urlencode($id);
          }
        }
        if (!empty($_POST['alimento_id'])) {
          foreach ($_POST['alimento_id'] as $id) {
            $queryParams[] = 'alimento_id[]=' . urlencode($id);
          }
        }
      }

      if (!empty($queryParams)) {
        $redirectUrl .= '?' . implode('&', $queryParams);
      }

      header('Location: ' . $redirectUrl);
      exit; // Es crucial salir después de la redirección
    }

    // Si la solicitud es GET (ya sea la carga inicial o después de una redirección POST/REDIRECT/GET)
    // Procesar los filtros desde $_GET
    $almacen_id_filtro = $_GET['almacen_id'] ?? [];
    $alimento_id_filtro = $_GET['alimento_id'] ?? [];
    $estado_id_filtro = $_GET['estado_id'] ?? [];

    // Si no hay filtros en GET, o si se redirigió después de limpiar, obtener todas las órdenes
    if (empty($almacen_id_filtro) && empty($alimento_id_filtro) && empty($estado_id_filtro)) {
      $ordenes = $this->ordenDAO->getAllOrdenes();
    } else {
      // Aplicar filtros si están presentes en $_GET
      $ordenes = $this->ordenDAO->getOrdenesFiltradas($almacen_id_filtro, $alimento_id_filtro, $estado_id_filtro);
    }

    // Mover la lógica de "enriquecimiento" a un método separado
    return $this->enrichOrdenesWithNames($ordenes);
  }

  /**
   * Enriquecer una lista de objetos Orden con los nombres de almacén, alimento y estado.
   * @param array $ordenes Array de objetos Orden.
   * @return array Array de objetos Orden con propiedades de nombre añadidas.
   */
  private function enrichOrdenesWithNames(array $ordenes): array
  {
    foreach ($ordenes as $orden) {
      $almacen = $this->almacenDAO->getAlmacenById($orden->getAlmacen_id());
      $orden->almacen_nombre = $almacen ? $almacen->getNombre() : 'Sin almacén';

      $alimento = $this->alimentoDAO->getAlimentoById($orden->getAlimento_id());
      $orden->alimento_nombre = $alimento ? $alimento->getNombre() : 'Sin alimento';
      $orden->alimento_precio = $alimento ? $alimento->getPrecio() : 0;

      $estado = $this->estadoDAO->getEstadoById($orden->getEstado_id());
      $orden->estado_nombre = $estado ? $estado->getNombre() : 'Sin estado';
    }
    return $ordenes;
  }
}