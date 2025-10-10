<?php

// Incluye los archivos necesarios para la conexión a la base de datos, el modelo y la tabla del stock.
require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/stock_almacen/stock_almacenTabla.php';
require_once __DIR__ . '../../modelos/stock_almacen/stock_almacenModelo.php';

/**
 * Clase para el acceso a datos (DAO) de la tabla 'stock_almacenes'.
 * Maneja las operaciones de la base de datos relacionadas con el stock en los almacenes.
 */
class Stock_AlmacenDAO
{
  // Propiedades privadas para la conexión y la creación de la tabla.
  private $db;
  private $conn;
  private $crearTabla;

  /**
   * Constructor de la clase.
   * Inicializa la conexión y se asegura de que la tabla 'stock_almacenes' exista.
   */
  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new Stock_AlmacenCrearTabla($this->db);
    $this->crearTabla->crearTablaStock();
    $this->conn = $this->db->connect();
  }

  /**
   * Obtiene todos los registros de stock de todos los almacenes.
   *
   * @return array Un array de objetos Stock_Almacen.
   */
  public function getAllStock_almacenes()
  {
    $sql = "SELECT * FROM stock_almacenes";
    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $stock_almacenes = [];
    while ($row = $result->fetch_assoc()) {
      $stock_almacenes[] = new Stock_Almacen($row['id'], $row['almacen_id'], $row['alimento_id'], $row['stock']);
    }
    return $stock_almacenes;
  }

  /**
   * Obtiene un registro de stock por su ID.
   *
   * @param int $id El ID del registro.
   * @return Stock_Almacen|null Un objeto Stock_Almacen si se encuentra, de lo contrario, null.
   */
  public function getStock_almacenById($id)
  {
    $sql = "SELECT * FROM stock_almacenes WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $almacen_id, $alimento_id, $stock);
    $stmt->fetch();
    return new Stock_Almacen($id, $almacen_id, $alimento_id, $stock);
  }

  /**
   * Obtiene los elementos de stock para un almacén específico, incluyendo el nombre y precio del alimento.
   *
   * @param int $almacen_id El ID del almacén.
   * @return array Un array de objetos Stock_Almacen enriquecidos.
   */
  public function getStock_almacenByAlmacenId($almacen_id)
  {
    $sql = "SELECT sa.*, a.nombre as alimento_nombre, a.precio as alimento_precio FROM stock_almacenes sa JOIN alimentos a ON sa.alimento_id = a.id WHERE sa.almacen_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $almacen_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $stock_almacenes = [];
    while ($row = $result->fetch_assoc()) {
      // Se crea el objeto y luego se añaden propiedades extra.
      $stock_almacen = new Stock_Almacen($row['id'], $row['almacen_id'], $row['alimento_id'], $row['stock']); // El constructor no recibe el precio. ¡Revisar!
      $stock_almacen->setAlimentoNombre($row['alimento_nombre']);
      $stock_almacen->setAlimentoPrecio($row['alimento_precio']);
      $stock_almacenes[] = $stock_almacen;
    }
    return $stock_almacenes;
  }

  /**
   * Obtiene una lista de registros de stock por el ID del alimento.
   *
   * @param int $alimento_id El ID del alimento.
   * @return array Un array de objetos Stock_Almacen.
   */
  public function getStock_almacenByAlimentoId($alimento_id)
  {
    $sql = "SELECT * FROM stock_almacenes WHERE alimento_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $alimento_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $stock_almacenes = [];
    while ($row = $result->fetch_assoc()) {
      $stock_almacenes[] = new Stock_Almacen($row['id'], $row['almacen_id'], $row['alimento_id'], $row['stock']);
    }
    return $stock_almacenes;
  }

  /**
   * Obtiene un registro de stock específico por el ID del almacén y el ID del alimento.
   *
   * @param int $almacen_id El ID del almacén.
   * @param int $alimento_id El ID del alimento.
   * @return Stock_Almacen|null Un objeto Stock_Almacen si se encuentra, de lo contrario, null.
   */
  public function getStock_almacenByAlmacenIdAndAlimentoId($almacen_id, $alimento_id)
  {
    $sql = "SELECT * FROM stock_almacenes WHERE almacen_id = ? AND alimento_id = ?";
    $stmt = $this->conn->prepare($sql);

    if (!$stmt) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $stmt->bind_param("ii", $almacen_id, $alimento_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      return new Stock_Almacen($row['id'], $row['almacen_id'], $row['alimento_id'], $row['stock']);
    }
    return null;
  }

  /**
   * Actualiza el stock de un alimento en un almacén o lo registra si no existe.
   *
   * @param int $almacen_id El ID del almacén.
   * @param int $alimento_id El ID del alimento.
   * @param int $cantidad La cantidad a agregar (si es un nuevo registro) o sumar al stock actual.
   * @return bool True si la operación fue exitosa, de lo contrario, false.
   */
  public function actualizarStock_almacen($almacen_id, $alimento_id, $cantidad)
  {
    $stockActual = $this->getStock_almacenByAlmacenIdAndAlimentoId($almacen_id, $alimento_id);

    if ($stockActual) {
      // Si el registro de stock ya existe, actualiza la cantidad.
      $nuevoStock = $stockActual->getStock() + $cantidad;
      $sql = "UPDATE stock_almacenes SET stock = ? WHERE almacen_id = ? AND alimento_id = ?";
      $stmt = $this->conn->prepare($sql);

      if (!$stmt) {
        die("Error en la consulta: " . $this->conn->error);
      }
      $stmt->bind_param("iii", $nuevoStock, $almacen_id, $alimento_id);
      return $stmt->execute();
    } else {
      // Si no existe, inserta un nuevo registro de stock.
      $sql = "INSERT INTO stock_almacenes(almacen_id, alimento_id, stock) VALUES (?, ?, ?)";
      $stmt = $this->conn->prepare($sql);

      if (!$stmt) {
        die("Error en la consulta: " . $this->conn->error);
      }
      $stmt->bind_param("iii", $almacen_id, $alimento_id, $cantidad);
      return $stmt->execute();
    }
  }

  /**
   * Reduce el stock de un alimento en un almacén.
   *
   * @param int $almacen_id El ID del almacén.
   * @param int $alimento_id El ID del alimento.
   * @param int $cantidad La cantidad a reducir.
   * @return bool True si la reducción fue exitosa, de lo contrario, false.
   */
  public function reducirStock_almacen($almacen_id, $alimento_id, $cantidad)
  {
    $stockActual = $this->getStock_almacenByAlmacenIdAndAlimentoId($almacen_id, $alimento_id);

    if ($stockActual) {
      $nuevoStock = $stockActual->getStock() - $cantidad;
      if ($nuevoStock < 0) {
        return false; // No hay suficiente stock para la reducción.
      }
      $sql = "UPDATE stock_almacenes SET stock = ? WHERE almacen_id = ? AND alimento_id = ?";
      $stmt = $this->conn->prepare($sql);

      if (!$stmt) {
        error_log("Error en la consulta: " . $this->conn->error);
        return false;
      }
      $stmt->bind_param("iii", $nuevoStock, $almacen_id, $alimento_id);
      return $stmt->execute();
    }
    return false; // El registro de stock no fue encontrado.
  }

  /**
   * Obtiene una lista de alimentos con stock positivo en un almacén.
   *
   * @param int $almacen_id El ID del almacén.
   * @return array Un array de arrays asociativos con los datos de los alimentos.
   */
  public function getAlimentosConStockByAlmacenId($almacen_id)
  {
    $sql = "SELECT sa.alimento_id, a.nombre as alimento_nombre, sa.stock FROM stock_almacenes sa JOIN alimentos a ON sa.alimento_id = a.id WHERE sa.almacen_id = ? AND sa.stock > 0";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $almacen_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $alimentosConStock = [];
    while ($row = $result->fetch_assoc()) {
      $alimentosConStock[] = [
        'id' => $row['alimento_id'],
        'nombre' => $row['alimento_nombre'],
        'stock' => $row['stock']
      ];
    }
    return $alimentosConStock;
  }

  public function getStocksFiltradas(array $almacen_id, array $alimento_id)
  {
    // Construye la consulta SQL dinámicamente.
    $sql = "SELECT * FROM stock_almacenes WHERE 1=1";
    $params = [];
    $tipos = '';

    if (!empty($almacen_id)) {
      $placeholders = implode(',', array_fill(0, count($almacen_id), '?'));
      $sql .= " AND almacen_id IN ($placeholders)";
      $params = array_merge($params, $almacen_id);
      $tipos .= str_repeat('i', count($almacen_id));
    }

    if (!empty($alimento_id)) {
      $placeholders = implode(',', array_fill(0, count($alimento_id), '?'));
      $sql .= " AND alimento_id IN ($placeholders)";
      $params = array_merge($params, $alimento_id);
      $tipos .= str_repeat('i', count($alimento_id));
    }

    $sql .= " ORDER BY id";

    $stmt = $this->conn->prepare($sql);
    if ($stmt === false) {
      error_log("Error en prepare (getStocksFiltradas): " . $this->conn->error);
      return [];
    }

    // Vincula los parámetros dinámicamente.
    if (!empty($params)) {
      $bind_names = [];
      $bind_names[] = $tipos;
      foreach ($params as $key => $value) {
        $bind_names[] = &$params[$key];
      }
      call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }

    if (!$stmt->execute()) {
      error_log("Error en execute (getStocksFiltradas): " . $stmt->error);
      $stmt->close();
      return [];
    }

    $resultado = $stmt->get_result();

    $stocks = [];
    while ($row = $resultado->fetch_assoc()) {
      $stock = new Stock_Almacen(
        $row['id'],
        $row['almacen_id'],
        $row['alimento_id'],
        $row['stock']
      );
      $stocks[] = $stock;
    }
    $stmt->close();
    return $stocks;
  }

  public function getTotalStockByAlimentoId($alimento_id)
  {
    $sql = "SELECT SUM(stock) AS total_stock FROM stock_almacenes WHERE alimento_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $alimento_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total_stock'] ?? 0;
  }

  public function getTotalEconomicValue()
  {
    $sql = "SELECT SUM(sa.stock * a.precio) AS total_valor FROM stock_almacenes sa JOIN alimentos a ON sa.alimento_id = a.id";
    $result = $this->conn->query($sql);

    if (!$result) {
      error_log("Error en la consulta: " . $this->conn->error);
      return 0;
    }

    $row = $result->fetch_assoc();
    return $row['total_valor'] ?? 0;
  }
}