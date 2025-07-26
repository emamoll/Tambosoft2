<?php

require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

class EstadoCrearTabla
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function crearTablaEstados()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS estados (
              id INT PRIMARY KEY AUTO_INCREMENT, 
              nombre VARCHAR(255) NOT NULL UNIQUE)";

    $conn->query($sql);
    $conn->close();
  }

  public function insertarValoresTablaEstados()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $resultado = $conn->query("SELECT COUNT(*) as total FROM estados");
    $fila = $resultado->fetch_assoc();

    if ($fila['total'] == 0) {
      $sql = "INSERT INTO estados (nombre) VALUES 
          ('creada'), 
          ('preparacion'), 
          ('en traslado'), 
          ('entregada'), 
          ('cancelada')";
      $conn->query($sql);
    }
    $conn->close();
  }

}