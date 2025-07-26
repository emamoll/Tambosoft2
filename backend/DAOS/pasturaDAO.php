<?php

require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/pastura/pasturaModelo.php';
require_once __DIR__ . '../../modelos/pastura/pasturaTabla.php';

class PasturaDAO
{
  private $db;
  private $conn;
  private $crearTabla;

  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new PasturaCrearTabla($this->db);
    $this->crearTabla->crearTablaPastura();
    $this->conn = $this->db->connect();
  }

  public function registrarPastura(Pastura $p)
  {
    $sqlVer = "SELECT id FROM pasturas WHERE nombre = ?";
    $stmtVer = $this->conn->prepare($sqlVer);
    $nombrePastura = $p->getNombre();
    $stmtVer->bind_param("s", $nombrePastura);
    $stmtVer->execute();
    $stmtVer->store_result();

    if ($stmtVer->num_rows > 0) {
      $stmtVer->close();
      return false; // La pastura ya existe
    }
    $stmtVer->close();

    $sql = "INSERT INTO pasturas (nombre) VALUES (?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombrePastura);

    if (!$stmt->execute()) {
      error_log("Error al insertar pastura: " . $stmt->error);
      $stmt->close();
      return false;
    }
    $stmt->close();
    return true;
  }

  public function getAllPasturas()
  {
    $sql = "SELECT * FROM pasturas";
    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $pasturas = [];

    while ($row = $result->fetch_assoc()) {
      $pasturas[] = new Pastura($row['id'], $row['nombre']);
    }

    return $pasturas;
  }

  public function getPasturaById($id)
  {
    $sql = "SELECT * FROM pasturas WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
      return new Pastura($row['id'], $row['nombre']);
    }
    return null;
  }

  public function getPasturaByNombre($nombre)
  {
    $sql = "SELECT * FROM pasturas WHERE nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
      return new Categoria($row['id'], $row['nombre']);
    }
    return null;
  }

  public function modificarPastura(Pastura $p)
  {
    $sql = "UPDATE pasturas SET nombre = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $nombre = $p->getNombre();
    $id = $p->getId();
    $stmt->bind_param("si", $nombre, $id);

    return $stmt->execute();
  }

  public function eliminarPastura($nombre)
  {
    $sql = "DELETE FROM pasturas WHERE n$nombre = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $nombre);
    return $stmt->execute();
  }
}