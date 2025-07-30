<?php

require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

class Orden_canceladaCrearTabla
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function crearTablaOrdenes_canceladas()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS ordenes_canceladas (
              id INT PRIMARY KEY AUTO_INCREMENT,
              orden_id INT NOT NULL,
              descripcion VARCHAR(255) NOT NULL,
              fecha DATE NOT NULL,
              hora TIME NOT NULL,
              FOREIGN KEY (orden_id) REFERENCES ordenes(id)
            )";
    $conn->query($sql);
    $conn->close();
  }
}