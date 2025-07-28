<?php

require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/alimento/alimentoTabla.php';
require_once __DIR__ . '../../modelos/alimento/alimentoModelo.php';

class AlimentoDAO
{
  private $db;
  private $conn;
  private $crearTabla;

  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new AlimentoCrearTabla($this->db);
    $this->crearTabla->crearTablaAlimentos();
    $this->conn = $this->db->connect();
  }

  public function getAllAlimentos()
  {
    $sql = "SELECT * FROM alimentos";
    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $alimentos = [];

    while ($row = $result->fetch_assoc()) {
      $alimentos[] = new Alimento($row['id'], $row['nombre'], $row['precio'], $row['descripcion'], $row['peso'], $row['fecha_vencimiento']);
    }

    return $alimentos;
  }

  public function getAlimentoById($id)
  {
    $sql = "SELECT * FROM alimentos WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
    $stmt->fetch();

    return new Alimento($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
  }

  public function getAlimentoByNombre($nombre)
  {
    $sql = "SELECT * FROM alimentos WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
    $stmt->fetch();

    return new Alimento($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
  }

  public function getAlimentoByPrecio($precio)
  {
    $sql = "SELECT * FROM alimentos WHERE precio = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $precio);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
    $stmt->fetch();

    return new Alimento($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
  }

  public function getAlimentoByPeso($peso)
  {
    $sql = "SELECT * FROM alimentos WHERE peso = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $peso);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
    $stmt->fetch();

    return new Alimento($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
  }

  public function getAlimentoByFechaVencimiento($fecha_vencimiento)
  {
    $sql = "SELECT * FROM alimentos WHERE fecha_vencimiento) = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $fecha_vencimiento);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
    $stmt->fetch();

    return new Alimento($id, $nombre, $precio, $descripcion, $peso, $fecha_vencimiento);
  }

  public function getAlimentosPorIds(array $ids)
  {
    if (empty($ids)) {
      return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT * FROM alimentos WHERE id IN ($placeholders)";
    $stmt = $this->conn->prepare($sql);

    if ($stmt === false) {
      die("Error en prepare: " . $this->conn->error);
    }

    $tipos = str_repeat('i', count($ids));
    $refs = [];
    foreach ($ids as $key => $value) {
      $refs[$key] = &$ids[$key];
    }
    array_unshift($refs, $tipos);

    call_user_func_array([$stmt, 'bind_param'], $refs);

    $stmt->execute();

    $result = $stmt->get_result();

    $alimentos = [];

    while ($row = $result->fetch_assoc()) {
      $alimentos[] = new Alimento(
        $row['id'],
        $row['nombre'],
        $row['precio'],
        $row['descripcion'],
        $row['peso'],
        $row['fecha_vencimiento']
      );
    }

    $stmt->close();

    return $alimentos;
  }

  public function registrarAlimento(Alimento $a)
  {
    $sqlVer = "SELECT id FROM alimentos WHERE nombre = ?";
    $stmtVer = $this->conn->prepare($sqlVer);
    $nombre = $a->getNombre();
    $stmtVer->bind_param("s", $nombre);
    $stmtVer->execute();
    $stmtVer->store_result();

    if ($stmtVer->num_rows > 0) {
      return false;
    }

    $stmtVer->close();

    $sql = "INSERT INTO alimentos (nombre, precio, descripcion, peso, fecha_vencimiento) VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $n = $a->getNombre();
    $p = $a->getPrecio();
    $d = $a->getDescripcion();
    $pe = $a->getPeso();
    $f = $a->getFecha_vencimiento();
    $stmt->bind_param("sssss", $n, $p, $d, $pe, $f);

    if (!$stmt->execute()) {
      die("Error en execute (inserción): " . $stmt->error);
    }

    $stmt->close();

    return true;
  }

  public function modificarAlimento(Alimento $a)
  {
    $sql = "UPDATE alimentos SET precio = ?, descripcion = ?, peso = ?, fecha_vencimiento = ? WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $n = $a->getNombre();
    $p = $a->getPrecio();
    $d = $a->getDescripcion();
    $pe = $a->getPeso();
    $f = $a->getFecha_vencimiento();
    $stmt->bind_param('sssss', $p, $d, $pe, $f, $n);

    return $stmt->execute();
  }

  public function eliminarAlimento($nombre)
  {
    $sql = "DELETE FROM alimentos WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);

    return $stmt->execute();
  }
}