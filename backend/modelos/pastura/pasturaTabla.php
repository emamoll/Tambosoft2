<?php

require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

class PasturaCrearTabla
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function crearTablaPastura()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS pasturas (
              id INT PRIMARY KEY AUTO_INCREMENT,
              nombre VARCHAR(255) NOT NULL UNIQUE)";
    $conn->query($sql);
    $conn->close();
  }
}
