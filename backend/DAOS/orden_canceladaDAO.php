<?php

require_once __DIR__ . '../../servicios/databaseFactory.php';
require_once __DIR__ . '../../modelos/orden_cancelada/orden_canceladaTabla.php';
require_once __DIR__ . '../../modelos/orden_cancelada/orden_canceladaModelo.php';

class Orden_canceladaDAO
{
  private $db;
  private $conn;

  public function __construct()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $this->conn = $this->db->connect();
  }

  public function registrarOrden_cancelada(Orden_cancelada $o_c)
  {
    $sql = "INSERT INTO ordenes_canceladas (orden_id, descripcion, fecha, hora) VALUES (?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $orden_id = $o_c->getOrden_id();
    $descripcion = $o_c->getDescripcion();
    $fecha = $o_c->getFecha();
    $hora = $o_c->getHora();
    $stmt->bind_param("isss", $orden_id, $descripcion, $fecha, $hora);

    if (!$stmt->execute()) {
      error_log("Error al registrar cancelación: " . $stmt->error);
      return false;
    }
    $stmt->close();
    return true;
  }

  public function getOrden_canceladaByOrdenId($orden_id)
  {
    $sql = "SELECT * FROM ordenes_canceladas WHERE orden_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $orden_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      return new Orden_cancelada($row['id'], $row['orden_id'], $row['descripcion'], $row['fecha'], $row['hora']);
    }
    return null;
  }
}