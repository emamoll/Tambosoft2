<?php

require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/estado/estadoTabla.php';
require_once __DIR__ . '../../modelos/estado/estadoModelo.php';

class EstadoDAO
{
  private $db;
  private $conn;
  private $crearTabla;

  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->crearTabla = new EstadoCrearTabla($this->db);
    $this->crearTabla->crearTablaEstados();
    $this->crearTabla->insertarValoresTablaEstados();
    $this->conn = $this->db->connect();
  }

  public function getAllEstados()
  {
    $sql = "SELECT * FROM estados";
    $result = $this->conn->query($sql);

    if (!$result) {
      die("Error en la consulta: " . $this->conn->error);
    }

    $estados = [];

    while ($row = $result->fetch_assoc()) {
      $estados[] = new Estado($row['id'], $row['nombre']);
    }

    return $estados;
  }

  public function getEstadoById($id)
  {
    $sql = "SELECT * FROM estados WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->store_result();

    if (!$stmt) {
      die("Error al preparar la consulta: " . $this->conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
      return null;
    }

    $row = $resultado->fetch_assoc();
    return new Estado($row['id'], $row['nombre']);
  }
}