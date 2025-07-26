<?php

require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/stock_almacen/stock_almacenTabla.php';
require_once __DIR__ . '../../modelos/stock_almacen/stock_almacenModelo.php';

class Stock_AlmacenDAO
{
  private $db;
  private $conn;
  private $crearTabla;

  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new Stock_AlmacenCrearTabla($this->db);
    $this->crearTabla->crearTablaStock();
    $this->conn = $this->db->connect();
  }

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

  public function getStock_almacenByAlmacenId($almacen_id)
  {
    $sql = "SELECT sa.*, a.nombre as alimento_nombre, a.precio as alimento_precio
                FROM stock_almacenes sa
                JOIN alimentos a ON sa.alimento_id = a.id
                WHERE sa.almacen_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $almacen_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $stock_almacenes = [];
    while ($row = $result->fetch_assoc()) {
      $stock_almacen = new Stock_Almacen($row['id'], $row['almacen_id'], $row['alimento_id'], $row['stock'], $row['alimento_precio']);
      $stock_almacen->setAlimentoNombre($row['alimento_nombre']);
      $stock_almacen->setAlimentoPrecio($row['alimento_precio']);
      $stock_almacenes[] = $stock_almacen;
    }

    return $stock_almacenes;
  }

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

  public function actualizarStock_almacen($almacen_id, $alimento_id, $cantidad)
  {
    $stockActual = $this->getStock_almacenByAlmacenIdAndAlimentoId($almacen_id, $alimento_id);

    if ($stockActual) {
      $nuevoStock = $stockActual->getStock() + $cantidad;
      $sql = "UPDATE stock_almacenes SET stock = ? WHERE almacen_id = ? AND alimento_id = ?";
      $stmt = $this->conn->prepare($sql);

      if (!$stmt) {
        die("Error en la consulta: " . $this->conn->error);
      }

      $stmt->bind_param("iii", $nuevoStock, $almacen_id, $alimento_id);
      return $stmt->execute();
    } else {
      $sql = "INSERT INTO stock_almacenes(almacen_id, alimento_id, stock) VALUES (?, ?, ?)";
      $stmt = $this->conn->prepare($sql);

      if (!$stmt) {
        die("Error en la consulta: " . $this->conn->error);
      }

      $stmt->bind_param("iii", $almacen_id, $alimento_id, $cantidad);
      return $stmt->execute();
    }
  }

  public function getAllStock_almacenesItems()
  {
    $sql = "SELECT 
                    sa.id as stock_id,
                    sa.stock,
                    a.nombre as alimento_nombre,
                    alm.nombre as almacen_nombre,
                    c.nombre as campo_nombre,
                    alm.id as almacen_id,
                    a.id as alimento_id
                FROM 
                    stock_almacenes sa
                JOIN 
                    alimentos a ON sa.alimento_id = a.id
                JOIN 
                    almacenes alm ON sa.almacen_id = alm.id
                JOIN 
                    campos c ON alm.campo_id = c.id
                ORDER BY 
                    alm.nombre, a.nombre"; // Ordenar para una mejor lectura

    $result = $this->conn->query($sql);

    if (!$result) {
      error_log("DEBUG: StockAlimentosDAO - Error al obtener todos los ítems de stock con detalles: " . $this->conn->error);
      return [];
    }

    $stockDetails = [];
    while ($row = $result->fetch_assoc()) {
      $stockDetails[] = $row;
    }
    return $stockDetails;
  }
}