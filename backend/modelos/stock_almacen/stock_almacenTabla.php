<?php

require_once __DIR__ . '../../../servicios/databaseFactory.php';
require_once __DIR__ . '../../../servicios/databaseConnectionInterface.php';

class Stock_AlmacenCrearTabla
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function crearTablaStock()
  {
    $this->db = DatabaseFactory::createDatabaseConnection('mysql');
    $conn = $this->db->connect();
    $sql = "CREATE TABLE IF NOT EXISTS stock_almacenes(
              id INT PRIMARY KEY AUTO_INCREMENT,
              almacen_id INT NOT NULL,
              alimento_id INT NOT NULL,
              stock INT NOT NULL,
              FOREIGN KEY (almacen_id) REFERENCES almacenes(id),
              FOREIGN KEY (alimento_id) REFERENCES alimentos(id),
              UNIQUE (almacen_id, alimento_id))";
    $conn->query($sql);
    $conn->close();
  }
}