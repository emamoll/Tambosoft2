<?php

require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/campo/campoTabla.php';
require_once __DIR__ . '../../modelos/campo/campoModelo.php';
require_once __DIR__ . '../../modelos/almacen/almacenTabla.php';

class campoDAO
{
  private $db;
  private $conn;
  private $crearTabla;
  private $crearTablaAlmacen;

  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new CampoCrearTabla($this->db);
    $this->crearTabla->crearTablaCampos();
    $this->crearTablaAlmacen = new AlmacenCrearTabla($this->db);
    $this->crearTablaAlmacen->crearTablaAlmacen();
    $this->conn = $this->db->connect();
  }

  public function getAllCampos()
  {
    $sql = "SELECT * FROM campos";
    $result = $this->conn->query($sql);
    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $campos = [];

    while ($row = $result->fetch_assoc()) {
      $campos[] = new Campo($row['id'], $row['nombre'], $row['ubicacion']);
    }

    return $campos;
  }

  public function getCampoById($id)
  {
    $sql = "SELECT * FROM campos WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    if ($row = $result->fetch_assoc()) {
      return new Campo($row['id'], $row['nombre'], $row['ubicacion']);
    }
    return null;
  }

  public function getCampoByNombre($nombre)
  {
    $sql = "SELECT * FROM campos WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
      return null;
    }

    $stmt->bind_result($id, $nombre, $ubicacion);
    $stmt->fetch();

    return new Campo($id, $nombre, $ubicacion);
  }

  public function registrarCampo(Campo $c)
  {
    // Verificar si ya existe un campo con ese nombre
    $sqlVer = "SELECT id FROM campos WHERE nombre = ?";
    $stmtVer = $this->conn->prepare($sqlVer);
    $nombre = $c->getNombre();
    $stmtVer->bind_param("s", $nombre);
    $stmtVer->execute();
    $stmtVer->store_result();

    if ($stmtVer->num_rows > 0) {
      $stmtVer->close();
      return false;
    }
    $stmtVer->close();

    // Insertar el campo
    $sql = "INSERT INTO campos (nombre, ubicacion) VALUES (?, ?)";
    $stmt = $this->conn->prepare($sql);
    $n = $c->getNombre();
    $u = $c->getUbicacion();
    $stmt->bind_param("ss", $n, $u);

    if (!$stmt->execute()) {
      $stmt->close();
      return false;
    }

    // Obtener el ID del campo recién insertado
    $campo_id = $stmt->insert_id;
    $stmt->close();

    // Insertar el almacén con el mismo nombre y campo_id
    $sqlAlm = "INSERT INTO almacenes (nombre, campo_id) VALUES (?, ?)";
    $stmtAlm = $this->conn->prepare($sqlAlm);
    $stmtAlm->bind_param("si", $n, $campo_id);

    $resultado = $stmtAlm->execute();
    $stmtAlm->close();

    return $resultado;
  }

  public function modificarCampo(Campo $c)
  {
    $sql = "UPDATE campos SET ubicacion = ? WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $n = $c->getNombre();
    $u = $c->getUbicacion();
    $stmt->bind_param("ss", $u, $n);

    return $stmt->execute();
  }

  public function eliminarCampo($nombre)
  {
    $sql = "DELETE FROM campos WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);

    return $stmt->execute();
  }
}
