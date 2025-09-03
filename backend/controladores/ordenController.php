<?php

// Incluye todos los archivos DAO y modelos necesarios para el controlador de órdenes.
require_once __DIR__ . '../../DAOS/ordenDAO.php';
require_once __DIR__ . '../../DAOS/estadoDAO.php';
require_once __DIR__ . '../../DAOS/alimentoDAO.php';
require_once __DIR__ . '../../DAOS/almacenDAO.php';
require_once __DIR__ . '../../DAOS/stock_almacenDAO.php';
require_once __DIR__ . '../../modelos/orden_cancelada/orden_canceladaModelo.php';
require_once __DIR__ . '../../DAOS/orden_canceladaDAO.php';
require_once __DIR__ . '../../controladores/stock_almacenController.php';

/**
 * Clase controladora para gestionar las operaciones de las órdenes.
 * Incluye la creación, modificación, cambio de estado y cancelación de órdenes,
 * así como la gestión de filtros.
 */
class OrdenController
{
  // Propiedades privadas para las instancias de las clases DAO y otros controladores.
  private $ordenDAO;
  private $stock_almacenController;
  private $alimentoDAO;
  private $almacenDAO;
  private $estadoDAO;
  private $orden_canceladaDAO;

  /**
   * Constructor de la clase.
   * Inicializa todas las propiedades DAO y el controlador de stock.
   */
  public function __construct()
  {
    $this->ordenDAO = new OrdenDAO();
    $this->stock_almacenController = new Stock_almacenController();
    $this->alimentoDAO = new AlimentoDAO();
    $this->almacenDAO = new AlmacenDAO();
    $this->estadoDAO = new EstadoDAO();
    $this->orden_canceladaDAO = new Orden_canceladaDAO();
  }

  /**
   * Procesa las acciones de los formularios (crear, modificar, cambiar de estado, cancelar).
   *
   * @return array|string Un array con el tipo y mensaje de la respuesta, o una cadena vacía.
   */
  public function procesarFormulario()
  {
    // Verifica que la petición sea POST.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return '';
    }

    // Obtiene la acción de la orden y valida que sea una de las acciones permitidas.
    $accionOrden = $_POST['accionOrden'] ?? '';
    if (!in_array($accionOrden, ['crear', 'modificar', 'enviar', 'preparar', 'trasladar', 'entregar', 'cancelar'])) {
      return ''; // Evita procesar si no es una acción válida.
    }
    $mensaje = '';

    // Obtiene los datos del formulario.
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

    // Utiliza un switch para manejar las diferentes acciones.
    switch ($accionOrden) {
      case 'crear':
        // Lógica para crear una nueva orden.
        if (empty($almacenId) || empty($alimentoId) || empty($cantidad)) {
          return ['tipo' => 'error', 'mensaje' => 'Por favor, completá todos los campos para crear la orden.'];
        }

        if (!is_numeric($cantidad) || $cantidad <= 0) {
          return ['tipo' => 'error', 'mensaje' => 'La cantidad debe ser un número positivo.'];
        }

        // Verifica que el almacén y el alimento existan.
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

        // Verifica que haya suficiente stock.
        $stockDisponible = $this->stock_almacenController->getStockByAlimentoInAlmacen($almacen_id, $alimento_id);
        if ($stockDisponible < $cantidad) {
          return ['tipo' => 'error', 'mensaje' => 'No hay suficiente stock disponible para el alimento seleccionado en este almacén. Stock actual: ' . $stockDisponible];
        }

        // Crea el objeto Orden y lo registra.
        $orden = new Orden(null, $almacen_id, $alimento_id, $cantidad, $fecha_creacion, $hora_creacion, $fecha_actualizacion, $hora_actualizacion, $estado_id);
        if ($this->ordenDAO->registrarOrden($orden)) {
          // Si la orden se registra, reduce el stock.
          if ($this->stock_almacenController->reducirStock($almacen_id, $alimento_id, $cantidad)) {
            return ['tipo' => 'success', 'mensaje' => 'Orden registrada correctamente.'];
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Orden registrada, pero hubo un error al descontar el stock.'];
          }
        } else {
          return ['tipo' => 'error', 'mensaje' => 'Error al registrar la orden'];
        }
      case 'modificar':
        // Lógica para modificar una orden existente.
        $ordenActual = $this->ordenDAO->getOrdenById($id);
        if (!$ordenActual) {
          return ['tipo' => 'error', 'mensaje' => 'La orden no existe para modificar'];
        }

        // Determina los nuevos IDs de almacén y alimento.
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

        // Obtiene la nueva cantidad y valida que sea un número positivo.
        $cantidadNueva = $cantidad !== '' ? $cantidad : $ordenActual->getCantidad();
        if (!is_numeric($cantidadNueva) || $cantidadNueva <= 0) {
          return ['tipo' => 'error', 'mensaje' => 'La cantidad debe ser un número positivo.'];
        }

        $cantidadVieja = $ordenActual->getCantidad();
        $diferenciaDeStock = $cantidadNueva - $cantidadVieja;

        // Verifica si hay suficiente stock para el aumento.
        $stockDisponible = $this->stock_almacenController->getStockByAlimentoInAlmacen($almacen_id_nuevo, $alimento_id_nuevo);
        if ($diferenciaDeStock > 0 && $stockDisponible < $diferenciaDeStock) {
          return ['tipo' => 'error', 'mensaje' => 'No hay suficiente stock para aumentar la cantidad de la orden. Stock actual: ' . $stockDisponible];
        }

        // Crea el objeto Orden con los datos modificados y lo actualiza.
        $fechaNueva = date('Y-m-d');
        $horaNueva = date('H:i');
        $ordenModificada = new Orden($id, $almacen_id_nuevo, $alimento_id_nuevo, $cantidadNueva, $ordenActual->getFecha_creacion(), $ordenActual->getHora_creacion(), $fechaNueva, $horaNueva, $estado_id);
        if ($this->ordenDAO->modificarOrden($ordenModificada)) {
          // Ajusta el stock según la diferencia de cantidad.
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
        // Lógica para eliminar una orden.
        $ordenActual = $this->ordenDAO->getOrdenById($id);
        if (!$ordenActual) {
          return ['tipo' => 'error', 'mensaje' => 'La orden no existe para eliminar'];
        }
        if ($this->ordenDAO->eliminarOrden($id)) {
          return ['tipo' => 'success', 'mensaje' => 'Orden eliminada correctamente'];
        } else {
          return ['tipo' => 'error', 'mensaje' => 'Error al eliminar la orden'];
        }
      // Lógica para cambiar el estado de la orden. Cada caso es similar:
      // 1. Obtiene la orden actual.
      // 2. Valida que el estado actual sea el correcto para el cambio.
      // 3. Crea una nueva instancia de Orden con el nuevo estado y la actualiza.
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
        $ordenEnviada = new Orden($id, $ordenActual->getAlmacen_id(), $ordenActual->getAlimento_id(), $ordenActual->getCantidad(), $ordenActual->getFecha_creacion(), $ordenActual->getHora_creacion(), $fechaNueva, $horaNueva, $estadoNuevo_id);
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
        $ordenEnviada = new Orden($id, $ordenActual->getAlmacen_id(), $ordenActual->getAlimento_id(), $ordenActual->getCantidad(), $ordenActual->getFecha_creacion(), $ordenActual->getHora_creacion(), $fechaNueva, $horaNueva, $estadoNuevo_id);
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
        $ordenEnviada = new Orden($id, $ordenActual->getAlmacen_id(), $ordenActual->getAlimento_id(), $ordenActual->getCantidad(), $ordenActual->getFecha_creacion(), $ordenActual->getHora_creacion(), $fechaNueva, $horaNueva, $estadoNuevo_id);
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
        $ordenEnviada = new Orden($id, $ordenActual->getAlmacen_id(), $ordenActual->getAlimento_id(), $ordenActual->getCantidad(), $ordenActual->getFecha_creacion(), $ordenActual->getHora_creacion(), $fechaNueva, $horaNueva, $estadoNuevo_id);
        if ($this->ordenDAO->modificarOrden($ordenEnviada)) {
          return ['tipo' => 'success', 'mensaje' => 'Orden entregada correctamente'];
        } else {
          return ['tipo' => 'error', 'mensaje' => 'Error al entregar la orden'];
        }
      case 'cancelar':
        // Lógica para cancelar una orden.
        $ordenActual = $this->ordenDAO->getOrdenById($id);
        if (!$ordenActual) {
          return ['tipo' => 'error', 'mensaje' => 'La orden no existe para cancelar'];
        }
        $fechaNueva = date('Y-m-d');
        $horaNueva = date('H:i');
        $estadoNuevo_id = 6;
        $ordenCancelada = new Orden($id, $ordenActual->getAlmacen_id(), $ordenActual->getAlimento_id(), $ordenActual->getCantidad(), $ordenActual->getFecha_creacion(), $ordenActual->getHora_creacion(), $fechaNueva, $horaNueva, $estadoNuevo_id);
        $descripcion_cancelacion = $_POST['descripcion'] ?? 'Sin descripción.';

        if ($this->ordenDAO->modificarOrden($ordenCancelada)) {
          // Devuelve el stock al almacén.
          $almacen_id_cancelada = $ordenActual->getAlmacen_id();
          $alimento_id_cancelado = $ordenActual->getAlimento_id();
          $cantidad_cancelada = $ordenActual->getCantidad();
          if ($this->stock_almacenController->actualizarStock_almacen($almacen_id_cancelada, $alimento_id_cancelado, $cantidad_cancelada)) {
            // Registra el detalle de la cancelación.
            $cancelacion = new Orden_cancelada(null, $id, $descripcion_cancelacion, $fechaNueva, $horaNueva);
            if ($this->orden_canceladaDAO->registrarOrden_cancelada($cancelacion)) {
              return ['tipo' => 'success', 'mensaje' => 'Orden cancelada y stock devuelto correctamente.'];
            } else {
              return ['tipo' => 'error', 'mensaje' => 'Orden cancelada y stock devuelto, pero hubo un error al registrar la descripción de la cancelación.'];
            }
          } else {
            return ['tipo' => 'error', 'mensaje' => 'Orden cancelada, pero hubo un error al devolver el stock.'];
          }
        } else {
          return ['tipo' => 'error', 'mensaje' => 'Error al cancelar la orden'];
        }
      default:
        return ['tipo' => 'error', 'mensaje' => 'Acción no válida.'];
    }
  }

  /**
   * Obtiene todas las órdenes y las enriquece con los nombres de almacén, alimento y estado.
   *
   * @return array Un array de objetos Orden enriquecidos.
   */
  public function obtenerOrdenes()
  {
    $ordenes = $this->ordenDAO->getAllOrdenes();
    return $this->enrichOrdenesWithNames($ordenes);
  }

  /**
   * Obtiene una orden específica por su ID.
   *
   * @param int $id El ID de la orden.
   * @return Orden|null La orden encontrada o null si no existe.
   */
  public function obtenerOrdenPorId($id)
  {
    return $this->ordenDAO->getOrdenById($id);
  }

  /**
   * Procesa los filtros de búsqueda para las órdenes.
   * Implementa el patrón Post/Redirect/Get (PRG) para evitar re-envíos de formularios.
   *
   * @return array Un array de objetos Orden filtrados y enriquecidos.
   */
  public function procesarFiltro()
  {
    $ordenes = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['aplicar_filtros']) || isset($_POST['limpiar_filtros']))) {
      // Redirige a la misma página, pero con los parámetros de filtro en la URL (GET).
      $redirectUrl = $_SERVER['PHP_SELF'];
      $queryParams = [];
      if (isset($_POST['limpiar_filtros'])) {
        // No se añaden parámetros, la URL ya está limpia.
      } elseif (isset($_POST['aplicar_filtros'])) {
        // Añade los filtros seleccionados a los parámetros de la URL.
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
      exit; // Es crucial salir después de la redirección.
    }

    // Si la petición es GET, procesa los filtros desde la URL.
    $almacen_id_filtro = $_GET['almacen_id'] ?? [];
    $alimento_id_filtro = $_GET['alimento_id'] ?? [];
    $estado_id_filtro = $_GET['estado_id'] ?? [];
    if (empty($almacen_id_filtro) && empty($alimento_id_filtro) && empty($estado_id_filtro)) {
      $ordenes = $this->ordenDAO->getAllOrdenes();
    } else {
      $ordenes = $this->ordenDAO->getOrdenesFiltradas($almacen_id_filtro, $alimento_id_filtro, $estado_id_filtro);
    }
    // Enriquece las órdenes con nombres.
    return $this->enrichOrdenesWithNames($ordenes);
  }

  /**
   * Método privado para añadir los nombres de almacén, alimento y estado a los objetos Orden.
   *
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

  /**
   * Obtiene los detalles de una cancelación de orden.
   *
   * @param int $orden_id El ID de la orden.
   * @return Orden_cancelada|null El objeto de cancelación si existe, de lo contrario, null.
   */
  public function obtenerDetalleCancelacion($orden_id)
  {
    return $this->orden_canceladaDAO->getOrden_canceladaByOrdenId($orden_id);
  }
}